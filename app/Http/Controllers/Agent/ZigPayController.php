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
            Log::error('ZigPay generateToken: missing credentials', [
                'mid_empty' => empty($this->mid),
                'email_empty' => empty($this->email),
                'secretkey_empty' => empty($this->secretkey),
            ]);
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

        Log::info('ZigPay generateToken: requesting token', ['url' => $url]);
        $response = Helpers::pay_curl_post($url, $header, json_encode($parameters), 'POST');
        Apiresponse::insertGetId([
            'message' => $response,
            'api_type' => 1,
            'created_at' => now(),
            'ip_address' => request()->ip(),
        ]);

        $res = json_decode($response, true);
        $token = $res['token'] ?? '';
        Log::info('ZigPay generateToken: result', ['token_received' => !empty($token)]);
        return $token;
    }

    private function forwardMemberCallback($userId, $status, $clientId, $amount, $utr, $txnid, $ctime)
    {
        $member = Member::where('user_id', $userId)->first();
        if (empty($member->call_back_url)) {
            Log::info('ZigPay forwardMemberCallback: no callback URL for user', ['user_id' => $userId]);
            return;
        }

        $user = User::find($userId);
        if (!$user || empty($user->api_token)) {
            Log::warning('ZigPay forwardMemberCallback: user not found or no api_token', ['user_id' => $userId]);
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

        Log::info('ZigPay forwardMemberCallback: firing', [
            'user_id' => $userId,
            'status' => $status,
            'url' => $url,
        ]);

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

        // -----------------------------------------------------------------------
        // For WEB orders, client_id is empty at this point.
        // We set client_id = order_token (refId) so the callback lookup always works
        // regardless of whether ZigPay sends back the order_token in the client_id field.
        // -----------------------------------------------------------------------
        if ($mode === 'WEB' || empty($client_id)) {
            Gatewayorder::where('id', $gatewayOrderId)->update(['client_id' => $refId]);
            Log::info('ZigPay createOrder: WEB order – set client_id = order_token', [
                'gateway_order_id' => $gatewayOrderId,
                'ref_id' => $refId,
            ]);
        }

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

        Log::info('ZigPay createOrder: sending payload', [
            'url' => $url,
            'payload' => $payload,
            'gateway_order_id' => $gatewayOrderId,
            'mode' => $mode,
        ]);

        $response = Helpers::pay_curl_post($url, $header, json_encode($payload), 'POST');
        Apiresponse::insertGetId([
            'message' => $response,
            'api_type' => 1,
            'created_at' => now(),
            'ip_address' => request()->ip(),
        ]);

        $res = json_decode($response, true);
        Log::info('ZigPay createOrder: raw response', [
            'gateway_order_id' => $gatewayOrderId,
            'response' => $res,
        ]);

        if (!is_array($res)) {
            Log::error('ZigPay createOrder: non-array response', ['raw' => $response]);
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
                'message' => 'MID minimum is Rs. ' . $detectedMin . '. Please enter Rs. ' . $detectedMin . ' or above.',
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
        ]);

        if ($statusCode !== '1' && empty($transactionId) && empty($qrString)) {
            $msg = $res['message'] ?? $res['responseMessage'] ?? 'Failed to create ZigPay order';
            Log::warning('ZigPay createOrder: order creation failed at provider', [
                'gateway_order_id' => $gatewayOrderId,
                'status_code' => $statusCode,
                'message' => $msg,
            ]);
            return response()->json(['status' => 'failure', 'message' => $msg]);
        }

        Log::info('ZigPay createOrder: order created successfully', [
            'gateway_order_id' => $gatewayOrderId,
            'transaction_id' => $transactionId,
            'has_qr' => !empty($qrString),
            'mode' => $mode,
        ]);

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

        Log::info('ZigPay callbackUrl: RECEIVED raw payload', [
            'payload' => $rawPayload,
            'ip' => request()->ip(),
            'content_type' => $request->header('Content-Type'),
        ]);

        Apiresponse::insertGetId([
            'message' => json_encode($rawPayload),
            'api_type' => 1,
            'created_at' => $ctime,
            'ip_address' => request()->ip(),
        ]);

        // -----------------------------------------------------------------------
        // STEP 1: Parse callback fields from form-data or JSON body
        // ZigPay callback format: {"amount":"101.00","message":"...","client_id":"ZIG181...","utr":"...","status_id":1}
        // client_id here is the RefID we sent (= order_token in gatewayorders table)
        // -----------------------------------------------------------------------
        $statusId   = (int)($request->input('status_id') ?? 0);
        $clientId   = (string)($request->input('client_id') ?? '');
        $amount     = (float)($request->input('amount') ?? 0);
        $utr        = (string)($request->input('utr') ?? '');
        $message    = (string)($request->input('message') ?? '');
        $referenceId = (string)($request->input('order_id') ?? '');

        // Fallback: try JSON body if form fields are empty
        if ($statusId < 1 || $statusId > 3 || $clientId === '') {
            $decoded = json_decode((string)$request->getContent(), true);
            if (is_array($decoded)) {
                Log::info('ZigPay callbackUrl: falling back to raw JSON body', ['decoded' => $decoded]);
                $statusId    = $statusId    ?: (int)($decoded['status_id'] ?? 0);
                $clientId    = $clientId !== '' ? $clientId : (string)($decoded['client_id'] ?? $decoded['RefID'] ?? $decoded['ref_id'] ?? '');
                $amount      = $amount > 0   ? $amount    : (float)($decoded['amount'] ?? 0);
                $utr         = $utr !== ''   ? $utr       : (string)($decoded['utr'] ?? $decoded['utR_RRN'] ?? $decoded['rrn'] ?? '');
                $message     = $message !== '' ? $message : (string)($decoded['message'] ?? $decoded['responseMessage'] ?? '');
                $referenceId = $referenceId !== '' ? $referenceId : (string)($decoded['order_id'] ?? $decoded['txnID'] ?? $decoded['txnId'] ?? $decoded['transaction_id'] ?? '');
            }
        }

        Log::info('ZigPay callbackUrl: parsed fields', [
            'status_id'   => $statusId,
            'client_id'   => $clientId,
            'amount'      => $amount,
            'utr'         => $utr,
            'message'     => $message,
            'reference_id' => $referenceId,
        ]);

        // -----------------------------------------------------------------------
        // STEP 2: Validate required fields
        // -----------------------------------------------------------------------
        if (empty($clientId) || $statusId < 1 || $statusId > 3) {
            Log::error('ZigPay callbackUrl: INVALID payload – missing client_id or bad status_id', [
                'status_id' => $statusId,
                'client_id' => $clientId,
                'amount'    => $amount,
                'payload'   => $rawPayload,
            ]);
            return response()->json(['status' => 'failure', 'message' => 'Invalid callback payload'], 400);
        }

        return DB::transaction(function () use ($clientId, $statusId, $amount, $utr, $message, $referenceId, $ctime) {

            // ------------------------------------------------------------------
            // STEP 3: Find the gateway order
            //
            // ZigPay sends back our RefID (e.g. "ZIG1811776584794") in client_id.
            // That RefID is stored in BOTH:
            //   - gatewayorders.order_token  (always set)
            //   - gatewayorders.client_id    (set for API orders & WEB orders via our fix above)
            //
            // We try client_id first (covers API + WEB after the fix),
            // then fall back to order_token (safety net for any old WEB orders).
            // ------------------------------------------------------------------
            Log::info('ZigPay callbackUrl: looking up gateway order', ['client_id' => $clientId]);

            $gatewayOrder = Gatewayorder::where('client_id', $clientId)
                ->lockForUpdate()
                ->first();

            if (!$gatewayOrder) {
                Log::warning('ZigPay callbackUrl: not found by client_id, trying order_token', ['client_id' => $clientId]);
                $gatewayOrder = Gatewayorder::where('order_token', $clientId)
                    ->lockForUpdate()
                    ->first();
            }

            if (!$gatewayOrder) {
                Log::error('ZigPay callbackUrl: ORDER NOT FOUND in gatewayorders', [
                    'searched_client_id'  => $clientId,
                    'searched_order_token' => $clientId,
                ]);
                return response()->json(['status' => 'failure', 'message' => 'Order not found'], 404);
            }

            Log::info('ZigPay callbackUrl: gateway order found', [
                'gateway_order_id'     => $gatewayOrder->id,
                'gateway_order_status' => $gatewayOrder->status_id,
                'mode'                 => $gatewayOrder->mode,
                'user_id'              => $gatewayOrder->user_id,
                'stored_client_id'     => $gatewayOrder->client_id,
                'stored_order_token'   => $gatewayOrder->order_token,
            ]);

            // Use stored amount as fallback if callback omits it
            $txnAmount = $amount > 0 ? $amount : (float)$gatewayOrder->amount;
            Log::info('ZigPay callbackUrl: resolved txn amount', [
                'callback_amount' => $amount,
                'order_amount'    => $gatewayOrder->amount,
                'resolved_amount' => $txnAmount,
            ]);

            // ------------------------------------------------------------------
            // STEP 4: Handle PENDING status (status_id = 2)
            // ------------------------------------------------------------------
            if ($statusId === 2) {
                Log::info('ZigPay callbackUrl: STATUS = PENDING', ['gateway_order_id' => $gatewayOrder->id]);
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
                    Log::error('ZigPay callbackUrl: member callback forward failed (pending)', ['error' => $e->getMessage()]);
                }
                return response()->json(['status' => 'success', 'message' => 'Pending callback accepted']);
            }

            // ------------------------------------------------------------------
            // STEP 5: Handle FAILED status (status_id = 3)
            // ------------------------------------------------------------------
            if ($statusId === 3) {
                Log::info('ZigPay callbackUrl: STATUS = FAILED', ['gateway_order_id' => $gatewayOrder->id]);
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
                    Log::error('ZigPay callbackUrl: member callback forward failed (failed)', ['error' => $e->getMessage()]);
                }
                return response()->json(['status' => 'success', 'message' => 'Failed callback accepted']);
            }

            // ------------------------------------------------------------------
            // STEP 6: Handle SUCCESS status (status_id = 1)
            // ------------------------------------------------------------------
            Log::info('ZigPay callbackUrl: STATUS = SUCCESS – beginning credit flow', [
                'gateway_order_id'     => $gatewayOrder->id,
                'current_order_status' => $gatewayOrder->status_id,
            ]);

            // Guard: already processed (status_id 1 = success, 9 = processing)
            if ((int)$gatewayOrder->status_id === 1) {
                Log::warning('ZigPay callbackUrl: order already CREDITED – skipping duplicate', [
                    'gateway_order_id' => $gatewayOrder->id,
                ]);
                return response()->json(['status' => 'success', 'message' => 'Already processed']);
            }

            if ((int)$gatewayOrder->status_id === 9) {
                Log::warning('ZigPay callbackUrl: order in PROCESSING state (9) – possible duplicate callback in flight', [
                    'gateway_order_id' => $gatewayOrder->id,
                ]);
                return response()->json(['status' => 'success', 'message' => 'Already processing']);
            }

            // Guard: order must be in pending state (3) to process
            if ((int)$gatewayOrder->status_id !== 3) {
                Log::error('ZigPay callbackUrl: UNEXPECTED order status – cannot process credit', [
                    'gateway_order_id'     => $gatewayOrder->id,
                    'current_order_status' => $gatewayOrder->status_id,
                    'expected_status'      => 3,
                ]);
                return response()->json(['status' => 'success', 'message' => 'Already processed']);
            }

            // Guard: duplicate UTR
            if (!empty($utr) && Report::where('txnid', $utr)->exists()) {
                Log::error('ZigPay callbackUrl: DUPLICATE UTR detected – refusing credit', [
                    'utr'              => $utr,
                    'gateway_order_id' => $gatewayOrder->id,
                ]);
                return response()->json(['status' => 'failure', 'message' => 'Duplicate transaction']);
            }

            // Mark as processing to prevent race conditions
            Gatewayorder::where('id', $gatewayOrder->id)->update(['status_id' => 9]);
            Log::info('ZigPay callbackUrl: order locked for processing (status_id=9)', ['gateway_order_id' => $gatewayOrder->id]);

            // ------------------------------------------------------------------
            // STEP 7: Load user
            // ------------------------------------------------------------------
            $user = User::find($gatewayOrder->user_id);
            if (!$user) {
                Log::error('ZigPay callbackUrl: USER NOT FOUND', [
                    'user_id'          => $gatewayOrder->user_id,
                    'gateway_order_id' => $gatewayOrder->id,
                ]);
                return response()->json(['status' => 'failure', 'message' => 'User not found']);
            }

            Log::info('ZigPay callbackUrl: user loaded', [
                'user_id'          => $user->id,
                'user_mobile'      => $user->mobile,
                'gateway_order_id' => $gatewayOrder->id,
            ]);

            // ------------------------------------------------------------------
            // STEP 8: Calculate commission
            // ------------------------------------------------------------------
            $opening_balance = $user->balance->aeps_balance ?? 0;
            $scheme_id       = $user->scheme_id;
            $provider_id     = $this->provider_id;

            Log::info('ZigPay callbackUrl: calculating commission', [
                'scheme_id'       => $scheme_id,
                'provider_id'     => $provider_id,
                'txn_amount'      => $txnAmount,
                'opening_balance' => $opening_balance,
            ]);

            $commissionLibrary = new GetcommissionLibrary();
            $commission = $commissionLibrary->get_commission($scheme_id, $provider_id, $txnAmount);

            Log::info('ZigPay callbackUrl: commission calculated', ['commission' => $commission]);

            $retailer     = $commission['retailer'] ?? 0;
            $d            = $commission['distributor'] ?? 0;
            $sd           = $commission['sdistributor'] ?? 0;
            $st           = $commission['sales_team'] ?? 0;
            $rf           = $commission['referral'] ?? 0;
            $creditAmount = $txnAmount - $retailer;

            Log::info('ZigPay callbackUrl: credit breakdown', [
                'txn_amount'    => $txnAmount,
                'retailer_fee'  => $retailer,
                'credit_amount' => $creditAmount,
            ]);

            // ------------------------------------------------------------------
            // STEP 9: Credit balance
            // ------------------------------------------------------------------
            Balance::where('user_id', $user->id)->increment('aeps_balance', $creditAmount);
            $newBalance = Balance::where('user_id', $user->id)->value('aeps_balance');

            Log::info('ZigPay callbackUrl: balance credited', [
                'user_id'         => $user->id,
                'credit_amount'   => $creditAmount,
                'opening_balance' => $opening_balance,
                'new_balance'     => $newBalance,
            ]);

            // ------------------------------------------------------------------
            // STEP 10: Create report
            // ------------------------------------------------------------------
            $txnIdForReport = $utr ?: $gatewayOrder->order_token;
            Log::info('ZigPay callbackUrl: inserting report', [
                'txnid_used' => $txnIdForReport,
                'mode'       => $gatewayOrder->mode,
            ]);

            $reportId = Report::insertGetId([
                'number'          => $user->mobile,
                'provider_id'     => $provider_id,
                'amount'          => $txnAmount,
                'api_id'          => $this->api_id,
                'status_id'       => 6,
                'created_at'      => $ctime,
                'user_id'         => $user->id,
                'profit'          => '-' . $retailer,
                'mode'            => $gatewayOrder->mode,
                'txnid'           => $txnIdForReport,
                'ip_address'      => $gatewayOrder->ip_address,
                'description'     => 'Add Money via ZigPay' . ($message ? ' - ' . $message : ''),
                'opening_balance' => $opening_balance,
                'total_balance'   => $newBalance,
                'credit_by'       => $user->id,
                'wallet_type'     => 2,
                'client_id'       => $gatewayOrder->client_id ?? '',
            ]);

            Log::info('ZigPay callbackUrl: report inserted', ['report_id' => $reportId]);

            // For WEB orders (no merchant client_id), point client_id to report_id itself
            if ($gatewayOrder->mode !== 'API') {
                Report::where('id', $reportId)->update(['client_id' => $reportId]);
                Log::info('ZigPay callbackUrl: WEB order – updated report.client_id = report_id', [
                    'report_id' => $reportId,
                ]);
            }

            // ------------------------------------------------------------------
            // STEP 11: Finalize gateway order
            // ------------------------------------------------------------------
            Gatewayorder::where('id', $gatewayOrder->id)->update([
                'status_id' => 1,
                'report_id' => $reportId,
                'remark'       => $utr,
            ]);

            Log::info('ZigPay callbackUrl: gateway order finalised (status_id=1)', [
                'gateway_order_id' => $gatewayOrder->id,
                'report_id'        => $reportId,
                'utr'              => $utr,
            ]);

            // ------------------------------------------------------------------
            // STEP 12: Distribute parent commissions
            // ------------------------------------------------------------------
            try {
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
                Log::info('ZigPay callbackUrl: parent commissions distributed', ['report_id' => $reportId]);
            } catch (\Exception $e) {
                Log::error('ZigPay callbackUrl: commission distribution failed', [
                    'report_id' => $reportId,
                    'error'     => $e->getMessage(),
                    'trace'     => $e->getTraceAsString(),
                ]);
            }

            // ------------------------------------------------------------------
            // STEP 13: Fire merchant/API callback (for API-mode orders)
            // ------------------------------------------------------------------
            if (!empty($gatewayOrder->callback_url)) {
                try {
                    $queryParams = [
                        'status'    => 'credit',
                        'client_id' => $gatewayOrder->client_id,
                        'amount'    => $txnAmount,
                        'utr'       => $utr,
                        'txnid'     => $gatewayOrder->id,
                    ];
                    $signatureString = http_build_query($queryParams);
                    $queryParams['signature'] = hash_hmac('sha256', $signatureString, $user->api_token);
                    $cbUrl = $gatewayOrder->callback_url . '?' . http_build_query($queryParams);

                    Log::info('ZigPay callbackUrl: firing merchant callback_url', [
                        'url'              => $cbUrl,
                        'gateway_order_id' => $gatewayOrder->id,
                    ]);

                    $cbResponse = Helpers::pay_curl_get($cbUrl);
                    Traceurl::insertGetId([
                        'user_id'          => $user->id,
                        'url'              => $cbUrl,
                        'number'           => $user->mobile,
                        'response_message' => $cbResponse,
                        'created_at'       => $ctime,
                    ]);

                    Log::info('ZigPay callbackUrl: merchant callback response', ['response' => $cbResponse]);
                } catch (\Exception $e) {
                    Log::error('ZigPay callbackUrl: merchant callback failed', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            } else {
                Log::info('ZigPay callbackUrl: no merchant callback_url – skipping (WEB order or not set)');
            }

            // ------------------------------------------------------------------
            // STEP 14: Forward member-level callback
            // ------------------------------------------------------------------
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
                Log::error('ZigPay callbackUrl: member callback forward failed (success)', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            Log::info('ZigPay callbackUrl: COMPLETED SUCCESSFULLY', [
                'gateway_order_id' => $gatewayOrder->id,
                'report_id'        => $reportId,
                'user_id'          => $user->id,
                'amount_credited'  => $creditAmount,
            ]);

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
            'RefId'      => $request->transaction_id,
            'Service_Id' => 1,
        ];
        $headers = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'accept: */*',
        ];

        $response = Helpers::pay_curl_post($url, $headers, json_encode($payload), 'POST');
        Apiresponse::insertGetId([
            'message'    => $response,
            'api_type'   => 1,
            'created_at' => now(),
            'ip_address' => request()->ip(),
        ]);

        $res = json_decode($response, true);
        if (!is_array($res)) {
            return response()->json(['status' => false, 'message' => 'Invalid response received.']);
        }

        return response()->json([
            'status'  => true,
            'message' => $res['message'] ?? $res['responseMessage'] ?? 'Status retrieved',
            'data'    => [
                'transaction_status' => strtolower((string)($res['status'] ?? '')),
                'utr'                => $res['utR_RRN'] ?? '',
                'reference_id'       => $res['client_RefNo'] ?? '',
                'payment_mode'       => 'UPI',
                'raw'                => $res,
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
            'status'  => true,
            'message' => 'Transaction record found successfully!',
            'data'    => [
                'client_id' => $request->client_id,
                'report_id' => $report->id,
                'amount'    => $report->amount,
                'utr'       => $report->txnid,
                'status'    => 'credit',
            ],
        ]);
    }
}