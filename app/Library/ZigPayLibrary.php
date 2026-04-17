<?php

namespace App\library {

    use App\Models\Api;
    use App\Models\Apiresponse;
    use App\Library\RefundLibrary;
    use Helpers;

    class ZigPayLibrary
    {
        public function __construct()
        {
            $this->api_id = 15;
            $credentials = json_decode(optional(Api::find($this->api_id))->credentials);
            $this->base_url = rtrim($credentials->base_url ?? 'https://api.zigpay.in', '/');
            $this->mid = $credentials->mid ?? '';
            $this->email = $credentials->email ?? '';
            $this->secretkey = $credentials->secretkey ?? '';
        }

        public function transferNow($user_id, $mobile_number, $amount, $beneficiary_name, $account_number, $ifsc_code, $insert_id)
        {
            $token = $this->generateToken();
            if (empty($token)) {
                Apiresponse::insertGetId([
                    'message' => 'Token generation failed',
                    'api_type' => $this->api_id,
                    'report_id' => $insert_id,
                    'request_message' => $this->base_url . '/api/Auth/generate-token',
                ]);
                return ['status_id' => 3, 'txnid' => 'Authentication failed', 'payid' => ''];
            }

            $paymentMode = 2; // default IMPS
            $bankName = $this->getBanknameByIfsc($ifsc_code);
            $refId = $this->buildRefId($insert_id);

            $payload = [
                'RefID' => $refId,
                'AccountNo' => (string)$account_number,
                'MobileNumber' => (string)$mobile_number,
                'PaymentMode' => $paymentMode,
                'Amount' => (string)$amount,
                'HolderName' => (string)$beneficiary_name,
                'IFSC' => (string)$ifsc_code,
                'latlong' => '0,0',
                'AccountType' => 'savings',
                'BankName' => (string)$bankName,
            ];

            $url = $this->base_url . '/api/OrderPayment/pay-order';
            $headers = [
                'accept: */*',
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
            ];

            $response = Helpers::pay_curl_post($url, $headers, json_encode($payload), 'POST');

            Apiresponse::insertGetId([
                'message' => $response,
                'api_type' => $this->api_id,
                'report_id' => $insert_id,
                'request_message' => $url . '?' . json_encode($payload),
            ]);

            $res = json_decode($response, true);
            if (!is_array($res)) {
                return ['status_id' => 3, 'txnid' => 'Invalid response', 'payid' => ''];
            }

            $status = (string)($res['status'] ?? '0');
            $message = (string)($res['message'] ?? 'Transaction failed');
            $data = is_array($res['data'] ?? null) ? $res['data'] : [];

            $apiStatus = strtolower((string)($data['apI_status'] ?? $data['api_status'] ?? 'pending'));
            $utr = (string)($data['banK_refno'] ?? $data['bank_refno'] ?? '');
            $payid = (string)($data['txn_id'] ?? '');

            if ($status === '1') {
                if ($apiStatus === 'success') {
                    return ['status_id' => 1, 'txnid' => $utr, 'payid' => $payid];
                } elseif ($apiStatus === 'failed') {
                    return ['status_id' => 2, 'txnid' => $message, 'payid' => $payid];
                } else {
                    return ['status_id' => 3, 'txnid' => '', 'payid' => $payid];
                }
            }

            return ['status_id' => 2, 'txnid' => $message, 'payid' => $payid];
        }

        public function get_transaction_status($insert_id)
        {
            $token = $this->generateToken();
            if (empty($token)) {
                return ['status_id' => 3, 'txnid' => 'Authentication failed', 'payid' => ''];
            }

            $url = $this->base_url . '/api/v1/check/status';
            $payload = [
                'RefId' => $this->buildRefId($insert_id),
                'Service_Id' => 2, // payout
            ];
            $headers = [
                'Content-Type: application/json',
                'accept: */*',
                'Authorization: Bearer ' . $token,
            ];

            $response = Helpers::pay_curl_post($url, $headers, json_encode($payload), 'POST');

            Apiresponse::insertGetId([
                'message' => $response,
                'api_type' => $this->api_id,
                'report_id' => $insert_id,
                'request_message' => $url . '?' . json_encode($payload),
            ]);

            $res = json_decode($response, true);
            if (!is_array($res)) {
                return ['status_id' => 3, 'txnid' => 'Invalid response', 'payid' => ''];
            }

            $providerStatus = strtolower((string)($res['status'] ?? 'pending'));
            $utr = (string)($res['utR_RRN'] ?? '');
            $payid = (string)($res['txnID'] ?? '');

            if ($providerStatus === 'success' || $providerStatus === '1') {
                return ['status_id' => 1, 'txnid' => $utr, 'payid' => $payid];
            } elseif ($providerStatus === 'failed' || $providerStatus === '3') {
                $msg = (string)($res['responseMessage'] ?? 'Transaction failed');
                return ['status_id' => 2, 'txnid' => $msg, 'payid' => $payid];
            }

            return ['status_id' => 3, 'txnid' => '', 'payid' => $payid];
        }

        public function checkStatusByCron($insert_id)
        {
            $statusResponse = $this->get_transaction_status($insert_id);
            $statusId = (int)($statusResponse['status_id'] ?? 3);

            if ($statusId === 1) {
                $mode = 'Check status';
                $txnid = $statusResponse['txnid'] ?? '';
                $library = new RefundLibrary();
                return $library->update_transaction($status = 1, $txnid, $insert_id, $mode);
            } elseif ($statusId === 2) {
                $mode = 'Check status';
                $txnid = $statusResponse['txnid'] ?? 'Failed';
                $library = new RefundLibrary();
                return $library->update_transaction($status = 2, $txnid, $insert_id, $mode);
            }

            return null;
        }

        private function generateToken()
        {
            if (empty($this->mid) || empty($this->email) || empty($this->secretkey)) {
                return '';
            }

            $url = $this->base_url . '/api/Auth/generate-token';
            $headers = [
                'accept: */*',
                'Content-Type: application/json',
            ];
            $payload = [
                'mid' => $this->mid,
                'email' => $this->email,
                'secretkey' => $this->secretkey,
            ];

            $response = Helpers::pay_curl_post($url, $headers, json_encode($payload), 'POST');

            $res = json_decode($response, true);
            return (string)($res['token'] ?? '');
        }

        private function buildRefId($insert_id)
        {
            // ZigPay requires Client_RefNo/RefID length between 15 and 35 chars.
            // Keep this deterministic so the same report id maps to same reference.
            return 'ZPO' . date('ymd') . str_pad((string)$insert_id, 8, '0', STR_PAD_LEFT);
        }

        private function getBanknameByIfsc($ifsc_code)
        {
            $url = "https://ifsc.razorpay.com/$ifsc_code";
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $data = curl_exec($curl);
            curl_close($curl);
            $res = json_decode($data);
            return $res->BANK ?? '';
        }
    }
}

