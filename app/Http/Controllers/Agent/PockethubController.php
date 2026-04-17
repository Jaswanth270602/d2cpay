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

use App\Models\Provider;
use App\Models\Gatewayorder;
use App\Models\Api;
use App\Models\User;
use App\Models\Apiresponse;
use App\Models\Report;
use App\Models\Balance;
use App\Models\Sitesetting;
use App\Models\Traceurl;

use App\Library\BasicLibrary;
use App\Library\GetcommissionLibrary;
use App\Library\SmsLibrary;
use App\Library\PockethubLibrary;

class PockethubController extends Controller
{

    public function __construct()
    {
        $this->provider_id = 333;
        $providers = Provider::find($this->provider_id);
        $this->min_amount = (isset($providers->min_amount)) ? $providers->min_amount : 10;
        $this->max_amount = (isset($providers->max_amount)) ? $providers->max_amount : 2000;

        $this->api_id = 8;
        $sitesettings = Sitesetting::where('company_id', 1)->first();
        $this->brand_name = (empty($sitesettings)) ? '' : $sitesettings->brand_name;
    }

    function welcome()
    {
        $user_id = Auth::id();
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($this->provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($serviceStatus == 1) {
            $data = array('page_title' => 'Payin');
            return view('agent.add-money.pockethub')->with($data);
        } else {
            return redirect()->back();
        }
    }

    function createOrderWeb(Request $request)
    {
        $rules = array(
            'amount' => 'required|numeric|between:' . $this->min_amount . ',' . $this->max_amount . '',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $amount = $request->amount;
        $mode = 'WEB';
        $user_id = Auth::id();
        $redirect_url = url('agent/add-money/v1/welcome');
        $callback_url = '';
        $client_id = '';
        $name = Auth::User()->name;
        $mobile = Auth::User()->mobile;
        $email = Auth::User()->email;
        return Self::createOrderMiddle($amount, $mode, $user_id, $redirect_url, $callback_url, $client_id, $name, $mobile, $email);
    }

    function createOrderApi(Request $request)
    {
        $rules = array(
            'amount' => 'required|numeric|between:' . $this->min_amount . ',' . $this->max_amount,
            'client_id' => 'required',  // Assuming 'clients' is the table for client data.
            'redirect_url' => 'required|url',
            'callback_url' => 'required|url',
            'customer_name' => 'required|string|max:255',
            'mobile_number' => 'required|digits:10',  // Adjust digits length according to your requirements
            'email' => 'required|email|max:255',  // Ensure email is in proper format
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $amount = $request->amount;
        $mode = 'API';
        $user_id = Auth::id();
        $redirect_url = $request->redirect_url;
        $callback_url = $request->callback_url;
        $client_id = $request->client_id;
        $customer_name = $request->customer_name;
        $mobile_number = $request->mobile_number;
        $email = $request->email;
        return Self::createOrderMiddle($amount, $mode, $user_id, $redirect_url, $callback_url, $client_id, $customer_name, $mobile_number, $email);
    }

    function createOrderMiddle($amount, $mode, $user_id, $redirect_url, $callback_url, $client_id, $name, $mobile, $email)
    {
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($this->provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($serviceStatus == 1) {
            $now = new \DateTime();
            $created_at = $now->format('Y-m-d H:i:s');
            $userDetails = User::find($user_id);
            $orderId = Gatewayorder::insertGetId([
                'user_id' => $user_id,
                'purpose' => 'Add Money',
                'amount' => $amount,
                'email' => Auth::User()->email,
                'ip_address' => request()->ip(),
                'created_at' => $created_at,
                'status_id' => 3,
                'mode' => $mode,
                'redirect_url' => $redirect_url,
                'callback_url' => $callback_url,
                'client_id' => $client_id,
            ]);
            $redirectUrl = url('api/call-back/pockethub-redirect-url') . '/' . $orderId;
            $library = new PockethubLibrary();
            $response = $library->payinCreateOrder($orderId, $name, $mobile, $email, $amount, $redirectUrl);
            $status_id = $response['status_id'];
            $message = $response['message'];
            if ($status_id == 1) {
                return Response()->json([
                    'status' => 'success',
                    'message' => 'Successfully created',
                    'payment_url' => $response['payment_url'],
                    'order_id' => $orderId,
                ]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => $message]);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission!']);
        }
    }


    function callbackUrl(Request $request)
    {
        Apiresponse::insertGetId(['message' => $request, 'api_type' => $this->api_id, 'request_message' => $request, 'ip_address' => request()->ip()]);
        if (request()->ip() != '194.163.189.198') {
            return Response()->json(['status' => 'failure', 'message' => 'Invalid request']);
        }
        $status = $request->status;
        if ($status == 'paid') {
            $exploadId = explode('_', $request->orderId);
            $client_txn_id = $exploadId[1];
            $amount = $request->amount;
            $gatewayorders = Gatewayorder::where('id', $client_txn_id)->where('amount', $amount)->where('status_id', 3)->first();
            if ($gatewayorders) {
                $utr = $request->utr;
                $reports = Report::where('txnid', $utr)->first();
                if ($reports) {
                    return ['status' => false, 'message' => 'Dupplicate transaction'];
                }
                $user_id = $gatewayorders->user_id;
                $userDetails = User::find($user_id);
                $opening_balance = $userDetails->balance->aeps_balance;
                $provider_id = $this->provider_id;
                $scheme_id = $userDetails->scheme_id;
                $library = new GetcommissionLibrary();
                $commission = $library->get_commission($scheme_id, $provider_id, $amount);
                $retailer = $commission['retailer'];
                $incrementAmount = $amount - $retailer;
                Balance::where('user_id', $user_id)->increment('aeps_balance', $incrementAmount);
                $balance = Balance::where('user_id', $user_id)->first();
                $aeps_balance = $balance->aeps_balance;
                $description = "Payin";
                $now = new \DateTime();
                $ctime = $now->format('Y-m-d H:i:s');
                $insert_id = Report::insertGetId([
                    'number' => $userDetails->mobile,
                    'provider_id' => $provider_id,
                    'amount' => $amount,
                    'api_id' => $this->api_id,
                    'status_id' => 6,
                    'created_at' => $ctime,
                    'user_id' => $user_id,
                    'profit' => '-' . $retailer,
                    'mode' => $gatewayorders->mode,
                    'txnid' => $utr,
                    'ip_address' => $gatewayorders->ip_address,
                    'description' => $description,
                    'opening_balance' => $opening_balance,
                    'total_balance' => $aeps_balance,
                    'credit_by' => $user_id,
                    'wallet_type' => 2,
                    'client_id' => $gatewayorders->client_id ?? '',
                ]);
                Gatewayorder::where('id', $client_txn_id)->update(['status_id' => 1, 'report_id' => $insert_id]);
                if (!empty($gatewayorders->callback_url)) {
                    $client_id = $gatewayorders->client_id;
                    // Prepare query parameters with proper encoding
                    $queryParams = [
                        'status' => 'credit',
                        'client_id' => $client_id,
                        'amount' => $amount,
                        'utr' => $utr,
                        'payerVPA' => '',
                        'report_id' => $insert_id,
                    ];
                    $url = $gatewayorders->callback_url . '?' . http_build_query($queryParams);
                    // Initialize cURL
                    $curl = curl_init();
                    curl_setopt_array($curl, [
                        CURLOPT_URL => $url,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_TIMEOUT => 30, // Timeout set to prevent hanging requests
                    ]);
                    $response = curl_exec($curl);
                    $curlError = curl_error($curl);
                    curl_close($curl);
                    // Log response or error message
                    Traceurl::insertGetId([
                        'user_id' => $user_id,
                        'url' => $url,
                        'number' => $userDetails->mobile,
                        'response_message' => $curlError ?: $response,
                        'created_at' => $ctime
                    ]);
                }

                return ['status' => 'success', 'message' => 'Transaction successful'];
            } else {
                return ['status' => 'failure', 'message' => 'Invalid gateway response'];
            }


        } else {
            return Response()->json(['status' => false, 'message' => 'Failed']);
        }
    }

    function redirectUrl(Redirect $redirect, $id)
    {
        $gatewayorders = Gatewayorder::find($id);
        $redirect_url = $gatewayorders->redirect_url ?? '';
        if (!empty($redirect_url)) {
            $client_id = $gatewayorders->client_id;
            $status_id = $gatewayorders->status_id;
            $url = $redirect_url;
            return redirect($url);
        }
        return redirect(url('agent/add-money/v3/welcome'));
    }

    function statusEnquiryApi(Request $request)
    {
        $rules = array(
            'client_id' => 'required|exists:gatewayorders,client_id',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $client_id = $request->client_id;
        $user_id = Auth::id();
        $gatewayorders = Gatewayorder::where('client_id', $client_id)->where('user_id', $user_id)->first();
        if ($gatewayorders) {
            $report_id = $gatewayorders->report_id;
            $reports = Report::find($report_id);
            if ($reports) {
                $data = [
                    'client_id' => $client_id,
                    'report_id' => $report_id,
                    'amount' => $reports->amount,
                    'utr' => $reports->txnid,
                    'status' => 'credit',
                ];
                return Response()->json(['status' => true, 'message' => 'Transaction record found successfully!', 'data' => $data]);
            } else {
                return Response()->json(['status' => false, 'message' => 'No matching report found!']);
            }
        } else {
            return Response()->json(['status' => false, 'message' => 'No matching report found!']);
        }
    }
}
