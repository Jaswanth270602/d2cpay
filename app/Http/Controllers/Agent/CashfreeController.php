<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use App\Gatewayorder;
use App\Provider;
use App\User;
use App\Balance;
use App\Report;
use App\Sitesetting;
use App\Library\SmsLibrary;
use Helpers;
use Str;
use App\Apiresponse;
use App\Cashfreegateway;
use App\Library\GetcommissionLibrary;


class CashfreeController extends Controller
{
    public function __construct()
    {
        $this->company_id = Helpers::company_id()->id;
        $companies = Helpers::company_id();
        $this->company_id = $companies->id;
        $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
        if ($sitesettings) {
            $this->brand_name = $sitesettings->brand_name;
        } else {
            $this->brand_name = "";
        }
        $cashfreegateways = Cashfreegateway::find(1);
        $this->app_id = (empty($cashfreegateways) ? '' : $cashfreegateways->app_id);
        $this->secret_key = (empty($cashfreegateways) ? '' : $cashfreegateways->secret_key);
        $this->base_url = (empty($cashfreegateways) ? '' : $cashfreegateways->base_url);
        $this->status_id = (empty($cashfreegateways) ? '' : $cashfreegateways->status_id);
        $this->min_amount = (empty($cashfreegateways) ? '' : $cashfreegateways->min_amount);
        $this->max_amount = (empty($cashfreegateways) ? '' : $cashfreegateways->max_amount);
        $this->provider_id = 326;
    }

    function welcome(Request $request)
    {
        if (Auth::User()->company->cashfree == 1 && Auth::User()->profile->cashfree == 1) {
            $data = array('page_title' => 'Add Money');
            $gatewayorders = Gatewayorder::where('user_id', Auth::id())->orderBy('id', 'DESC')->paginate(50);
            return view('agent.add-money.cashfree', compact('gatewayorders'))->with($data);
        } else {
            return redirect()->back();
        }
    }

    function create_order(Request $request)
    {
        if (Auth::User()->company->cashfree == 1 && Auth::User()->profile->cashfree == 1 && $this->status_id == 1) {
            $rules = array(
                'amount' => 'required|numeric|between:' . $this->min_amount . ',' . $this->max_amount . '',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $amount = $request->amount;
            $user_id = Auth::id();
            $mode = 'WEB';
            return Self::createOrderMiddle($amount, $user_id, $mode);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'payment gateway disable']);
        }
    }

    function createOrderApp(Request $request)
    {
        if (Auth::User()->company->cashfree == 1 && Auth::User()->profile->cashfree == 1 && $this->status_id == 1) {
            $rules = array(
                'amount' => 'required|numeric|between:' . $this->min_amount . ',' . $this->max_amount . '',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $amount = $request->amount;
            $user_id = Auth::id();
            $mode = 'APP';
            return Self::createOrderMiddle($amount, $user_id, $mode);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'payment gateway disable']);
        }
    }

    function createOrderMiddle($amount, $user_id, $mode)
    {
        $now = new \DateTime();
        $created_at = $now->format('Y-m-d H:i:s');
        $orderId = Gatewayorder::insertGetId([
            'user_id' => $user_id,
            'purpose' => 'Add Money',
            'amount' => $amount,
            'email' => Auth::User()->email,
            'ip_address' => request()->ip(),
            'created_at' => $created_at,
            'status_id' => 3,
            'mode' => $mode,
        ]);
        $order_id = 'order_' . $orderId;
        $userDetails = User::find($user_id);
        $returnUrl = url('agent/add-money/v1/return-url') . '?cf_id={order_id}&cf_token={order_token}';
        $notifyUrl = url('api/call-back/cashfree-gateway');
        $url = $this->base_url . "pg/orders";
        $bodyData = json_encode(array(
            'order_id' => "$order_id",
            'order_amount' => $amount,
            'order_currency' => 'INR',
            'customer_details' => array(
                'customer_id' => "$user_id",
                'customer_email' => $userDetails->email,
                'customer_phone' => $userDetails->mobile,
            ),
            'order_meta' => array(
                'return_url' => $returnUrl,
                'notify_url' => $notifyUrl,
                'payment_methods' => 'cc,dc,ccc,ppc,nb,upi',
            ),
        ));
        $headers = array(
            'Content-Type: application/json',
            'x-api-version: 2021-05-21',
            "x-client-id: $this->app_id",
            "x-client-secret: $this->secret_key"
        );

        $response = Self::callCashfreeOrderApi($url, $bodyData, $headers);
        if ($response["code"] == 200) {
            Gatewayorder::where('id', $orderId)->update(['callbackLogs' => $response["data"]]);
            $res = $response["data"];
            Gatewayorder::where('id', $orderId)->update(['order_token' => $res['order_token'], 'cf_order_id' => $res['cf_order_id']]);
            $token = Self::createTokenForApp($amount, $order_id, $headers);
            return Response([
                'status' => 'success',
                'message' => 'Successful..!',
                'payment_link' => $res['payment_link'],
                'orderId' => $order_id,
                'app_id' => $this->app_id,
                'order_token' => $token,
                'mode' => 'PROD', // TEST / PROD
                'notify_url' => $notifyUrl,
            ]);
        } else {
            $res = $response["data"];
            Gatewayorder::where('id', $orderId)->update(['status_id' => 2, 'remark' => $res['message']]);
            return Response(['status' => 'failure', 'message' => 'Something went wrong with order creation! \n']);
        }
    }

    function createTokenForApp($amount, $order_id, $headers)
    {
        $url = $this->base_url . 'api/v2/cftoken/order';
        $bodyData = json_encode(array('orderId' => $order_id, 'orderAmount' => $amount, 'orderCurrency' => 'INR'));
        $response = Self::callCashfreeOrderApi($url, $bodyData, $headers);
        if ($response["code"] == 200) {
            $res = $response["data"];
            $token = $res['cftoken'];
        } else {
            $token = '';
        }
        return $token;

    }

    function callCashfreeOrderApi($url, $bodyData, $header)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_VERBOSE, 1);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $bodyData);
        $resp = curl_exec($curl);
        if ($resp === false) {
            throw new \Exception("Unable to post to cashfree");
        }
        $info = curl_getinfo($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $responseJson = json_decode($resp, true);
        curl_close($curl);
        return array("code" => $httpCode, "data" => $responseJson);
    }

    function cashfree_callback(Request $request)
    {
        Apiresponse::insertGetId(['message' => $request, 'api_type' => 1, 'request_message' => 'paymentGateway', 'ip_address' => request()->ip()]);
        $data = $request->data;
        $orderId = $data['order']['order_id'];
        $orderAmount = $data['order']['order_amount'];
        $referenceId = $data['payment']['bank_reference'];
        $txStatus = $data['payment']['payment_status'];
        $paymentMode = $data['payment']['payment_group'];
        $txMsg = "Payment";
        $txTime = $data['payment']['payment_time'];
        if ($txStatus == 'SUCCESS') {
            $provider_id = $this->provider_id;
            $exploadOrderId = explode('_', $orderId);
            $gatewayorder_id = $exploadOrderId[1];
            $gatewayorders = Gatewayorder::where('id', $gatewayorder_id)->where('status_id', 3)->first();
            if ($gatewayorders) {
                $user_id = $gatewayorders->user_id;
                $amount = $gatewayorders->amount;
                $userDetails = User::find($user_id);
                $opening_balance = $userDetails->balance->user_balance;
                // get payment method
                $payment_group = $data['payment']['payment_group'];
                $methodCode = ($payment_group == 'wallet') ? $data['payment']['payment_method']['app']['channel'] : $payment_group;
                $library = new GetcommissionLibrary();
                $commission = $library->getGatewayCharges($methodCode, $amount);
                $retailer = $commission['retailer'];

                $incrementAmount = $amount - $retailer;
                Balance::where('user_id', $user_id)->increment('user_balance', $incrementAmount);
                $balance = Balance::where('user_id', $user_id)->first();
                $user_balance = $balance->user_balance;
                $description = "Add Money $userDetails->name ($paymentMode)";
                $now = new \DateTime();
                $ctime = $now->format('Y-m-d H:i:s');
                $insert_id = Report::insertGetId([
                    'number' => $userDetails->mobile,
                    'provider_id' => $provider_id,
                    'amount' => $amount,
                    'api_id' => 0,
                    'status_id' => 6,
                    'created_at' => $ctime,
                    'user_id' => $user_id,
                    'profit' => '-' . $retailer,
                    'mode' => $gatewayorders->mode,
                    'txnid' => $referenceId,
                    'ip_address' => $gatewayorders->ip_address,
                    'description' => $description,
                    'opening_balance' => $opening_balance,
                    'total_balance' => $user_balance,
                    'credit_by' => $user_id,
                    'wallet_type' => 1,
                ]);

                $message = "Dear $userDetails->name Your Wallet Credited With Amount $amount Your Current balance is $user_balance $this->brand_name";
                $template_id = 5;
                $library = new SmsLibrary();
                $library->send_sms($userDetails->mobile, $message, $template_id);
                Gatewayorder::where('id', $gatewayorder_id)->update(['status_id' => 1]);
                return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Already Updated!']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'something went wrong']);
        }
    }

    function return_url(Request $request)
    {
        return redirect('agent/add-money/v1/welcome');
    }
}
