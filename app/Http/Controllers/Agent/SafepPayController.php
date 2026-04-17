<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Models\Gatewayorder;
use App\Models\Provider;
use App\Models\User;
use App\Models\Balance;
use App\Models\Report;
use App\Models\Api;
use Helpers;
use App\Models\Apiresponse;
use App\Models\Traceurl;
use QrCode;

use App\Library\BasicLibrary;
use App\Library\GetcommissionLibrary;
use App\Library\RefundLibrary;
use App\Library\Commission_increment;
use Illuminate\Support\Facades\Log;


class SafepPayController extends Controller
{
    private $api_id;
    private $provider_id;
    private $min_amount;
    private $max_amount;
    private $base_url;
    private $client_id;
    private $client_secret;
    private $bearer_token;

    public function __construct()
    {
        $this->api_id = 14;
        $this->provider_id = 338;
        
        $providers = Provider::find($this->provider_id);
        $this->min_amount = (isset($providers->min_amount)) ? $providers->min_amount : 10;
        $this->max_amount = (isset($providers->max_amount)) ? $providers->max_amount : 50000;

        $credentials = json_decode(optional(Api::find($this->api_id))->credentials);
        $this->base_url = $credentials->base_url ?? 'https://safeppay.com/api/';
        $this->client_id = $credentials->client_id ?? 'd11bf01e-936a-41b5-99b8-34fc75d5edad';
        $this->client_secret = $credentials->client_secret ?? 'v8addUwIW55EGxoMryEjYKaxJXgdhhOWOGtgGkeA';
        
        // Generate token on initialization
        $this->bearer_token = $this->generateToken();
    }

    /**
     * Generate authentication token
     */
    private function generateToken()
    {
        $url = $this->base_url . 'generate-token';
        
        $parameters = [
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
        ];

        $header = [
            "Content-Type: application/json",
            "Accept: application/json"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parameters));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $res = json_decode($response);
        
        if (isset($res->status) && $res->status == true) {
            return $res->data->access_token ?? '';
        }
        
        return '';
    }

    function welcome()
    {
        $user_id = Auth::id();
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($this->provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($serviceStatus == 1) {
            $data = array('page_title' => 'Payin 7 - SafepPay');
            return view('agent.add-money.safeppay')->with($data);
        } else {
            return redirect()->back();
        }
    }

    function createOrderWeb(Request $request)
    {
        $rules = array(
            'amount' => 'required|numeric|between:' . $this->min_amount . ',' . $this->max_amount,
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
        // Step 1: Extract API token from Authorization header
        $token = str_replace('Bearer ', '', $request->header('Authorization'));
        $user = \App\Models\User::where('api_token', $token)->first();
    
        if (!$user) {
            return response()->json(['status' => 'failure', 'message' => 'Invalid or missing API token.'], 401);
        }
    
        // Step 2: Validate input
        $rules = [
            'amount' => 'required|numeric|between:' . $this->min_amount . ',' . $this->max_amount,
            'client_id' => 'required',
            'callback_url' => 'required|url',
            'customer_name' => 'required|string|max:255',
            'mobile_number' => 'required|digits:10',
            'email' => 'required|email|max:255',
        ];
    
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
    
        // Step 3: Assign values
        $user_id = $user->id;  // <- user identified by API token
        $amount = $request->amount;
        $mode = 'API';
        $callback_url = $request->callback_url;
        $client_id = $request->client_id;
        $customer_name = $request->customer_name;
        $mobile_number = $request->mobile_number;
        $email = $request->email;
    
        // Step 4: Create order
        return Self::createOrderMiddle(
            $amount,
            $user_id,
            $mode,
            $callback_url,
            $client_id,
            $customer_name,
            $email,
            $mobile_number
        );
    }

    function createOrderMiddle($amount, $user_id, $mode, $callback_url, $client_id, $name, $email, $mobile)
    {
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($this->provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        
        if ($serviceStatus == 1) {
            $now = new \DateTime();
            $created_at = $now->format('Y-m-d H:i:s');
            
            // Generate order reference/token before insert
            $temp_order_token = 'ORD' . time() . rand(1000, 9999);
            
            $orderId = Gatewayorder::insertGetId([
                'user_id' => $user_id,
                'purpose' => 'Add Money',
                'amount' => $amount,
                'email' => $email,
                'ip_address' => request()->ip(),
                'created_at' => $created_at,
                'status_id' => 3, // Pending status
                'callback_url' => $callback_url,
                'client_id' => $client_id,
                'mode' => $mode,
                'order_token' => $temp_order_token,
                'api_id' => $this->api_id,
            ]);

            // Generate final order reference with actual ID
            $order_token = 'ORD' . $orderId . time();

            $url = $this->base_url . 'payment/create';
            
            // Corrected parameters to match API docs
            $parameters = [
                'order_reference' => $order_token,
                'payment_amount' => $amount,
                'payer_first_name' => explode(' ', $name)[0],
                'payer_last_name' => explode(' ', $name)[1] ?? 'd2c',
                'payer_email' => $email,
                'payer_mobile' => $mobile,
                'payment_note' => 'Payment for order #' . $orderId,
                'redirect_url' => url('api/call-back/safeppay'),
            ];

            $header = [
                "Authorization: Bearer " . $this->bearer_token,
                "Content-Type: application/json",
                "Accept: application/json"
            ];

            $method = 'POST';
            $response = Helpers::pay_curl_post($url, $header, json_encode($parameters), $method);
            $res = json_decode($response);
            
            // Log API response - Fixed column names for apiresponse table
            Apiresponse::insertGetId([
                'message' => $response, 
                'api_type' => 1, 
                'created_at' => now(), 
                'ip_address' => request()->ip()
            ]);
            
            $status = $res->status ?? false;
            
            if ($status == true) {
                $transaction_id = $res->data->transaction->id ?? '';
                $qrString = $res->data->channels->upi_intents->default ?? '';
                $qrCode = $res->data->channels->qr_code ?? '';
                $checkout_url = $res->data->channels->checkout_url ?? '';
                
                // Store transaction_id in remark field and update order_token
                // Since transaction_id and order_reference columns don't exist, 
                // we use order_token for order reference and remark for transaction_id
                Gatewayorder::where('id', $orderId)->update([
                    'remark' => $transaction_id, // Store gateway transaction_id in remark
                    'order_token' => $order_token // Update with final order token
                ]);
                
                if ($mode == 'API') {
                    $data = [
                        'qrString' => $qrString,
                        'qrCode' => $qrCode,
                        'checkout_url' => $checkout_url,
                        'txnid' => $orderId,
                        'order_token' => $order_token,
                        'transaction_id' => $transaction_id,
                        'upi_intents' => $res->data->channels->upi_intents ?? null,
                    ];
                    return Response(['status' => 'success', 'message' => $res->message ?? 'Payment initiated successfully', 'data' => $data]);
                }
                
                $qrCodeUrl = url('agent/add-money/v7/view-qrcode') . '?upi_string=' . urlencode($qrString);
                $data = [
                    'qrCodeUrl' => $qrCodeUrl,
                    'qrString' => $qrString,
                    'qrCode' => $qrCode,
                    'checkout_url' => $checkout_url,
                    'txnid' => $orderId,
                    'order_token' => $order_token,
                    'transaction_id' => $transaction_id,
                ];
                return Response(['status' => 'success', 'message' => $res->message ?? 'Payment initiated successfully', 'data' => $data]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => $res->message ?? 'Failed to create payment']);
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
        $ctime = now();
        $rawContent = $request->getContent();
    
        // Log incoming request
        Log::info('Callback received', ['content' => $rawContent, 'ip' => $request->ip()]);
        
        Apiresponse::insertGetId([
            'message' => $rawContent,
            'api_type' => 1,
            'created_at' => $ctime,
            'ip_address' => $request->ip()
        ]);
    
        $data = json_decode($rawContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Invalid JSON in callback', ['error' => json_last_error_msg()]);
            return response()->json(['error' => 'Invalid JSON'], 400);
        }
    
        Log::info('Full callback data structure', ['data' => $data]);
    
        // Check if required fields exist
        if (!isset($data['data']['transaction']['status'])) {
            Log::warning('Missing required fields in callback', compact('data'));
            return response()->json(['error' => 'Missing required fields'], 400);
        }
    
        // Correct field mapping based on actual provider response
        $transaction_status = $data['data']['transaction']['status'] ?? null;
        $order_reference    = $data['data']['transaction']['reference_id'] ?? null;
        $transaction_id     = $data['data']['transaction']['transaction_id'] ?? null;
    
        $amount             = $data['data']['transaction']['amount']['total'] ?? null;
        $charge             = $data['data']['transaction']['amount']['fee'] ?? 0;
        $received_amount    = $data['data']['transaction']['amount']['net'] ?? $amount;
    
        $utr                = $data['data']['summary']['utr'] ?? null;
        $payment_mode       = $data['data']['summary']['payment_mode'] ?? null;
    
        Log::info('Parsed callback data', compact(
            'transaction_status', 'order_reference', 'transaction_id',
            'amount', 'charge', 'received_amount', 'utr', 'payment_mode'
        ));
    
        // Validate required fields
        if (!$transaction_status || !$order_reference || !$amount) {
            Log::error('Missing critical fields', compact(
                'transaction_status', 'order_reference', 'amount'
            ));
            return response()->json(['error' => 'Missing critical transaction data'], 400);
        }
    
        if ($transaction_status === 'success') {
            Log::info('Processing successful transaction', ['order_reference' => $order_reference]);
            
            $gatewayorders = Gatewayorder::where('order_token', $order_reference)
                ->where('status_id', 3)
                ->first();
    
            if (!$gatewayorders) {
                Log::error('Gateway order not found or status not 3', [
                    'order_reference' => $order_reference,
                    'existing_orders' => Gatewayorder::where('order_token', $order_reference)->get()->toArray()
                ]);
                return response()->json(['status' => 'failure', 'message' => 'Invalid gateway response']);
            }
    
            Log::info('Found gateway order', ['gateway_order_id' => $gatewayorders->id]);
    
            // Duplicate transaction check
            if ($utr && Report::where('txnid', $utr)->exists()) {
                Log::warning('Duplicate transaction detected', ['utr' => $utr]);
                return response()->json(['status' => false, 'message' => 'Duplicate transaction']);
            }
    
            $user_id = $gatewayorders->user_id;
            $user = User::find($user_id);
            if (!$user) {
                Log::error('User not found for gateway order', ['user_id' => $user_id]);
                return response()->json(['status' => 'failure', 'message' => 'User not found']);
            }
    
            Log::info('Found user', ['user_id' => $user_id]);
    
            $opening_balance = $user->balance->aeps_balance ?? 0;
            $provider_id = $this->provider_id;
            $scheme_id = $user->scheme_id;
    
            // Commission
            $library = new GetcommissionLibrary();
            $commission = $library->get_commission($scheme_id, $provider_id, $amount);
            Log::info('Commission calculated', $commission);
    
            $retailer = $commission['retailer'] ?? 0;
            $d = $commission['distributor'] ?? 0;
            $sd = $commission['sdistributor'] ?? 0;
            $st = $commission['sales_team'] ?? 0;
            $rf = $commission['referral'] ?? 0;
    
            $incrementAmount = $amount - $retailer;
    
            Log::info('Balance update details', [
                'user_id' => $user_id,
                'amount' => $amount,
                'retailer' => $retailer,
                'incrementAmount' => $incrementAmount
            ]);
    
            // Update user balance
            $balanceUpdated = Balance::where('user_id', $user_id)->increment('aeps_balance', $incrementAmount);
            $aeps_balance = Balance::where('user_id', $user_id)->value('aeps_balance');
            Log::info('User balance updated', [
                'user_id' => $user_id, 
                'new_balance' => $aeps_balance,
                'rows_affected' => $balanceUpdated
            ]);
    
            $description = "Add Money via " . ($payment_mode ?? 'Safepay');
    
            $insert_id = Report::insertGetId([
                'number' => $user->mobile,
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
            Log::info('Report inserted', ['report_id' => $insert_id]);
    
            if ($gatewayorders->mode != 'API') {
                Report::where('id', $insert_id)->update(['client_id' => $insert_id]);
                Log::info('Report client_id updated for non-API mode', ['report_id' => $insert_id]);
            }
    
            // Parent commission
            $commissionLibrary = new Commission_increment();
            $commissionLibrary->parent_recharge_commission(
                $user_id, $user->mobile, $insert_id, $provider_id, $amount,
                $this->api_id, $retailer, $d, $sd, $st, $rf
            );
            Log::info('Parent commission executed', ['report_id' => $insert_id]);
    
            // Update gateway order
            $gatewayUpdated = Gatewayorder::where('id', $gatewayorders->id)->update([
                'status_id' => 1,
                'report_id' => $insert_id
            ]);
            Log::info('Gateway order updated', [
                'order_id' => $gatewayorders->id, 
                'status' => 1, 
                'report_id' => $insert_id,
                'rows_affected' => $gatewayUpdated
            ]);
    
            // Send callback to client
            if (!empty($gatewayorders->callback_url)) {
                try {
                    $clientId = $gatewayorders->client_id;
                    $apiToken = $user->api_token;
                    $queryParams = [
                        'status' => 'credit',
                        'client_id' => $clientId,
                        'amount' => $amount,
                        'utr' => $utr,
                        'txnid' => $gatewayorders->id,
                        'order_token' => $order_reference,
                        'transaction_id' => $transaction_id,
                    ];
                    $signatureString = http_build_query($queryParams);
                    $queryParams['signature'] = hash_hmac('sha256', $signatureString, $apiToken);
                    $url = $gatewayorders->callback_url . '?' . http_build_query($queryParams);
    
                    $response = Helpers::curlGet($url);
                    Traceurl::insertGetId([
                        'user_id' => $user_id,
                        'url' => $url,
                        'number' => $user->mobile,
                        'response_message' => $response,
                        'created_at' => $ctime
                    ]);
                    Log::info('Callback sent', ['url' => $url, 'response' => $response]);
                } catch (\Exception $e) {
                    Log::error('Callback failed', ['error' => $e->getMessage()]);
                }
            }
    
            return response()->json(['status' => 'success', 'message' => 'Transaction successful']);
        }
    
        if ($transaction_status === 'failed') {
            Log::info('Processing failed transaction', ['order_reference' => $order_reference]);
            
            $gatewayorders = Gatewayorder::where('order_token', $order_reference)->first();
            if ($gatewayorders) {
                Gatewayorder::where('id', $gatewayorders->id)->update(['status_id' => 2]);
                Log::info('Gateway order updated to failed', ['order_id' => $gatewayorders->id]);
            }
            return response()->json(['status' => 'failure', 'message' => 'Transaction failed']);
        }
    
        Log::warning('Callback with unknown transaction status', ['status' => $transaction_status]);
        return response()->json(['status' => 'failure', 'message' => 'Invalid status']);
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
        
        $gatewayorders = Gatewayorder::where('client_id', $client_id)
            ->where('user_id', $user_id)
            ->orderBy('id', 'DESC')
            ->first();
            
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
                return Response()->json([
                    'status' => true, 
                    'message' => 'Transaction record found successfully!', 
                    'data' => $data
                ]);
            } else {
                return Response()->json(['status' => false, 'message' => 'No matching report found!']);
            }
        } else {
            return Response()->json(['status' => false, 'message' => 'No matching report found!']);
        }
    }

    function checkBalance()
    {
        $url = $this->base_url . 'balance/payin';
        
        $header = [
            "Authorization: Bearer " . $this->bearer_token,
            "Accept: application/json"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $res = json_decode($response);
        
        if (isset($res->status) && $res->status == true) {
            $balance = $res->data->balance ?? 0;
            return Response()->json([
                'status' => true, 
                'message' => $res->message ?? 'Balance fetched successfully', 
                'balance' => $balance
            ]);
        } else {
            return Response()->json([
                'status' => false, 
                'message' => $res->message ?? 'Failed to fetch balance'
            ]);
        }
    }

    function statusCheckApi(Request $request)
    {
        $rules = array(
            'transaction_id' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }

        $transaction_id = $request->transaction_id;
        $url = $this->base_url . 'payment/' . $transaction_id . '/status';
        
        $header = [
            "Authorization: Bearer " . $this->bearer_token,
            "Accept: application/json"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $res = json_decode($response);
        
        if (isset($res->status)) {
            return Response()->json([
                'status' => $res->status, 
                'message' => $res->message ?? 'Status retrieved', 
                'data' => $res->data ?? null
            ]);
        } else {
            return Response()->json([
                'status' => false, 
                'message' => 'Failed to retrieve status'
            ]);
        }
    }
}