<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
use App\Library\QuickPayCashLibrary;

class QuickPayCashController extends Controller
{
    private $api_id;
    private $provider_id;
    private $min_amount;
    private $max_amount;
    private $base_url;
    private $merchantId;
    private $merchantKey;
    private $qpcLibrary;

    public function __construct()
    {
        $this->api_id = 16;
        $this->provider_id = 340;
        $this->qpcLibrary = new QuickPayCashLibrary();

        $credentials = json_decode(optional(Api::find($this->api_id))->credentials);
        $this->base_url = rtrim($credentials->base_url ?? 'https://portalquickpaycash.com', '/');
        $this->merchantId = $credentials->merchant_id ?? '';
        $this->merchantKey = $credentials->merchant_key ?? '';

        $provider = Provider::find($this->provider_id);
        $this->min_amount = 100;
        $this->max_amount = isset($provider->max_amount) ? (int)$provider->max_amount : 10000;
    }

    private function absoluteHttpsUrl(string $path): string
    {
        return QuickPayCashLibrary::publicUrl($path);
    }

    private function creditGatewayOrder(Gatewayorder $gatewayOrder, float $txnAmount, string $utr, string $orderId, $ctime, string $creditMode = 'Call-back'): array
    {
        if ((int)$gatewayOrder->status_id === 1) {
            return ['success' => true, 'report_id' => $gatewayOrder->report_id, 'already' => true];
        }
        if ((int)$gatewayOrder->status_id === 9) {
            return ['success' => false, 'message' => 'Already processing'];
        }
        if ((int)$gatewayOrder->status_id !== 3) {
            return ['success' => false, 'message' => 'Already processed'];
        }

        if (!empty($utr) && Report::where('txnid', $utr)->exists()) {
            return ['success' => false, 'message' => 'Duplicate transaction'];
        }

        Gatewayorder::where('id', $gatewayOrder->id)->update(['status_id' => 9]);

        $user = User::find($gatewayOrder->user_id);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        $opening_balance = $user->balance->aeps_balance ?? 0;
        $commissionLibrary = new GetcommissionLibrary();
        $commission = $commissionLibrary->get_commission($user->scheme_id, $this->provider_id, $txnAmount);
        $retailer = $commission['retailer'] ?? 0;
        $d = $commission['distributor'] ?? 0;
        $sd = $commission['sdistributor'] ?? 0;
        $st = $commission['sales_team'] ?? 0;
        $rf = $commission['referral'] ?? 0;
        $creditAmount = $txnAmount - $retailer;

        Balance::where('user_id', $user->id)->increment('aeps_balance', $creditAmount);
        $newBalance = Balance::where('user_id', $user->id)->value('aeps_balance');

        $txnIdForReport = $utr ?: ($orderId ?: $gatewayOrder->order_token);
        $description = 'Add Money via Quick Pay Cash';
        if ($creditMode !== 'Call-back') {
            $description .= ' (' . $creditMode . ')';
        }

        $reportId = Report::insertGetId([
            'number' => $user->mobile,
            'provider_id' => $this->provider_id,
            'amount' => $txnAmount,
            'api_id' => $this->api_id,
            'status_id' => 6,
            'created_at' => $ctime,
            'user_id' => $user->id,
            'profit' => '-' . $retailer,
            'mode' => $gatewayOrder->mode,
            'txnid' => $txnIdForReport,
            'ip_address' => $gatewayOrder->ip_address,
            'description' => $description,
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
            'remark' => $utr ?: $orderId,
        ]);

        try {
            $parentCommission = new Commission_increment();
            $parentCommission->parent_recharge_commission(
                $user->id,
                $user->mobile,
                $reportId,
                $this->provider_id,
                $txnAmount,
                $this->api_id,
                $retailer,
                $d,
                $sd,
                $st,
                $rf
            );
        } catch (\Exception $e) {
            Log::error('QPC payin commission failed', ['error' => $e->getMessage()]);
        }

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
                $cbUrl = $gatewayOrder->callback_url . '?' . http_build_query($queryParams);
                $cbResponse = Helpers::pay_curl_get($cbUrl);
                Traceurl::insertGetId([
                    'user_id' => $user->id,
                    'url' => $cbUrl,
                    'number' => $user->mobile,
                    'response_message' => $cbResponse,
                    'created_at' => $ctime,
                ]);
            } catch (\Exception $e) {
                Log::error('QPC merchant callback failed', ['error' => $e->getMessage()]);
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
            Log::error('QPC member callback failed', ['error' => $e->getMessage()]);
        }

        return ['success' => true, 'report_id' => $reportId, 'utr' => $txnIdForReport];
    }

    public function syncPendingOrderFromQpc(Gatewayorder $gatewayOrder): void
    {
        if ((int)$gatewayOrder->status_id !== 3 || empty($gatewayOrder->order_token)) {
            return;
        }

        $remote = $this->qpcLibrary->getPayinStatus($gatewayOrder->order_token);
        $parsed = $this->qpcLibrary->parsePayinStatusResponse($remote);
        $status = $parsed['status'];

        if ($status === 'SUCCESS') {
            DB::transaction(function () use ($gatewayOrder, $parsed) {
                $locked = Gatewayorder::where('id', $gatewayOrder->id)->lockForUpdate()->first();
                if (!$locked || (int)$locked->status_id !== 3) {
                    return;
                }
                $txnAmount = $parsed['amount'] > 0 ? $parsed['amount'] : (float)$locked->amount;
                $this->creditGatewayOrder(
                    $locked,
                    $txnAmount,
                    $parsed['utr'],
                    $parsed['orderId'],
                    now(),
                    'Status sync'
                );
            });
            return;
        }

        if (in_array($status, ['FAILED', 'CANCELLED', 'REFUNDED'], true)) {
            Gatewayorder::where('id', $gatewayOrder->id)
                ->where('status_id', 3)
                ->update(['status_id' => 2, 'remark' => $parsed['utr'] ?: $parsed['orderId']]);
        }
    }

    private function qpcRequest(string $url, string $body): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'accept: application/json'],
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 60,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        return [
            'body' => $response === false ? '' : $response,
            'http_code' => $httpCode,
            'error' => $curlError,
        ];
    }

    private function logQpcResponse(string $message, string $requestMessage = '', ?string $responseType = null): void
    {
        Apiresponse::insertGetId([
            'message' => $message,
            'api_type' => $this->api_id,
            'response_type' => $responseType,
            'request_message' => $requestMessage,
            'created_at' => now(),
            'ip_address' => request()->ip(),
        ]);
    }

    private function qpcFailureMessage(array $result, string $action): string
    {
        if (!empty($result['error'])) {
            return 'Unable to reach Quick Pay Cash (' . $action . '): ' . $result['error'];
        }

        $body = trim((string)($result['body'] ?? ''));
        $decoded = json_decode($body, true);

        if (is_array($decoded)) {
            return (string)($decoded['message'] ?? 'Quick Pay Cash ' . $action . ' failed.');
        }

        $httpCode = (int)($result['http_code'] ?? 0);
        if ($httpCode >= 500) {
            return 'Quick Pay Cash service error (HTTP ' . $httpCode . '). Contact QPC support.';
        }

        return $body !== ''
            ? 'Invalid response from Quick Pay Cash during ' . $action . '.'
            : 'Empty response from Quick Pay Cash during ' . $action . '.';
    }

    private function extractPayinData(array $res): array
    {
        $data = is_array($res['data'] ?? null) ? $res['data'] : $res;
        $deepLink = is_array($data['deepLink'] ?? null) ? $data['deepLink'] : [];

        $paymentUrl = (string)($data['paymentUrl'] ?? $data['paymentPageUrl'] ?? '');
        $qrCodeUrl = (string)($data['qrCodeUrl'] ?? '');
        $orderId = (string)($data['orderId'] ?? $data['platOrderNo'] ?? '');
        $qrString = (string)($deepLink['upi_intent'] ?? '');
        $paymentLink = (string)($data['paymentLink'] ?? '');

        if ($qrString === '' && str_starts_with($paymentLink, 'upi://')) {
            $qrString = $paymentLink;
        }
        if ($qrString === '' && $paymentUrl !== '' && !str_starts_with($paymentUrl, 'http')) {
            $qrString = $paymentUrl;
        }

        return [
            'paymentUrl' => $paymentUrl,
            'qrCodeUrl' => $qrCodeUrl,
            'orderId' => $orderId,
            'qrString' => $qrString,
            'status' => strtolower((string)($data['status'] ?? $data['orderStatus'] ?? 'pending')),
        ];
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
        if (($activeService['status_id'] ?? 0) == 1) {
            return view('agent.add-money.quickpaycash')->with([
                'page_title' => 'Payin 9',
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

        $user = Auth::user();
        $name = trim((string)$user->name);
        $email = trim((string)$user->email);
        $mobile = preg_replace('/\D/', '', (string)$user->mobile);

        if ($name === '' || $email === '' || strlen($mobile) !== 10) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Please update your profile with valid name, email, and 10-digit mobile number before generating QR.',
            ]);
        }

        return $this->createOrderMiddle(
            $request->amount,
            Auth::id(),
            'WEB',
            '',
            '',
            $name,
            $email,
            $mobile
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
        if (($activeService['status_id'] ?? 0) != 1) {
            return response()->json(['status' => 'failure', 'message' => 'Service not active!']);
        }

        if (empty($this->merchantId) || empty($this->merchantKey)) {
            return response()->json(['status' => 'failure', 'message' => ' credentials not configured']);
        }

        $ctime = now();
        $member = Member::where('user_id', $user_id)->first();
        $payoutCallbackUrl = $member->payoutcallbackurl ?? '';
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
            'payoutcallbackurl' => $payoutCallbackUrl,
            'client_id' => $client_id,
            'mode' => $mode,
            'order_token' => 'QPCTMP' . time(),
        ]);

        $merchantOrderNo = $this->qpcLibrary->buildPayinOrderNo($gatewayOrderId);
        $amountStr = number_format((float)$amount, 2, '.', '');

        Gatewayorder::where('id', $gatewayOrderId)->update([
            'order_token' => $merchantOrderNo,
            'client_id' => ($mode === 'WEB' || empty($client_id)) ? $merchantOrderNo : $client_id,
        ]);

        $payload = [
            'merchantId' => $this->merchantId,
            'merchantOrderNo' => $merchantOrderNo,
            'amount' => $amountStr,
            'currency' => 'INR',
            'payerName' => (string)$name,
            'payerEmail' => (string)$email,
            'payerMobile' => (string)$mobile,
            'description' => 'Add Money - ' . $gatewayOrderId,
            'returnUrl' => $this->absoluteHttpsUrl('agent/add-money/v9/welcome'),
            'callbackUrl' => $this->absoluteHttpsUrl('api/call-back/qpc-payin'),
            'signature' => $this->qpcLibrary->signPayinCreate($merchantOrderNo, $amountStr),
        ];

        $url = $this->base_url . '/api/payin/create';
        $result = $this->qpcRequest($url, json_encode($payload));
        $this->logQpcResponse($result['body'], $url . '?' . json_encode($payload), 'payin_create');

        $res = json_decode($result['body'], true);
        if (!is_array($res)) {
            return response()->json([
                'status' => 'failure',
                'message' => $this->qpcFailureMessage($result, 'create order'),
            ]);
        }

        $topStatus = strtoupper((string)($res['status'] ?? ''));
        if ($topStatus === 'FAILED') {
            return response()->json([
                'status' => 'failure',
                'message' => $res['message'] ?? 'Failed to create QPC order',
            ]);
        }

        $payin = $this->extractPayinData($res);
        if (empty($payin['paymentUrl']) && empty($payin['qrString']) && empty($payin['qrCodeUrl'])) {
            return response()->json([
                'status' => 'failure',
                'message' => $res['message'] ?? 'Failed to create QPC order',
            ]);
        }

        Gatewayorder::where('id', $gatewayOrderId)->update([
            'remark' => $payin['orderId'],
        ]);

        $responseData = [
            'txnid' => $gatewayOrderId,
            'order_token' => $merchantOrderNo,
            'transaction_id' => $payin['orderId'],
            'qrString' => $payin['qrString'],
            'paymentUrl' => $payin['paymentUrl'],
            'qrCodeUrl' => $payin['qrCodeUrl'],
            'status' => $payin['status'] ?: 'pending',
        ];

        if ($mode !== 'API') {
            if ($payin['qrCodeUrl'] !== '' && str_starts_with($payin['qrCodeUrl'], 'http')) {
                $responseData['qrCodeUrl'] = $payin['qrCodeUrl'];
            } elseif ($payin['qrString'] !== '') {
                $responseData['qrCodeUrl'] = url('agent/add-money/v9/view-qrcode') . '?upi_string=' . urlencode($payin['qrString']);
            }
            if ($payin['paymentUrl'] !== '') {
                $responseData['upiLink'] = $payin['paymentUrl'];
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => $res['message'] ?? 'Order created successfully',
            'data' => $responseData,
        ]);
    }

    public function viewQrcode(Request $request)
    {
        return response(QrCode::size(300)->generate($request->upi_string), 200)
            ->header('Content-Type', 'image/svg+xml');
    }

    public function webOrderStatus(Request $request)
    {
        $validator = Validator::make($request->all(), ['txnid' => 'required|integer']);
        if ($validator->fails()) {
            return response()->json(['ok' => false, 'message' => $validator->messages()->first()]);
        }

        $gatewayOrder = Gatewayorder::where('id', (int)$request->txnid)
            ->where('user_id', Auth::id())
            ->where('api_id', $this->api_id)
            ->first();

        if (!$gatewayOrder) {
            return response()->json(['ok' => false, 'message' => 'Order not found']);
        }

        $sid = (int)$gatewayOrder->status_id;
        if ($sid === 1) {
            $utr = (string)($gatewayOrder->remark ?? '');
            if ($utr === '' && !empty($gatewayOrder->report_id)) {
                $utr = (string)(Report::where('id', $gatewayOrder->report_id)->value('txnid') ?? '');
            }
            return response()->json([
                'ok' => true,
                'payment_status' => 'success',
                'data' => ['utr' => $utr, 'amount' => (float)$gatewayOrder->amount],
            ]);
        }
        if ($sid === 2) {
            return response()->json([
                'ok' => true,
                'payment_status' => 'failed',
                'message' => 'Payment failed or was declined.',
            ]);
        }

        $this->syncPendingOrderFromQpc($gatewayOrder);
        $gatewayOrder->refresh();

        if ((int)$gatewayOrder->status_id === 1) {
            $utr = (string)($gatewayOrder->remark ?? '');
            if ($utr === '' && !empty($gatewayOrder->report_id)) {
                $utr = (string)(Report::where('id', $gatewayOrder->report_id)->value('txnid') ?? '');
            }
            return response()->json([
                'ok' => true,
                'payment_status' => 'success',
                'data' => ['utr' => $utr, 'amount' => (float)$gatewayOrder->amount],
            ]);
        }
        if ((int)$gatewayOrder->status_id === 2) {
            return response()->json([
                'ok' => true,
                'payment_status' => 'failed',
                'message' => 'Payment failed or was declined.',
            ]);
        }

        return response()->json(['ok' => true, 'payment_status' => 'pending']);
    }

    public function payinCallback(Request $request)
    {
        $ctime = now();
        $payload = QuickPayCashLibrary::parseIncomingCallback($request);
        $audit = QuickPayCashLibrary::buildCallbackAudit($request, $payload);

        Log::info('QPC payin callback received', $audit);

        Apiresponse::insertGetId([
            'message' => json_encode($audit),
            'api_type' => $this->api_id,
            'response_type' => 'call_back',
            'request_message' => substr((string)$request->getContent(), 0, 65000),
            'ip_address' => $request->ip(),
            'created_at' => $ctime,
        ]);

        $merchantOrderNo = (string)($payload['merchantOrderNo'] ?? '');
        if ($merchantOrderNo === '' && QuickPayCashLibrary::isEffectivelyEmptyPayload($payload)) {
            return response()->json([
                'received' => true,
                'status' => true,
                'message' => 'Empty callback acknowledged',
            ]);
        }

        $receivedSignature = (string)($payload['signature'] ?? '');
        $signatureValid = $receivedSignature !== '' && $this->qpcLibrary->verifyPayinCallback($payload, $receivedSignature);
        $verifiedViaApi = false;

        if (!$signatureValid && $merchantOrderNo !== '') {
            $confirmed = $this->qpcLibrary->confirmPayinSuccessFromApi($merchantOrderNo);
            if ($confirmed) {
                $verifiedViaApi = true;
                $payload = array_merge($payload, [
                    'status' => 'SUCCESS',
                    'orderStatus' => 'SUCCESS',
                    'utr' => (string)($confirmed['utr'] ?? $payload['utr'] ?? ''),
                    'amount' => (float)($confirmed['amount'] ?? $payload['amount'] ?? 0),
                    'orderId' => (string)($confirmed['orderId'] ?? $payload['orderId'] ?? ''),
                ]);
            }
        }

        if (!$signatureValid && !$verifiedViaApi) {
            Log::error('QPC payin callback: invalid signature', ['payload' => $payload]);
            return response()->json([
                'received' => false,
                'status' => false,
                'message' => 'Invalid signature',
            ], 400);
        }

        $status = strtoupper((string)($payload['status'] ?? $payload['orderStatus'] ?? ''));
        $amount = (float)($payload['amount'] ?? 0);
        $utr = (string)($payload['utr'] ?? '');
        $orderId = (string)($payload['orderId'] ?? $payload['platOrderNo'] ?? '');

        if ($merchantOrderNo === '') {
            return response()->json([
                'received' => false,
                'status' => false,
                'message' => 'Missing merchantOrderNo',
            ], 400);
        }

        if (in_array($status, ['PENDING', 'PROCESSING', ''], true)) {
            if ($status === '' && !$verifiedViaApi) {
                $pendingOrder = Gatewayorder::where(function ($query) use ($merchantOrderNo) {
                    $query->where('order_token', $merchantOrderNo)
                        ->orWhere('client_id', $merchantOrderNo);
                })->where('api_id', $this->api_id)->first();
                if ($pendingOrder) {
                    $this->syncPendingOrderFromQpc($pendingOrder);
                }
            }
            return response()->json([
                'received' => true,
                'status' => true,
                'message' => 'Pending accepted',
            ]);
        }

        if (in_array($status, ['FAILED', 'CANCELLED', 'REFUNDED'], true)) {
            return DB::transaction(function () use ($merchantOrderNo, $ctime, $amount, $utr) {
                $gatewayOrder = Gatewayorder::where(function ($query) use ($merchantOrderNo) {
                    $query->where('order_token', $merchantOrderNo)
                        ->orWhere('client_id', $merchantOrderNo);
                })->where('api_id', $this->api_id)->lockForUpdate()->first();

                if ($gatewayOrder && (int)$gatewayOrder->status_id === 3) {
                    Gatewayorder::where('id', $gatewayOrder->id)->update(['status_id' => 2, 'remark' => $utr]);
                    try {
                        $this->forwardMemberCallback(
                            $gatewayOrder->user_id,
                            'failed',
                            $gatewayOrder->client_id ?: $merchantOrderNo,
                            $amount > 0 ? $amount : (float)$gatewayOrder->amount,
                            $utr,
                            $gatewayOrder->id,
                            $ctime
                        );
                    } catch (\Exception $e) {
                        Log::error('QPC payin failed callback forward error', ['error' => $e->getMessage()]);
                    }
                }
                return response()->json([
                    'received' => true,
                    'status' => true,
                    'message' => 'Failed callback accepted',
                ]);
            });
        }

        if ($status !== 'SUCCESS') {
            return response()->json([
                'received' => true,
                'status' => true,
                'message' => 'Status ignored: ' . $status,
            ]);
        }

        return DB::transaction(function () use ($merchantOrderNo, $amount, $utr, $orderId, $ctime, $verifiedViaApi) {
            $gatewayOrder = Gatewayorder::where(function ($query) use ($merchantOrderNo) {
                $query->where('order_token', $merchantOrderNo)
                    ->orWhere('client_id', $merchantOrderNo);
            })->where('api_id', $this->api_id)->lockForUpdate()->first();

            if (!$gatewayOrder) {
                return response()->json([
                    'received' => false,
                    'status' => false,
                    'message' => 'Order not found',
                ], 404);
            }

            $txnAmount = $amount > 0 ? $amount : (float)$gatewayOrder->amount;
            $creditMode = $verifiedViaApi ? 'Status-sync' : 'Call-back';
            $result = $this->creditGatewayOrder($gatewayOrder, $txnAmount, $utr, $orderId, $ctime, $creditMode);

            if ($result['already'] ?? false) {
                return response()->json([
                    'received' => true,
                    'status' => true,
                    'message' => 'Already processed',
                    'report_id' => $result['report_id'] ?? null,
                ]);
            }

            if (!($result['success'] ?? false)) {
                return response()->json([
                    'received' => false,
                    'status' => false,
                    'message' => $result['message'] ?? 'Unable to process payment',
                ], 400);
            }

            return response()->json([
                'received' => true,
                'status' => true,
                'message' => 'Transaction successful',
                'report_id' => $result['report_id'] ?? null,
                'utr' => $result['utr'] ?? $utr,
            ]);
        });
    }

    public function statusEnquiryApi(Request $request)
    {
        $validator = Validator::make($request->all(), ['client_id' => 'required']);
        if ($validator->fails()) {
            return response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }

        $gatewayOrder = Gatewayorder::where('client_id', $request->client_id)
            ->where('user_id', Auth::id())
            ->where('api_id', $this->api_id)
            ->orderBy('id', 'DESC')
            ->first();

        if (!$gatewayOrder) {
            $gatewayOrder = Gatewayorder::where('order_token', $request->client_id)
                ->where('user_id', Auth::id())
                ->where('api_id', $this->api_id)
                ->orderBy('id', 'DESC')
                ->first();
        }

        if (!$gatewayOrder) {
            return response()->json(['status' => false, 'message' => 'No matching order found!']);
        }

        if ((int)$gatewayOrder->status_id === 1 && $gatewayOrder->report_id) {
            $report = Report::find($gatewayOrder->report_id);
            return response()->json([
                'status' => true,
                'message' => 'Transaction record found successfully!',
                'data' => [
                    'client_id' => $request->client_id,
                    'report_id' => $report->id ?? null,
                    'amount' => $report->amount ?? $gatewayOrder->amount,
                    'utr' => $report->txnid ?? '',
                    'status' => 'credit',
                ],
            ]);
        }

        if ((int)$gatewayOrder->status_id === 2) {
            return response()->json([
                'status' => true,
                'message' => 'Transaction failed',
                'data' => [
                    'client_id' => $request->client_id,
                    'status' => 'failed',
                ],
            ]);
        }

        $this->syncPendingOrderFromQpc($gatewayOrder);
        $gatewayOrder->refresh();

        if ((int)$gatewayOrder->status_id === 1 && $gatewayOrder->report_id) {
            $report = Report::find($gatewayOrder->report_id);
            return response()->json([
                'status' => true,
                'message' => 'Transaction record found successfully!',
                'data' => [
                    'client_id' => $request->client_id,
                    'report_id' => $report->id ?? null,
                    'amount' => $report->amount ?? $gatewayOrder->amount,
                    'utr' => $report->txnid ?? '',
                    'status' => 'credit',
                ],
            ]);
        }

        $remote = $this->qpcLibrary->getPayinStatus($gatewayOrder->order_token);
        $parsed = $this->qpcLibrary->parsePayinStatusResponse($remote);

        return response()->json([
            'status' => true,
            'message' => 'Transaction status retrieved',
            'data' => [
                'client_id' => $request->client_id,
                'status' => $parsed['status'] === 'SUCCESS' ? 'credit' : 'pending',
                'utr' => $parsed['utr'] ?? '',
                'amount' => $gatewayOrder->amount,
            ],
        ]);
    }
}
