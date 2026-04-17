<?php

namespace App\library {

    use DB;
    use App\Models\Company;
    use App\Models\Api;
    use App\Models\User;
    use App\Models\Frenzopay;
    use App\Models\Apiresponse;
    use App\Models\Report;
    use Helpers;
    use App\Library\RefundLibrary;
    use Symfony\Component\HttpFoundation\Response;

    class SprezapayLibrary
    {
        public function __construct()
        {
            $this->api_id = 6;
            $apis = Api::find($this->api_id);
            $this->api_key = $apis->api_key;
        }


        function transferNow($user_id, $mobile_number, $amount, $beneficiary_name, $account_number, $ifsc_code, $insert_id)
        {
            $userDetails = User::find($user_id);
            $url = 'https://erp.pay2all.in/api/v1/payout/bank_transfer';
            $parameters = array(
                'mobile_number' => $mobile_number,
                'account_number' => $account_number,
                'beneficiary_name' => $beneficiary_name,
                'ifsc' => $ifsc_code,
                'provider_id' => 160,
                'client_id' => $insert_id,
                'amount' => $amount,
                'wallet_id' => 0,
                'lat' => '0.00',
                'long' => '0.00',
            );
            $method = 'POST';
            $header = ["Accept:application/json", "Authorization:" . $this->api_key];
            $response = Helpers::pay_curl_post($url, $header, $parameters, $method);
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'report_id' => $insert_id, 'request_message' => $url . '?' . json_encode($parameters)]);
            $res = json_decode($response);
            $status_id = $res->status_id ?? 3;
            $message = $res->message ?? 'Transaction Failed.';
            if ($status_id == 1) {
                return ['status_id' => 1, 'txnid' => $res->utr ?? '', 'payid' => ''];
            } elseif ($status_id == 2) {
                if ($message == 'Insufficient balance') {
                    $message = 'Transaction Failed. Please try again later.';
                }
                return ['status_id' => 2, 'txnid' => $message, 'payid' => '',];
            } else {
                return ['status_id' => 3, 'txnid' => '', 'payid' => '',];
            }
        }


    }
}