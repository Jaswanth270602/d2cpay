<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use DB;
use Hash;
use Helpers;
use App\Models\Api;
use App\Models\Masterbank;
use App\Models\User;
use App\Models\Accountvalidate;
use App\Models\Provider;
use App\Models\Balance;
use App\Models\Report;
use App\Models\Mreport;
use App\Models\Beneficiary;
// library here
use App\Library\BasicLibrary;
use App\Library\LocationRestrictionsLibrary;
use App\Library\GetcommissionLibrary;
use App\Library\Commission_increment;
use App\Library\DmtLibrary;
//dmt service
use App\Pay2all\Dmt as Pay2all;

class Moneyv2Controller extends Controller
{

    public function __construct()
    {
        $this->vendor_id = 1;
        $this->money_provider_id = 317;
        $this->verification_provider_id = 315;

        $apis = Api::where('vender_id', $this->vendor_id)->first();
        $this->api_id = $apis->id;
    }



    function welcome()
    {
        $user_id = Auth::id();
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($this->money_provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($serviceStatus == 1 && Auth::User()->role_id == 8) {
            $masterbank = Masterbank::where('status_id', 1)->select('id', 'bank_name')->get();
            $data = array('page_title' => 'Money Transfer');
            return view('agent.dmt.route_two', compact('masterbank'))->with($data);
        } else {
            return redirect()->back();
        }
    }

    function getCustomer(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        if ($this->vendor_id == 1) {
            $library = new Pay2all();
            return $library->getCustomer($mobile_number);
        } else {
            return Response()->json(['status' => 'failure', 'message' => "You don't have permission to access this page", 'ad1' => '', 'ad2' => '']);
        }
    }

    function addSender(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',
            'first_name' => 'required',
            'last_name' => 'required',
            'pincode' => 'required',
            'state' => 'required',
            'address' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $first_name = $request->first_name;
        $last_name = $request->last_name;
        $pincode = $request->pincode;
        $state = $request->state;
        $address = $request->address;
        $ad1 = $request->ad1;
        $ad2 = $request->ad2;
        if ($this->vendor_id == 1) {
            $library = new Pay2all();
            return $library->addSender($mobile_number, $first_name, $last_name, $pincode, $state, $address, $ad1, $ad2);
        } else {
            return Response()->json(['status' => 'failure', 'message' => "You don't have permission to access this page", 'ad1' => '', 'ad2' => '']);
        }
    }

    function confirmSender(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',
            'otp' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $otp = $request->otp;
        $ad1 = $request->ad1;
        $ad2 = $request->ad2;
        if ($this->vendor_id == 1) {
            $library = new Pay2all();
            return $library->confirmSender($mobile_number, $otp, $ad1, $ad2);
        } else {
            return Response()->json(['status' => 'failure', 'message' => "You don't have permission to access this page", 'ad1' => '', 'ad2' => '']);
        }
    }

    function senderResendOtp(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',
            'first_name' => 'required',
            'last_name' => 'required',
            'pincode' => 'required',
            'state' => 'required',
            'address' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $first_name = $request->first_name;
        $last_name = $request->last_name;
        $pincode = $request->pincode;
        $state = $request->state;
        $address = $request->address;
        $ad1 = $request->ad1;
        $ad2 = $request->ad2;
        if ($this->vendor_id == 1) {
            $library = new Pay2all();
            return $library->senderResendOtp($mobile_number, $first_name, $last_name, $pincode, $state, $address, $ad1, $ad2);
        } else {
            return Response()->json(['status' => 'failure', 'message' => "You don't have permission to access this page", 'ad1' => '', 'ad2' => '']);
        }
    }

    function getAllBeneficiary(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',
            'sender_name' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $sender_name = $request->sender_name;
        if ($this->vendor_id == 1) {
            $library = new Pay2all();
            return $library->getAllBeneficiary($mobile_number, $sender_name);
        } else {
            return Response()->json(['status' => 'failure', 'message' => "You don't have permission to access this page", 'ad1' => '', 'ad2' => '']);
        }
    }

    function searchByAccount(Request $request)
    {
        $rules = array(
            'account_number' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $account_number = $request->account_number;
        $beneficiaries = Beneficiary::where('account_number', $account_number)->where('api_id', $this->api_id)->get();
        $response = array();
        foreach ($beneficiaries as $value) {
            $product = array();
            $product["remiter_number"] = $value->remiter_number;
            $product["account_number"] = $value->account_number;
            $product["ifsc"] = $value->ifsc;
            $product["bank_name"] = $value->bank_name;
            $product["name"] = $value->name;
            array_push($response, $product);
        }
        return Response()->json(['status' => 'success', 'message' => 'Successful..!', 'beneficiaries' => $response]);
    }

    function getIfscCode(Request $request)
    {
        $rules = array(
            'bank_id' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $bank_id = $request->bank_id;
        if ($this->vendor_id == 1) {
            $library = new Pay2all();
            return $library->getIfscCode($bank_id);
        } else {
            return Response()->json(['status' => 'failure', 'message' => "You don't have permission to access this page", 'ad1' => '', 'ad2' => '']);
        }
    }

    function addBeneficiary(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',
            //'bank_id' => 'required',
            'ifsc_code' => 'required',
            'account_number' => 'required',
            'beneficiary_name' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $bank_id = $request->bank_id;
        $ifsc_code = $request->ifsc_code;
        $account_number = $request->account_number;
        $beneficiary_name = $request->beneficiary_name;
        if ($this->vendor_id == 1) {
            $library = new Pay2all();
            return $library->addBeneficiary($mobile_number, $bank_id, $ifsc_code, $account_number, $beneficiary_name);
        } else {
            return Response()->json(['status' => 'failure', 'message' => "You don't have permission to access this page", 'ad1' => '', 'ad2' => '']);
        }
    }

    function confirmBeneficiary(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',
            'otp' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $otp = $request->otp;
        $ad1 = $request->ad1;
        $ad2 = $request->ad2;
        if ($this->vendor_id == 1) {
            $library = new Pay2all();
            return $library->confirmBeneficiary($mobile_number, $otp, $ad1, $ad2);
        } else {
            return Response()->json(['status' => 'failure', 'message' => "You don't have permission to access this page", 'ad1' => '', 'ad2' => '']);
        }
    }


    function deleteBeneficiary(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',
            'beneficiary_id' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $mobile_number = $request->mobile_number;
        $beneficiary_id = $request->beneficiary_id;
        if ($this->vendor_id == 1) {
            $library = new Pay2all();
            return $library->deleteBeneficiary($mobile_number, $beneficiary_id);
        } else {
            return Response()->json(['status' => 'failure', 'message' => "You don't have permission to access this page"]);
        }
    }

    function confirmDeleteBeneficiary(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',
            'otp' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $ad1 = $request->ad1;
        $ad2 = $request->ad2;
        $otp = $request->otp;
        if ($this->vendor_id == 1) {
            $library = new Pay2all();
            return $library->confirmDeleteBeneficiary($mobile_number, $ad1, $ad2, $otp);
        } else {
            return Response()->json(['status' => 'failure', 'message' => "You don't have permission to access this page"]);
        }
    }

    function accountVerifyWeb(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',
            // 'bank_id' => 'required',
            'ifsc_code' => 'required',
            'account_number' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $bank_id = $request->bank_id;
        $ifsc_code = $request->ifsc_code;
        $account_number = $request->account_number;
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $user_id = Auth::id();
        $client_id = '';
        $mode = "WEB";
        $locationrestrictionsLibrary = new LocationRestrictionsLibrary();
        $isLoginValid = $locationrestrictionsLibrary->loginRestrictions($user_id, $latitude, $longitude);
        if ($isLoginValid == 0) {
            $kilometer = Auth::User()->company->login_restrictions_km;
            return Response()->json(['status' => 'failure', 'message' => "You must be within $kilometer kilometer to access this service."]);
        }
        return Self::accountVerifyMiddle($mobile_number, $bank_id, $ifsc_code, $account_number, $latitude, $longitude, $user_id, $client_id, $mode);
    }

    function accountVerifyApp(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',
            // 'bank_id' => 'required',
            'ifsc_code' => 'required',
            'account_number' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $bank_id = $request->bank_id;
        $ifsc_code = $request->ifsc_code;
        $account_number = $request->account_number;
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $user_id = Auth::id();
        $client_id = '';
        $mode = "APP";
        $locationrestrictionsLibrary = new LocationRestrictionsLibrary();
        $isLoginValid = $locationrestrictionsLibrary->loginRestrictions($user_id, $latitude, $longitude);
        if ($isLoginValid == 0) {
            $kilometer = Auth::User()->company->login_restrictions_km;
            return Response()->json(['status' => 'failure', 'message' => "You must be within $kilometer kilometer to access this service."]);
        }
        return Self::accountVerifyMiddle($mobile_number, $bank_id, $ifsc_code, $account_number, $latitude, $longitude, $user_id, $client_id, $mode);
    }

    function accountVerifyMiddle($mobile_number, $bank_id, $ifsc_code, $account_number, $latitude, $longitude, $user_id, $client_id, $mode)
    {
        $request_ip = request()->ip();
        $userdetails = User::find($user_id);
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($this->verification_provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($userdetails->company->server_down == 1 && $serviceStatus == 1) {
            $accountvalidates = Accountvalidate::where('account_number', $account_number)->where('api_id', $this->api_id)->first();
            if (!empty($accountvalidates)) {
                $data = array('beneficiary_name' => $accountvalidates->beneficiary_name);
                return Response()->json(['status' => 'success', 'message' => 'verifyed form our database', 'data' => $data]);
            }
            $provider_id = $this->verification_provider_id;
            $amount = 3;
            $scheme_id = $userdetails->scheme_id;
            $library = new GetcommissionLibrary();
            $commission = $library->get_commission($scheme_id, $provider_id, $amount);
            $retailer = $commission['retailer'];
            $d = $commission['distributor'];
            $sd = $commission['sdistributor'];
            $st = $commission['sales_team'];
            $rf = $commission['referral'];
            $amount = ($retailer <= 1) ? 3 : $retailer;
            $opening_balance = $userdetails->balance->user_balance;
            $sumamount = $amount + $userdetails->lock_amount + $userdetails->balance->lien_amount;
            if ($opening_balance >= $sumamount && $sumamount >= 1) {
                $providers = Provider::find($provider_id);
                Balance::where('user_id', $user_id)->decrement('user_balance', $amount);
                $balance = Balance::where('user_id', $user_id)->first();
                $user_balance = $balance->user_balance;
                $now = new \DateTime();
                $ctime = $now->format('Y-m-d H:i:s');
                $description = "$providers->provider_name  $account_number";
                $wallet_type = 1;
                $insert_id = Report::insertGetId([
                    'number' => $account_number,
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
                    'wallet_type' => $wallet_type,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ]);
                if ($this->vendor_id == 1) {
                    $library = new Pay2all();
                    $response = $library->accountVerify($mobile_number, $bank_id, $ifsc_code, $account_number, $insert_id, $this->api_id);
                    $status_id = $response['status_id'];
                    if ($status_id == 1) {
                        $name = $response['name'];
                        Report::where('id', $insert_id)->update(['status_id' => 1, 'txnid' => $name]);
                        Accountvalidate::insertGetId([
                            'account_number' => $account_number,
                            'ifsc_code' => $ifsc_code,
                            'beneficiary_name' => $name,
                            'created_at' => $ctime,
                            'status_id' => 1,
                            'api_id' => $this->api_id,
                        ]);
                        $library = new Commission_increment();
                        $library->parent_recharge_commission($user_id, $account_number, $insert_id, $provider_id, $amount, $this->api_id, $amount, $d, $sd, $st, $rf);
                        //get wise commission
                        $library = new GetcommissionLibrary();
                        $apiComms = $library->getApiCommission($this->api_id, $provider_id, $amount);
                        $apiCommission = $apiComms['apiCommission'];
                        $commissionType = $apiComms['commissionType'];
                        $library = new Commission_increment();
                        $library->updateApiComm($user_id, $provider_id, $this->api_id, $amount, $retailer, $d, $sd, $st, $rf, $apiCommission, $insert_id, $commissionType);
                        $data = array('beneficiary_name' => $name);
                        return Response()->json(['status' => 'success', 'message' => 'verifyed form vendor database', 'data' => $data]);
                    } elseif ($status_id == 2) {
                        Balance::where('user_id', $user_id)->increment('user_balance', $amount);
                        $balance = Balance::where('user_id', $user_id)->first();
                        $user_balance = $balance->user_balance;
                        Report::where('id', $insert_id)->update(['status_id' => 2, 'profit' => 0, 'total_balance' => $user_balance]);
                        return Response()->json(['status' => 'failure', 'message' => $response['message']]);
                    } else {
                        $data = array('beneficiary_name' => '');
                        return Response()->json(['status' => 'pending', 'message' => 'Transaction in process', 'data' => $data]);
                    }
                } else {
                    Balance::where('user_id', $user_id)->increment('user_balance', $amount);
                    $balance = Balance::where('user_id', $user_id)->first();
                    $user_balance = $balance->user_balance;
                    Report::where('id', $insert_id)->update(['status_id' => 2, 'profit' => 0, 'total_balance' => $user_balance]);
                    return Response()->json(['status' => 'failure', 'message' => "You don't have permission to access this page"]);
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Insufficient fund.']);
            }
        } else {
            $message = ($userdetails->company->server_down == 1) ? 'Service not active!' : $userdetails->company->server_message;
            return Response()->json(['status' => 'failure', 'message' => $message]);
        }
    }

    function viewAccountTransfer(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',
            'beneficiary_id' => 'required',
            'account_number' => 'required',
            'ifsc_code' => 'required',
            'beneficiary_name' => 'required',
            'bank_name' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $providers = Provider::find($this->money_provider_id);
        $min = (empty($providers)) ? 100 : $providers->min_amount;
        $max = (empty($providers)) ? 25000 : $providers->max_amount;
        $placeholder = "Enter Amount: Min:$min and Max:$max";
        $data = array(
            'mobile_number' => $request->mobile_number,
            'beneficiary_id' => $request->beneficiary_id,
            'account_number' => $request->account_number,
            'ifsc_code' => $request->ifsc_code,
            'beneficiary_name' => $request->beneficiary_name,
            'bank_name' => $request->bank_name,
            'placeholder' => $placeholder,
        );
        return Response()->json(['status' => 'success', 'message' => 'Successful..!', 'data' => $data]);
    }

    function transferNowWeb(Request $request)
    {
        $providers = Provider::find($this->money_provider_id);
        $min = (empty($providers)) ? 100 : $providers->min_amount;
        $max = (empty($providers)) ? 25000 : $providers->max_amount;
        $rules = array(
            'mobile_number' => 'required|digits:10',
            'beneficiary_id' => 'required',
            'account_number' => 'required',
            'ifsc_code' => 'required',
            'channel_id' => 'required',
            'amount' => 'required|numeric|between:' . $min . ',' . $max . '',
            'transaction_pin' => '' . (Auth::User()->company->transaction_pin == 1) ? 'required|digits:6' : 'nullable' . '',
            'dupplicate_transaction' => 'required|unique:check_duplicates',
            'latitude' => 'required',
            'longitude' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $beneficiary_id = $request->beneficiary_id;
        $account_number = $request->account_number;
        $ifsc_code = $request->ifsc_code;
        $channel_id = $request->channel_id;
        $amount = $request->amount;
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $user_id = Auth::id();
        $mode = 'WEB';
        if (Auth::User()->company->transaction_pin == 1) {
            if (!Hash::check($request->transaction_pin, Auth::User()->transaction_pin)) {
                return Response()->json(['status' => 'failure', 'message' => 'Invalid transaction pin']);
            }
        }
        DB::table('check_duplicates')->insert(['dupplicate_transaction' => $request->dupplicate_transaction]);
        if (Auth::User()->role_id == 10) {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry you cannot access this URL']);
        }
        $locationrestrictionsLibrary = new LocationRestrictionsLibrary();
        $isLoginValid = $locationrestrictionsLibrary->loginRestrictions($user_id, $latitude, $longitude);
        if ($isLoginValid == 0) {
            $kilometer = Auth::User()->company->login_restrictions_km;
            return Response()->json(['status' => 'failure', 'message' => "You must be within $kilometer kilometer to access this service."]);
        }
        return Self::transferNowMiddle($mobile_number, $beneficiary_id, $account_number, $ifsc_code, $channel_id, $amount, $latitude, $longitude, $user_id, $mode);
    }

    function transferNowApp(Request $request)
    {
        $providers = Provider::find($this->money_provider_id);
        $min = (empty($providers)) ? 100 : $providers->min_amount;
        $max = (empty($providers)) ? 25000 : $providers->max_amount;
        $rules = array(
            'mobile_number' => 'required|digits:10',
            'beneficiary_id' => 'required',
            'account_number' => 'required',
            'ifsc_code' => 'required',
            'channel_id' => 'required',
            'amount' => 'required|numeric|between:' . $min . ',' . $max . '',
            'transaction_pin' => '' . (Auth::User()->company->transaction_pin == 1) ? 'required|digits:6' : 'nullable' . '',
            'latitude' => 'required',
            'longitude' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $beneficiary_id = $request->beneficiary_id;
        $account_number = $request->account_number;
        $ifsc_code = $request->ifsc_code;
        $channel_id = $request->channel_id;
        $amount = $request->amount;
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $user_id = Auth::id();
        $mode = 'WEB';
        if (Auth::User()->company->transaction_pin == 1) {
            if (!Hash::check($request->transaction_pin, Auth::User()->transaction_pin)) {
                return Response()->json(['status' => 'failure', 'message' => 'Invalid transaction pin']);
            }
        }

        $locationrestrictionsLibrary = new LocationRestrictionsLibrary();
        $isLoginValid = $locationrestrictionsLibrary->loginRestrictions($user_id, $latitude, $longitude);
        if ($isLoginValid == 0) {
            $kilometer = Auth::User()->company->login_restrictions_km;
            return Response()->json(['status' => 'failure', 'message' => "You must be within $kilometer kilometer to access this service."]);
        }
        return Self::transferNowMiddle($mobile_number, $beneficiary_id, $account_number, $ifsc_code, $channel_id, $amount, $latitude, $longitude, $user_id, $mode);
    }

    function transferNowMiddle($mobile_number, $beneficiary_id, $account_number, $ifsc_code, $channel_id, $amount, $latitude, $longitude, $user_id, $mode)
    {
        $userdetails = User::find($user_id);
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($this->money_provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($userdetails->company->server_down == 1 && $serviceStatus == 1) {
            $retailer = 5;
            $opening_balance = $userdetails->balance->user_balance;
            $sumamount = $amount + $userdetails->lock_amount + $userdetails->balance->lien_amount + $retailer;
            if ($opening_balance >= $sumamount && $sumamount >= 10) {
                $now = new \DateTime();
                $ctime = $now->format('Y-m-d H:i:s');
                $provider_id = $this->money_provider_id;
                $mreport_id = Mreport::insertGetId([
                    'number' => $account_number,
                    'provider_id' => $provider_id,
                    'amount' => $amount,
                    'api_id' => $this->api_id,
                    'status_id' => 3,
                    'ip_address' => request()->ip(),
                    'created_at' => $ctime,
                    'user_id' => $user_id,
                    'customer_number' => $mobile_number,
                    'channel' => $channel_id,
                    'profit' => '-' . $retailer,
                    'mode' => $mode,
                    'total_balance' => $opening_balance,
                ]);
                $full_amount = number_format($amount, 2);
                $splitAmount = new DmtLibrary();
                $partsAmount = $splitAmount->splitAmount($amount, $provider_id);
                foreach ($partsAmount as $amounts) {
                    sleep(5);
                    Self::trnnew($mobile_number, $user_id, $beneficiary_id, $amounts, $account_number, $mode, $mreport_id, $ifsc_code, $channel_id, $latitude, $longitude);
                }
                $payment_mode = ($channel_id == 2) ? 'IMPS' : 'NEFT';
                $beneficiary = Beneficiary::where('account_number', $account_number)->where('benficiary_id', $beneficiary_id)->first();
                $account_number = (empty($beneficiary)) ? '' : $beneficiary->account_number;
                $ifsc = (empty($beneficiary)) ? '' : $beneficiary->ifsc;
                $bank_name = (empty($beneficiary)) ? '' : $beneficiary->bank_name;
                $beneficiary_name = (empty($beneficiary)) ? '' : $beneficiary->name;
                $remiter_number = (empty($beneficiary)) ? '' : $beneficiary->remiter_number;
                $remiter_name = (empty($beneficiary)) ? '' : $beneficiary->remiter_name;
                $url = url('agent/money-receipt/' . $mreport_id);
                $thermal_print = url('agent/thermal-printer-receipt/' . $mreport_id);
                $benedetails = array(
                    'account_number' => $account_number,
                    'ifsc' => $ifsc,
                    'bank_name' => $bank_name,
                    'beneficiary_name' => $beneficiary_name,
                    'remiter_number' => $remiter_number,
                    'remiter_name' => $remiter_name,
                    'payment_mode' => $payment_mode,
                    'full_amount' => $full_amount,
                    'created_at' => $ctime,
                    'print_url' => $url,
                    'thermal_print' => $thermal_print,
                );
                $reports = Self::getReceipt($mreport_id);
                return Response()->json([
                    'status' => 'success',
                    'benedetails' => $benedetails,
                    'reports' => $reports,
                ]);
            }else{
                return Response()->json(['status' => 'failure', 'message' => 'Insufficient fund.']);
            }
        } else {
            $message = ($userdetails->company->server_down == 1) ? 'Service not active!' : $userdetails->company->server_message;
            return Response()->json(['status' => 'failure', 'message' => $message]);
        }
    }


    function trnnew($mobile_number, $user_id, $beneficiary_id, $amount, $account_number, $mode, $mreport_id, $ifsc_code, $channel_id, $latitude, $longitude)
    {
        $userdetails = User::find($user_id);
        $opening_balance = $userdetails->balance->user_balance;
        $scheme_id = $userdetails->scheme_id;
        $provider_id = $this->money_provider_id;
        $library = new GetcommissionLibrary();
        $commission = $library->get_commission($scheme_id, $provider_id, $amount);
        $retailer = $commission['retailer'];
        $d = $commission['distributor'];
        $sd = $commission['sdistributor'];
        $st = $commission['sales_team'];
        $rf = $commission['referral'];
        $sumamount = $amount + $userdetails->lock_amount + $userdetails->balance->lien_amount + $retailer;
        if ($opening_balance >= $sumamount && $sumamount >= 10) {
            $decrementAmount = $amount + $retailer;
            Balance::where('user_id', $user_id)->decrement('user_balance', $decrementAmount);
            $balance = Balance::where('user_id', $user_id)->first();
            $user_balance = $balance->user_balance;
            $beneficiary = Beneficiary::where('account_number', $account_number)->where('benficiary_id', $beneficiary_id)->where('api_id', $this->api_id)->first();
            $beneficiaryId = (empty($beneficiary)) ? '' : $beneficiary->id;
            $providers = Provider::find($provider_id);
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            $description = "$providers->provider_name  $account_number";
            $api_id = $this->api_id;
            $insert_id = Report::insertGetId([
                'number' => $account_number,
                'provider_id' => $provider_id,
                'amount' => $amount,
                'api_id' => $api_id,
                'status_id' => 3,
                'created_at' => $ctime,
                'user_id' => $user_id,
                'profit' => '-' . $retailer,
                'mode' => $mode,
                'ip_address' => request()->ip(),
                'description' => $description,
                'opening_balance' => $opening_balance,
                'total_balance' => $user_balance,
                'wallet_type' => 1,
                'mreportid' => $mreport_id,
                'channel' => $channel_id,
                'beneficiary_id' => $beneficiaryId,
                'latitude' => $latitude,
                'longitude' => $longitude,
            ]);
            $response = Self::callApi($amount, $user_id, $ifsc_code, $beneficiary_id, $insert_id, $account_number, $mobile_number, $channel_id, $api_id, $latitude, $longitude);
            $status_id = $response['status_id'];
            $txnid = $response['txnid'];
            $payid = $response['payid'];
            if ($status_id == 1) {
                Report::where('id', $insert_id)->update(['status_id' => 1, 'txnid' => $txnid, 'payid' => $payid]);
                $library = new Commission_increment();
                $library->parent_recharge_commission($user_id, $account_number, $insert_id, $provider_id, $amount, $api_id, $retailer, $d, $sd, $st, $rf);
                // get wise commission
                $library = new GetcommissionLibrary();
                $apiComms = $library->getApiCommission($api_id, $provider_id, $amount);
                $apiCommission = $apiComms['apiCommission'];
                $commissionType = $apiComms['commissionType'];
                $library = new Commission_increment();
                $library->updateApiComm($user_id, $provider_id, $api_id, $amount, $retailer, $d, $sd, $st, $rf, $apiCommission, $insert_id, $commissionType);
                return ['status' => 'success', 'message' => 'Transaction success!', 'utr' => $txnid, 'payid' => $insert_id];
            } elseif ($status_id == 2) {
                Balance::where('user_id', $user_id)->increment('user_balance', $decrementAmount);
                $balance = Balance::where('user_id', $user_id)->first();
                $user_balance = $balance->user_balance;
                Report::where('id', $insert_id)->update(['status_id' => 2, 'txnid' => $txnid, 'payid' => $payid, 'total_balance' => $user_balance]);
                return ['status' => 'failure', 'message' => 'Transaction failed!', 'utr' => '', 'payid' => $insert_id];
            } else {
                Report::where('id', $insert_id)->update(['payid' => $payid, 'txnid' => $txnid,]);
                return ['status' => 'pending', 'message' => 'Transaction process!', 'utr' => '', 'payid' => $insert_id];
            }
        }else{
            return Response()->json(['status' => 'failure', 'message' => 'Your Balance Is Low Please Refill Your Wallet']);
        }
    }

    function callApi($amount, $user_id, $ifsc_code, $beneficiary_id, $insert_id, $account_number, $mobile_number, $channel_id, $api_id, $latitude, $longitude)
    {
        if ($this->vendor_id == 1) {
            $library = new Pay2all();
            return $library->transferNow($amount, $user_id, $ifsc_code, $beneficiary_id, $insert_id, $account_number, $mobile_number, $channel_id, $api_id, $latitude, $longitude);
        } else {
            return ['status_id' => 2, 'txnid' => '', 'payid' => ''];
        }
    }


    function getReceipt($mreport_id)
    {
        $reports = Report::where('mreportid', $mreport_id)->get();
        $response = array();
        foreach ($reports as $value) {
            $product = array();
            $product["report_id"] = $value->id;
            $product["utr_number"] = $value->txnid;
            $product["amount"] = number_format($value->amount, 2);
            $product["charges"] = '1%';
            $product["status"] = $value->status->status;
            array_push($response, $product);
        }
        return $response;
    }


    function getTransactionCharges(Request $request)
    {
        $providers = Provider::find($this->money_provider_id);
        $min = (empty($providers)) ? 100 : $providers->min_amount;
        $max = (empty($providers)) ? 25000 : $providers->max_amount;
        $rules = array(
            'amount' => 'required|numeric|between:' . $min . ',' . $max . '',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $amount = $request->amount;
        $user_id = Auth::id();
        $provider_id = $this->money_provider_id;
        $library = new DmtLibrary();
        return $library->getTransactionCharges($user_id, $amount, $provider_id);
    }

    function bank_list()
    {
        if ($this->vendor_id == 1) {
            $library = new Pay2all();
            return $library->getBankList();
        } else {
            return Response()->json(['status' => 'failure', 'message' => "You don't have permission to access this page"]);
        }
    }

}
