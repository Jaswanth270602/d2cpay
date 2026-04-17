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
use App\Cashfreegateway;
use App\Traceurl;
use QrCode;

use App\Library\BasicLibrary;
use App\Library\GetcommissionLibrary;

class PayuController extends Controller
{
    public function __construct()
    {
        $this->api_id = 3;
        $this->provider_id = 331;

        $this->key = 'OUO6aH';
        $this->salt = 'kvM1k8Ercb8cEuffnP6afSkFO5g5tuB9';
        $this->paymentUrl = 'https://secure.payu.in/_payment';
    }


    function welcome()
    {
        $user_id = Auth::id();
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($this->provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($serviceStatus == 1) {
            $data = array('page_title' => 'Add Money');
            return view('agent.add-money.payU')->with($data);
        } else {
            return redirect()->back();
        }
    }

    function create_order(Request $request)
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

    function createOrderApi(Request $request)
    {
        $rules = array(
            'amount' => 'required|numeric|min:1', // Must be a number and at least 1
            'callback_url' => 'required|url', // Must be a valid URL
            'client_id' => 'required|string|max:255', // Required string with max length of 50

        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $amount = $request->amount;
        $user_id = Auth::id();
        $mode = 'API';
        $callback_url = $request->callback_url;
        $client_id = $request->client_id;
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
            $userDetails = User::find($user_id);
            $txnid = Gatewayorder::insertGetId([
                'user_id' => $user_id,
                'purpose' => 'Add Money',
                'amount' => $amount,
                'email' => $userDetails->email,
                'ip_address' => request()->ip(),
                'created_at' => $created_at,
                'status_id' => 3,
                'mode' => $mode,
                'api_id' => $this->api_id,
                'callback_url' => $callback_url,
                'client_id' => $client_id,
            ]);
            $productInfo = "Product Purchase";
            $firstName = $userDetails->name;
            $email = $userDetails->email;
            $phone = $userDetails->mobile;
            $udf1 = "";
            $udf2 = "";
            $udf3 = "";
            $udf4 = "";
            $udf5 = "";
            $successUrl = url('/payu-success'); // Define in web.php
            $failureUrl = url('/payu-failure'); // Define in web.php

            // Generate hash key (sha512 of concatenated string)
            $hashString = "$this->key|$txnid|$amount|$productInfo|$firstName|$email|$udf1|$udf2|$udf3|$udf4|$udf5||||||$this->salt";
            $hash = hash('sha512', $hashString);

            $postFields = [
                "key" => $this->key,
                "txnid" => $txnid,
                "amount" => $amount,
                "productinfo" => $productInfo,
                "firstname" => $firstName,
                "email" => $email,
                "phone" => $phone,
                "surl" => $successUrl,
                "furl" => $failureUrl,
                "hash" => $hash,
                "pg" => "UPI",
                "bankcode" => "INTENT",
                "upi_mode" => "intent",
                'txn_s2s_flow' => 4,
            ];
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $this->paymentUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($postFields),
                CURLOPT_HTTPHEADER => [
                    "Content-Type: application/x-www-form-urlencoded"
                ],
            ]);
            $response = curl_exec($curl);
            curl_close($curl);
            $res = json_decode($response);
            if (!isset($res->metaData)) {
                return Response()->json(['status' => 'failure', 'message' => 'Invalid response format']);
            }
            $txnStatus = $res->metaData->txnStatus ?? 'unknown';
            if ($txnStatus == 'pending') {
                $qrString = Self::formatUPIIntent($res->result->intentURIData);
                $qrCodeUrl = url('agent/add-money/v2/view-qrcode') . '?upi_string=' . urlencode($qrString);
                $data = [
                    'qrCodeUrl' => $qrCodeUrl,
                    'qrString' => $qrString,
                    'txnid' => $txnid,
                    'amount' => $amount,
                ];
                if ($mode == 'API') {
                    unset($data['qrCodeUrl']);
                }

                return Response(['status' => 'success', 'message' => 'Successful', 'data' => $data]);
            } else {
                $message = $res->metaData->message ?? 'Transaction failed';
                return Response()->json(['status' => 'failure', 'message' => $message]);
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

    function formatUPIIntent($intentURI)
    {
        if (!$intentURI) {
            return null;
        }
        parse_str(str_replace('&', '&', $intentURI), $data);
        // Map extracted parameters to required format
        return sprintf(
            "upi://pay?mc=7322&pa=%s&am=%s&tr=%s&pn=%s",
            urlencode($data['pa'] ?? 'unknown@upi'),
            urlencode($data['am'] ?? '0.00'),
            urlencode($data['tr'] ?? uniqid()),
            urlencode($data['pn'] ?? 'UNKNOWN')
        );
    }

    function callbackUrl(Request $request)
    {
        Apiresponse::insertGetId(['message' => $request, 'api_type' => 1, 'created_at' => now(), 'ip_address' => request()->ip(), 'response_type' => 'payu']);
        $txnid = $request->input('txnid');
        $amount = $request->input('amount');
        $productInfo = $request->input('productinfo');
        $firstName = $request->input('firstname');
        $email = $request->input('email');
        $udf1 = $request->input('udf1', '');
        $udf2 = $request->input('udf2', '');
        $udf3 = $request->input('udf3', '');
        $udf4 = $request->input('udf4', '');
        $udf5 = $request->input('udf5', '');
        $status = $request->input('status');

        // Create the reverse hash string
        $hashString = "$this->salt|$status||||||$udf5|$udf4|$udf3|$udf2|$udf1|$email|$firstName|$productInfo|$amount|$txnid|$this->key";

        // Generate hash
        $generatedHash = hash('sha512', $hashString);
        if ($generatedHash != $request->input('hash')) {
            return ['status' => false, 'message' => 'invalid hash'];
        }

        $status = $request->status;
        $unmappedstatus = $request->unmappedstatus;
        $amount = $request->amount;
        $client_id = $request->txnid;
        $utr = $request->bank_ref_no;
        $payerVPA = $request->field3 ?? '';
        if ($status == 'success' && $unmappedstatus == 'captured') {
            $gatewayorders = Gatewayorder::where('id', $client_id)->where('status_id', 3)->first();
            if ($gatewayorders) {
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
                $description = "$payerVPA";
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
                    'txnid' => $utr,
                    'ip_address' => $gatewayorders->ip_address,
                    'description' => $description,
                    'opening_balance' => $opening_balance,
                    'total_balance' => $aeps_balance,
                    'credit_by' => $user_id,
                    'wallet_type' => 2,
                    'client_id' => $gatewayorders->client_id ?? '',
                ]);
                Gatewayorder::where('id', $client_id)->update(['status_id' => 1, 'report_id' => $insert_id]);
                if (!empty($gatewayorders->callback_url)) {
                    $client_id = $gatewayorders->client_id;
                    // Prepare query parameters with proper encoding
                    $queryParams = [
                        'status' => 'credit',
                        'client_id' => $client_id,
                        'amount' => $amount,
                        'utr' => $utr,
                        'payerVPA' => $payerVPA,
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
            return ['status' => 'failure', 'message' => 'Invalid gateway response'];
        }

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

    /**
     * Generate hash for PayU callback request
     */


}
