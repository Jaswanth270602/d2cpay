<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use App\Models\User;
use App\Models\Api;
use App\Models\Masterbank;
use App\Models\Purchase;
use App\Models\Balance;
use App\Models\Report;
use App\Models\Returnrequest;
use App\Models\Status;
use App\Models\Sitesetting;
use DB;
use Hash;
use Helpers;
use App\Library\MemberLibrary;
use App\Library\SmsLibrary;
use App\Library\PermissionLibrary;
use App\Library\LocationRestrictionsLibrary;

class TransferController extends Controller
{

    public function __construct()
    {
        $this->company_id = Helpers::company_id()->id;
        $companies = Helpers::company_id();
        $this->company_id = $companies->id;
        $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
        $this->brand_name = (empty($sitesettings)) ? '' : $sitesettings->brand_name;
        $this->provider_id = 326;
        $this->min_amount = 10;
        $this->max_amount = 1000000;
    }


    function purchase_balance(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            if ($request->fromdate && $request->todate) {
                $fromdate = $request->fromdate;
                $todate = $request->todate;
                $urls = url('admin/purchase-balance-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate;
            } else {
                $fromdate = date('Y-m-d', time());
                $todate = date('Y-m-d', time());
                $urls = url('admin/purchase-balance-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate;
            }
            $data = array(
                'page_title' => 'Purchase Balance',
                'report_slug' => 'Purchase Balance',
                'fromdate' => $fromdate,
                'todate' => $todate,
                'urls' => $urls
            );
            $apis = Api::where('status_id', 1)->select('id', 'api_name')->get();
            $banks = Masterbank::where('status_id', 1)->select('id', 'bank_name')->get();
            $status = Status::whereIn('id', [1, 2, 3, 4, 5, 6, 7])->select('id', 'status')->get();
            return view('admin.balance.purchase_balance', compact('apis', 'banks', 'status'))->with($data);
        } else {
            return redirect()->back();
        }
    }

    function purchase_balance_api(Request $request)
    {
        $fromdate = $request->get('fromdate');
        $todate = $request->get('amp;todate');

        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length"); // Rows display per page

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc
        $searchValue = $search_arr['value']; // Search value

        $totalRecords = Purchase::select('count(*) as allcount')
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->count();

        $totalRecordswithFilter = Purchase::select('count(*) as allcount')
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('utr', 'like', '%' . $searchValue . '%')
            ->count();

        // Fetch records
        $records = Purchase::orderBy($columnName, $columnSortOrder)
            ->where('utr', 'like', '%' . $searchValue . '%')
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->orderBy('id', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();
        $data_arr = array();
        foreach ($records as $value) {
            $apis = Api::find($value->api_id);
            $data_arr[] = array(
                "id" => $value->id,
                "created_at" => "$value->created_at",
                "user" => $value->user->name,
                "api_name" => (empty($apis)) ? '' : $apis->api_name,
                "bank_name" => $value->masterbank->bank_name,
                "amount" => number_format($value->amount, 2),
                "utr" => $value->utr,
                "purchase_type" => $value->purchase_type,
                "status" => '<span class="' . $value->status->class . '">' . $value->status->status . '</span>',
            );
        }
        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordswithFilter,
            "aaData" => $data_arr
        );
        echo json_encode($response);
        exit;
    }


    function purchase_balance_now(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $rules = array(
                'api_id' => 'required',
                'masterbank_id' => 'required',
                'utr' => 'required',
                'amount' => 'required|numeric|between:' . $this->min_amount . ',' . $this->max_amount . '',
                'password' => 'required',

            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $api_id = $request->api_id;
            $masterbank_id = $request->masterbank_id;
            $utr = $request->utr;
            $amount = $request->amount;
            $password = $request->password;
            $user_id = Auth::id();
            $userdetail = User::find($user_id);
            $current_password = $userdetail->password;
            if (Hash::check($password, $current_password)) {
                $now = new \DateTime();
                $ctime = $now->format('Y-m-d H:i:s');
                Purchase::insertGetId([
                    'user_id' => Auth::id(),
                    'api_id' => $api_id,
                    'masterbank_id' => $masterbank_id,
                    'utr' => $utr,
                    'amount' => $amount,
                    'created_at' => $ctime,
                    'purchase_type' => 'Manual',
                    'status_id' => 1,
                ]);
                $balance = Balance::where('user_id', $user_id)->first();
                $opening_balance = $balance->user_balance;
                Balance::where('user_id', Auth::id())->increment('user_balance', $amount);
                $balance = Balance::where('user_id', $user_id)->first();
                $user_balance = $balance->user_balance;
                $provider_id = $this->provider_id;
                $request_ip = request()->ip();
                $description = "Purchase Balance  $amount";
                $insert_id = Report::insertGetId([
                    'number' => Auth::User()->mobile,
                    'provider_id' => $provider_id,
                    'amount' => $amount,
                    'api_id' => 0,
                    'status_id' => 6,
                    'created_at' => $ctime,
                    'user_id' => $user_id,
                    'profit' => 0,
                    'mode' => "WEB",
                    'ip_address' => $request_ip,
                    'description' => $description,
                    'opening_balance' => $opening_balance,
                    'total_balance' => $user_balance,
                    'credit_by' => $user_id,
                    'wallet_type' => 1,
                ]);
                return Response()->json(['status' => 'success', 'message' => 'Purchase balance successfully']);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Password is wrong']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permiision']);
        }
    }

    function balance_transfer()
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['balance_transfer_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 7) {
            $data = array('page_title' => 'Balance Transfer');
            return view('admin.balance.balance_trasnfer')->with($data);
        } else {
            return redirect()->back();
        }
    }

    function balance_transfer_api(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length"); // Rows display per page

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc
        $searchValue = $search_arr['value']; // Search value
        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);

        // Total records
        $totalRecords = User::select('count(*) as allcount')
            ->whereIn('id', $my_down_member)
            ->whereNotIn('id', [Auth::id()])
            ->count();

        $totalRecordswithFilter = User::select('count(*) as allcount')
            ->whereIn('id', $my_down_member)
            ->whereNotIn('id', [Auth::id()])
            ->where(function ($query) use ($searchValue) {
                $query->where('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('mobile', 'like', '%' . $searchValue . '%')
                    ->orWhere('email', 'like', '%' . $searchValue . '%');
            })->count();

        // Fetch records

        $records = User::orderBy($columnName, $columnSortOrder)
            ->whereNotIn('id', [Auth::id()])
            ->whereIn('id', $my_down_member)
            ->where(function ($query) use ($searchValue) {
                $query->where('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('mobile', 'like', '%' . $searchValue . '%')
                    ->orWhere('email', 'like', '%' . $searchValue . '%');
            })->skip($start)
            ->select('id', 'name', 'last_name', 'mobile', 'role_id', 'balance_id')
            ->take($rowperpage)
            ->get();

        $data_arr = array();
        $sno = $start + 1;
        foreach ($records as $value) {

            $data_arr[] = array(
                "id" => $value->id,
                "name" => $value->name . ' ' . $value->last_name,
                "mobile" => $value->mobile,
                "member_type" => $value->role->role_title,
                "normal_balance" => number_format($value->balance->user_balance, 2),
                "aeps_balance" => number_format($value->balance->aeps_balance, 2),
                "action" => '<button class="btn btn-danger btn-sm" onclick="view_users(' . $value->id . ')">View</button>',

            );
        }

        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordswithFilter,
            "aaData" => $data_arr
        );

        echo json_encode($response);
        exit;

    }

    function view_transfer_users(Request $request)
    {
        if (Auth::User()->role_id <= 7) {
            $id = $request->id;
            $users = User::where('id', $id)->first();
            if ($users) {
                $details = array(
                    'id' => $users->id,
                    'mobile' => $users->mobile,
                    'name' => $users->name . ' ' . $users->last_name,
                    'balance' => number_format($users->balance->user_balance, 2),
                    'aeps_balance' => number_format($users->balance->aeps_balance, 2),
                );
                return Response()->json([
                    'status' => 'success',
                    'details' => $details
                ]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'user not found']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'sorry not permission']);
        }
    }


    function balance_transfer_now(Request $request)
    {
        if (Auth::User()->role_id <= 7) {
            $rules = array(
                'id' => 'required',
                'remark' => 'required',
                'amount' => 'required|numeric|between:' . $this->min_amount . ',' . $this->max_amount . '',
                'confirm_amount' => "same:amount",
                'password' => 'required',
                'dupplicate_transaction' => 'required|unique:check_duplicates',
                'latitude' => 'required',
                'longitude' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }

            $child_id = $request->id;
            $remark = $request->remark;
            $amount = $request->amount;
            $password = $request->password;
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $user_id = Auth::id();
            $userdetail = User::find($user_id);
            $current_password = $userdetail->password;
            $locationrestrictionsLibrary = new LocationRestrictionsLibrary();
            $isLoginValid = $locationrestrictionsLibrary->loginRestrictions($user_id, $latitude, $longitude);
            if ($isLoginValid == 0) {
                $kilometer = Auth::User()->company->login_restrictions_km;
                return Response()->json(['status' => 'failure', 'message' => "You must be within $kilometer kilometer to access this service."]);
            }

            if (Hash::check($password, $current_password)) {
                DB::table('check_duplicates')->insert(['dupplicate_transaction' => $request->dupplicate_transaction]);
                $type = 1;
                if ($request->wallet_type == 2){
                    return $this->balanceTransferPyain($user_id, $child_id, $amount, $remark, $type, $latitude, $longitude);
                }
                return $this->transfer_middle($user_id, $child_id, $amount, $remark, $type, $latitude, $longitude);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Password is wrong']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'sorry not permission']);
        }
    }

    function balance_trasnfer_application(Request $request)
    {
        if (Auth::User()->role_id <= 7) {
            $rules = array(
                'id' => 'required',
                'remark' => 'required',
                'amount' => 'required|numeric|between:' . $this->min_amount . ',' . $this->max_amount . '',
                'password' => 'required',
                'latitude' => 'required',
                'longitude' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'message' => 'validation errors', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $child_id = $request->id;
            $remark = $request->remark;
            $amount = $request->amount;
            $password = $request->password;
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $user_id = Auth::id();
            $userdetail = User::find($user_id);
            $current_password = $userdetail->password;
            $locationrestrictionsLibrary = new LocationRestrictionsLibrary();
            $isLoginValid = $locationrestrictionsLibrary->loginRestrictions($user_id, $latitude, $longitude);
            if ($isLoginValid == 0) {
                $kilometer = Auth::User()->company->login_restrictions_km;
                return Response()->json(['status' => 'failure', 'message' => "You must be within $kilometer kilometer to access this service."]);
            }
            if (Hash::check($password, $current_password)) {
                $type = 1;
                return $this->transfer_middle($user_id, $child_id, $amount, $remark, $type, $latitude, $longitude);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Password is wrong']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'sorry not permission']);
        }
    }

    function transfer_middle($user_id, $child_id, $amount, $remark, $type, $latitude, $longitude)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['balance_transfer_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'sorry not permission']);
            }
        }
        $userdetails = User::find($user_id);
        if ($userdetails->active == 1) {
            $childdetails = User::find($child_id);
            $opening_balance = $userdetails->balance->user_balance;
            $sumamount = $amount + $userdetails->lock_amount + $userdetails->balance->lien_amount;
            if ($opening_balance >= $sumamount && $sumamount >= 4) {
                DB::beginTransaction();
                try {
                    $now = new \DateTime();
                    $ctime = $now->format('Y-m-d H:i:s');
                    Balance::where('user_id', $user_id)->decrement('user_balance', $amount);
                    $balance = Balance::where('user_id', $user_id)->first();
                    $user_balance = $balance->user_balance;
                    $provider_id = $this->provider_id;
                    $request_ip = request()->ip();
                    $description = "Tansfer to  $childdetails->name $childdetails->last_name";
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
                        'latitude' => $latitude,
                        'longitude' => $longitude,
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

                    $description = "Transfer by $userdetails->name $userdetails->last_name";
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
                    DB::commit();
                    $message = "Dear $childdetails->name Your Wallet Credited With Amount $amount Your Current balance is $child_balance $this->brand_name";
                    $template_id = 5;
                    $library = new SmsLibrary();
                    $library->send_sms($childdetails->mobile, $message, $template_id);
                    return Response()->json(['status' => 'success', 'message' => 'Balance successfully trasnfered']);
                } catch (\Exception $ex) {
                    DB::rollback();
                    // throw $ex;
                    return response()->json(['status' => 'failure', 'message' => $ex->getMessage()]);
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Your balance is low kindly refill your wallet']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => $userdetails->reason]);
        }
    }


    function balanceTransferPyain($user_id, $child_id, $amount, $remark, $type, $latitude, $longitude)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['balance_transfer_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'sorry not permission']);
            }
        }
        $userdetails = User::find($user_id);
        if ($userdetails->active == 1) {
            $childdetails = User::find($child_id);
            $opening_balance = $userdetails->balance->user_balance;
            $sumamount = $amount + $userdetails->lock_amount + $userdetails->balance->lien_amount;
            if ($opening_balance >= $sumamount && $sumamount >= 4) {
                DB::beginTransaction();
                try {
                    $now = new \DateTime();
                    $ctime = $now->format('Y-m-d H:i:s');
                    Balance::where('user_id', $user_id)->decrement('user_balance', $amount);
                    $balance = Balance::where('user_id', $user_id)->first();
                    $user_balance = $balance->user_balance;
                    $provider_id = $this->provider_id;
                    $request_ip = request()->ip();
                    $description = "Tansfer to  $childdetails->name $childdetails->last_name";
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
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                    ]);
                    $message = "Dear $userdetails->name Your Wallet Debited With Amount $amount Your Current balance is $user_balance $this->brand_name";
                    $template_id = 4;
                    $library = new SmsLibrary();
                    $library->send_sms($userdetails->mobile, $message, $template_id);

                    // child update
                    $child_opening_balance = $childdetails->balance->aeps_balance;
                    Balance::where('user_id', $child_id)->increment('aeps_balance', $amount);
                    //Balance::where('user_id', $child_id)->update(['balance_alert' => 1]);
                    $childbalance = Balance::where('user_id', $child_id)->first();
                    $child_balance = $childbalance->aeps_balance;

                    $description = "Transfer by $userdetails->name $userdetails->last_name";
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
                        'wallet_type' => 2,
                    ]);
                    DB::commit();
                    $message = "Dear $childdetails->name Your Wallet Credited With Amount $amount Your Current balance is $child_balance $this->brand_name";
                    $template_id = 5;
                    $library = new SmsLibrary();
                    $library->send_sms($childdetails->mobile, $message, $template_id);
                    return Response()->json(['status' => 'success', 'message' => 'Balance successfully trasnfered']);
                } catch (\Exception $ex) {
                    DB::rollback();
                    // throw $ex;
                    return response()->json(['status' => 'failure', 'message' => $ex->getMessage()]);
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Your balance is low kindly refill your wallet']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => $userdetails->reason]);
        }
    }


    function balance_return()
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['balance_return_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2) {
            $data = array('page_title' => 'Balance Return');
            return view('admin.balance.balance_return')->with($data);
        } else {
            return Redirect::back();
        }
    }

    function balance_return_now(Request $request)
    {
        if (Auth::User()->role_id <= 7) {
            $rules = array(
                'id' => 'required',
                'remark' => 'required',
                'amount' => 'required|numeric|between:' . $this->min_amount . ',' . $this->max_amount . '',
                'confirm_amount' => "same:amount",
                'password' => 'required',
                'dupplicate_transaction' => 'required|unique:check_duplicates',
                'latitude' => 'required',
                'longitude' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $child_id = $request->id;
            $remark = $request->remark;
            $amount = $request->amount;
            $password = $request->password;
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $user_id = Auth::id();
            $userdetail = User::find($user_id);
            $current_password = $userdetail->password;
            $locationrestrictionsLibrary = new LocationRestrictionsLibrary();
            $isLoginValid = $locationrestrictionsLibrary->loginRestrictions($user_id, $latitude, $longitude);
            if ($isLoginValid == 0) {
                $kilometer = Auth::User()->company->login_restrictions_km;
                return Response()->json(['status' => 'failure', 'message' => "You must be within $kilometer kilometer to access this service."]);
            }
            if (Hash::check($password, $current_password)) {
                DB::table('check_duplicates')->insert(['dupplicate_transaction' => $request->dupplicate_transaction]);
                if (Auth::User()->role_id <= 2) {
                    return $this->return_middle($user_id, $child_id, $amount, $remark, $latitude, $longitude);
                } else {
                    return $this->return_request($user_id, $child_id, $amount, $remark, $latitude, $longitude);
                }

            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Password is wrong']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'sorry not permission']);
        }
    }

    function return_middle($user_id, $child_id, $amount, $remark, $latitude, $longitude)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['balance_return_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'sorry not permission']);
            }
        }
        $childdetails = User::find($child_id);
        $userdetails = User::find($user_id);
        $opening_balance = $childdetails->balance->user_balance;
        $sumamount = $amount + $childdetails->lock_amount + $childdetails->balance->lien_amount;
        if ($opening_balance >= $sumamount && $sumamount >= 4) {
            DB::beginTransaction();
            try {
                $now = new \DateTime();
                $ctime = $now->format('Y-m-d H:i:s');
                Balance::where('user_id', $child_id)->decrement('user_balance', $amount);
                $balance = Balance::where('user_id', $child_id)->first();
                $user_balance = $balance->user_balance;
                $provider_id = $this->provider_id;
                $request_ip = request()->ip();
                $description = "Debit by  $userdetails->name $userdetails->last_name";
                $insert_id = Report::insertGetId([
                    'number' => $userdetails->mobile,
                    'provider_id' => $provider_id,
                    'amount' => $amount,
                    'api_id' => 0,
                    'status_id' => 7,
                    'created_at' => $ctime,
                    'user_id' => $child_id,
                    'profit' => 0,
                    'mode' => "WEB",
                    'txnid' => $remark,
                    'ip_address' => $request_ip,
                    'description' => $description,
                    'opening_balance' => $opening_balance,
                    'total_balance' => $user_balance,
                    'credit_by' => $user_id,
                    'wallet_type' => 1,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ]);
                $message = "Dear $childdetails->name Your Wallet Debited With Amount $amount Your Current balance is $user_balance $this->brand_name";
                $template_id = 6;
                $library = new SmsLibrary();
                $library->send_sms($childdetails->mobile, $message, $template_id);

                $useropning_balance = $userdetails->balance->user_balance;
                Balance::where('user_id', $user_id)->increment('user_balance', $amount);
                $userbalance = Balance::where('user_id', $user_id)->first();
                $total_balance = $userbalance->user_balance;
                $description = "Credit by  $childdetails->name $childdetails->last_name";
                $insert_id = Report::insertGetId([
                    'number' => $childdetails->mobile,
                    'provider_id' => $provider_id,
                    'amount' => $amount,
                    'api_id' => 0,
                    'status_id' => 6,
                    'created_at' => $ctime,
                    'user_id' => $user_id,
                    'profit' => 0,
                    'mode' => "WEB",
                    'txnid' => $remark,
                    'ip_address' => $request_ip,
                    'description' => $description,
                    'opening_balance' => $useropning_balance,
                    'total_balance' => $total_balance,
                    'credit_by' => $child_id,
                    'wallet_type' => 1,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ]);
                DB::commit();
                $message = "Dear $userdetails->name Your Wallet Credited With Amount $amount Your Current balance is $total_balance $this->brand_name";
                $template_id = 7;
                $library = new SmsLibrary();
                $library->send_sms($userdetails->mobile, $message, $template_id);
                return Response()->json(['status' => 'success', 'message' => 'balance return successfull']);
            } catch (\Exception $ex) {
                DB::rollback();
                // throw $ex;
                return response()->json(['status' => 'failure', 'message' => $ex->getMessage()]);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'User balance is low']);
        }
    }

    function return_request($user_id, $child_id, $amount, $remark)
    {
        $childdetails = User::find($child_id);
        $opening_balance = $childdetails->balance->user_balance;
        $sumamount = $amount + $childdetails->lock_amount + $childdetails->balance->lien_amount;
        if ($opening_balance >= $sumamount && $sumamount >= 4) {
            Balance::where('user_id', $child_id)->increment('lien_amount', $amount);
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            Returnrequest::insertGetId([
                'user_id' => $child_id,
                'amount' => $amount,
                'remark' => $remark,
                'parent_id' => $user_id,
                'created_at' => $ctime,
                'status_id' => 3,
            ]);
            return Response()->json(['status' => 'success', 'message' => 'Return request successfully submited']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'User balance is low']);
        }
    }


    function balance_return_request()
    {
        if (Auth::User()->role_id <= 7) {
            $returnrequest = Returnrequest::where('user_id', Auth::id())->where('status_id', 3)->get();
            $data = array('page_title' => 'Balance Return Request');
            return view('admin.balance.balance_return_request', compact('returnrequest'))->with($data);
        } else {
            return Redirect::back();
        }
    }

    function view_return_request(Request $request)
    {
        $id = $request->id;
        $returnrequest = Returnrequest::where('id', $id)->where('user_id', Auth::id())->first();
        if ($returnrequest) {

            $parent_name = User::find($returnrequest->parent_id)->name;
            $details = array(
                'id' => $returnrequest->id,
                'parent_name' => $parent_name,
                'amount' => number_format($returnrequest->amount, 2),
                'remark' => $returnrequest->remark,
                'status_id' => $returnrequest->status_id,
            );
            return Response()->json([
                'status' => 'success',
                'details' => $details
            ]);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'record not found']);
        }
    }

    function approve_payment_return_request(Request $request)
    {
        $rules = array(
            'id' => 'required',
            'password' => 'required',
            'status_id' => 'required',
            'dupplicate_transaction' => 'required|unique:check_duplicates',
            'latitude' => 'required',
            'longitude' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $id = $request->id;
        $password = $request->password;
        $status_id = $request->status_id;
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $user_id = Auth::id();
        $userdetail = User::find($user_id);
        $current_password = $userdetail->password;
        $locationrestrictionsLibrary = new LocationRestrictionsLibrary();
        $isLoginValid = $locationrestrictionsLibrary->loginRestrictions($user_id, $latitude, $longitude);
        if ($isLoginValid == 0) {
            $kilometer = Auth::User()->company->login_restrictions_km;
            return Response()->json(['status' => 'failure', 'message' => "You must be within $kilometer kilometer to access this service."]);
        }
        if (Hash::check($password, $current_password)) {
            $returnrequest = Returnrequest::where('id', $id)->where('status_id', 3)->first();
            if ($returnrequest) {
                if ($status_id == 1) {
                    DB::table('check_duplicates')->insert(['dupplicate_transaction' => $request->dupplicate_transaction]);
                    $child_id = $returnrequest->parent_id;
                    $amount = $returnrequest->amount;
                    $remark = $returnrequest->remark;
                    $type = 2;

                    $userdetails = User::find($user_id);
                    $opening_balance = $userdetails->balance->user_balance;
                    $sumamount = $amount + $userdetails->lock_amount;
                    if ($opening_balance >= $sumamount && $sumamount >= 4) {
                        Returnrequest::where('id', $id)->update(['status_id' => 1]);
                        Balance::where('user_id', $user_id)->decrement('lien_amount', $amount);
                        return $this->transfer_middle($user_id, $child_id, $amount, $remark, $type, $latitude, $longitude);
                    } else {
                        return Response()->json(['status' => 'failure', 'message' => 'Your Balance is low']);
                    }
                } else {
                    $amount = $returnrequest->amount;
                    $user_id = $returnrequest->user_id;
                    Balance::where('user_id', $user_id)->decrement('lien_amount', $amount);
                    Returnrequest::where('id', $id)->update(['status_id' => 2]);
                    return Response()->json(['status' => 'success', 'message' => 'request successfully rejected']);
                }

            } else {
                return Response()->json(['status' => 'failure', 'message' => 'record not found']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Password is wrong']);
        }

    }

    function payinToPayout()
    {
        if (Auth::User()->role_id == 1) {
            $data = array('page_title' => 'Payin To Payout');
            return view('admin.balance.payinToPayout')->with($data);
        } else {
            return Redirect::back();
        }
    }

    function payinToPayoutStore(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $rules = array(
                'id' => 'required',
                'remark' => 'required',
                'amount' => 'required|numeric|between:' . $this->min_amount . ',' . $this->max_amount . '',
                'confirm_amount' => "same:amount",
                'password' => 'required',
                'dupplicate_transaction' => 'required|unique:check_duplicates',
                'latitude' => 'required',
                'longitude' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $child_id = $request->id;
            $remark = $request->remark;
            $amount = $request->amount;
            $password = $request->password;
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $user_id = Auth::id();
            $userdetail = User::find($user_id);
            $current_password = $userdetail->password;
            $locationrestrictionsLibrary = new LocationRestrictionsLibrary();
            $isLoginValid = $locationrestrictionsLibrary->loginRestrictions($user_id, $latitude, $longitude);
            if ($isLoginValid == 0) {
                $kilometer = Auth::User()->company->login_restrictions_km;
                return Response()->json(['status' => 'failure', 'message' => "You must be within $kilometer kilometer to access this service."]);
            }
            if (Hash::check($password, $current_password)) {
                DB::table('check_duplicates')->insert(['dupplicate_transaction' => $request->dupplicate_transaction]);
                $provider_id = 336;
                $request_ip = request()->ip();
                $childDetails = User::find($child_id);
                $apesOpeningBal = $childDetails->balance->aeps_balance;
                $sumamount = $amount + $childDetails->lock_amount;
                if ($apesOpeningBal >= $sumamount && $sumamount >= 1) {
                    Balance::where('user_id', $child_id)->decrement('aeps_balance', $amount);
                    $balance = Balance::where('user_id', $child_id)->first();
                    $aeps_balance = $balance->aeps_balance;
                    $description = "Tansfer to  $childDetails->name";
                    $now = new \DateTime();
                    $ctime = $now->format('Y-m-d H:i:s');
                    Report::insertGetId([
                        'number' => $childDetails->mobile,
                        'provider_id' => $provider_id,
                        'amount' => $amount,
                        'api_id' => 0,
                        'status_id' => 7,
                        'created_at' => $ctime,
                        'user_id' => $child_id,
                        'profit' => 0,
                        'mode' => 'WEB',
                        'txnid' => $remark,
                        'ip_address' => $request_ip,
                        'description' => $description,
                        'opening_balance' => $apesOpeningBal,
                        'total_balance' => $aeps_balance,
                        'credit_by' => $child_id,
                        'wallet_type' => 2,
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                    ]);
                    //increment in payout wallet
                    $opening_balance = $childDetails->balance->user_balance;
                    Balance::where('user_id', $child_id)->increment('user_balance', $amount);
                    $balance = Balance::where('user_id', $child_id)->first();
                    $user_balance = $balance->user_balance;
                    $description = "Tansfer By  $childDetails->name";
                    Report::insertGetId([
                        'number' => $childDetails->mobile,
                        'provider_id' => $provider_id,
                        'amount' => $amount,
                        'api_id' => 0,
                        'status_id' => 6,
                        'created_at' => $ctime,
                        'user_id' => $child_id,
                        'profit' => 0,
                        'mode' => 'WEB',
                        'txnid' => $remark,
                        'ip_address' => $request_ip,
                        'description' => $description,
                        'opening_balance' => $opening_balance,
                        'total_balance' => $user_balance,
                        'credit_by' => $child_id,
                        'wallet_type' => 1,
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                    ]);
                    return Response()->json(['status' => 'success', 'message' => 'Your funds have been successfully moved from your Payin Wallet to your Payout Wallet.']);
                } else {
                    return Response()->json(['status' => 'failure', 'message' => 'Insufficient fund in payin wallet!']);
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Password is wrong']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'sorry not permission']);
        }

    }

}

