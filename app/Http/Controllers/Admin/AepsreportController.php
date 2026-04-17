<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use App\Library\MemberLibrary;
use App\Library\RefundLibrary;
use App\Report;
use App\Api;
use App\Status;
use App\Provider;
use App\User;
use App\Service;
use \Crypt;
use App\Commissionreport;
use App\Aepsreport;
use App\Library\PermissionLibrary;

class AepsreportController extends Controller
{

    function aeps_report (Request $request){
        // get staff permission
        if (Auth::User()->role_id == 2){
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['aeps_report_permission'];
            if (!$myPermission == 1){
                return redirect()->back();
            }
        }
        if ($request->fromdate && $request->todate) {
            $fromdate = $request->fromdate;
            $todate = $request->todate;
            $provider_id = $request->provider_id;
            $urls = url('admin/aeps-report-api').'?'.'fromdate='.$fromdate.'&todate='.$todate.'&provider_id='.$provider_id;
        } else {
            $fromdate = date('Y-m-d', time());
            $todate = date('Y-m-d', time());
            $provider_id = 0;
            $urls = url('admin/aeps-report-api').'?'.'fromdate='.$fromdate.'&todate='.$todate.'&provider_id='.$provider_id;
        }
        $data = array(
            'page_title' => 'Aeps Report',
            'fromdate' => $fromdate,
            'todate' => $todate,
            'provider_id' => $provider_id,
            'urls' => $urls
        );
        $apis = Api::get();
        $status = Status::get();
        $providers = Provider::whereIn('id', [318, 319, 320, 321, 322])->get();
        return view('admin.report.aeps_report', compact('apis','status', 'providers'))->with($data);
    }

    function aeps_report_api (Request $request){
        $fromdate = $request->get('fromdate');
        $todate =  $request->get('amp;todate');
        $provider_id =  $request->get('amp;provider_id');

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

        if ($provider_id == 0){
            $providers = Provider::whereIn('id', [318, 319, 320, 321, 322])->get(['id']);
        }else{
            $providers = Provider::where('id', $provider_id)->get(['id']);
        }

        $totalRecords = Report::select('count(*) as allcount')
            ->whereIn('user_id', $my_down_member)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('provider_id', $providers)
            ->count();

        $totalRecordswithFilter = Report::select('count(*) as allcount')
            ->whereIn('user_id', $my_down_member)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('number', 'like', '%' .$searchValue . '%')
            ->whereIn('provider_id', $providers)
            ->count();

        // Fetch records

        $records = Report::orderBy($columnName,$columnSortOrder)
            ->where('number', 'like', '%' .$searchValue . '%')
            ->whereIn('user_id', $my_down_member)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('provider_id', $providers)
            ->orderBy('id', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();
        $data_arr = array();
        foreach($records as $value){
            $aepsreports = Aepsreport::where('report_id', $value->id)->first();
            $aadhar_number = (empty($aepsreports)) ? '' :  $aepsreports->aadhar_number;
            $statement_url = url('admin/user-ledger-report').'/'.Crypt::encrypt($value->user_id);
            $data_arr[] = array(
                "id" => $value->id,
                "created_at" => "$value->created_at",
                "user" => '<a href="'.$statement_url.'">'.$value->user->name . ' '. $value->user->last_name.'</a>',
                "provider" => $value->provider->provider_name,
                "number" => $value->number,
                "txnid" => $value->txnid,
                "amount" => number_format($value->amount,2),
                "profit" => number_format($value->profit,2),
                "balance" => number_format($value->total_balance,2),
                "aadhar_number" => $aadhar_number,
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

    function payout_settlement (Request $request){
        // get staff permission
        if (Auth::User()->role_id == 2){
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['payout_settlement_permission'];
            if (!$myPermission == 1){
                return redirect()->back();
            }
        }
        if ($request->fromdate && $request->todate) {
            $fromdate = $request->fromdate;
            $todate = $request->todate;
            $urls = url('admin/payout-settlement-api').'?'.'fromdate='.$fromdate.'&todate='.$todate;
        } else {
            $fromdate = date('Y-m-d', time());
            $todate = date('Y-m-d', time());
            $urls = url('admin/payout-settlement-api').'?'.'fromdate='.$fromdate.'&todate='.$todate;
        }
        $data = array(
            'page_title' => 'Payout Settlement',
            'fromdate' => $fromdate,
            'todate' => $todate,
            'urls' => $urls
        );
        $apis = Api::get();
        $status = Status::get();
        return view('admin.report.payout_settlement', compact('apis','status'))->with($data);
    }

    function payout_settlement_api (Request $request){
        $fromdate = $request->get('fromdate');
        $todate =  $request->get('amp;todate');

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


        $providers = Provider::whereIn('id', [323, 324])->get(['id']);
        $totalRecords = Report::select('count(*) as allcount')
            ->whereIn('user_id', $my_down_member)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('provider_id', $providers)
            ->count();

        $totalRecordswithFilter = Report::select('count(*) as allcount')
            ->whereIn('user_id', $my_down_member)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('number', 'like', '%' .$searchValue . '%')
            ->whereIn('provider_id', $providers)
            ->count();

        // Fetch records

        $records = Report::orderBy($columnName,$columnSortOrder)
            ->where('number', 'like', '%' .$searchValue . '%')
            ->whereIn('user_id', $my_down_member)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('provider_id', $providers)
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
                "profit" => number_format($value->profit,2),
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

    function aeps_operator_report (Request $request){
        // get staff permission
        if (Auth::User()->role_id == 2){
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['aeps_operator_report_permission'];
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

        $provider_id = Provider::where('service_id', 13)->get(['id']);
        if ($child_id == 0){
            $reports = Report::whereIn('user_id', $my_down_member)
                ->where('status_id', 6)
                ->groupBy('provider_id')
                ->selectRaw('*, sum(amount) as total_amount, sum(profit) as total_profit, sum(distributor_comm) as total_dcomm, sum(super_distributor_comm) as total_sdcomm, sum(sales_team_comm) as total_stcomm, sum(white_label_comm) as total_wlcomm, sum(white_label_reseller_comm) as total_wlrcomm,  sum(company_staff) as total_cscomm, sum(api_comm) as total_api_comm, count(*) as all_count')
                ->orderBy('id', 'DESC')
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->whereIn('provider_id', $provider_id)
                ->whereNotIn('remark', ['profit'])
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
                ->whereNotIn('remark', ['profit'])
                ->get();

        }

        $users = User::whereIn('id', $my_down_member)->where('status_id', 1)->get();
        $apis = Api::get();
        $status = Status::whereIn('id', [1,2,3,4,5,6,7])->get();
        $data = array(
            'page_title' => 'Aeps Operator Wise Report',
            'fromdate' => $fromdate,
            'todate' => $todate,
            'child_id' => $child_id,
            'row_count' => count($reports),
        );
        return view('admin.report.aeps_operator_report', compact('reports','users','apis','status'))->with($data);
    }
}
