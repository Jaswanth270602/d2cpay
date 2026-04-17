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
use App\Library\Commission_increment;

class VtransactController extends Controller
{
    public function __construct()
    {
        $this->api_id = 12;
        $this->provider_id = 335;
        $providers = Provider::find($this->provider_id);
        $this->min_amount = (isset($providers->min_amount)) ? $providers->min_amount : 10;
        $this->max_amount = (isset($providers->max_amount)) ? $providers->max_amount : 2000;

        $this->base_url = optional(json_decode(optional(Api::find($this->api_id))->credentials))->base_url ?? '';
        $this->merchantId = optional(json_decode(optional(Api::find($this->api_id))->credentials))->merchantId ?? '';
        $this->clientid = optional(json_decode(optional(Api::find($this->api_id))->credentials))->clientid ?? '';
        $this->clientSecretKey = optional(json_decode(optional(Api::find($this->api_id))->credentials))->clientSecretKey ?? '';
    }


    function welcome()
    {
        $user_id = Auth::id();
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($this->provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($serviceStatus == 1) {
            $data = array('page_title' => 'Payin 5');
            return view('agent.add-money.vtransact')->with($data);
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
        $name = Auth::User()->name;
        $email = Auth::User()->email;
        $mobile = Auth::User()->mobile;
        return Self::createOrderMiddle($amount, $user_id, $mode, $callback_url, $client_id, $name, $email, $mobile);
    }

    function createOrderApi(Request $request)
    {
        $rules = array(
            'amount' => 'required|numeric|between:' . $this->min_amount . ',' . $this->max_amount,
            'client_id' => 'required',  // Assuming 'clients' is the table for client data.
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
        return Self::createOrderMiddle($amount, $user_id, $mode, $callback_url, $client_id, $customer_name, $email, $mobile_number);
    }

    function createOrderMiddle($amount, $user_id, $mode, $callback_url, $client_id, $name, $email, $mobile)
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
                'api_id' => $this->api_id,
                'callback_url' => $callback_url,
                'client_id' => $client_id,
                'mode' => $mode,
            ]);
            // Define the URL and payload
            $fName = urlencode($name);
            $payin_ref = 'payin' . $orderId;
            $url = "https://api.vtransact.in/VTranSact564895/api/v1/Integrate/VTranSactDynamicQR?payin_ref=$payin_ref&amount=$amount&fName=$fName&lName=$fName&mNo=$mobile&email=$email&add1=xxxx&city=xxxx&state=xxxxx&pCode=xxxxxxx";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{
  "merchantId": "' . $this->merchantId . '",
  "clientid": "' . $this->clientid . '",
  "clientSecretKey": "' . $this->clientSecretKey . '"
}
',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            $jsonDecopde = json_decode($response, true);
            $res = json_decode($jsonDecopde);
            $status = $res->status ?? 'false';
            if ($status == 'true') {
                $qrString = $res->UpiLink ?? '';
                if ($mode == 'API') {
                    $data = [
                        'qrString' => $qrString,
                        'txnid' => $orderId,
                    ];
                    return Response(['status' => 'success', 'message' => $res->message ?? '', 'data' => $data]);
                }
                $qrCodeUrl = url('agent/add-money/v5/view-qrcode') . '?upi_string=' . urlencode($qrString);
                $data = [
                    'qrCodeUrl' => $qrCodeUrl,
                    'qrString' => $qrString,
                    'txnid' => $orderId,
                ];
                return Response(['status' => 'success', 'message' => $res->message ?? '', 'data' => $data]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Failed to create order!']);
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
        $clientIp = '160.187.80.157';
        if ($clientIp != request()->ip()) {
            return Response()->json(['status' => 'failure', 'message' => 'Invalid IP address!']);
        }
        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'Invalid JSON'], 400);
        }
        $status = $data['status'] ?? null;
        $payin_ref = $data['payin_ref'] ?? null;
        $amount = $data['Amount'] ?? null;
        $utr = $data['rrn'] ?? null;
        $exploadRef = explode('payin', $payin_ref);
        $orderId = $exploadRef[1];
        if ($status == 'Success') {
            $gatewayorders = Gatewayorder::where('id', $orderId)->where('status_id', 3)->first();
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
                $d = $commission['distributor'];
                $sd = $commission['sdistributor'];
                $st = $commission['sales_team'];
                $rf = $commission['referral'];
                $incrementAmount = $amount - $retailer;
                Balance::where('user_id', $user_id)->increment('aeps_balance', $incrementAmount);
                $balance = Balance::where('user_id', $user_id)->first();
                $aeps_balance = $balance->aeps_balance;
                $description = "Add Money";
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
                if ($gatewayorders->mode != 'API') {
                    Report::where('id', $insert_id)->update(['client_id' => $insert_id]);
                }
                Gatewayorder::where('id', $orderId)->update(['status_id' => 1, 'report_id' => $insert_id]);
                $number = $userDetails->mobile;
                $library = new Commission_increment();
                $library->parent_recharge_commission($user_id, $number, $insert_id, $provider_id, $amount, $this->api_id, $retailer, $d, $sd, $st, $rf);

                if (!empty($gatewayorders->callback_url)) {
                    $clientId = $gatewayorders->client_id;
                    $apiToken = $userDetails->api_token;
                    // Prepare query parameters with proper encoding
                    $queryParams = [
                        'status' => 'credit',
                        'client_id' => $clientId,
                        'amount' => $amount,
                        'utr' => $utr,
                        'txnid' => $orderId,
                    ];
                    // Create the signature string
                    $signatureString = http_build_query($queryParams); // status=credit&client_id=...&...
                    $signature = hash_hmac('sha256', $signatureString, $apiToken);
                    // Append signature to the query
                    $queryParams['signature'] = $signature;
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
        } elseif ($status == 'Failed') {
            Gatewayorder::where('id', $orderId)->update(['status_id' => 2]);
        }
        return Response()->json(['status' => 'failure', 'message' => 'Invalid status!']);
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
        $gatewayorders = Gatewayorder::where('client_id', $client_id)->where('user_id', $user_id)->orderBy('id', 'DESC')->first();
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
