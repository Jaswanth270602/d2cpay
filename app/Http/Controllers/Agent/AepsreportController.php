<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Validator;
use App\Report;
use App\Provider;
use \Crypt;
use Auth;

class AepsreportController extends Controller
{
    function ledger_report(Request $request)
    {
        if ($request->fromdate && $request->todate) {
            $fromdate = $request->fromdate;
            $todate = $request->todate;
            $urls = url('agent/aeps-ledger-report-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate;
        } else {
            $fromdate = date('Y-m-d', time());
            $todate = date('Y-m-d', time());
            $urls = url('agent/aeps-ledger-report-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate;
        }
        $data = array(
            'page_title' => 'Aeps Ledger Report',
            'fromdate' => $fromdate,
            'todate' => $todate,
            'urls' => $urls
        );
        return view('agent.report.aeps_ledger_report')->with($data);
    }

    function ledger_report_api(Request $request)
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


        $user_id = Auth::id();

        $totalRecords = Report::select('count(*) as allcount')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('wallet_type', 2)
            ->count();

        $totalRecordswithFilter = Report::select('count(*) as allcount')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('id', 'like', '%' . $searchValue . '%')
            ->where('wallet_type', 2)
            ->count();

        // Fetch records

        $records = Report::orderBy($columnName, $columnSortOrder)
            ->where('id', 'like', '%' . $searchValue . '%')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('wallet_type', 2)
            ->orderBy('id', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();
        $data_arr = array();
        foreach ($records as $value) {

            if ($value->status_id == 1 || $value->status_id == 3 || $value->status_id == 7 || $value->status_id == 5) {
                $debit = '<span style="color: red;"><i class="fas fa-minus-square"></i> ' . number_format($value->amount, 2) . '</span>';
            } else {
                $debit = 0;
            }

            if ($value->status_id == 2 || $value->status_id == 4 || $value->status_id == 6) {
                $credit = '<span style="color: green;"><i class="fas fa-plus-square"></i> ' . number_format($value->amount, 2) . '</span>';
            } else {
                $credit = 0;
            }

            if ($value->profit < 0) {
                $profit = '<span style="color: red;"><i class="fas fa-minus-square"></i>  ' . number_format($value->profit, 2) . '</span>';
            } else {
                $profit = '<span style="color: green;"><i class="fas fa-plus-square"></i> ' . number_format($value->profit, 2) . '</span>';
            }


            $data_arr[] = array(
                "id" => $value->id,
                "created_at" => "$value->created_at",
                "txnid" => $value->txnid,
                "description" => $value->description,
                "opening_balance" => number_format($value->opening_balance, 2),
                "debit" => $debit,
                "credit" => $credit,
                "profit" => $profit,
                "total_balance" => number_format($value->total_balance, 2),
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

    function aeps_report(Request $request)
    {
        if ($request->fromdate && $request->todate) {
            $fromdate = $request->fromdate;
            $todate = $request->todate;
            $urls = url('agent/aeps-report-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate;
        } else {
            $fromdate = date('Y-m-d', time());
            $todate = date('Y-m-d', time());
            $urls = url('agent/aeps-report-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate;
        }
        $data = array(
            'page_title' => 'Aeps Report',
            'fromdate' => $fromdate,
            'todate' => $todate,
            'urls' => $urls
        );

        return view('agent.report.aeps_report')->with($data);
    }

    function aeps_report_api(Request $request)
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


        $user_id = Auth::id();
        $providers = Provider::whereIn('id', [318, 319, 320, 321, 322])->get(['id']);
        $totalRecords = Report::select('count(*) as allcount')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('provider_id', $providers)
            ->where('wallet_type', 2)
            ->count();

        $totalRecordswithFilter = Report::select('count(*) as allcount')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('number', 'like', '%' . $searchValue . '%')
            ->whereIn('provider_id', $providers)
            ->where('wallet_type', 2)
            ->count();

        // Fetch records

        $records = Report::orderBy($columnName, $columnSortOrder)
            ->select('id', 'created_at', 'provider_id', 'number', 'txnid', 'amount', 'status_id')
            ->where('number', 'like', '%' . $searchValue . '%')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('provider_id', $providers)
            ->where('wallet_type', 2)
            ->orderBy('id', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();
        $data_arr = array();
        foreach ($records as $value) {


            $data_arr[] = array(
                "id" => $value->id,
                "created_at" => "$value->created_at",
                "provider" => $value->provider->provider_name,
                "number" => $value->number,
                "txnid" => $value->txnid,
                "amount" => number_format($value->amount, 2),
                'bank_name' => (!empty($value->aepsreport->report_id)) ? $value->aepsreport->bank_name : '',
                'aadhar_number' => (!empty($value->aepsreport->report_id)) ? $value->aepsreport->aadhar_number : '',
                "status" => '<span class="' . $value->status->class . '">' . $value->status->status . '</span>',
                "view" => '<button class="btn btn-danger btn-sm" onclick="view_recharges(' . $value->id . ')"><i class="fas fa-eye"></i> View</button>',
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

    function payout_settlement_report(Request $request)
    {
        if ($request->fromdate && $request->todate) {
            $fromdate = $request->fromdate;
            $todate = $request->todate;
            $urls = url('agent/payout-settlement-report-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate;
        } else {
            $fromdate = date('Y-m-d', time());
            $todate = date('Y-m-d', time());
            $urls = url('agent/payout-settlement-report-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate;
        }
        $data = array(
            'page_title' => 'Payout Settlement Report',
            'fromdate' => $fromdate,
            'todate' => $todate,
            'urls' => $urls
        );
        return view('agent.report.payout_settlement_report')->with($data);
    }

    function payout_settlement_report_api(Request $request)
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

        $user_id = Auth::id();
        $providers = Provider::whereIn('id', [323, 324])->get(['id']);
        $totalRecords = Report::select('count(*) as allcount')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('provider_id', $providers)
            ->where('wallet_type', 2)
            ->count();

        $totalRecordswithFilter = Report::select('count(*) as allcount')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('number', 'like', '%' . $searchValue . '%')
            ->whereIn('provider_id', $providers)
            ->where('wallet_type', 2)
            ->count();

        // Fetch records

        $records = Report::orderBy($columnName, $columnSortOrder)
            ->select('id', 'created_at', 'provider_id', 'number', 'txnid', 'amount', 'profit', 'status_id')
            ->where('number', 'like', '%' . $searchValue . '%')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('provider_id', $providers)
            ->where('wallet_type', 2)
            ->orderBy('id', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();
        $data_arr = array();
        foreach ($records as $value) {


            $data_arr[] = array(
                "id" => $value->id,
                "created_at" => "$value->created_at",
                "provider" => $value->provider->provider_name,
                "number" => $value->number,
                "txnid" => $value->txnid,
                "amount" => number_format($value->amount, 2),
                "profit" => number_format($value->profit, 2),
                "status" => '<span class="' . $value->status->class . '">' . $value->status->status . '</span>',
                "view" => '<button class="btn btn-danger btn-sm" onclick="view_recharges(' . $value->id . ')"><i class="fas fa-eye"></i> View</button>',
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
}
