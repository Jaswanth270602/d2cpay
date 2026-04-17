<?php

namespace App\library {
    use App\Numberdata;
    use App\Circleprovider;
    use App\Backupapi;
    use App\Provider;
    use App\User;
    use App\Report;
    use App\Providerlimit;
    use App\Service;
    use DB;
    use Auth;
    use App\Library\GetcommissionLibrary;
    use App\Notifications\DatabseNotification;
    use Notification;
    use App\Denomination;
    use App\State;
    use App\Apicheckbalance;
    use App\Api;
    use Helpers;

    class ApibalanceLibrary {



        function api_balance ($api_id){
            $apis = Api::find($api_id);
            if ($apis->vender_id == 10){
              return Self::pay2all_balance($api_id);
            }
            $apicheckbalances = Apicheckbalance::where('api_id', $api_id)->first();
            if ($apicheckbalances){
                if ($apicheckbalances->response_type == 1){
                    return Self::json_api($api_id);
                }elseif ($apicheckbalances->response_type == 2){
                    return Self::xml_api($api_id);
                }else{
                    $balance = 0;
                    $response = 'Api not found';
                }

            }else{
                $balance = 0;
                $response = 'Api not found';
            }
            return ['balance' => $balance, 'response' => $response];
        }


        function json_api ($api_id){
            $apicheckbalances = Apicheckbalance::where('api_id', $api_id)->first();
            $endurl = $apicheckbalances->base_url;
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $endurl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($curl);
            Apicheckbalance::where('api_id', $api_id)->update(['last_response' => $response]);
            $res = json_decode($response, true);
            if ($apicheckbalances->status_type == 1){
                $status = $res["$apicheckbalances->status_parameter"];
                if ($status == $apicheckbalances->status_value){
                    $balance = $res["$apicheckbalances->balance_parameter"];
                }else{
                    $balance = 0;
                }
            }else{
                $balance = $res["$apicheckbalances->balance_parameter"];
            }
            return array('balance' => $balance);
        }

        function xml_api ($api_id){
            $apicheckbalances = Apicheckbalance::where('api_id', $api_id)->first();
            $endurl = $apicheckbalances->base_url;
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $endurl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($curl);
            Apicheckbalance::where('api_id', $api_id)->update(['last_response' => $response]);
            $xmlres = simplexml_load_string($response);
            $resjsonencode = json_encode($xmlres); 
            $res = json_decode($resjsonencode, true);
            if ($apicheckbalances->status_type == 1){
                $status = $res["$apicheckbalances->status_parameter"];
                if ($status == $apicheckbalances->status_value){
                    $balance = $res["$apicheckbalances->balance_parameter"];
                }else{
                    $balance = 0;
                }
            }else{
                $balance = $res["$apicheckbalances->balance_parameter"];
            }
            return array('balance' => $balance);
        }

        function pay2all_balance ($api_id){
            $apis = Api::find($api_id);
            $key = 'Bearer '.$apis->api_key;
            $url = "https://api.pay2all.in/user";
            $api_request_parameters = array();
            $method = 'GET';
            $header = ["Accept:application/json", "Authorization:".$key];
            $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
            if ($response){
                $res = json_decode($response);
                $balance = $res->balance;
            }else{
                $balance = 0;
            }
            return array('balance' => $balance);
        }

    }

}