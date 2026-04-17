<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use Helpers;
use App\Api;
use App\Upiprovider;
use App\Upiextension;
use App\User;
use App\Provider;
use App\Balance;
use App\Report;
use App\Library\BasicLibrary;
use App\Library\GetcommissionLibrary;
use App\Library\Commission_increment;

class UpitransferController extends Controller
{
    public function __construct()
    {
        $this->company_id = Helpers::company_id()->id;
        $dt = Helpers::company_id();
        $this->company_id = $dt->id;
        $api = Api::where('vender_id', 10)->first();
        $this->key = (empty($api)) ? '' : 'Bearer ' . $api->api_key;
        $this->url = (empty($api)) ? '' : "https://api.pay2all.in/v1/money/";
        $this->api_id = (empty($api)) ? 1 : $api->id;
        $this->trasnferProviderId = 328;
        $this->verifyProviderId = 327;
    }


    function welcome()
    {
        $user_id = Auth::id();
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($this->trasnferProviderId, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($serviceStatus == 1 && Auth::User()->role_id == 8) {
            $data = array('page_title' => 'Trasnfer To UPI');
            $upiproviders = Upiprovider::select('id', 'provider_name')->where('status_id', 1)->get();
            return view('agent.upi-transfer.welcome', compact('upiproviders'))->with($data);
        } else {
            return redirect()->back();
        }
    }

    function getUpiextensions(Request $request)
    {
        $rules = array(
            'provider_id' => 'required|exists:upiproviders,id',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $provider_id = $request->provider_id;
        $upiproviders = Upiprovider::find($provider_id);
        $upiextensions = Upiextension::where('upiprovider_id', $provider_id)->get();
        $response = array();
        foreach ($upiextensions as $value) {
            $product = array();
            $product["id"] = $value->id;
            $product["name"] = $value->extension_name;
            array_push($response, $product);
        }
        return Response()->json([
            'status' => 'success',
            'message' => 'Successful..!',
            'placeholder' => "Enter $upiproviders->provider_name Upi id",
            'example_text' => "Example: $upiproviders->provider_name number 1234567890",
            'extensions' => $response,
        ]);
    }


    function fatchNameWeb(Request $request)
    {
        $rules = array(
            'upi_id' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $user_id = Auth::id();
        $upi_id = $request->upi_id;
        $mode = "WEB";
        $client_id = '';
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        return Self::fatchNameMiddle($user_id, $upi_id, $mode, $client_id, $latitude, $longitude);
    }

    function fatchNameApp(Request $request)
    {
        $rules = array(
            'upi_id' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $user_id = Auth::id();
        $upi_id = $request->upi_id;
        $mode = "APP";
        $client_id = '';
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        return Self::fatchNameMiddle($user_id, $upi_id, $mode, $client_id, $latitude, $longitude);
    }

    function fatchNameApi(Request $request)
    {
        $rules = array(
            'upi_id' => 'required',
            'client_id' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $user_id = Auth::id();
        $upi_id = $request->upi_id;
        $mode = "API";
        $client_id = $request->client_id;
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        return Self::fatchNameMiddle($user_id, $upi_id, $mode, $client_id, $latitude, $longitude);
    }

    function fatchNameMiddle($user_id, $upi_id, $mode, $client_id, $latitude, $longitude)
    {
        $userDetails = User::find($user_id);
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($this->verifyProviderId, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($userDetails->company->server_down == 1 && $serviceStatus == 1) {
            $provider_id = $this->verifyProviderId;
            $amount = 3;
            $scheme_id = $userDetails->scheme_id;
            $library = new GetcommissionLibrary();
            $commission = $library->get_commission($scheme_id, $provider_id, $amount);
            $retailer = $commission['retailer'];
            $d = $commission['distributor'];
            $sd = $commission['sdistributor'];
            $st = $commission['sales_team'];
            $rf = $commission['referral'];
            $amount = ($retailer <= 1) ? 3 : $retailer;
            $opening_balance = $userDetails->balance->user_balance;
            $sumamount = $amount + $userDetails->lock_amount + $userDetails->balance->lien_amount;
            if ($opening_balance >= $sumamount && $sumamount >= 0) {
                $providers = Provider::find($provider_id);
                Balance::where('user_id', $user_id)->decrement('user_balance', $amount);
                $balance = Balance::where('user_id', $user_id)->first();
                $user_balance = $balance->user_balance;
                $now = new \DateTime();
                $ctime = $now->format('Y-m-d H:i:s');
                $description = "$providers->provider_name  $upi_id";
                $wallet_type = 1;
                $api_id = $this->api_id;
                $request_ip = request()->ip();
                $insert_id = Report::insertGetId([
                    'number' => $upi_id,
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
                    'wallet_type' => $wallet_type,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ]);
                $library = new Commission_increment();
                $library->parent_recharge_commission($user_id, $upi_id, $insert_id, $provider_id, $amount, $api_id, $amount, $d, $sd, $st, $rf);
                //get wise commission
                $library = new GetcommissionLibrary();
                $apiComms = $library->getApiCommission($api_id, $provider_id, $amount);
                $apiCommission = $apiComms['apiCommission'];
                $commissionType = $apiComms['commissionType'];
                $library = new Commission_increment();
                $library->updateApiComm($user_id, $provider_id, $api_id, $amount, $retailer, $d, $sd, $st, $rf, $apiCommission, $insert_id, $commissionType);
                return Response()->json([
                    'status' => 'success',
                    'beneficiary_name' => 'Testing',
                    'message' => 'data fetched successfully'
                ]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'insufficient funds kindly refill your wallet!']);
            }
        } else {
            $message = ($userDetails->company->server_down == 1) ? 'Service not active!' : $userDetails->company->server_message;
            return Response()->json(['status' => 'failure', 'message' => $message]);
        }
    }

    function viewTransaction(Request $request)
    {
        $providers = Provider::find($this->trasnferProviderId);
        if ($providers->min_amount == 0 && $providers->max_amount == 0) {
            $amount_validation = 'required|regex:/^\d+(\.\d{1,2})?$/';
        } else {
            $amount_validation = 'required|numeric|between:' . $providers->min_amount . ',' . $providers->max_amount . '';
        }
        $rules = array(
            'upi_id' => 'required',
            'beneficiary_name' => 'required',
            'customer_mobile' => 'required',
            'amount' => "$amount_validation",
            'latitude' => 'required',
            'longitude' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $upi_id = $request->upi_id;
        $beneficiary_name = $request->beneficiary_name;
        $customer_mobile = $request->customer_mobile;
        $amount = $request->amount;
        $details = array(
            'upi_id' => $upi_id,
            'beneficiary_name' => $beneficiary_name,
            'customer_mobile' => $customer_mobile,
            'amount' => $amount,
        );
        return Response()->json(['status' => 'success', 'message' => 'successful..!', 'details' => $details]);
    }
}
