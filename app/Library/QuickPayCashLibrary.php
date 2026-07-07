<?php

namespace App\library {

    use App\Models\Api;
    use App\Models\Apiresponse;
    use App\Library\RefundLibrary;
    use Helpers;

    class QuickPayCashLibrary
    {
        private $api_id;
        private $base_url;
        private $merchantId;
        private $merchantKey;

        public function __construct()
        {
            $this->api_id = 16;
            $credentials = json_decode(optional(Api::find($this->api_id))->credentials);
            $this->base_url = rtrim($credentials->base_url ?? 'https://portalquickpaycash.com', '/');
            $this->merchantId = $credentials->merchant_id ?? '';
            $this->merchantKey = $credentials->merchant_key ?? '';
        }

        public function signPayinCreate(string $merchantOrderNo, $amount): string
        {
            return $this->sign($merchantOrderNo . $this->formatAmount($amount));
        }

        public function signPayoutCreate(string $merchantOrderNo, $payAmount): string
        {
            return $this->sign($merchantOrderNo . $this->formatAmount($payAmount));
        }

        public function signStatus(string $merchantOrderNo): string
        {
            return $this->sign($merchantOrderNo);
        }

        public static function isTerminalPayinStatus(string $status): bool
        {
            return in_array(strtoupper($status), ['SUCCESS', 'FAILED', 'CANCELLED', 'REFUNDED'], true);
        }

        public static function resolvePayinLogType(string $status): string
        {
            return self::isTerminalPayinStatus($status) ? 'call_back' : 'status_check';
        }

        public function verifyPayinCallback(array $payload, string $receivedSignature): bool
        {
            $merchantKey = $this->merchantKey;
            if ($merchantKey === '' || $receivedSignature === '') {
                return false;
            }

            $merchantOrderNo = (string)($payload['merchantOrderNo'] ?? '');
            if ($merchantOrderNo === '') {
                return false;
            }

            $orderId = (string)($payload['orderId'] ?? '');
            $platOrderNo = (string)($payload['platOrderNo'] ?? '');
            $received = strtoupper($receivedSignature);

            // QPC doc v2.3: MD5(orderId + merchantOrderNo + status + merchantKey)
            $statusForOrderId = strtoupper((string)($payload['status'] ?? ''));
            if ($orderId !== '' && $statusForOrderId !== '') {
                $expected = strtoupper(md5($orderId . $merchantOrderNo . $statusForOrderId . $merchantKey));
                if (hash_equals($expected, $received)) {
                    return true;
                }
            }

            // QPC production format: MD5(platOrderNo + merchantOrderNo + orderStatus + merchantKey)
            $orderStatus = strtoupper((string)($payload['orderStatus'] ?? ''));
            if ($platOrderNo !== '' && $orderStatus !== '') {
                $expected = strtoupper(md5($platOrderNo . $merchantOrderNo . $orderStatus . $merchantKey));
                if (hash_equals($expected, $received)) {
                    return true;
                }
            }

            // Fallback: try all status variants on both reference ids
            $statusCandidates = $this->callbackStatusCandidates($payload);
            foreach ($statusCandidates as $status) {
                if ($orderId !== '') {
                    $expected = strtoupper(md5($orderId . $merchantOrderNo . $status . $merchantKey));
                    if (hash_equals($expected, $received)) {
                        return true;
                    }
                }
                if ($platOrderNo !== '') {
                    $expected = strtoupper(md5($platOrderNo . $merchantOrderNo . $status . $merchantKey));
                    if (hash_equals($expected, $received)) {
                        return true;
                    }
                }
            }

            return false;
        }

        public function confirmPayinSuccessFromApi(string $merchantOrderNo): ?array
        {
            if ($merchantOrderNo === '') {
                return null;
            }

            $res = $this->getPayinStatus($merchantOrderNo);
            $parsed = $this->parsePayinStatusResponse($res);
            if ($parsed['status'] === 'SUCCESS') {
                $parsed['merchantOrderNo'] = $merchantOrderNo;
                return $parsed;
            }

            return null;
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

            if (self::isEffectivelyEmptyPayload($payload) && !empty($request->query())) {
                $payload = $request->query()->all();
            }

            if (isset($payload['data']) && is_array($payload['data'])) {
                $payload = array_merge($payload, $payload['data']);
            }

            return self::normalizePayinPayload(is_array($payload) ? $payload : []);
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
            $merchantOrderNo = (string)(
                $payload['merchantOrderNo']
                ?? $payload['merchant_order_no']
                ?? $payload['orderNo']
                ?? ''
            );
            $status = (string)($payload['status'] ?? $payload['orderStatus'] ?? '');
            $platOrderNo = (string)($payload['platOrderNo'] ?? $payload['plat_order_no'] ?? '');
            $orderId = (string)($payload['orderId'] ?? $payload['order_id'] ?? '');
            if ($orderId === '' && $platOrderNo !== '') {
                $orderId = $platOrderNo;
            }
            $utr = $payload['utr'] ?? $payload['UTR'] ?? $payload['bank_rrn'] ?? $payload['rrn'] ?? '';
            $amount = $payload['amount'] ?? $payload['payAmount'] ?? $payload['pay_amount'] ?? 0;
            $signature = (string)($payload['signature'] ?? $payload['sign'] ?? '');

            $payload['merchantOrderNo'] = $merchantOrderNo;
            $payload['platOrderNo'] = $platOrderNo;
            $payload['status'] = strtoupper($status);
            $payload['orderStatus'] = strtoupper((string)($payload['orderStatus'] ?? $status));
            $payload['orderId'] = $orderId;
            $payload['signature'] = $signature;
            $payload['utr'] = $utr === null || $utr === '' ? '' : (string)$utr;
            $payload['amount'] = is_numeric($amount) ? (float)$amount : 0;

            return $payload;
        }

        private function callbackStatusCandidates(array $payload): array
        {
            $candidates = [];
            foreach (['status', 'orderStatus'] as $field) {
                if (!isset($payload[$field]) || $payload[$field] === '') {
                    continue;
                }
                $raw = (string)$payload[$field];
                $candidates[] = $raw;
                $candidates[] = strtoupper($raw);
                $candidates[] = strtolower($raw);
            }

            return array_values(array_unique($candidates));
        }

        public static function isEffectivelyEmptyPayload($payload): bool
        {
            return !is_array($payload) || $payload === [] || $payload === ['' => null];
        }

        public function verifyPayoutCallback(array $payload, string $receivedSignature): bool
        {
            $merchantKey = $this->merchantKey;
            if ($merchantKey === '' || $receivedSignature === '') {
                return false;
            }

            $merchantOrderNo = (string)($payload['merchantOrderNo'] ?? '');
            $refCandidates = array_values(array_unique(array_filter([
                (string)($payload['payoutId'] ?? ''),
                (string)($payload['orderId'] ?? ''),
                (string)($payload['platOrderNo'] ?? ''),
            ])));
            $statusCandidates = $this->callbackStatusCandidates($payload);

            foreach ($refCandidates as $refId) {
                foreach ($statusCandidates as $status) {
                    $expected = strtoupper(md5($refId . $merchantOrderNo . $status . $merchantKey));
                    if (hash_equals($expected, strtoupper($receivedSignature))) {
                        return true;
                    }
                }
            }

            return false;
        }

        public function transferNow($user_id, $mobile_number, $amount, $beneficiary_name, $account_number, $ifsc_code, $insert_id)
        {
            if (empty($this->merchantId) || empty($this->merchantKey)) {
                return ['status_id' => 3, 'txnid' => 'QPC credentials missing', 'payid' => ''];
            }

            $merchantOrderNo = $this->buildPayoutRefId($insert_id);
            $payAmount = $this->formatAmount($amount);
            $notifyUrl = self::publicUrl('api/call-back/qpc-payout');

            $payload = [
                'merchantId' => $this->merchantId,
                'merchantOrderNo' => $merchantOrderNo,
                'payAmount' => $payAmount,
                'currency' => 'INR',
                'transferType' => 'IMPS',
                'bankCode' => (string)$ifsc_code,
                'bankNumber' => (string)$account_number,
                'accountHoldName' => (string)$beneficiary_name,
                'mobile' => (string)$mobile_number,
                'description' => 'Payout ' . $insert_id,
                'notifyUrl' => $notifyUrl,
                'signature' => $this->signPayoutCreate($merchantOrderNo, $payAmount),
            ];

            $url = $this->base_url . '/api/payout/create';
            $headers = ['Content-Type: application/json', 'accept: application/json'];
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
                return ['status_id' => 3, 'txnid' => 'Invalid response', 'payid' => $merchantOrderNo];
            }

            $topStatus = strtoupper((string)($res['status'] ?? ''));
            $data = is_array($res['data'] ?? null) ? $res['data'] : [];
            $providerStatus = strtoupper((string)($data['status'] ?? $data['orderStatus'] ?? ''));
            $payoutId = (string)($data['payoutId'] ?? $data['platOrderNo'] ?? '');
            $message = (string)($res['message'] ?? 'Transaction failed');

            if (in_array($topStatus, ['SUCCESS', '200'], true)) {
                if ($providerStatus === 'SUCCESS') {
                    return ['status_id' => 1, 'txnid' => $payoutId, 'payid' => $merchantOrderNo];
                }
                if (in_array($providerStatus, ['FAILED', 'CANCELLED'], true)) {
                    return ['status_id' => 2, 'txnid' => $message, 'payid' => $merchantOrderNo];
                }
                return ['status_id' => 3, 'txnid' => '', 'payid' => $merchantOrderNo];
            }

            if ($topStatus === 'FAILED') {
                return ['status_id' => 2, 'txnid' => $message, 'payid' => $merchantOrderNo];
            }

            return ['status_id' => 3, 'txnid' => '', 'payid' => $merchantOrderNo];
        }

        public function get_transaction_status($insert_id)
        {
            if (empty($this->merchantId) || empty($this->merchantKey)) {
                return ['status_id' => 3, 'txnid' => 'QPC credentials missing', 'payid' => ''];
            }

            $merchantOrderNo = $this->buildPayoutRefId($insert_id);
            $url = $this->base_url . '/api/payout/status';
            $payload = [
                'merchantId' => $this->merchantId,
                'merchantOrderNo' => $merchantOrderNo,
                'signature' => $this->signStatus($merchantOrderNo),
            ];
            $headers = ['Content-Type: application/json', 'accept: application/json'];
            $response = Helpers::pay_curl_post($url, $headers, json_encode($payload), 'POST');

            Apiresponse::insertGetId([
                'message' => $response,
                'api_type' => $this->api_id,
                'response_type' => 'status_check',
                'report_id' => $insert_id,
                'request_message' => $url . '?' . json_encode($payload),
            ]);

            $res = json_decode($response, true);
            if (!is_array($res)) {
                return ['status_id' => 3, 'txnid' => 'Invalid response', 'payid' => $merchantOrderNo];
            }

            $data = is_array($res['data'] ?? null) ? $res['data'] : [];
            $providerStatus = strtoupper((string)($data['status'] ?? $data['orderStatus'] ?? ''));
            $utr = (string)($data['utr'] ?? '');
            $payoutId = (string)($data['payoutId'] ?? $data['platOrderNo'] ?? '');

            if ($providerStatus === 'SUCCESS') {
                return ['status_id' => 1, 'txnid' => $utr ?: $payoutId, 'payid' => $merchantOrderNo];
            }
            if (in_array($providerStatus, ['FAILED', 'CANCELLED'], true)) {
                $msg = (string)($res['message'] ?? $data['orderMessage'] ?? 'Transaction failed');
                return ['status_id' => 2, 'txnid' => $msg, 'payid' => $merchantOrderNo];
            }

            return ['status_id' => 3, 'txnid' => '', 'payid' => $merchantOrderNo];
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

        public function buildPayoutRefId($insert_id): string
        {
            return 'QPO' . date('ymd') . str_pad((string)$insert_id, 11, '0', STR_PAD_LEFT);
        }

        public function buildPayinOrderNo($gatewayOrderId): string
        {
            return 'QPI' . $gatewayOrderId . date('His');
        }

        public function getPayinStatus(string $merchantOrderNo, ?int $gatewayOrderId = null): array
        {
            $url = $this->base_url . '/api/payin/status';
            $payload = [
                'merchantId' => $this->merchantId,
                'merchantOrderNo' => $merchantOrderNo,
                'signature' => $this->signStatus($merchantOrderNo),
            ];
            $headers = ['Content-Type: application/json', 'accept: application/json'];
            $response = Helpers::pay_curl_post($url, $headers, json_encode($payload), 'POST');

            Apiresponse::insertGetId([
                'message' => $response,
                'api_type' => $this->api_id,
                'response_type' => 'status_check',
                'request_message' => $url . '?' . json_encode($payload),
                'report_id' => $gatewayOrderId,
            ]);

            $res = json_decode($response, true);
            return is_array($res) ? $res : [];
        }

        public function parsePayinStatusResponse(array $res): array
        {
            $data = is_array($res['data'] ?? null) ? $res['data'] : $res;
            $status = strtoupper((string)($data['status'] ?? $data['orderStatus'] ?? ''));
            $topStatus = strtoupper((string)($res['status'] ?? ''));

            if ($status === '' && in_array($topStatus, ['SUCCESS', '200'], true)) {
                $status = strtoupper((string)($data['status'] ?? $data['orderStatus'] ?? 'PENDING'));
            }

            if (in_array($status, ['PAID', 'COMPLETED', 'SUCCESSFUL'], true)) {
                $status = 'SUCCESS';
            }

            return [
                'status' => $status,
                'utr' => (string)($data['utr'] ?? $data['UTR'] ?? $data['bank_rrn'] ?? $data['rrn'] ?? ''),
                'orderId' => (string)($data['orderId'] ?? $data['platOrderNo'] ?? ''),
                'amount' => (float)($data['amount'] ?? $data['payAmount'] ?? 0),
            ];
        }

        public static function publicBaseUrl(): string
        {
            $qpcPublic = trim((string)config('app.qpc_public_url', ''));
            if ($qpcPublic !== '') {
                return rtrim($qpcPublic, '/');
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

        private function sign(string $middle): string
        {
            return strtoupper(md5($this->merchantId . $middle . $this->merchantKey));
        }

        private function formatAmount($amount): string
        {
            return number_format((float)$amount, 2, '.', '');
        }
    }
}
