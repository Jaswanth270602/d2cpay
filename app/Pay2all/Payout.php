<?php

namespace App\Pay2all {

    use Helpers;
    use App\Models\Api;
    use App\Models\Masterbank;
    use App\Models\Beneficiary;
    use App\Models\Apiresponse;
    use http\Env\Response;
    use App\Pay2all\Apicredentials as Pay2allcredentials;

    class Payout
    {

        public function __construct()
        {
            $library = new Pay2allcredentials();
            $response = $library->getCredentials();
            $this->base_url = $response['base_url'];
            $this->authorizationKey = $response['authorizationKey'];
            $this->api_id = $response['api_id'];
        }


        function accountVerify($mobile_number, $bank_id, $ifsc_code, $account_number, $insert_id, $api_id)
        {
            $url = $this->base_url . "api/v1/verify/bank_account";
            $parameters = array(
                'mobile_number' => $mobile_number,
                'number' => $account_number,
                'ifsc' => $ifsc_code,
                'provider_id' => 127,
                'client_id' => $insert_id
            );
            $method = 'POST';
            $header = ["Accept:application/json", "Authorization:" . $this->authorizationKey];
            $response = Helpers::pay_curl_post($url, $header, $parameters, $method);
            // $response = '{"status_id":1,"name":"niraj singh chauhan","message":"Success"}';
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $api_id, 'report_id' => $insert_id, 'request_message' => $url . '?' . json_encode($parameters)]);
            $res = json_decode($response);
            if (isset($res->status_id)) {
                $status_id = $res->status_id;
                if ($status_id == 1) {
                    return ['status_id' => 1, 'message' => $res->message, 'name' => $res->name];
                } elseif ($status_id == 2) {
                    return ['status_id' => 2, 'message' => $res->message, 'name' => ''];
                } else {
                    return ['status_id' => 3, 'message' => '', 'name' => ''];
                }
            } else {
                return ['status_id' => 3, 'message' => '', 'name' => ''];
            }
        }

        function transferNow($user_id, $mobile_number, $amount, $holder_name, $account_number, $ifsc_code, $insert_id, $vender_id, $api_id, $latitude, $longitude)
        {
            $url = $this->base_url . "api/v1/payout/bank_transfer";
            $parameters = array(
                'mobile_number' => $mobile_number,
                'account_number' => $account_number,
                'beneficiary_name' => $holder_name,
                'ifsc' => $ifsc_code,
                'provider_id' => 160,
                'client_id' => $insert_id,
                'amount' => $amount,
                'wallet_id' => 0,
                'lat' => $latitude,
                'long' => $longitude,
            );
            $method = 'POST';
            $header = ["Accept:application/json", "Authorization:" . $this->authorizationKey];
            $response = Helpers::pay_curl_post($url, $header, $parameters, $method);
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $api_id, 'report_id' => $insert_id, 'request_message' => $url . '?' . json_encode($parameters)]);
            $res = json_decode($response);
            if (isset($res->status_id)) {
                $status_id = $res->status_id;
                if ($status_id == 1) {
                    return ['status_id' => 1, 'txnid' => $res->utr, 'payid' => $res->report_id];
                } elseif ($status_id == 2) {
                    return ['status_id' => 2, 'txnid' => '', 'payid' => ''];
                } else {
                    return ['status_id' => 3, 'txnid' => '', 'payid' => ''];
                }
            } else {
                return ['status_id' => 3, 'txnid' => '', 'payid' => ''];
            }

        }


    }
}