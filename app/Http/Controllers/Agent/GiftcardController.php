<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;

use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use App\Provider;
use App\Balance;
use App\Report;
use App\User;
use App\Commissionreport;
use App\Api;
use App\Apiresponse;
use DB;
use Helpers;
use App\Library\BasicLibrary;
use App\Library\RechargeLibrary;
use App\Library\GetcommissionLibrary;
use App\Library\Commission_increment;

class GiftcardController extends Controller {

    public function __construct(){
        $this->company_id = Helpers::company_id()->id;
        $dt = Helpers::company_id();
        $this->company_id = $dt->id;
        $api = Api::where('company_id', $this->company_id)->where('vender_id', 10)->first();
        if ($api) {
            $this->key = 'Bearer '.$api->api_key;
            $this->url = "https://api.pay2all.in/v1/money/";
            $this->api_id = $api->id;
        }
    }

    function amazon_coupons (Request $request){
        if (Auth::User()->profile->giftcard == 1){
            $url = "https://api.pay2all.in//v1/giftcard?provider_id=167";
            $api_request_parameters = array();
            $method = 'GET';
            $header = ["Accept:application/json", "Authorization:".$this->key];
            $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
            $res = json_decode($response);
            $schema = $res->schema;
            $data = array('page_title' => 'Amazon Coupons');
            return view('agent.giftcard.amazon_coupons', compact('schema'))->with($data);
        }else{
            return redirect()->back();
        }

    }

    function purchase_amazon_coupons (Request $request){
        $rules = array(
            'mobile_number' => 'required|digits:10',
            'email' => 'required|email',
            'denomination' => 'required',
            'provider_id' => 'required',
            'qty' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'message' => 'validation errors', 'errors' => $validator->getMessageBag()->toArray()]);
        }

        $mobile_number = $request->mobile_number;
        $email = $request->email;
        $denomination = $request->denomination;
        $provider_id = $request->provider_id;
        $qty = $request->qty;
        $user_id = Auth::id();
        $mode = "WEB";
        $request_ip =  request()->ip();
        $client_id = "";
        return $this->purchase_amazon_coupons_middle($mobile_number, $email, $denomination, $provider_id, $qty, $user_id, $mode, $request_ip, $client_id);
    }

    function purchase_amazon_coupons_middle ($number, $email, $denomination, $provider_id, $qty, $user_id, $mode, $request_ip, $client_id){
        $userdetails = User::find($user_id);
        if ($userdetails->company->server_down == 1){
            if ($userdetails->profile->giftcard == 1){
                $providers = Provider::find($provider_id);
                $provider_id = $providers->id;
                $providers = Provider::where('merchant_pay2all', $provider_id)->first();
                if ($providers){
                    $amount = $denomination * $qty;
                    $provider_id = $providers->id;
                    $validation = new BasicLibrary();
                    $validation =  $validation->recharge_validation($user_id, $provider_id, $amount, $number);
                    $validation_status = $validation['status'];
                    $validation_message = $validation['message'];
                    if ($validation_status == 'success'){
                        $scheme_id = $userdetails->scheme_id;
                        $library = new GetcommissionLibrary();
                        $commission =  $library->get_commission($scheme_id, $provider_id, $amount);
                        $retailer = $commission['retailer'];
                        $distributor = $commission['distributor'];
                        $sdistributor = $commission['sdistributor'];
                        $sales_team = $commission['sales_team'];
                        $opening_balance = $userdetails->balance->user_balance;
                        $sumamount = $amount + $userdetails->lock_amount + $userdetails->balance->lien_amount;
                        if ($opening_balance >= $sumamount && $sumamount >= 4) {
                            $deduct_amount = $amount - $retailer;
                            Balance::where('user_id', $user_id)->decrement('user_balance', $deduct_amount);
                            $balance = Balance::where('user_id', $user_id)->first();
                            $user_balance = $balance->user_balance;

                            $now = new \DateTime();
                            $ctime = $now->format('Y-m-d H:i:s');
                            $description = "$providers->provider_name  $number";
                            $insert_id = Report::insertGetId([
                                'number' => $number,
                                'provider_id' => $provider_id,
                                'amount' => $amount,
                                'api_id' => $this->api_id,
                                'status_id' => 3,
                                'client_id' => $client_id,
                                'created_at' => $ctime,
                                'user_id' => $user_id,
                                'profit' => $retailer,
                                'mode' => $mode,
                                'ip_address' => $request_ip,
                                'description' => $description,
                                'opening_balance' => $opening_balance,
                                'total_balance' => $user_balance,
                                'wallet_type' => 1,
                            ]);
                            $url = "https://api.pay2all.in//v1/giftcard/purchase_giftcard";
                            $api_request_parameters = array(
                                'provider_id' => $providers->merchant_pay2all,
                                'qty' => $qty,
                                'email' => $email,
                                'denomination' => $denomination,
                                'mobile_number' => $number,
                                'client_id' => $insert_id,
                            );
                            $method = 'POST';
                            $header = ["Accept:application/json", "Authorization:".$this->key];
                            $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
                            // $response = '{"status":0,"report_id":2290119,"coupon_code":[{"id":4395,"coupon_code":"MGC8-3EV5CM-WFS8","amount":"2500","user_id":1,"remark":"Pay2all","status":1,"created_at":"2020-11-03 14:52:09","updated_at":"2020-11-03 14:52:09","provider_id":167}],"message":"Success"}';
                            Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'report_id' => $insert_id, 'request_message' => json_encode($api_request_parameters)]);
                            $res = json_decode($response);
                            if (array_key_exists("status", $res)) {
                                $status = $res->status;
                                if ($status == 0 || $status == 1){
                                    $txnid = $res->report_id;
                                    Report::where('id', $insert_id)->update(['status_id' => 1, 'txnid' => $txnid]);
                                    $coupon_code = $res->coupon_code;
                                    $coupon_code = $this->get_coupon_list($coupon_code);
                                    $library = new Commission_increment();
                                    $library->parent_recharge_commission($user_id, $number, $insert_id, $provider_id,$amount, $this->api_id, $retailer, $distributor, $sdistributor, $sales_team);
                                    return Response()->json(['status' => 'success', 'message' => 'Coupon successfully generated please check coupon right side and copy all coupon', 'coupon_code' => $coupon_code]);
                                }elseif ($status == 2){
                                    Balance::where('user_id', $user_id)->increment('user_balance', $deduct_amount);
                                    $balance = Balance::where('user_id', $user_id)->first();
                                    $user_balance = $balance->user_balance;
                                    Report::where('id', $insert_id)->update(['status_id' => 2, 'total_balance' => $user_balance]);
                                    return Response()->json(['status' => 'failure', 'message' => 'Your transaction is fail please try after sometime']);
                                }
                            }else{
                                return Response()->json(['status' => 'pending', 'message' => 'Some technical Error Occur,Kindly Check your Leadger or Transaction History to know transaction status']);
                            }
                        }else{
                            return Response()->json(['status' => 'failure', 'message' => 'Your balance is low kindly refill your wallet']);
                        }

                    }else{
                        return Response()->json(['status' => 'failure', 'message' => $validation_message]);
                    }
                }else{
                    return Response()->json(['status' => 'failure', 'message' => 'Provider not activate']);
                }
            }else{
                return Response()->json(['status' => 'failure', 'message' => 'Service not activate kindly contact customer care']);
            }
        }else{
            return Response()->json(['status' => 'failure', 'message' => $userdetails->company->server_message]);
        }

    }



    function get_coupon_list($coupon_code){
        $response = array();
        foreach ($coupon_code as $value) {
            $product = array();
            $product["id"] = $value->id;
            $product["coupon_code"] = $value->coupon_code;
            $product["amount"] = $value->amount;
            array_push($response, $product);
        }
        return $response;
    }


    function reports (Request $request){
        if (Auth::User()->profile->giftcard == 1){
            if ($request->fromdate && $request->todate) {
                $fromdate = $request->fromdate;
                $todate = $request->todate;
                $urls = url('agent/giftcard/reports-api').'?'.'fromdate='.$fromdate.'&todate='.$todate;
            } else {
                $fromdate = date('Y-m-d', time());
                $todate = date('Y-m-d', time());
                $urls = url('agent/giftcard/reports-api').'?'.'fromdate='.$fromdate.'&todate='.$todate;
            }
            $data = array(
                'page_title' => 'Giftcard Report',
                'fromdate' => $fromdate,
                'todate' => $todate,
                'urls' => $urls
            );

            return view('agent.giftcard.reports')->with($data);
        }else{
            return redirect()->back();
        }
    }

    function reports_api (Request $request){
        $fromdate = $request->get('fromdate');
        $todate =  $request->get('amp;todate');
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length"); // Rows display per page

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc
        $searchValue = $search_arr['value']; // Search value


        $user_id = Auth::id();
        $providers = Provider::whereIn('service_id', [17])->get(['id']);
        $totalRecords = Report::select('count(*) as allcount')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('provider_id', $providers)
            ->count();

        $totalRecordswithFilter = Report::select('count(*) as allcount')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('number', 'like', '%' .$searchValue . '%')
            ->whereIn('provider_id', $providers)
            ->count();

        // Fetch records

        $records = Report::orderBy($columnName,$columnSortOrder)
            ->where('number', 'like', '%' .$searchValue . '%')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('provider_id', $providers)
            ->orderBy('id', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();
        $data_arr = array();
        foreach($records as $value){
            $data_arr[] = array(
                "id" => $value->id,
                "created_at" => "$value->created_at",
                "provider" => $value->provider->provider_name,
                "number" => $value->number,
                "txnid" => $value->txnid,
                "amount" => number_format($value->amount,2),
                "status" => '<span class="'. $value->status->class.'">'. $value->status->status.'</span>',
                "view" => '<button class="btn btn-danger btn-sm" onclick="view_recharges('.$value->id .')"><i class="fas fa-eye"></i> View</button>',
            );
        }
        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordswithFilter,
            "aaData" => $data_arr
        );
        echo json_encode($response);
        exit;


    }

}
