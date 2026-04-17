<?php

namespace App\library {

    use App\Models\Apiresponse;
    use App\Models\Api;
    use App\Library\RefundLibrary;


    class VtransactLibrary
    {

        public function __construct()
        {
            $this->api_id = 12;
            $this->base_url = optional(json_decode(optional(Api::find($this->api_id))->credentials))->base_url ?? '';
            $this->merchantId = optional(json_decode(optional(Api::find($this->api_id))->credentials))->merchantId ?? '';
            $this->clientid = optional(json_decode(optional(Api::find($this->api_id))->credentials))->clientid ?? '';
            $this->clientSecretKey = optional(json_decode(optional(Api::find($this->api_id))->credentials))->clientSecretKey ?? '';
        }


        function transferNow($user_id, $mobile_number, $amount, $beneficiary_name, $account_number, $ifsc_code, $insert_id)
        {
            $url = 'https://api.vtransact.in/VTranSact564895/api/v1/Integrate/vtransactPayout';
            $queryParams = http_build_query([
                'payout_refno' => $insert_id,
                'amount' => $amount,
                'payout_mode' => 'IMPS',
                'user_mobile_number' => $mobile_number,
                'account_name' => $beneficiary_name,
                'account_no' => $account_number,
                'ifsc' => $ifsc_code,
            ]);
            $fullUrl = $url . '?' . $queryParams;
            $postData = [
                'merchantId' => $this->merchantId,
                'clientid' => $this->clientid,
                'clientSecretKey' => $this->clientSecretKey,
            ];
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $fullUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                ],
                CURLOPT_POSTFIELDS => json_encode($postData),
            ]);
            $response = curl_exec($ch);
            $curlErrNo = curl_errno($ch);
            $curlErr = curl_error($ch);
            curl_close($ch);
            if ($curlErrNo) {
                Apiresponse::insertGetId(['message' => $curlErr, 'api_type' => $this->api_id, 'report_id' => $insert_id, 'request_message' => $fullUrl]);
                return ['status_id' => 3, 'txnid' => $curlErr, 'payid' => ''];
            }
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'report_id' => $insert_id, 'request_message' => $fullUrl]);
            sleep(15);
            return Self::get_transaction_status($insert_id);
        }


        function get_transaction_status($insert_id)
        {
            $url = 'https://api.vtransact.in/VTranSact564895/api/v1/Integrate/VTranSactStatusCheck';
            $queryParams = http_build_query([
                'payout_refno' => $insert_id,
            ]);
            $fullUrl = $url . '?' . $queryParams;
            $postData = [
                'merchantId' => $this->merchantId,
                'clientid' => $this->clientid,
                'clientSecretKey' => $this->clientSecretKey,
            ];
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $fullUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                ],
                CURLOPT_POSTFIELDS => json_encode($postData),
            ]);
            $response = curl_exec($ch);
            $curlErrNo = curl_errno($ch);
            $curlErr = curl_error($ch);
            curl_close($ch);
            if ($curlErrNo) {
                Apiresponse::insertGetId(['message' => $curlErr, 'api_type' => $this->api_id, 'report_id' => $insert_id, 'request_message' => $fullUrl]);
                return ['status_id' => 3, 'txnid' => $curlErr, 'payid' => ''];
            }
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'report_id' => $insert_id, 'request_message' => $fullUrl]);
            $res = json_decode($response);
            $txnStatus = $res->txnStatus ?? 'Pending';
            if ($txnStatus == 'Success') {
                return ['status_id' => 1, 'txnid' => $res->rrn ?? '', 'payid' => ''];
            } else {
                return ['status_id' => 3, 'txnid' => '', 'payid' => ''];
            }
        }

        function checkStatusByCron($insert_id)
        {
            $url = 'https://api.vtransact.in/VTranSact564895/api/v1/Integrate/VTranSactStatusCheck';
            $queryParams = http_build_query([
                'payout_refno' => $insert_id,
            ]);
            $fullUrl = $url . '?' . $queryParams;
            $postData = [
                'merchantId' => $this->merchantId,
                'clientid' => $this->clientid,
                'clientSecretKey' => $this->clientSecretKey,
            ];
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $fullUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                ],
                CURLOPT_POSTFIELDS => json_encode($postData),
            ]);
            $response = curl_exec($ch);
            $curlErrNo = curl_errno($ch);
            $curlErr = curl_error($ch);
            curl_close($ch);
            if ($curlErrNo) {
                Apiresponse::insertGetId(['message' => $curlErr, 'api_type' => $this->api_id, 'report_id' => $insert_id, 'request_message' => $fullUrl]);
            }
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'report_id' => $insert_id, 'request_message' => $fullUrl]);
            $res = json_decode($response);
            $txnStatus = $res->txnStatus ?? 'Pending';
            if ($txnStatus == 'Success') {
                $mode = 'Check status';
                $txnid = $res->rrn ?? '';
                $library = new RefundLibrary();
                return $library->update_transaction($status = 1, $txnid, $insert_id, $mode);
            }elseif ($txnStatus == 'Refunded'){
                $mode = 'Check status';
                $txnid = $res->statusDescription ?? '';
                $library = new RefundLibrary();
                return $library->update_transaction($status = 2, $txnid, $insert_id, $mode);
            }
        }


    }

}