<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use App\Report;
use App\Provider;
use App\Beneficiary;
use App\Api;
use App\Status;
use App\User;
use \Crypt;
use App\Library\MemberLibrary;
use App\Library\PermissionLibrary;

class MoneyreportController extends Controller
{
    function account_validate_report (Request $request){
        // get staff permission
        if (Auth::User()->role_id == 2){
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['account_validate_report_permission'];
            if (!$myPermission == 1){
                return redirect()->back();
            }
        }
        if ($request->fromdate && $request->todate) {
            $fromdate = $request->fromdate;
            $todate = $request->todate;
            $status_id = $request->status_id;
            $urls = url('admin/account-validate-report-api').'?'.'fromdate='.$fromdate.'&todate='.$todate.'&status_id='.$status_id;
        } else {
            $fromdate = date('Y-m-d', time());
            $todate = date('Y-m-d', time());
            $status_id = 0;
            $urls = url('admin/account-validate-report-api').'?'.'fromdate='.$fromdate.'&todate='.$todate.'&status_id='.$status_id;
        }
        $data = array(
            'page_title' => 'Account Validate',
            'fromdate' => $fromdate,
            'todate' => $todate,
            'status_id' => $status_id,
            'urls' => $urls
        );
        $apis = Api::get();
        $status = Status::get();
        $verificationstatus = Status::whereIn('id', [1,2,3,4,5])->get();
        return view('admin.report.account_validate_report', compact('apis', 'status','verificationstatus'))->with($data);
    }


    function account_validate_report_api (Request $request){
        $fromdate = $request->get('fromdate');
        $todate =  $request->get('amp;todate');
        $status_id =  $request->get('amp;status_id');

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
        if ($status_id == 0){
            $status_id = Status::get(['id']);
        }else{
            $status_id = Status::where('id', $status_id)->get(['id']);
        }

        $providers = Provider::whereIn('id', [315])->get(['id']);
        $totalRecords = Report::select('count(*) as allcount')
            ->whereIn('user_id', $my_down_member)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('status_id', $status_id)
            ->whereIn('provider_id', $providers)
            ->count();

        $totalRecordswithFilter = Report::select('count(*) as allcount')
            ->whereIn('user_id', $my_down_member)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('number', 'like', '%' .$searchValue . '%')
            ->whereIn('status_id', $status_id)
            ->whereIn('provider_id', $providers)
            ->count();

        // Fetch records

        $records = Report::orderBy($columnName,$columnSortOrder)
            ->where('number', 'like', '%' .$searchValue . '%')
            ->whereIn('user_id', $my_down_member)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('status_id', $status_id)
            ->whereIn('provider_id', $providers)
            ->select('user_id',  'id', 'created_at', 'provider_id', 'number', 'txnid',  'amount', 'profit',  'status_id', 'mode')
            ->orderBy('id', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();
        $data_arr = array();
        foreach($records as $value){
            $statement_url = url('admin/user-ledger-report').'/'.Crypt::encrypt($value->user_id);
            $data_arr[] = array(
                "id" => $value->id,
                "created_at" => "$value->created_at",
                "user" => '<a href="'.$statement_url.'">'.$value->user->name . ' '. $value->user->last_name.'</a>',
                "provider" => $value->provider->provider_name,
                "number" => $value->number,
                "txnid" => $value->txnid,
                "amount" => number_format($value->amount,2),
                "status" => '<span class="'. $value->status->class.'">'. $value->status->status.'</span>',
                "view" => '<button class="btn btn-danger btn-sm" onclick="view_recharges('.$value->id .')"><i class="fas fa-eye"></i> View</button>',
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

    function money_transfer_report (Request $request){
        // get staff permission
        if (Auth::User()->role_id == 2){
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['money_transfer_report_permission'];
            if (!$myPermission == 1){
                return redirect()->back();
            }
        }
        if ($request->fromdate && $request->todate) {
            $fromdate = $request->fromdate;
            $todate = $request->todate;
            $status_id = $request->status_id;
            $urls = url('admin/money-transfer-report-api').'?'.'fromdate='.$fromdate.'&todate='.$todate.'&status_id='.$status_id;
        } else {
            $fromdate = date('Y-m-d', time());
            $todate = date('Y-m-d', time());
            $status_id = 0;
            $urls = url('admin/money-transfer-report-api').'?'.'fromdate='.$fromdate.'&todate='.$todate.'&status_id='.$status_id;
        }
        $data = array(
            'page_title' => 'Money Transfer Report',
            'fromdate' => $fromdate,
            'todate' => $todate,
            'status_id' => $status_id,
            'urls' => $urls
        );
        $apis = Api::where('status_id', 1)->select('id', 'api_name')->get();
        $status = Status::whereIn('id', [1, 2, 3, 4, 5, 6, 7])->select('id', 'status')->get();
        $moneystatus = Status::whereIn('id', [1,2,3,5])->select('id','status')->get();
        return view('admin.report.money_transfer_report', compact('apis','status','moneystatus'))->with($data);
    }

    function money_transfer_report_api (Request $request){
        $fromdate = $request->get('fromdate');
        $todate =  $request->get('amp;todate');
        $status_id =  $request->get('amp;status_id');

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


        if ($status_id == 0){
            $status_id = Status::whereIn('id', [1,2,3,5])->get(['id']);
        }else{
            $status_id = Status::where('id', $status_id)->get(['id']);
        }
        $providers = Provider::whereIn('id', [316, 317])->get(['id']);
        $totalRecords = Report::select('count(*) as allcount')
            ->whereIn('user_id', $my_down_member)
            ->whereIn('status_id', $status_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('provider_id', $providers)
            ->count();

        $totalRecordswithFilter = Report::select('count(*) as allcount')
            ->whereIn('user_id', $my_down_member)
            ->whereIn('status_id', $status_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('number', 'like', '%' .$searchValue . '%')
            ->whereIn('provider_id', $providers)
            ->count();

        // Fetch records

        $records = Report::orderBy($columnName,$columnSortOrder)
            ->where('number', 'like', '%' .$searchValue . '%')
            ->whereIn('user_id', $my_down_member)
            ->whereIn('status_id', $status_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('provider_id', $providers)
            ->select('beneficiary_id', 'user_id', 'api_id', 'id', 'created_at', 'provider_id', 'number', 'txnid', 'opening_balance', 'amount', 'profit', 'total_balance', 'status_id', 'mode')
            ->orderBy('id', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();
        $data_arr = array();
        foreach($records as $value){
            $beneficiary = Beneficiary::find($value->beneficiary_id);
            $remiter_number = (empty($beneficiary)) ? '' :  $beneficiary->remiter_number;
            $bene_name = (empty($beneficiary)) ? '' :  $beneficiary->name;
            $bank_name = (empty($beneficiary)) ? '' :  $beneficiary->bank_name;
            $remiter_name = (empty($beneficiary)) ? '' :  $beneficiary->remiter_name;
            $payment_mode = ($value->channel == 2) ? 'IMPS' : 'NEFT';
            $statement_url = url('admin/user-ledger-report').'/'.Crypt::encrypt($value->user_id);
            $data_arr[] = array(
                "id" => $value->id,
                "created_at" => "$value->created_at",
                "users" => '<a href="'.$statement_url.'">'.$value->user->name . ' '. $value->user->last_name.'</a>',
                "provider" => $value->provider->provider_name,
                "number" => $value->number,
                "remiter_number" => $remiter_number,
                "remiter_name" => $remiter_name,
                "bene_name" => $bene_name,
                "bank_name" => $bank_name,
                "txnid" => $value->txnid,
                "amount" => number_format($value->amount, 2),
                "profit" => number_format($value->profit, 2),
                "payment_mode" => $payment_mode,
                "status" => '<span class="'. $value->status->class.'">'. $value->status->status.'</span>',
                "view" => '<button class="btn btn-danger btn-sm" onclick="view_recharges('.$value->id .')"><i class="fas fa-eye"></i> View</button>',
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

    function money_operator_report (Request $request){
        // get staff permission
        if (Auth::User()->role_id == 2){
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['money_operator_report_permission'];
            if (!$myPermission == 1){
                return redirect()->back();
            }
        }
        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);

        if ($request->fromdate && $request->todate) {
            $fromdate = $request->fromdate;
            $todate = $request->todate;
            $child_id = $request->child_id;
        } else {
            $fromdate = date('Y-m-d', time());
            $todate = date('Y-m-d', time());
            $child_id = 0;

        }

        $provider_id = Provider::whereIn('service_id', [16,17,18])->get(['id']);
        if ($child_id == 0){
            $reports = Report::whereIn('user_id', $my_down_member)
                ->where('status_id', 1)
                ->groupBy('provider_id')
                ->selectRaw('*, sum(amount) as total_amount, sum(profit) as total_profit, sum(distributor_comm) as total_dcomm, sum(super_distributor_comm) as total_sdcomm, sum(sales_team_comm) as total_stcomm, sum(white_label_comm) as total_wlcomm, sum(white_label_reseller_comm) as total_wlrcomm,  sum(company_staff) as total_cscomm, sum(api_comm) as total_api_comm, count(*) as all_count')
                ->orderBy('id', 'DESC')
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->whereIn('provider_id', $provider_id)
                ->get();
        }else{
            $reports = Report::whereIn('user_id', $my_down_member)
                ->where('status_id', 1)
                ->groupBy('provider_id')
                ->selectRaw('*, sum(amount) as total_amount, sum(profit) as total_profit, sum(distributor_comm) as total_dcomm, sum(super_distributor_comm) as total_sdcomm, sum(sales_team_comm) as total_stcomm, sum(white_label_comm) as total_wlcomm, sum(white_label_reseller_comm) as total_wlrcomm,  sum(company_staff) as total_cscomm, sum(api_comm) as total_api_comm, count(*) as all_count')
                ->orderBy('id', 'DESC')
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->where('user_id', $child_id)
                ->whereIn('provider_id', $provider_id)
                ->get();
        }
        $users = User::whereIn('id', $my_down_member)->where('status_id', 1)->get();
        $apis = Api::get();
        $status = Status::whereIn('id', [1,2,3,4,5,6,7])->get();
        $data = array(
            'page_title' => 'Money Operator Wise Report',
            'fromdate' => $fromdate,
            'todate' => $todate,
            'child_id' => $child_id,
            'row_count' => count($reports),
        );
        return view('admin.report.money_operator_report', compact('reports','users','apis','status'))->with($data);
    }
}
