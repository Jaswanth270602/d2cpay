<?php

namespace App\library {

    use App\Models\Apiresponse;
    use App\Models\Api;
    use App\Models\User;


    class PockethubLibrary
    {

        public function __construct()
        {
            $this->api_id = 7;
        }


        function transferNow($user_id, $mobile_number, $amount, $beneficiary_name, $account_number, $ifsc_code, $insert_id)
        {
            $userDetails = User::find($user_id);
            $api_id = 7;
            $base_url = optional(json_decode(optional(Api::find($api_id))->credentials))->base_url ?? '';
            $user_token = optional(json_decode(optional(Api::find($api_id))->credentials))->user_token ?? '';
            $api_password = optional(json_decode(optional(Api::find($api_id))->credentials))->api_password ?? '';
            $merchant_secret_key = optional(json_decode(optional(Api::find($api_id))->credentials))->merchant_secret_key ?? '';

            $bank_name = $this->getBanknameByIfsc($ifsc_code);
            $data = [
                'user_token' => $user_token,
                'api_password' => $api_password,
                'merchant_secret_key' => $merchant_secret_key,
                'transaction_pin' => '5068',
                'account_no' => $account_number,
                'ifsc_code' => $ifsc_code,
                'mobile_no' => $mobile_number,
                'email_id' => $userDetails->email,
                'branch' => 'telangana',
                'bene_name' => $beneficiary_name,
                'bank_name' => $bank_name,
                'transferType' => 'IMPS',
                'remark' => 'Payout',
                'order_id' => $insert_id,
                'amount' => $amount,
            ];
            $url = $base_url . 'payout_request.php';
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $data,
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'report_id' => $insert_id, 'request_message' => $url . '?' . json_encode($data)]);
            $res = json_decode($response);
            $status = $res->status ?? 'pending';
            if ($status == 'success') {
                $UTR = $res->UTR ?? '';
                return ['status_id' => 1, 'txnid' => $UTR, 'payid' => '',];
            }elseif($status == 'failed'){
                return ['status_id' => 2, 'txnid' => '', 'payid' => '',];
            }else{
                return ['status_id' => 3, 'txnid' => '', 'payid' => '',];
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
            return $bankName = (!empty($res->BANK)) ? $res->BANK : '';
        }

        function payinCreateOrder($orderId, $name, $mobile, $email, $amount, $redirectUrl)
        {
            $api_id = 8;
            $base_url = optional(json_decode(optional(Api::find($api_id))->credentials))->base_url ?? '';
            $user_token = optional(json_decode(optional(Api::find($api_id))->credentials))->user_token ?? '';
            $api_password = optional(json_decode(optional(Api::find($api_id))->credentials))->api_password ?? '';
            $merchant_secret_key = optional(json_decode(optional(Api::find($api_id))->credentials))->merchant_secret_key ?? '';
            $data = [
                'order_id' => 'abcdefg_'.$orderId,
                'amount' => $amount,
                'name' => $name,
                'mobile' => $mobile,
                'email' => $email,
                'user_token' => $user_token,
                'api_password' => $api_password,
                'merchant_secret_key' => $merchant_secret_key,
                'redirect_url' => $redirectUrl,
            ];
            $url = $base_url . 'payin_intent.php';
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $data,
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            $res = json_decode($response);
            if (!empty($res->redirectUrl)){
                return [
                    'status_id' => 1,
                    'message' => $res->message ?? 'success',
                    'payment_url' => $res->redirectUrl ?? '',
                    'payid' => $res->Order_id ?? '',
                ];
            }else{
                return ['status_id' => 2, 'message' => $res->message ?? 'failed', 'payment_url' => ''];
            }
        }



    }
}