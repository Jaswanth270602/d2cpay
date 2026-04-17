<?php

namespace App\Http\Controllers;

use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Validator;
use App\Models\Loginlog;
use App\Models\State;
use App\Models\District;
use Hash;
use App\Library\SmsLibrary;
use App\Helpers\UserSystemInfoHelper;
use App\Models\Frontbanner;
use App\Models\Notification;
use App\Models\Tableotp;
use App\Models\Member;
use App\Models\Service;
use App\Models\Provider;
use App\Models\Report;
use App\Models\Beneficiary;
use App\Models\Status;
use App\Models\Company;
use Helpers;
use App\Library\MemberLibrary;
use App\Library\BasicLibrary;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->company_id = Helpers::company_id()->id;
        $dt = Helpers::company_id();
        $this->company_id = $dt->id;
        $companies = Company::find($this->company_id);
        $this->cdnLink = (empty($companies)) ? '' : $companies->cdn_link;
    }


    function all_transaction_report(Request $request)
    {
        $fromdate = ($request->fromdate) ? $request->fromdate : date('Y-m-d', strtotime('-30 days'));
        $todate = ($request->todate) ? $request->todate : date('Y-m-d', time());
        $reports = Report::where('user_id', Auth::id())
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->orderBy('id', 'DESC')
            ->select('id', 'provider_id', 'user_id', 'created_at', 'number', 'txnid', 'opening_balance', 'amount', 'profit', 'total_balance', 'status_id')
            ->paginate(20);
        $response = array();
        foreach ($reports as $value) {
            $product = array();
            $product["id"] = $value->id;
            $product["service_id"] = $value->provider->service_id;
            $product["service_name"] = Service::find($value->provider->service_id)->service_name;
            $product["user"] = $value->user->name . ' ' . $value->user->last_name;
            $product["provider_icon"] = $this->cdnLink . $value->provider->provider_image;
            $product["created_at"] = "$value->created_at";
            $product["provider"] = $value->provider->provider_name;
            $product["number"] = $value->number;
            $product["txnid"] = $value->txnid;
            $product["opening_balance"] = number_format($value->opening_balance, 2);
            $product["amount"] = number_format($value->amount, 2);
            $product["profit"] = number_format($value->profit, 2);
            $product["total_balance"] = number_format($value->total_balance, 2);
            $product["status"] = $value->status->status;
            array_push($response, $product);
        }
        return response()->json([
            'total' => $reports->total(),
            'pageNumber' => $reports->currentPage(),
            'nextPageUrl' => $reports->nextPageUrl(),
            'page' => $reports->currentPage(),
            'pages' => $reports->lastPage(),
            'perpage' => $reports->perPage(),
            'reports' => $response,
            'status' => 'success',
        ]);
    }

    function ledger_report(Request $request)
    {
        $fromdate = ($request->fromdate) ? $request->fromdate : date('Y-m-d', strtotime('-30 days'));
        $todate = ($request->todate) ? $request->todate : date('Y-m-d', time());
        $reports = Report::where('user_id', Auth::id())
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('wallet_type', 1)
            ->orderBy('id', 'DESC')
            ->paginate(20);
        $response = array();
        foreach ($reports as $value) {
            if ($value->status_id == 1 || $value->status_id == 3 || $value->status_id == 7 || $value->status_id == 5) {
                $debit = number_format($value->amount, 2);
            } else {
                $debit = 0;
            }
            if ($value->status_id == 2 || $value->status_id == 4 || $value->status_id == 6) {
                $credit = number_format($value->amount, 2);
            } else {
                $credit = 0;
            }

            if ($value->profit < 0) {
                $profit = number_format($value->profit, 2);
            } else {
                $profit = number_format($value->profit, 2);
            }
            $product = array();
            $product["id"] = $value->id;
            $product["service_id"] = $value->provider->service_id;
            $product["provider_icon"] = $this->cdnLink . $value->provider->provider_image;
            $product["created_at"] = "$value->created_at";
            $product["status_id"] = $value->status_id;
            $product["txnid"] = $value->txnid;
            $product["description"] = $value->description;
            $product["opening_balance"] = number_format($value->opening_balance, 2);
            $product["debit"] = $debit;
            $product["credit"] = $credit;
            $product["profit"] = $profit;
            $product["total_balance"] = number_format($value->total_balance, 2);
            $product["status"] = $value->status->status;
            array_push($response, $product);
        }
        return response()->json([
            'total' => $reports->total(),
            'pageNumber' => $reports->currentPage(),
            'nextPageUrl' => $reports->nextPageUrl(),
            'page' => $reports->currentPage(),
            'pages' => $reports->lastPage(),
            'perpage' => $reports->perPage(),
            'reports' => $response,
            'status' => 'success',
        ]);
    }

    function welcome(Request $request, $report_slug)
    {
        $library = new \App\Library\BasicLibrary;
        $companyActiveService = $library->getCompanyActiveService(Auth::id());
        $userActiveService = $library->getUserActiveService(Auth::id());
        $services = Service::whereIn('id', $companyActiveService)->whereIn('id', $userActiveService)->where('report_slug', $report_slug)->first();
        if ($services) {
            $fromdate = ($request->fromdate) ? $request->fromdate : date('Y-m-d', strtotime('-30 days'));
            $todate = ($request->todate) ? $request->todate : date('Y-m-d', time());
            if ($services->servicegroup_id == 4) {
                return Self::bankingReport($fromdate, $todate, $services);
            } elseif ($services->servicegroup_id == 5) {
                return Self::aepsReport($fromdate, $todate, $services);
            } else {
                return Self::otherReport($fromdate, $todate, $services);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Service not active!']);
        }
    }

    function bankingReport($fromdate, $todate, $services)
    {
        $provider_id = Provider::where('service_id', $services->id)->get(['id']);
        $reports = Report::where('user_id', Auth::id())
            ->whereIn('provider_id', $provider_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->select('id', 'provider_id', 'user_id', 'created_at', 'number', 'txnid', 'opening_balance', 'amount', 'profit', 'total_balance', 'status_id', 'beneficiary_id')
            ->orderBy('id', 'DESC')
            ->paginate(20);
        $response = array();
        foreach ($reports as $value) {
            $beneficiary = Beneficiary::find($value->beneficiary_id);
            $remiter_number = (empty($beneficiary)) ? '' : $beneficiary->remiter_number;
            $bene_name = (empty($beneficiary)) ? '' : $beneficiary->name;
            $bank_name = (empty($beneficiary)) ? '' : $beneficiary->bank_name;
            $payment_mode = ($value->channel == 2) ? 'IMPS' : 'NEFT';
            $product = array();
            $product["id"] = $value->id;
            $product["provider_icon"] = $this->cdnLink . $value->provider->provider_image;
            $product["created_at"] = "$value->created_at";
            $product["provider"] = $value->provider->provider_name;
            $product["number"] = $value->number;
            $product["remiter_number"] = $remiter_number;
            $product["bene_name"] = $bene_name;
            $product["bank_name"] = $bank_name;
            $product["txnid"] = $value->txnid;
            $product["opening_balance"] = number_format($value->opening_balance, 2);
            $product["amount"] = number_format($value->amount, 2);
            $product["profit"] = number_format($value->profit, 2);
            $product["total_balance"] = number_format($value->total_balance, 2);
            $product["payment_mode"] = $payment_mode;
            $product["status"] = $value->status->status;
            array_push($response, $product);
        }
        return response()->json([
            'total' => $reports->total(),
            'pageNumber' => $reports->currentPage(),
            'nextPageUrl' => $reports->nextPageUrl(),
            'page' => $reports->currentPage(),
            'pages' => $reports->lastPage(),
            'perpage' => $reports->perPage(),
            'reports' => $response,
            'status' => 'success',
        ]);
    }

    function aepsReport($fromdate, $todate, $services)
    {
        $provider_id = Provider::where('service_id', $services->id)->get(['id']);
        $reports = Report::where('user_id', Auth::id())
            ->whereIn('provider_id', $provider_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->select('id', 'provider_id', 'user_id', 'created_at', 'number', 'txnid', 'opening_balance', 'amount', 'profit', 'total_balance', 'status_id')
            ->orderBy('id', 'DESC')
            ->paginate(20);
        $response = array();
        foreach ($reports as $value) {
            $product = array();
            $product["id"] = $value->id;
            $product["provider_icon"] = $this->cdnLink . $value->provider->provider_image;
            $product["created_at"] = "$value->created_at";
            $product["provider"] = $value->provider->provider_name;
            $product["number"] = $value->number;
            $product["txnid"] = $value->txnid;
            $product["amount"] = number_format($value->amount, 2);
            $product["profit"] = number_format($value->profit, 2);
            $product["bank_name"] = (!empty($value->aepsreport->report_id)) ? $value->aepsreport->bank_name : '';
            $product["aadhar_number"] = (!empty($value->aepsreport->report_id)) ? $value->aepsreport->aadhar_number : '';
            $product["status"] = $value->status->status;
            array_push($response, $product);
        }
        return response()->json([
            'total' => $reports->total(),
            'pageNumber' => $reports->currentPage(),
            'nextPageUrl' => $reports->nextPageUrl(),
            'page' => $reports->currentPage(),
            'pages' => $reports->lastPage(),
            'perpage' => $reports->perPage(),
            'reports' => $response,
            'status' => 'success',
        ]);
    }

    function otherReport($fromdate, $todate, $services)
    {
        $provider_id = Provider::where('service_id', $services->id)->get(['id']);
        $reports = Report::where('user_id', Auth::id())
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('provider_id', $provider_id)
            ->orderBy('id', 'DESC')
            ->select('id', 'provider_id', 'user_id', 'created_at', 'number', 'txnid', 'opening_balance', 'amount', 'profit', 'total_balance', 'status_id')
            ->paginate(20);
        $response = array();
        foreach ($reports as $value) {
            $product = array();
            $product["id"] = $value->id;
            $product["service_id"] = $value->provider->service_id;
            $product["service_name"] = Service::find($value->provider->service_id)->service_name;
            $product["user"] = $value->user->name . ' ' . $value->user->last_name;
            $product["provider_icon"] = $this->cdnLink . $value->provider->provider_image;
            $product["created_at"] = "$value->created_at";
            $product["provider"] = $value->provider->provider_name;
            $product["number"] = $value->number;
            $product["txnid"] = $value->txnid;
            $product["opening_balance"] = number_format($value->opening_balance, 2);
            $product["amount"] = number_format($value->amount, 2);
            $product["profit"] = number_format($value->profit, 2);
            $product["total_balance"] = number_format($value->total_balance, 2);
            $product["status"] = $value->status->status;
            array_push($response, $product);
        }
        return response()->json([
            'total' => $reports->total(),
            'pageNumber' => $reports->currentPage(),
            'nextPageUrl' => $reports->nextPageUrl(),
            'page' => $reports->currentPage(),
            'pages' => $reports->lastPage(),
            'perpage' => $reports->perPage(),
            'reports' => $response,
            'status' => 'success',
        ]);
    }

    function operator_report(Request $request)
    {
        $fromdate = ($request->fromdate) ? $request->fromdate : date('Y-m-d', strtotime('-30 days'));
        $todate = ($request->todate) ? $request->todate : date('Y-m-d', time());
        $reports = Report::where('user_id', Auth::id())
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('status_id', 1)
            ->groupBy('provider_id')
            ->selectRaw('provider_id, sum(amount) as total_amount, sum(profit) as total_profit, count(*) as all_count, max(id) as latest_id')
            ->orderBy('latest_id', 'DESC')
            ->get();
        $response = array();
        $i = 1;
        foreach ($reports as $value) {
            $product = array();
            $product["id"] = $i++;
            $product["provider"] = $value->provider->provider_name;
            $product["provider_icon"] = $this->cdnLink . $value->provider->provider_image;
            $product["amount"] = number_format($value->total_amount, 2);
            $product["profit"] = number_format($value->total_profit, 2);
            array_push($response, $product);
        }

        $total_sales = Report::where('user_id', Auth::id())
            ->whereIn('status_id', [1])
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->sum('amount');

        $total_profit = Report::where('user_id', Auth::id())
            ->whereIn('status_id', [1])
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->sum('profit');

        $sales = array(
            'total_sales' => number_format($total_sales, 2),
            'total_profit' => number_format($total_profit, 2),
        );
        return Response()->json(['status' => 'success', 'report' => $response, 'sales' => $sales]);
    }

    function income_report(Request $request)
    {
        $fromdate = ($request->fromdate) ? $request->fromdate : date('Y-m-d', strtotime('-30 days'));
        $todate = ($request->todate) ? $request->todate : date('Y-m-d', time());
        $users = User::where('id', Auth::id())->get();
        $response = array();
        foreach ($users as $value) {
// opening balance
            $openingbalance = Report::where('user_id', $value->id)
                ->whereDate('created_at', '<', $fromdate)
                ->where('wallet_type', 1)
                ->orderBy('id', 'DESC')
                ->first();
            if ($openingbalance) {
                $opening_bal = number_format($openingbalance->total_balance, 2);
            } else {
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
            if ($closing_balance) {
                $cl_bal = number_format($closing_balance->total_balance, 2);
            } else {
                $cl_bal = $value->balance->user_balance;
            }

            $product = array();
            $product["id"] = $value->id;
            $product["name"] = $value->name . ' ' . $value->last_name;
            $product["opening_balance"] = $opening_bal;
            $product["credit_amount"] = $credit;
            $product["debit_amount"] = $debit;
            $product["sales"] = number_format($sales, 2);
            $product["profit"] = number_format($profit, 2);
            $product["charges"] = number_format($charges, 2);
            $product["pending"] = number_format($pending, 2);
            $product["closing_bal"] = $cl_bal;
            array_push($response, $product);
        }
        return Response()->json(['status' => 'success', 'report' => $response]);
    }
}
