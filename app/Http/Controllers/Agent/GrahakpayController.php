<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use Str;
use App\Models\Gatewayorder;
use App\Models\Provider;
use App\Models\User;
use App\Models\Balance;
use App\Models\Report;
use App\Models\Sitesetting;
use App\Models\Api;
use App\Library\SmsLibrary;
use Helpers;
use App\Models\Apiresponse;
use App\Models\Traceurl;
use QrCode;

use App\Library\BasicLibrary;
use App\Library\GetcommissionLibrary;

class GrahakpayController extends Controller
{
    public function __construct()
    {
        $this->api_id = 10;
        $this->provider_id = 334;

        $this->base_url = optional(json_decode(optional(Api::find($this->api_id))->credentials))->base_url ?? '';
        $this->api_token = optional(json_decode(optional(Api::find($this->api_id))->credentials))->api_token ?? '';
    }

    function welcome()
    {
        $user_id = Auth::id();
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($this->provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($serviceStatus == 1) {
            $data = array('page_title' => 'Payin 2');
            return view('agent.add-money.grahakpay')->with($data);
        } else {
            return redirect()->back();
        }
    }

    function createOrderWeb(Request $request)
    {
        $rules = array(
            'amount' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $amount = $request->amount;
        $user_id = Auth::id();
        $mode = 'WEB';
        $callback_url = '';
        $client_id = '';
        return Self::createOrderMiddle($amount, $user_id, $mode, $callback_url, $client_id);

    }

    function createOrderApp(Request $request)
    {
        $rules = array(
            'amount' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $amount = $request->amount;
        $user_id = Auth::id();
        $mode = 'APP';
        $callback_url = '';
        $client_id = '';
        return Self::createOrderMiddle($amount, $user_id, $mode, $callback_url, $client_id);
    }

    function createOrderMiddle($amount, $user_id, $mode, $callback_url, $client_id)
    {
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($this->provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($serviceStatus == 1) {
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
                'callback_url' => $callback_url,
                'client_id' => $client_id,
                'mode' => $mode,
            ]);
            $callback_url = url('api/call-back/rmstrade-qrcode');
            $url = $this->base_url . 'api/add-money/v1/create-order';
            $parameters = array(
                'api_token' => $this->api_token,
                'amount' => $amount,
                'client_id' => $orderId,
                'redirect_url' => $callback_url,
                'callback_url' => $callback_url,
                'customer_name' => Auth::User()->name,
                'mobile_number' => Auth::User()->mobile,
                'email' => Auth::User()->email,
            );
            $method = 'POST';
            $header = ["Accept:application/json"];
            $response = Helpers::pay_curl_post($url, $header, $parameters, $method);
            $res = json_decode($response);
            $status = $res->status ?? 'failure';
            if ($status == 'success') {
                $data = [
                    'payment_link' => $res->data->payment_link ?? '',
                    'txnid' => $orderId,
                ];
                return Response(['status' => 'success', 'message' => $res->message ?? '', 'data' => $data]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => $res->message ?? '']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Service not active!']);
        }
    }

    function viewQrcode(Request $request)
    {
        $upi_string = $request->upi_string;
        // Generate the QR code as an image
        return response(QrCode::size(300)->generate($upi_string), 200)
            ->header('Content-Type', 'image/svg+xml');
    }

    function callbackUrl(Request $request)
    {
        Apiresponse::insertGetId(['message' => $request, 'api_type' => 1, 'created_at' => now(), 'ip_address' => request()->ip()]);
        $rules = array(
            'status' => ['required', 'string', 'in:credit,pending,failure'],
            'client_id' => ['required', 'integer', 'exists:gatewayorders,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'utr' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9]+$/'],
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $status = $request->status;
        $client_id = $request->client_id;
        $amount = $request->amount;
        $utr = $request->utr;
        $payerVPA = $request->payerVPA;
        $payerName = $request->payerName;
        if ($status == 'credit') {
            $gatewayorders = Gatewayorder::where('id', $client_id)->where('status_id', 3)->first();
            if ($gatewayorders) {
                $reports = Report::where('txnid', $utr)->first();
                if ($reports) {
                    return ['status' => false, 'message' => 'Dupplicate transaction'];
                }
                $user_id = $gatewayorders->user_id;
                $userDetails = User::find($user_id);
                $opening_balance = $userDetails->balance->user_balance;
                $provider_id = $this->provider_id;
                $scheme_id = $userDetails->scheme_id;
                $library = new GetcommissionLibrary();
                $commission = $library->get_commission($scheme_id, $provider_id, $amount);
                $retailer = $commission['retailer'];
                $incrementAmount = $amount - $retailer;
                Balance::where('user_id', $user_id)->increment('user_balance', $incrementAmount);
                $balance = Balance::where('user_id', $user_id)->first();
                $user_balance = $balance->user_balance;
                $description = "$payerVPA | $payerName";
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
                    'total_balance' => $user_balance,
                    'credit_by' => $user_id,
                    'wallet_type' => 1,
                ]);
                Gatewayorder::where('id', $client_id)->update(['status_id' => 1]);
                if (!empty($gatewayorders->callback_url)) {
                    $client_id = $gatewayorders->client_id;
                    // Prepare query parameters with proper encoding
                    $queryParams = [
                        'status' => 'credit',
                        'client_id' => $client_id,
                        'amount' => $amount,
                        'utr' => $utr,
                        'payerVPA' => '',
                        'txnid' => $request->txnid,
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
            return Response()->json(['status' => 'failure', 'message' => 'Invalid status!']);
        }
    }


}

