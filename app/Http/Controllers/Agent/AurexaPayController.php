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
use App\Library\AurexaPayLibrary;

class AurexaPayController extends Controller
{
    private $api_id;
    private $provider_id;
    private $min_amount;
    private $max_amount;
    private $base_url;
    private $axLibrary;

    public function __construct()
    {
        $this->api_id = 19;
        $this->provider_id = 343;
        $this->axLibrary = new AurexaPayLibrary();

        $credentials = json_decode(optional(Api::find($this->api_id))->credentials);
        $this->base_url = rtrim($credentials->base_url ?? 'https://AurexaPay.com', '/');

        $provider = Provider::find($this->provider_id);
        $this->min_amount = isset($provider->min_amount) ? (int)$provider->min_amount : AurexaPayLibrary::PAYIN_MIN;
        $this->max_amount = isset($provider->max_amount) ? (int)$provider->max_amount : AurexaPayLibrary::PAYIN_MAX;
    }

    private function absoluteHttpsUrl(string $path): string
    {
        return AurexaPayLibrary::publicUrl($path);
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
            'description' => 'Add Money via AurexaPay',
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

    /**
     * True when report already received Payin wallet credit (balances moved).
     * Status 1 with opening==closing is a broken manual success — needs credit.
     */
    private function reportAlreadyCredited(?Report $report): bool
    {
        if (!$report) {
            return false;
        }
        $status = (int)$report->status_id;
        if ($status === 6) {
            $opening = (float)($report->opening_balance ?? 0);
            $closing = (float)($report->total_balance ?? 0);
            // Proper credit always moves closing away from opening when amount > 0
            if ((float)$report->amount <= 0) {
                return true;
            }
            return abs($closing - $opening) > 0.001;
        }
        if ($status === 1) {
            $opening = (float)($report->opening_balance ?? 0);
            $closing = (float)($report->total_balance ?? 0);
            return abs($closing - $opening) > 0.001;
        }
        return false;
    }

    /**
     * Merchant/member HTTP callbacks — must run OUTSIDE DB transactions
     * (long curls cause "MySQL server has gone away" on commit).
     */
    private function sendPayinCreditNotifications(array $notify): void
    {
        $userId = (int)($notify['user_id'] ?? 0);
        $callbackUrl = (string)($notify['callback_url'] ?? '');
        $ctime = $notify['ctime'] ?? now();
        $amount = (float)($notify['amount'] ?? 0);
        $utr = (string)($notify['utr'] ?? '');
        $clientId = (string)($notify['client_id'] ?? '');
        $gatewayOrderId = (int)($notify['gateway_order_id'] ?? 0);
        $mobile = (string)($notify['mobile'] ?? '');
        $apiToken = (string)($notify['api_token'] ?? '');

        if ($callbackUrl !== '') {
            try {
                $queryParams = [
                    'status' => 'credit',
                    'client_id' => $clientId,
                    'amount' => $amount,
                    'utr' => $utr,
                    'txnid' => $gatewayOrderId,
                ];
                $signatureString = http_build_query($queryParams);
                $queryParams['signature'] = hash_hmac('sha256', $signatureString, $apiToken);
                $cbUrl = $callbackUrl . '?' . http_build_query($queryParams);
                $cbResponse = Helpers::pay_curl_get($cbUrl);
                try {
                    DB::reconnect();
                } catch (\Throwable $e) {
                    // ignore
                }
                Traceurl::insertGetId([
                    'user_id' => $userId,
                    'url' => $cbUrl,
                    'number' => $mobile,
                    'response_message' => $cbResponse,
                    'created_at' => $ctime,
                ]);
            } catch (\Exception $e) {
                Log::error('AurexaPay merchant callback failed', ['error' => $e->getMessage()]);
            }
        }

        try {
            $this->forwardMemberCallback(
                $userId,
                'credit',
                $clientId,
                $amount,
                $utr,
                $gatewayOrderId,
                $ctime
            );
        } catch (\Exception $e) {
            Log::error('AurexaPay member callback failed', ['error' => $e->getMessage()]);
        }
    }

    private function creditGatewayOrder(Gatewayorder $gatewayOrder, float $txnAmount, string $utr, string $orderId, $ctime, string $creditMode = 'Call-back', bool $sendNotifications = true): array
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

        if ($this->reportAlreadyCredited($existingReport)) {
            Gatewayorder::where('id', $gatewayOrder->id)
                ->whereIn('status_id', [3, 9])
                ->update([
                    'status_id' => 1,
                    'remark' => $utr ?: ($orderId ?: ($gatewayOrder->remark ?? '')),
                ]);
            return ['success' => true, 'report_id' => $existingReportId, 'already' => true];
        }

        if (!empty($utr)) {
            $duplicateQuery = Report::where('txnid', $utr)->whereIn('status_id', [1, 6]);
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

        // Ensure balance row exists
        $balanceRow = Balance::where('user_id', $user->id)->first();
        if (!$balanceRow) {
            Balance::insert([
                'user_id' => $user->id,
                'aeps_balance' => 0,
                'user_balance' => 0,
            ]);
        }

        $opening_balance = (float)(Balance::where('user_id', $user->id)->value('aeps_balance') ?? 0);
        $commissionLibrary = new GetcommissionLibrary();
        $commission = $commissionLibrary->get_commission($user->scheme_id, $this->provider_id, $txnAmount);
        $retailer = $commission['retailer'] ?? 0;
        $d = $commission['distributor'] ?? 0;
        $sd = $commission['sdistributor'] ?? 0;
        $st = $commission['sales_team'] ?? 0;
        $rf = $commission['referral'] ?? 0;
        $creditAmount = $txnAmount - $retailer;

        Balance::where('user_id', $user->id)->increment('aeps_balance', $creditAmount);
        $newBalance = (float)Balance::where('user_id', $user->id)->value('aeps_balance');

        $txnIdForReport = $utr ?: ($orderId ?: $gatewayOrder->order_token);
        $description = 'Add Money via AurexaPay';
        if ($creditMode !== 'Call-back') {
            $description .= ' (' . $creditMode . ')';
        }

        // Update pending (3) or broken success-without-credit (1 / empty 6)
        $canUpdateExisting = $existingReport && in_array((int)$existingReport->status_id, [1, 3, 6], true)
            && !$this->reportAlreadyCredited($existingReport);

        if ($canUpdateExisting) {
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
                'wallet_type' => 2,
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
            Log::error('AurexaPay payin commission failed', ['error' => $e->getMessage()]);
        }

        $notify = [
            'user_id' => $user->id,
            'api_token' => $user->api_token,
            'mobile' => $user->mobile,
            'callback_url' => $gatewayOrder->callback_url ?? '',
            'client_id' => $gatewayOrder->client_id ?: $gatewayOrder->order_token,
            'gateway_order_id' => $gatewayOrder->id,
            'amount' => $txnAmount,
            'utr' => $utr,
            'ctime' => $ctime,
        ];

        if ($sendNotifications) {
            $this->sendPayinCreditNotifications($notify);
        }

        return [
            'success' => true,
            'report_id' => $reportId,
            'utr' => $txnIdForReport,
            'notify' => $notify,
        ];
    }

    public function syncPendingOrderFromProvider(Gatewayorder $gatewayOrder): bool
    {
        if (empty($gatewayOrder->order_token)) {
            return false;
        }

        // Fresh connection before any long HTTP work
        try {
            DB::reconnect();
        } catch (\Throwable $e) {
            // continue; credit path will reconnect again
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

        $parsed = null;
        $status = 'PENDING';

        $providerTxnId = (string)($gatewayOrder->remark ?? '');
        if ($providerTxnId === '' && !empty($gatewayOrder->report_id)) {
            $providerTxnId = (string)(Report::where('id', $gatewayOrder->report_id)->value('payid') ?? '');
        }
        if ($providerTxnId === '') {
            $providerTxnId = (string)$gatewayOrder->order_token;
        }

        $remote = $this->axLibrary->getPayinStatus($providerTxnId, (int)$gatewayOrder->id);
        $parsed = $this->axLibrary->parsePayinStatusResponse($remote);
        $status = $parsed['status'];

        // Reconnect after HTTP — prevents "MySQL server has gone away" on commit
        try {
            DB::reconnect();
        } catch (\Throwable $e) {
            Log::warning('AurexaPay sync DB reconnect failed', ['error' => $e->getMessage()]);
        }

        $gatewayOrder = Gatewayorder::find($gatewayOrder->id);
        if (!$gatewayOrder || (int)$gatewayOrder->status_id !== 3) {
            return $gatewayOrder && (int)$gatewayOrder->status_id === 1;
        }

        if ($status === 'SUCCESS' && is_array($parsed)) {
            $notify = null;
            $credited = false;

            try {
                DB::transaction(function () use ($gatewayOrder, $parsed, &$credited, &$notify) {
                    $locked = Gatewayorder::where('id', $gatewayOrder->id)->lockForUpdate()->first();
                    if (!$locked || (int)$locked->status_id !== 3) {
                        $credited = (int)($locked->status_id ?? 0) === 1;
                        return;
                    }
                    $txnAmount = $parsed['amount'] > 0 ? $parsed['amount'] : (float)$locked->amount;
                    // Defer HTTP notifications until AFTER commit
                    $result = $this->creditGatewayOrder(
                        $locked,
                        $txnAmount,
                        (string)($parsed['utr'] ?? ''),
                        (string)($parsed['orderId'] ?? ''),
                        now(),
                        'Status sync',
                        false
                    );
                    $credited = (bool)($result['success'] ?? false) || (bool)($result['already'] ?? false);
                    if (!empty($result['notify'])) {
                        $notify = $result['notify'];
                    }
                    if (!$credited) {
                        Log::error('AurexaPay payin status sync credit failed', [
                            'order_id' => $locked->id,
                            'message' => $result['message'] ?? 'unknown',
                        ]);
                    }
                });
            } catch (\Throwable $e) {
                Log::error('AurexaPay sync credit transaction failed', [
                    'order_id' => $gatewayOrder->id,
                    'error' => $e->getMessage(),
                ]);
                try {
                    DB::reconnect();
                    Gatewayorder::where('id', $gatewayOrder->id)->where('status_id', 9)->update(['status_id' => 3]);
                } catch (\Throwable $inner) {
                    // ignore
                }
                return false;
            }

            if ($credited && is_array($notify)) {
                try {
                    DB::reconnect();
                } catch (\Throwable $e) {
                    // ignore
                }
                $this->sendPayinCreditNotifications($notify);
            }

            return $credited;
        }

        if (in_array($status, ['FAILED', 'CANCELLED', 'REFUNDED'], true)) {
            Gatewayorder::where('id', $gatewayOrder->id)
                ->where('status_id', 3)
                ->update(['status_id' => 2, 'remark' => ($parsed['utr'] ?? '') ?: ($parsed['orderId'] ?? '')]);
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
            return 'Unable to reach AurexaPay (' . $action . '): ' . $result['error'];
        }

        $body = trim((string)($result['body'] ?? ''));
        $decoded = json_decode($body, true);

        if (is_array($decoded)) {
            return (string)($decoded['message'] ?? 'AurexaPay ' . $action . ' failed.');
        }

        $httpCode = (int)($result['http_code'] ?? 0);
        if ($httpCode >= 500) {
            return 'AurexaPay service error (HTTP ' . $httpCode . '). Contact AurexaPay support.';
        }

        return $body !== ''
            ? 'Invalid response from AurexaPay during ' . $action . '.'
            : 'Empty response from AurexaPay during ' . $action . '.';
    }

    private function extractPayinDataFromIntent(array $result): array
    {
        $qr = (string)($result['qrString'] ?? $result['qr'] ?? '');
        $paymentUrl = (string)($result['paymentUrl'] ?? '');
        $orderId = (string)($result['orderId'] ?? '');

        return [
            'paymentUrl' => $paymentUrl !== '' ? $paymentUrl : $qr,
            'paymentPageUrl' => $paymentUrl,
            'qrCodeUrl' => (string)($result['qrCodeUrl'] ?? ''),
            'orderId' => $orderId,
            'qrString' => $qr,
            'utrInputLink' => '',
            'deepLink' => [
                'upi_intent' => $qr,
                'upi_phonepe' => '',
                'upi_gpay' => '',
                'upi_paytm' => '',
            ],
            'status' => strtolower((string)($result['status'] ?? 'pending')),
            'orderStatus' => strtoupper((string)($result['status'] ?? 'PENDING')),
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
            return view('agent.add-money.aurexapay')->with([
                'page_title' => 'Payin 12',
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
        if (empty($credentials->client_key ?? $credentials->clientKey ?? null) || empty($credentials->client_secret ?? $credentials->clientSecret ?? null)) {
            return response()->json(['status' => 'failure', 'message' => 'AurexaPay credentials not configured']);
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
            'order_token' => 'AXITMP' . time(),
        ]);

        $merchantOrderNo = $this->axLibrary->buildPayinMerchantRef($gatewayOrderId);

        Gatewayorder::where('id', $gatewayOrderId)->update([
            'order_token' => $merchantOrderNo,
            'client_id' => ($mode === 'WEB' || empty($client_id)) ? $merchantOrderNo : $client_id,
        ]);

        $intentParams = [
            'amount' => (float)$amount,
            'reference' => $merchantOrderNo,
            'name' => (string)$name,
            'email' => (string)$email,
            'mobile' => (string)$mobile,
        ];

        $intentResult = $this->axLibrary->createPayinIntent($intentParams, $gatewayOrderId);
        if (!($intentResult['ok'] ?? false)) {
            Gatewayorder::where('id', $gatewayOrderId)->update(['status_id' => 2]);
            return response()->json([
                'status' => 'failure',
                'message' => $intentResult['message'] ?? 'Failed to create AurexaPay order',
            ]);
        }

        $payin = $this->extractPayinDataFromIntent($intentResult);
        // Allow success even if QR fields are empty when provider returns a transaction id (status polling).
        if ($payin['orderId'] === '' && $payin['qrString'] === '' && $payin['paymentUrl'] === '') {
            Gatewayorder::where('id', $gatewayOrderId)->update(['status_id' => 2]);
            return response()->json([
                'status' => 'failure',
                'message' => $intentResult['message'] ?? 'Failed to create AurexaPay order',
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
            $upiForQr = $payin['qrString'] !== '' ? $payin['qrString'] : $payin['paymentUrl'];
            if ($payin['qrCodeUrl'] !== '' && str_starts_with($payin['qrCodeUrl'], 'http')) {
                $responseData['qrCodeUrl'] = $payin['qrCodeUrl'];
            } elseif ($upiForQr !== '' && (str_starts_with(strtolower($upiForQr), 'upi://') || str_contains(strtolower($upiForQr), 'pa='))) {
                $responseData['qrCodeUrl'] = url('agent/add-money/v12/view-qrcode') . '?upi_string=' . urlencode($upiForQr);
                $responseData['qrString'] = $upiForQr;
            } elseif ($payin['paymentUrl'] !== '' && str_starts_with(strtolower($payin['paymentUrl']), 'http')) {
                // Hosted payment page — no QR image; button opens link.
                $responseData['qrCodeUrl'] = '';
                $responseData['qrString'] = $payin['paymentUrl'];
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
     * Standard ack expected by AurexaPay for POST application/json payin webhooks.
     */
    private function AurexaPayCallbackAck(string $message = 'Callback Received Successfully')
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
        $payload = AurexaPayLibrary::parseIncomingCallback($request);
        $audit = AurexaPayLibrary::buildCallbackAudit($request, $payload);
        // Prefer merchant_refid / txn_id from AurexaPay JSON callback payload
        $merchantOrderNo = (string)(
            $payload['merchantOrderNo']
            ?? $payload['reference']
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
        $logType = AurexaPayLibrary::resolvePayinLogType($status);

        Log::info('AurexaPay payin callback received', array_merge($audit, ['log_type' => $logType, 'status' => $status]));

        $this->logProviderResponse(
            json_encode($audit),
            substr((string)$request->getContent(), 0, 65000),
            $logType,
            $gatewayOrderId,
            $request->ip()
        );

        if ($merchantOrderNo === '' && AurexaPayLibrary::isEffectivelyEmptyPayload($payload)) {
            return $this->AurexaPayCallbackAck();
        }

        if ($merchantOrderNo === '') {
            return response()->json(['received' => false, 'status' => false, 'message' => 'Missing merchant_refid or txn_id'], 400);
        }

        if (!$gatewayOrder) {
            return response()->json(['received' => false, 'status' => false, 'message' => 'Order not found'], 404);
        }

        // AurexaPay: PENDING — acknowledge only, no credit
        if (in_array($status, ['PENDING', 'PROCESSING', ''], true)) {
            if ($orderId !== '' && !empty($gatewayOrder->report_id)) {
                Report::where('id', $gatewayOrder->report_id)
                    ->where('status_id', 3)
                    ->update(['payid' => $orderId]);
            }
            return $this->AurexaPayCallbackAck();
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
                        Log::error('AurexaPay payin failed callback forward error', ['error' => $e->getMessage()]);
                    }
                }
                // Idempotent: already-failed orders still ack 200
                return $this->AurexaPayCallbackAck();
            });
        }

        if ($status !== 'SUCCESS') {
            return $this->AurexaPayCallbackAck();
        }

        // Trust webhook SUCCESS + amount/UTR. Do NOT block on status API
        // (AurexaPay often returns StatusCode 106 while dashboard already shows Success).
        $verifiedViaApi = false;
        if ($utr === '' || $amount <= 0 || $orderId === '') {
            $statusLookupId = $orderId !== '' ? $orderId : (string)($gatewayOrder->remark ?? '');
            if ($statusLookupId === '') {
                $statusLookupId = $merchantOrderNo;
            }
            $confirmed = $this->axLibrary->confirmPayinSuccessFromApi($statusLookupId, (int)$gatewayOrder->id);
            if ($confirmed) {
                $verifiedViaApi = true;
                $utr = $utr !== '' ? $utr : (string)($confirmed['utr'] ?? '');
                $orderId = $orderId !== '' ? $orderId : (string)($confirmed['orderId'] ?? '');
                $amount = $amount > 0 ? $amount : (float)($confirmed['amount'] ?? 0);
            }
        }

        $notify = null;
        $ack = null;

        try {
            DB::transaction(function () use ($gatewayOrder, $amount, $utr, $orderId, $ctime, $verifiedViaApi, &$notify, &$ack) {
                $locked = Gatewayorder::where('id', $gatewayOrder->id)->lockForUpdate()->first();
                if (!$locked) {
                    $ack = response()->json(['received' => false, 'status' => false, 'message' => 'Order not found'], 404);
                    return;
                }

                if ((int)$locked->status_id === 1) {
                    $ack = $this->AurexaPayCallbackAck();
                    return;
                }

                $txnAmount = $amount > 0 ? $amount : (float)$locked->amount;
                $creditMode = $verifiedViaApi ? 'Status-sync' : 'Call-back';
                $result = $this->creditGatewayOrder($locked, $txnAmount, $utr, $orderId, $ctime, $creditMode, false);

                if ($result['already'] ?? false) {
                    $ack = $this->AurexaPayCallbackAck();
                    return;
                }

                if (!($result['success'] ?? false)) {
                    Log::error('AurexaPay payin callback credit failed', [
                        'order_id' => $locked->id,
                        'message' => $result['message'] ?? 'unknown',
                    ]);
                    $ack = $this->AurexaPayCallbackAck();
                    return;
                }

                if (!empty($result['notify'])) {
                    $notify = $result['notify'];
                }
                $ack = $this->AurexaPayCallbackAck();
            });
        } catch (\Throwable $e) {
            Log::error('AurexaPay payin callback transaction failed', [
                'order_id' => $gatewayOrder->id ?? null,
                'error' => $e->getMessage(),
            ]);
            try {
                DB::reconnect();
                if (!empty($gatewayOrder->id)) {
                    Gatewayorder::where('id', $gatewayOrder->id)->where('status_id', 9)->update(['status_id' => 3]);
                }
            } catch (\Throwable $inner) {
                // ignore
            }
            return $this->AurexaPayCallbackAck();
        }

        if (is_array($notify)) {
            try {
                DB::reconnect();
            } catch (\Throwable $e) {
                // ignore
            }
            $this->sendPayinCreditNotifications($notify);
        }

        return $ack ?: $this->AurexaPayCallbackAck();
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
