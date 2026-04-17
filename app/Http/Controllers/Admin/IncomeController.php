<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use App\Models\User;
use App\Models\Role;
use App\Models\Scheme;
use App\Models\State;
use App\Models\Balance;
use App\Models\Profile;
use App\Models\Member;
use App\Models\Company;
use App\Models\Api;
use App\Models\Status;
use Hash;
use App\Models\District;
use App\Models\Report;
use App\Models\Provider;
use \Crypt;
use Str;
use Carbon;
use Illuminate\Support\Facades\Cache;
use App\Library\MemberLibrary;
use App\Library\SmsLibrary;
use App\Library\PermissionLibrary;

class IncomeController extends Controller
{
    function user_income (Request $request, $role_slug){
        $roles = Role::where('role_slug', $role_slug)->first();
        if ($roles){
            if ($roles->id > Auth::User()->role_id){
                if ($request->fromdate && $request->todate) {
                    $fromdate = $request->fromdate;
                    $todate = $request->todate;
                    $urls = url('admin/income/user-income-api').'/'.$role_slug.'?'.'fromdate='.$fromdate.'&todate='.$todate;
                } else {
                    $fromdate = date('Y-m-d', time());
                    $todate = date('Y-m-d', time());
                    $urls = url('admin/income/user-income-api').'/'.$role_slug.'?'.'fromdate='.$fromdate.'&todate='.$todate;
                }
                $data = array(
                    'page_title' => 'Income Report',
                    'fromdate' => $fromdate,
                    'todate' => $todate,
                    'urls' => $urls,
                    'role_slug' => $role_slug,
                );
                return view('admin.income.user_income')->with($data);
            }else{
                return Redirect::back();
            }

        }else{
            return Redirect::back();
        }
    }

    function user_income_api (Request $request, $role_slug){
        $roles = Role::where('role_slug', $role_slug)->first();
        if ($roles){
            $role_id = $roles->id;
        }else{
            $role_id = 8;
        }
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


        $myrole = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($myrole, $company_id, $user_id);

        $totalRecords = User::select('count(*) as allcount')
            ->whereIn('id', $my_down_member)
            ->where('role_id', $role_id)
            ->count();

        $totalRecordswithFilter = User::select('count(*) as allcount')
            ->whereIn('id', $my_down_member)
            ->where('role_id', $role_id)
            ->where('mobile', 'like', '%' .$searchValue . '%')
            ->count();

        // Fetch records

        $records = User::orderBy($columnName,$columnSortOrder)
            ->where('mobile', 'like', '%' .$searchValue . '%')
            ->whereIn('id', $my_down_member)
            ->where('role_id', $role_id)
            ->skip($start)
            ->take($rowperpage)
            ->get();
        $data_arr = array();
        foreach($records as $value){
            // opening balance
            $openingbalance = Report::where('user_id', $value->id)
                ->whereDate('created_at',  '<', $fromdate)
                ->where('wallet_type', 1)
                ->orderBy('id', 'DESC')
                ->first();
            if ($openingbalance){
                $opening_bal = number_format($openingbalance->total_balance,2);
            }else{
                $opening_bal = 0;
            }

            // credit amount
            if ($value->role_id == 8 || $value->role_id == 9 || $value->role_id == 10){
                $credit = Report::where('user_id', $value->id)
                    ->whereDate('created_at', '>=', $fromdate)
                    ->whereDate('created_at', '<=', $todate)
                    ->where('wallet_type', 1)
                    ->where('status_id', 6)
                    ->sum('amount');
            }else{
                $credit = Report::where('user_id', $value->id)
                    ->whereDate('created_at', '>=', $fromdate)
                    ->whereDate('created_at', '<=', $todate)
                    ->where('wallet_type', 1)
                    ->where('status_id', 6)
                    ->whereNotIn('remark', ['remark'])
                    ->sum('amount');
            }


            // debit amout
            $debit = Report::where('user_id', $value->id)
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->where('wallet_type', 1)
                ->where('status_id', 7)
                ->sum('amount');

            // sales
            $sales = Report::where('user_id', $value->id)
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->where('wallet_type', 1)
                ->where('status_id', 1)
                ->sum('amount');

            // profit
            if ($value->role_id == 8 || $value->role_id == 9 || $value->role_id == 10){
                $profit = Report::where('user_id', $value->id)
                    ->whereDate('created_at', '>=', $fromdate)
                    ->whereDate('created_at', '<=', $todate)
                    ->where('wallet_type', 1)
                    ->where('profit', '>', 0)
                    ->where('status_id', 1)
                    ->sum('profit');
            }else{
                $profit = Report::where('user_id', $value->id)
                    ->whereDate('created_at', '>=', $fromdate)
                    ->whereDate('created_at', '<=', $todate)
                    ->where('wallet_type', 1)
                    ->where('profit', '>', 0)
                    ->where('status_id', 6)
                    ->where('remark', 'profit')
                    ->sum('profit');
            }


            // chages
            $charges = Report::where('user_id', $value->id)
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->where('wallet_type', 1)
                ->where('profit', '<', 0)
                ->where('status_id', 1)
                ->sum('profit');

            $pending = Report::where('user_id', $value->id)
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->where('wallet_type', 1)
                ->where('status_id', 3)
                ->sum('amount');


            // closing balance
            $closing_balance = Report::where('user_id', $value->id)
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->where('wallet_type', 1)
                ->orderBy('id', 'DESC')
                ->first();
            if ($closing_balance){
                $cl_bal = number_format($closing_balance->total_balance,2);
            }else{
                $cl_bal = $value->balance->user_balance;
            }

            $statement_url = url('admin/user-ledger-report').'/'.Crypt::encrypt($value->id);
            $data_arr[] = array(
                "id" => $value->id,
                "name" => '<a href="'.$statement_url.'">'.$value->name . ' '. $value->last_name.'</a>',
                "opening_balance" => $opening_bal,
                "credit_amount" => '<span style="color: green;"><i class="fas fa-plus-square"></i> '.number_format($credit, 2).'</span>',
                "debit_amount" => '<span style="color: red;"><i class="fas fa-minus-square"></i> '.number_format($debit, 2).'</span>',
                "sales" => number_format($sales, 2),
                'profit' => '<span style="color: green"><i class="fas fa-plus-square"></i> '.number_format($profit, 2).'</span>',
                'charges' => '<span style="color: red;">'.number_format($charges, 2).'</span>',
                'pending' => number_format($pending, 2),
                'closing_bal' => $cl_bal,
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


    function my_income (Request $request){
                if ($request->fromdate && $request->todate) {
                    $fromdate = $request->fromdate;
                    $todate = $request->todate;
                    $urls = url('admin/income/my-income-api').'?'.'fromdate='.$fromdate.'&todate='.$todate;
                } else {
                    $fromdate = date('Y-m-d', time());
                    $todate = date('Y-m-d', time());
                    $urls = url('admin/income/my-income-api').'?'.'fromdate='.$fromdate.'&todate='.$todate;
                }
                $data = array(
                    'page_title' => 'Income Report',
                    'fromdate' => $fromdate,
                    'todate' => $todate,
                    'urls' => $urls,
                );
                return view('admin.income.my_income')->with($data);


    }

    function my_income_api (Request $request){

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


        $user_id = Auth::id();
        $totalRecords = User::select('count(*) as allcount')
            ->where('id', $user_id)
            ->count();

        $totalRecordswithFilter = User::select('count(*) as allcount')
            ->where('id', $user_id)
            ->where('mobile', 'like', '%' .$searchValue . '%')
            ->count();

        // Fetch records

        $records = User::orderBy($columnName,$columnSortOrder)
            ->where('mobile', 'like', '%' .$searchValue . '%')
            ->where('id', $user_id)
            ->skip($start)
            ->take($rowperpage)
            ->get();
        $data_arr = array();
        foreach($records as $value){
            // opening balance
            $openingbalance = Report::where('user_id', $value->id)
                ->whereDate('created_at',  '<', $fromdate)
                ->where('wallet_type', 1)
                ->orderBy('id', 'DESC')
                ->first();
            if ($openingbalance){
                $opening_bal = number_format($openingbalance->total_balance,2);
            }else{
                $opening_bal = 0;
            }

            // credit amount
            if ($value->role_id == 8 || $value->role_id == 9 || $value->role_id == 10){
                $credit = Report::where('user_id', $value->id)
                    ->whereDate('created_at', '>=', $fromdate)
                    ->whereDate('created_at', '<=', $todate)
                    ->where('wallet_type', 1)
                    ->where('status_id', 6)
                    ->sum('amount');
            }else{
                $credit = Report::where('user_id', $value->id)
                    ->whereDate('created_at', '>=', $fromdate)
                    ->whereDate('created_at', '<=', $todate)
                    ->where('wallet_type', 1)
                    ->where('status_id', 6)
                    ->whereNotIn('remark', ['remark'])
                    ->sum('amount');
            }


            // debit amout
            $debit = Report::where('user_id', $value->id)
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->where('wallet_type', 1)
                ->where('status_id', 7)
                ->sum('amount');

            // sales
            $sales = Report::where('user_id', $value->id)
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->where('wallet_type', 1)
                ->where('status_id', 1)
                ->sum('amount');

            // profit
            if ($value->role_id == 8 || $value->role_id == 9 || $value->role_id == 10){
                $profit = Report::where('user_id', $value->id)
                    ->whereDate('created_at', '>=', $fromdate)
                    ->whereDate('created_at', '<=', $todate)
                    ->where('wallet_type', 1)
                    ->where('profit', '>', 0)
                    ->where('status_id', 1)
                    ->sum('profit');
            }else{
                $profit = Report::where('user_id', $value->id)
                    ->whereDate('created_at', '>=', $fromdate)
                    ->whereDate('created_at', '<=', $todate)
                    ->where('wallet_type', 1)
                    ->where('profit', '>', 0)
                    ->where('status_id', 6)
                    ->where('remark', 'profit')
                    ->sum('profit');
            }


            // chages
            $charges = Report::where('user_id', $value->id)
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->where('wallet_type', 1)
                ->where('profit', '<', 0)
                ->where('status_id', 1)
                ->sum('profit');

            $pending = Report::where('user_id', $value->id)
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->where('wallet_type', 1)
                ->where('status_id', 3)
                ->sum('amount');


            // closing balance
            $closing_balance = Report::where('user_id', $value->id)
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->where('wallet_type', 1)
                ->orderBy('id', 'DESC')
                ->first();
            if ($closing_balance){
                $cl_bal = number_format($closing_balance->total_balance,2);
            }else{
                $cl_bal = $value->balance->user_balance;
            }


            $data_arr[] = array(
                "id" => $value->id,
                "name" => $value->name.' '.$value->last_name,
                "opening_balance" => $opening_bal,
                "credit_amount" => '<span style="color: green;"><i class="fas fa-plus-square"></i> '.number_format($credit, 2).'</span>',
                "debit_amount" => '<span style="color: red;"><i class="fas fa-minus-square"></i> '.number_format($debit, 2).'</span>',
                "sales" => number_format($sales, 2),
                'profit' => '<span style="color: green"><i class="fas fa-plus-square"></i> '.number_format($profit, 2).'</span>',
                'charges' => '<span style="color: red;">'.number_format($charges, 2).'</span>',
                'pending' => number_format($pending, 2),
                'closing_bal' => $cl_bal,
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


    function operator_wise_sale(Request $request)
    {
        // get staff permission
        if (Auth::user()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['operator_wise_sale_permission'];
            if ($myPermission != 1) {
                return redirect()->back();
            }
        }

        $role_id = Auth::user()->role_id;
        $company_id = Auth::user()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);

        $fromdate = $request->fromdate ?? date('Y-m-d');
        $todate = $request->todate ?? date('Y-m-d');
        $child_id = $request->child_id ?? 0;

        $provider_id = Provider::where('service_id', 12)->pluck('id');

        $query = Report::whereIn('user_id', $my_down_member)
            ->where('status_id', 1)
            ->groupBy('provider_id')
            ->selectRaw('
            provider_id, 
            sum(amount) as total_amount, 
            sum(profit) as total_profit, 
            sum(distributor_comm) as total_dcomm, 
            sum(super_distributor_comm) as total_sdcomm, 
            sum(sales_team_comm) as total_stcomm, 
            sum(white_label_comm) as total_wlcomm, 
            sum(white_label_reseller_comm) as total_wlrcomm,  
            sum(company_staff) as total_cscomm, 
            sum(api_comm) as total_api_comm, 
            count(*) as all_count'
            )
            ->orderBy('provider_id', 'DESC')
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereNotIn('provider_id', $provider_id);

        if ($child_id != 0) {
            $query->where('user_id', $child_id);
        }

        $reports = $query->get();

        $users = User::whereIn('id', $my_down_member)->where('status_id', 1)->get();
        $apis = Api::all();
        $status = Status::whereIn('id', [1, 2, 3, 4, 5, 6, 7])->get();

        $data = [
            'page_title' => 'Operator Wise Report',
            'report_slug' => 'Operator Wise Report',
            'fromdate' => $fromdate,
            'todate' => $todate,
            'child_id' => $child_id,
            'row_count' => $reports->count(),
        ];

        return view('admin.income.operator_wise_sale', compact('reports', 'users', 'apis', 'status'))->with($data);
    }

    function api_summary_report(Request $request)
    {
        // get staff permission
        if (Auth::user()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['api_summary_permission'];
            if ($myPermission != 1) {
                return redirect()->back();
            }
        }

        if (Auth::user()->role_id <= 2) {
            $role_id = Auth::user()->role_id;
            $company_id = Auth::user()->company_id;
            $user_id = Auth::id();
            $library = new MemberLibrary();
            $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);

            $fromdate = $request->fromdate ?? date('Y-m-d');
            $todate = $request->todate ?? date('Y-m-d');

            $reports = Report::whereIn('user_id', $my_down_member)
                ->where('status_id', 1)
                ->groupBy('api_id')
                ->selectRaw('
                api_id,
                sum(amount) as total_amount, 
                sum(profit) as total_profit, 
                sum(distributor_comm) as total_dcomm, 
                sum(super_distributor_comm) as total_sdcomm, 
                sum(sales_team_comm) as total_stcomm, 
                sum(white_label_comm) as total_wlcomm, 
                sum(white_label_reseller_comm) as total_wlrcomm,  
                sum(company_staff) as total_cscomm, 
                count(*) as all_count'
                )
                ->orderBy('api_id', 'DESC')
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->whereNotIn('api_id', [0])
                ->get();

            $total_amount = Report::whereIn('user_id', $my_down_member)
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->where('status_id', 1)
                ->whereNotIn('api_id', [0])
                ->sum('amount');
            $total_profit = Report::whereIn('user_id', $my_down_member)
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->where('status_id', 1)
                ->whereNotIn('api_id', [0])
                ->sum('profit');
            $total_dcomm = Report::whereIn('user_id', $my_down_member)
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->where('status_id', 1)
                ->whereNotIn('api_id', [0])
                ->sum('distributor_comm');
            $total_sdcomm = Report::whereIn('user_id', $my_down_member)
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->where('status_id', 1)
                ->whereNotIn('api_id', [0])
                ->sum('super_distributor_comm');
            $total_stcomm = Report::whereIn('user_id', $my_down_member)
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->where('status_id', 1)
                ->whereNotIn('api_id', [0])
                ->sum('sales_team_comm');
            $total_wlcomm = Report::whereIn('user_id', $my_down_member)
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->where('status_id', 1)
                ->whereNotIn('api_id', [0])
                ->sum('white_label_comm');
            $total_wlrcomm = Report::whereIn('user_id', $my_down_member)
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->where('status_id', 1)
                ->whereNotIn('api_id', [0])
                ->sum('white_label_reseller_comm');
            $total_cscomm = Report::whereIn('user_id', $my_down_member)
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->where('status_id', 1)
                ->whereNotIn('api_id', [0])
                ->sum('company_staff');
            $all_count = Report::whereIn('user_id', $my_down_member)
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->where('status_id', 1)
                ->whereNotIn('api_id', [0])
                ->count();

            $data = [
                'page_title' => 'Api Summary',
                'fromdate' => $fromdate,
                'todate' => $todate,
                'total_amount' => number_format($total_amount, 2),
                'total_profit' => number_format($total_profit, 2),
                'total_dcomm' => number_format($total_dcomm, 2),
                'total_sdcomm' => number_format($total_sdcomm, 2),
                'total_stcomm' => number_format($total_stcomm, 2),
                'total_wlcomm' => number_format($total_wlcomm, 2),
                'total_wlrcomm' => number_format($total_wlrcomm, 2),
                'total_cscomm' => number_format($total_cscomm, 2),
                'all_count' => $all_count,
                'row_count' => $reports->count(),
            ];

            return view('admin.income.api_summary_report', compact('reports'))->with($data);
        } else {
            return redirect()->back();
        }
    }
}
