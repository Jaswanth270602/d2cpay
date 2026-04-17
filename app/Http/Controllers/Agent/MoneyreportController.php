<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use App\Report;
use App\Provider;
use App\Beneficiary;
use \Crypt;

class MoneyreportController extends Controller
{

    function account_verification(Request $request)
    {
        if ($request->fromdate && $request->todate) {
            $fromdate = $request->fromdate;
            $todate = $request->todate;
            $urls = url('agent/account-verification-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate;
        } else {
            $fromdate = date('Y-m-d', time());
            $todate = date('Y-m-d', time());
            $urls = url('agent/account-verification-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate;
        }
        $data = array(
            'page_title' => 'Account Verification',
            'fromdate' => $fromdate,
            'todate' => $todate,
            'urls' => $urls
        );

        return view('agent.report.account_verification')->with($data);
    }

    function account_verification_api(Request $request)
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
        $providers = Provider::whereIn('id', [315])->get(['id']);
        $totalRecords = Report::select('count(*) as allcount')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('provider_id', $providers)
            ->count();

        $totalRecordswithFilter = Report::select('count(*) as allcount')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('number', 'like', '%' . $searchValue . '%')
            ->whereIn('provider_id', $providers)
            ->count();

        // Fetch records

        $records = Report::orderBy($columnName, $columnSortOrder)
            ->select('id', 'created_at', 'provider_id', 'amount', 'number', 'txnid', 'status_id')
            ->where('number', 'like', '%' . $searchValue . '%')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('provider_id', $providers)
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

    function money_transfer_report(Request $request)
    {
        if ($request->fromdate && $request->todate) {
            $fromdate = $request->fromdate;
            $todate = $request->todate;
            $sender_number = $request->sender_number;
            $urls = url('agent/money-transfer-report-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate . '&sender_number=' . $sender_number;
        } else {
            $fromdate = date('Y-m-d', time());
            $todate = date('Y-m-d', time());
            $sender_number = '';
            $urls = url('agent/money-transfer-report-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate . '&sender_number=' . $sender_number;
        }
        $data = array(
            'page_title' => 'Money Transfer Report',
            'fromdate' => $fromdate,
            'todate' => $todate,
            'sender_number' => $sender_number,
            'urls' => $urls
        );
        return view('agent.report.money_transfer_report')->with($data);
    }

    function money_transfer_report_api(Request $request)
    {
        $fromdate = $request->get('fromdate');
        $todate = $request->get('amp;todate');
        $sender_number = $request->get('amp;sender_number');

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
        $providers = Provider::whereIn('id', [316, 317])->get(['id']);
        if ($sender_number) {
            $beneficiary_id = Beneficiary::where('remiter_number', $sender_number)->get(['id']);
            $totalRecords = Report::select('count(*) as allcount')
                ->where('user_id', $user_id)
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->whereIn('provider_id', $providers)
                ->whereIn('beneficiary_id', $beneficiary_id)
                ->count();

            $totalRecordswithFilter = Report::select('count(*) as allcount')
                ->where('user_id', $user_id)
                ->where('number', 'like', '%' . $searchValue . '%')
                ->whereIn('provider_id', $providers)
                ->whereIn('beneficiary_id', $beneficiary_id)
                ->count();

            // Fetch records

            $records = Report::orderBy($columnName, $columnSortOrder)
                ->select('id', 'created_at', 'provider_id', 'number', 'txnid', 'amount', 'profit', 'total_balance', 'status_id', 'beneficiary_id', 'channel')
                ->where('number', 'like', '%' . $searchValue . '%')
                ->where('user_id', $user_id)
                ->whereIn('provider_id', $providers)
                ->whereIn('beneficiary_id', $beneficiary_id)
                ->orderBy('id', 'DESC')
                ->skip($start)
                ->take($rowperpage)
                ->get();
        } else {
            $totalRecords = Report::select('count(*) as allcount')
                ->where('user_id', $user_id)
                ->whereIn('provider_id', $providers)
                ->count();

            $totalRecordswithFilter = Report::select('count(*) as allcount')
                ->where('user_id', $user_id)
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->where('number', 'like', '%' . $searchValue . '%')
                ->whereIn('provider_id', $providers)
                ->count();

            // Fetch records

            $records = Report::orderBy($columnName, $columnSortOrder)
                ->select('id', 'created_at', 'provider_id', 'number', 'txnid', 'amount', 'profit', 'total_balance', 'status_id', 'beneficiary_id', 'channel')
                ->where('number', 'like', '%' . $searchValue . '%')
                ->where('user_id', $user_id)
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->whereIn('provider_id', $providers)
                ->orderBy('id', 'DESC')
                ->skip($start)
                ->take($rowperpage)
                ->get();
        }
        $data_arr = array();
        foreach ($records as $value) {
            $beneficiary = Beneficiary::find($value->beneficiary_id);
            $remiter_number = (empty($beneficiary)) ? '' : $beneficiary->remiter_number;
            $bene_name = (empty($beneficiary)) ? '' : $beneficiary->name;
            $bank_name = (empty($beneficiary)) ? '' : $beneficiary->bank_name;
            $payment_mode = ($value->channel == 2) ? 'IMPS' : 'NEFT';
            $data_arr[] = array(
                "id" => $value->id,
                "created_at" => "$value->created_at",
                "provider" => $value->provider->provider_name,
                "number" => $value->number,
                "remiter_number" => $remiter_number,
                "bene_name" => $bene_name,
                "bank_name" => $bank_name,
                "txnid" => $value->txnid,
                "amount" => number_format($value->amount, 2),
                "profit" => number_format($value->profit, 2),
                "balance" => number_format($value->total_balance, 2),
                "payment_mode" => $payment_mode,
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
