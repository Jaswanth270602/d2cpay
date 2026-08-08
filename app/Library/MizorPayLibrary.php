<?php

namespace App\library {

    use App\Models\Api;
    use App\Models\Apiresponse;
    use App\Library\RefundLibrary;
    use Helpers;

    class MizorPayLibrary
    {
        public const PAYOUT_MIN = 100;
        public const PAYOUT_MAX = 25000;

        private $api_id;
        private $base_url;
        private $tokenId;
        private $secretKey;

        public function __construct()
        {
            $this->api_id = 20;
            $credentials = json_decode(optional(Api::find($this->api_id))->credentials);
            $this->base_url = rtrim($credentials->base_url ?? 'https://payout.mizorpay.in', '/');
            $this->tokenId = trim((string)($credentials->token_id ?? $credentials->Token_Id ?? $credentials->tokenId ?? ''));
            $this->secretKey = trim((string)($credentials->secret_key ?? $credentials->Secret_Key ?? $credentials->secretKey ?? ''));
        }

        public function buildPayoutRef($insertId): string
        {
            return 'MZP' . date('ymd') . str_pad((string)$insertId, 11, '0', STR_PAD_LEFT);
        }

        public static function normalizeStatus(string $status): string
        {
            $status = strtoupper(trim($status));
            if (in_array($status, ['SUCCESS', 'SUCCESSFUL', 'COMPLETED', 'PAID'], true)) {
                return 'SUCCESS';
            }
            if (in_array($status, ['FAILED', 'FAILURE', 'REJECTED', 'CANCELLED', 'CANCELED'], true)) {
                return 'FAILED';
            }
            return 'PENDING';
        }

        private function authHeaders(): array
        {
            return [
                'Accept: application/json',
                'Content-Type: application/json',
                'Token-Id: ' . $this->tokenId,
                'Secret-Key: ' . $this->secretKey,
            ];
        }

        private function resolveBankName($ifsc_code, $bank_name = null): string
        {
            $bankName = trim((string)$bank_name);
            if ($bankName !== '') {
                return strtoupper(substr($bankName, 0, 11));
            }

            $ifsc = strtoupper(trim((string)$ifsc_code));
            if (strlen($ifsc) >= 4) {
                return substr($ifsc, 0, 4);
            }

            return 'BANK';
        }

        private function parseBulkCreateResponse(array $res, string $merchantRef): array
        {
            $topError = trim((string)($res['error'] ?? $res['message'] ?? ''));
            if ($topError !== '') {
                return [
                    'status_id' => 2,
                    'txnid' => $topError,
                    'payid' => $merchantRef,
                ];
            }

            foreach ($res['successful'] ?? [] as $item) {
                if (!is_array($item)) {
                    continue;
                }
                if ((string)($item['transaction_id'] ?? '') !== $merchantRef) {
                    continue;
                }

                $providerStatus = strtolower((string)($item['status'] ?? 'queued'));
                if (in_array($providerStatus, ['success', 'successful', 'completed'], true)) {
                    return ['status_id' => 1, 'txnid' => $merchantRef, 'payid' => $merchantRef];
                }
                if (in_array($providerStatus, ['failed', 'rejected', 'cancelled'], true)) {
                    return [
                        'status_id' => 2,
                        'txnid' => \App\Library\LockHoldPayoutLibrary::REASON_BANK_DOWNTIME,
                        'payid' => $merchantRef,
                    ];
                }

                return ['status_id' => 3, 'txnid' => '', 'payid' => $merchantRef];
            }

            $errors = $res['errors'] ?? [];
            if (is_array($errors)) {
                foreach ($errors as $errorItem) {
                    if (!is_array($errorItem)) {
                        if (is_string($errorItem) && stripos($errorItem, 'insufficient') !== false) {
                            return [
                                'status_id' => 2,
                                'txnid' => $errorItem,
                                'payid' => $merchantRef,
                            ];
                        }
                        continue;
                    }

                    if (isset($errorItem['error']) && is_string($errorItem['error'])) {
                        return [
                            'status_id' => 2,
                            'txnid' => $errorItem['error'],
                            'payid' => $merchantRef,
                        ];
                    }

                    $txnErrors = $errorItem['transaction_id'] ?? null;
                    if (is_array($txnErrors)) {
                        return [
                            'status_id' => 2,
                            'txnid' => implode(', ', $txnErrors),
                            'payid' => $merchantRef,
                        ];
                    }
                }
            }

            if ((int)($res['success'] ?? 0) === 0 && (int)($res['failed'] ?? 0) > 0) {
                return [
                    'status_id' => 2,
                    'txnid' => \App\Library\LockHoldPayoutLibrary::REASON_BANK_DOWNTIME,
                    'payid' => $merchantRef,
                ];
            }

            return ['status_id' => 3, 'txnid' => '', 'payid' => $merchantRef];
        }

        public function transferNow($user_id, $mobile_number, $amount, $beneficiary_name, $account_number, $ifsc_code, $insert_id, $channel_id = null, $bank_name = null, $email = null)
        {
            if ($this->tokenId === '' || $this->secretKey === '') {
                return ['status_id' => 3, 'txnid' => 'MizorPay credentials missing', 'payid' => ''];
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
            $user = \App\Models\User::find($user_id);
            $accountNumber = (string)$account_number;
            $bankName = $this->resolveBankName($ifsc_code, $bank_name);

            $payload = [[
                'bank_name' => $bankName,
                'account_number' => $accountNumber,
                'confirm_account_number' => $accountNumber,
                'ifsc_code' => strtoupper((string)$ifsc_code),
                'beneficiary_name' => (string)$beneficiary_name,
                'amount' => (int)round($amount),
                'email' => (string)($email ?: ($user->email ?? 'noreply@d2cpay.co')),
                'mobile' => (string)$mobile_number,
                'transaction_id' => $merchantRef,
            ]];

            $url = $this->base_url . '/api/payment-initiate-bulk-v3';
            $headers = $this->authHeaders();
            $body = json_encode($payload, JSON_UNESCAPED_SLASHES);
            $response = Helpers::pay_curl_post($url, $headers, $body, 'POST');

            Apiresponse::insertGetId([
                'message' => $response,
                'api_type' => $this->api_id,
                'response_type' => 'payout_create',
                'report_id' => $insert_id,
                'request_message' => $url . '?' . $body,
            ]);

            $res = json_decode($response, true);
            if (!is_array($res)) {
                return ['status_id' => 3, 'txnid' => 'Invalid response', 'payid' => $merchantRef];
            }

            return $this->parseBulkCreateResponse($res, $merchantRef);
        }

        public function get_transaction_status($insert_id)
        {
            if ($this->tokenId === '' || $this->secretKey === '') {
                return ['status_id' => 3, 'txnid' => 'MizorPay credentials missing', 'payid' => ''];
            }

            $merchantRef = $this->buildPayoutRef($insert_id);
            $url = $this->base_url . '/api/payout/check-status';
            $payload = ['txn_id' => $merchantRef];
            $headers = $this->authHeaders();
            $body = json_encode($payload, JSON_UNESCAPED_SLASHES);
            $response = Helpers::pay_curl_post($url, $headers, $body, 'POST');

            Apiresponse::insertGetId([
                'message' => $response,
                'api_type' => $this->api_id,
                'response_type' => 'status_check',
                'report_id' => $insert_id,
                'request_message' => $url . '?' . $body,
            ]);

            $res = json_decode($response, true);
            if (!is_array($res)) {
                return ['status_id' => 3, 'txnid' => 'Invalid response', 'payid' => $merchantRef];
            }

            $payout = is_array($res['payout'] ?? null) ? $res['payout'] : $res;
            $providerStatus = self::normalizeStatus((string)($payout['status'] ?? 'PENDING'));
            $utr = (string)($payout['utr'] ?? '');
            $mizorTxnId = (string)($payout['mizor_pay_txn_id'] ?? '');

            if ($providerStatus === 'SUCCESS') {
                return ['status_id' => 1, 'txnid' => $utr !== '' ? $utr : $mizorTxnId, 'payid' => $merchantRef];
            }
            if ($providerStatus === 'FAILED') {
                $msg = (string)($payout['message'] ?? $payout['error'] ?? \App\Library\LockHoldPayoutLibrary::REASON_BANK_DOWNTIME);
                return ['status_id' => 2, 'txnid' => $msg, 'payid' => $merchantRef];
            }

            return ['status_id' => 3, 'txnid' => $mizorTxnId, 'payid' => $merchantRef];
        }

        public function checkBalance(): array
        {
            if ($this->tokenId === '' || $this->secretKey === '') {
                return ['ok' => false, 'balance' => 0, 'message' => 'MizorPay credentials missing'];
            }

            $url = $this->base_url . '/api/check-balance';
            $response = Helpers::pay_curl_post($url, $this->authHeaders(), '', 'GET');
            $res = json_decode($response, true);

            if (!is_array($res)) {
                return ['ok' => false, 'balance' => 0, 'message' => 'Invalid response'];
            }

            if (!empty($res['error'])) {
                return ['ok' => false, 'balance' => 0, 'message' => (string)$res['error']];
            }

            return [
                'ok' => true,
                'balance' => (float)($res['balance'] ?? 0),
                'message' => (string)($res['success'] ?? 'OK'),
            ];
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
    }
}
