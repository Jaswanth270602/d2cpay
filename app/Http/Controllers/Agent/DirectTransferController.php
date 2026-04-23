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
use Excel;
use App\Models\Api;
use App\Models\User;
use App\Models\Provider;
use App\Models\Balance;
use App\Models\Report;
use App\Models\Masterbank;
use App\Models\Accountvalidate;
use App\Models\Apiresponse;
use App\Models\Banktransferswitching;
use App\Models\Payoutbulkupload;

// library here
use App\Library\BasicLibrary;
use App\Library\GetcommissionLibrary;
use App\Library\Commission_increment;
use App\Library\LocationRestrictionsLibrary;
use App\Library\AccosisLibrary;
use App\Library\PaywizeLibrary;
use App\Library\SprezapayLibrary;
use App\Library\PockethubLibrary;
use App\Library\RazorpaypayoutLibrary;
use App\Library\PunjikendraLibrary;
use App\Library\VtransactLibrary;
use App\Library\SafepPayLibrary;
use App\Library\ZigPayLibrary;
use App\Imports\BulkUpload;
use App\Exports\BulkPayoutTemplateExport;

class DirectTransferController extends Controller
{

    public function __construct()
    {
        $this->verification_provider_id = 315;
        $this->provider_id = 324;
    }


    function welcome()
    {
        $user_id = Auth::id();
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($this->provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($serviceStatus == 1 && Auth::User()->role_id == 8) {
            $banks = Masterbank::where('status_id', 1)->select('id', 'bank_name')->get();
            $data = array('page_title' => 'Direct Transfer');
            return view('agent.direct-transfer.payoutTwo', compact('banks'))->with($data);
        } else {
            return redirect()->back();
        }
    }

    function bulkUpload()
    {
        $user_id = Auth::id();
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($this->provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($serviceStatus == 1) {
            $mt = explode(' ', microtime());
            $mili = ((int)$mt[1]) * 1000 + ((int)round($mt[0] * 1000));
            $data = array(
                'page_title' => 'Bulk Excel Upload',
                'timestamp' => $mili,
            );
            return view('agent.direct-transfer.bulkUpload')->with($data);
        } else {
            return redirect()->back();
        }
    }

    function downloadBulkTemplate()
    {
        return Excel::download(new BulkPayoutTemplateExport(), 'bulk_payout_upload.xlsx');
    }

    function getIfscCode(Request $request)
    {
        $rules = array(
            'bank_id' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $bank_id = $request->bank_id;
        $masterbanks = Masterbank::find($bank_id);
        if ($masterbanks) {
            $data = array('ifsc' => $masterbanks->ifsc);
            return Response()->json(['status' => 'success', 'message' => 'Successful..!', 'data' => $data]);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Record not found!']);
        }
    }

    function accountVerifyWeb(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',
            'ifsc_code' => 'required',
            'account_number' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $bank_id = $request->bank_id;
        $ifsc_code = $request->ifsc_code;
        $account_number = $request->account_number;
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $user_id = Auth::id();
        $client_id = '';
        $mode = "WEB";
        $locationrestrictionsLibrary = new LocationRestrictionsLibrary();
        $isLoginValid = $locationrestrictionsLibrary->loginRestrictions($user_id, $latitude, $longitude);
        if ($isLoginValid == 0) {
            $kilometer = Auth::User()->company->login_restrictions_km;
            return Response()->json(['status' => 'failure', 'message' => "You must be within $kilometer kilometer to access this service."]);
        }
        return Self::accountVerifyMiddle($mobile_number, $bank_id, $ifsc_code, $account_number, $latitude, $longitude, $user_id, $client_id, $mode);
    }

    function accountVerifyApi(Request $request)
    {
        $userId = Auth::id();
        $rules = array(
            'mobile_number' => 'required|digits:10',
            'ifsc_code' => 'required',
            'account_number' => 'required',
            'client_id' => ['required', new UniqueClientIdForUser($userId)],
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $bank_id = $request->bank_id;
        $ifsc_code = $request->ifsc_code;
        $account_number = $request->account_number;
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $user_id = Auth::id();
        $client_id = $request->client_id;
        $mode = "API";
        return Self::accountVerifyMiddle($mobile_number, $bank_id, $ifsc_code, $account_number, $latitude, $longitude, $user_id, $client_id, $mode);
    }

    function accountVerifyMiddle($mobile_number, $bank_id, $ifsc_code, $account_number, $latitude, $longitude, $user_id, $client_id, $mode)
    {
        $api_id = 1;
        $request_ip = request()->ip();
        $userdetails = User::find($user_id);
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($this->verification_provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($userdetails->company->server_down == 1 && $serviceStatus == 1) {
            $accountvalidates = Accountvalidate::where('account_number', $account_number)->where('api_id', $api_id)->first();
            if (!empty($accountvalidates)) {
                $data = array('beneficiary_name' => $accountvalidates->beneficiary_name);
                return Response()->json(['status' => 'success', 'message' => 'verifyed form our database', 'data' => $data]);
            }
            $provider_id = $this->verification_provider_id;
            $amount = 3;
            $scheme_id = $userdetails->scheme_id;
            $library = new GetcommissionLibrary();
            $commission = $library->get_commission($scheme_id, $provider_id, $amount);
            $retailer = $commission['retailer'];
            $d = $commission['distributor'];
            $sd = $commission['sdistributor'];
            $st = $commission['sales_team'];
            $rf = $commission['referral'];
            $amount = ($retailer <= 1) ? 3 : $retailer;
            $opening_balance = $userdetails->balance->user_balance;
            $sumamount = $amount + $userdetails->lock_amount + $userdetails->balance->lien_amount;
            if ($opening_balance >= $sumamount && $sumamount >= 1) {
                $providers = Provider::find($provider_id);
                Balance::where('user_id', $user_id)->decrement('user_balance', $amount);
                $balance = Balance::where('user_id', $user_id)->first();
                $user_balance = $balance->user_balance;
                $now = new \DateTime();
                $ctime = $now->format('Y-m-d H:i:s');
                $description = "$providers->provider_name  $account_number";
                $wallet_type = 1;
                $insert_id = Report::insertGetId([
                    'number' => $account_number,
                    'provider_id' => $provider_id,
                    'amount' => $amount,
                    'api_id' => $api_id,
                    'status_id' => 3,
                    'client_id' => $client_id,
                    'created_at' => $ctime,
                    'user_id' => $user_id,
                    'profit' => $retailer,
                    'mode' => $mode,
                    'ip_address' => $request_ip,
                    'description' => $description,
                    'opening_balance' => $opening_balance,
                    'total_balance' => $user_balance,
                    'wallet_type' => $wallet_type,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ]);
                if ($api_id == 1) {
                    $library = new Pay2allPayout();
                    $response = $library->accountVerify($mobile_number, $bank_id, $ifsc_code, $account_number, $insert_id, $api_id);
                } else {
                    Balance::where('user_id', $user_id)->increment('user_balance', $amount);
                    $balance = Balance::where('user_id', $user_id)->first();
                    $user_balance = $balance->user_balance;
                    Report::where('id', $insert_id)->update(['status_id' => 2, 'profit' => 0, 'total_balance' => $user_balance]);
                    return Response()->json(['status' => 'failure', 'message' => "You don't have permission to access this page"]);
                }
                $status_id = $response['status_id'];
                if ($status_id == 1) {
                    $name = $response['name'];
                    Report::where('id', $insert_id)->update(['status_id' => 1, 'txnid' => $name]);
                    Accountvalidate::insertGetId([
                        'account_number' => $account_number,
                        'ifsc_code' => $ifsc_code,
                        'beneficiary_name' => $name,
                        'created_at' => $ctime,
                        'status_id' => 1,
                        'api_id' => $api_id,
                    ]);
                    $library = new Commission_increment();
                    $library->parent_recharge_commission($user_id, $account_number, $insert_id, $provider_id, $amount, $api_id, $amount, $d, $sd, $st, $rf);
                    //get wise commission
                    $library = new GetcommissionLibrary();
                    $apiComms = $library->getApiCommission($api_id, $provider_id, $amount);
                    $apiCommission = $apiComms['apiCommission'];
                    $commissionType = $apiComms['commissionType'];
                    $library = new Commission_increment();
                    $library->updateApiComm($user_id, $provider_id, $api_id, $amount, $retailer, $d, $sd, $st, $rf, $apiCommission, $insert_id, $commissionType);
                    $data = array('beneficiary_name' => $name);
                    return Response()->json(['status' => 'success', 'message' => 'verifyed form vendor database', 'data' => $data]);
                } elseif ($status_id == 2) {
                    Balance::where('user_id', $user_id)->increment('user_balance', $amount);
                    $balance = Balance::where('user_id', $user_id)->first();
                    $user_balance = $balance->user_balance;
                    Report::where('id', $insert_id)->update(['status_id' => 2, 'profit' => 0, 'total_balance' => $user_balance]);
                    return Response()->json(['status' => 'failure', 'message' => 'Transaction failed. Please try again.']);
                } else {
                    $data = array('beneficiary_name' => '');
                    return Response()->json(['status' => 'pending', 'message' => 'Transaction in process', 'data' => $data]);
                }

            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Insufficient fund.']);
            }
        } else {
            $message = ($userdetails->company->server_down == 1) ? 'Service not active!' : $userdetails->company->server_message;
            return Response()->json(['status' => 'failure', 'message' => $message]);
        }
    }


    function bulkUploadStore(Request $request)
    {
        $request->validate([
            'dupplicate_transaction' => 'required|unique:check_duplicates',
            'excel_file' => 'required|mimes:xlsx,xls',
        ]);
        $file = $request->file('excel_file');
        // Process the Excel file
        $user_id = Auth::id();
        $uniqueId = random_int(1000000000, 9999999999) . $user_id; // Generates a UUID
        $import = new BulkUpload($uniqueId);
        Excel::import($import, $file);
        DB::table('check_duplicates')->insert(['dupplicate_transaction' => $request->dupplicate_transaction]);
        $payoutbulkuploads = Payoutbulkupload::where('user_id', $user_id)->where('bulk_id', $uniqueId)->get();
        if (count($payoutbulkuploads) == 0) {
            return redirect()->back()->with('failure', 'No valid rows found. Please use the sample template and fill at least one row.');
        }
        if (count($payoutbulkuploads) > 100) {
            Payoutbulkupload::where('user_id', $user_id)->where('bulk_id', $uniqueId)->delete();
            return redirect()->back()->with('failure', 'The file contains more than 100 rows. Please upload a smaller file.');
        }
        $processedRows = 0;
        $createdReportRows = 0;
        $skippedRows = 0;
        $failureReasons = [];
        foreach ($payoutbulkuploads as $value) {
            sleep(1);
            $mobile_number = $value->mobile_number;
            $email = $value->email;
            $beneficiary_name = $value->beneficiary_name;
            $ifsc_code = $value->ifsc_code;
            $account_number = $value->account_number;
            $amount = $value->amount;
            $uploadMode = strtoupper(trim((string)$value->mode));
            if (!in_array($uploadMode, ['IMPS', 'NEFT'])) {
                $skippedRows++;
                continue;
            }
            $channel_id = ($uploadMode == 'IMPS') ? 2 : 1;
            $client_id = '';
            $mode = 'Bulk Upload';
            $response = $this->transferNowMiddle($mobile_number, $email, $beneficiary_name, $ifsc_code, $account_number, $amount, $channel_id, $client_id, $mode, $user_id);
            $processedRows++;

            // Report row exists when transfer API returns a non-empty payid (insert_id).
            $payload = method_exists($response, 'getData') ? (array)$response->getData(true) : [];
            if (!empty($payload['payid'])) {
                $createdReportRows++;
            } else {
                $skippedRows++;
                $message = trim((string)($payload['message'] ?? ''));
                if ($message !== '') {
                    $failureReasons[] = $message;
                }
            }
        }
        Payoutbulkupload::where('user_id', $user_id)->where('bulk_id', $uniqueId)->delete();
        if ($processedRows == 0) {
            return redirect()->back()->with('failure', 'No rows were processed. Ensure mode is IMPS or NEFT in each row.');
        }
        if ($createdReportRows == 0) {
            $reasonText = '';
            if (!empty($failureReasons)) {
                $reasonText = ' Reason: ' . implode(' | ', array_unique($failureReasons));
            }
            return redirect()->back()->with('failure', 'Upload received, but no transaction was created. Please check balance/service status and file data.' . $reasonText);
        }

        return redirect()->back()->with(
            'success',
            "Bulk upload completed. Total rows: {$processedRows}, report entries created: {$createdReportRows}, skipped: {$skippedRows}."
        );

    }

    function trasnferNowWeb(Request $request)
    {
        $providers = Provider::find($this->provider_id);
        $rules = array(
            'mobile_number' => 'required|digits:10',
            //'email' => 'required|email',
            'beneficiary_name' => 'required',
            'ifsc_code' => 'required|min:11|max:11',
            'account_number' => 'required',
            'amount' => 'required|numeric|between:' . $providers->min_amount . ',' . $providers->max_amount . '',
            //'channel_id' => 'required',
            //'client_id' => 'required',
            //'client_id' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json([
                'status' => 'failure',
                'message' => $validator->messages()->first(),
                'utr' => $validator->messages()->first(),
                'payid' => '',
                'errors' => $validator->getMessageBag()->toArray(),
            ]);
        }
        $mobile_number = $request->mobile_number;
        $email = Auth::User()->email;
        $beneficiary_name = $request->beneficiary_name;
        $ifsc_code = $request->ifsc_code;
        $account_number = $request->account_number;
        $amount = $request->amount;
        $channel_id = 2;
        $client_id = '';
        $mode = 'WEB';
        $user_id = Auth::id();
        return Self::transferNowMiddle($mobile_number, $email, $beneficiary_name, $ifsc_code, $account_number, $amount, $channel_id, $client_id, $mode, $user_id);
    }

    function transfer_now_api(Request $request)
    {
        $providers = Provider::find($this->provider_id);
        $rules = array(
            'mobile_number' => 'required|digits:10',
            'email' => 'required|email',
            'beneficiary_name' => 'required',
            'ifsc_code' => 'required|min:11|max:11',
            'account_number' => 'required',
            'amount' => 'required|numeric|between:' . $providers->min_amount . ',' . $providers->max_amount . '',
            'channel_id' => 'required',
            'client_id' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json([
                'status' => 'failure',
                'message' => $validator->messages()->first(),
                'utr' => $validator->messages()->first(),
                'payid' => '',
                'errors' => $validator->getMessageBag()->toArray(),
            ]);
        }
        $mobile_number = $request->mobile_number;
        $email = $request->email;
        $beneficiary_name = $request->beneficiary_name;
        $ifsc_code = $request->ifsc_code;
        $account_number = $request->account_number;
        $amount = $request->amount;
        $channel_id = $request->channel_id;
        $client_id = $request->client_id;
        $mode = "API";
        $user_id = Auth::id();
        $request_ip = request()->ip();
        if (!empty(Auth::User()->member->ip_address)) {
            if (Auth::User()->member->ip_address != $request_ip) {
                return Response()->json(['status' => 'failure', 'message' => "Unauthorized ip address", 'utr' => $request_ip, 'payid' => '']);
            }
        }
        return Self::transferNowMiddle($mobile_number, $email, $beneficiary_name, $ifsc_code, $account_number, $amount, $channel_id, $client_id, $mode, $user_id);
    }


    function transferNowMiddle($mobile_number, $email, $beneficiary_name, $ifsc_code, $account_number, $amount, $channel_id, $client_id, $mode, $user_id)
    {
        $userdetails = User::find($user_id);
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($this->provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($userdetails->company->server_down == 1 && $serviceStatus == 1) {
            $opening_balance = $userdetails->balance->user_balance;
            $scheme_id = $userdetails->scheme_id;
            $provider_id = $this->provider_id;
            $providers = Provider::find($provider_id);
            $library = new GetcommissionLibrary();
            $commission = $library->get_commission($scheme_id, $provider_id, $amount);
            $retailer = ($commission['retailer'] == 0) ? 10 : $commission['retailer'];
            $distributor = $commission['distributor'];
            $sdistributor = $commission['sdistributor'];
            $sales_team = $commission['sales_team'];
            $referral = $commission['referral'];
            $sumamount = $amount + $retailer + $userdetails->lock_amount + $userdetails->balance->lien_amount;
            if ($opening_balance >= $sumamount && $sumamount >= 10) {
                $decrementAmount = $amount + $retailer;
                Balance::where('user_id', $user_id)->decrement('user_balance', $decrementAmount);
                $balances = Balance::where('user_id', $user_id)->first();
                $user_balance = $balances->user_balance;
                $api_id = $userdetails->company->payout_route;
                $banktransferswitching = Banktransferswitching::where('minimum_amount', '<=', $amount)
                    ->where('maximum_amount', '>=', $amount)
                    ->where(function ($query) use ($user_id) {
                        $query->where('user_id', $user_id)
                            ->orWhere('user_id', 0) // Handle case where user_id is 0
                            ->orWhereNull('user_id'); // Handle case where user_id might not be set
                    })->first();
                if ($banktransferswitching) {
                    $api_id = $banktransferswitching->api_id ?? $api_id;
                }

                $now = new \DateTime();
                $ctime = $now->format('Y-m-d H:i:s');
                $description = $beneficiary_name . ' | ' . $account_number;
                $row_data = array('mobile_number' => $mobile_number, 'email' => $email, 'account_number' => $account_number, 'ifsc_code' => $ifsc_code, 'beneficiary_name' => $beneficiary_name);
                $insert_id = Report::insertGetId([
                    'number' => $account_number,
                    'provider_id' => $provider_id,
                    'amount' => $amount,
                    'api_id' => $api_id,
                    'status_id' => 3,
                    'created_at' => $ctime,
                    'user_id' => $user_id,
                    'profit' => '-' . $retailer,
                    'mode' => $mode,
                    'ip_address' => request()->ip(),
                    'description' => $description,
                    'opening_balance' => $opening_balance,
                    'total_balance' => $user_balance,
                    'wallet_type' => 1,
                    'channel' => $channel_id,
                    'client_id' => $client_id,
                    'row_data' => json_encode($row_data),
                ]);
                if ($mode != 'API') {
                    Report::where('id', $insert_id)->update(['client_id' => $insert_id]);
                }

                $vender_id = 1;
                $latitude = '';
                $longitude = '';
                if ($api_id == 2) {
                    $response = Self::callmnppayApi($user_id, $mobile_number, $amount, $beneficiary_name, $account_number, $ifsc_code, $insert_id);
                    $status_id = $response['status_id'];
                    $utr = $response['txnid'];
                    $payid = $response['payid'];
                } elseif ($api_id == 4) {
                    $library = new AccosisLibrary();
                    $response = $library->impsTransfer($user_id, $mobile_number, $amount, $beneficiary_name, $account_number, $ifsc_code, $insert_id, $vender_id, $api_id, $latitude, $longitude);
                    $status_id = $response['status_id'];
                    $utr = $response['txnid'];
                    $payid = $response['payid'];
                } elseif ($api_id == 5) {
                    $library = new PaywizeLibrary();
                    $response = $library->transferNow($user_id, $mobile_number, $amount, $beneficiary_name, $account_number, $ifsc_code, $insert_id);
                    $status_id = $response['status_id'];
                    $utr = $response['txnid'];
                    $payid = $response['payid'];
                } elseif ($api_id == 6) {
                    $library = new SprezapayLibrary();
                    $response = $library->transferNow($user_id, $mobile_number, $amount, $beneficiary_name, $account_number, $ifsc_code, $insert_id);
                    $status_id = $response['status_id'];
                    $utr = $response['txnid'];
                    $payid = $response['payid'];
                } elseif ($api_id == 7) {
                    $library = new PockethubLibrary();
                    $response = $library->transferNow($user_id, $mobile_number, $amount, $beneficiary_name, $account_number, $ifsc_code, $insert_id);
                    $status_id = $response['status_id'];
                    $utr = $response['txnid'];
                    $payid = $response['payid'];
                } elseif ($api_id == 11) {
                    $library = new RazorpaypayoutLibrary();
                    $response = $library->transferNow($user_id, $mobile_number, $amount, $beneficiary_name, $account_number, $ifsc_code, $insert_id);
                    $status_id = $response['status_id'];
                    $utr = $response['txnid'];
                    $payid = $response['payid'];
                }elseif ($api_id == 10){
                    $library = new PunjikendraLibrary();
                    $response = $library->transferNow($user_id, $mobile_number, $amount, $beneficiary_name, $account_number, $ifsc_code, $insert_id);
                    $status_id = $response['status_id'];
                    $utr = $response['txnid'];
                    $payid = $response['payid'];
                }elseif ($api_id == 12){
                    $library = new VtransactLibrary();
                    $response = $library->transferNow($user_id, $mobile_number, $amount, $beneficiary_name, $account_number, $ifsc_code, $insert_id);
                    $status_id = $response['status_id'];
                    $utr = $response['txnid'];
                    $payid = $response['payid'];
                }elseif ($api_id == 14){
                    $library = new SafepPayLibrary();
                    $response = $library->transferNow($user_id, $mobile_number, $amount, $beneficiary_name, $account_number, $ifsc_code, $insert_id);
                    $status_id = $response['status_id'];
                    $utr = $response['txnid'];
                    $payid = $response['payid'];
                }elseif ($api_id == 15){
                    $library = new ZigPayLibrary();
                    $response = $library->transferNow($user_id, $mobile_number, $amount, $beneficiary_name, $account_number, $ifsc_code, $insert_id);
                    $status_id = $response['status_id'];
                    $utr = $response['txnid'];
                    $payid = $response['payid'];
                }else {
                    $status_id = 2;
                    $utr = '';
                    $payid = '';
                }
                if ($status_id == 1) {
                    Report::where('id', $insert_id)->update(['status_id' => 1, 'txnid' => $utr, 'payid' => $payid]);
                    $library = new Commission_increment();
                    $library->parent_recharge_commission($user_id, $account_number, $insert_id, $provider_id, $amount, $api_id, $retailer, $distributor, $sdistributor, $sales_team, $referral);
                    return Response()->json(['status' => 'success', 'message' => 'Your payout was successful! Thank you for using our service.', 'utr' => $utr, 'payid' => $insert_id]);
                } elseif ($status_id == 2) {
                    Balance::where('user_id', $user_id)->increment('user_balance', $decrementAmount);
                    $balance = Balance::where('user_id', $user_id)->first();
                    $user_balance = $balance->user_balance;
                    Report::where('id', $insert_id)->update(['status_id' => 2, 'reason' => $utr, 'total_balance' => $user_balance, 'payid' => $payid]);
                    $message = ($utr == 'Insufficient fund') ? 'Transaction Failed' : 'Transaction Failed. Please try again.`';
                    return Response()->json(['status' => 'failure', 'message' => $utr, 'utr' => '', 'payid' => $insert_id]);
                } else {
                    Report::where('id', $insert_id)->update(['payid' => $payid]);
                    return Response()->json(['status' => 'pending', 'message' => 'Your payout transaction is in process. Please wait for confirmation.', 'utr' => '', 'payid' => $insert_id]);
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Insufficient funds', 'utr' => '', 'payid' => '']);
            }
        } else {
            $message = ($userdetails->company->server_down == 1) ? 'Service not active!' : $userdetails->company->server_message;
            return Response()->json(['status' => 'failure', 'message' => $message, 'utr' => '', 'payid' => '']);
        }
    }

    function callmnppayApi($user_id, $mobile_number, $amount, $beneficiary_name, $account_number, $ifsc_code, $insert_id)
    {
        $api_id = 2;
        $userDetails = User::find($user_id);
        $apis = Api::find($api_id);
        $url = 'https://mnppay.in/api/payout/v1/transfer-now';
        $parameters = array(
            'api_token' => $apis->api_key ?? '',
            'mobile_number' => $mobile_number,
            'email' => $userDetails->email,
            'beneficiary_name' => $beneficiary_name,
            'ifsc_code' => $ifsc_code,
            'account_number' => $account_number,
            'amount' => $amount,
            'channel_id' => 2,
            'client_id' => $insert_id,
        );
        $method = 'POST';
        $header = ["Accept:application/json"];
        $response = Helpers::pay_curl_post($url, $header, $parameters, $method);
        Apiresponse::insertGetId(['message' => $response, 'api_type' => $api_id, 'report_id' => $insert_id, 'request_message' => $url . '?' . json_encode($parameters)]);
        $res = json_decode($response);
        $status = $res->status ?? 'pending';
        if ($status == 'success') {
            return ['status_id' => 1, 'txnid' => $res->utr ?? '', 'payid' => ''];
        } elseif ($status == 'failure') {
            return ['status_id' => 2, 'txnid' => '', 'payid' => ''];
        } else {
            return ['status_id' => 3, 'txnid' => '', 'payid' => ''];
        }

    }
}
