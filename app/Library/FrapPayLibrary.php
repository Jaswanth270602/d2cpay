<?php

namespace App\library {

    use App\Models\Api;
    use App\Models\Apiresponse;
    use App\Library\RefundLibrary;
    use Helpers;
    use Illuminate\Support\Facades\Cache;

    class FrapPayLibrary
    {
        public const PAYIN_MIN = 1;
        public const PAYIN_MAX = 100000;
        public const PAYOUT_MIN = 1;
        public const PAYOUT_MAX = 100000;

        private $api_id;
        private $base_url;
        private $apiPathPrefix;
        private $apiKey;
        private $secretKey;
        private $lastError = '';

        public function __construct()
        {
            $this->api_id = 18;
            $credentials = json_decode(optional(Api::find($this->api_id))->credentials);
            // Partner docs: UAT = /api/partner/uat , Production = /api/partner/v1
            $this->base_url = rtrim($credentials->base_url ?? 'https://frappay.in/api/partner/uat', '/');
            $this->apiKey = (string)($credentials->api_key ?? '');
            $this->secretKey = (string)($credentials->secret_key ?? '');
            $path = parse_url($this->base_url, PHP_URL_PATH);
            $this->apiPathPrefix = rtrim($path ?: '/api/partner/uat', '/');
        }

        public function getLastError(): string
        {
            return $this->lastError;
        }

        public function isUatEnvironment(): bool
        {
            return str_contains(strtolower($this->base_url), '/uat');
        }

        public static function isUatSimulatedPayin(array $intent): bool
        {
            $env = strtolower((string)($intent['environment'] ?? ''));
            $message = strtolower((string)($intent['message'] ?? ''));
            $accessKey = (string)($intent['access_key'] ?? '');
            $status = strtoupper((string)($intent['status'] ?? ''));

            if ($env === 'uat' && $status === 'SUCCESS' && $accessKey === '') {
                return true;
            }

            return str_contains($message, 'uat payin simulated');
        }

        public function buildPayinRef(int $gatewayOrderId): string
        {
            return 'FPI' . $gatewayOrderId . date('His');
        }

        public function buildPayoutRef($insertId): string
        {
            return 'FPO' . date('ymd') . str_pad((string)$insertId, 11, '0', STR_PAD_LEFT);
        }

        public static function normalizeStatus(string $status): string
        {
            $status = strtoupper(trim($status));
            if (in_array($status, ['SUCCESS', 'SUCCESSFUL', 'COMPLETED', 'PAID', 'CREDIT'], true)) {
                return 'SUCCESS';
            }
            if (in_array($status, ['FAILED', 'FAILURE', 'CANCELLED', 'CANCELED', 'REVERSED', 'REFUNDED'], true)) {
                return 'FAILED';
            }
            return 'PENDING';
        }

        public static function publicBaseUrl(): string
        {
            $configured = trim((string)config('app.frappay_public_url', ''));
            if ($configured === '') {
                $configured = trim((string)config('app.qpc_public_url', ''));
            }
            if ($configured !== '') {
                return rtrim($configured, '/');
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

        private function sign(string $method, string $apiPath, string $rawBody): array
        {
            $timestamp = (string)round(microtime(true) * 1000);
            $canonical = $timestamp . '.' . strtoupper($method) . '.' . $apiPath . '.' . $rawBody;
            $signature = hash_hmac('sha256', $canonical, $this->secretKey);

            return [
                'timestamp' => $timestamp,
                'signature' => $signature,
                'canonical' => $canonical,
            ];
        }

        /**
         * @return array{0:string|false,1:int,2:string} response, httpCode, curlError
         */
        private function curlExecute(string $url, string $method, array $headers, string $rawBody, bool $sendBody): array
        {
            $ch = curl_init($url);
            $opts = [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_CONNECTTIMEOUT => 20,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                CURLOPT_DNS_CACHE_TIMEOUT => 120,
            ];
            if ($sendBody) {
                $opts[CURLOPT_POSTFIELDS] = $rawBody;
            }
            curl_setopt_array($ch, $opts);
            $response = curl_exec($ch);
            $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = (string)curl_error($ch);
            curl_close($ch);

            // If DNS fails, retry once using resolved IPv4 + Host header (common localhost DNS flake).
            if ($response === false && (
                str_contains(strtolower($curlError), 'resolving')
                || str_contains(strtolower($curlError), 'could not resolve host')
            )) {
                $parts = parse_url($url);
                $host = (string)($parts['host'] ?? '');
                if ($host !== '') {
                    $ip = gethostbyname($host);
                    if ($ip !== '' && $ip !== $host && filter_var($ip, FILTER_VALIDATE_IP)) {
                        $scheme = $parts['scheme'] ?? 'https';
                        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
                        $path = $parts['path'] ?? '/';
                        $query = isset($parts['query']) ? '?' . $parts['query'] : '';
                        $ipUrl = $scheme . '://' . $ip . $port . $path . $query;
                        $headersWithHost = $headers;
                        $headersWithHost[] = 'Host: ' . $host;
                        $ch = curl_init($ipUrl);
                        $opts[CURLOPT_URL] = $ipUrl;
                        $opts[CURLOPT_HTTPHEADER] = $headersWithHost;
                        curl_setopt_array($ch, $opts);
                        $response = curl_exec($ch);
                        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        $curlError = (string)curl_error($ch);
                        curl_close($ch);
                    }
                }
            }

            return [$response, $httpCode, $curlError];
        }

        private function request(string $method, string $relativePath, $body = null, bool $useBearer = false, ?int $reportId = null, string $responseType = 'api'): array
        {
            $method = strtoupper($method);
            $apiPath = $this->apiPathPrefix . '/' . ltrim($relativePath, '/');
            $url = rtrim($this->base_url, '/') . '/' . ltrim($relativePath, '/');

            $rawBody = '';
            if ($method !== 'GET' && $method !== 'HEAD') {
                if ($body === null) {
                    $rawBody = '{}';
                } elseif (is_string($body)) {
                    $rawBody = $body;
                } else {
                    $rawBody = json_encode($body, JSON_UNESCAPED_SLASHES);
                }
            }

            $headers = [
                'Accept: application/json',
                'Content-Type: application/json',
            ];

            if ($useBearer) {
                $token = $this->getAccessToken();
                if ($token === '') {
                    $msg = $this->lastError !== '' ? $this->lastError : 'Unable to generate FrapPay token';
                    return [
                        'ok' => false,
                        'http_code' => 0,
                        'body' => '',
                        'json' => ['success' => false, 'message' => $msg],
                    ];
                }
                $headers[] = 'Authorization: Bearer ' . $token;
            } else {
                $signed = $this->sign($method, $apiPath, $rawBody === '' ? '{}' : $rawBody);
                // Token session docs: body is normally {}
                if ($rawBody === '') {
                    $rawBody = '{}';
                    $signed = $this->sign($method, $apiPath, $rawBody);
                }
                $headers[] = 'x-api-key: ' . $this->apiKey;
                $headers[] = 'x-timestamp: ' . $signed['timestamp'];
                $headers[] = 'x-signature: ' . $signed['signature'];
            }

            $response = false;
            $httpCode = 0;
            $curlError = '';
            $attempts = 3;
            for ($attempt = 1; $attempt <= $attempts; $attempt++) {
                [$response, $httpCode, $curlError] = $this->curlExecute(
                    $url,
                    $method,
                    $headers,
                    $rawBody,
                    $method !== 'GET' && $method !== 'HEAD'
                );

                // Retry transient localhost DNS / connect failures to frappay.in
                $transient = $response === false && (
                    str_contains(strtolower($curlError), 'resolving timed out')
                    || str_contains(strtolower($curlError), 'could not resolve host')
                    || str_contains(strtolower($curlError), 'failed to connect')
                    || str_contains(strtolower($curlError), 'timed out')
                );
                if (!$transient || $attempt === $attempts) {
                    break;
                }
                usleep(400000 * $attempt);
            }

            if ($response === false) {
                $friendly = 'Unable to reach FrapPay (' . ($curlError !== '' ? $curlError : 'network error') . '). '
                    . 'On localhost this is usually DNS/VPN/firewall. Retry in a few seconds.';
                $response = json_encode(['success' => false, 'message' => $friendly]);
            }

            try {
                Apiresponse::insertGetId([
                    'message' => (string)$response,
                    'api_type' => $this->api_id,
                    'response_type' => $responseType,
                    'request_message' => $method . ' ' . $url . ($rawBody !== '' ? '?' . $rawBody : ''),
                    'report_id' => $reportId,
                    'created_at' => now(),
                ]);
            } catch (\Throwable $e) {
                // ignore logging failures
            }

            $json = json_decode((string)$response, true);
            return [
                'ok' => $httpCode >= 200 && $httpCode < 300 && is_array($json) && (($json['success'] ?? false) === true),
                'http_code' => $httpCode,
                'body' => (string)$response,
                'json' => is_array($json) ? $json : [],
            ];
        }

        public function getAccessToken(bool $forceRefresh = false): string
        {
            if ($this->apiKey === '' || $this->secretKey === '') {
                $this->lastError = 'FrapPay credentials missing (api_key / secret_key)';
                return '';
            }

            $cacheKey = 'frappay:token:' . md5($this->apiKey . '|' . $this->base_url);
            if (!$forceRefresh && Cache::has($cacheKey)) {
                return (string)Cache::get($cacheKey);
            }

            $result = $this->request('POST', 'auth/token', new \stdClass(), false, null, 'auth_token');
            $data = is_array($result['json']['data'] ?? null) ? $result['json']['data'] : [];
            $token = (string)($data['accessToken'] ?? $data['token'] ?? '');
            $expiresIn = (int)($data['expiresIn'] ?? 900);
            if ($token !== '') {
                $this->lastError = '';
                Cache::put($cacheKey, $token, now()->addSeconds(max(60, $expiresIn - 60)));
                return $token;
            }

            $this->lastError = (string)($result['json']['message'] ?? 'Unable to generate FrapPay token');
            return '';
        }

        public function easebuzzInitiateUrl(string $environment = ''): string
        {
            $env = strtolower($environment);
            if ($env === '') {
                $env = $this->isUatEnvironment() ? 'uat' : 'production';
            }

            // Hosted checkout (FrapPay merchant does not allow seamless SUVA mode).
            return ($env === 'uat' || $env === 'test')
                ? 'https://testpay.easebuzz.in/pay/'
                : 'https://pay.easebuzz.in/pay/';
        }

        public function hostedCheckoutUrl(string $accessKey, string $environment = ''): string
        {
            $accessKey = trim($accessKey);
            if ($accessKey === '') {
                return '';
            }

            return rtrim($this->easebuzzInitiateUrl($environment), '/') . '/' . rawurlencode($accessKey);
        }

        /**
         * FrapPay's Easebuzz merchant is NOT authorised for seamless SUVA QR.
         * Use hostedCheckoutUrl() instead — returns the Pay With Easebuzz page.
         */
        public function fetchUpiQrLink(string $accessKey, string $environment = ''): array
        {
            $checkout = $this->hostedCheckoutUrl($accessKey, $environment);
            if ($checkout === '') {
                return ['ok' => false, 'qr_link' => '', 'payment_page_url' => '', 'message' => 'Missing access key'];
            }

            return [
                'ok' => true,
                'qr_link' => '',
                'payment_page_url' => $checkout,
                'message' => 'Open hosted payment page to complete UPI (seamless QR not enabled for this merchant)',
            ];
        }

        /**
         * Docs v1.1 webhook: HMAC-SHA256(secret, rawBody) compared to x-signature (sha256=...).
         */
        public function verifyWebhookSignature(string $rawBody, ?string $signatureHeader): bool
        {
            if ($this->secretKey === '') {
                return false;
            }

            $provided = trim((string)$signatureHeader);
            if ($provided === '') {
                return false;
            }
            $provided = preg_replace('/^sha256=/i', '', $provided);

            $expected = hash_hmac('sha256', $rawBody, $this->secretKey);
            if ($expected === '' || $provided === '') {
                return false;
            }

            return hash_equals($expected, $provided);
        }

        public function createPayin(array $params, ?int $gatewayOrderId = null): array
        {
            // Docs v1.1: browser redirects use surl/furl. Server webhooks are configured in dashboard.
            $surl = trim((string)($params['surl'] ?? $params['success_url'] ?? ''));
            $furl = trim((string)($params['furl'] ?? $params['failure_url'] ?? ''));
            if ($surl === '') {
                $surl = self::publicUrl('api/call-back/frappay-payin-success');
            }
            if ($furl === '') {
                $furl = self::publicUrl('api/call-back/frappay-payin-failure');
            }

            $payload = [
                'amount' => (float)($params['amount'] ?? 0),
                'customerName' => (string)($params['customerName'] ?? ''),
                'method' => (string)($params['method'] ?? 'UPI'),
                'referenceId' => (string)($params['referenceId'] ?? ''),
                'remark' => (string)($params['remark'] ?? ($params['referenceId'] ?? 'Add Money')),
                'surl' => $surl,
                'furl' => $furl,
            ];

            $result = $this->request('POST', 'payin', $payload, true, $gatewayOrderId, 'payin_create');
            $json = $result['json'];
            $data = is_array($json['data'] ?? null) ? $json['data'] : [];

            // Docs: payin create returns HTTP 201 with success:true
            if (!($result['ok'] ?? false)) {
                return [
                    'ok' => false,
                    'message' => (string)($json['message'] ?? ($this->lastError !== '' ? $this->lastError : 'FrapPay payin create failed')),
                    'data' => $data,
                    'raw' => $json,
                ];
            }

            $status = self::normalizeStatus((string)($data['status'] ?? 'pending'));
            $qrLink = (string)(
                $data['qr_link']
                ?? $data['qrLink']
                ?? $data['upiLink']
                ?? $data['upi_link']
                ?? $data['paymentUrl']
                ?? $data['payment_url']
                ?? ''
            );
            // Form-submission docs still require access_key from initiate response (production).
            $accessKey = (string)($data['access_key'] ?? $data['accessKey'] ?? $data['accesskey'] ?? '');
            $environment = (string)($data['environment'] ?? ($this->isUatEnvironment() ? 'uat' : 'production'));

            // Do NOT fetch Easebuzz QR here — it can timeout and break create-order.
            // Controller/fetch-qr endpoint handles QR / hosted checkout separately.

            return [
                'ok' => true,
                'message' => (string)($json['message'] ?? 'Payin created'),
                'txnId' => (string)($data['txnId'] ?? $data['txn_id'] ?? ''),
                'status' => $status,
                'amount' => (float)($data['amount'] ?? $payload['amount']),
                'qr_link' => $qrLink,
                'access_key' => $accessKey,
                'environment' => $environment,
                'surl' => $surl,
                'furl' => $furl,
                'uat_simulated' => self::isUatSimulatedPayin([
                    'environment' => $environment,
                    'message' => (string)($json['message'] ?? ''),
                    'access_key' => $accessKey,
                    'status' => $status,
                ]),
                'data' => $data,
                'raw' => $json,
            ];
        }

        public function getTransactionStatus(string $txnId, ?int $reportId = null): array
        {
            if ($txnId === '') {
                return ['ok' => false, 'status' => 'PENDING', 'message' => 'Missing txnId'];
            }

            $result = $this->request('GET', 'transactions/' . rawurlencode($txnId), null, true, $reportId, 'status_check');
            $json = $result['json'];
            $data = is_array($json['data'] ?? null) ? $json['data'] : [];

            if (!($result['ok'] ?? false)) {
                return [
                    'ok' => false,
                    'status' => 'PENDING',
                    'message' => (string)($json['message'] ?? 'Status fetch failed'),
                    'data' => $data,
                ];
            }

            $statusRaw = (string)(
                $data['status']
                ?? $data['paymentStatus']
                ?? $data['payment_status']
                ?? $data['txnStatus']
                ?? 'pending'
            );

            return [
                'ok' => true,
                'status' => self::normalizeStatus($statusRaw),
                'txnId' => (string)($data['txnId'] ?? $data['txn_id'] ?? $txnId),
                'providerTxnId' => (string)($data['providerTxnId'] ?? $data['provider_txn_id'] ?? ''),
                'amount' => (float)($data['amount'] ?? 0),
                'utr' => (string)(
                    $data['utr']
                    ?? $data['UTR']
                    ?? $data['bankRef']
                    ?? $data['bank_ref']
                    ?? $data['providerTxnId']
                    ?? ''
                ),
                'message' => (string)($json['message'] ?? ''),
                'data' => $data,
            ];
        }

        public function getWalletBalance(): array
        {
            $result = $this->request('GET', 'wallet/balance', null, true, null, 'wallet_balance');
            return $result['json'] ?? [];
        }

        /**
         * Partner payin wallet balance (source of truth when status API lags behind dashboard).
         */
        public function getPayinWalletBalance(): array
        {
            $json = $this->getWalletBalance();
            $data = is_array($json['data'] ?? null) ? $json['data'] : [];
            $ok = (bool)($json['success'] ?? false);

            return [
                'ok' => $ok,
                'payin' => (float)($data['payinWalletBalance'] ?? $data['payin_wallet_balance'] ?? 0),
                'payout' => (float)($data['payoutWalletBalance'] ?? $data['payout_wallet_balance'] ?? 0),
                'total' => (float)($data['totalWalletBalance'] ?? $data['total_wallet_balance'] ?? 0),
                'raw' => $json,
            ];
        }

        public function transferNow($user_id, $mobile_number, $amount, $beneficiary_name, $account_number, $ifsc_code, $insert_id)
        {
            if ($this->apiKey === '' || $this->secretKey === '') {
                return ['status_id' => 3, 'txnid' => 'FrapPay credentials missing', 'payid' => ''];
            }

            $amount = (float)$amount;
            if ($amount < self::PAYOUT_MIN || $amount > self::PAYOUT_MAX) {
                return [
                    'status_id' => 2,
                    'txnid' => 'Amount must be between ' . self::PAYOUT_MIN . ' and ' . self::PAYOUT_MAX,
                    'payid' => '',
                ];
            }

            $merchantRef = $this->buildPayoutRef($insert_id);
            $bankName = $this->getBanknameByIfsc($ifsc_code);
            $payload = [
                'amount' => $amount,
                'beneficiaryName' => (string)$beneficiary_name,
                'accountNo' => (string)$account_number,
                'ifsc' => strtoupper((string)$ifsc_code),
                'bankName' => (string)$bankName,
                'mobile' => (string)$mobile_number,
                'method' => 'IMPS',
                'referenceId' => $merchantRef,
                'remark' => 'Payout ' . $insert_id,
            ];

            $result = $this->request('POST', 'payout', $payload, true, $insert_id, 'payout_create');
            $json = $result['json'];
            $data = is_array($json['data'] ?? null) ? $json['data'] : [];

            if (!($result['ok'] ?? false)) {
                return [
                    'status_id' => 2,
                    'txnid' => \App\Library\LockHoldPayoutLibrary::REASON_BANK_DOWNTIME,
                    'payid' => $merchantRef,
                ];
            }

            $providerStatus = self::normalizeStatus((string)($data['status'] ?? 'pending'));
            $txnId = (string)($data['txnId'] ?? '');
            $utr = (string)($data['utr'] ?? $txnId);

            if ($providerStatus === 'SUCCESS') {
                return ['status_id' => 1, 'txnid' => $utr, 'payid' => $merchantRef];
            }
            if ($providerStatus === 'FAILED') {
                return [
                    'status_id' => 2,
                    'txnid' => \App\Library\LockHoldPayoutLibrary::REASON_BANK_DOWNTIME,
                    'payid' => $merchantRef,
                ];
            }

            return ['status_id' => 3, 'txnid' => $utr, 'payid' => $merchantRef];
        }

        public function get_transaction_status($insert_id)
        {
            $log = Apiresponse::where('report_id', $insert_id)
                ->where('api_type', $this->api_id)
                ->where('response_type', 'payout_create')
                ->orderBy('id', 'DESC')
                ->first();

            $txnId = '';
            if ($log) {
                $res = json_decode((string)$log->message, true);
                $txnId = (string)($res['data']['txnId'] ?? '');
            }

            if ($txnId === '') {
                return ['status_id' => 3, 'txnid' => '', 'payid' => $this->buildPayoutRef($insert_id)];
            }

            $status = $this->getTransactionStatus($txnId, $insert_id);
            $merchantRef = $this->buildPayoutRef($insert_id);

            if (($status['status'] ?? '') === 'SUCCESS') {
                return ['status_id' => 1, 'txnid' => $status['utr'] ?: $txnId, 'payid' => $merchantRef];
            }
            if (($status['status'] ?? '') === 'FAILED') {
                return [
                    'status_id' => 2,
                    'txnid' => \App\Library\LockHoldPayoutLibrary::REASON_BANK_DOWNTIME,
                    'payid' => $merchantRef,
                ];
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

        private function getBanknameByIfsc($ifsc_code): string
        {
            $ifsc = strtoupper(trim((string)$ifsc_code));
            if ($ifsc === '') {
                return 'BANK';
            }
            $url = 'https://ifsc.razorpay.com/' . rawurlencode($ifsc);
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
            ]);
            $data = curl_exec($curl);
            curl_close($curl);
            $res = json_decode((string)$data);

            return (string)($res->BANK ?? 'BANK');
        }
    }
}
