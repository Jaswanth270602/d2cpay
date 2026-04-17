<?php

namespace App\Pay2all {

    use Helpers;
    use App\Models\Api;
    use App\Models\Masterbank;
    use App\Models\Beneficiary;
    use App\Models\Apiresponse;
    use http\Env\Response;
    use App\Pay2all\Apicredentials as Pay2allcredentials;

    class Dmt
    {

        public function __construct()
        {
            $library = new Pay2allcredentials();
            $response =  $library->getCredentials();
            $this->base_url = $response['base_url'];
            $this->authorizationKey = $response['authorizationKey'];
            $this->api_id = $response['api_id'];
        }


        function getCustomer($mobile_number)
        {
            $url = $this->base_url . 'api/v1/dmt/verification';
            $parameters = array(
                'mobile_number' => $mobile_number
            );
            $method = 'POST';
            $header = ["Accept:application/json", "Authorization:" . $this->authorizationKey];
            $response = Helpers::pay_curl_post($url, $header, $parameters, $method);
            $res = json_decode($response);
            $status_id = $res->status_id;
            if ($status_id == 1) {
                $data = array('name' => $res->data->first_name, 'mobile_number' => $mobile_number, 'total_limit' => $res->data->limit);
                return Response(['status' => 'success', 'message' => 'Successfull.', 'ad1' => '', 'ad2' => '', 'data' => $data]);
            } elseif ($status_id == 2) {
                $data = array('is_otp' => $res->otp);
                return Response()->json(['status' => 'pending', 'message' => 'mobile number does not exist', 'ad1' => '', 'ad2' => '', 'data' => $data]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Something went wrong!', 'ad1' => '', 'ad2' => '']);
            }
        }

        function addSender($mobile_number, $first_name, $last_name, $pincode, $state, $address, $ad1, $ad2)
        {
            $url = $this->base_url . 'api/v1/dmt/add_sender';
            $parameters = array(
                'mobile_number' => $mobile_number,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'pin_code' => $pincode,
                'address' => $address,
                'address2' => $address,
                'state' => $state,
            );
            $method = 'POST';
            $header = ["Accept:application/json", "Authorization:" . $this->authorizationKey];
            $response = Helpers::pay_curl_post($url, $header, $parameters, $method);
            $res = json_decode($response);
            $status_id = $res->status_id;
            if ($status_id == 1) {
                return Response()->json(['status' => 'pending', 'message' => $res->message, 'ad1' => '', 'ad2' => '']);
            } else {
                return Response()->json(['status' => 'failure', 'message' => $res->message, 'ad1' => '', 'ad2' => '']);
            }
        }

        function confirmSender($mobile_number, $otp, $ad1, $ad2)
        {
            $url = $this->base_url . 'api/v1/dmt/add_sender_confirm';
            $parameters = array(
                'mobile_number' => $mobile_number,
                'otp' => $otp,
            );
            $method = 'POST';
            $header = ["Accept:application/json", "Authorization:" . $this->authorizationKey];
            $response = Helpers::pay_curl_post($url, $header, $parameters, $method);
            $res = json_decode($response);
            $status_id = $res->status_id;
            if ($status_id == 1) {
                return Response()->json(['status' => 'success', 'message' => $res->message]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => $res->message]);
            }
        }

        function senderResendOtp($mobile_number, $first_name, $last_name, $pincode, $state, $address, $ad1, $ad2)
        {
            $url = $this->base_url . 'api/v1/dmt/add_sender';
            $parameters = array(
                'mobile_number' => $mobile_number,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'pin_code' => $pincode,
                'address' => $address,
                'address2' => $address,
                'state' => $state,
            );
            $method = 'POST';
            $header = ["Accept:application/json", "Authorization:" . $this->authorizationKey];
            $response = Helpers::pay_curl_post($url, $header, $parameters, $method);
            $res = json_decode($response);
            $status_id = $res->status_id;
            if ($status_id == 1) {
                return Response()->json(['status' => 'pending', 'message' => $res->message, 'ad1' => '', 'ad2' => '']);
            } else {
                return Response()->json(['status' => 'failure', 'message' => $res->message, 'ad1' => '', 'ad2' => '']);
            }
        }

        function getAllBeneficiary($mobile_number, $sender_name)
        {
            $url = $this->base_url . "api/v1/dmt/beneficiary?mobile_number=$mobile_number";
            $parameters = array();
            $method = 'GET';
            $header = ["Accept:application/json", "Authorization:" . $this->authorizationKey];
            $response = Helpers::pay_curl_post($url, $header, $parameters, $method);
            $res = json_decode($response);
            $status_id = $res->status_id;
            if ($status_id == 1) {
                $beneficiaries = $res->beneficiaries;
                Self::updateBeneficiary($beneficiaries, $mobile_number, $sender_name);
                $beneficiaryList = Self::getBeneficiaryList($beneficiaries);
                return Response()->json(['status' => 'success', 'message' => 'Successful..!', 'beneficiaries' => $beneficiaryList]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => $res->message]);
            }
        }

        function updateBeneficiary($beneficiaries, $mobile_number, $sender_name)
        {
            foreach ($beneficiaries as $value) {
                $beneficiary = Beneficiary::where('account_number', $value->bank_account_number)->where('benficiary_id', $value->dmtbeneficiary_id)->where('api_id', $this->api_id)->first();
                $data = array(
                    'benficiary_id' => $value->dmtbeneficiary_id,
                    'account_number' => $value->bank_account_number,
                    'ifsc' => $value->ifsc,
                    'bank_name' => $value->bank,
                    'name' => $value->beneficiary_name,
                    'remiter_number' => $mobile_number,
                    'remiter_name' => $sender_name,
                    'status_id' => 1,
                    'api_id' => $this->api_id,
                );
                if ($beneficiary) {
                    $beneficiary_id = $beneficiary->id;
                    Beneficiary::where('id', $beneficiary_id)->update($data);
                } else {
                    Beneficiary::insert($data);
                }
            }
        }

        function getBeneficiaryList($beneficiaries)
        {
            $response = array();
            $i = 1;
            foreach ($beneficiaries as $value) {
                $product = array();
                $product["id"] = $i++;
                $product["beneficiary_id"] = $value->dmtbeneficiary_id;
                $product["bank_name"] = $value->bank;
                $product["mobile_number"] = $value->mobile_number;
                $product["beneficiary_name"] = $value->beneficiary_name;
                $product["ifsc_code"] = $value->ifsc;
                $product["account_number"] = $value->bank_account_number;
                $product["is_verify"] = $value->is_verify;
                $product["status_id"] = $value->active;
                array_push($response, $product);
            }
            return $response;
        }

        function getIfscCode($bank_id)
        {
            $masterbanks = Masterbank::find($bank_id);
            if ($masterbanks) {
                $data = array('ifsc' => $masterbanks->ifsc);
                return Response()->json(['status' => 'success', 'message' => 'Successful..!', 'data' => $data]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Record not found!']);
            }
        }

        function addBeneficiary($mobile_number, $bank_id, $ifsc_code, $account_number, $beneficiary_name)
        {
            $url = $this->base_url . "api/v1/dmt/add_beneficiary";
            $parameters = array(
                'mobile_number' => $mobile_number,
                'bank_account_number' => $account_number,
                'beneficiary_name' => $beneficiary_name,
                'ifsc' => $ifsc_code,
            );
            $method = 'POST';
            $header = ["Accept:application/json", "Authorization:" . $this->authorizationKey];
            $response = Helpers::pay_curl_post($url, $header, $parameters, $method);
            $res = json_decode($response);
            $status_id = $res->status_id;
            if ($status_id == 1) {
                if ($res->otp == 1) {
                    return Response()->json(['status' => 'pending', 'message' => $res->message, 'ad1' => $res->dmtbeneficiary_id, 'ad2' => '']);
                } else {
                    return Response()->json(['status' => 'success', 'message' => 'Beneficiary added successfully', 'ad1' => '', 'ad2' => '']);
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => $res->message, 'ad1' => '', 'ad2' => '']);
            }
        }

        function confirmBeneficiary($mobile_number, $otp, $ad1, $ad2)
        {
            $url = $this->base_url . "api/v1/dmt/add_beneficiary_confirm";
            $parameters = array(
                'mobile_number' => $mobile_number,
                'dmtbeneficiary_id' => $ad1,
                'otp' => $otp,
            );
            $method = 'POST';
            $header = ["Accept:application/json", "Authorization:" . $this->authorizationKey];
            $response = Helpers::pay_curl_post($url, $header, $parameters, $method);
            $res = json_decode($response);
            $status_id = $res->status_id;
            if ($status_id == 1) {
                return Response()->json(['status' => 'success', 'message' => 'Beneficiary Successfully addedd']);
            } else {
                return Response()->json(['status' => 'failure', 'message' => $res->message]);
            }
        }

        function deleteBeneficiary($mobile_number, $beneficiary_id)
        {
            $url = $this->base_url . "api/v1/dmt/delete_beneficiary";
            $parameters = array(
                'mobile_number' => $mobile_number,
                'dmtbeneficiary_id' => $beneficiary_id,
                'vendor_id' => 10,
            );
            $method = 'POST';
            $header = ["Accept:application/json", "Authorization:" . $this->authorizationKey];
            $response = Helpers::pay_curl_post($url, $header, $parameters, $method);
            $res = json_decode($response);
            $status_id = $res->status_id;
            if ($status_id == 1) {
                if ($res->otp == 1) {
                    return Response()->json(['status' => 'pending', 'message' => $res->message, 'ad1' => $beneficiary_id, 'ad2' => '']);
                } else {
                    return Response()->json(['status' => 'success', 'message' => $res->message]);
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => $res->message]);
            }
        }


        function confirmDeleteBeneficiary($mobile_number, $ad1, $ad2, $otp)
        {
            $url = $this->base_url . "api/v1/dmt/delete_beneficiary_confirm";
            $parameters = array(
                'mobile_number' => $mobile_number,
                'vendor_id' => 10,
                'dmtbeneficiary_id' => $ad1,
                'otp' => $otp,
            );
            $method = 'POST';
            $header = ["Accept:application/json", "Authorization:" . $this->authorizationKey];
            $response = Helpers::pay_curl_post($url, $header, $parameters, $method);
            $res = json_decode($response);
            $status_id = $res->status_id;
            if ($status_id == 1) {
                return Response()->json(['status' => 'success', 'message' => $res->message]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => $res->message]);
            }
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
            // $response = '{"status_id":1,"name":"SANJAY KUMAR","message":"Success"}';
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $api_id, 'report_id' => $insert_id, 'request_message' => $url . '?' . json_encode($parameters)]);
            $res = json_decode($response);
            if (isset($res->status_id)){
                $status_id = $res->status_id;
                if ($status_id == 1) {
                    return ['status_id' => 1, 'message' => $res->message, 'name' => $res->name];
                } elseif ($status_id == 2) {
                    return ['status_id' => 2, 'message' => $res->message, 'name' => ''];
                } else {
                    return ['status_id' => 3, 'message' => '', 'name' => ''];
                }
            }else{
                return ['status_id' => 3, 'message' => '', 'name' => ''];
            }
        }


        function transferNow($amount, $user_id, $ifsc_code, $beneficiary_id, $insert_id, $account_number, $mobile_number, $channel_id, $api_id, $latitude, $longitude)
        {
           // return ['status_id' => 2, 'txnid' => '', 'payid' => ''];
            $url = $this->base_url . "api/v1/dmt/transfer";
            $parameters = array(
                'dmtbeneficiary_id' => $beneficiary_id,
                'mobile_number' => $mobile_number,
                'amount' => $amount,
                'client_id' => $insert_id,
                'provider_id' => 39,
                'lat' => $latitude,
                'long' => $longitude,
                'channel_id' => $channel_id,
            );
            $method = 'POST';
            $header = ["Accept:application/json", "Authorization:" . $this->authorizationKey];
            $response = Helpers::pay_curl_post($url, $header, $parameters, $method);
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $api_id, 'report_id' => $insert_id, 'request_message' => $url . '?' . json_encode($parameters)]);
            $res = json_decode($response);
            if (isset($res->status_id)){
                $status_id = $res->status_id;
                if ($status_id == 1) {
                    return ['status_id' => 1, 'txnid' => $res->data->utr, 'payid' => $res->data->report_id];
                } elseif ($status_id == 2) {
                    return ['status_id' => 2, 'txnid' => '', 'payid' => ''];
                } else {
                    return ['status_id' => 3, 'txnid' => '', 'payid' => ''];
                }
            }else{
                return ['status_id' => 3, 'txnid' => '', 'payid' => ''];
            }
        }

        function getBankList()
        {
            $masterbank = Masterbank::where('status_id', 1)->select('bank_id', 'bank_name', 'ifsc')->get();
            $response = array();
            foreach ($masterbank as $value) {
                $product = array();
                $product["bank_id"] = $value->id;
                $product["bank_name"] = $value->bank_name;
                $product["ifsc_code"] = $value->ifsc;
                array_push($response, $product);
            }
            return Response()->json(['status' => 'success', 'message' => 'successful..!', 'bank_list' => $response]);
        }


    }
}