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
use App\Library\FrapPayLibrary;

class FrapPayController extends Controller
{
    private $api_id;
    private $provider_id;
    private $min_amount;
    private $max_amount;
    private $fpLibrary;

    public function __construct()
    {
        $this->api_id = 18;
        $this->provider_id = 342;
        $this->fpLibrary = new FrapPayLibrary();

        $provider = Provider::find($this->provider_id);
        $this->min_amount = isset($provider->min_amount) ? (int)$provider->min_amount : FrapPayLibrary::PAYIN_MIN;
        $this->max_amount = isset($provider->max_amount) ? (int)$provider->max_amount : FrapPayLibrary::PAYIN_MAX;
    }

    private function absoluteHttpsUrl(string $path): string
    {
        return FrapPayLibrary::publicUrl($path);
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
            'description' => 'Add Money via FrapPay',
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

        if ($utr !== '') {
            $dup = Report::where('txnid', $utr);
            if ($existingReportId > 0) {
                $dup->where('id', '!=', $existingReportId);
            }
            if ($dup->exists()) {
                return ['success' => false, 'message' => 'Duplicate transaction'];
            }
        }

        Gatewayorder::where('id', $gatewayOrder->id)->update(['status_id' => 9]);

        $user = User::find($gatewayOrder->user_id);
        if (!$user) {
            Gatewayorder::where('id', $gatewayOrder->id)->update(['status_id' => 3]);
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
        $description = 'Add Money via FrapPay';
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
            (new Commission_increment())->parent_recharge_commission(
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
            Log::error('FrapPay payin commission failed', ['error' => $e->getMessage()]);
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
                Log::error('FrapPay merchant callback failed', ['error' => $e->getMessage()]);
            }
        }

        return ['success' => true, 'report_id' => $reportId, 'utr' => $txnIdForReport];
    }

    private function markFailed(Gatewayorder $gatewayOrder, string $reason = ''): void
    {
        Gatewayorder::where('id', $gatewayOrder->id)->whereIn('status_id', [3, 9])->update(['status_id' => 2]);
        if (!empty($gatewayOrder->report_id)) {
            Report::where('id', $gatewayOrder->report_id)->where('status_id', 3)->update([
                'status_id' => 2,
                'reason' => $reason,
            ]);
        }
    }

    public function welcome()
    {
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($this->provider_id, Auth::id());
        if (($activeService['status_id'] ?? 0) == 1) {
            return view('agent.add-money.frappay')->with([
                'page_title' => 'Payin 11',
                'min_amount' => $this->min_amount,
                'max_amount' => $this->max_amount,
            ]);
        }
        return redirect()->back();
    }

    public function createOrderWeb(Request $request)
    {
        $rules = ['amount' => 'required|numeric|between:' . $this->min_amount . ',' . $this->max_amount];
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
                'message' => 'Please update your profile with valid name, email, and 10-digit mobile number.',
            ]);
        }

        return $this->createOrderMiddle($request->amount, Auth::id(), 'WEB', '', '', $name, $email, $mobile);
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
        if (empty($credentials->api_key) || empty($credentials->secret_key)) {
            return response()->json(['status' => 'failure', 'message' => 'FrapPay credentials not configured']);
        }

        $ctime = now();
        $member = Member::where('user_id', $user_id)->first();
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
            'payoutcallbackurl' => $member->payoutcallbackurl ?? '',
            'client_id' => $client_id,
            'mode' => $mode,
            'order_token' => 'FPITMP' . time(),
        ]);

        $merchantRef = $this->fpLibrary->buildPayinRef($gatewayOrderId);
        Gatewayorder::where('id', $gatewayOrderId)->update([
            'order_token' => $merchantRef,
            'client_id' => ($mode === 'WEB' || empty($client_id)) ? $merchantRef : $client_id,
        ]);

        $intent = $this->fpLibrary->createPayin([
            'amount' => (float)$amount,
            'customerName' => (string)$name,
            'method' => 'UPI',
            'referenceId' => $merchantRef,
            'remark' => 'Add Money - ' . $gatewayOrderId,
            'callback_url' => $this->absoluteHttpsUrl('api/call-back/frappay-payin'),
        ], $gatewayOrderId);

        if (!($intent['ok'] ?? false)) {
            Gatewayorder::where('id', $gatewayOrderId)->update(['status_id' => 2]);
            return response()->json([
                'status' => 'failure',
                'message' => $intent['message'] ?? 'Failed to create FrapPay order',
            ]);
        }

        $txnId = (string)($intent['txnId'] ?? '');
        $qrLink = (string)($intent['qr_link'] ?? '');
        $providerStatus = (string)($intent['status'] ?? 'PENDING');
        $uatSimulated = (bool)($intent['uat_simulated'] ?? FrapPayLibrary::isUatSimulatedPayin($intent));
        $environment = (string)($intent['environment'] ?? '');
        $accessKey = (string)($intent['access_key'] ?? '');

        $user = User::find($user_id);
        $reportId = $this->createPendingPayinReport(
            Gatewayorder::find($gatewayOrderId),
            $user,
            $txnId ?: $merchantRef,
            $ctime
        );
        Gatewayorder::where('id', $gatewayOrderId)->update([
            'report_id' => $reportId,
            'remark' => $txnId,
            // Keep access key for later QR retry if needed (prefix-safe).
            'purpose' => $accessKey !== '' ? ('Add Money|' . $accessKey) : 'Add Money',
        ]);

        if ($providerStatus === 'FAILED') {
            $this->markFailed(Gatewayorder::find($gatewayOrderId), $intent['message'] ?? 'Failed');
            return response()->json(['status' => 'failure', 'message' => $intent['message'] ?? 'Payin failed']);
        }

        // UAT: FrapPay simulates collection immediately and never returns QR / accessKey.
        // Credit wallet (provider already SUCCESS) but expose sandbox flag so UI does not pretend a payment link was generated.
        if ($uatSimulated || ($providerStatus === 'SUCCESS' && $qrLink === '' && $accessKey === '')) {
            $credit = $this->creditGatewayOrder(
                Gatewayorder::find($gatewayOrderId),
                (float)$amount,
                $txnId,
                $txnId,
                $ctime,
                'UAT-simulated'
            );
            return response()->json([
                'status' => 'success',
                'message' => 'UAT sandbox: FrapPay simulated this payin (no QR / payment link in UAT). Amount credited.',
                'data' => [
                    'txnid' => $gatewayOrderId,
                    'order_token' => $merchantRef,
                    'transaction_id' => $txnId,
                    'qrString' => '',
                    'qrCodeUrl' => '',
                    'upi_link' => '',
                    'upi_intent' => '',
                    'status' => 'success',
                    'report_id' => $credit['report_id'] ?? $reportId,
                    'payment_status' => 'SUCCESS',
                    'uat_simulated' => true,
                    'environment' => $environment ?: 'uat',
                ],
            ]);
        }

        // Real collection already confirmed with a QR deeplink (rare).
        if ($providerStatus === 'SUCCESS' && $qrLink !== '') {
            $credit = $this->creditGatewayOrder(
                Gatewayorder::find($gatewayOrderId),
                (float)$amount,
                $txnId,
                $txnId,
                $ctime,
                'Create-order'
            );
            return response()->json([
                'status' => 'success',
                'message' => 'Payment already confirmed by FrapPay',
                'data' => [
                    'txnid' => $gatewayOrderId,
                    'order_token' => $merchantRef,
                    'transaction_id' => $txnId,
                    'qrString' => $qrLink,
                    'upi_link' => $qrLink,
                    'status' => 'success',
                    'report_id' => $credit['report_id'] ?? $reportId,
                    'payment_status' => 'SUCCESS',
                    'uat_simulated' => false,
                    'environment' => $environment,
                    'needs_qr' => false,
                ],
            ]);
        }

        // Hosted Easebuzz checkout (seamless SUVA QR is not authorised for this merchant).
        $checkoutUrl = $accessKey !== ''
            ? $this->fpLibrary->hostedCheckoutUrl($accessKey, $environment ?: 'production')
            : '';
        $localCheckoutUrl = $accessKey !== ''
            ? url('agent/add-money/v11/checkout/' . $gatewayOrderId)
            : '';

        // Prefer native UPI deeplink QR; else QR of hosted checkout URL (opens Easebuzz pay page).
        // Use SVG (default) — PNG requires Imagick which is often missing on localhost.
        $finalPaymentUrl = $checkoutUrl !== '' ? $checkoutUrl : $localCheckoutUrl;
        $qrPayload = $qrLink !== '' ? $qrLink : $finalPaymentUrl;
        $qrCodeUrl = $this->buildQrDataUri($qrPayload);

        return response()->json([
            'status' => 'success',
            'message' => $finalPaymentUrl !== ''
                ? 'Order created. Scan QR or open payment page to pay via UPI.'
                : 'Order created and pending at FrapPay.',
            'data' => [
                'txnid' => $gatewayOrderId,
                'order_token' => $merchantRef,
                'transaction_id' => $txnId,
                'qrString' => $qrPayload,
                'qrCodeUrl' => $qrCodeUrl,
                'upi_link' => $qrLink,
                'upi_intent' => $qrLink,
                'status' => 'pending',
                'report_id' => $reportId,
                'payment_status' => 'PENDING',
                'uat_simulated' => false,
                'environment' => $environment,
                'needs_qr' => false,
                'has_access_key' => $accessKey !== '',
                'access_key' => $accessKey,
                'payment_page_url' => $finalPaymentUrl,
                'auto_open_payment' => $finalPaymentUrl !== '',
                'qr_is_checkout_url' => $qrLink === '' && $finalPaymentUrl !== '',
            ],
        ]);
    }

    private function buildQrDataUri(string $payload): string
    {
        $payload = trim($payload);
        if ($payload === '') {
            return '';
        }

        try {
            $svg = (string)QrCode::size(220)->margin(1)->generate($payload);
            return 'data:image/svg+xml;base64,' . base64_encode($svg);
        } catch (\Throwable $e) {
            Log::warning('FrapPay QR SVG generate failed', ['error' => $e->getMessage()]);
        }

        // Last-resort remote QR image (works without Imagick/GD).
        return 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . rawurlencode($payload);
    }

    private function accessKeyFromOrder(Gatewayorder $order): string
    {
        $purpose = (string)($order->purpose ?? '');
        if (str_starts_with($purpose, 'Add Money|')) {
            return trim(substr($purpose, strlen('Add Money|')));
        }
        return '';
    }

    public function fetchQr(Request $request)
    {
        $gatewayOrderId = (int)$request->input('txnid', 0);
        $order = Gatewayorder::where('id', $gatewayOrderId)
            ->where('user_id', Auth::id())
            ->where('api_id', $this->api_id)
            ->first();

        if (!$order) {
            return response()->json(['status' => 'failure', 'message' => 'Order not found']);
        }

        $accessKey = $this->accessKeyFromOrder($order);
        if ($accessKey === '') {
            return response()->json([
                'status' => 'failure',
                'message' => 'No payment access key for this order',
                'data' => [
                    'payment_page_url' => '',
                    'qrString' => '',
                ],
            ]);
        }

        $environment = str_contains(strtolower((string)optional(Api::find($this->api_id))->base_url), '/uat')
            ? 'uat'
            : 'production';
        $creds = json_decode(optional(Api::find($this->api_id))->credentials);
        if (!empty($creds->base_url) && str_contains(strtolower((string)$creds->base_url), '/uat')) {
            $environment = 'uat';
        }

        $checkoutUrl = $this->fpLibrary->hostedCheckoutUrl($accessKey, $environment);

        return response()->json([
            'status' => 'success',
            'message' => 'Open payment page to complete UPI',
            'data' => [
                'txnid' => $order->id,
                'qrString' => $checkoutUrl,
                'qrCodeUrl' => $this->buildQrDataUri($checkoutUrl),
                'upi_link' => '',
                'upi_intent' => '',
                'payment_page_url' => $checkoutUrl,
                'access_key' => $accessKey,
                'payment_status' => 'pending',
                'auto_open_payment' => false,
                'qr_is_checkout_url' => true,
            ],
        ]);
    }

    public function checkout(Request $request, $id)
    {
        $order = Gatewayorder::where('id', (int)$id)
            ->where('user_id', Auth::id())
            ->where('api_id', $this->api_id)
            ->first();

        if (!$order) {
            abort(404);
        }

        $accessKey = $this->accessKeyFromOrder($order);
        if ($accessKey === '') {
            return response('Payment access key missing for this order.', 400);
        }

        $environment = 'production';
        $creds = json_decode(optional(Api::find($this->api_id))->credentials);
        if (!empty($creds->base_url) && str_contains(strtolower((string)$creds->base_url), '/uat')) {
            $environment = 'uat';
        }

        $checkoutUrl = $this->fpLibrary->hostedCheckoutUrl($accessKey, $environment);
        if ($checkoutUrl === '') {
            return response('Unable to build payment page URL.', 400);
        }

        return redirect()->away($checkoutUrl);
    }

    public function webOrderStatus(Request $request)
    {
        $gatewayOrderId = (int)$request->input('txnid', 0);
        $order = Gatewayorder::where('id', $gatewayOrderId)
            ->where('user_id', Auth::id())
            ->where('api_id', $this->api_id)
            ->first();

        if (!$order) {
            return response()->json(['status' => 'failure', 'message' => 'Order not found']);
        }

        if ((int)$order->status_id === 1) {
            return response()->json(['ok' => true, 'status' => 'success', 'payment_status' => 'success', 'report_id' => $order->report_id]);
        }
        if ((int)$order->status_id === 2) {
            return response()->json(['ok' => true, 'status' => 'success', 'payment_status' => 'failed', 'report_id' => $order->report_id]);
        }

        $txnId = (string)($order->remark ?: '');
        if ($txnId !== '') {
            $synced = $this->syncPendingOrder($order);
            $order->refresh();
            if ($synced || (int)$order->status_id === 1) {
                return response()->json(['ok' => true, 'status' => 'success', 'payment_status' => 'success', 'report_id' => $order->report_id]);
            }
            if ((int)$order->status_id === 2) {
                return response()->json(['ok' => true, 'status' => 'success', 'payment_status' => 'failed', 'report_id' => $order->report_id]);
            }
        }

        return response()->json(['ok' => true, 'status' => 'success', 'payment_status' => 'pending', 'report_id' => $order->report_id]);
    }

    public function syncPendingOrder(Gatewayorder $gatewayOrder): bool
    {
        $txnId = (string)($gatewayOrder->remark ?: '');
        if ($txnId === '' || (int)$gatewayOrder->status_id !== 3) {
            return (int)$gatewayOrder->status_id === 1;
        }

        $status = $this->fpLibrary->getTransactionStatus($txnId, $gatewayOrder->report_id);
        if (($status['status'] ?? '') === 'SUCCESS') {
            $credit = $this->creditGatewayOrder(
                $gatewayOrder,
                (float)($status['amount'] > 0 ? $status['amount'] : $gatewayOrder->amount),
                (string)($status['utr'] ?: $txnId),
                $txnId,
                now(),
                'Status-check'
            );
            return (bool)($credit['success'] ?? false);
        }
        if (($status['status'] ?? '') === 'FAILED') {
            $this->markFailed($gatewayOrder, (string)($status['message'] ?? 'Failed'));
            return false;
        }

        // FrapPay partner status often lags behind dashboard/wallet. Reconcile from payin wallet.
        $reconciledIds = $this->reconcilePendingFromPartnerWallet();
        $gatewayOrder->refresh();

        return in_array((int)$gatewayOrder->id, $reconciledIds, true) || (int)$gatewayOrder->status_id === 1;
    }

    /**
     * Auto-credit pending payins when FrapPay payin wallet already holds the money
     * but GET /transactions/{txnId} still returns pending (known FrapPay lag/bug).
     *
     * Uses a wallet watermark: on increase, exact-match pending order amounts to the delta.
     * First run bootstraps against the full partner payin wallet balance.
     *
     * @return int[] credited gateway order ids
     */
    public function reconcilePendingFromPartnerWallet(): array
    {
        $lockKey = 'frappay:wallet-reconcile-lock';
        if (!Cache::add($lockKey, 1, 45)) {
            return [];
        }

        try {
            $wallet = $this->fpLibrary->getPayinWalletBalance();
            if (!($wallet['ok'] ?? false)) {
                return [];
            }

            $partnerPayinBalance = round((float)$wallet['payin'], 2);
            $watermarkKey = 'frappay:payin_wallet_watermark';
            $watermark = Cache::get($watermarkKey);
            $hasWatermark = $watermark !== null && $watermark !== '';

            $target = $hasWatermark
                ? round($partnerPayinBalance - (float)$watermark, 2)
                : $partnerPayinBalance;

            // Wallet dropped (refund/adjustment) — just move watermark down.
            if ($hasWatermark && $target < 0) {
                Cache::forever($watermarkKey, $partnerPayinBalance);
                return [];
            }

            if ($target < 1) {
                if (!$hasWatermark) {
                    Cache::forever($watermarkKey, $partnerPayinBalance);
                }
                return [];
            }

            $pendingOrders = Gatewayorder::where('api_id', $this->api_id)
                ->where('status_id', 3)
                ->whereNotNull('remark')
                ->where('remark', '!=', '')
                ->where('created_at', '>=', now()->subDays(14))
                ->orderBy('id', 'DESC')
                ->limit(40)
                ->get();

            if ($pendingOrders->isEmpty()) {
                Cache::forever($watermarkKey, $partnerPayinBalance);
                return [];
            }

            $items = [];
            foreach ($pendingOrders as $order) {
                $items[] = [
                    'id' => (int)$order->id,
                    'amount' => round((float)$order->amount, 2),
                    'txn' => (string)$order->remark,
                ];
            }

            // Prefer newer ids when multiple subsets exist (just-paid order is usually newest).
            usort($items, fn ($a, $b) => $b['id'] <=> $a['id']);

            $subset = $this->findExactAmountSubset($items, $target);
            if ($subset === []) {
                // Keep old watermark so a later poll can match once pending set is complete.
                if (!$hasWatermark) {
                    Cache::forever($watermarkKey, $partnerPayinBalance);
                }
                return [];
            }

            $creditedIds = [];
            foreach ($subset as $row) {
                $order = Gatewayorder::find($row['id']);
                if (!$order || (int)$order->status_id !== 3) {
                    continue;
                }

                $txnId = (string)($order->remark ?: '');
                $credit = $this->creditGatewayOrder(
                    $order,
                    (float)$order->amount,
                    $txnId,
                    $txnId,
                    now(),
                    'Wallet-reconcile'
                );
                if ($credit['success'] ?? false) {
                    $creditedIds[] = (int)$order->id;
                    Log::info('FrapPay wallet reconcile credited order', [
                        'gateway_order_id' => $order->id,
                        'txn' => $txnId,
                        'amount' => $order->amount,
                        'target' => $target,
                        'wallet' => $partnerPayinBalance,
                    ]);
                }
            }

            if ($creditedIds !== []) {
                Cache::forever($watermarkKey, $partnerPayinBalance);
            }

            return $creditedIds;
        } catch (\Throwable $e) {
            Log::error('FrapPay wallet reconcile failed', ['error' => $e->getMessage()]);
            return [];
        } finally {
            Cache::forget($lockKey);
        }
    }

    /**
     * @param array<int, array{id:int,amount:float,txn:string}> $items
     * @return array<int, array{id:int,amount:float,txn:string}>
     */
    private function findExactAmountSubset(array $items, float $target): array
    {
        $target = round($target, 2);
        $n = count($items);
        if ($n === 0 || $target < 1) {
            return [];
        }

        $best = null;
        $search = function (int $start, float $remaining, array $chosen) use (&$search, &$best, $items, $n) {
            if (abs($remaining) < 0.009) {
                if ($best === null || count($chosen) < count($best)) {
                    $best = $chosen;
                }
                return;
            }
            if ($remaining < 0 || $start >= $n || ($best !== null && count($chosen) >= count($best))) {
                return;
            }
            for ($i = $start; $i < $n; $i++) {
                $amt = $items[$i]['amount'];
                if ($amt - $remaining > 0.009) {
                    continue;
                }
                $chosen[] = $items[$i];
                $search($i + 1, round($remaining - $amt, 2), $chosen);
                array_pop($chosen);
                if ($best !== null && count($best) === 1) {
                    return;
                }
            }
        };

        $search(0, $target, []);
        return $best ?? [];
    }

    public function statusEnquiryApi(Request $request)
    {
        $rules = ['client_id' => 'required'];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }

        $order = Gatewayorder::where('api_id', $this->api_id)
            ->where(function ($q) use ($request) {
                $q->where('client_id', $request->client_id)->orWhere('order_token', $request->client_id);
            })
            ->where('user_id', Auth::id())
            ->orderBy('id', 'DESC')
            ->first();

        if (!$order) {
            return response()->json(['status' => 'failure', 'message' => 'Order not found']);
        }

        if ((int)$order->status_id === 3) {
            $this->syncPendingOrder($order);
            $order->refresh();
        }

        $statusMap = [1 => 'success', 2 => 'failed', 3 => 'pending', 6 => 'success'];
        $paymentStatus = $statusMap[(int)$order->status_id] ?? 'pending';
        $report = $order->report_id ? Report::find($order->report_id) : null;

        return response()->json([
            'status' => 'success',
            'message' => 'Status fetched',
            'data' => [
                'client_id' => $order->client_id,
                'order_token' => $order->order_token,
                'transaction_id' => $order->remark,
                'amount' => (float)$order->amount,
                'status' => $paymentStatus,
                'utr' => $report->txnid ?? '',
                'report_id' => $order->report_id,
            ],
        ]);
    }

    public function payinCallback(Request $request)
    {
        $ctime = now();
        $payload = $request->all();
        if (empty($payload)) {
            $decoded = json_decode((string)$request->getContent(), true);
            if (is_array($decoded)) {
                $payload = $decoded;
            }
        }

        if (isset($payload['data']) && is_array($payload['data'])) {
            $payload = array_merge($payload['data'], $payload);
        }

        Apiresponse::insertGetId([
            'message' => json_encode($payload),
            'api_type' => $this->api_id,
            'response_type' => 'call_back',
            'request_message' => substr((string)($request->getContent() ?: json_encode($request->query())), 0, 65000),
            'ip_address' => $request->ip(),
            'created_at' => $ctime,
        ]);

        $txnId = (string)(
            $payload['txnId']
            ?? $payload['txn_id']
            ?? $payload['transaction_id']
            ?? $payload['transactionId']
            ?? ''
        );
        $referenceId = (string)(
            $payload['referenceId']
            ?? $payload['reference_id']
            ?? $payload['merchant_refid']
            ?? $payload['merchantRefId']
            ?? $payload['order_token']
            ?? ''
        );
        $statusRaw = (string)(
            $payload['status']
            ?? $payload['paymentStatus']
            ?? $payload['payment_status']
            ?? $payload['txnStatus']
            ?? ''
        );
        $status = FrapPayLibrary::normalizeStatus($statusRaw);
        $amount = (float)($payload['amount'] ?? $payload['txnAmount'] ?? $payload['paidAmount'] ?? 0);
        $utr = (string)(
            $payload['utr']
            ?? $payload['UTR']
            ?? $payload['bankRef']
            ?? $payload['bank_ref']
            ?? $payload['providerTxnId']
            ?? $txnId
        );

        $order = null;
        if ($referenceId !== '') {
            $order = Gatewayorder::where('api_id', $this->api_id)
                ->where(function ($q) use ($referenceId) {
                    $q->where('order_token', $referenceId)->orWhere('client_id', $referenceId);
                })->orderBy('id', 'DESC')->first();
        }
        if (!$order && $txnId !== '') {
            $order = Gatewayorder::where('api_id', $this->api_id)->where('remark', $txnId)->orderBy('id', 'DESC')->first();
        }

        if (!$order) {
            $this->reconcilePendingFromPartnerWallet();
            return response()->json(['received' => true, 'status' => false, 'message' => 'Order not found'], 404);
        }

        Apiresponse::where('api_type', $this->api_id)
            ->where('response_type', 'call_back')
            ->whereNull('report_id')
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->update(['report_id' => $order->report_id ?: $order->id]);

        if ($status === 'SUCCESS') {
            $this->creditGatewayOrder(
                $order,
                $amount > 0 ? $amount : (float)$order->amount,
                $utr,
                $txnId ?: (string)$order->remark,
                $ctime,
                'Call-back'
            );
        } elseif ($status === 'FAILED') {
            $this->markFailed($order, (string)($payload['message'] ?? 'Failed'));
        } else {
            // Unknown/pending callback — sync status + wallet reconcile.
            $this->syncPendingOrder($order);
        }

        return response()->json(['received' => true, 'status' => true, 'message' => 'Callback processed']);
    }

    public function viewQrcode(Request $request)
    {
        return $this->fetchQr($request);
    }
}
