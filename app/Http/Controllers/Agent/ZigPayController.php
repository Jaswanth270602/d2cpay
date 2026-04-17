<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Validator;
use App\Models\Gatewayorder;
use App\Models\Provider;
use App\Models\User;
use App\Models\Balance;
use App\Models\Report;
use App\Models\Api;
use App\Models\Member;
use Helpers;
use App\Models\Apiresponse;
use App\Models\Traceurl;
use QrCode;
use App\Library\BasicLibrary;
use App\Library\GetcommissionLibrary;
use App\Library\Commission_increment;

class ZigPayController extends Controller
{
    private $api_id;
    private $provider_id;
    private $min_amount;
    private $max_amount;
    private $base_url;
    private $mid;
    private $email;
    private $secretkey;

    public function __construct()
    {
        // Keep these configurable from API credentials if present.
        $this->api_id = 15;
        $this->provider_id = 339;

        $credentials = json_decode(optional(Api::find($this->api_id))->credentials);
        $this->base_url = rtrim($credentials->base_url ?? 'https://api.zigpay.in', '/');
        $this->mid = $credentials->mid ?? '';
        $this->email = $credentials->email ?? '';
        $this->secretkey = $credentials->secretkey ?? '';

        $provider = Provider::find($this->provider_id);
        $detectedGatewayMin = 1;
        if (!empty($this->mid)) {
            $detectedGatewayMin = (int)Cache::get('zigpay:min_amount:' . $this->mid, 1);
        }
        $this->min_amount = max(1, $detectedGatewayMin);
        $this->max_amount = isset($provider->max_amount) ? $provider->max_amount : 50000;
    }

    private function generateToken()
    {
        if (empty($this->mid) || empty($this->email) || empty($this->secretkey)) {
            return '';
        }

        $url = $this->base_url . '/api/Auth/generate-token';
        $header = [
            'accept: */*',
            'Content-Type: application/json',
        ];
        $parameters = [
            'mid' => $this->mid,
            'email' => $this->email,
            'secretkey' => $this->secretkey,
        ];

        $response = Helpers::pay_curl_post($url, $header, json_encode($parameters), 'POST');
        Apiresponse::insertGetId([
            'message' => $response,
            'api_type' => 1,
            'created_at' => now(),
            'ip_address' => request()->ip(),
        ]);

        $res = json_decode($response, true);
        return $res['token'] ?? '';
    }

    private function forwardMemberCallback($userId, $status, $clientId, $amount, $utr, $txnid, $ctime)
    {
        $member = Member::where('user_id', $userId)->first();
        if (empty($member->call_back_url)) {
            return;
        }

        $user = User::find($userId);
        if (!$user || empty($user->api_token)) {
            return;
        }

        $queryParams = [
            'status' => $status,
            'client_id' => $clientId,
            'amount' => $amount,
            'utr' => $utr,
            'txnid' => $txnid,
        ];
        $signatureString = http_build_query($queryParams);
        $queryParams['signature'] = hash_hmac('sha256', $signatureString, $user->api_token);
        $url = $member->call_back_url . '?' . http_build_query($queryParams);
        $response = Helpers::pay_curl_get($url);
        Traceurl::insertGetId([
            'user_id' => $userId,
            'url' => $url,
            'number' => $user->mobile,
            'response_message' => $response,
            'created_at' => $ctime,
        ]);
    }

    public function welcome()
    {
        $user_id = Auth::id();
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($this->provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($serviceStatus == 1) {
            return view('agent.add-money.zigpay')->with([
                'page_title' => 'Payin 8',
                'min_amount' => $this->min_amount,
                'max_amount' => $this->max_amount,
            ]);
        }
        return redirect()->back();
    }

    public function createOrderWeb(Request $request)
    {
        $rules = [
            'amount' => 'required|numeric|between:' . $this->min_amount . ',' . $this->max_amount,
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }

        return $this->createOrderMiddle(
            $request->amount,
            Auth::id(),
            'WEB',
            '',
            '',
            Auth::user()->name,
            Auth::user()->email,
            Auth::user()->mobile
        );
    }

    public function createOrderApi(Request $request)
    {
        $rules = [
            'amount' => 'required|numeric|between:' . $this->min_amount . ',' . $this->max_amount,
            'client_id' => 'required',
            'callback_url' => 'required|url',
            'customer_name' => 'required|string|max:255',
            'mobile_number' => 'required|digits:10',
            'email' => 'required|email|max:255',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }

        return $this->createOrderMiddle(
            $request->amount,
            Auth::id(),
            'API',
            $request->callback_url,
            $request->client_id,
            $request->customer_name,
            $request->email,
            $request->mobile_number
        );
    }

    private function createOrderMiddle($amount, $user_id, $mode, $callback_url, $client_id, $name, $email, $mobile)
    {
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($this->provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($serviceStatus != 1) {
            return response()->json(['status' => 'failure', 'message' => 'Service not active!']);
        }

        $token = $this->generateToken();
        if (empty($token)) {
            return response()->json(['status' => 'failure', 'message' => 'Unable to generate token']);
        }

        $ctime = now();
        $initialToken = 'ZIGTMP' . time() . rand(1000, 9999);
        $gatewayOrderId = Gatewayorder::insertGetId([
            'user_id' => $user_id,
            'purpose' => 'Add Money',
            'amount' => $amount,
            'email' => $email,
            'ip_address' => request()->ip(),
            'created_at' => $ctime,
            'status_id' => 3,
            'api_id' => $this->api_id,
            'callback_url' => $callback_url,
            'client_id' => $client_id,
            'mode' => $mode,
            'order_token' => $initialToken,
        ]);

        $refId = 'ZIG' . $gatewayOrderId . time();
        Gatewayorder::where('id', $gatewayOrderId)->update(['order_token' => $refId]);

        $payload = [
            'RefID' => $refId,
            'Amount' => (string)$amount,
            'Customer_Name' => (string)$name,
            'Customer_Mobile' => (string)$mobile,
            'Customer_Email' => (string)$email,
        ];

        $url = $this->base_url . '/api/Payin/create-order';
        $header = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'accept: */*',
        ];

        $response = Helpers::pay_curl_post($url, $header, json_encode($payload), 'POST');
        Apiresponse::insertGetId([
            'message' => $response,
            'api_type' => 1,
            'created_at' => now(),
            'ip_address' => request()->ip(),
        ]);

        $res = json_decode($response, true);
        if (!is_array($res)) {
            return response()->json(['status' => 'failure', 'message' => 'Invalid response from ZigPay']);
        }

        $providerMessage = (string)($res['message'] ?? $res['responseMessage'] ?? '');
        if (preg_match('/minimum\s+transaction\s+amount\s+must\s+be\s+(\d+)/i', $providerMessage, $matches)) {
            $detectedMin = (int)($matches[1] ?? 0);
            if ($detectedMin > 0 && !empty($this->mid)) {
                Cache::put('zigpay:min_amount:' . $this->mid, $detectedMin, now()->addDays(7));
                $this->min_amount = max(1, $detectedMin);
            }
            return response()->json([
                'status' => 'failure',
                'message' => ' MID minimum is Rs. ' . $detectedMin . '. Please enter Rs. ' . $detectedMin . ' or above.',
            ]);
        }

        $statusCode = (string)($res['status'] ?? ($res['status_id'] ?? ''));
        $docData = is_array($res['data'] ?? null) ? $res['data'] : [];
        $transactionId = (string)($docData['txn_id'] ?? $res['txnID'] ?? $res['txnId'] ?? $res['transaction_id'] ?? $res['transactionId'] ?? $res['order_id'] ?? $res['orderId'] ?? '');
        $gatewayStatus = strtolower((string)($res['transaction_status'] ?? $res['statusText'] ?? $res['message'] ?? $docData['api_status'] ?? ''));
        $qrString = (string)($docData['qrString'] ?? $res['qrString'] ?? $res['qr_string'] ?? '');
        $upiLink = (string)($docData['upilink'] ?? $res['upi_link'] ?? $res['upiLink'] ?? '');
        if ($qrString === '' && $upiLink !== '') {
            $qrString = $upiLink;
        }
        $utr = (string)($res['utR_RRN'] ?? $res['utr'] ?? $res['rrn'] ?? $res['bank_rrn'] ?? '');

        Gatewayorder::where('id', $gatewayOrderId)->update([
            'remark' => $transactionId,
            'utr' => $utr,
        ]);

        if ($statusCode !== '1' && empty($transactionId) && empty($qrString)) {
            $msg = $res['message'] ?? $res['responseMessage'] ?? 'Failed to create ZigPay order';
            return response()->json(['status' => 'failure', 'message' => $msg]);
        }

        if ($mode === 'API') {
            return response()->json([
                'status' => 'success',
                'message' => $res['message'] ?? 'Order created successfully',
                'data' => [
                    'txnid' => $gatewayOrderId,
                    'order_token' => $refId,
                    'transaction_id' => $transactionId,
                    'qrString' => $qrString,
                    'upiLink' => $upiLink,
                    'status' => $gatewayStatus ?: 'pending',
                ],
            ]);
        }

        $qrCodeUrl = $qrString ? url('agent/add-money/v8/view-qrcode') . '?upi_string=' . urlencode($qrString) : '';
        return response()->json([
            'status' => 'success',
            'message' => $res['message'] ?? 'Order created successfully',
            'data' => [
                'txnid' => $gatewayOrderId,
                'order_token' => $refId,
                'transaction_id' => $transactionId,
                'qrString' => $qrString,
                'upiLink' => $upiLink,
                'qrCodeUrl' => $qrCodeUrl,
                'status' => $gatewayStatus ?: 'pending',
            ],
        ]);
    }

    public function viewQrcode(Request $request)
    {
        return response(QrCode::size(300)->generate($request->upi_string), 200)
            ->header('Content-Type', 'image/svg+xml');
    }

    public function callbackUrl(Request $request)
    {
        $ctime = now();
        $rawPayload = $request->all();
        Apiresponse::insertGetId([
            'message' => json_encode($rawPayload),
            'api_type' => 1,
            'created_at' => $ctime,
            'ip_address' => request()->ip(),
        ]);

        // ZigPay docs callback fields: status_id, amount, utr, client_id, message.
        $statusId = (int)($request->input('status_id') ?? 0);
        $clientId = (string)($request->input('client_id') ?? '');
        $amount = (float)($request->input('amount') ?? 0);
        $utr = (string)($request->input('utr') ?? '');
        $message = (string)($request->input('message') ?? '');
        $referenceId = (string)($request->input('order_id') ?? '');
        if ($statusId < 1 || $statusId > 3 || $clientId === '') {
            $decoded = json_decode((string)$request->getContent(), true);
            if (is_array($decoded)) {
                $statusId = $statusId ?: (int)($decoded['status_id'] ?? 0);
                $clientId = $clientId !== '' ? $clientId : (string)($decoded['client_id'] ?? $decoded['RefID'] ?? $decoded['ref_id'] ?? '');
                $amount = $amount > 0 ? $amount : (float)($decoded['amount'] ?? 0);
                $utr = $utr !== '' ? $utr : (string)($decoded['utr'] ?? $decoded['utR_RRN'] ?? $decoded['rrn'] ?? '');
                $message = $message !== '' ? $message : (string)($decoded['message'] ?? $decoded['responseMessage'] ?? '');
                $referenceId = $referenceId !== '' ? $referenceId : (string)($decoded['order_id'] ?? $decoded['txnID'] ?? $decoded['txnId'] ?? $decoded['transaction_id'] ?? '');
            }
        }

        if (empty($clientId) || $statusId < 1 || $statusId > 3) {
            Log::warning('ZigPay callback invalid payload', [
                'status_id' => $statusId,
                'client_id' => $clientId,
                'amount' => $amount,
                'payload' => $rawPayload,
            ]);
            return response()->json(['status' => 'failure', 'message' => 'Invalid callback payload'], 400);
        }
        return DB::transaction(function () use ($clientId, $statusId, $amount, $utr, $message, $referenceId, $ctime) {
            $gatewayOrder = Gatewayorder::where('order_token', $clientId)->lockForUpdate()->first();
            if (!$gatewayOrder) {
                $gatewayOrder = Gatewayorder::where('client_id', $clientId)->lockForUpdate()->first();
            }
            if (!$gatewayOrder) {
                $gatewayOrder = Gatewayorder::where('remark', $clientId)->lockForUpdate()->first();
            }
            if (!$gatewayOrder && is_numeric($clientId)) {
                $gatewayOrder = Gatewayorder::where('id', (int)$clientId)->lockForUpdate()->first();
            }
            if (!$gatewayOrder && $referenceId !== '') {
                $gatewayOrder = Gatewayorder::where('remark', $referenceId)->lockForUpdate()->first();
            }
            if (!$gatewayOrder) {
                return response()->json(['status' => 'failure', 'message' => 'Order not found'], 404);
            }

            // If callback omits amount, safely use created order amount.
            $txnAmount = $amount > 0 ? $amount : (float)$gatewayOrder->amount;

            if ($statusId === 2) {
                try {
                    $this->forwardMemberCallback(
                        $gatewayOrder->user_id,
                        'pending',
                        $gatewayOrder->client_id ?: $gatewayOrder->order_token,
                        $txnAmount,
                        $utr,
                        $gatewayOrder->id,
                        $ctime
                    );
                } catch (\Exception $e) {
                    Log::error('ZigPay member callback forward failed (pending)', ['error' => $e->getMessage()]);
                }
                return response()->json(['status' => 'success', 'message' => 'Pending callback accepted']);
            }

            if ($statusId === 3) {
                Gatewayorder::where('id', $gatewayOrder->id)->update(['status_id' => 2]);
                try {
                    $this->forwardMemberCallback(
                        $gatewayOrder->user_id,
                        'failed',
                        $gatewayOrder->client_id ?: $gatewayOrder->order_token,
                        $txnAmount,
                        $utr,
                        $gatewayOrder->id,
                        $ctime
                    );
                } catch (\Exception $e) {
                    Log::error('ZigPay member callback forward failed (failed)', ['error' => $e->getMessage()]);
                }
                return response()->json(['status' => 'success', 'message' => 'Failed callback accepted']);
            }

            // Success status flow
            if ((int)$gatewayOrder->status_id !== 3) {
                return response()->json(['status' => 'success', 'message' => 'Already processed']);
            }
            if (!empty($utr) && Report::where('txnid', $utr)->exists()) {
                return response()->json(['status' => 'failure', 'message' => 'Duplicate transaction']);
            }

            Gatewayorder::where('id', $gatewayOrder->id)->update(['status_id' => 9]);

            $user = User::find($gatewayOrder->user_id);
            if (!$user) {
                return response()->json(['status' => 'failure', 'message' => 'User not found']);
            }

            $opening_balance = $user->balance->aeps_balance ?? 0;
            $scheme_id = $user->scheme_id;
            $provider_id = $this->provider_id;

            $commissionLibrary = new GetcommissionLibrary();
            $commission = $commissionLibrary->get_commission($scheme_id, $provider_id, $txnAmount);

            $retailer = $commission['retailer'] ?? 0;
            $d = $commission['distributor'] ?? 0;
            $sd = $commission['sdistributor'] ?? 0;
            $st = $commission['sales_team'] ?? 0;
            $rf = $commission['referral'] ?? 0;
            $creditAmount = $txnAmount - $retailer;

            Balance::where('user_id', $user->id)->increment('aeps_balance', $creditAmount);
            $newBalance = Balance::where('user_id', $user->id)->value('aeps_balance');

            $reportId = Report::insertGetId([
                'number' => $user->mobile,
                'provider_id' => $provider_id,
                'amount' => $txnAmount,
                'api_id' => $this->api_id,
                'status_id' => 6,
                'created_at' => $ctime,
                'user_id' => $user->id,
                'profit' => '-' . $retailer,
                'mode' => $gatewayOrder->mode,
                'txnid' => $utr ?: $gatewayOrder->order_token,
                'ip_address' => $gatewayOrder->ip_address,
                'description' => 'Add Money via ZigPay' . ($message ? ' - ' . $message : ''),
                'opening_balance' => $opening_balance,
                'total_balance' => $newBalance,
                'credit_by' => $user->id,
                'wallet_type' => 2,
                'client_id' => $gatewayOrder->client_id ?? '',
            ]);

            if ($gatewayOrder->mode !== 'API') {
                Report::where('id', $reportId)->update(['client_id' => $reportId]);
            }

            Gatewayorder::where('id', $gatewayOrder->id)->update([
                'status_id' => 1,
                'report_id' => $reportId,
                'utr' => $utr,
            ]);

            $parentCommission = new Commission_increment();
            $parentCommission->parent_recharge_commission(
                $user->id,
                $user->mobile,
                $reportId,
                $provider_id,
                $txnAmount,
                $this->api_id,
                $retailer,
                $d,
                $sd,
                $st,
                $rf
            );

            if (!empty($gatewayOrder->callback_url)) {
                try {
                    $queryParams = [
                        'status' => 'credit',
                        'client_id' => $gatewayOrder->client_id,
                        'amount' => $txnAmount,
                        'utr' => $utr,
                        'txnid' => $gatewayOrder->id,
                    ];
                    $signatureString = http_build_query($queryParams);
                    $queryParams['signature'] = hash_hmac('sha256', $signatureString, $user->api_token);
                    $url = $gatewayOrder->callback_url . '?' . http_build_query($queryParams);
                    $response = Helpers::pay_curl_get($url);
                    Traceurl::insertGetId([
                        'user_id' => $user->id,
                        'url' => $url,
                        'number' => $user->mobile,
                        'response_message' => $response,
                        'created_at' => $ctime,
                    ]);
                } catch (\Exception $e) {
                    Log::error('ZigPay callback forward failed', ['error' => $e->getMessage()]);
                }
            }

            try {
                $this->forwardMemberCallback(
                    $user->id,
                    'credit',
                    $gatewayOrder->client_id ?: $gatewayOrder->order_token,
                    $txnAmount,
                    $utr,
                    $gatewayOrder->id,
                    $ctime
                );
            } catch (\Exception $e) {
                Log::error('ZigPay member callback forward failed (success)', ['error' => $e->getMessage()]);
            }

            return response()->json(['status' => 'success', 'message' => 'Transaction successful']);
        });
    }

    public function statusCheckApi(Request $request)
    {
        $rules = [
            'transaction_id' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }

        $token = $this->generateToken();
        if (empty($token)) {
            return response()->json(['status' => false, 'message' => 'Unable to generate token']);
        }

        $url = $this->base_url . '/api/v1/check/status';
        $payload = [
            'RefId' => $request->transaction_id,
            'Service_Id' => 1,
        ];
        $headers = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'accept: */*',
        ];

        $response = Helpers::pay_curl_post($url, $headers, json_encode($payload), 'POST');
        Apiresponse::insertGetId([
            'message' => $response,
            'api_type' => 1,
            'created_at' => now(),
            'ip_address' => request()->ip(),
        ]);

        $res = json_decode($response, true);
        if (!is_array($res)) {
            return response()->json(['status' => false, 'message' => 'Invalid response received.']);
        }

        return response()->json([
            'status' => true,
            'message' => $res['message'] ?? $res['responseMessage'] ?? 'Status retrieved',
            'data' => [
                'transaction_status' => strtolower((string)($res['status'] ?? '')),
                'utr' => $res['utR_RRN'] ?? '',
                'reference_id' => $res['client_RefNo'] ?? '',
                'payment_mode' => 'UPI',
                'raw' => $res,
            ],
        ]);
    }

    public function statusEnquiryApi(Request $request)
    {
        $rules = [
            'client_id' => 'required|exists:gatewayorders,client_id',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }

        $gatewayOrder = Gatewayorder::where('client_id', $request->client_id)
            ->where('user_id', Auth::id())
            ->orderBy('id', 'DESC')
            ->first();

        if (!$gatewayOrder) {
            return response()->json(['status' => false, 'message' => 'No matching report found!']);
        }

        $report = Report::find($gatewayOrder->report_id);
        if (!$report) {
            return response()->json(['status' => false, 'message' => 'No matching report found!']);
        }

        return response()->json([
            'status' => true,
            'message' => 'Transaction record found successfully!',
            'data' => [
                'client_id' => $request->client_id,
                'report_id' => $report->id,
                'amount' => $report->amount,
                'utr' => $report->txnid,
                'status' => 'credit',
            ],
        ]);
    }
}
