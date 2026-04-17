<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use Helpers;
use DB;
use Hash;
use App\Models\Api;
use App\Models\Provider;
use App\Models\Balance;
use App\Models\Report;
use App\Models\Apiresponse;
use App\Models\User;
use App\Library\BasicLibrary;
use App\Library\GetcommissionLibrary;
use App\Library\Commission_increment;

class CreditCardController extends Controller
{
    public function __construct()
    {
        $this->api_id = 1;
        $apis = Api::where('id', $this->api_id)->first();
        $this->api_token = $apis->api_key;
        $this->base_url = 'https://mpayment.in/';

        $this->provider_id = 329;
        $providers = Provider::find($this->provider_id);
        $this->min_length = $providers->min_length;
        $this->max_length = $providers->max_length;
    }

    function welcome()
    {

        $user_id = Auth::id();
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($this->provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($serviceStatus == 1 && Auth::User()->role_id == 8) {
            $data = array('page_title' => 'Credit Card Bill Payment');
            return view('agent.credit-card.welcome')->with($data);
        } else {
            return redirect()->back();
        }
    }

    function view_transaction(Request $request)
    {
        $user_id = Auth::id();
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($this->provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($serviceStatus == 1 && Auth::User()->role_id == 8) {
            $rules = array(
                'card_number' => 'required',
                'amount' => 'required',
                'name' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
            }
            $card_number = $request->card_number;
            $amount = $request->amount;
            $providers = Provider::find($this->provider_id);
            $data = array(
                'provider_name' => $providers->provider_name,
                'card_number' => $card_number,
                'amount' => $amount,
                'name' => $request->name,
            );
            return Response()->json(['status' => 'success', 'message' => 'Successfull..!', 'data' => $data]);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Service not active!']);
        }

    }

    function payNowWeb(Request $request)
    {
        $rules = array(
            'card_number' => 'required',
            'amount' => 'required',
            'name' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'dupplicate_transaction' => 'required|unique:check_duplicates',
            'transaction_pin' => '' . (Auth::User()->company->transaction_pin == 1) ? 'required|digits:6' : 'nullable' . '',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        if (Auth::User()->company->transaction_pin == 1) {
            if (!Hash::check($request->transaction_pin, Auth::User()->transaction_pin)) {
                return Response()->json(['status' => 'failure', 'message' => 'Invalid transaction pin']);
            }
        }
        DB::table('check_duplicates')->insert(['dupplicate_transaction' => $request->dupplicate_transaction]);
        $card_number = $request->card_number;
        $amount = $request->amount;
        $name = $request->name;
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $user_id = Auth::id();
        $mode = 'WEB';
        $client_id = '';
        return Self::payNowMiddle($card_number, $amount, $name, $latitude, $longitude, $user_id, $mode, $client_id);
    }

    function payNowApp(Request $request)
    {
        $rules = array(
            'card_number' => 'required',
            'amount' => 'required',
            'name' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'transaction_pin' => '' . (Auth::User()->company->transaction_pin == 1) ? 'required|digits:6' : 'nullable' . '',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        if (Auth::User()->company->transaction_pin == 1) {
            if (!Hash::check($request->transaction_pin, Auth::User()->transaction_pin)) {
                return Response()->json(['status' => 'failure', 'message' => 'Invalid transaction pin']);
            }
        }
        $card_number = $request->card_number;
        $amount = $request->amount;
        $name = $request->name;
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $user_id = Auth::id();
        $mode = 'APP';
        $client_id = '';
        return Self::payNowMiddle($card_number, $amount, $name, $latitude, $longitude, $user_id, $mode, $client_id);
    }

    function payNowApi(Request $request)
    {
        $rules = array(
            'card_number' => 'required',
            'amount' => 'required',
            'name' => 'required',
            'client_id' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $card_number = $request->card_number;
        $amount = $request->amount;
        $name = $request->name;
        $latitude = '';
        $longitude = '';
        $user_id = Auth::id();
        $mode = 'API';
        $client_id = $request->client_id;
        return Self::payNowMiddle($card_number, $amount, $name, $latitude, $longitude, $user_id, $mode, $client_id);
    }

    function payNowMiddle($card_number, $amount, $name, $latitude, $longitude, $user_id, $mode, $client_id)
    {
        $userDetails = User::find($user_id);
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($this->provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($userDetails->company->server_down == 1 && $serviceStatus == 1) {
            $provider_id = $this->provider_id;
            $scheme_id = $userDetails->scheme_id;
            $library = new GetcommissionLibrary();
            $commission = $library->get_commission($scheme_id, $provider_id, $amount);
            $retailer = $commission['retailer'];
            $d = $commission['distributor'];
            $sd = $commission['sdistributor'];
            $st = $commission['sales_team'];
            $rf = $commission['referral'];
            $opening_balance = $userDetails->balance->user_balance;
            $sumamount = $amount + $userDetails->lock_amount + $userDetails->balance->lien_amount;
            if ($opening_balance >= $sumamount && $sumamount >= 0) {
                $decrementAmount = $amount + $retailer;
                $providers = Provider::find($provider_id);
                Balance::where('user_id', $user_id)->decrement('user_balance', $decrementAmount);
                $balance = Balance::where('user_id', $user_id)->first();
                $user_balance = $balance->user_balance;
                $now = new \DateTime();
                $ctime = $now->format('Y-m-d H:i:s');
                $wallet_type = 1;
                $api_id = $this->api_id;
                $request_ip = request()->ip();
                $cardNumber = $lastFourDigits = '************' . substr($card_number, -4);
                $description = "$providers->provider_name  $cardNumber";
                $insert_id = Report::insertGetId([
                    'number' => $cardNumber,
                    'provider_id' => $provider_id,
                    'amount' => $amount,
                    'api_id' => $api_id,
                    'status_id' => 3,
                    'client_id' => $client_id,
                    'created_at' => $ctime,
                    'user_id' => $user_id,
                    'profit' => '-' . $retailer,
                    'mode' => $mode,
                    'ip_address' => $request_ip,
                    'description' => $description,
                    'opening_balance' => $opening_balance,
                    'total_balance' => $user_balance,
                    'wallet_type' => $wallet_type,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ]);
                $response = Self::callMpayment($user_id, $card_number, $amount, $name, $insert_id);
                $status_id = $response['status_id'];
                $txnid = $response['txnid'];
                if ($status_id == 1) {
                    Report::where('id', $insert_id)->update(['status_id' => 1, 'txnid' => $txnid]);
                    $message = "Transaction is success";
                    $library = new Commission_increment();
                    $library->parent_recharge_commission($user_id, $cardNumber, $insert_id, $provider_id, $amount, $this->api_id, $retailer, $d, $sd, $st, $rf);
                    //get wise commission
                    $library = new GetcommissionLibrary();
                    $apiComms = $library->getApiCommission($api_id, $provider_id, $amount);
                    $apiCommission = $apiComms['apiCommission'];
                    $commissionType = $apiComms['commissionType'];
                    $library = new Commission_increment();
                    $library->updateApiComm($user_id, $provider_id, $api_id, $amount, $retailer, $d, $sd, $st, $rf, $apiCommission, $insert_id, $commissionType);
                    return Response()->json(['status' => 'success', 'message' => $message, 'txnid' => $txnid, 'payid' => $insert_id]);
                } elseif ($status_id == 2) {
                    Balance::where('user_id', $user_id)->increment('user_balance', $decrementAmount);
                    $balance = Balance::where('user_id', $user_id)->first();
                    $user_balance = $balance->user_balance;
                    Report::where('id', $insert_id)->update(['status_id' => 2, 'txnid' => $txnid, 'profit' => 0, 'total_balance' => $user_balance]);
                    $message = "Transaction is failed";
                    return Response()->json(['status' => 'failure', 'message' => $message, 'txnid' => '', 'payid' => $insert_id]);
                } else {
                    if ($userDetails->role_id == 10) {
                        return Response()->json(['status' => 'pending', 'message' => 'Process..', 'txnid' => '', 'payid' => $insert_id]);
                    } else {
                        $message = "Transaction is process..";
                        return Response()->json(['status' => 'success', 'message' => $message, 'txnid' => '', 'payid' => $insert_id]);
                    }
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'insufficient funds kindly refill your wallet!']);
            }
        } else {
            $message = ($userDetails->company->server_down == 1) ? 'Service not active!' : $userDetails->company->server_message;
            return Response()->json(['status' => 'failure', 'message' => $message]);
        }
    }

    function callMpayment($user_id, $card_number, $amount, $name, $insert_id)
    {
        $url = $this->base_url . 'api/credit-card/v1/payment';
        $api_request_parameters = array(
            'api_token' => $this->api_token,
            'card_number' => $card_number,
            'amount' => $amount,
            'name' => $name,
            'client_id' => $insert_id,
        );
        $method = 'POST';
        $header = ["Accept:application/json"];
        $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
        Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'report_id' => $insert_id, 'request_message' => $url . '?' . json_encode($api_request_parameters)]);
        $res = json_decode($response);
        $status = $res->status ?? 'pending';
        if ($status == 'success') {
            return ['status_id' => 1, 'txnid' => $res->txnid ?? ''];
        } elseif ($status == 'failure') {
            return ['status_id' => 2, 'txnid' => ''];
        } else {
            return ['status_id' => 3, 'txnid' => ''];
        }
    }

}
