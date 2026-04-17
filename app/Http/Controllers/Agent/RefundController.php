<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Apiresponse;
use App\Library\RefundLibrary;
use App\Models\Callbackurl;
use App\Models\Report;
use App\Models\Company;
use App\Models\Sitesetting;
use App\Library\BasicLibrary;
use App\Library\RechargeLibrary;
use App\Library\GetcommissionLibrary;
use App\Library\Commission_increment;
use App\Library\SmsLibrary;
use App\Library\PaywizeLibrary;
use App\Models\Provider;
use App\Models\Balance;
use App\Models\User;
use App\Models\Commissionreport;
use App\Models\Api;
use App\Models\Member;
use App\Models\Traceurl;
use Helpers;
use Illuminate\Support\Facades\Log;


class RefundController extends Controller
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
    }


    function dynamic_response(Request $request, $api_id)
    {
        $callbackurls = Callbackurl::where('api_id', $api_id)->first();
        if ($callbackurls) {
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            Apiresponse::insertGetId(['message' => $request, 'api_type' => $api_id, 'response_type' => 'call_back', 'ip_address' => request()->ip(), 'created_at' => $ctime]);
            $status_parameter = $callbackurls->status_parameter;
            $success_value = $callbackurls->success_value;
            $failure_value = $callbackurls->failure_value;
            $failure_value_two = $callbackurls->failure_value_two;
            $failure_value_three = $callbackurls->failure_value_three;
            $uniq_id = $callbackurls->uniq_id;
            $operator_ref = $callbackurls->operator_ref;
            $ip_address = $callbackurls->ip_address;

            $id = $request["$uniq_id"];
            $status = $request["$status_parameter"];
            $request_ip = request()->ip();

            if ($ip_address) {
                if ($ip_address == $request_ip) {

                } else {
                    return Response()->json(['status' => 'failure', 'message' => 'Invalid ip address']);
                }
            }

            if ($status == $success_value) {
                $status = 1;
                $txnid = $request["$operator_ref"];
            } elseif ($status == $failure_value_two) {
                $status = 2;
                $txnid = '';
            } elseif ($status == $failure_value_three) {
                $status = 2;
                $txnid = '';
            } elseif ($status == $failure_value) {
                $status = 2;
                $txnid = '';
            } else {
                $status = 3;
                $txnid = '';
            }
            if ($id != '' && $status != '') {
                $mode = "Call-back";
                $reports = Report::find($id);

                if ($reports->status_id == 1) {
                    return Response()->json(['status' => 'failure', 'message' => 'Transaction is success!']);
                }

                if ($reports->wallet_type == 1) {
                    $library = new RefundLibrary();
                    return $library->update_transaction($status, $txnid, $id, $mode);
                } elseif ($reports->wallet_type == 2) {
                    $library = new RefundLibrary();
                    $library->update_transaction_aeps($status, $txnid, $id, $mode);
                }
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Invalid URL']);
        }
    }

    function merchant_pay2all(Request $request)
    {
        Apiresponse::insertGetId(['message' => $request, 'api_type' => 1]);
        $id = $request->client_id;
        $status = $request->status_id;
        if ($status == 1) {
            $status = 1;
            $txnid = $request->utr;
        } elseif ($status == 2) {
            $status = 2;
            $txnid = '';
        } elseif ($status == 6) {
            $amount = $request->amount;
            $utr = $request->utr;
            $virtual_account_number = $request->virtual_account_number;
            $sender_name = $request->sender_name;
            return $this->update_auto_payemnt($amount, $utr, $virtual_account_number, $sender_name);
        } else {
            $status = 3;
            $txnid = '';
        }
        if ($id != '' && $status != '') {
            $mode = "Call-back";
            $reports = Report::find($id);
            if ($reports->wallet_type == 1) {
                $library = new RefundLibrary();
                return $library->update_transaction($status, $txnid, $id, $mode);
            } elseif ($reports->wallet_type == 2) {
                $library = new RefundLibrary();
                $library->update_transaction_aeps($status, $txnid, $id, $mode);
            }
        }
    }

    function update_auto_payemnt($amount, $utr, $virtual_account_number, $sender_name)
    {
        $request_ip = request()->ip();
        $host = $_SERVER['HTTP_HOST'];
        $companies = Company::where('company_website', $host)->first();
        if ($companies) {
            $icici_code = $companies->icici_code;
            $exploadnumber = explode($icici_code, $virtual_account_number);
            $mobile_number = $exploadnumber[1];
            $userdetails = User::where('mobile', $mobile_number)->first();
            if ($userdetails) {
                $reports = Report::where('txnid', $utr)->first();
                if ($reports) {
                    return Response()->json(['status' => 'failure', 'message' => 'duplicate utr number']);
                } else {
                    $provider_id = 262;
                    $scheme_id = $userdetails->scheme_id;
                    $library = new GetcommissionLibrary();
                    $commission = $library->get_commission($scheme_id, $provider_id, $amount);
                    $retailer = $commission['retailer'];

                    $user_id = $userdetails->id;
                    $opening_balance = $userdetails->balance->user_balance;

                    $increament_amount = $amount + $retailer;
                    Balance::where('user_id', $user_id)->increment('user_balance', $increament_amount);
                    Balance::where('user_id', $user_id)->update(['balance_alert' => 1]);
                    $balance = Balance::where('user_id', $user_id)->first();
                    $user_balance = $balance->user_balance;
                    $now = new \DateTime();
                    $ctime = $now->format('Y-m-d H:i:s');
                    $insert_id = Report::insertGetId([
                        'number' => $userdetails->mobile,
                        'provider_id' => $provider_id,
                        'amount' => $amount,
                        'api_id' => 0,
                        'status_id' => 6,
                        'created_at' => $ctime,
                        'user_id' => $user_id,
                        'profit' => $retailer,
                        'mode' => "WEB",
                        'txnid' => $utr,
                        'ip_address' => $request_ip,
                        'description' => $sender_name,
                        'opening_balance' => $opening_balance,
                        'total_balance' => $user_balance,
                        'credit_by' => $user_id,
                        'wallet_type' => 1,
                    ]);
                    $message = "Dear $userdetails->name Your Wallet Credited With Amount $amount Your Current balance is $user_balance $this->brand_name";
                    $template_id = 5;
                    $library = new SmsLibrary();
                    $library->send_sms($userdetails->mobile, $message, $template_id);
                    return Response()->json(['status' => 'success', 'message' => 'balance successfully updated']);
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'user does not exist']);
            }

        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Company not found']);
        }
    }

    function accosisPayout(Request $request)
    {
        Apiresponse::insertGetId(['message' => $request, 'api_type' => 4]);
        $exploadId = explode('00000000', $request->referenceId);
        $id = $exploadId[1];
        $status = $request->status;
        if ($status == 'SUCCESS' || $status == 'PROCESSED') {
            $status_id = 1;
            $txnid = $request->utr;
        } elseif ($status == 'FAILED') {
            $status_id = 2;
            $txnid = '';
        } else {
            $status_id = 3;
            $txnid = '';
        }
        if ($id != '' && $status_id != '') {
            $mode = "Call-back";
            $reports = Report::find($id);
            if ($reports->wallet_type == 1) {
                $library = new RefundLibrary();
                return $library->update_transaction($status_id, $txnid, $id, $mode);
            } elseif ($reports->wallet_type == 2) {
                $library = new RefundLibrary();
                $library->update_transaction_aeps($status_id, $txnid, $id, $mode);
            }
        }
    }

    function paywizeCallback(Request $request)
    {
        Apiresponse::insertGetId(['message' => $request, 'api_type' => 5]);
        $payload = $request->payload;
        $library = new PaywizeLibrary();
        $response = $library->decryptData($payload);
        $data = $response['data'];
        $sender_id = $data['sender_id'];
        $exmploadId = explode('marspay', $sender_id);
        $id = $exmploadId[1];
        $status = $data['status'];
        if ($status == 'Success') {
            $status_id = 1;
            $txnid = $data['utr_number'];
        } elseif ($status == 'Failed') {
            $status_id = 2;
            $txnid = '';
        } else {
            $status_id = 3;
            $txnid = '';
        }
        $mode = "Call-back";
        $library = new RefundLibrary();
        $library->update_transaction($status_id, $txnid, $id, $mode);
        return Response()->json(['status' => true, 'message' => 'callback received success']);
    }

    function pockethubPayout(Request $request)
    {
        Apiresponse::insertGetId(['message' => $request, 'api_type' => 5]);
    }

    function razorpayPayout(Request $request)
    {
        Apiresponse::insertGetId(['message' => $request, 'api_type' => 11]);
        $payload = $request->payload;
        $payout = $payload['payout']['entity'];
        $id = $payout['reference_id'];
        $status = $payout['status'];
        if ($status == 'processed') {
            $status = 1;
            $txnid = $payout['utr'];
        } else {
            $status = 3;
            $txnid = '';
        }
        if ($id != '' && $status != '') {
            $mode = "Call-back";
            $reports = Report::find($id);
            if ($reports->wallet_type == 1) {
                $library = new RefundLibrary();
                return $library->update_transaction($status, $txnid, $id, $mode);
            } elseif ($reports->wallet_type == 2) {
                $library = new RefundLibrary();
                $library->update_transaction_aeps($status, $txnid, $id, $mode);
            }
        }
    }

    function vtransactPayout(Request $request)
    {
        Apiresponse::insertGetId(['message' => $request, 'api_type' => 12]);
    }

    public function zigpayPayout(Request $request)
    {
      
        Log::info('ZigPay payout callback received slag.', [
            'request_received' => $request,
        ]);
        $ctime = now();
        $payload = $request->all();
        $jsonPayload = json_decode((string)$request->getContent(), true);
        if (!is_array($payload) || empty($payload)) {
            $payload = is_array($jsonPayload) ? $jsonPayload : [];
        }
        Log::info('ZigPay payout callback received', [
            'ip' => request()->ip(),
            'payload' => $payload,
        ]);

        Apiresponse::insertGetId([
            'message' => json_encode($payload),
            'api_type' => 15,
            'response_type' => 'call_back',
            'ip_address' => request()->ip(),
            'created_at' => $ctime,
        ]);

        $statusRaw = (string)($payload['status_id'] ?? $payload['status'] ?? $payload['api_status'] ?? $payload['transaction_status'] ?? '');
        $statusLower = strtolower(trim($statusRaw));
        if (in_array($statusLower, ['1', 'success', 'processed', 'completed'], true)) {
            $statusId = 1;
        } elseif (in_array($statusLower, ['2', 'failed', 'failure', 'rejected'], true)) {
            $statusId = 2;
        } else {
            $statusId = 3;
        }

        $clientRef = (string)($payload['client_id'] ?? $payload['RefID'] ?? $payload['RefId'] ?? $payload['ref_id'] ?? $payload['client_RefNo'] ?? $payload['Client_RefNo'] ?? $payload['reference_id'] ?? $payload['order_id'] ?? '');
        $utr = (string)($payload['utr'] ?? $payload['utR_RRN'] ?? $payload['rrn'] ?? $payload['banK_refno'] ?? $payload['bank_refno'] ?? '');
        $amount = (float)($payload['amount'] ?? 0);
        $reason = (string)($payload['message'] ?? $payload['responseMessage'] ?? '');

        $report = null;
        if ($clientRef !== '' && ctype_digit($clientRef)) {
            $report = Report::find((int)$clientRef);
        }
        if (!$report && $clientRef !== '') {
            $report = Report::where('client_id', $clientRef)->orderBy('id', 'DESC')->first();
        }
        if (!$report && $clientRef !== '') {
            $report = Report::where('payid', $clientRef)->orderBy('id', 'DESC')->first();
        }
        if (!$report && preg_match('/(\d{1,10})$/', $clientRef, $matches)) {
            $report = Report::find((int)$matches[1]);
        }

        if (!$report) {
            return response()->json(['status' => false, 'message' => 'Report not found'], 404);
        }

        $mode = "Call-back";
        $refundLibrary = new RefundLibrary();
        if ($report->wallet_type == 1) {
            $refundLibrary->update_transaction($statusId, $utr ?: $reason, $report->id, $mode);
        } elseif ($report->wallet_type == 2) {
            $refundLibrary->update_transaction_aeps($statusId, $utr ?: $reason, $report->id, $mode);
        }

        $member = Member::where('user_id', $report->user_id)->first();
        if (!empty($member->call_back_url)) {
            $userDetails = User::find($report->user_id);
            if ($userDetails) {
                $merchantStatus = $statusId === 1 ? 'success' : ($statusId === 2 ? 'failed' : 'pending');
                $queryParams = [
                    'status' => $merchantStatus,
                    'client_id' => $report->client_id ?: $report->id,
                    'amount' => $amount > 0 ? $amount : (float)$report->amount,
                    'utr' => $utr,
                    'txnid' => $report->id,
                ];
                $signatureString = http_build_query($queryParams);
                $queryParams['signature'] = hash_hmac('sha256', $signatureString, $userDetails->api_token);
                $url = $member->call_back_url . '?' . http_build_query($queryParams);
                try {
                    $response = Helpers::pay_curl_get($url);
                    Traceurl::insertGetId([
                        'user_id' => $report->user_id,
                        'url' => $url,
                        'number' => $userDetails->mobile ?? '',
                        'response_message' => $response,
                        'created_at' => $ctime,
                    ]);
                } catch (\Exception $e) {
                    Log::error('ZigPay payout callback forward failed', ['error' => $e->getMessage(), 'report_id' => $report->id]);
                }
            }
        }

        return response()->json(['status' => true, 'message' => 'Callback processed successfully']);
    }
    
    public function safepPayout(Request $request)
    {
        // Log the incoming callback request
        Apiresponse::insertGetId([
            'message' => json_encode($request->all()), 
            'api_type' => 14, // SafePay API ID
            'request_message' => 'SafePay Callback Received'
        ]);
        
        Log::info("S: [SafepPayout] Callback received", [
            'request' => $request->all()
        ]);
        
        try {
            // Extract data from callback
            $data = $request->data;
            
            if (!$data) {
                Log::error("S: [SafepPayout] No data in callback");
                return response()->json(['status' => false, 'message' => 'No data received'], 400);
            }
            
            $transaction = $data['transaction'] ?? [];
            $summary = $data['summary'] ?? [];
            
            // Get reference_id (this is your insert_id from transferNow)
            $id = $transaction['reference_id'] ?? '';
            $transactionStatus = $transaction['status'] ?? '';
            $transaction_id = $transaction['transaction_id'] ?? '';
            $utr = $summary['utr'] ?? '';
            
            Log::info("S: [SafepPayout] Processing callback", [
                'reference_id' => $id,
                'transaction_status' => $transactionStatus,
                'transaction_id' => $transaction_id,
                'utr' => $utr
            ]);
            
            // Map SafePay status to your internal status
            if ($transactionStatus == 'success') {
                $status = 1; // Success
                $txnid = $utr;
            } elseif (in_array($transactionStatus, ['initiate', 'pending'])) {
                $status = 3; // Pending
                $txnid = '';
            } else {
                $status = 2; // Failed
                $txnid = '';
            }
            
            if (empty($id)) {
                Log::error("S: [SafepPayout] Missing reference_id in callback");
                return response()->json(['status' => false, 'message' => 'Invalid reference_id'], 400);
            }
            
            $mode = "Call-back";
            $reports = Report::find($id);
            
            if (!$reports) {
                Log::error("S: [SafepPayout] Report not found", ['id' => $id]);
                return response()->json(['status' => false, 'message' => 'Report not found'], 404);
            }
            
            Log::info("S: [SafepPayout] Updating transaction", [
                'report_id' => $id,
                'wallet_type' => $reports->wallet_type,
                'status' => $status,
                'utr' => $txnid
            ]);
            
            // Update transaction based on wallet type
            if ($reports->wallet_type == 1) {
                $library = new RefundLibrary();
                $result = $library->update_transaction($status, $txnid, $id, $mode);
                
                Log::info("S: [SafepPayout] Transaction updated (wallet_type=1)", [
                    'report_id' => $id,
                    'result' => $result
                ]);
                
                return response()->json(['status' => true, 'message' => 'Callback processed successfully']);
                
            } elseif ($reports->wallet_type == 2) {
                $library = new RefundLibrary();
                $result = $library->update_transaction_aeps($status, $txnid, $id, $mode);
                
                Log::info("S: [SafepPayout] Transaction updated (wallet_type=2)", [
                    'report_id' => $id,
                    'result' => $result
                ]);
                
                return response()->json(['status' => true, 'message' => 'Callback processed successfully']);
            } else {
                Log::warning("S: [SafepPayout] Unknown wallet_type", [
                    'wallet_type' => $reports->wallet_type
                ]);
                
                return response()->json(['status' => false, 'message' => 'Invalid wallet type'], 400);
            }
            
        } catch (\Exception $e) {
            Log::error("S: [SafepPayout] Exception occurred", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['status' => false, 'message' => 'Internal server error'], 500);
        }
    }
    
}
