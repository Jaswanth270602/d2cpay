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
use DB;
use Helpers;
use App\Models\Sitesetting;
use App\Library\BasicLibrary;
use App\Library\RechargeLibrary;
use App\Library\GetcommissionLibrary;
use App\Library\Commission_increment;
use App\Library\SmsLibrary;

class WalletController extends Controller {


    public function __construct()   {
        $this->company_id = Helpers::company_id()->id;
        $companies = Helpers::company_id();
        $this->company_id = $companies->id;
        $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
        if ($sitesettings){
            $this->brand_name = $sitesettings->brand_name;
        }else{
            $this->brand_name = "";
        }
    }


    function verify_user (Request $request){
        $rules = array(
            'mobile_number' => 'required|exists:users,mobile|digits:10',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'message' => 'validation errors', 'errors' => $validator->getMessageBag()->toArray()]);
        }

        $mobile_number = $request->mobile_number;
        if (Auth::User()->mobile == $mobile_number){
            return Response()->json(['status' => 'failure', 'message' => 'Soory You have No Permission']);
        }
        $userdetails = User::where('mobile', $mobile_number)->first();
        if ($userdetails){
            $details = array(
                'child_id' => $userdetails->id,
                'name' => $userdetails->name.' '. $userdetails->last_name,
                'number' => $mobile_number,
            );
            return Response()->json([
                'status' => 'success',
                'message' => 'success',
                'details' => $details,
            ]);
        }else{
            return Response()->json(['status' => 'failure', 'message' => 'user does not exist']);
        }
    }

    function transfer_now (Request $request){
        $rules = array(
            'mobile_number' => 'required|exists:users,mobile|digits:10',
            'amount' => 'required',
        
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'message' => 'validation errors', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $amount = $request->amount;
        $remark = $request->remark;
        if (Auth::User()->mobile == $mobile_number){
            return Response()->json(['status' => 'failure', 'message' => 'Soory You have No Permission']);
        }
        $childdetails = User::where('mobile', $mobile_number)->first();
        if ($childdetails){
            $user_id = Auth::id();
            $child_id = $childdetails->id;
            return $this->transfer_now_middle($user_id, $child_id, $amount, $remark);
        }else{
            return Response()->json(['status' => 'failure', 'message' => 'user does not exist']);
        }
    }


    function transfer_now_middle ($user_id, $child_id, $amount, $remark){
        $userdetails = User::find($user_id);
        if ($userdetails->active == 1){
            $childdetails = User::find($child_id);
            $opening_balance = $userdetails->balance->user_balance;
            $sumamount = $amount + $userdetails->lock_amount + $userdetails->balance->lien_amount;
            if ($opening_balance >= $sumamount && $sumamount >= 9) {
                $now = new \DateTime();
                $ctime = $now->format('Y-m-d H:i:s');
                Balance::where('user_id', $user_id)->decrement('user_balance', $amount);
                $balance = Balance::where('user_id', $user_id)->first();
                $user_balance = $balance->user_balance;
                $provider_id = 325;
                $request_ip =  request()->ip();
                $description = "Tansfer to  $childdetails->name";
                $insert_id = Report::insertGetId([
                    'number' => $childdetails->mobile,
                    'provider_id' => $provider_id,
                    'amount' => $amount,
                    'api_id' => 0,
                    'status_id' => 7,
                    'created_at' => $ctime,
                    'user_id' => $user_id,
                    'profit' => 0,
                    'mode' => "WEB",
                    'txnid' => $remark,
                    'ip_address' => $request_ip,
                    'description' => $description,
                    'opening_balance' => $opening_balance,
                    'total_balance' => $user_balance,
                    'credit_by' => $child_id,
                    'wallet_type' => 1,
                ]);
                $message = "Dear $userdetails->name Your Wallet Debited With Amount $amount Your Current balance is $user_balance $this->brand_name";
                $template_id = 4;
                $library = new SmsLibrary();
                $library->send_sms($userdetails->mobile, $message, $template_id);

                // child update
                $child_opening_balance = $childdetails->balance->user_balance;
                Balance::where('user_id', $child_id)->increment('user_balance', $amount);
                Balance::where('user_id', $child_id)->update(['balance_alert' => 1]);
                $childbalance = Balance::where('user_id', $child_id)->first();
                $child_balance = $childbalance->user_balance;

                $description = "Transfer by $userdetails->name";
                $insert_id = Report::insertGetId([
                    'number' => $userdetails->mobile,
                    'provider_id' => $provider_id,
                    'amount' => $amount,
                    'api_id' => 0,
                    'status_id' => 6,
                    'created_at' => $ctime,
                    'user_id' => $child_id,
                    'profit' => 0,
                    'mode' => "WEB",
                    'txnid' => $remark,
                    'ip_address' => $request_ip,
                    'description' => $description,
                    'opening_balance' => $child_opening_balance,
                    'total_balance' => $child_balance,
                    'credit_by' => $user_id,
                    'wallet_type' => 1,
                ]);

                $message = "Dear $childdetails->name Your Wallet Credited With Amount $amount Your Current balance is $child_balance $this->brand_name";
                $template_id = 5;
                $library = new SmsLibrary();
                $library->send_sms($childdetails->mobile, $message, $template_id);
                return Response()->json(['status' => 'success', 'message' => 'Balance successfully trasnfered']);
            }else{
                return Response()->json(['status' => 'failure', 'message' => 'Your balance is low kindly refill your wallet']);
            }
        }else{
            return Response()->json(['status' => 'failure', 'message' => $userdetails->reason]);
        }
    }


}
