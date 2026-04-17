<?php

namespace App\library {

    use App\Models\Apiresponse;
    use App\Models\User;
    use Helpers;


    class AccosisLibrary
    {

        public function __construct()
        {
            $this->api_key = 'IwDrk31WhzRuxMeI06eF1Dyhdul1lHb6';
            $this->api_secret = 'Kg3EiBSgNgwAl1hpxDKZWUgVPRAfw1kW';
            $this->baseUrl = 'https://api.accosis.com/';
        }


        function impsTransfer($user_id, $mobile_number, $amount, $holder_name, $account_number, $ifsc_code, $insert_id, $vender_id, $api_id, $latitude, $longitude)
        {
            $referenceId = '00000000' . $insert_id;
            //$hash = Self::generateImpsHash($referenceId, $amount, $user_id);
            $userDetails = User::find($user_id);
            $url = $this->baseUrl . 'v1/neo-banking/e-fund-transfer-bulk';
            $bodyJson = array(
                [
                    'name' => $holder_name,
                    'email' => $userDetails->email,
                    'phone' => $mobile_number,
                    'bankAccount' => $account_number,
                    'ifsc' => $ifsc_code,
                    'amount' => $amount . '.00',
                    'paymentMode' => 'IMPS',
                    'referenceId' => $referenceId,
                ]
            );
            $headers = array(
                'apiKey: ' . $this->api_key . '',
                'secretKey: ' . $this->api_secret . '',
                'Content-Type: application/json'
            );
            $response = Self::impsPostApi($url, $bodyJson, $headers);
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $api_id, 'report_id' => $insert_id, 'request_message' => $url . '?' . json_encode($bodyJson)]);
            $res = json_decode($response);
            $respCode = $res->respCode ?? 201;
            if ($respCode == 309 || $respCode == 99) {
                return ['status_id' => 2, 'txnid' => '', 'payid' => ''];
            }
            return ['status_id' => 3, 'txnid' => '', 'payid' => ''];
        }

        function generateImpsHash($insert_id, $amount, $user_id)
        {
            $userDetails = User::find($user_id);
            $url = $this->baseUrl . 'v1/pg/generate-hash';
            $bodyJson = [
                "referenceId" => $insert_id,
                "amount" => number_format($amount, 2, '.', ''),
                "customerDetails" => [
                    "name" => $userDetails->name,
                    "mobileNo" => $userDetails->mobile ?? '0000000000',
                    "emailId" => $userDetails->email ?? 'noemail@domain.com',
                ],
                "paymentDetails" => [
                    "mode" => "IMPSPAY",
                    "bankCode" => "ACCPO",
                ],
            ];
            $headers = array(
                'apiKey: ' . $this->api_key . '',
                'secretKey: ' . $this->api_secret . '',
                'Content-Type: application/json'
            );
            return Self::impsPostApi($url, $bodyJson, $headers);
        }

        function impsPostApi($url, $bodyJson, $headers)
        {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($bodyJson),
                CURLOPT_HTTPHEADER => $headers,
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            return $response;
        }

    }

}