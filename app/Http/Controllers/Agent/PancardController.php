<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;

use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use App\Models\Provider;
use App\Models\Balance;
use App\Models\Report;
use App\Models\User;
use App\Models\Commissionreport;
use App\Models\Api;
use App\Models\Service;
use DB;
use Hash;
use Helpers;
use App\Models\Apiresponse;
use App\Library\BasicLibrary;
use App\Library\RechargeLibrary;
use App\Library\GetcommissionLibrary;
use App\Library\Commission_increment;
use App\Library\LocationRestrictionsLibrary;

class PancardController extends Controller
{

    public function __construct()
    {
        $this->company_id = Helpers::company_id()->id;
        $dt = Helpers::company_id();
        $this->company_id = $dt->id;
        $api = Api::where('vender_id', 10)->first();
        $this->key = (empty($api)) ? '' : 'Bearer ' . $api->api_key;
        $this->url = (empty($api)) ? '' : "https://api.pay2all.in/v1/money/";
        $this->api_id = (empty($api)) ? '' : $api->id;
        $this->provider_id = 325;
    }

    function welcome(Request $request)
    {
        $user_id = Auth::id();
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($this->provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($serviceStatus == 1 && Auth::User()->role_id == 8) {
            $data = array('page_title' => 'Pancard Coupons');
            return view('agent.pancard.welcome')->with($data);
        } else {
            return redirect()->back();
        }
    }

    function buy_coupons(Request $request)
    {
        $user_id = Auth::id();
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($this->provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($serviceStatus == 1 && Auth::User()->role_id == 8) {
            $rules = array(
                'username' => 'required',
                'quantity' => 'required',
                'transaction_pin' => '' . (Auth::User()->company->transaction_pin == 1) ? 'required|digits:6' : 'nullable' . '',
                'latitude' => 'required',
                'longitude' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'message' => 'validation errors', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $username = $request->username;
            $quantity = $request->quantity;
            $mode = "WEB";
            $request_ip = request()->ip();
            $client_id = "";
            $user_id = Auth::id();
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            if (Auth::User()->role_id == 10) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry you cannot access this URL']);
            }
            $locationrestrictionsLibrary = new LocationRestrictionsLibrary();
            $isLoginValid = $locationrestrictionsLibrary->loginRestrictions($user_id, $latitude, $longitude);
            if ($isLoginValid == 0) {
                $kilometer = Auth::User()->company->login_restrictions_km;
                return Response()->json(['status' => 'failure', 'message' => "You must be within $kilometer kilometer to access this service."]);
            }
            return $this->buy_coupons_middle($username, $quantity, $mode, $request_ip, $client_id, $user_id, $latitude, $longitude);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function buy_coupons_app(Request $request)
    {
        $user_id = Auth::id();
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($this->provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($serviceStatus == 1 && Auth::User()->role_id == 8) {
            $rules = array(
                'username' => 'required',
                'quantity' => 'required',
                'latitude' => 'required',
                'longitude' => 'required',
                'transaction_pin' => '' . (Auth::User()->company->transaction_pin == 1) ? 'required|digits:6' : 'nullable' . '',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'message' => 'validation errors', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $username = $request->username;
            $quantity = $request->quantity;
            $mode = "APP";
            $request_ip = request()->ip();
            $client_id = "";
            $user_id = Auth::id();
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            if (Auth::User()->role_id == 10) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry you cannot access this URL']);
            }
            $locationrestrictionsLibrary = new LocationRestrictionsLibrary();
            $isLoginValid = $locationrestrictionsLibrary->loginRestrictions($user_id, $latitude, $longitude);
            if ($isLoginValid == 0) {
                $kilometer = Auth::User()->company->login_restrictions_km;
                return Response()->json(['status' => 'failure', 'message' => "You must be within $kilometer kilometer to access this service."]);
            }
            return $this->buy_coupons_middle($username, $quantity, $mode, $request_ip, $client_id, $user_id, $latitude, $longitude);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function buy_coupons_middle($username, $quantity, $mode, $request_ip, $client_id, $user_id, $latitude, $longitude)
    {
        $userdetails = User::find($user_id);
        $provider_id = $this->provider_id;
        $providers = Provider::where('id', $provider_id)->where('status_id', 1)->first();
        if ($userdetails->active == 1 && $userdetails->member->kyc_status && $userdetails->status_id == 1) {
            $amount = $quantity * 107;
            $opening_balance = $userdetails->balance->user_balance;
            $sumamount = $amount + $userdetails->lock_amount + $userdetails->balance->lien_amount;
            $library = new BasicLibrary();
            $activeService = $library->getActiveService($this->provider_id, $user_id);
            $serviceStatus = $activeService['status_id'];
            if ($userdetails->company->server_down == 1 && $serviceStatus == 1) {
                if ($opening_balance >= $sumamount && $sumamount >= 4) {
                    $api_id = 1;
                    $scheme_id = $userdetails->scheme_id;
                    $library = new GetcommissionLibrary();
                    $commission = $library->get_commission($scheme_id, $provider_id, $amount);
                    $retailer = $commission['retailer'];
                    $deduct_amount = $amount - $retailer;
                    Balance::where('user_id', $user_id)->decrement('user_balance', $deduct_amount);
                    $balance = Balance::where('user_id', $user_id)->first();
                    $user_balance = $balance->user_balance;
                    $now = new \DateTime();
                    $ctime = $now->format('Y-m-d H:i:s');
                    $description = "$providers->provider_name  $username";
                    $insert_id = Report::insertGetId([
                        'number' => $username,
                        'provider_id' => $provider_id,
                        'amount' => $amount,
                        'api_id' => $api_id,
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
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                    ]);
                    $url = "https://api.pay2all.in/v1/payment/recharge";
                    $api_request_parameters = array(
                        'number' => $username,
                        'provider_id' => 95,
                        'amount' => $amount,
                        'client_id' => $insert_id,
                    );
                    $method = 'POST';
                    $header = ["Accept:application/json", "Authorization:" . $this->key];
                    $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
                    $api_request_parameters = json_encode($api_request_parameters);
                    $request_message = $url . '?' . $api_request_parameters;
                    Apiresponse::insertGetId(['message' => $response, 'api_type' => $api_id, 'report_id' => $insert_id, 'request_message' => $url . '?' . json_encode($api_request_parameters)]);
                    $res = json_decode($response);
                    $status = (empty($res->status)) ? 3 : $res->status;
                    if ($status == 0 || $status == 1) {
                        Report::where('id', $insert_id)->update(['status_id' => 1, 'txnid' => $res->utr]);
                        return ['status' => 'success', 'message' => 'Transaction success!', 'utr' => $res->utr, 'payid' => $insert_id];
                    } elseif ($status == 2) {
                        Balance::where('user_id', $user_id)->increment('user_balance', $deduct_amount);
                        $balance = Balance::where('user_id', $user_id)->first();
                        $user_balance = $balance->user_balance;
                        Report::where('id', $insert_id)->update(['status_id' => 2, 'profit' => 0, 'total_balance' => $user_balance]);
                        return ['status' => 'failure', 'message' => 'Transaction failed!', 'utr' => '', 'payid' => $insert_id];
                    } else {
                        return ['status' => 'pending', 'message' => 'Transaction process!', 'utr' => '', 'payid' => $insert_id];
                    }
                } else {
                    return Response()->json(['status' => 'failure', 'message' => 'Your balance is low kindly refill your wallet']);
                }
            } else {
                $message = ($userdetails->company->server_down == 1) ? 'Service not active!' : $userdetails->company->server_message;
                return Response()->json(['status' => 'failure', 'message' => $message]);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => $userdetails->reason]);
        }

    }


    function reports(Request $request)
    {
        if (Auth::User()->profile->pancard == 1) {
            if ($request->fromdate && $request->todate) {
                $fromdate = $request->fromdate;
                $todate = $request->todate;
                $urls = url('agent/pancard/reports-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate;
            } else {
                $fromdate = date('Y-m-d', time());
                $todate = date('Y-m-d', time());
                $urls = url('agent/pancard/reports-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate;
            }
            $data = array(
                'page_title' => 'Pancard Report',
                'fromdate' => $fromdate,
                'todate' => $todate,
                'urls' => $urls
            );

            return view('agent.pancard.reports')->with($data);
        } else {
            return redirect()->back();
        }

    }

    function reports_api(Request $request)
    {
        $fromdate = $request->get('fromdate');
        $todate = $request->get('amp;todate');
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
        $totalRecords = Report::select('count(*) as allcount')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('provider_id', $this->provider_id)
            ->count();

        $totalRecordswithFilter = Report::select('count(*) as allcount')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('number', 'like', '%' . $searchValue . '%')
            ->where('provider_id', $this->provider_id)
            ->count();

        // Fetch records
        $records = Report::orderBy($columnName, $columnSortOrder)
            ->where('number', 'like', '%' . $searchValue . '%')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('provider_id', $this->provider_id)
            ->orderBy('id', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();
        $data_arr = array();
        foreach ($records as $value) {
            $data_arr[] = array(
                "id" => $value->id,
                "created_at" => "$value->created_at",
                "provider" => $value->provider->provider_name,
                "number" => $value->number,
                "txnid" => $value->txnid,
                "amount" => number_format($value->amount, 2),
                "profit" => number_format($value->profit, 2),
                "balance" => number_format($value->total_balance, 2),
                "status" => '<span class="' . $value->status->class . '">' . $value->status->status . '</span>',
                "view" => '<button class="btn btn-danger btn-sm" onclick="view_recharges(' . $value->id . ')"><i class="fas fa-eye"></i> View</button>',
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
