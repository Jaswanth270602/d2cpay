<?php

namespace App\library {

    use App\Models\Razorpaycontact;
    use App\Models\User;
    use App\Models\Api;
    use App\Models\Apiresponse;
    use Str;
    use Helpers;

    class PunjikendraLibrary
    {

        public function __construct()
        {
            $this->api_id = 10;
            $this->base_url = optional(json_decode(optional(Api::find($this->api_id))->credentials))->base_url ?? '';
            $this->api_key = optional(json_decode(optional(Api::find($this->api_id))->credentials))->api_key ?? '';
        }

        function transferNow($user_id, $mobile_number, $amount, $beneficiary_name, $account_number, $ifsc_code, $insert_id)
        {
            $url = $this->base_url . 'api/payouts';
            $bank_name = $this->getBanknameByIfsc($ifsc_code);
            $parameters = array(
                'Apikey' => $this->api_key,
                'beneName' => $beneficiary_name,
                'beneAccountNo' => $account_number,
                'beneifsc' => $ifsc_code,
                'beneBankName' => $bank_name,
                'clientReferenceNo' => $insert_id,
                'amount' => $amount . '.00',
            );
            $method = 'POST';
            $header = ["Accept:application/json"];
            $response = Helpers::pay_curl_post($url, $header, $parameters, $method);
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'report_id' => $insert_id, 'request_message' => $url . '?' . json_encode($parameters)]);
            $res = json_decode($response);
            $status = $res->status ?? 'Pending';
            if ($status == false) {
                return ['status_id' => 2, 'txnid' => 'Transaction Failed please try after some time', 'payid' => ''];
            }
            $tstatus = $res->tstatus ?? 'INITIATED';
            if ($tstatus == 'SUCCESS') {
                return ['status_id' => 1, 'txnid' => $res->bankReferenceNumber ?? '', 'payid' => ''];
            } elseif ($tstatus == 'Failed') {
                $message = $res->message ?? 'Transaction Failed';
                if (Str::startsWith($message, 'Insufficient balance')) {
                    $message = 'Transaction Failed please try after some time';
                }
                return ['status_id' => 2, 'txnid' => $message, 'payid' => ''];
            } else {
                return ['status_id' => 3, 'txnid' => '', 'payid' => ''];
            }
        }

        function getBanknameByIfsc($ifsc_code)
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