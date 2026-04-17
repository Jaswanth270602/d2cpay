<?php

namespace App\Pay2all {

    use Helpers;
    use App\Models\Api;
    use App\Models\Apiprovider;
    use App\Models\Provider;
    use App\Models\Apiresponse;
    use App\Models\User;
    use http\Env\Response;
    use App\Pay2all\Apicredentials as Pay2allcredentials;

    class RechargeBillpay
    {

        public function __construct()
        {
            $library = new Pay2allcredentials();
            $response = $library->getCredentials();
            $this->base_url = $response['base_url'];
            $this->authorizationKey = $response['authorizationKey'];
            $this->api_id = $response['api_id'];
        }

        function getBillerParams($provider_id)
        {
            $apiproviders = Apiprovider::where('api_id', $this->api_id)->where('provider_id', $provider_id)->first();
            if (empty($apiproviders->operator_code)) {
                return ['status_id' => 2, 'message' => "Biller id not found"];
            }
            $billerId = $apiproviders->operator_code;
            $url = $this->base_url . "api/bbps/biller/$billerId";
            $parameters = array();
            $method = 'GET';
            $header = ["Accept:application/json", "Authorization:" . $this->authorizationKey];
            $response = Helpers::pay_curl_post($url, $header, $parameters, $method);
            $res = json_decode($response);
            if (isset($res->status_id)) {
                if ($res->status_id == 1) {
                    $biller = $res->biller[0];
                    if ($biller->status == 'ACTIVE') {
                        $fetchRequirement = ($biller->fetchRequirement == 'MANDATORY') ? 1 : 0;
                        $customerParams = $biller->customerParams;
                        Provider::where('id', $provider_id)->update(['fetchRequirement' => $fetchRequirement, 'customerParams' => json_encode($customerParams)]);
                        return ['status_id' => 1, 'message' => "Successful..!"];
                    } else {
                        return ['status_id' => 2, 'message' => "Something went wrong"];
                    }
                } else {
                    return ['status_id' => 2, 'message' => "Something went wrong"];
                }
            } else {
                return ['status_id' => 2, 'message' => "Something went wrong"];
            }
        }

        function fatchBill($provider_id, $customerParams, $mode, $client_id)
        {
            $data = json_decode($customerParams, true);
            $number = $data[0]['value'];
            $apiproviders = Apiprovider::where('api_id', $this->api_id)->where('provider_id', $provider_id)->first();
            if (empty($apiproviders->operator_code)) {
                return Response()->json(['status' => 'failure', 'message' => 'Biller id not found']);
            }
            $providers = Provider::find($provider_id);
            $billerId = $apiproviders->operator_code;
            $url = $this->base_url . "api/bbps/fetch";
            $parameters = array(
                'billerId' => $billerId,
                'customerParams' => json_decode($customerParams),
            );
            $jsonBody = json_encode($parameters);
            $header = [
                'Accept: application/json',
                'Content-Type: application/json',
                "Authorization:" . $this->authorizationKey
            ];
            $response = Self::jsonPostCurl($url, $jsonBody, $header);
            $res = json_decode($response);
            if (isset($res->status_id)) {
                $status_id = $res->status_id;
                if ($status_id == 1) {
                    $data = array(
                        'provider_name' => $providers->provider_name,
                        'number' => $number,
                        'amount' => $res->amount,
                        'name' => $res->name,
                        'dueDate' => $res->dueDate,
                        'billDate' => $res->billDate,
                        'reference_id' => $res->reference_id,
                    );
                    return Response(['status' => 'success', 'message' => 'Fetch successful', 'data' => $data]);
                } else {
                    return Response(['status' => 'failure', 'message' => $res->message]);
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => "Something went wrong"]);
            }
        }

        function payNow($provider_id, $amount, $number, $customerParams, $user_id, $mode, $latitude, $longitude, $client_id, $reference_id, $insert_id)
        {
            $apiproviders = Apiprovider::where('api_id', $this->api_id)->where('provider_id', $provider_id)->first();
            if (empty($apiproviders->operator_code)) {
                return ['status_id' => 2, 'txnid' => 'Biller id not found'];
            }
            $userDetails = User::find($user_id);
            $providers = Provider::find($provider_id);
            $billerId = $apiproviders->operator_code;
            $url = $this->base_url . "api/bbps/payment";
            $parameters = array(
                'mobile_number' => $userDetails->mobile,
                'reference_id' => $reference_id,
                'amount' => $amount,
                'billerId' => $billerId,
                'client_id' => $insert_id,
                'customerParams' => json_decode($customerParams),
            );
            $jsonBody = json_encode($parameters);
            $header = [
                'Accept: application/json',
                'Content-Type: application/json',
                "Authorization:" . $this->authorizationKey
            ];
            $response = Self::jsonPostCurl($url, $jsonBody, $header);
            //$response = '{"status_id":1,"utr":"CC014195BAAA60501717","message":"success"}';
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'report_id' => $insert_id, 'request_message' => $url . '?' . $jsonBody]);
            $res = json_decode($response);
            if (isset($res->status_id)) {
                $status_id = $res->status_id;
                if ($status_id == 1) {
                    return ['status_id' => 1, 'txnid' => $res->utr];
                } elseif ($status_id == 2) {
                    return ['status_id' => 2, 'txnid' => $res->message,];
                } else {
                    return ['status_id' => 3, 'txnid' => 'Invalid response'];
                }
            } else {
                return ['status_id' => 3, 'txnid' => 'Invalid response'];
            }
        }


        function payNowPrepaid($provider_id, $amount, $number, $user_id, $client_id, $insert_id)
        {
            $apiproviders = Apiprovider::where('api_id', $this->api_id)->where('provider_id', $provider_id)->first();
            if (empty($apiproviders->operator_code)) {
                return ['status_id' => 2, 'txnid' => 'provider id not found'];
            }
            $url = $this->base_url . "api/v1/payment/recharge";
            $api_request_parameters = array(
                'number' => $number,
                'provider_id' => $apiproviders->operator_code,
                'amount' => $amount,
                'client_id' => $insert_id,
            );
            $method = 'POST';
            $header = ["Accept:application/json", "Authorization:" . $this->authorizationKey];
            $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'report_id' => $insert_id, 'request_message' => $url . '?' . json_encode($api_request_parameters)]);
            $res = json_decode($response);
            if (isset($res->status_id)) {
                $status_id = $res->status_id;
                if ($status_id == 1) {
                    return ['status_id' => 1, 'txnid' => $res->utr];
                } elseif ($status_id == 2) {
                    return ['status_id' => 2, 'txnid' => ''];
                } else {
                    return ['status_id' => 3, 'txnid' => ''];
                }
            } else {
                return ['status_id' => 3, 'txnid' => 'Invalid response'];
            }
        }

        function jsonPostCurl($url, $jsonBody, $header)
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
                CURLOPT_POSTFIELDS => $jsonBody,
                CURLOPT_HTTPHEADER => $header,
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            return $response;
        }


    }
}