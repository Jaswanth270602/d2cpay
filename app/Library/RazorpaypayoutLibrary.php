<?php

namespace App\library {

    use App\Models\Razorpaycontact;
    use App\Models\User;
    use App\Models\Api;
    use App\Models\Apiresponse;
    use Str;

    class RazorpaypayoutLibrary
    {


        function transferNow($user_id, $mobile_number, $amount, $beneficiary_name, $account_number, $ifsc_code, $insert_id)
        {
            $api_id = 11;
            $api_key = optional(json_decode(optional(Api::find($api_id))->credentials))->Authorization ?? '';
            $razorAccountNumber = optional(json_decode(optional(Api::find($api_id))->credentials))->account_number ?? '';
            $razorpaycontacts = Razorpaycontact::where('mobile_number', $mobile_number)->where('account_number', $account_number)->where('api_id', $api_id)->first();
            if (empty($razorpaycontacts)) {
                $response = Self::addContact($amount, $user_id, $ifsc_code, $insert_id, $account_number, $mobile_number, $beneficiary_name, $api_id);
                if ($response['status'] == 'failure') {
                    return ['status_id' => 2, 'txnid' => '', 'payid' => ''];
                }
            }
            $razorpaycontacts = Razorpaycontact::where('mobile_number', $mobile_number)->where('account_number', $account_number)->where('api_id', $api_id)->first();
            if ($razorpaycontacts) {
                $parameters = array(
                    'account_number' => $razorAccountNumber,
                    'fund_account_id' => "$razorpaycontacts->razorpay_id",
                    'amount' => $amount . '00',
                    'currency' => 'INR',
                    'mode' => 'IMPS',
                    'purpose' => 'payout',
                    'queue_if_low_balance' => true,
                    'reference_id' => "$insert_id",
                    'narration' => 'Payment',
                );
                $url = "https://api.razorpay.com/v1/payouts";
                $parameters = json_encode($parameters);
                $response = Self::SendToApi($parameters, $url, $api_key);
                Apiresponse::insertGetId(['message' => $response, 'api_type' => $api_id, 'report_id' => $insert_id, 'request_message' => $parameters]);
                $responseDecode = json_decode($response);
                $errorCode = $responseDecode->error->code ?? '';
                if ($errorCode == 'BAD_REQUEST_ERROR') {
                    $description = $responseDecode->error->description ?? '';
                    return ['status_id' => 2, 'txnid' => $description, 'payid' => ''];
                }
                $status = $responseDecode->status ?? 'pending';
                if ($status == 'processed') {
                    return ['status_id' => 1, 'txnid' => $responseDecode->utr ?? '', 'payid' => ''];
                }
                return ['status_id' => 3, 'txnid' => '', 'payid' => ''];
            } else {
                $description = $responseDecode->error->description ?? '';
                return ['status_id' => 2, 'txnid' => $description, 'payid' => ''];
            }
        }

        function addContact($amount, $user_id, $ifsc_code, $insert_id, $account_number, $mobile_number, $beneficiary_name, $api_id)
        {
            $api_key = optional(json_decode(optional(Api::find($api_id))->credentials))->Authorization ?? '';
            $userDetails = User::find($user_id);
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            $razorpaycontact_id = Razorpaycontact::insertGetId([
                'user_id' => $user_id,
                'account_number' => $account_number,
                'mobile_number' => $mobile_number,
                'created_at' => $ctime,
                'status_id' => 3,
                'api_id' => $api_id,
            ]);
            $parameters = json_encode(array(
                'name' => $userDetails->name . ' ' . $userDetails->last_name,
                'email' => $userDetails->email,
                'contact' => $userDetails->mobile,
                'type' => 'vendor',
                'reference_id' => "$razorpaycontact_id",
            ));
            $url = "https://api.razorpay.com/v1/contacts";
            $response = Self::SendToApi($parameters, $url, $api_key);
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $api_id, 'report_id' => $insert_id, 'request_message' => $parameters]);
            $responseDecode = json_decode($response);
            $respnseStatus = (empty($responseDecode->active)) ? false : $responseDecode->active;
            if ($respnseStatus == true) {
                Razorpaycontact::where('id', $razorpaycontact_id)->update(['contact_id' => $responseDecode->id]);
                $bank_account = array(
                    'name' => $beneficiary_name,
                    'ifsc' => $ifsc_code,
                    'account_number' => $account_number,
                );
                $parameters = array(
                    'contact_id' => $responseDecode->id,
                    'account_type' => 'bank_account',
                    'bank_account' => $bank_account,
                );
                $url = "https://api.razorpay.com/v1/fund_accounts";
                $parameters = json_encode($parameters);
                $response = Self::SendToApi($parameters, $url, $api_key);
                Apiresponse::insertGetId(['message' => $response, 'api_type' => $api_id, 'report_id' => $insert_id, 'request_message' => $parameters]);
                $decodeAccountRes = json_decode($response);
                $decodeStatus = (empty($decodeAccountRes->active)) ? false : $decodeAccountRes->active;
                if ($decodeStatus == true) {
                    Razorpaycontact::where('id', $razorpaycontact_id)->update(['razorpay_id' => $decodeAccountRes->id, 'status_id' => 1]);
                    return ['status' => 'success', 'message' => 'Successful..!'];
                }

            }
            Razorpaycontact::where('id', $razorpaycontact_id)->delete();
            return ['status' => 'failure', 'message' => 'Something went wrong'];
        }

        function SendToApi($parameters, $url, $api_key)
        {
            $idempotencyKey = Str::uuid(); // Or use any unique string

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
                    "Authorization: $api_key",
                    "X-Payout-Idempotency: $idempotencyKey",
                    "Content-Type: text/plain"
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            return $response;
        }

    }
}