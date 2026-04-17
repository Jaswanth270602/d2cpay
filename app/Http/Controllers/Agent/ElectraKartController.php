<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Library\BasicLibrary;
use App\Library\GetcommissionLibrary;
use App\Library\Commission_increment;
use App\Models\Apiresponse;
use App\Models\Balance;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Gatewayorder;
use App\Models\Report;
use App\Models\User;
use App\Models\Traceurl;
use QrCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Validator;

class ElectraKartController extends Controller
{
    public function __construct()
    {
        $this->merchantId = '4146418';
        $this->provider_id = 337;
        $this->api_id = 13;
        
        $providers = Provider::find($this->provider_id);
        $this->min_amount = (isset($providers->min_amount)) ? $providers->min_amount : 10;
        $this->max_amount = (isset($providers->max_amount)) ? $providers->max_amount : 5000;
    }

    public function welcome()
    {
        $user_id = Auth::id();
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($this->provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        
        if ($serviceStatus == 1) {
            $data = ['page_title' => 'Add Money - Electrakart'];
            return view('agent.add-money.electrakart')->with($data);
        } else {
            return redirect()->back();
        }
    }

    public function createOrderWeb(Request $request)
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

    public function createOrderApi(Request $request)
    {
        $rules = array(
            'amount' => 'required|numeric|between:' . $this->min_amount . ',' . $this->max_amount,
            'client_id' => 'required',
            'callback_url' => 'required|url',
            'customer_name' => 'required|string|max:255',
            'mobile_number' => 'required|digits:10',
            'email' => 'required|email|max:255',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }

        $amount = $request->amount;
        $mode = 'API';
        $user_id = Auth::id();
        $callback_url = $request->callback_url;
        $client_id = $request->client_id;
        $customer_name = $request->customer_name;
        $mobile_number = $request->mobile_number;
        $email = $request->email;
        
        return Self::createOrderMiddle($amount, $user_id, $mode, $callback_url, $client_id, $customer_name, $email, $mobile_number);
    }

    private function createOrderMiddle($amount, $user_id, $mode, $callback_url, $client_id, $name, $email, $mobile)
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
                'email' => $email,
                'ip_address' => request()->ip(),
                'created_at' => $created_at,
                'status_id' => 3, // pending
                'api_id' => $this->api_id,
                'callback_url' => $callback_url,
                'client_id' => $client_id,
                'mode' => $mode,
            ]);

            $customOrderId = 'electra' . str_pad($orderId, 6, '0', STR_PAD_LEFT);
            Gatewayorder::where('id', $orderId)->update(['client_id' => $customOrderId]);

            $paymentUrl = "https://electrakart.in/PaymentUrl.aspx?amount={$amount}&orderid={$customOrderId}&MerchantID={$this->merchantId}";

            if ($mode == 'API') {
                $data = [
                    'paymentUrl' => $paymentUrl,
                    'txnid' => $orderId,
                    'orderid' => $customOrderId,
                ];
                return Response(['status' => 'success', 'message' => 'Order created successfully!', 'data' => $data]);
            }

            $data = [
                'paymentUrl' => $paymentUrl,
                'txnid' => $orderId,
                'orderid' => $customOrderId,
            ];
            return Response(['status' => 'success', 'message' => 'Order created successfully!', 'data' => $data]);

        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Service not active!']);
        }
    }

    public function viewQrcode(Request $request)
    {
        $upi_string = $request->upi_string;
        return response(QrCode::size(300)->generate($upi_string), 200)
            ->header('Content-Type', 'image/svg+xml');
    }

    
public function callbackUrl(Request $request)
{
    Apiresponse::insertGetId([
        'message' => json_encode($request->all()),
        'api_type' => 1,
        'created_at' => now(),
        'ip_address' => request()->ip()
    ]);

    $data = $request->all();
    $status = $data['status'] ?? $data['Status'] ?? null;
    $orderid = $data['orderid'] ?? $data['OrderId'] ?? $data['payin_ref'] ?? null;
    $amount = $data['amount'] ?? $data['Amount'] ?? null;
    $utr = $data['utr'] ?? $data['rrn'] ?? $data['transactionId'] ?? null;

    Log::info('Electrakart Callback Hit', compact('status', 'orderid', 'amount', 'utr'));

    if (!$status || !$orderid || !$amount || !$utr) {
        Log::warning('Missing required fields in callback', compact('data'));
        return response()->json(['status' => 'failure', 'message' => 'Missing required fields']);
    }

    // Use DB transaction + row lock to prevent race condition
    return DB::transaction(function () use ($status, $orderid, $amount, $utr) {

        // Lock the order row for update (prevents concurrent processing)
        $gatewayorders = Gatewayorder::where('client_id', $orderid)
            ->whereIn('status_id', [3, 9]) // 3 = pending, 9 = processing
            ->lockForUpdate()
            ->first();

        if (!$gatewayorders) {
            Log::warning('Order not found or already processed', ['orderid' => $orderid]);
            return response()->json(['status' => 'failure', 'message' => 'Invalid or already processed']);
        }

        // If already has report_id, exit
        if (!empty($gatewayorders->report_id)) {
            Log::warning('Duplicate callback: report already exists', [
                'orderid' => $orderid,
                'report_id' => $gatewayorders->report_id
            ]);
            return response()->json(['status' => false, 'message' => 'Transaction already processed']);
        }

        // Check if txnid already exists
        if (Report::where('txnid', $utr)->exists()) {
            Log::warning('Duplicate txn found', ['txnid' => $utr]);
            return response()->json(['status' => false, 'message' => 'Duplicate transaction']);
        }

        // Mark as processing to block second request mid-way
        Gatewayorder::where('id', $gatewayorders->id)->update(['status_id' => 9]);

        if (strtolower($status) === 'success') {
            $user_id = $gatewayorders->user_id;
            $userDetails = User::find($user_id);

            if (!$userDetails || !$userDetails->balance) {
                Log::error('User or balance not found', ['user_id' => $user_id]);
                return response()->json(['status' => false, 'message' => 'User or balance missing']);
            }

            $opening_balance = $userDetails->balance->aeps_balance;
            $provider_id = $this->provider_id;
            $scheme_id = $userDetails->scheme_id;

            try {
                $library = new GetcommissionLibrary();
                $commission = $library->get_commission($scheme_id, $provider_id, $amount);

                $retailer = $commission['retailer'] ?? 0;
                $d = $commission['distributor'] ?? 0;
                $sd = $commission['sdistributor'] ?? 0;
                $st = $commission['sales_team'] ?? 0;
                $rf = $commission['referral'] ?? 0;

                $incrementAmount = $amount - $retailer;

                Balance::where('user_id', $user_id)->increment('aeps_balance', $incrementAmount);
                $aeps_balance = Balance::where('user_id', $user_id)->value('aeps_balance');

                $description = "Add Money via Electrakart";
                $ctime = now()->format('Y-m-d H:i:s');

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

                Gatewayorder::where('id', $gatewayorders->id)->update([
                    'status_id' => 1, // success
                    'report_id' => $insert_id
                ]);

                // Commission increments
                (new Commission_increment())->parent_recharge_commission(
                    $user_id,
                    $userDetails->mobile,
                    $insert_id,
                    $provider_id,
                    $amount,
                    $this->api_id,
                    $retailer,
                    $d,
                    $sd,
                    $st,
                    $rf
                );

                // API callback to merchant if required
                if (!empty($gatewayorders->callback_url)) {
                    $queryParams = [
                        'status' => 'credit',
                        'client_id' => $gatewayorders->client_id,
                        'amount' => $amount,
                        'utr' => $utr,
                        'txnid' => $gatewayorders->id,
                    ];
                    $signature = hash_hmac('sha256', http_build_query($queryParams), $userDetails->api_token);
                    $queryParams['signature'] = $signature;

                    $url = $gatewayorders->callback_url . '?' . http_build_query($queryParams);
                    $response = @file_get_contents($url);

                    Traceurl::insertGetId([
                        'user_id' => $user_id,
                        'url' => $url,
                        'number' => $userDetails->mobile,
                        'response_message' => $response ?: 'No response',
                        'created_at' => $ctime
                    ]);
                }

                Log::info('Transaction processed successfully', ['report_id' => $insert_id]);
                return response()->json(['status' => 'success', 'message' => 'Transaction successful']);

            } catch (\Throwable $e) {
                Log::error('Processing failed', ['error' => $e->getMessage()]);
                return response()->json(['status' => false, 'message' => 'Processing error']);
            }

        } elseif (in_array(strtolower($status), ['failed', 'failure'])) {
            Gatewayorder::where('id', $gatewayorders->id)->update(['status_id' => 2]); // failed
            Log::warning('Payment failed', ['orderid' => $orderid]);
            return response()->json(['status' => 'failure', 'message' => 'Payment failed']);
        }

        return response()->json(['status' => 'failure', 'message' => 'Invalid status']);
    });
}


    public function statusEnquiryApi(Request $request)
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
            
            if ($report_id) {
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
                }
            }
            
            // Check order status
            if ($gatewayorders->status_id == 1) {
                $data = [
                    'client_id' => $client_id,
                    'amount' => $gatewayorders->amount,
                    'status' => 'credit',
                ];
                return Response()->json(['status' => true, 'message' => 'Transaction successful!', 'data' => $data]);
            } elseif ($gatewayorders->status_id == 2) {
                $data = [
                    'client_id' => $client_id,
                    'amount' => $gatewayorders->amount,
                    'status' => 'failed',
                ];
                return Response()->json(['status' => false, 'message' => 'Transaction failed!', 'data' => $data]);
            } else {
                $data = [
                    'client_id' => $client_id,
                    'amount' => $gatewayorders->amount,
                    'status' => 'pending',
                ];
                return Response()->json(['status' => false, 'message' => 'Transaction pending!', 'data' => $data]);
            }
        } else {
            return Response()->json(['status' => false, 'message' => 'No matching record found!']);
        }
    }
}