<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use App\Models\User;
use Validator;
use Hash;
use Helpers;
use App\Models\Balance;
use App\Models\Report;
use App\Models\Payoutbeneficiary;
use App\Models\Masterbank;
use App\Models\Accountvalidate;
use App\Models\Api;
use App\Models\Provider;
use App\Models\Apiresponse;
use App\Models\Service;
use App\Models\Banktransferswitching;
use App\Library\BasicLibrary;
use App\Library\GetcommissionLibrary;
use App\Library\Commission_increment;
use App\Library\LocationRestrictionsLibrary;
use App\Library\AccosisLibrary;
use App\Library\PaywizeLibrary;
use App\Library\SprezapayLibrary;
use App\Library\PockethubLibrary;
use App\Library\RazorpaypayoutLibrary;
use App\Library\PunjikendraLibrary;
use App\Library\VtransactLibrary;
use App\Library\SafepPayLibrary;
use App\Library\ZigPayLibrary;
use DB;

//dmt service
use App\Runpaisa\Payout as runPaisaPayout;

class PayoutController extends Controller
{

    public function __construct()
    {
        $this->company_id = Helpers::company_id()->id;
        $dt = Helpers::company_id();
        $this->company_id = $dt->id;
        $api = Api::where('vender_id', 10)->first();
        if ($api) {
            $this->key = 'Bearer ' . $api->api_key;
            $this->url = "https://api.pay2all.in/v1/money/";
            $this->api_id = $api->id;
        }
    }

    function move_to_wallet(Request $request)
    {
        $provider_id = 323;
        $user_id = Auth::id();
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($serviceStatus == 1) {
            $data = array('page_title' => 'Move To Wallet');
            return view('agent.aeps.move_to_wallet')->with($data);
        } else {
            return redirect()->back();
        }
    }

    function move_to_bank(Request $request)
    {
        $provider_id = 324;
        $user_id = Auth::id();
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($serviceStatus == 1) {
            $data = array('page_title' => 'Payout');
            $masterbank = Masterbank::where('status_id', 1)->get();
            return view('agent.aeps.move_to_bank', compact('masterbank'))->with($data);
        } else {
            return Redirect::back();
        }
    }

    function move_to_wallet_web(Request $request)
    {
        $rules = array(
            'amount' => 'required',
            'remark' => 'required',
            'password' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'transaction_pin' => '' . (Auth::User()->company->transaction_pin == 1) ? 'required|digits:6' : 'nullable' . '',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $amount = $request->amount;
        $remark = $request->remark;
        $password = $request->password;
        $user_id = Auth::id();
        $mode = "WEB";
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $locationrestrictionsLibrary = new LocationRestrictionsLibrary();
        $isLoginValid = $locationrestrictionsLibrary->loginRestrictions($user_id, $latitude, $longitude);
        if ($isLoginValid == 0) {
            $kilometer = Auth::User()->company->login_restrictions_km;
            return Response()->json(['status' => 'failure', 'message' => "You must be within $kilometer kilometer to access this service."]);
        }
        return $this->move_to_wallet_middle($amount, $remark, $password, $user_id, $mode, $latitude, $longitude);
    }

    function move_to_wallet_app(Request $request)
    {
        $rules = array(
            'amount' => 'required',
            'remark' => 'required',
            'login_password' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'transaction_pin' => '' . (Auth::User()->company->transaction_pin == 1) ? 'required|digits:6' : 'nullable' . '',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $amount = $request->amount;
        $remark = $request->remark;
        $password = $request->login_password;
        $user_id = Auth::id();
        $mode = "APP";
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $locationrestrictionsLibrary = new LocationRestrictionsLibrary();
        $isLoginValid = $locationrestrictionsLibrary->loginRestrictions($user_id, $latitude, $longitude);
        if ($isLoginValid == 0) {
            $kilometer = Auth::User()->company->login_restrictions_km;
            return Response()->json(['status' => 'failure', 'message' => "You must be within $kilometer kilometer to access this service."]);
        }
        return $this->move_to_wallet_middle($amount, $remark, $password, $user_id, $mode, $latitude, $longitude);
    }

    function move_to_wallet_middle($amount, $remark, $password, $user_id, $mode, $latitude, $longitude)
    {
        $now = new \DateTime();
        $ctime = $now->format('Y-m-d H:i:s');
        $request_ip = request()->ip();
        $provider_id = 323;
        $userdetails = User::find($user_id);
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($userdetails->company->server_down == 1 && $serviceStatus == 1) {
            if ($userdetails->active == 1) {
                $current_password = $userdetails->password;
                if (Hash::check($password, $current_password)) {
                    $opening_balance = $userdetails->balance->aeps_balance;
                    $sumamount = $amount + $userdetails->lock_amount;
                    if ($opening_balance >= $sumamount && $sumamount >= 1) {
                        DB::beginTransaction();
                        try {
                            Balance::where('user_id', $user_id)->decrement('aeps_balance', $amount);
                            $balance = Balance::where('user_id', $user_id)->first();
                            $user_balance = $balance->aeps_balance;
                            $description = "Tansfer to  $userdetails->name";
                            Report::insertGetId([
                                'number' => $userdetails->mobile,
                                'provider_id' => $provider_id,
                                'amount' => $amount,
                                'api_id' => 0,
                                'status_id' => 7,
                                'created_at' => $ctime,
                                'user_id' => $user_id,
                                'profit' => 0,
                                'mode' => $mode,
                                'txnid' => $remark,
                                'ip_address' => $request_ip,
                                'description' => $description,
                                'opening_balance' => $opening_balance,
                                'total_balance' => $user_balance,
                                'credit_by' => $user_id,
                                'wallet_type' => 2,
                                'latitude' => $latitude,
                                'longitude' => $longitude,
                            ]);
                            //increament user balance
                            $opening_balance = $userdetails->balance->user_balance;
                            Balance::where('user_id', $user_id)->increment('user_balance', $amount);
                            $balance = Balance::where('user_id', $user_id)->first();
                            $user_balance = $balance->user_balance;
                            $description = "Tansfer By  $userdetails->name";
                            Report::insertGetId([
                                'number' => $userdetails->mobile,
                                'provider_id' => $provider_id,
                                'amount' => $amount,
                                'api_id' => 0,
                                'status_id' => 6,
                                'created_at' => $ctime,
                                'user_id' => $user_id,
                                'profit' => 0,
                                'mode' => $mode,
                                'txnid' => $remark,
                                'ip_address' => $request_ip,
                                'description' => $description,
                                'opening_balance' => $opening_balance,
                                'total_balance' => $user_balance,
                                'credit_by' => $user_id,
                                'wallet_type' => 1,
                                'latitude' => $latitude,
                                'longitude' => $longitude,
                            ]);
                            DB::commit();
                            return Response()->json(['status' => 'success', 'message' => 'Aeps balance successfully transfered']);
                        } catch (\Exception $ex) {
                            DB::rollback();
                            return response()->json(['status' => 'failure', 'message' => $ex->getMessage()]);
                        }
                    } else {
                        return Response()->json(['status' => 'failure', 'message' => 'Your aeps balance is low']);
                    }
                } else {
                    return Response()->json(['status' => 'failure', 'message' => 'Password is wrong']);
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => $userdetails->reason]);
            }
        } else {
            $message = ($userdetails->company->server_down == 1) ? 'Service not active!' : $userdetails->company->server_message;
            return Response()->json(['status' => 'failure', 'message' => $message]);
        }

    }


    function beneficiary_list(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $payoutbeneficiary = Payoutbeneficiary::where('mobile_number', $mobile_number)->whereNotIn('status_id', [0])->get();
        $response = array();
        $i = 1;
        foreach ($payoutbeneficiary as $value) {
            $banks = Masterbank::where('bank_id', $value->bank_id)->first();
            $product = array();
            $product["sr_no"] = $i++;
            $product["beneficiary_id"] = $value->id;
            $product["mobile_number"] = $value->mobile_number;
            $product["account_number"] = $value->account_number;
            $product["holder_name"] = $value->holder_name;
            $product["bank_name"] = $banks->bank_name;
            $product["ifsc_code"] = $value->ifsc_code;
            $product["status_id"] = $value->status_id;
            array_push($response, $product);
        }
        return Response()->json(['status' => 'success', 'beneficiary_list' => $response]);
    }

    function account_validate(Request $request)
    {
        $rules = array(
            'bank_id' => 'required|exists:masterbanks,bank_id',
            'ifsc_code' => 'required',
            'account_number' => 'required',
            'mobile_number' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $user_id = Auth::id();
        $mode = "WEB";
        $bank_id = $request->bank_id;
        $ifsc_code = $request->ifsc_code;
        $account_number = $request->account_number;
        $mobile_number = $request->mobile_number;
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $client_id = "";
        $locationrestrictionsLibrary = new LocationRestrictionsLibrary();
        $isLoginValid = $locationrestrictionsLibrary->loginRestrictions($user_id, $latitude, $longitude);
        if ($isLoginValid == 0) {
            $kilometer = Auth::User()->company->login_restrictions_km;
            return Response()->json(['status' => 'failure', 'message' => "You must be within $kilometer kilometer to access this service."]);
        }
        return $this->account_validate_middle($user_id, $mode, $bank_id, $ifsc_code, $account_number, $mobile_number, $client_id, $latitude, $longitude);
    }

    function account_validate_app(Request $request)
    {
        $rules = array(
            'bank_id' => 'required|exists:masterbanks,bank_id',
            'ifsc_code' => 'required',
            'account_number' => 'required',
            'mobile_number' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $user_id = Auth::id();
        $mode = "APP";
        $bank_id = $request->bank_id;
        $ifsc_code = $request->ifsc_code;
        $account_number = $request->account_number;
        $mobile_number = $request->mobile_number;
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $client_id = "";
        $locationrestrictionsLibrary = new LocationRestrictionsLibrary();
        $isLoginValid = $locationrestrictionsLibrary->loginRestrictions($user_id, $latitude, $longitude);
        if ($isLoginValid == 0) {
            $kilometer = Auth::User()->company->login_restrictions_km;
            return Response()->json(['status' => 'failure', 'message' => "You must be within $kilometer kilometer to access this service."]);
        }
        return $this->account_validate_middle($user_id, $mode, $bank_id, $ifsc_code, $account_number, $mobile_number, $client_id, $latitude, $longitude);
    }

    function account_validate_middle($user_id, $mode, $bank_id, $ifsc_code, $account_number, $mobile_number, $client_id, $latitude, $longitude)
    {
        $vender_id = 1;
        $apis = Api::where('vender_id', $vender_id)->first();
        $api_id = (empty($apis)) ? 1 : $apis->id;
        $userdetails = User::find($user_id);
        $request_ip = request()->ip();
        $provider_id = 315;
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($userdetails->company->server_down == 1 && $serviceStatus == 1) {
            if ($userdetails->active == 1) {
                $accountvalidates = Accountvalidate::where('account_number', $account_number)->where('api_id', $api_id)->first();
                if (!empty($accountvalidates)) {
                    return Response()->json(['status' => 'success', 'beneficiary_name' => $accountvalidates->beneficiary_name, 'message' => 'verifyed form vendor database']);
                }
                $amount = 3;
                $scheme_id = $userdetails->scheme_id;
                $library = new GetcommissionLibrary();
                $commission = $library->get_commission($scheme_id, $provider_id, $amount);
                $retailer = $commission['retailer'];
                $amount = ($retailer <= 1) ? 3 : $retailer;
                $opening_balance = $userdetails->balance->aeps_balance;
                $sumamount = $amount + $userdetails->lock_amount;
                if ($opening_balance >= $sumamount && $sumamount >= 1) {
                    $providers = Provider::find($provider_id);
                    Balance::where('user_id', $user_id)->decrement('aeps_balance', $amount);
                    $balance = Balance::where('user_id', $user_id)->first();
                    $user_balance = $balance->aeps_balance;
                    $now = new \DateTime();
                    $ctime = $now->format('Y-m-d H:i:s');
                    $description = "$providers->provider_name  $account_number";
                    $insert_id = Report::insertGetId([
                        'number' => $account_number,
                        'provider_id' => $provider_id,
                        'amount' => $amount,
                        'api_id' => $api_id,
                        'status_id' => 3,
                        'client_id' => $client_id,
                        'created_at' => $ctime,
                        'user_id' => $user_id,
                        'profit' => 0,
                        'mode' => $mode,
                        'ip_address' => $request_ip,
                        'description' => $description,
                        'opening_balance' => $opening_balance,
                        'total_balance' => $user_balance,
                        'wallet_type' => 2,
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                    ]);
                    if ($vender_id == 1) {
                        $library = new Pay2allpayout();
                        $response = $library->accountVerify($mobile_number, $bank_id, $ifsc_code, $account_number, $insert_id, $api_id);
                        $status_id = $response['status_id'];
                        $message = $response['message'];
                        $name = $response['name'];
                    } else {
                        $status_id = 2;
                        $message = "You don't have permission to access this page";
                        $name = '';
                    }
                    if ($status_id == 1) {
                        Report::where('id', $insert_id)->update(['status_id' => 1, 'txnid' => $name]);
                        Accountvalidate::insertGetId([
                            'account_number' => $account_number,
                            'ifsc_code' => $ifsc_code,
                            'beneficiary_name' => $name,
                            'created_at' => $ctime,
                            'status_id' => 1,
                            'api_id' => $api_id,
                        ]);
                        return Response()->json(['status' => 'success', 'beneficiary_name' => $name, 'message' => 'Verifyed form vendor database']);
                    } elseif ($status_id == 2) {
                        Balance::where('user_id', $user_id)->increment('aeps_balance', $amount);
                        $balance = Balance::where('user_id', $user_id)->first();
                        $user_balance = $balance->aeps_balance;
                        Report::where('id', $insert_id)->update(['status_id' => 2, 'profit' => 0, 'total_balance' => $user_balance]);
                        return Response()->json(['status' => 'failure', 'message' => $message]);
                    } else {
                        return Response()->json(['status' => 'success', 'beneficiary_name' => '', 'message' => '']);
                    }
                } else {
                    return Response()->json(['status' => 'failure', 'message' => 'Your aeps balance is low']);
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => $userdetails->reason]);
            }
        } else {
            $message = ($userdetails->company->server_down == 1) ? 'Service not active!' : $userdetails->company->server_message;
            return Response()->json(['status' => 'failure', 'message' => $message]);
        }
    }

    function add_beneficiary(Request $request)
    {
        $rules = array(
            'bank_id' => 'required|exists:masterbanks,bank_id',
            'ifsc_code' => 'required',
            'account_number' => 'required',
            'mobile_number' => 'required',
            'beneficiary_name' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $bank_id = $request->bank_id;
        $ifsc_code = $request->ifsc_code;
        $account_number = $request->account_number;
        $mobile_number = $request->mobile_number;
        $beneficiary_name = $request->beneficiary_name;
        $checkbeneficiary = Payoutbeneficiary::where('mobile_number', $mobile_number)->where('account_number', $account_number)->whereIn('status_id', [1, 2, 3])->first();
        if ($checkbeneficiary) {
            return Response()->json(['status' => 'failure', 'message' => 'beneficiary already added']);
        } else {
            $countbene = Payoutbeneficiary::where('mobile_number', $mobile_number)->whereNotIn('status_id', [0])->count();
            if ($countbene <= 500) {
                $now = new \DateTime();
                $ctime = $now->format('Y-m-d H:i:s');
                Payoutbeneficiary::insertGetId([
                    'user_id' => Auth::id(),
                    'mobile_number' => $mobile_number,
                    'account_number' => $account_number,
                    'holder_name' => $beneficiary_name,
                    'bank_id' => $bank_id,
                    'ifsc_code' => $ifsc_code,
                    'created_at' => $ctime,
                    'status_id' => 1,
                ]);
                return Response()->json(['status' => 'success', 'message' => 'success']);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'only 500 account u can add']);
            }
        }
    }

    function delete_beneficiary(Request $request)
    {
        $rules = array(
            'recipient_id' => 'required',
            'mobile_number' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $recipient_id = $request->recipient_id;
        $mobile_number = $request->mobile_number;
        Payoutbeneficiary::where('id', $recipient_id)->where('mobile_number', $mobile_number)->update(['status_id' => 0]);
        return Response()->json(['status' => 'success', 'message' => 'Beneficiary Successfully Deleted']);
    }

    function transfer_now(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|exists:payoutbeneficiaries,mobile_number',
            'account_number' => 'required|exists:payoutbeneficiaries,account_number',
            'holder_name' => 'required',
            'bank_name' => 'required',
            'ifsc_code' => 'required',
            'recipient_id' => 'required|exists:payoutbeneficiaries,id',
            'amount' => 'required',
            'password' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'dupplicate_transaction' => 'required|unique:check_duplicates',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        DB::table('check_duplicates')->insert(['dupplicate_transaction' => $request->dupplicate_transaction]);
        $mobile_number = $request->mobile_number;
        $account_number = $request->account_number;
        $holder_name = $request->holder_name;
        $bank_name = $request->bank_name;
        $ifsc_code = $request->ifsc_code;
        $recipient_id = $request->recipient_id;
        $amount = $request->amount;
        $password = $request->password;
        $user_id = Auth::id();
        $mode = "WEB";
        $request_ip = request()->ip();
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $locationrestrictionsLibrary = new LocationRestrictionsLibrary();
        $isLoginValid = $locationrestrictionsLibrary->loginRestrictions($user_id, $latitude, $longitude);
        if ($isLoginValid == 0) {
            $kilometer = Auth::User()->company->login_restrictions_km;
            return Response()->json(['status' => 'failure', 'message' => "You must be within $kilometer kilometer to access this service."]);
        }
        return $this->transfer_now_middle($mobile_number, $account_number, $holder_name, $bank_name, $ifsc_code, $recipient_id, $amount, $password, $user_id, $mode, $request_ip, $latitude, $longitude);
    }

    function transfer_now_app(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|exists:payoutbeneficiaries,mobile_number',
            'account_number' => 'required|exists:payoutbeneficiaries,account_number',
            'holder_name' => 'required',
            'bank_name' => 'required',
            'ifsc_code' => 'required',
            'recipient_id' => 'required|exists:payoutbeneficiaries,id',
            'amount' => 'required',
            'password' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'dupplicate_transaction' => 'required|unique:check_duplicates',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        DB::table('check_duplicates')->insert(['dupplicate_transaction' => $request->dupplicate_transaction]);
        $mobile_number = $request->mobile_number;
        $account_number = $request->account_number;
        $holder_name = $request->holder_name;
        $bank_name = $request->bank_name;
        $ifsc_code = $request->ifsc_code;
        $recipient_id = $request->recipient_id;
        $amount = $request->amount;
        $password = $request->password;
        $user_id = Auth::id();
        $mode = "APP";
        $request_ip = request()->ip();
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $locationrestrictionsLibrary = new LocationRestrictionsLibrary();
        $isLoginValid = $locationrestrictionsLibrary->loginRestrictions($user_id, $latitude, $longitude);
        if ($isLoginValid == 0) {
            $kilometer = Auth::User()->company->login_restrictions_km;
            return Response()->json(['status' => 'failure', 'message' => "You must be within $kilometer kilometer to access this service."]);
        }
        return $this->transfer_now_middle($mobile_number, $account_number, $holder_name, $bank_name, $ifsc_code, $recipient_id, $amount, $password, $user_id, $mode, $request_ip, $latitude, $longitude);
    }


    function transfer_now_middle($mobile_number, $account_number, $holder_name, $bank_name, $ifsc_code, $recipient_id, $amount, $password, $user_id, $mode, $request_ip, $latitude, $longitude)
    {
        $provider_id = 324;
        if ($amount >= 9 && $amount <= 200000) {
            $userdetails = User::find($user_id);
            $library = new BasicLibrary();
            $activeService = $library->getActiveService($provider_id, $user_id);
            $serviceStatus = $activeService['status_id'];
            if ($userdetails->company->server_down == 1 && $serviceStatus == 1) {
                if ($userdetails->active == 1) {
                    $now = new \DateTime();
                    $ctime = $now->format('Y-m-d H:i:s');
                    $current_password = $userdetails->password;
                    if (Hash::check($password, $current_password)) {
                        $scheme_id = $userdetails->scheme_id;
                        $company_id = $userdetails->company_id;
                        $library = new GetcommissionLibrary();
                        $commission = $library->get_commission($scheme_id, $provider_id, $amount);
                        $retailer = $commission['retailer'];
                        $d = $commission['distributor'];
                        $sd = $commission['sdistributor'];
                        $st = $commission['sales_team'];
                        $rf = $commission['referral'];
                        $opening_balance = $userdetails->balance->user_balance;
                        $sumamount = $amount + $userdetails->lock_amount + $retailer;
                        if ($opening_balance >= $sumamount && $sumamount >= 9) {
                            $decrementAmount = $amount + $retailer;
                            Balance::where('user_id', $user_id)->decrement('user_balance', $decrementAmount);
                            $balance = Balance::where('user_id', $user_id)->first();
                            $user_balance = $balance->user_balance;
                            $description = "Payout to  $account_number";
                            $api_id = $userdetails->company->payout_route;
                            $banktransferswitching = Banktransferswitching::where('minimum_amount', '<=', $amount)
                                ->where('maximum_amount', '>=', $amount)
                                ->where(function ($query) use ($user_id) {
                                    $query->where('user_id', $user_id)
                                        ->orWhere('user_id', 0) // Handle case where user_id is 0
                                        ->orWhereNull('user_id'); // Handle case where user_id might not be set
                                })->first();
                            if ($banktransferswitching) {
                                $api_id = $banktransferswitching->api_id ?? $api_id;
                            }
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
                                'ip_address' => $request_ip,
                                'description' => $description,
                                'opening_balance' => $opening_balance,
                                'total_balance' => $user_balance,
                                'credit_by' => $user_id,
                                'wallet_type' => 1,
                                'latitude' => $latitude,
                                'longitude' => $longitude,
                            ]);
                            Report::where('id', $insert_id)->update(['client_id' => $insert_id]);
                            $vender_id = 1;
                            $response = Self::callApi($user_id, $mobile_number, $amount, $holder_name, $account_number, $ifsc_code, $insert_id, $vender_id, $api_id, $latitude, $longitude);
                            $status_id = $response['status_id'];
                            $txnid = $response['txnid'];
                            $payid = $response['payid'];
                            if ($status_id == 0 || $status_id == 1) {
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
                                Report::where('id', $insert_id)->update(['status_id' => 2, 'reason' => $txnid, 'txnid' => $txnid, 'payid' => $payid, 'total_balance' => $user_balance]);
                                return ['status' => 'failure', 'message' => 'Transaction failed!', 'utr' => '', 'payid' => $insert_id];
                            } else {
                                Report::where('id', $insert_id)->update(['payid' => $payid]);
                                return ['status' => 'success', 'message' => 'Transaction process!', 'utr' => '', 'payid' => $insert_id];
                            }
                        } else {
                            return Response()->json(['status' => 'failure', 'message' => 'Your balance is low']);
                        }
                    } else {
                        return Response()->json(['status' => 'failure', 'message' => 'password is wrong']);
                    }
                } else {
                    return Response()->json(['status' => 'failure', 'message' => "$userdetails->reason, or Payout not activate"]);
                }
            } else {
                $message = ($userdetails->company->server_down == 1) ? 'Service not active!' : $userdetails->company->server_message;
                return Response()->json(['status' => 'failure', 'message' => $message]);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Amount Should be Minimum Rs 10 Or Maximum 200000']);
        }
    }


    function callApi($user_id, $mobile_number, $amount, $holder_name, $account_number, $ifsc_code, $insert_id, $vender_id, $api_id, $latitude, $longitude)
    {
        if ($api_id == 2) {
            return Self::callmnppayApi($user_id, $mobile_number, $amount, $holder_name, $account_number, $ifsc_code, $insert_id, $vender_id, $api_id, $latitude, $longitude);
        } elseif ($api_id == 4) {
            $library = new AccosisLibrary();
            return $library->impsTransfer($user_id, $mobile_number, $amount, $holder_name, $account_number, $ifsc_code, $insert_id, $vender_id, $api_id, $latitude, $longitude);
        } elseif ($api_id == 5) {
            $library = new PaywizeLibrary();
            return $library->transferNow($user_id, $mobile_number, $amount, $holder_name, $account_number, $ifsc_code, $insert_id);
        } elseif ($api_id == 6) {
            $library = new SprezapayLibrary();
            return $library->transferNow($user_id, $mobile_number, $amount, $holder_name, $account_number, $ifsc_code, $insert_id);
        } elseif ($api_id == 7) {
            $library = new PockethubLibrary();
            return $library->transferNow($user_id, $mobile_number, $amount, $holder_name, $account_number, $ifsc_code, $insert_id);
        }elseif ($api_id == 10) {
            $library = new PunjikendraLibrary();
            return $library->transferNow($user_id, $mobile_number, $amount, $holder_name, $account_number, $ifsc_code, $insert_id);
        } elseif ($api_id == 11) {
            $library = new RazorpaypayoutLibrary();
            return $library->transferNow($user_id, $mobile_number, $amount, $holder_name, $account_number, $ifsc_code, $insert_id);
        }elseif ($api_id == 12){
            $library = new VtransactLibrary();
            return $library->transferNow($user_id, $mobile_number, $amount, $holder_name, $account_number, $ifsc_code, $insert_id);
        }elseif ($api_id == 14){
            $library = new SafepPayLibrary();
            return $library->transferNow($user_id, $mobile_number, $amount, $holder_name, $account_number, $ifsc_code, $insert_id);
        }elseif ($api_id == 15){
            $library = new ZigPayLibrary();
            return $library->transferNow($user_id, $mobile_number, $amount, $holder_name, $account_number, $ifsc_code, $insert_id);
        }
        return ['status_id' => 2, 'txnid' => '', 'payid' => ''];
    }

    function callmnppayApi($user_id, $mobile_number, $amount, $holder_name, $account_number, $ifsc_code, $insert_id, $vender_id, $api_id, $latitude, $longitude)
    {
        $userDetails = User::find($user_id);
        $apis = Api::find($api_id);
        $url = 'https://mnppay.in/api/payout/v1/transfer-now';
        $parameters = array(
            'api_token' => $apis->api_key ?? '',
            'mobile_number' => $mobile_number,
            'email' => $userDetails->email,
            'beneficiary_name' => $holder_name,
            'ifsc_code' => $ifsc_code,
            'account_number' => $account_number,
            'amount' => $amount,
            'channel_id' => 2,
            'client_id' => $insert_id,
        );
        $method = 'POST';
        $header = ["Accept:application/json"];
        $response = Helpers::pay_curl_post($url, $header, $parameters, $method);
        Apiresponse::insertGetId(['message' => $response, 'api_type' => $api_id, 'report_id' => $insert_id, 'request_message' => $url . '?' . json_encode($parameters)]);
        $res = json_decode($response);
        $status = $res->status ?? 'pending';
        if ($status == 'success') {
            return ['status_id' => 1, 'txnid' => $res->utr ?? '', 'payid' => ''];
        } elseif ($status == 'failure') {
            return ['status_id' => 2, 'txnid' => '', 'payid' => ''];
        } else {
            return ['status_id' => 3, 'txnid' => '', 'payid' => ''];
        }
    }


}
