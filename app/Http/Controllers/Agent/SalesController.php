<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use App\Models\Report;
use App\Models\Provider;
use App\Models\Beneficiary;
use App\Models\User;
use \Crypt;

class SalesController extends Controller
{
    function income_report (Request $request){
        if ($request->fromdate && $request->todate) {
            $wallet_type = $request->wallet_type;
            $fromdate = $request->fromdate;
            $todate = $request->todate;
            if ($wallet_type == 1){
                $urls = url('agent/income-report-api').'?'.'fromdate='.$fromdate.'&todate='.$todate;
            }else{
                $urls = url('agent/income-report-aeps-api').'?'.'fromdate='.$fromdate.'&todate='.$todate;
            }
        } else {

            $wallet_type = 1;
            $fromdate = date('Y-m-d', time());
            $todate = date('Y-m-d', time());
            $urls = url('agent/income-report-api').'?'.'fromdate='.$fromdate.'&todate='.$todate;

        }
        $data = array(
            'page_title' => 'Income Report',
            'fromdate' => $fromdate,
            'todate' => $todate,
            'urls' => $urls,
            'wallet_type' => $wallet_type,
        );

        return view('agent.sales.income_report')->with($data);
    }

    function income_report_api (Request $request){
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
            $credit = Report::where('user_id', $value->id)
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->where('wallet_type', 1)
                ->where('status_id', 6)
                ->sum('amount');

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
            $profit = Report::where('user_id', $value->id)
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->where('wallet_type', 1)
                ->where('profit', '>', 0)
                ->where('status_id', 1)
                ->sum('profit');

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

    function income_report_aeps_api (Request $request){
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
                ->where('wallet_type', 2)
                ->orderBy('id', 'DESC')
                ->first();
            if ($openingbalance){
                $opening_bal = number_format($openingbalance->total_balance,2);
            }else{
                $opening_bal = 0;
            }

            // credit amount
            $credit = Report::where('user_id', $value->id)
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->where('wallet_type', 2)
                ->whereIn('provider_id', [147,148,150])
                ->where('status_id', 6)
                ->sum('amount');

            // debit amout
            $debit = Report::where('user_id', $value->id)
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->where('wallet_type', 2)
                ->where('status_id', 7)
                ->sum('amount');

            // sales
            $sales = Report::where('user_id', $value->id)
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->where('wallet_type', 2)
                ->where('status_id', 1)
                ->sum('amount');

            // profit
            $profit = Report::where('user_id', $value->id)
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->where('wallet_type', 2)
                ->where('profit', '>', 0)
                ->where('status_id', 6)
                ->sum('profit');

            // chages
            $charges = Report::where('user_id', $value->id)
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->where('wallet_type', 2)
                ->where('profit', '<', 0)
                ->where('status_id', 1)
                ->sum('profit');

            $pending = Report::where('user_id', $value->id)
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->where('wallet_type', 2)
                ->where('status_id', 3)
                ->sum('amount');


            // closing balance
            $closing_balance = Report::where('user_id', $value->id)
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->where('wallet_type', 2)
                ->orderBy('id', 'DESC')
                ->first();
            if ($closing_balance){
                $cl_bal = number_format($closing_balance->total_balance,2);
            }else{
                $cl_bal = $value->balance->aeps_balance;
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


    function operator_report(Request $request)
    {
        $fromdate = $request->input('fromdate', date('Y-m-d'));
        $todate = $request->input('todate', date('Y-m-d'));

        $userId = Auth::id();

        $reports = Report::where('user_id', $userId)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('status_id', 1)
            ->groupBy('provider_id')
            ->selectRaw('provider_id, sum(amount) as total_amount, sum(profit) as total_profit, count(*) as all_count')
            ->orderBy('provider_id', 'DESC')
            ->get();

        $total_amount = Report::where('user_id', $userId)
            ->where('status_id', 1)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->sum('amount');

        $total_profit = Report::where('user_id', $userId)
            ->where('status_id', 1)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->sum('profit');

        $all_count = Report::where('user_id', $userId)
            ->where('status_id', 1)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->count();

        $data = [
            'page_title' => 'Operator Wise Report',
            'fromdate' => $fromdate,
            'todate' => $todate,
            'total_amount' => number_format($total_amount, 2),
            'total_profit' => number_format($total_profit, 2),
            'all_count' => $all_count,
            'row_count' => $reports->count(),
        ];

        return view('agent.sales.operator_report', compact('reports'))->with($data);
    }





}
