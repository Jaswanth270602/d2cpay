<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use \Crypt;

// models here
use App\Models\User;
use App\Models\Service;
use App\Models\Provider;
use App\Models\Servicebanner;
use App\Models\Api;
use App\Models\Balance;
use App\Models\Report;
use App\Library\LocationRestrictionsLibrary;
use App\Library\BasicLibrary;
use App\Library\GetcommissionLibrary;
use App\Library\Commission_increment;

// api module
use App\Pay2all\RechargeBillpay as pay2allBillPay;

class BbpsV1Controller extends Controller
{
    public function __construct()
    {
        $this->vendor_id = 1;
        $apis = Api::where('vender_id', $this->vendor_id)->first();
        $this->api_id = $apis->id;
    }


    function welcome($slug)
    {
        $services = Service::where('sub_slug', $slug)->where('status_id', 1)->where('bbps', 1)->first();
        if (empty($services)) {
            return redirect()->back();
        }
        if (Auth::User()->role_id <= 9) {
            $service_id = $services->id;
            $providers = Provider::where('service_id', $service_id)->where('status_id', 1)->select('id', 'provider_name')->get();
            $servicebanner = Servicebanner::where('company_id', Auth::User()->company_id)->where('service_id', $service_id)->where('status_id', 1)->select('service_banner')->get();
            $data = array(
                'page_title' => $services->service_name
            );
            return view('agent.bbps.pay2allBbps', compact('providers', 'servicebanner'))->with($data);
        } else {
            return redirect()->back();
        }
    }

    function billerParams(Request $request)
    {
        $rules = array(
            'provider_id' => 'required|exists:providers,id',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $provider_id = $request->provider_id;
        $providers = Provider::find($provider_id);
        if (empty($providers->customerParams)) {
            if ($this->vendor_id == 1) {
                $library = new pay2allBillPay();
                $response = $library->getBillerParams($provider_id);
                $status_id = $response['status_id'];
                if ($status_id == 2) {
                    return Response()->json(['status' => 'failure', 'message' => $response['message']]);
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => "You don't have permission to access this page"]);
            }
        }
        $providers = Provider::find($provider_id);
        return Response()->json([
            'status' => 'success',
            'message' => "Successfull..",
            'fetchRequirement' => $providers->fetchRequirement,
            'customerParams' => json_decode($providers->customerParams)
        ]);
    }

    function fatchBill(Request $request)
    {
        $rules = array(
            'provider_id' => 'required|exists:providers,id',
            'customerParams' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $provider_id = $request->provider_id;
        $customerParams = $request->customerParams;
        $mode = 'WEB';
        $client_id = '';
        return Self::fatchBillMiddle($provider_id, $customerParams, $mode, $client_id);
    }

    function fatchBillMiddle($provider_id, $customerParams, $mode, $client_id)
    {
        if ($this->vendor_id == 1) {
            $library = new pay2allBillPay();
            return $library->fatchBill($provider_id, $customerParams, $mode, $client_id);
        } else {
            return Response()->json(['status' => 'failure', 'message' => "You don't have permission to access this page"]);
        }
    }

    function viewBill(Request $request)
    {
        $providers = Provider::find($request->provider_id);
        $minAmount = (empty($providers)) ? 0 : $providers->min_amount;
        $maxAmount = (empty($providers)) ? 0 : $providers->max_amount;
        $amountValidation = ($minAmount == 0 && $maxAmount == 0) ? 'required|regex:/^\d+(\.\d{1,2})?$/' : 'required|numeric|between:' . $minAmount . ',' . $maxAmount . '';
        $rules = array(
            'provider_id' => 'required|exists:providers,id',
            'amount' => "$amountValidation",
            'customerParams' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $customerParams = json_decode($request->customerParams);
        $number = $customerParams[0]->value;
        $data = array(
            'provider_name' => $providers->provider_name,
            'amount' => $request->amount,
            'number' => $number,
        );
        return Response()->json(['status' => 'success', 'message' => 'Successful..!', 'data' => $data, 'customerParams' => $customerParams]);
    }


    function payNow(Request $request)
    {
        $providers = Provider::find($request->provider_id);
        $minAmount = (empty($providers)) ? 0 : $providers->min_amount;
        $maxAmount = (empty($providers)) ? 0 : $providers->max_amount;
        $amountValidation = ($minAmount == 0 && $maxAmount == 0) ? 'required|regex:/^\d+(\.\d{1,2})?$/' : 'required|numeric|between:' . $minAmount . ',' . $maxAmount . '';
        $rules = array(
            'provider_id' => 'required|exists:providers,id',
            'amount' => "$amountValidation",
            'customerParams' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'reference_id' => '' . ($providers->fetchRequirement == 1) ? 'required' : 'nullable' . '',
            'transaction_pin' => '' . (Auth::User()->company->transaction_pin == 1) ? 'required|digits:6' : 'nullable' . '',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $customerParams = $request->customerParams;
        $jsonDecode = json_decode($customerParams, true);
        $number = $jsonDecode[0]['value'];
        $provider_id = $request->provider_id;
        $amount = $request->amount;
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $user_id = Auth::id();
        $mode = "WEB";
        $client_id = '';
        $reference_id = $request->reference_id;
        $locationrestrictionsLibrary = new LocationRestrictionsLibrary();
        $isLoginValid = $locationrestrictionsLibrary->loginRestrictions($user_id, $latitude, $longitude);
        if ($isLoginValid == 0) {
            $kilometer = Auth::User()->company->login_restrictions_km;
            return Response()->json(['status' => 'failure', 'message' => "You must be within $kilometer kilometer to access this service."]);
        }
        return Self::payNowMiddle($provider_id, $amount, $number, $customerParams, $user_id, $mode, $latitude, $longitude, $client_id, $reference_id);
    }

    function payNowMiddle($provider_id, $amount, $number, $customerParams, $user_id, $mode, $latitude, $longitude, $client_id, $reference_id)
    {
        $request_ip = request()->ip();
        $providers = Provider::find($provider_id);
        $userdetails = User::find($user_id);
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
                //get commission
                $scheme_id = $userdetails->scheme_id;
                $library = new GetcommissionLibrary();
                $commission = $library->get_commission($scheme_id, $provider_id, $amount);
                $retailer = $commission['retailer'];
                $d = $commission['distributor'];
                $sd = $commission['sdistributor'];
                $st = $commission['sales_team'];
                $rf = $commission['referral'];
                $decrementAmount = $amount - $retailer;
                Balance::where('user_id', $user_id)->decrement('user_balance', $decrementAmount);
                $balance = Balance::where('user_id', $user_id)->first();
                $user_balance = $balance->user_balance;
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
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ]);
                if ($api_id == 1) {
                    $library = new pay2allBillPay();
                    $response = $library->payNow($provider_id, $amount, $number, $customerParams, $user_id, $mode, $latitude, $longitude, $client_id, $reference_id, $insert_id);
                    $status_id = $response['status_id'];
                    $txnid = $response['txnid'];
                } else {
                    $status_id = 2;
                    $txnid = "You don't have permission!";
                }
                $print_url = url('agent/transaction-receipt') . '/' . Crypt::encrypt($insert_id);
                $mobile_anchor = url('agent/mobile-receipt') . '/' . Crypt::encrypt($insert_id);
                $data = ['operator_ref' => $txnid, 'provider_name' => $providers->provider_name, 'payid' => $insert_id, 'number' => $number, 'profit' => $retailer, 'amount' => $amount, 'date' => "$ctime", 'print_url' => $print_url, 'mobile_anchor' => $mobile_anchor,];
                if ($status_id == 1) {
                    Report::where('id', $insert_id)->update(['status_id' => 1, 'txnid' => $txnid]);
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
                    return Response()->json(['status' => 'success', 'message' => $message, 'data' => $data]);
                } elseif ($status_id == 2) {
                    $message = "Dear  $userdetails->name, Transaction Failed, Number : $number  Operator : $providers->provider_name And Amount Rs $amount Please check Detail or Try After some time, Thanks";
                    Balance::where('user_id', $user_id)->increment('user_balance', $decrementAmount);
                    $balance = Balance::where('user_id', $user_id)->first();
                    $user_balance = $balance->user_balance;
                    Report::where('id', $insert_id)->update(['status_id' => 2, 'txnid' => $txnid, 'profit' => 0, 'total_balance' => $user_balance]);
                    return Response()->json(['status' => 'failure', 'message' => $message, 'data' => $data]);
                } else {
                    $message = "Dear $userdetails->name, Recharge Submitted Number: $number Operator: $providers->provider_name And Amount: $amount, Your Remaining Balance is $user_balance Thanks";
                    if (Auth::User()->role_id == 10) {
                        return Response()->json(['status' => 'pending', 'message' => $message, 'data' => $data]);
                    } else {
                        return Response()->json(['status' => 'success', 'message' => $message, 'data' => $data]);
                    }
                }
            }
        } else {
            $message = ($userdetails->company->server_down == 1) ? 'Service not active!' : $userdetails->company->server_message;
            return Response()->json(['status' => 'failure', 'message' => $message, 'operator_ref' => $userdetails->company->server_message, 'payid' => '']);
        }
    }
}

