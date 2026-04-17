<?php

namespace App\Paysprint {

    use Helpers;
    use App\Models\Api;
    use App\Models\Masterbank;
    use App\Models\Beneficiary;
    use App\Models\Apiresponse;
    use App\Models\Paysprintremitter;
    use App\Models\Paysprintdmtbank;
    use http\Env\Response;
    use App\Paysprint\Apicredentials as PaysprintApicredentials;

    class Dmt
    {

        public function __construct()
        {
            $mode = 'LIVE'; // LIVE or TEST
            $library = new PaysprintApicredentials();
            $response = $library->getCredentials($mode);
            $this->base_url = $response['base_url'];
            $this->partner_id = $response['partner_id'];
            $this->api_key = $response['api_key'];
            $this->jwt_header = $response['jwt_header'];
            $this->authorised_key = $response['authorised_key'];
            $this->key = $response['key'];
            $this->iv = $response['iv'];
            $this->api_id = $response['api_id'];
            $this->bank3_flag = 'no';
            $this->pincode = "201301";
            $this->dob = "1995-12-16";
            $this->gst_state = "09";
            $this->address = "Noida";
            $this->pipe = 'bank1';
        }


        function getCustomer($mobile_number)
        {
            $url = $this->base_url . 'api/v1/service/dmt/remitter/queryremitter';
            $parameters = '{"mobile":"' . $mobile_number . '","bank3_flag":"' . $this->bank3_flag . '"}';
            $token = Self::generateToken();
            $header = array(
                'Accept: application/json',
                'Content-Type: application/json',
                "Token: $token",
                "Authorisedkey: $this->authorised_key"
            );
            $method = "POST";
            $response = Self::sendCurlPost($url, $header, $parameters, $method);
            $res = json_decode($response);
            if (isset($res->response_code)) {
                if ($res->response_code == 1 && !isset($res->stateresp)) {
                    $paysprintremitters = Paysprintremitter::where('mobile', $mobile_number)->first();
                    if (empty($paysprintremitters)) {
                        Paysprintremitter::insert([
                            'mobile' => $mobile_number,
                            'firstname' => $res->data->fname,
                            'lastname' => $res->data->lname,
                            'address' => $this->address,
                            'pincode' => $this->pincode,
                            'dob' => $this->dob,
                            'gst_state' => $this->gst_state,
                        ]);
                    }
                    $data = array('name' => $res->data->fname, 'mobile_number' => $mobile_number, 'total_limit' => $res->data->bank3_limit);
                    return Response(['status' => 'success', 'message' => 'Successfull.', 'ad1' => '', 'ad2' => '', 'data' => $data]);
                } elseif ($res->status == false && $res->response_code == 0) {
                    $data = array('is_otp' => 1);
                    return Response()->json(['status' => 'pending', 'message' => $res->message, 'ad1' => $res->stateresp, 'ad2' => 1, 'data' => $data]);
                } else {
                    return Response()->json(['status' => 'failure', 'message' => $res->message, 'ad1' => '', 'ad2' => '']);
                }
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

        function confirmSender($mobile_number, $first_name, $last_name, $pincode, $state, $address, $otp, $ad1, $ad2)
        {
            $url = $this->base_url . 'api/v1/service/dmt/remitter/registerremitter';
            $parameters = '{"mobile":"' . $mobile_number . '","firstname":"' . $first_name . '","lastname":"' . $last_name . '","address":"' . $this->address . '","otp":"' . $otp . '","pincode":"' . $this->pincode . '","stateresp":"' . $ad1 . '","bank3_flag":"' . $this->bank3_flag . '","dob":"' . $this->dob . '","gst_state":"' . $this->gst_state . '"}';
            $token = Self::generateToken();
            $header = array(
                'Accept: application/json',
                'Content-Type: application/json',
                "Token: $token",
                "Authorisedkey: $this->authorised_key"
            );
            $method = "POST";
            $response = Self::sendCurlPost($url, $header, $parameters, $method);
            $res = json_decode($response);
            if (isset($res->response_code)) {
                if ($res->response_code == 1) {
                    return Response()->json(['status' => 'success', 'message' => $res->message]);
                } else {
                    return Response()->json(['status' => 'failure', 'message' => $res->message]);
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => "Something's gone wrong. We're working to get it fixed as soon as we can."]);
            }
        }

        function senderResendOtp($mobile_number, $first_name, $last_name, $pincode, $state, $address, $ad1, $ad2)
        {
            $url = $this->base_url . 'api/v1/service/dmt/remitter/queryremitter';
            $parameters = '{"mobile":"' . $mobile_number . '","bank3_flag":"' . $this->bank3_flag . '"}';
            $token = Self::generateToken();
            $header = array(
                'Accept: application/json',
                'Content-Type: application/json',
                "Token: $token",
                "Authorisedkey: $this->authorised_key"
            );
            $method = "POST";
            $response = Self::sendCurlPost($url, $header, $parameters, $method);
            $res = json_decode($response);
            if (isset($res->response_code)) {
               if ($res->status == false && $res->response_code == 0) {
                   return Response()->json(['status' => 'pending', 'message' => $res->message, 'ad1' => '', 'ad2' => '']);
                } else {
                    return Response()->json(['status' => 'failure', 'message' => $res->message, 'ad1' => '', 'ad2' => '']);
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Something went wrong!', 'ad1' => '', 'ad2' => '']);
            }
        }

        function getAllBeneficiary($mobile_number, $sender_name)
        {
            $url = $this->base_url . "api/v1/service/dmt/beneficiary/registerbeneficiary/fetchbeneficiary";
            $method = "POST";
            $api_request_parameters = '{"mobile":"' . $mobile_number . '"}';
            $token = Self::generateToken();
            $header = array(
                'Accept: application/json',
                'Content-Type: application/json',
                "Token: $token",
                "Authorisedkey: $this->authorised_key"
            );
            $response = Self::sendCurlPost($url, $header, $api_request_parameters, $method);
            $res = json_decode($response);
            if ($res->status == true && $res->response_code == 1) {
                $beneficiaries = $res->data;
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
                $beneficiary = Beneficiary::where('account_number', $value->accno)->where('benficiary_id', $value->bene_id)->where('api_id', $this->api_id)->first();
                $data = array(
                    'benficiary_id' => $value->bene_id,
                    'account_number' => $value->accno,
                    'ifsc' => $value->ifsc,
                    'bank_name' => $value->bankname,
                    'name' => $value->name,
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
                $product["beneficiary_id"] = $value->bene_id;
                $product["bank_name"] = $value->bankname;
                $product["mobile_number"] = '';
                $product["beneficiary_name"] = $value->name;
                $product["ifsc_code"] = $value->ifsc;
                $product["account_number"] = $value->accno;
                $product["is_verify"] = $value->verified;
                $product["status_id"] = 1;
                array_push($response, $product);
            }
            return $response;
        }

        function getIfscCode($bank_id)
        {
            $paysprintdmtbanks = Paysprintdmtbank::find($bank_id);
            if ($paysprintdmtbanks) {
                $data = array('ifsc' => $paysprintdmtbanks->ifsc);
                return Response()->json(['status' => 'success', 'message' => 'Successful..!', 'data' => $data]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Record not found!']);
            }
        }

        function addBeneficiary($mobile_number, $bank_id, $ifsc_code, $account_number, $beneficiary_name)
        {
            $paysprintdmtbanks = Paysprintdmtbank::find($bank_id);
            $bankid = (empty($paysprintdmtbanks)) ? '' : $paysprintdmtbanks->bank_id;
            $url = $this->base_url . "api/v1/service/dmt/beneficiary/registerbeneficiary";
            $parameters = '{"mobile":"' . $mobile_number . '","benename":"' . $beneficiary_name . '","bankid":"' . $bankid . '","accno":"' . $account_number . '","ifsccode":"' . $ifsc_code . '","verified":"0","gst_state":"' . $this->gst_state . '","dob":"' . $this->dob . '","address":"' . $this->address . '","pincode":"' . $this->pincode . '"}';
            $token = Self::generateToken();
            $header = array(
                'Accept: application/json',
                'Content-Type: application/json',
                "Token: $token",
                "Authorisedkey: $this->authorised_key"
            );
            $method = "POST";
            $response = Self::sendCurlPost($url, $header, $parameters, $method);
            $res = json_decode($response);
            if (isset($res->response_code)) {
                if ($res->response_code == 1) {
                    return Response()->json(['status' => 'success', 'message' => $res->message]);
                } else {
                    return Response()->json(['status' => 'failure', 'message' => $res->message]);
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Someting went wrong', 'ad1' => '', 'ad2' => '']);
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
            $url = $this->base_url . "api/v1/service/dmt/beneficiary/registerbeneficiary/deletebeneficiary";
            $parameters = '{"mobile": "' . $mobile_number . '","bene_id": "' . $beneficiary_id . '"}';
            $token = Self::generateToken();
            $header = array(
                'Accept: application/json',
                'Content-Type: application/json',
                "Token: $token",
                "Authorisedkey: $this->authorised_key"
            );
            $method = "POST";
            $response = Self::sendCurlPost($url, $header, $parameters, $method);
            $res = json_decode($response);
            $response_code = $res->response_code;
            if ($response_code == 1) {
                return Response()->json(['status' => 'success', 'message' => $res->message]);
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
            $benename = "Singh";
            $paysprintdmtbanks = Paysprintdmtbank::find($bank_id);
            $bankid = (empty($paysprintdmtbanks)) ? '' : $paysprintdmtbanks->bank_id;
            $url = $this->base_url . "api/v1/service/dmt/beneficiary/registerbeneficiary/benenameverify";
            $parameters = '{"mobile":"' . $mobile_number . '","accno":"' . $account_number . '","bankid":"' . $bankid . '","benename":"' . $benename . '","referenceid":"' . $insert_id . '","pincode":"' . $this->pincode . '","address":"' . $this->address . '","dob":"' . $this->dob . '","gst_state":"' . $this->gst_state . '"}';
            $token = Self::generateToken();
            $header = array(
                'Accept: application/json',
                'Content-Type: application/json',
                "Token: $token",
                "Authorisedkey: $this->authorised_key"
            );
            $method = "POST";
            $response = Self::sendCurlPost($url, $header, $parameters, $method);
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $api_id, 'report_id' => $insert_id, 'request_message' => $url . '?' . $parameters]);
            $res = json_decode($response);
            if (isset($res->response_code)) {
                if ($res->status == true && $res->response_code == 1) {
                    return ['status_id' => 1, 'message' => 'SuccessFul..', 'name' => $res->benename];
                } elseif ($res->status == false) {
                    return ['status_id' => 2, 'message' => $res->message, 'name' => ''];
                } else {
                    return ['status_id' => 3, 'message' => '', 'name' => ''];
                }
            } else {
                return ['status_id' => 3, 'message' => '', 'name' => ''];
            }
        }


        function transferNow($amount, $user_id, $ifsc_code, $beneficiary_id, $insert_id, $account_number, $mobile_number, $channel_id, $api_id, $latitude, $longitude)
        {
            $txntype = ($channel_id == 2) ? 'IMPS' : 'NEFT';
            $url = $this->base_url . "api/v1/service/dmt/transact/transact";
            $parameters = '{"mobile":"' . $mobile_number . '","referenceid":"' . $insert_id . '","pipe":"' . $this->pipe . '","pincode":"' . $this->pincode . '","address":"' . $this->address . '","dob":"' . $this->dob . '","gst_state":"' . $this->gst_state . '","bene_id":"' . $beneficiary_id . '","txntype":"' . $txntype . '","amount":"' . $amount . '"}';
            $token = Self::generateToken();
            $header = array(
                'Accept: application/json',
                'Content-Type: application/json',
                "Token: $token",
                "Authorisedkey: $this->authorised_key"
            );
            $method = "POST";
            $response = Self::sendCurlPost($url, $header, $parameters, $method);
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $api_id, 'report_id' => $insert_id, 'request_message' => $url . '?' . $parameters]);
            $res = json_decode($response);
            if (isset($res->status)) {
                $status = $res->status;
                if ($status == true) {
                    if ($res->txn_status == 1) {
                        return ['status_id' => 1, 'txnid' => $res->utr, 'payid' => $res->ackno];
                    } elseif ($res->txn_status == 0) {
                        return ['status_id' => 2, 'txnid' => '', 'payid' => $res->ackno];
                    } else {
                        return ['status_id' => 3, 'txnid' => '', 'payid' => $res->ackno];
                    }
                } elseif ($status == false) {
                    return ['status_id' => 2, 'txnid' => '', 'payid' => ''];
                } else {
                    return ['status_id' => 3, 'txnid' => '', 'payid' => ''];
                }
            } else {
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

        public function generateToken()
        {
            $Jwtheader = $this->jwt_header;
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            $reqid = rand(100000, 999999);
            $timestamp = strtotime($ctime);
            $payload = '{ 
            "timestamp": "' . $timestamp . '", 
            "partnerId": "' . $this->partner_id . '", 
            "reqid": "' . $reqid . '" 
        }';
            $apikey = $this->api_key;
            $library = new PaysprintApicredentials();
            $Jwt = $library->encode($Jwtheader, $payload, $apikey);
            return $Jwt;
        }

        public function sendCurlPost($url, $header, $api_request_parameters, $method)
        {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $api_request_parameters,
                CURLOPT_HTTPHEADER => $header,
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            return $response;
        }


    }
}