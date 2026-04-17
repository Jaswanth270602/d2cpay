<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use Helpers;
use App\Models\Api;
use App\Models\Vendorpayment;
use App\Models\Masterbank;
use DB;
use App\Models\Purchase;
use App\Models\User;
use App\Models\Balance;
use App\Models\Report;
use App\Models\Sitesetting;
use App\Models\Apiresponse;
use App\Library\SmsLibrary;

class VendorpaymentController extends Controller
{

    public function __construct()
    {
        $this->company_id = Helpers::company_id()->id;
        $dt = Helpers::company_id();
        $this->company_id = $dt->id;
        $api = Api::where('vender_id', 6)->first();
        if ($api) {
            $this->key = 'Bearer ' . $api->api_key;
            $this->api_id = $api->id;
        }
        $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
        if ($sitesettings) {
            $this->brand_name = $sitesettings->brand_name;
            $this->backend_template_id = $sitesettings->backend_template_id;
        } else {
            $this->brand_name = "";
            $this->backend_template_id = 1;
        }
    }

    function welcome(Request $request)
    {
        if (Auth::User()->company->vendor_payment == 1 && Auth::User()->role_id == 1) {
            $data = array(
                'page_title' => 'Vendor Payment',
            );
            $apis = Api::where('company_id', Auth::User()->company_id)->get();
            $vendorpayments = Vendorpayment::where('user_id', Auth::id())->get();
            $masterbanks = Masterbank::where('status_id', 1)->get();
            if ($this->backend_template_id == 1) {
                return view('admin.vendor-payment.welcome', compact('apis', 'vendorpayments', 'masterbanks'))->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.vendor-payment.welcome', compact('apis', 'vendorpayments', 'masterbanks'))->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.vendor-payment.welcome', compact('apis', 'vendorpayments', 'masterbanks'))->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.vendor-payment.welcome', compact('apis', 'vendorpayments', 'masterbanks'))->with($data);
            } else {
                return redirect()->back();
            }
        } else {
            return redirect()->back();
        }
    }

    function add_api(Request $request)
    {
        if (Auth::User()->company->vendor_payment == 1 && Auth::User()->role_id == 1) {
            $rules = array(
                'api_id' => 'required|exists:apis,id|unique:vendorpayments',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            $api_id = $request->api_id;
            Vendorpayment::insert([
                'user_id' => Auth::id(),
                'api_id' => $api_id,
                'created_at' => $ctime,
                'status_id' => 1,
            ]);
            return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function view_beneficiary(Request $request)
    {
        if (Auth::User()->company->vendor_payment == 1 && Auth::User()->role_id == 1) {
            $rules = array(
                'id' => 'required|exists:vendorpayments,id',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
            }
            $id = $request->id;
            $vendorpayments = Vendorpayment::find($id);
            if ($vendorpayments) {
                $details = array(
                    'id' => $vendorpayments->id,
                    'beneficiary_name' => $vendorpayments->beneficiary_name,
                    'account_number' => $vendorpayments->account_number,
                    'ifsc_code' => $vendorpayments->ifsc_code,
                    'masterbank_id' => $vendorpayments->masterbank_id,
                    'status_id' => $vendorpayments->status_id,
                );
                return Response()->json(['status' => 'success', 'message' => 'Successful..!', 'details' => $details]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Record not found']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function add_beneficiary(Request $request)
    {
        if (Auth::User()->company->vendor_payment == 1 && Auth::User()->role_id == 1) {
            $rules = array(
                'beneficiary_id' => 'required|exists:vendorpayments,id',
                'beneficiary_name' => 'required',
                'account_number' => 'required',
                'confirm_account_number' => 'required_with:account_number|same:account_number',
                'ifsc_code' => 'required',
                'masterbank_id' => 'required|exists:masterbanks,id',
                'status_id' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $beneficiary_id = $request->beneficiary_id;
            $beneficiary_name = $request->beneficiary_name;
            $account_number = $request->account_number;
            $ifsc_code = $request->ifsc_code;
            $masterbank_id = $request->masterbank_id;
            $status_id = $request->status_id;
            Vendorpayment::where('id', $beneficiary_id)->update([
                'beneficiary_name' => $beneficiary_name,
                'account_number' => $account_number,
                'ifsc_code' => $ifsc_code,
                'masterbank_id' => $masterbank_id,
                'status_id' => $status_id,
            ]);
            return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function delete_beneficiary(Request $request)
    {
        if (Auth::User()->company->vendor_payment == 1 && Auth::User()->role_id == 1) {
            $rules = array(
                'id' => 'required|exists:vendorpayments,id',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
            }
            $id = $request->id;
            Vendorpayment::where('id', $id)->delete();
            return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function view_transfer_details(Request $request)
    {
        if (Auth::User()->company->vendor_payment == 1 && Auth::User()->role_id == 1) {
            $rules = array(
                'id' => 'required|exists:vendorpayments,id',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
            }
            $id = $request->id;
            $vendorpayments = Vendorpayment::find($id);
            if ($vendorpayments) {
                $api_name = ($vendorpayments->api_id == 0) ? 'Unknown Api' : $vendorpayments->api->api_name;
                $details = array(
                    'id' => $vendorpayments->id,
                    'beneficiary_name' => $vendorpayments->beneficiary_name,
                    'account_number' => $vendorpayments->account_number,
                    'ifsc_code' => $vendorpayments->ifsc_code,
                    'bank_name' => ($vendorpayments->masterbank_id == 0) ? 'Unknown Bank' : $vendorpayments->masterbank->bank_name,
                    'api_name' => $api_name,
                );
                $userDetails = User::find(Auth::id());
                $otp = mt_rand(100000, 999999);
                User::where('id', $userDetails->id)->update(['login_otp' => $otp]);
                $message = "Your One Time Password is $otp for Request vendor transfer : $api_name. Please do not share the OTP with anyone for security reasons - $this->brand_name";
                $template_id = 17;
                $library = new SmsLibrary();
                $library->send_sms($userDetails->mobile, $message, $template_id);
                return Response()->json(['status' => 'success', 'message' => 'Successful..!', 'details' => $details]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Record not found']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function transfer_now(Request $request)
    {
        if (Auth::User()->company->vendor_payment == 1 && Auth::User()->role_id == 1) {
            $rules = array(
                'id' => 'required|exists:vendorpayments,id',
                'beneficiary_name' => 'required',
                'account_number' => 'required',
                'ifsc_code' => 'required',
                'bank_name' => 'required',
                'amount' => "required|regex:/^\d+(\.\d{1,2})?$/",
                'transaction_pin' => 'required|digits:6',
                'otp' => 'required|digits:6',
                'payment_mode' => 'required',
                'dupplicate_transaction' => 'required|unique:check_duplicates'
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $id = $request->id;
            $beneficiary_name = $request->beneficiary_name;
            $account_number = $request->account_number;
            $ifsc_code = $request->ifsc_code;
            $bank_name = $request->bank_name;
            $amount = $request->amount;
            $transaction_pin = $request->transaction_pin;
            $payment_mode = $request->payment_mode;
            $user_id = Auth::id();
            if ($transaction_pin == Auth::User()->transaction_pin) {
                if ($request->otp == Auth::User()->login_otp) {
                    $vendorpayments = Vendorpayment::where('id', $id)->where('beneficiary_name', $beneficiary_name)->where('account_number', $account_number)->where('ifsc_code', $ifsc_code)->where('status_id', 1)->first();
                    if ($vendorpayments) {
                        $masterbank_id = $vendorpayments->masterbank_id;
                        DB::table('check_duplicates')->insert(['dupplicate_transaction' => $request->dupplicate_transaction]);
                        return Self::transfer_now_middle($id, $beneficiary_name, $account_number, $ifsc_code, $bank_name, $amount, $payment_mode, $user_id, $masterbank_id);
                    } else {
                        return Response()->json(['status' => 'failure', 'message' => 'Bank details not found or Disabled']);
                    }
                } else {
                    return Response()->json(['status' => 'failure', 'message' => 'Invalid One Time Password']);
                }


            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Invalid Transaction Pin']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function transfer_now_middle($id, $beneficiary_name, $account_number, $ifsc_code, $bank_name, $amount, $payment_mode, $user_id, $masterbank_id)
    {
        $vendorpayments = Vendorpayment::where('id', $id)->first();

        if ($payment_mode == 1) {
            $startLimit = 100;
            $endLimit = 1000000;
        } elseif ($payment_mode == 2) {
            $startLimit = 100;
            $endLimit = 200000;
        } elseif ($payment_mode == 7) {
            $startLimit = 200000;
            $endLimit = 1000000;
        }
        if ($amount >= $startLimit && $amount <= $endLimit) {
            $userDetails = User::find($user_id);
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            $purchase_id = Purchase::insertGetId([
                'user_id' => Auth::id(),
                'api_id' => $vendorpayments->api_id,
                'masterbank_id' => $masterbank_id,
                'amount' => $amount,
                'created_at' => $ctime,
                'purchase_type' => 'Vendor Payment',
                'status_id' => 3,
            ]);
            $url = "https://partners.connect21.in/api/payout/v2/transfer";
            $api_request_parameters = array(
                'mobile_number' => $userDetails->mobile,
                'amount' => $amount,
                'beneficiary_name' => $beneficiary_name,
                'account_number' => $account_number,
                'ifsc' => $ifsc_code,
                'channel_id' => $payment_mode,
                'provider_id' => 143,
                'client_id' => $purchase_id,
                'wallet_id' => 4,
            );
            $method = 'POST';
            $header = ["Accept:application/json", "Authorization:" . $this->key];
            $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
            Apiresponse::insertGetId(['message' => $response, 'api_type' => $this->api_id, 'report_id' => $purchase_id, 'request_message' => json_encode($api_request_parameters)]);
            $res = json_decode($response);
            if (array_key_exists("status_id", $res)) {
                $status = $res->status_id;
                if ($status == 0 || $status == 1) {
                    $utr = $res->utr;
                    Purchase::where('id', $purchase_id)->update(['status_id' => 1, 'utr' => $utr]);
                    $balance = Balance::where('user_id', $user_id)->first();
                    $opening_balance = $balance->user_balance;
                    Balance::where('user_id', Auth::id())->increment('user_balance', $amount);
                    $balance = Balance::where('user_id', $user_id)->first();
                    $user_balance = $balance->user_balance;
                    $provider_id = 151;
                    $request_ip = request()->ip();
                    $description = "Purchase Balance  $amount";
                    $insert_id = Report::insertGetId([
                        'number' => Auth::User()->mobile,
                        'provider_id' => $provider_id,
                        'amount' => $amount,
                        'api_id' => $this->api_id,
                        'status_id' => 6,
                        'created_at' => $ctime,
                        'user_id' => $user_id,
                        'profit' => 0,
                        'mode' => "WEB",
                        'txnid' => $utr,
                        'ip_address' => $request_ip,
                        'description' => $description,
                        'opening_balance' => $opening_balance,
                        'total_balance' => $user_balance,
                        'credit_by' => $user_id,
                        'wallet_type' => 1,
                    ]);
                    return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
                } elseif ($status == 2) {
                    Purchase::where('id', $purchase_id)->update(['status_id' => 2]);
                    return Response()->json(['status' => 'failure', 'message' => $res->message]);
                } else {
                    return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => "Response not getting form bank side please check bank statement"]);
            }

        } else {
            return Response()->json(['status' => 'failure', 'message' => "Amount Should be Minimum Rs $startLimit Or Maximum $endLimit"]);
        }
    }
}
