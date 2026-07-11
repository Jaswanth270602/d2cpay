<?php

namespace App\library {

    use App\Models\Api;
    use App\Models\Apiresponse;
    use App\Library\RefundLibrary;
    use Helpers;
    use Illuminate\Support\Facades\Cache;

    class RojgaarPeLibrary
    {
        public const PAYIN_MIN = 300;
        public const PAYIN_MAX = 20000;
        public const PAYOUT_MIN = 500;
        public const PAYOUT_MAX = 30000;

        private $api_id;
        private $base_url;
        private $loginId;
        private $payinSecretKey;
        private $payoutSecretKey;

        public function __construct()
        {
            $this->api_id = 17;
            $credentials = json_decode(optional(Api::find($this->api_id))->credentials);
            $this->base_url = rtrim($credentials->base_url ?? 'https://rojgaarpe.com', '/');
            $this->loginId = (string)($credentials->login_id ?? '');
            $this->payinSecretKey = (string)($credentials->secret_key ?? '');
            $this->payoutSecretKey = (string)($credentials->payout_secret_key ?? $credentials->secret_key ?? '');
        }

        public function buildPayinMerchantRef(int $gatewayOrderId): string
        {
            return 'RPI' . $gatewayOrderId . date('His');
        }

        public function buildPayoutMerchantRef($insert_id): string
        {
            return 'RPO' . date('ymd') . str_pad((string)$insert_id, 11, '0', STR_PAD_LEFT);
        }

        public static function isTerminalPayinStatus(string $status): bool
        {
            $normalized = self::normalizePayinWebhookStatus($status);
            return in_array($normalized, ['SUCCESS', 'FAILED'], true);
        }

        public static function resolvePayinLogType(string $status): string
        {
            return self::isTerminalPayinStatus($status) ? 'call_back' : 'status_check';
        }

        public static function normalizePayinWebhookStatus(string $status): string
        {
            $status = strtoupper(trim($status));
            if (in_array($status, ['COMPLETED', 'SUCCESS', 'SUCCESSFUL', 'PAID'], true)) {
                return 'SUCCESS';
            }
            if (in_array($status, ['FAILED', 'FAILURE', 'CANCELLED'], true)) {
                return 'FAILED';
            }
            return 'PENDING';
        }

        public static function normalizePayoutStatus(string $status): string
        {
            $status = strtoupper(trim($status));
            if ($status === 'SUCCESS') {
                return 'SUCCESS';
            }
            if (in_array($status, ['FAILED', 'FAILURE', 'CANCELLED'], true)) {
                return 'FAILED';
            }
            return 'PENDING';
        }

        public static function parseIncomingCallback(\Illuminate\Http\Request $request): array
        {
            $payload = $request->all();
            $raw = trim((string)$request->getContent());

            if (self::isEffectivelyEmptyPayload($payload) && $raw !== '') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $payload = $decoded;
                } else {
                    parse_str($raw, $formPayload);
                    if (is_array($formPayload) && !empty($formPayload)) {
                        $payload = $formPayload;
                    }
                }
            }

            if (isset($payload['data']) && is_array($payload['data'])) {
                $payload = array_merge($payload, $payload['data']);
            }

            return self::normalizePayinPayload(is_array($payload) ? $payload : []);
        }

        public static function parseIncomingPayoutCallback(\Illuminate\Http\Request $request): array
        {
            $payload = self::parseIncomingCallback($request);
            $merchantRef = (string)(
                $payload['merchant_refid']
                ?? $payload['merchantOrderNo']
                ?? $payload['reference_id']
                ?? ''
            );
            $status = self::normalizePayoutStatus((string)($payload['status'] ?? ''));

            $payload['merchant_refid'] = $merchantRef;
            $payload['merchantOrderNo'] = $merchantRef;
            $payload['status'] = $status;
            $payload['orderStatus'] = $status;
            $payload['utr'] = (string)($payload['utr'] ?? '');
            $payload['provider_txn_id'] = (string)($payload['provider_txn_id'] ?? $payload['txn_id'] ?? '');
            $payload['amount'] = is_numeric($payload['amount'] ?? null) ? (float)$payload['amount'] : 0;

            return $payload;
        }

        public static function buildCallbackAudit(\Illuminate\Http\Request $request, array $payload): array
        {
            return [
                'method' => $request->method(),
                'content_type' => $request->header('Content-Type'),
                'query' => $request->query(),
                'parsed' => $payload,
                'raw' => (string)$request->getContent(),
                'ip' => $request->ip(),
            ];
        }

        public static function normalizePayinPayload(array $payload): array
        {
            $merchantRef = (string)(
                $payload['merchant_refid']
                ?? $payload['merchantOrderNo']
                ?? $payload['reference_id']
                ?? $payload['txn_id']
                ?? ''
            );
            $statusRaw = (string)($payload['status'] ?? $payload['orderStatus'] ?? '');
            $status = self::normalizePayinWebhookStatus($statusRaw);
            $utr = (string)($payload['utr'] ?? $payload['UTR'] ?? '');
            $amount = $payload['amount'] ?? 0;
            $providerTxn = (string)($payload['provider_txn'] ?? $payload['provider_txn_id'] ?? $payload['txn_id'] ?? '');

            $payload['merchant_refid'] = $merchantRef;
            $payload['merchantOrderNo'] = $merchantRef;
            $payload['reference_id'] = $merchantRef;
            $payload['status'] = $status;
            $payload['orderStatus'] = $status;
            $payload['status_raw'] = $statusRaw;
            $payload['utr'] = $utr;
            $payload['amount'] = is_numeric($amount) ? (float)$amount : 0;
            $payload['orderId'] = $providerTxn;
            $payload['provider_txn'] = $providerTxn;

            return $payload;
        }

        public static function isEffectivelyEmptyPayload($payload): bool
        {
            return !is_array($payload) || $payload === [] || $payload === ['' => null];
        }

        public function getPayinToken(bool $forceRefresh = false): string
        {
            $cacheKey = 'rojgaarpe:payin_token:' . $this->loginId;

            if (!$forceRefresh && Cache::has($cacheKey)) {
                return (string)Cache::get($cacheKey);
            }

            if ($this->payinSecretKey === '' || $this->loginId === '') {
                return '';
            }

            $url = $this->base_url . '/api/v2/generate/token';
            $headers = [
                'secret_key: ' . $this->payinSecretKey,
                'Content-Type: application/json',
            ];
            $body = json_encode(['loginID' => $this->loginId]);
            $response = Helpers::pay_curl_post($url, $headers, $body, 'POST');
            $res = json_decode($response, true);
            $token = is_array($res) ? (string)($res['data']['token'] ?? '') : '';

            if ($token !== '') {
                Cache::put($cacheKey, $token, now()->addMinutes(9));
            }

            return $token;
        }

        public function getPayoutToken(bool $forceRefresh = false): string
        {
            $cacheKey = 'rojgaarpe:payout_token:' . $this->loginId;

            if (!$forceRefresh && Cache::has($cacheKey)) {
                return (string)Cache::get($cacheKey);
            }

            if ($this->payoutSecretKey === '' || $this->loginId === '') {
                return '';
            }

            $url = $this->base_url . '/api/v2/generatepayout/token';
            $headers = [
                'secret_key: ' . $this->payoutSecretKey,
                'Content-Type: application/json',
            ];
            $body = json_encode(['loginID' => $this->loginId]);
            $response = Helpers::pay_curl_post($url, $headers, $body, 'POST');
            $res = json_decode($response, true);
            $token = is_array($res) ? (string)($res['data']['token'] ?? '') : '';

            if ($token !== '') {
                Cache::put($cacheKey, $token, now()->addMinutes(9));
            }

            return $token;
        }

        public static function formatPayinCreateError(string $message, string $statusCode, string $loginId = ''): string
        {
            $message = trim($message);
            $statusCode = trim($statusCode);
            $loginSuffix = $loginId !== '' ? ' for login ID ' . $loginId : '';

            if (stripos($message, 'payin functionality is disabled') !== false) {
                return 'RojgaarPe merchant account error: Payin is disabled' . $loginSuffix
                    . '. Ask RojgaarPe support to enable Payin on your merchant account.';
            }

            if ($statusCode === '500' || stripos($message, 'null reference') !== false) {
                return 'RojgaarPe server error: merchant Payin setup is incomplete' . $loginSuffix
                    . '. Ask RojgaarPe to enable Payin, configure the commission slab, and assign a UPI provider.'
                    . ($message !== '' ? ' (' . $message . ')' : '');
            }

            if (stripos($message, 'commission slab') !== false) {
                return 'RojgaarPe merchant account error: Payin commission slab is not configured' . $loginSuffix
                    . '. Ask RojgaarPe to configure the Payin commission slab in their merchant dashboard.';
            }

            $knownCodes = [
                '104' => 'Invalid amount. RojgaarPe Payin allows amounts between ' . self::PAYIN_MIN . ' and ' . self::PAYIN_MAX . '.',
                '105' => 'Amount exceeds the RojgaarPe maximum limit.',
                '108' => 'Customer name is required.',
                '110' => 'Customer mobile number is required.',
                '111' => 'Invalid customer mobile number. Must be exactly 10 digits.',
            ];

            if ($statusCode !== '' && isset($knownCodes[$statusCode])) {
                return $knownCodes[$statusCode];
            }

            return $message !== '' ? $message : 'Payin create failed';
        }

        public function createPayinIntent(array $params, ?int $gatewayOrderId = null): array
        {
            $result = $this->requestPayinCreateIntent($params, $gatewayOrderId, false);
            if (($result['ok'] ?? false) || !($result['retry_token'] ?? false)) {
                return $result;
            }

            return $this->requestPayinCreateIntent($params, $gatewayOrderId, true);
        }

        private function requestPayinCreateIntent(array $params, ?int $gatewayOrderId, bool $forceRefresh): array
        {
            $token = $this->getPayinToken($forceRefresh);
            if ($token === '') {
                return ['ok' => false, 'message' => 'Unable to generate payin token'];
            }

            $url = $this->base_url . '/api/v2/payinsection/create-intent';
            $headers = [
                'secret_key: ' . $this->payinSecretKey,
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ];
            $body = json_encode($params);
            $response = Helpers::pay_curl_post($url, $headers, $body, 'POST');

            Apiresponse::insertGetId([
                'message' => $response,
                'api_type' => $this->api_id,
                'response_type' => 'payin_create',
                'request_message' => $url . '?' . $body,
                'report_id' => $gatewayOrderId,
            ]);

            $res = json_decode($response, true);
            if (!is_array($res)) {
                return ['ok' => false, 'message' => 'Invalid response from RojgaarPe'];
            }

            if (!($res['status'] ?? false)) {
                $message = (string)($res['message'] ?? 'Payin create failed');
                $statusCode = (string)($res['StatusCode'] ?? $res['statusCode'] ?? '');
                $message = self::formatPayinCreateError($message, $statusCode, $this->loginId);
                $retryToken = !$forceRefresh && in_array($statusCode, ['102', '105'], true);

                return [
                    'ok' => false,
                    'message' => $message,
                    'status_code' => $statusCode,
                    'retry_token' => $retryToken,
                    'raw' => $res,
                ];
            }

            $data = is_array($res['data'] ?? null) ? $res['data'] : [];

            return [
                'ok' => true,
                'message' => (string)($res['message'] ?? 'Order created'),
                'qr' => (string)($data['qr'] ?? ''),
                'client_ref_id' => (string)($data['client_ref_id'] ?? ''),
                'provider_txn' => (string)($data['provider_txn'] ?? ''),
                'txn_id' => (string)($data['txn_id'] ?? ''),
                'raw' => $res,
            ];
        }

        public static function isPayinStatusPollNoise(string $apiMessage, ?string $responseType = null): bool
        {
            if ($responseType === 'status_check') {
                return true;
            }

            $normalized = strtolower(trim($apiMessage));
            if ($normalized === '') {
                return false;
            }

            $noiseFragments = [
                'invalid loginid',
                'invalid token',
                'loginid required',
                'authorization token required',
                'transaction not found',
            ];

            foreach ($noiseFragments as $fragment) {
                if (str_contains($normalized, $fragment)) {
                    return true;
                }
            }

            return false;
        }

        public static function pendingPayinDisplayReason(): string
        {
            return 'Awaiting payment confirmation';
        }

        public function getPayinStatus(string $merchantRef, ?int $gatewayOrderId = null): array
        {
            if ($merchantRef === '') {
                return [];
            }

            $response = $this->requestPayinStatus($merchantRef, $gatewayOrderId, false);
            if ($this->shouldRetryPayinStatus($response)) {
                return $this->requestPayinStatus($merchantRef, $gatewayOrderId, true);
            }

            return $response;
        }

        private function shouldRetryPayinStatus(array $response): bool
        {
            if ($response === []) {
                return true;
            }

            if ($response['status'] ?? false) {
                return false;
            }

            $statusCode = (string)($response['StatusCode'] ?? $response['statusCode'] ?? '');

            return in_array($statusCode, ['102', '105'], true);
        }

        private function requestPayinStatus(string $merchantRef, ?int $gatewayOrderId, bool $forceRefresh): array
        {
            $token = $this->getPayinToken($forceRefresh);
            if ($token === '') {
                return [];
            }

            $url = $this->base_url . '/api/v2/create-intent/status';
            $headers = [
                'secret_key: ' . $this->payinSecretKey,
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ];
            $body = json_encode([
                'loginID' => $this->loginId,
                'merchant_refid' => $merchantRef,
            ]);
            $response = Helpers::pay_curl_post($url, $headers, $body, 'POST');

            Apiresponse::insertGetId([
                'message' => $response,
                'api_type' => $this->api_id,
                'response_type' => 'status_check',
                'request_message' => $url . '?' . $body,
                'report_id' => $gatewayOrderId,
            ]);

            $res = json_decode($response, true);

            return is_array($res) ? $res : [];
        }

        public function parsePayinStatusResponse(array $res): array
        {
            $data = is_array($res['data'] ?? null) ? $res['data'] : [];
            $status = self::normalizePayinWebhookStatus((string)($data['status'] ?? ''));

            return [
                'status' => $status,
                'utr' => (string)($data['utr'] ?? ''),
                'orderId' => (string)($data['txn_id'] ?? $data['provider_txn'] ?? ''),
                'amount' => (float)($data['amount'] ?? 0),
                'remark' => (string)($data['remark'] ?? $res['message'] ?? ''),
            ];
        }

        public function confirmPayinSuccessFromApi(string $merchantRef, ?int $gatewayOrderId = null): ?array
        {
            if ($merchantRef === '') {
                return null;
            }

            $res = $this->getPayinStatus($merchantRef, $gatewayOrderId);
            $parsed = $this->parsePayinStatusResponse($res);
            if ($parsed['status'] === 'SUCCESS') {
                $parsed['merchant_refid'] = $merchantRef;
                return $parsed;
            }

            return null;
        }

        public function transferNow($user_id, $mobile_number, $amount, $beneficiary_name, $account_number, $ifsc_code, $insert_id)
        {
            if ($this->payoutSecretKey === '' || $this->loginId === '') {
                return ['status_id' => 3, 'txnid' => 'RojgaarPe payout credentials missing', 'payid' => ''];
            }

            $amount = (float)$amount;
            if ($amount < self::PAYOUT_MIN || $amount > self::PAYOUT_MAX) {
                return [
                    'status_id' => 2,
                    'txnid' => 'Amount must be between ' . self::PAYOUT_MIN . ' and ' . self::PAYOUT_MAX,
                    'payid' => '',
                ];
            }

            $token = $this->getPayoutToken();
            if ($token === '') {
                return ['status_id' => 3, 'txnid' => 'Unable to generate payout token', 'payid' => ''];
            }

            $merchantRef = $this->buildPayoutMerchantRef($insert_id);
            $bankName = $this->getBanknameByIfsc($ifsc_code);
            $callbackUrl = self::publicUrl('api/call-back/rojgaarpe-payout');

            $payload = [
                'amount' => (float)$amount,
                'merchant_refid' => $merchantRef,
                'customer_name' => (string)$beneficiary_name,
                'customer_mobile' => (string)$mobile_number,
                'account_holder_name' => (string)$beneficiary_name,
                'account_number' => (string)$account_number,
                'ifsc' => (string)$ifsc_code,
                'bank_name' => (string)$bankName,
                'mode' => 'IMPS',
                'callback_url' => $callbackUrl,
            ];

            $url = $this->base_url . '/api/v2/payout/initiate-transfer';
            $headers = [
                'secret_key: ' . $this->payoutSecretKey,
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ];
            $response = Helpers::pay_curl_post($url, $headers, json_encode($payload), 'POST');

            Apiresponse::insertGetId([
                'message' => $response,
                'api_type' => $this->api_id,
                'response_type' => 'payout_create',
                'report_id' => $insert_id,
                'request_message' => $url . '?' . json_encode($payload),
            ]);

            $res = json_decode($response, true);
            if (!is_array($res)) {
                return ['status_id' => 3, 'txnid' => 'Invalid response', 'payid' => $merchantRef];
            }

            if (!($res['status'] ?? false)) {
                return ['status_id' => 2, 'txnid' => (string)($res['message'] ?? 'Payout failed'), 'payid' => $merchantRef];
            }

            $data = is_array($res['data'] ?? null) ? $res['data'] : [];
            $providerStatus = self::normalizePayoutStatus((string)($data['status'] ?? 'PENDING'));
            $utr = (string)($data['utr'] ?? '');
            $providerTxnId = (string)($data['provider_txn_id'] ?? '');

            if ($providerStatus === 'SUCCESS') {
                return ['status_id' => 1, 'txnid' => $utr ?: $providerTxnId, 'payid' => $merchantRef];
            }
            if ($providerStatus === 'FAILED') {
                return ['status_id' => 2, 'txnid' => (string)($res['message'] ?? 'Payout failed'), 'payid' => $merchantRef];
            }

            return ['status_id' => 3, 'txnid' => $utr, 'payid' => $merchantRef];
        }

        public function get_transaction_status($insert_id)
        {
            $providerTxnId = $this->resolvePayoutProviderTxnId($insert_id);
            if ($providerTxnId === '') {
                return ['status_id' => 3, 'txnid' => '', 'payid' => $this->buildPayoutMerchantRef($insert_id)];
            }

            $token = $this->getPayoutToken();
            if ($token === '') {
                return ['status_id' => 3, 'txnid' => 'Unable to generate payout token', 'payid' => ''];
            }

            $url = $this->base_url . '/api/v2/payout/payment-status2';
            $headers = [
                'secret_key: ' . $this->payoutSecretKey,
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ];
            $body = json_encode(['TxnID' => $providerTxnId]);
            $response = Helpers::pay_curl_post($url, $headers, $body, 'POST');

            Apiresponse::insertGetId([
                'message' => $response,
                'api_type' => $this->api_id,
                'response_type' => 'status_check',
                'report_id' => $insert_id,
                'request_message' => $url . '?' . $body,
            ]);

            $res = json_decode($response, true);
            if (!is_array($res) || !($res['status'] ?? false)) {
                return ['status_id' => 3, 'txnid' => '', 'payid' => $this->buildPayoutMerchantRef($insert_id)];
            }

            $data = is_array($res['data'] ?? null) ? $res['data'] : [];
            $providerStatus = self::normalizePayoutStatus((string)($data['status'] ?? $data['payment_status'] ?? ''));
            $utr = (string)($data['utr'] ?? '');
            $merchantRef = $this->buildPayoutMerchantRef($insert_id);

            if ($providerStatus === 'SUCCESS') {
                return ['status_id' => 1, 'txnid' => $utr, 'payid' => $merchantRef];
            }
            if ($providerStatus === 'FAILED') {
                return ['status_id' => 2, 'txnid' => (string)($data['remark'] ?? $res['message'] ?? 'Failed'), 'payid' => $merchantRef];
            }

            return ['status_id' => 3, 'txnid' => '', 'payid' => $merchantRef];
        }

        public function checkStatusByCron($insert_id)
        {
            $statusResponse = $this->get_transaction_status($insert_id);
            $statusId = (int)($statusResponse['status_id'] ?? 3);

            if ($statusId === 1) {
                $library = new RefundLibrary();
                return $library->update_transaction(1, $statusResponse['txnid'] ?? '', $insert_id, 'Check status');
            }
            if ($statusId === 2) {
                $library = new RefundLibrary();
                return $library->update_transaction(2, $statusResponse['txnid'] ?? 'Failed', $insert_id, 'Check status');
            }

            return null;
        }

        private function resolvePayoutProviderTxnId($insert_id): string
        {
            $log = Apiresponse::where('report_id', $insert_id)
                ->where('api_type', $this->api_id)
                ->where('response_type', 'payout_create')
                ->orderBy('id', 'DESC')
                ->first();

            if (!$log) {
                return '';
            }

            $res = json_decode((string)$log->message, true);
            if (!is_array($res)) {
                return '';
            }

            $data = is_array($res['data'] ?? null) ? $res['data'] : [];

            return (string)($data['provider_txn_id'] ?? '');
        }

        private function getBanknameByIfsc($ifsc_code): string
        {
            $url = 'https://ifsc.razorpay.com/' . rawurlencode((string)$ifsc_code);
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
            ]);
            $data = curl_exec($curl);
            curl_close($curl);
            $res = json_decode((string)$data);

            return (string)($res->BANK ?? 'BANK');
        }

        public static function publicBaseUrl(): string
        {
            foreach (['app.rojgaarpe_public_url', 'app.qpc_public_url'] as $configKey) {
                $configured = trim((string)config($configKey, ''));
                if ($configured !== '') {
                    return rtrim($configured, '/');
                }
            }

            $base = rtrim((string)config('app.url'), '/');
            if (str_starts_with($base, 'https://')) {
                return $base;
            }

            return 'https://d2cpay.co';
        }

        public static function publicUrl(string $path): string
        {
            return self::publicBaseUrl() . '/' . ltrim($path, '/');
        }
    }
}
