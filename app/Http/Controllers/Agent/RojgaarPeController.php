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
use App\Library\RojgaarPeLibrary;

class RojgaarPeController extends Controller
{
    private $api_id;
    private $provider_id;
    private $min_amount;
    private $max_amount;
    private $base_url;
    private $rpLibrary;

    public function __construct()
    {
        $this->api_id = 17;
        $this->provider_id = 341;
        $this->rpLibrary = new RojgaarPeLibrary();

        $credentials = json_decode(optional(Api::find($this->api_id))->credentials);
        $this->base_url = rtrim($credentials->base_url ?? 'https://rojgaarpe.com', '/');

        $provider = Provider::find($this->provider_id);
        $this->min_amount = isset($provider->min_amount) ? (int)$provider->min_amount : 300;
        $this->max_amount = isset($provider->max_amount) ? (int)$provider->max_amount : 20000;
    }

    private function absoluteHttpsUrl(string $path): string
    {
        return RojgaarPeLibrary::publicUrl($path);
    }

    private function createPendingPayinReport(Gatewayorder $gatewayOrder, User $user, string $providerOrderId, $ctime): int
    {
        $openingBalance = $user->balance->aeps_balance ?? 0;

        $reportId = Report::insertGetId([
            'number' => $user->mobile,
            'provider_id' => $this->provider_id,
            'amount' => $gatewayOrder->amount,
            'api_id' => $this->api_id,
            'status_id' => 3,
            'created_at' => $ctime,
            'user_id' => $user->id,
            'profit' => 0,
            'mode' => $gatewayOrder->mode,
            'txnid' => '',
            'payid' => $providerOrderId,
            'ip_address' => $gatewayOrder->ip_address,
            'description' => 'Add Money via RojgaarPe',
            'opening_balance' => $openingBalance,
            'total_balance' => $openingBalance,
            'credit_by' => $user->id,
            'wallet_type' => 2,
            'client_id' => $gatewayOrder->client_id ?? '',
        ]);

        if ($gatewayOrder->mode !== 'API') {
            Report::where('id', $reportId)->update(['client_id' => $reportId]);
        }

        return $reportId;
    }

    private function markPayinReportFailed(Gatewayorder $gatewayOrder, string $reason = '', string $utr = '', string $providerOrderId = ''): void
    {
        if (empty($gatewayOrder->report_id)) {
            return;
        }

        $updates = ['status_id' => 2];
        if ($reason !== '') {
            $updates['reason'] = $reason;
        }
        if ($utr !== '') {
            $updates['txnid'] = $utr;
        }
        if ($providerOrderId !== '') {
            $updates['payid'] = $providerOrderId;
        }

        Report::where('id', $gatewayOrder->report_id)
            ->where('status_id', 3)
            ->update($updates);
    }

    private function creditGatewayOrder(Gatewayorder $gatewayOrder, float $txnAmount, string $utr, string $orderId, $ctime, string $creditMode = 'Call-back'): array
    {
        if ((int)$gatewayOrder->status_id === 1) {
            return ['success' => true, 'report_id' => $gatewayOrder->report_id, 'already' => true];
        }
        if ((int)$gatewayOrder->status_id === 9) {
            Gatewayorder::where('id', $gatewayOrder->id)->where('status_id', 9)->update(['status_id' => 3]);
            $gatewayOrder->refresh();
        }
        if ((int)$gatewayOrder->status_id !== 3) {
            return ['success' => false, 'message' => 'Already processed'];
        }

        $existingReportId = (int)($gatewayOrder->report_id ?? 0);
        $existingReport = $existingReportId > 0 ? Report::find($existingReportId) : null;

        if ($existingReport && in_array((int)$existingReport->status_id, [1, 6], true)) {
            return ['success' => true, 'report_id' => $existingReportId, 'already' => true];
        }

        if (!empty($utr)) {
            $duplicateQuery = Report::where('txnid', $utr);
            if ($existingReportId > 0) {
                $duplicateQuery->where('id', '!=', $existingReportId);
            }
            if ($duplicateQuery->exists()) {
                $this->releaseGatewayOrderProcessingLock($gatewayOrder->id);
                return ['success' => false, 'message' => 'Duplicate transaction'];
            }
        }

        Gatewayorder::where('id', $gatewayOrder->id)->update(['status_id' => 9]);

        $user = User::find($gatewayOrder->user_id);
        if (!$user) {
            $this->releaseGatewayOrderProcessingLock($gatewayOrder->id);
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
        $description = 'Add Money via RojgaarPe';
        if ($creditMode !== 'Call-back') {
            $description .= ' (' . $creditMode . ')';
        }

        if ($existingReport && (int)$existingReport->status_id === 3) {
            $reportId = $existingReportId;
            Report::where('id', $reportId)->update([
                'status_id' => 6,
                'amount' => $txnAmount,
                'profit' => '-' . $retailer,
                'txnid' => $txnIdForReport,
                'description' => $description,
                'opening_balance' => $opening_balance,
                'total_balance' => $newBalance,
                'payid' => $orderId ?: ($existingReport->payid ?? ''),
            ]);
        } else {
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
                'payid' => $orderId,
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
            Log::error('RojgaarPe payin commission failed', ['error' => $e->getMessage()]);
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
                Log::error('RojgaarPe merchant callback failed', ['error' => $e->getMessage()]);
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
            Log::error('RojgaarPe member callback failed', ['error' => $e->getMessage()]);
        }

        return ['success' => true, 'report_id' => $reportId, 'utr' => $txnIdForReport];
    }

    public function syncPendingOrderFromProvider(Gatewayorder $gatewayOrder): bool
    {
        if (empty($gatewayOrder->order_token)) {
            return false;
        }

        $sid = (int)$gatewayOrder->status_id;
        if ($sid === 9) {
            Gatewayorder::where('id', $gatewayOrder->id)->where('status_id', 9)->update(['status_id' => 3]);
            $gatewayOrder->refresh();
            $sid = (int)$gatewayOrder->status_id;
        }

        if ($sid !== 3) {
            return $sid === 1;
        }

        $remote = $this->rpLibrary->getPayinStatus($gatewayOrder->order_token, (int)$gatewayOrder->id);
        $parsed = $this->rpLibrary->parsePayinStatusResponse($remote);
        $status = $parsed['status'];

        if ($status === 'SUCCESS') {
            $credited = false;
            DB::transaction(function () use ($gatewayOrder, $parsed, &$credited) {
                $locked = Gatewayorder::where('id', $gatewayOrder->id)->lockForUpdate()->first();
                if (!$locked || (int)$locked->status_id !== 3) {
                    $credited = (int)($locked->status_id ?? 0) === 1;
                    return;
                }
                $txnAmount = $parsed['amount'] > 0 ? $parsed['amount'] : (float)$locked->amount;
                $result = $this->creditGatewayOrder(
                    $locked,
                    $txnAmount,
                    $parsed['utr'],
                    $parsed['orderId'],
                    now(),
                    'Status sync'
                );
                $credited = (bool)($result['success'] ?? false);
                if (!$credited) {
                    Log::error('RojgaarPe payin status sync credit failed', [
                        'order_id' => $locked->id,
                        'message' => $result['message'] ?? 'unknown',
                    ]);
                }
            });
            return $credited;
        }

        if (in_array($status, ['FAILED', 'CANCELLED', 'REFUNDED'], true)) {
            Gatewayorder::where('id', $gatewayOrder->id)
                ->where('status_id', 3)
                ->update(['status_id' => 2, 'remark' => $parsed['utr'] ?: $parsed['orderId']]);
            $gatewayOrder->refresh();
            $this->markPayinReportFailed(
                $gatewayOrder,
                'Payment ' . strtolower($status),
                $parsed['utr'] ?? '',
                $parsed['orderId'] ?? ''
            );
            return false;
        }

        return false;
    }

    private function releaseGatewayOrderProcessingLock(int $gatewayOrderId): void
    {
        Gatewayorder::where('id', $gatewayOrderId)->where('status_id', 9)->update(['status_id' => 3]);
    }

    private function findGatewayOrderByMerchantOrderNo(string $merchantOrderNo): ?Gatewayorder
    {
        if ($merchantOrderNo === '') {
            return null;
        }

        return Gatewayorder::where(function ($query) use ($merchantOrderNo) {
            $query->where('order_token', $merchantOrderNo)
                ->orWhere('client_id', $merchantOrderNo);
        })->where('api_id', $this->api_id)->orderBy('id', 'DESC')->first();
    }

    private function providerRequest(string $url, string $body): array
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

    private function logProviderResponse(string $message, string $requestMessage = '', ?string $responseType = null, ?int $reportId = null, ?string $ipAddress = null): void
    {
        Apiresponse::insertGetId([
            'message' => $message,
            'api_type' => $this->api_id,
            'response_type' => $responseType,
            'request_message' => $requestMessage,
            'report_id' => $reportId,
            'created_at' => now(),
            'ip_address' => $ipAddress ?? request()->ip(),
        ]);
    }

    private function providerFailureMessage(array $result, string $action): string
    {
        if (!empty($result['error'])) {
            return 'Unable to reach RojgaarPe (' . $action . '): ' . $result['error'];
        }

        $body = trim((string)($result['body'] ?? ''));
        $decoded = json_decode($body, true);

        if (is_array($decoded)) {
            return (string)($decoded['message'] ?? 'RojgaarPe ' . $action . ' failed.');
        }

        $httpCode = (int)($result['http_code'] ?? 0);
        if ($httpCode >= 500) {
            return 'RojgaarPe service error (HTTP ' . $httpCode . '). Contact RojgaarPe support.';
        }

        return $body !== ''
            ? 'Invalid response from RojgaarPe during ' . $action . '.'
            : 'Empty response from RojgaarPe during ' . $action . '.';
    }

    private function extractPayinDataFromIntent(array $result): array
    {
        $qr = (string)($result['qr'] ?? '');
        $orderId = (string)($result['provider_txn'] ?? $result['txn_id'] ?? $result['client_ref_id'] ?? '');

        return [
            'paymentUrl' => $qr,
            'paymentPageUrl' => '',
            'qrCodeUrl' => '',
            'orderId' => $orderId,
            'qrString' => $qr,
            'utrInputLink' => '',
            'deepLink' => [
                'upi_intent' => $qr,
                'upi_phonepe' => '',
                'upi_gpay' => '',
                'upi_paytm' => '',
            ],
            'status' => 'pending',
            'orderStatus' => 'PENDING',
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
            return view('agent.add-money.rojgaarpe')->with([
                'page_title' => 'Payin 10',
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

        $credentials = json_decode(optional(Api::find($this->api_id))->credentials);
        if (empty($credentials->login_id) || empty($credentials->secret_key)) {
            return response()->json(['status' => 'failure', 'message' => 'RojgaarPe credentials not configured']);
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
            'order_token' => 'RPITMP' . time(),
        ]);

        $merchantOrderNo = $this->rpLibrary->buildPayinMerchantRef($gatewayOrderId);

        Gatewayorder::where('id', $gatewayOrderId)->update([
            'order_token' => $merchantOrderNo,
            'client_id' => ($mode === 'WEB' || empty($client_id)) ? $merchantOrderNo : $client_id,
        ]);

        $intentParams = [
            'amount' => (float)$amount,
            'merchant_refid' => $merchantOrderNo,
            'payerName' => (string)$name,
            'mobileID' => (string)$mobile,
            'callback_url' => $this->absoluteHttpsUrl('api/call-back/rojgaarpe-payin'),
            'remark' => 'Add Money - ' . $gatewayOrderId,
        ];

        $intentResult = $this->rpLibrary->createPayinIntent($intentParams, $gatewayOrderId);
        if (!($intentResult['ok'] ?? false)) {
            Gatewayorder::where('id', $gatewayOrderId)->update(['status_id' => 2]);
            return response()->json([
                'status' => 'failure',
                'message' => $intentResult['message'] ?? 'Failed to create RojgaarPe order',
            ]);
        }

        $payin = $this->extractPayinDataFromIntent($intentResult);
        if ($payin['qrString'] === '' && $payin['paymentUrl'] === '') {
            Gatewayorder::where('id', $gatewayOrderId)->update(['status_id' => 2]);
            return response()->json([
                'status' => 'failure',
                'message' => $intentResult['message'] ?? 'Failed to create RojgaarPe order',
            ]);
        }

        Gatewayorder::where('id', $gatewayOrderId)->update([
            'remark' => $payin['orderId'],
        ]);

        $user = User::find($user_id);
        $reportId = null;
        if ($user) {
            $gatewayOrder = Gatewayorder::find($gatewayOrderId);
            if ($gatewayOrder) {
                $reportId = $this->createPendingPayinReport($gatewayOrder, $user, $payin['orderId'], $ctime);
                Gatewayorder::where('id', $gatewayOrderId)->update(['report_id' => $reportId]);
            }
        }

        $responseData = [
            'txnid' => $gatewayOrderId,
            'order_token' => $merchantOrderNo,
            'transaction_id' => $payin['orderId'],
            'qrString' => $payin['qrString'],
            'paymentUrl' => $payin['paymentUrl'],
            'qrCodeUrl' => $payin['qrCodeUrl'],
            'status' => $payin['status'] ?: 'pending',
        ];

        if ($reportId) {
            $responseData['report_id'] = $reportId;
        }

        if ($mode === 'API') {
            $responseData['payment_page_url'] = '';
            $responseData['upi_link'] = $payin['qrString'];
            $responseData['upi_intent'] = $payin['deepLink']['upi_intent'] ?? $payin['qrString'];
            $responseData['upi_phonepe'] = $payin['deepLink']['upi_phonepe'] ?? '';
            $responseData['upi_gpay'] = $payin['deepLink']['upi_gpay'] ?? '';
            $responseData['upi_paytm'] = $payin['deepLink']['upi_paytm'] ?? '';
            $responseData['payment_status'] = $payin['orderStatus'] ?: 'PENDING';
            $responseData['utr_input_url'] = $payin['utrInputLink'] ?? '';
        }

        if ($mode !== 'API') {
            if ($payin['qrCodeUrl'] !== '' && str_starts_with($payin['qrCodeUrl'], 'http')) {
                $responseData['qrCodeUrl'] = $payin['qrCodeUrl'];
            } elseif ($payin['qrString'] !== '') {
                $responseData['qrCodeUrl'] = url('agent/add-money/v10/view-qrcode') . '?upi_string=' . urlencode($payin['qrString']);
            }
            if ($payin['paymentUrl'] !== '') {
                $responseData['upiLink'] = $payin['paymentUrl'];
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => $intentResult['message'] ?? 'Order created successfully',
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

        $this->syncPendingOrderFromProvider($gatewayOrder);
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

    /**
     * Standard ack expected by RojgaarPe for POST application/json payin webhooks.
     */
    private function rojgaarPeCallbackAck(string $message = 'Callback Received Successfully')
    {
        return response()->json([
            'received' => true,
            'status' => true,
            'message' => $message,
        ], 200);
    }

    public function payinCallback(Request $request)
    {
        $ctime = now();
        $payload = RojgaarPeLibrary::parseIncomingCallback($request);
        $audit = RojgaarPeLibrary::buildCallbackAudit($request, $payload);
        // Prefer merchant_refid / txn_id from RojgaarPe JSON callback payload
        $merchantOrderNo = (string)(
            $payload['merchantOrderNo']
            ?? $payload['merchant_refid']
            ?? $payload['txn_id']
            ?? ''
        );
        $gatewayOrder = $this->findGatewayOrderByMerchantOrderNo($merchantOrderNo);
        $gatewayOrderId = $gatewayOrder->id ?? null;

        $status = strtoupper((string)($payload['status'] ?? $payload['orderStatus'] ?? ''));
        $amount = (float)($payload['amount'] ?? 0);
        $utr = (string)($payload['utr'] ?? '');
        $orderId = (string)(
            $payload['orderId']
            ?? $payload['provider_txnid']
            ?? $payload['provider_txn_id']
            ?? $payload['platOrderNo']
            ?? ''
        );
        $logType = RojgaarPeLibrary::resolvePayinLogType($status);

        Log::info('RojgaarPe payin callback received', array_merge($audit, ['log_type' => $logType, 'status' => $status]));

        $this->logProviderResponse(
            json_encode($audit),
            substr((string)$request->getContent(), 0, 65000),
            $logType,
            $gatewayOrderId,
            $request->ip()
        );

        if ($merchantOrderNo === '' && RojgaarPeLibrary::isEffectivelyEmptyPayload($payload)) {
            return $this->rojgaarPeCallbackAck();
        }

        if ($merchantOrderNo === '') {
            return response()->json(['received' => false, 'status' => false, 'message' => 'Missing merchant_refid'], 400);
        }

        if (!$gatewayOrder) {
            return response()->json(['received' => false, 'status' => false, 'message' => 'Order not found'], 404);
        }

        // RojgaarPe: PENDING — acknowledge only, no credit
        if (in_array($status, ['PENDING', 'PROCESSING', ''], true)) {
            if ($orderId !== '' && !empty($gatewayOrder->report_id)) {
                Report::where('id', $gatewayOrder->report_id)
                    ->where('status_id', 3)
                    ->update(['payid' => $orderId]);
            }
            return $this->rojgaarPeCallbackAck();
        }

        if (in_array($status, ['FAILED', 'CANCELLED', 'REFUNDED'], true)) {
            return DB::transaction(function () use ($gatewayOrder, $ctime, $amount, $utr, $status, $orderId, $merchantOrderNo) {
                $locked = Gatewayorder::where('id', $gatewayOrder->id)->lockForUpdate()->first();
                if ($locked && (int)$locked->status_id === 3) {
                    Gatewayorder::where('id', $locked->id)->update(['status_id' => 2, 'remark' => $utr ?: $orderId]);
                    $this->markPayinReportFailed($locked, 'Payment ' . strtolower($status), $utr, $orderId);
                    try {
                        $this->forwardMemberCallback(
                            $locked->user_id,
                            'failed',
                            $locked->client_id ?: $merchantOrderNo,
                            $amount > 0 ? $amount : (float)$locked->amount,
                            $utr,
                            $locked->id,
                            $ctime
                        );
                    } catch (\Exception $e) {
                        Log::error('RojgaarPe payin failed callback forward error', ['error' => $e->getMessage()]);
                    }
                }
                // Idempotent: already-failed orders still ack 200
                return $this->rojgaarPeCallbackAck();
            });
        }

        if ($status !== 'SUCCESS') {
            return $this->rojgaarPeCallbackAck();
        }

        // Prefer UTR / amount / provider id from webhook; optionally enrich via status API
        $verifiedViaApi = false;
        $confirmed = $this->rpLibrary->confirmPayinSuccessFromApi($merchantOrderNo, (int)$gatewayOrder->id);
        if ($confirmed) {
            $verifiedViaApi = true;
            $payload = array_merge($payload, [
                'status' => 'SUCCESS',
                'orderStatus' => 'SUCCESS',
                'utr' => (string)($confirmed['utr'] ?? $payload['utr'] ?? ''),
                'amount' => (float)($confirmed['amount'] ?? $payload['amount'] ?? 0),
                'orderId' => (string)($confirmed['orderId'] ?? $payload['orderId'] ?? ''),
            ]);
            $utr = (string)($payload['utr'] ?? '');
            $orderId = (string)($payload['orderId'] ?? '');
            $amount = (float)($payload['amount'] ?? 0);
        }

        return DB::transaction(function () use ($gatewayOrder, $amount, $utr, $orderId, $ctime, $verifiedViaApi) {
            $locked = Gatewayorder::where('id', $gatewayOrder->id)->lockForUpdate()->first();
            if (!$locked) {
                return response()->json(['received' => false, 'status' => false, 'message' => 'Order not found'], 404);
            }

            // Duplicate SUCCESS: acknowledge 200 without re-crediting
            if ((int)$locked->status_id === 1) {
                return $this->rojgaarPeCallbackAck();
            }

            $txnAmount = $amount > 0 ? $amount : (float)$locked->amount;
            $creditMode = $verifiedViaApi ? 'Status-sync' : 'Call-back';
            $result = $this->creditGatewayOrder($locked, $txnAmount, $utr, $orderId, $ctime, $creditMode);

            if ($result['already'] ?? false) {
                return $this->rojgaarPeCallbackAck();
            }

            if (!($result['success'] ?? false)) {
                Log::error('RojgaarPe payin callback credit failed', [
                    'order_id' => $locked->id,
                    'message' => $result['message'] ?? 'unknown',
                ]);
                return response()->json([
                    'received' => false,
                    'status' => false,
                    'message' => $result['message'] ?? 'Unable to process payment',
                ], 500);
            }

            return $this->rojgaarPeCallbackAck();
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

        if ((int)$gatewayOrder->status_id === 3) {
            $this->syncPendingOrderFromProvider($gatewayOrder);
            $gatewayOrder->refresh();
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
            $report = $gatewayOrder->report_id ? Report::find($gatewayOrder->report_id) : null;
            return response()->json([
                'status' => true,
                'message' => 'Transaction failed',
                'data' => [
                    'client_id' => $request->client_id,
                    'report_id' => $report->id ?? $gatewayOrder->report_id,
                    'amount' => $report->amount ?? $gatewayOrder->amount,
                    'utr' => $report->txnid ?? '',
                    'status' => 'failed',
                ],
            ]);
        }

        $report = $gatewayOrder->report_id ? Report::find($gatewayOrder->report_id) : null;
        return response()->json([
            'status' => true,
            'message' => 'Transaction is pending',
            'data' => [
                'client_id' => $request->client_id,
                'report_id' => $report->id ?? $gatewayOrder->report_id,
                'amount' => $report->amount ?? $gatewayOrder->amount,
                'utr' => $report->txnid ?? '',
                'status' => 'pending',
            ],
        ]);
    }
}
