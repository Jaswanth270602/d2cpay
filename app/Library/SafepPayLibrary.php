<?php

namespace App\library {

    use App\Models\Razorpaycontact;
    use App\Models\User;
    use App\Models\Api;
    use App\Models\Apiresponse;
    use Illuminate\Support\Facades\Cache;
    use Str;
    use Illuminate\Support\Facades\Log;


    class SafepPayLibrary
    {
        public function transferNow($user_id, $mobile_number, $amount, $beneficiary_name, $account_number, $ifsc_code, $insert_id)
        {
            $api_id = 14;
        
            Log::info("S: [TransferNow] Initiated", [
                'user_id' => $user_id,
                'insert_id' => $insert_id,
                'amount' => $amount
            ]);
        
            // Get access token
            $api_key = Self::getAccessToken($api_id);
            if (empty($api_key)) {
                Log::error("S: [TransferNow] Authentication failed", [
                    'api_id' => $api_id
                ]);
                return ['status_id' => 2, 'txnid' => 'Authentication failed', 'payid' => ''];
            }
        
            $userDetails = User::find($user_id);
        
            // Split beneficiary name into first and last name
            $nameParts = explode(' ', $beneficiary_name, 2);
            $firstName = $nameParts[0] ?? $beneficiary_name;
            $lastName = $nameParts[1] ?? '';
        
            // Prepare payout request payload
            $parameters = [
                'order_reference' => "$insert_id",
                'payment_amount' => $amount,
                'payment_mode' => 'IMPS',
                'bank_account_number' => $account_number,
                'ifsc_code' => $ifsc_code,
                'payer_first_name' => $firstName,
                'payer_last_name' => $lastName,
                'payer_email' => $userDetails->email ?? 'default@email.com',
                'payer_mobile' => $mobile_number,
                'payment_note' => 'Payment',
                'address' => $userDetails->address ?? ''
            ];
        
            $url = "https://safeppay.com/api/payout/create";
            $parameters_json = json_encode($parameters);
        
            Log::info("S: [TransferNow] Sending payout request", [
                'url' => $url,
                'headers' => ['Authorization' => "Bearer {$api_key}"],
                'payload' => $parameters
            ]);
        
            // Send API request
            $response = Self::SendToApi($parameters_json, $url, $api_key);
        
            // Save request and response to DB
            Apiresponse::insertGetId([
                'message' => $response,
                'api_type' => $api_id,
                'report_id' => $insert_id,
                'request_message' => $parameters_json
            ]);
        
            Log::info("S: [TransferNow] API response received", [
                'response_raw' => $response
            ]);
        
            // Decode response
            $responseDecode = json_decode($response);
            $status = $responseDecode->status ?? false;
        
            if ($status == false) {
                $errorMessage = $responseDecode->message ?? 'Transaction failed';
                Log::error("S: [TransferNow] Transaction failed", [
                    'user_id' => $user_id,
                    'insert_id' => $insert_id,
                    'response' => $responseDecode
                ]);
                return ['status_id' => 2, 'txnid' => $errorMessage, 'payid' => ''];
            }
        
            // Check transaction status from response
            $transactionStatus = $responseDecode->data->transaction->status ?? 'pending';
            $transaction_id = $responseDecode->data->transaction->transaction_id ?? '';
        
            Log::info("S: [TransferNow] Transaction status", [
                'transaction_id' => $transaction_id,
                'status' => $transactionStatus
            ]);
        
            if ($transactionStatus == 'success') {
                $utr = $responseDecode->data->summary->utr ?? '';
                Log::info("S: [TransferNow] Transaction successful", [
                    'transaction_id' => $transaction_id,
                    'utr' => $utr
                ]);
                return ['status_id' => 1, 'txnid' => $utr, 'payid' => $transaction_id];
            } elseif (in_array($transactionStatus, ['initiate', 'pending'])) {
                Log::warning("S: [TransferNow] Transaction pending", [
                    'transaction_id' => $transaction_id
                ]);
                return ['status_id' => 3, 'txnid' => '', 'payid' => $transaction_id];
            } else {
                Log::error("S: [TransferNow] Transaction failed", [
                    'transaction_id' => $transaction_id
                ]);
                return ['status_id' => 2, 'txnid' => 'Transaction failed', 'payid' => $transaction_id];
            }
        }

        function getAccessToken($api_id)
        {
            // Check if token exists in cache
            $cacheKey = 'safeppay_token_' . $api_id;
            $cachedToken = Cache::get($cacheKey);
            
            // Get credentials from database
            $credentials = json_decode(optional(Api::find($api_id))->credentials);
            $client_id = 'd11bf01e-936a-41b5-99b8-34fc75d5edad';
            $client_secret = 'v8addUwIW55EGxoMryEjYKaxJXgdhhOWOGtgGkeA';
            
            if (empty($client_id) || empty($client_secret)) {
                Log::error("S: [getAccessToken] Missing credentials in database", [
                    'api_id' => $api_id,
                    'has_client_id' => !empty($client_id),
                    'has_client_secret' => !empty($client_secret)
                ]);
                return '';
            }
            
            // Generate new token
            $parameters = json_encode(array(
                'client_id' => $client_id,
                'client_secret' => $client_secret
            ));
            
            $url = "https://safeppay.com/api/generate-token";
            
            Log::info("S: [getAccessToken] Requesting new token", [
                'url' => $url,
                'client_id' => $client_id
            ]);
            
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $parameters,
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json"
                ),
            ));
            
            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            
            Log::info("S: [getAccessToken] Token API response", [
                'http_code' => $httpCode,
                'response' => $response
            ]);
            
            $responseDecode = json_decode($response);
            
            // Fix: Use 'access_token' instead of 'token'
            $token = $responseDecode->data->access_token ?? '';
            
            if (!empty($token)) {
                // Cache token for 50 minutes (assuming 1 hour expiry)
                Cache::put($cacheKey, $token, 3000);
                Log::info("S: [getAccessToken] Token cached successfully", ['api_id' => $api_id]);
                return $token;
            }
            
            $errorMessage = $responseDecode->message ?? 'Unknown error';
            Log::error("S: [getAccessToken] Failed to get token", [
                'api_id' => $api_id,
                'error' => $errorMessage,
                'response' => $response
            ]);
            
            return '';
        }

        function SendToApi($parameters, $url, $api_key)
        {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $parameters,
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "Authorization: Bearer $api_key"
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            return $response;
        }
        
        function checkStatus($transaction_id, $api_id = 14)
        {
            $api_key = Self::getAccessToken($api_id);
            
            if (empty($api_key)) {
                return null;
            }
            
            $url = "https://safeppay.com/api/payout/$transaction_id/status";
            
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "Authorization: Bearer $api_key"
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            
            return json_decode($response);
        }
        
        function checkBalance($api_id = 14)
        {
            $api_key = Self::getAccessToken($api_id);
            
            if (empty($api_key)) {
                return null;
            }
            
            $url = "https://safeppay.com/api/balance/payout";
            
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "Authorization: Bearer $api_key"
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            
            return json_decode($response);
        }
    }
}