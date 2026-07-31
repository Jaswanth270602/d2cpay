<?php

namespace App\library {

    use App\Models\Api;
    use App\Models\Apiresponse;
    use App\Library\RefundLibrary;
    use Helpers;
    use Illuminate\Support\Facades\Cache;

    class AurexaPayLibrary
    {
        public const PAYIN_MIN = 500;
        public const PAYIN_MAX = 25000;
        public const PAYOUT_MIN = 600;
        public const PAYOUT_MAX = 25000;

        private $api_id;
        private $base_url;
        private $clientKey;
        private $clientSecret;

        public function __construct()
        {
            $this->api_id = 19;
            $credentials = json_decode(optional(Api::find($this->api_id))->credentials);
            $this->base_url = rtrim($credentials->base_url ?? 'https://aurexapay.com/api/v1.1', '/');
            $this->clientKey = (string)($credentials->client_key ?? $credentials->clientKey ?? '');
            $this->clientSecret = (string)($credentials->client_secret ?? $credentials->clientSecret ?? '');
        }

        public function buildPayinMerchantRef(int $gatewayOrderId): string
        {
            return 'AXI' . $gatewayOrderId . date('His');
        }

        public function buildPayoutRef($insertId): string
        {
            return 'AXO' . date('ymd') . str_pad((string)$insertId, 11, '0', STR_PAD_LEFT);
        }

        public static function publicBaseUrl(): string
        {
            $configured = trim((string)config('app.aurexapay_public_url', ''));
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

        public static function normalizeStatus(string $status): string
        {
            $status = strtoupper(trim($status));
            if (in_array($status, ['SUCCESS', 'SUCCESSFUL', 'COMPLETED', 'PAID', 'CREDIT', 'CREDITED'], true)) {
                return 'SUCCESS';
            }
            if (in_array($status, ['FAILED', 'FAILURE', 'FAILED', 'CANCELLED', 'CANCELED', 'REVERSED', 'REFUNDED', 'DECLINED'], true)) {
                return 'FAILED';
            }
            return 'PENDING';
        }

        public function getAccessToken(bool $forceRefresh = false): string
        {
            $cacheKey = 'aurexapay:token:' . md5($this->clientKey);

            if (!$forceRefresh && Cache::has($cacheKey)) {
                return (string)Cache::get($cacheKey);
            }

            if ($this->clientKey === '' || $this->clientSecret === '') {
                return '';
            }

            $url = $this->base_url . '/generateToken';
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => [
                    'clientKey' => $this->clientKey,
                    'clientSecret' => $this->clientSecret,
                ],
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_TIMEOUT => 45,
            ]);
            $response = curl_exec($ch);
            curl_close($ch);

            $res = json_decode((string)$response, true);
            $token = '';
            if (is_array($res)) {
                $token = (string)(
                    $res['data']['access_token']
                    ?? $res['data']['token']
                    ?? $res['access_token']
                    ?? $res['token']
                    ?? ''
                );
            }

            if ($token !== '') {
                Cache::put($cacheKey, $token, now()->addMinutes(50));
            }

            return $token;
        }

        private function request(string $method, string $path, $body = null, ?int $reportId = null, string $responseType = 'api', bool $forceToken = false): array
        {
            $method = strtoupper($method);
            $token = $this->getAccessToken($forceToken);
            if ($token === '') {
                return ['ok' => false, 'http_code' => 0, 'json' => [], 'raw' => '', 'message' => 'Unable to generate AurexaPay token'];
            }

            $url = $this->base_url . '/' . ltrim($path, '/');
            $rawBody = '';
            if ($method !== 'GET' && $method !== 'HEAD') {
                $rawBody = is_string($body) ? $body : json_encode($body ?? new \stdClass(), JSON_UNESCAPED_SLASHES);
            }

            $headers = [
                'Accept: application/json',
                'Authorization: Bearer ' . $token,
            ];
            if ($method !== 'GET' && $method !== 'HEAD') {
                $headers[] = 'Content-Type: application/json';
            }

            $ch = curl_init($url);
            $opts = [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_TIMEOUT => 60,
            ];
            if ($method !== 'GET' && $method !== 'HEAD') {
                $opts[CURLOPT_POSTFIELDS] = $rawBody;
            }
            curl_setopt_array($ch, $opts);
            $response = curl_exec($ch);
            $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = (string)curl_error($ch);
            curl_close($ch);

            $raw = $response === false ? ('curl_error:' . $curlError) : (string)$response;
            Apiresponse::insertGetId([
                'message' => $raw,
                'api_type' => $this->api_id,
                'response_type' => $responseType,
                'report_id' => $reportId,
                'request_message' => $method . ' ' . $url . ($rawBody !== '' ? '?' . $rawBody : ''),
            ]);

            $json = json_decode($raw, true);
            if (!is_array($json)) {
                $json = [];
            }

            $statusOk = in_array(strtolower((string)($json['status'] ?? '')), ['success', 'true', '1', 'ok'], true)
                || (($json['success'] ?? null) === true)
                || ($httpCode >= 200 && $httpCode < 300 && !isset($json['status']));

            // Retry once on auth failure
            if (!$forceToken && in_array($httpCode, [401, 403], true)) {
                return $this->request($method, $path, $body, $reportId, $responseType, true);
            }

            return [
                'ok' => $statusOk,
                'http_code' => $httpCode,
                'json' => $json,
                'raw' => $raw,
                'message' => (string)($json['message'] ?? ''),
            ];
        }

        public function checkBalance(): array
        {
            $result = $this->request('GET', 'checkBalance', null, null, 'balance_check');
            $data = is_array($result['json']['data'] ?? null) ? $result['json']['data'] : [];

            return [
                'ok' => (bool)($result['ok'] ?? false),
                'balance' => (float)($data['balance'] ?? 0),
                'name' => (string)($data['Name'] ?? $data['name'] ?? ''),
                'message' => (string)($data['message'] ?? $result['message'] ?? ''),
                'raw' => $result['json'],
            ];
        }

        public function createPayinIntent(array $params, ?int $gatewayOrderId = null): array
        {
            $token = $this->getAccessToken(false);
            if ($token === '') {
                return ['ok' => false, 'message' => 'Unable to generate AurexaPay token', 'raw' => []];
            }

            $payload = [
                'amount' => (string)(int)round((float)($params['amount'] ?? 0)),
                'reference' => (string)($params['reference'] ?? ''),
                'name' => (string)($params['name'] ?? ''),
                'email' => (string)($params['email'] ?? ''),
                'mobile' => (string)($params['mobile'] ?? ''),
            ];

            $url = $this->base_url . '/createUpiIntentSafePay';
            $headers = [
                'Accept: application/json',
                'Authorization: Bearer ' . $token,
            ];

            // Aurexa sample uses multipart form-data (--form), not JSON.
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_TIMEOUT => 60,
            ]);
            $response = curl_exec($ch);
            $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = (string)curl_error($ch);
            curl_close($ch);

            $raw = $response === false ? ('curl_error:' . $curlError) : (string)$response;
            Apiresponse::insertGetId([
                'message' => $raw,
                'api_type' => $this->api_id,
                'response_type' => 'payin_create',
                'report_id' => $gatewayOrderId,
                'request_message' => 'POST ' . $url . '?' . http_build_query($payload),
            ]);

            $json = json_decode($raw, true);
            if (!is_array($json)) {
                return ['ok' => false, 'message' => 'Invalid response from AurexaPay', 'raw' => []];
            }

            // Retry once on auth failure with fresh token
            if (in_array($httpCode, [401, 403], true)) {
                $token = $this->getAccessToken(true);
                if ($token !== '') {
                    $headers[1] = 'Authorization: Bearer ' . $token;
                    $ch = curl_init($url);
                    curl_setopt_array($ch, [
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_POST => true,
                        CURLOPT_HTTPHEADER => $headers,
                        CURLOPT_POSTFIELDS => $payload,
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => false,
                        CURLOPT_TIMEOUT => 60,
                    ]);
                    $response = curl_exec($ch);
                    curl_close($ch);
                    $raw = (string)$response;
                    Apiresponse::insertGetId([
                        'message' => $raw,
                        'api_type' => $this->api_id,
                        'response_type' => 'payin_create',
                        'report_id' => $gatewayOrderId,
                        'request_message' => 'POST ' . $url . '?retry=1&' . http_build_query($payload),
                    ]);
                    $json = json_decode($raw, true);
                    if (!is_array($json)) {
                        return ['ok' => false, 'message' => 'Invalid response from AurexaPay', 'raw' => []];
                    }
                }
            }

            $data = is_array($json['data'] ?? null) ? $json['data'] : $json;
            $message = (string)($json['message'] ?? $data['message'] ?? '');
            $statusRaw = strtolower((string)($json['status'] ?? ''));

            $orderId = (string)(
                $data['transaction_id']
                ?? $data['txnId']
                ?? $data['txn_id']
                ?? $data['id']
                ?? $data['orderId']
                ?? $data['order_id']
                ?? $json['transaction_id']
                ?? ''
            );

            $paymentUrl = (string)(
                $data['payment_link']
                ?? $data['paymentLink']
                ?? $data['paymentUrl']
                ?? $data['payment_url']
                ?? $data['checkout_url']
                ?? $data['url']
                ?? $json['payment_link']
                ?? ''
            );

            $qrString = (string)(
                $data['qrString']
                ?? $data['qr_string']
                ?? $data['qr']
                ?? $data['upi']
                ?? $data['upi_string']
                ?? $data['upi_intent']
                ?? $data['intent']
                ?? $data['upiIntent']
                ?? $data['deep_link']
                ?? $data['deeplink']
                ?? ''
            );

            // Some responses put UPI intent inside payment_link.
            if ($qrString === '' && $paymentUrl !== '') {
                if (str_starts_with(strtolower($paymentUrl), 'upi://') || str_contains(strtolower($paymentUrl), 'pa=')) {
                    $qrString = $paymentUrl;
                }
            }

            $qrCodeUrl = (string)(
                $data['qrCodeUrl']
                ?? $data['qr_code_url']
                ?? $data['qrImage']
                ?? $data['qr_image']
                ?? $data['qr_code']
                ?? ''
            );

            $hasPayInstrument = ($paymentUrl !== '' || $qrString !== '' || $qrCodeUrl !== '');
            $explicitFail = in_array($statusRaw, ['failed', 'failure', 'error', 'false'], true)
                || stripos($message, 'failed') !== false
                || stripos($message, 'not able') !== false
                || stripos($message, 'not white') !== false;

            // Aurexa sometimes returns status=success with message "Create Order Failed" and empty payment_link.
            if ($explicitFail && !$hasPayInstrument) {
                return [
                    'ok' => false,
                    'message' => $message !== '' ? $message : 'Payin create failed',
                    'orderId' => $orderId,
                    'raw' => $json,
                ];
            }

            if (!$hasPayInstrument) {
                return [
                    'ok' => false,
                    'message' => $message !== '' ? $message : 'Payin created but payment link/QR was empty',
                    'orderId' => $orderId,
                    'raw' => $json,
                ];
            }

            return [
                'ok' => true,
                'message' => $message !== '' ? $message : 'Order created',
                'orderId' => $orderId,
                'qrString' => $qrString,
                'paymentUrl' => $paymentUrl,
                'qrCodeUrl' => $qrCodeUrl,
                'status' => self::normalizeStatus((string)($data['txn_status'] ?? $data['payment_status'] ?? $data['status'] ?? 'PENDING')),
                'raw' => $json,
            ];
        }

        public function getPayinStatus(string $transactionId, ?int $gatewayOrderId = null): array
        {
            if ($transactionId === '') {
                return ['ok' => false, 'status' => 'PENDING', 'utr' => '', 'amount' => 0, 'orderId' => '', 'message' => 'Missing transaction id'];
            }

            $result = $this->request(
                'GET',
                'payinTransactionCheckStatus/' . rawurlencode($transactionId),
                null,
                $gatewayOrderId,
                'status_check'
            );
            $json = $result['json'];
            $data = is_array($json['data'] ?? null) ? $json['data'] : $json;

            $status = self::normalizeStatus((string)(
                $data['status']
                ?? $data['payment_status']
                ?? $data['txn_status']
                ?? $json['status']
                ?? 'PENDING'
            ));
            // When top-level status is "success" it often means API ok, not payment success
            if (isset($data['status']) || isset($data['payment_status']) || isset($data['txn_status'])) {
                // already normalized from data
            } elseif (strtolower((string)($json['status'] ?? '')) === 'success' && empty($data['status'])) {
                $status = 'PENDING';
            }

            return [
                'ok' => (bool)($result['ok'] ?? false),
                'status' => $status,
                'utr' => (string)($data['utr'] ?? $data['UTR'] ?? $data['bank_ref'] ?? ''),
                'amount' => (float)($data['amount'] ?? 0),
                'orderId' => (string)($data['id'] ?? $data['txnId'] ?? $data['txn_id'] ?? $transactionId),
                'message' => (string)($json['message'] ?? ''),
                'raw' => $json,
            ];
        }

        public function parsePayinStatusResponse(array $remote): array
        {
            return [
                'status' => (string)($remote['status'] ?? 'PENDING'),
                'utr' => (string)($remote['utr'] ?? ''),
                'amount' => (float)($remote['amount'] ?? 0),
                'orderId' => (string)($remote['orderId'] ?? ''),
            ];
        }

        public static function parseIncomingCallback(\Illuminate\Http\Request $request): array
        {
            $payload = $request->all();
            $raw = (string)$request->getContent();
            if ((empty($payload) || $payload === []) && $raw !== '') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $payload = $decoded;
                }
            }
            if (isset($payload['data']) && is_array($payload['data'])) {
                $payload = array_merge($payload, $payload['data']);
            }

            $reference = (string)(
                $payload['reference']
                ?? $payload['merchant_refid']
                ?? $payload['merchantOrderNo']
                ?? $payload['order_token']
                ?? $payload['client_id']
                ?? ''
            );
            $statusRaw = (string)($payload['status'] ?? $payload['payment_status'] ?? $payload['txn_status'] ?? '');
            $payload['reference'] = $reference;
            $payload['merchantOrderNo'] = $reference;
            $payload['status'] = self::normalizeStatus($statusRaw);
            $payload['status_raw'] = $statusRaw;
            $payload['utr'] = (string)($payload['utr'] ?? $payload['UTR'] ?? '');
            $payload['amount'] = (float)($payload['amount'] ?? 0);
            $payload['orderId'] = (string)(
                $payload['id']
                ?? $payload['txnId']
                ?? $payload['txn_id']
                ?? $payload['transaction_id']
                ?? $payload['orderId']
                ?? ''
            );

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

        public static function isEffectivelyEmptyPayload($payload): bool
        {
            return !is_array($payload) || $payload === [] || $payload === ['' => null];
        }

        public function confirmPayinSuccessFromApi(string $transactionId, ?int $gatewayOrderId = null): ?array
        {
            $remote = $this->getPayinStatus($transactionId, $gatewayOrderId);
            if (($remote['status'] ?? '') !== 'SUCCESS') {
                return null;
            }
            return $remote;
        }

        public static function resolvePayinLogType(string $status): string
        {
            $status = strtoupper(trim($status));
            if ($status === 'SUCCESS') {
                return 'call_back_success';
            }
            if ($status === 'FAILED') {
                return 'call_back_failed';
            }
            return 'call_back';
        }

        public static function isPayinStatusPollNoise(string $apiMessage, ?string $responseType = null): bool
        {
            if ($responseType === 'status_check') {
                return true;
            }
            return false;
        }

        public static function pendingPayinDisplayReason(): string
        {
            return 'Awaiting payment confirmation';
        }

        public function transferNow($user_id, $mobile_number, $amount, $beneficiary_name, $account_number, $ifsc_code, $insert_id, $channel_id = null, $bank_name = null, $email = null)
        {
            if ($this->clientKey === '' || $this->clientSecret === '') {
                return ['status_id' => 3, 'txnid' => 'AurexaPay credentials missing', 'payid' => ''];
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
            $mode = ((int)$channel_id === 1) ? 'NEFT' : 'IMPS';
            $user = \App\Models\User::find($user_id);
            $payload = [
                'amount' => (int)round($amount),
                'reference' => $merchantRef,
                'name' => (string)$beneficiary_name,
                'email' => (string)($email ?: ($user->email ?? 'noreply@d2cpay.co')),
                'mobile' => (string)$mobile_number,
                'trans_mode' => $mode,
                'ifsc' => strtoupper((string)$ifsc_code),
                'account' => (string)$account_number,
                'address' => (string)($bank_name ?: 'India'),
            ];

            $result = $this->request('POST', 'payoutTransaction', $payload, $insert_id, 'payout_create');
            $json = $result['json'];
            $data = is_array($json['data'] ?? null) ? $json['data'] : [];

            if (!($result['ok'] ?? false)) {
                return [
                    'status_id' => 2,
                    'txnid' => \App\Library\LockHoldPayoutLibrary::REASON_BANK_DOWNTIME,
                    'payid' => $merchantRef,
                ];
            }

            $providerStatus = self::normalizeStatus((string)($data['status'] ?? 'PENDING'));
            $txnId = (string)($data['id'] ?? $data['txnId'] ?? $data['txn_id'] ?? '');
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
            $merchantRef = $this->buildPayoutRef($insert_id);
            if ($log) {
                $res = json_decode((string)$log->message, true);
                $data = is_array($res['data'] ?? null) ? $res['data'] : [];
                $txnId = (string)($data['id'] ?? $data['txnId'] ?? $data['txn_id'] ?? '');
            }

            if ($txnId === '') {
                return ['status_id' => 3, 'txnid' => '', 'payid' => $merchantRef];
            }

            $result = $this->request(
                'GET',
                'payoutTransactionCheckStatus/' . rawurlencode($txnId),
                null,
                $insert_id,
                'status_check'
            );
            $json = $result['json'];
            $data = is_array($json['data'] ?? null) ? $json['data'] : [];
            $status = self::normalizeStatus((string)($data['status'] ?? $data['payment_status'] ?? 'PENDING'));
            $utr = (string)($data['utr'] ?? $txnId);

            if ($status === 'SUCCESS') {
                return ['status_id' => 1, 'txnid' => $utr, 'payid' => $merchantRef];
            }
            if ($status === 'FAILED') {
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
            $result = $this->get_transaction_status($insert_id);
            if (in_array((int)$result['status_id'], [1, 2], true)) {
                $library = new RefundLibrary();
                $library->update_transaction(
                    $insert_id,
                    (int)$result['status_id'],
                    (string)($result['txnid'] ?? ''),
                    'Cron'
                );
            }
            return $result;
        }
    }
}
