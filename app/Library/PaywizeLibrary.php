<?php

namespace App\library {

    use App\Models\Apiresponse;


    class PaywizeLibrary
    {

        public function __construct()
        {
            $this->api_id = 5;
            $this->api_key = 'oZOvYo7YItWrZUXOaL7TW0T0Irsgbats';
            $this->secret_key = 'UBvGSaNRYzdK2Wj8';
        }


        function transferNow($user_id, $mobile_number, $amount, $beneficiary_name, $account_number, $ifsc_code, $insert_id)
        {
            $sender_id = 'marspay' . $insert_id;
            $data = [
                "amount" => $amount . '.00',
                "payment_mode" => "IMPS",
                "beneficiary_ifsc" => $ifsc_code,
                "beneficiary_acc_number" => $account_number,
                "beneficiary_name" => $beneficiary_name,
                "remarks" => "Payout",
                "sender_id" => $sender_id,
            ];
            $payload = Self::encryptData($data);
            $url = 'https://app.paywize.in/api/v1/payout/request-payout';
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'X-Api-Key: ' . $this->api_key . '',
                    'X-Secret-Key: ' . $this->secret_key . '',
                    'Content-Type: application/json',
                    'Accept: application/json',
                ],
                CURLOPT_POSTFIELDS => json_encode([
                    'payload' => $payload
                ]),
                CURLOPT_TIMEOUT => 30,
            ]);
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'report_id' => $insert_id, 'request_message' => $url . '?' . json_encode($data)]);
            $res = json_decode($response);
            $success = $res->success;
            if ($success == true) {
                $data = $res->data;
                $status = $data->status;
                if ($status == 'Success') {
                    return ['status_id' => 1, 'txnid' => $data->utr_number, 'payid' => ''];
                } elseif ($status == 'Failed') {
                    return ['status_id' => 2, 'txnid' => '', 'payid' => ''];
                } else {
                    return ['status_id' => 3, 'txnid' => '', 'payid' => ''];
                }
            } elseif ($success == false) {
                $errors = $res->errors ?? 'Transaction Failed';
                if ($errors == 'Insufficient Balance.') {
                    return ['status_id' => 2, 'txnid' => 'Transaction Failed', 'payid' => ''];
                }
                return ['status_id' => 2, 'txnid' => $errors, 'payid' => ''];
            }
            return ['status_id' => 3, 'txnid' => '', 'payid' => ''];
        }


        function encryptData($data)
        {
            $cipherMethod = 'AES-256-CBC';
            $iv = $this->secret_key; // 16 characters secret key
            $key = $this->api_key; // 32 characters API key
            // If data is an array, convert it to JSON
            if (is_array($data)) {
                $jsonData = json_encode($data);
            }
            // Encrypt the data
            $encrypted = openssl_encrypt(
                $jsonData,        // Data to encrypt
                $cipherMethod,    // Cipher method (AES-256-CBC)
                $key,             // Encryption key
                OPENSSL_RAW_DATA, // Raw data mode for OpenSSL
                $iv               // Initialization Vector
            );
            if ($encrypted === false) {
                throw new Exception('Encryption failed.');
            }
            return base64_encode($encrypted);
        }

        function decryptData($encryptedData)
        {
            $cipherMethod = 'AES-256-CBC';
            $iv = $this->secret_key; // 16 characters secret key
            $key = $this->api_key; // 32 characters API key
            // Decode base64-encoded data
            $decodedData = base64_decode($encryptedData);
            // Decrypt data
            $decrypted = openssl_decrypt(
                $decodedData,     // Encrypted data
                $cipherMethod,    // Cipher method (AES-256-CBC)
                $key,             // Encryption key
                OPENSSL_RAW_DATA, // Raw data mode for OpenSSL
                $iv               // Initialization Vector
            );
            if ($decrypted === false) {
                throw new Exception('Decryption failed.');
            }
            return json_decode($decrypted, true) ?? [];
        }


    }

}