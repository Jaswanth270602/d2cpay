<?php

namespace App\library {

    use App\Models\Apiresponse;
    use App\Models\Api;
    use App\Models\User;


    class LetspeLibrary
    {

        public function __construct()
        {
            $this->api_id = 9;
            $this->base_url = optional(json_decode(optional(Api::find($this->api_id))->credentials))->base_url ?? '';
            $this->api_key = optional(json_decode(optional(Api::find($this->api_id))->credentials))->api_key ?? '';
            $this->salt_key = optional(json_decode(optional(Api::find($this->api_id))->credentials))->salt_key ?? '';
            $this->encryption_key = optional(json_decode(optional(Api::find($this->api_id))->credentials))->encryption_key ?? '';
        }


        function createOrders($order_id, $amount, $mobile_number, $username, $email_id)
        {

            $signature = Self::generateSignature($order_id, $amount, $mobile_number, $username, $email_id);
            $payload = json_encode([
                'apiKey' => $this->api_key,
                'amount' => $amount,
                'emailId' => $email_id,
                'mobileNumber' => $mobile_number,
                'username' => $username,
                'requestedOrderId' => $order_id,
                'signature' => $signature,
            ]);
            echo $encryptPayload = json_encode([
                'apiKey' => $this->api_key,
                'encrypted_data' => Self::encryptAES($payload),
            ]);
        }


        function generateSignature($order_id, $amount, $mobile_number, $username, $email_id)
        {
            $string = $this->api_key . $amount . $email_id . $mobile_number . $order_id;
            return hash_hmac('sha512', $string, $this->salt_key);
        }


        public function encryptAES($data)
        {
            $key = $this->encryption_key;
            $cipher = "AES-192-ECB";
            $encrypted = openssl_encrypt($data, $cipher, $key, OPENSSL_RAW_DATA);
            return base64_encode($encrypted);
        }

        /*   function encryptAES($data) {
               $method = 'AES-128-ECB'; // ECB mode
               $encrypted = openssl_encrypt($data, $method, $this->encryption_key, OPENSSL_RAW_DATA, $iv = '');
               return base64_encode($encrypted);
           }

           function decryptAES($encryptedData) {
               $method = 'AES-128-ECB'; // ECB mode
               $encrypted = base64_decode($encryptedData);
               return openssl_decrypt($encrypted, $method, $this->encryption_key, OPENSSL_RAW_DATA);
           }*/


    }
}