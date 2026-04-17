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
use App\Models\Bbpsparam;
use App\Models\Apiprovider;
use DB;
use Helpers;
use Hash;
use \Crypt;
use App\Library\BasicLibrary;
use App\Library\RechargeLibrary;
use App\Library\GetcommissionLibrary;
use App\Library\Commission_increment;
use App\Library\ValidationLibrary;
use App\Library\LocationRestrictionsLibrary;

class RechargeController extends Controller
{


    function view_recharge_details(Request $request)
    {
        $rules = array(
            'provider_id' => 'required|exists:providers,id',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        // validation library
        $validation = new ValidationLibrary();
        $rules = $validation->rechargeValidation($request);
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        // end validation
        $mobile_number = $request->mobile_number;
        $amount = $request->amount;
        $provider_id = $request->provider_id;
        $providers = Provider::find($provider_id);
        return Response()->json([
            'status' => 'success',
            'confirm_provider_name' => $providers->provider_name,
            'confirm_mobile_number' => $mobile_number,
            'confirm_amount' => $amount,
            'confirm_provider_id' => $provider_id,
        ]);
    }

    function web_recharge_now(Request $request)
    {
        $rules = array(
            'provider_id' => 'required|exists:providers,id',
            'transaction_pin' => '' . (Auth::User()->company->transaction_pin == 1) ? 'required|digits:6' : 'nullable' . '',
            'dupplicate_transaction' => 'required|unique:check_duplicates',
           // 'latitude' => 'required',
            //'longitude' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        // validation library
        $validation = new ValidationLibrary();
        $rules = $validation->rechargeValidation($request);

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        if (Auth::User()->company->transaction_pin == 1) {
            if (!Hash::check($request->transaction_pin, Auth::User()->transaction_pin)) {
                return Response()->json(['status' => 'failure', 'message' => 'Invalid transaction pin']);
            }
        }
        DB::table('check_duplicates')->insert(['dupplicate_transaction' => $request->dupplicate_transaction]);
        $number = $request->mobile_number;
        $amount = $request->amount;
        $provider_id = $request->provider_id;
        $optional1 = $request->optional1;
        $optional2 = $request->optional2;
        $optional3 = $request->optional3;
        $optional4 = $request->optional4;
        $client_id = $request->client_id;
        $user_id = Auth::id();
        $mode = "WEB";
        $request_ip = request()->ip();
        $payment_mode = "Cash";
        $duedate = $request->duedate;
        $customer_name = $request->customer_name;
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
        return Self::recharge_middle($number, $amount, $provider_id, $optional1, $optional2, $optional3, $optional4, $client_id, $user_id, $mode, $request_ip, $payment_mode, $duedate, $customer_name, $latitude, $longitude);
    }

    function app_recharge_now(Request $request)
    {
        $rules = array(
            'provider_id' => 'required|exists:providers,id',
            'transaction_pin' => '' . (Auth::User()->company->transaction_pin == 1) ? 'required|digits:6' : 'nullable' . '',
            'dupplicate_transaction' => 'required|unique:check_duplicates',
            'latitude' => 'required',
            'longitude' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        // validation library
        $validation = new ValidationLibrary();
        $rules = $validation->rechargeValidation($request);
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        if (Auth::User()->company->transaction_pin == 1) {
            if (!Hash::check($request->transaction_pin, Auth::User()->transaction_pin)) {
                return Response()->json(['status' => 'failure', 'message' => 'Invalid transaction pin']);
            }
        }
        DB::table('check_duplicates')->insert(['dupplicate_transaction' => $request->dupplicate_transaction]);
        $number = $request->mobile_number;
        $amount = $request->amount;
        $provider_id = $request->provider_id;
        $optional1 = $request->optional1;
        $optional2 = $request->optional2;
        $optional3 = $request->optional3;
        $optional4 = $request->optional4;
        $client_id = '';
        $user_id = Auth::id();
        $mode = "APP";
        $request_ip = request()->ip();
        $payment_mode = "Cash";
        $duedate = $request->duedate;
        $customer_name = $request->customer_name;
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
        return Self::recharge_middle($number, $amount, $provider_id, $optional1, $optional2, $optional3, $optional4, $client_id, $user_id, $mode, $request_ip, $payment_mode, $duedate, $customer_name, $latitude, $longitude);
    }

    function api_recharge_now(Request $request)
    {
        $rules = array(
            'provider_id' => 'required|exists:providers,id',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first(), 'operator_ref' => $validator->messages()->first(), 'payid' => '']);
        }
        $providers = Provider::find($request->provider_id);
        $min = ($providers->min_length == 0 && $providers->max_length == 0) ? 0 : $providers->min_length;
        $max = ($providers->min_length == 0 && $providers->max_length == 0) ? 50 : $providers->max_length;

        if ($providers->start_with) {
            $number_validation = 'required|regex:/^[\w-]*$/|between:' . $min . ',' . $max . '|starts_with:' . $providers->start_with . '';
        } else {
            $number_validation = 'required|regex:/^[\w-]*$/|between:' . $min . ',' . $max . '';
        }
        if ($providers->min_amount == 0 && $providers->max_amount == 0) {
            $amount_validation = 'required|regex:/^\d+(\.\d{1,2})?$/';
        } else {
            $amount_validation = 'required|numeric|between:' . $providers->min_amount . ',' . $providers->max_amount . '';
        }
        $block_amount = (empty($providers->block_amount)) ? "" : 'not_in:' . $providers->block_amount . '';
        $rules = array(
            'number' => $number_validation,
            'amount' => "$amount_validation|$block_amount",
            'provider_id' => 'required|exists:providers,id',
            'client_id' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first(), 'operator_ref' => $validator->messages()->first(), 'payid' => '']);
        }
        $number = $request->number;
        $amount = $request->amount;
        $provider_id = $request->provider_id;
        $optional1 = $request->optional1;
        $optional2 = $request->optional2;
        $optional3 = $request->optional3;
        $optional4 = $request->optional4;
        $client_id = $request->client_id;
        $user_id = Auth::id();
        $mode = "API";
        $request_ip = request()->ip();
        $payment_mode = "Cash";
        $duedate = "";
        $customer_name = "";
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        if (Auth::User()->member->ip_address == $request_ip) {
            return Self::recharge_middle($number, $amount, $provider_id, $optional1, $optional2, $optional3, $optional4, $client_id, $user_id, $mode, $request_ip, $payment_mode, $duedate, $customer_name, $latitude, $longitude);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Invalid ip address', 'operator_ref' => 'Invalid ip address', 'payid' => '']);
        }
    }

    function recharge_middle($number, $amount, $provider_id, $optional1, $optional2, $optional3, $optional4, $client_id, $user_id, $mode, $request_ip, $payment_mode, $duedate, $customer_name, $latitude, $longitude)
    {
        $userdetails = User::find($user_id);
        $providers = Provider::where('id', $provider_id)->where('status_id', 1)->first();
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($userdetails->company->server_down == 1 && $serviceStatus == 1) {
            $opening_balance = $userdetails->balance->user_balance;
            $sumamount = $amount + $userdetails->lock_amount + $userdetails->balance->lien_amount;
            if ($opening_balance >= $sumamount && $sumamount >= 4) {
                $library = new BasicLibrary();
                $apidetails = $library->get_api($provider_id, $number, $amount, $user_id);
                $api_id = $apidetails['api_id'];
                $state_id = $apidetails['state_id'];
                //get commission
                $scheme_id = $userdetails->scheme_id;
                $library = new GetcommissionLibrary();
                $commission = $library->get_commission($scheme_id, $provider_id, $amount);
                $retailer = $commission['retailer'];
                $d = $commission['distributor'];
                $sd = $commission['sdistributor'];
                $st = $commission['sales_team'];
                $rf = $commission['referral'];
                $deduct_amount = $amount - $retailer;
                Balance::where('user_id', $user_id)->decrement('user_balance', $deduct_amount);
                $balance = Balance::where('user_id', $user_id)->first();
                $user_balance = $balance->user_balance;
                if ($provider_id == 49 && $userdetails->role_id <= 9) {
                    $number = $optional2;
                }
                $row_data = collect([
                    'optional1' => $optional1,
                    'optional2' => $optional2,
                    'optional3' => $optional3,
                    'optional4' => $optional4,
                    'duedate' => $duedate,
                    'customer_name' => $customer_name,
                ]);
                $now = new \DateTime();
                $ctime = $now->format('Y-m-d H:i:s');
                $description = "$providers->provider_name  $number";
                $insert_id = Report::insertGetId([
                    'number' => $number,
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
                    'state_id' => $state_id,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ]);
                $api_type = 1;
                $library = new RechargeLibrary();
                $result = $library->recharge_api($number, $amount, $provider_id, $optional1, $optional2, $optional3, $optional4, $client_id, $user_id, $api_id, $insert_id, $api_type, $payment_mode);
                $status = $result['status'];
                $txnid = $result['txnid'];
                $print_url = url('agent/transaction-receipt') . '/' . Crypt::encrypt($insert_id);
                $mobile_anchor = url('agent/mobile-receipt') . '/' . Crypt::encrypt($insert_id);
                // if success
                if ($status == 1) {
                    Report::where('id', $insert_id)->update(['status_id' => 1, 'txnid' => $txnid, 'row_data' => $row_data]);
                    $transaction_details = array(
                        'status' => 'success',
                        'operator_ref' => $txnid,
                        'provider_name' => $providers->provider_name,
                        'payid' => $insert_id,
                        'number' => $number,
                        'profit' => $retailer,
                        'amount' => $amount,
                        'date' => "$ctime",
                        'print_url' => $print_url,
                        'mobile_anchor' => $mobile_anchor,
                    );
                    $message = "Dear $userdetails->name, Recharge Success Number: $number Operator: $providers->provider_name And Amount: $amount, Your Remaining Balance is $user_balance Thanks";
                    $library = new Commission_increment();
                    $library->parent_recharge_commission($user_id, $number, $insert_id, $provider_id, $amount, $api_id, $retailer, $d, $sd, $st, $rf);
                    // get wise commission
                    $library = new GetcommissionLibrary();
                    $apiComms = $library->getApiCommission($api_id, $provider_id, $amount);
                    $apiCommission = $apiComms['apiCommission'];
                    $commissionType = $apiComms['commissionType'];
                    $library = new Commission_increment();
                    $library->updateApiComm($user_id, $provider_id, $api_id, $amount, $retailer, $d, $sd, $st, $rf, $apiCommission, $insert_id, $commissionType);
                    if ($userdetails->role_id == 10) {
                        return Response()->json(['status' => 'success', 'message' => $message, 'operator_ref' => $txnid, 'payid' => $insert_id]);
                    } else {
                        return Response()->json(['status' => 'success', 'message' => $message, 'transaction_details' => $transaction_details]);
                    }
                } // if failure
                elseif ($status == 2) {
                    Balance::where('user_id', $user_id)->increment('user_balance', $deduct_amount);
                    $balance = Balance::where('user_id', $user_id)->first();
                    $user_balance = $balance->user_balance;
                    Report::where('id', $insert_id)->update(['status_id' => 2, 'txnid' => $txnid, 'profit' => 0, 'total_balance' => $user_balance, 'row_data' => $row_data]);
                    $message = "Dear  $userdetails->name, Transaction Failed, Number : $number  Operator : $providers->provider_name And Amount Rs $amount Please check Detail or Try After some time, Thanks";
                    $transaction_details = array(
                        'status' => 'failure',
                        'operator_ref' => $txnid,
                        'provider_name' => $providers->provider_name,
                        'payid' => $insert_id,
                        'number' => $number,
                        'profit' => $retailer,
                        'amount' => $amount,
                        'date' => "$ctime",
                        'print_url' => $print_url,
                        'mobile_anchor' => $mobile_anchor,
                    );
                    if ($userdetails->role_id == 10) {
                        return Response()->json(['status' => 'failure', 'message' => $message, 'operator_ref' => $txnid, 'payid' => $insert_id]);
                    } else {
                        return Response()->json(['status' => 'failure', 'message' => $message, 'transaction_details' => $transaction_details]);
                    }
                } // if pending
                else {
                    Report::where('id', $insert_id)->update(['row_data' => $row_data]);
                    $message = "Dear $userdetails->name, Recharge Submitted Number: $number Operator: $providers->provider_name And Amount: $amount, Your Remaining Balance is $user_balance Thanks";
                    $transaction_details = array(
                        'status' => 'pending',
                        'operator_ref' => $txnid,
                        'provider_name' => $providers->provider_name,
                        'payid' => $insert_id,
                        'number' => $number,
                        'profit' => $retailer,
                        'amount' => $amount,
                        'date' => "$ctime",
                        'print_url' => $print_url,
                        'mobile_anchor' => $mobile_anchor,
                    );
                    if ($userdetails->role_id == 10) {
                        return Response()->json(['status' => 'pending', 'message' => $message, 'operator_ref' => $txnid, 'payid' => $insert_id]);
                    } else {
                        return Response()->json(['status' => 'success', 'message' => $message, 'transaction_details' => $transaction_details]);
                    }
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Your Balance is low kinldy refill your wallet', 'operator_ref' => 'Your Balance is low kinldy refill your wallet', 'payid' => '']);
            }
        } else {
            $message = ($userdetails->company->server_down == 1) ? 'Service not active!' : $userdetails->company->server_message;
            return Response()->json(['status' => 'failure', 'message' => $message, 'operator_ref' => $userdetails->company->server_message, 'payid' => '']);
        }
    }


    function bbps_bill_verify_app(Request $request)
    {
        $rules = array(
            'optional1' => 'required',
            'provider_id' => 'required|exists:providers,id',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $provider_id = $request->provider_id;
        $optional1 = $request->optional1;
        $optional2 = $request->optional2;
        $optional3 = $request->optional3;
        $optional4 = $request->optional4;
        $payment_mode = $request->payment_mode;
        return Self::bbps_bill_verify_middle($provider_id, $optional1, $optional2, $optional3, $optional4, $payment_mode);
    }

    function bbps_bill_verify_api(Request $request)
    {
        $rules = array(
            'optional1' => 'required',
            'provider_id' => 'required|exists:providers,id',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $provider_id = $request->provider_id;
        $optional1 = $request->optional1;
        $optional2 = $request->optional2;
        $optional3 = $request->optional3;
        $optional4 = $request->optional4;
        $payment_mode = $request->payment_mode;
        return Self::bbps_bill_verify_middle($provider_id, $optional1, $optional2, $optional3, $optional4, $payment_mode);
    }

    function bbps_bill_verify(Request $request)
    {
        $rules = array(
            'number' => 'required',
            'provider_id' => 'required|exists:providers,id',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }

        $number = $request->number;
        $provider_id = $request->provider_id;
        $library = new BasicLibrary();
        $split_number = $library->split_number($number);
        $optional1 = $split_number['optional1'];
        $optional2 = $split_number['optional2'];
        $optional3 = $split_number['optional3'];
        $optional4 = $split_number['optional4'];
        $payment_mode = $request->payment_mode;
        return Self::bbps_bill_verify_middle($provider_id, $optional1, $optional2, $optional3, $optional4, $payment_mode);
    }

    function bbps_bill_verify_middle($provider_id, $optional1, $optional2, $optional3, $optional4, $payment_mode)
    {
        $api_id = 1;
        $userdetails = User::find(Auth::id());
        $mobile_number = $userdetails->mobile;
        $providers = Provider::where('id', $provider_id)->first();
        if ($providers->fetchRequirement == 0) {
            return Response()->json([
                'status' => 'success',
                'provider_name' => $providers->provider_name,
                'number' => $optional1,
                'amount' => '',
                'name' => '',
                'duedate' => '',
                'provider_id' => $provider_id,
                'optional1' => $optional1,
                'optional2' => $optional2,
                'optional3' => $optional3,
                'optional4' => '',
            ]);
        }
        $apiproviders = Apiprovider::where('api_id', $api_id)->where('provider_id', $provider_id)->first();
        if (!empty($apiproviders->operator_code)) {
            $operator_code = $apiproviders->operator_code;
            $apis = Api::find($api_id);
            $api_token = $apis->api_key;
            $url = "https://mpayment.in/api/telecom/v1/bill-verify";
            $api_request_parameters = array(
                'api_token' => $api_token,
                'provider_id' => $operator_code,
                'optional1' => $optional1,
                'optional2' => $optional2,
                'optional3' => $optional3,

            );
            $method = 'POST';
            $header = ["Accept:application/json"];
             $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
            $res = json_decode($response);
            $status = $res->status ?? 'failure';
            if ($status == 'success'){
                return Response()->json([
                    'status' => 'success',
                    'provider_name' => $providers->provider_name,
                    'number' => $optional1,
                    'amount' => $res->amount ?? 0,
                    'name' => $res->name ?? '',
                    'duedate' => $res->duedate ?? '',
                    'provider_id' => $provider_id,
                    'optional1' => $optional1,
                    'optional2' => $optional2,
                    'optional3' => $optional3,
                    'optional4' => $res->reference_id ?? '',
                ]);
            }else{
                return Response()->json(['status' => 'failure', 'message' => $res->message ?? 'Unknown error']);
            }

        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Operator code not added!']);
        }

    }


    function check_provider_validation(Request $request)
    {
        $rules = array(
            'provider_id' => 'required|exists:providers,id',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $provider_id = $request->provider_id;
        $providers = Provider::find($provider_id);
        if (empty($providers->customerParams)) {
            $api_id = 1;
            $providers = Provider::find($provider_id);
            $apiproviders = Apiprovider::where('api_id', $api_id)->where('provider_id', $provider_id)->first();
            if (!empty($apiproviders->operator_code)) {
                $apis = Api::find($api_id);
                $api_token = $apis->api_key;
                $operator_code = $apiproviders->operator_code;
                $url = "https://mpayment.in/api/telecom/v1/provider-validation";
                $api_request_parameters = array('api_token' => $api_token, 'provider_id' => $operator_code);
                $method = 'POST';
                $header = ["Accept:application/json"];
                $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
                $res = json_decode($response);
                $status = $res->status ?? 'failure';
                if ($status == 'success') {
                    Provider::where('id', $provider_id)->update(['fetchRequirement' => $res->is_validation ?? 0, 'customerParams' => json_encode($res->params)]);
                }
                if ($status != 'success') {
                    return Response()->json(['status' => 'failure', 'message' => 'something went wrong']);
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Provider code not added!']);
            }
        }
        $providers = Provider::find($provider_id);
        if (!empty($providers->customerParams)) {
            return Response()->json([
                'status' => 'success',
                'is_validation' => 1,
                'params' => json_decode($providers->customerParams),
                'payment_modes' => [],

            ]);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Something went wrong']);
        }
    }




    function check_balance_api(Request $request)
    {
        if (Auth::User()->profile->aeps == 1) {
            $balance = array(
                'normal_balance' => number_format(Auth::User()->balance->user_balance, 1),
                'aeps_balance' => number_format(Auth::User()->balance->aeps_balance, 1),
            );
        } else {
            $balance = array(
                'normal_balance' => number_format(Auth::User()->balance->user_balance, 1),
            );
        }

        return Response(['status' => 'success', 'balance' => $balance]);
    }

    function check_status_api(Request $request)
    {
        $rules = array(
            'client_id' => 'required|exists:reports,client_id',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $client_id = $request->client_id;
        $reports = Report::where('user_id', Auth::id())->where('client_id', $client_id)->first();
        if ($reports) {
            $transaction = array(
                'id' => $reports->id,
                'provider' => $reports->provider->provider_name,
                'date' => "$reports->created_at",
                'number' => $reports->number,
                'amount' => number_format($reports->amount, 2),
                'profit' => number_format($reports->profit, 2),
                'txnid' => $reports->txnid,
                'client_id' => $reports->client_id,
                'ip_address' => $reports->ip_address,
                'status' => $reports->status->status,
            );
            return Response()->json([
                'status' => 'success',
                'message' => 'success',
                'transaction' => $transaction,
            ]);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'record not found']);
        }
    }

    function get_provider()
    {
        $api = Api::where('vender_id', 10)->first();
        if ($api) {
            $key = 'Bearer ' . $api->api_key;
        }
        $url = "https://api.pay2all.in/v1/app/providers?service_id=28";
        $api_request_parameters = array();
        $method = 'GET';
        $header = ["Accept:application/json", "Authorization:" . $key];
        $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
        $res = json_decode($response);
        $providers = $res->providers;
        $now = new \DateTime();
        $ctime = $now->format('Y-m-d H:i:s');
        $service_id = 15;
        foreach ($providers as $value) {
            $providers = Provider::where('provider_name', $value->provider_name)->first();
            if (empty($providers)) {
                Provider::insert([
                    'provider_name' => $value->provider_name,
                    'service_id' => $service_id,
                    'api_id' => 1,
                    'merchant_pay2all' => $value->id,
                    'created_at' => $ctime,
                    'status_id' => 1,
                ]);
            }
        }
        return true;
    }


}
