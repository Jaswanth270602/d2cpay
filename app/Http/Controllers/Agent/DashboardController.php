<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Report;
use App\Models\Balance;
use App\Models\User;
use App\Models\Loginlog;
use App\Models\Sitesetting;
use Mail;
use Helpers;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller {

    public function __construct()
    {
        $this->company_id = Helpers::company_id()->id;
        $companies = Helpers::company_id();
        $this->company_id = $companies->id;
        $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
        if ($sitesettings) {
            $this->brand_name = $sitesettings->brand_name;
            $this->backend_template_id = $sitesettings->backend_template_id;
        } else {
            $this->brand_name = "";
            $this->backend_template_id = 1;
        }
    }

    function dashboard (){
        $data = array('page_title' => 'Dashboard');
        if ($this->backend_template_id == 1) {
            return view('agent.dashboard')->with($data);
        } elseif ($this->backend_template_id == 2) {
            return view('themes2.agent.dashboard')->with($data);
        } elseif ($this->backend_template_id == 3) {
            return view('themes3.agent.dashboard')->with($data);
        } elseif ($this->backend_template_id == 4) {
            return view('themes4.agent.dashboard')->with($data);
        } else {
            return redirect()->back();
        }
    }

    function dashboard_chart_api (Request $request){
        $datefrom = date('Y-m-d', time());
        $dateto = date('Y-m-d', time());
        $user_id = Auth::id();
        $reports = Report::where('user_id', $user_id)
            ->where('status_id', 1)
           ->whereDate('created_at', '>=', $datefrom)
            ->whereDate('created_at', '<=', $dateto)
            ->groupBy('provider_id')
            ->selectRaw('provider_id, sum(amount) as op_amount, sum(profit) as op_profit, max(id) as latest_id')
            ->orderBy('latest_id', 'DESC')
            ->get();

        $response = array();
        foreach ($reports as $value) {
            $product = array();
            $product["amount"] = $value->op_amount;
            $product["provider_name"] = $value->provider->provider_name;
            array_push($response, $product);
        }
        return Response()->json(['status' => 'success', 'provider' => $response]);
    }

    function dashboard_details_api (){
        $user_id = Auth::id();
        $today_success = Report::where('user_id', $user_id)->where('status_id', 1)->whereDate('created_at', '=', date('Y-m-d'))->sum('amount');
        $today_failure = Report::where('user_id', $user_id)->where('status_id', 2)->whereDate('created_at', '=', date('Y-m-d'))->sum('amount');
        $today_pending = Report::where('user_id', $user_id)->where('status_id', 3)->whereDate('created_at', '=', date('Y-m-d'))->sum('amount');
        $today_refunded = Report::where('user_id', $user_id)->where('status_id', 4)->whereDate('created_at', '=', date('Y-m-d'))->sum('amount');
        $today_credit = Report::where('user_id', Auth::id())->where('status_id', 6)->whereDate('created_at', '=', date('Y-m-d'))->sum('amount');
        $today_debit = Report::where('user_id', Auth::id())->where('status_id', 7)->whereDate('created_at', '=', date('Y-m-d'))->sum('amount');

        $normal_distributed_balance = Balance::where('user_id', $user_id)->sum('user_balance');
        $aeps_distributed_balance = Balance::where('user_id', $user_id)->sum('aeps_balance');


        $balances = array(
            'normal_distributed_balance' => number_format($normal_distributed_balance, 2),
            'aeps_distributed_balance' => number_format($aeps_distributed_balance, 2),
            'my_balances' => number_format(Auth::User()->balance->user_balance, 2),


        );

        $sales_overview = array(
            'today_success' => number_format($today_success, 2),
            'today_failure' => number_format($today_failure,2),
            'today_pending' => number_format($today_pending,2),
            'today_refunded' => number_format($today_refunded, 2),
            'today_credit' => number_format($today_credit, 2),
            'today_debit' => number_format($today_debit, 2),
        );
        return Response()->json([
            'status' => 'success',
            'sales_overview' => $sales_overview,
            'balances' => $balances,
        ]);
    }

    function activity_logs (Request $request){
        if ($request->fromdate && $request->todate) {
            $fromdate = $request->fromdate;
            $todate = $request->todate;
        } else {
            $fromdate = date('Y-m-d', time());
            $todate = date('Y-m-d', time());
        }
        $loginlogs =  Loginlog::where('user_id', Auth::id())
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->orderBy('id', 'desc')
            ->get();
        $data = array(
            'page_title' => 'Activity Logs',
            'fromdate' => $fromdate,
            'todate' => $todate,
        );
        return view('agent.activity_logs', compact('loginlogs'))->with($data);
    }

    function send_mail (){
        Mail::send(['text' => 'mail.send_mail'], ['name', 'pay2all'],function ($message){
            $message->to('neeraj@pay2all.in', 'Neeraj chauhan')->subject('this is test email');
            $message->from('noreplay@pay2all.in', 'pay2all');
        });
    }


    function getWalletBalance(Request $request)
    {
        $data = array(
            'normal_balance' => number_format(Auth::User()->balance->user_balance, 2),
            'aeps_balance' => number_format(Auth::User()->balance->aeps_balance, 2),
        );
       return Response()->json(['status' => 'success', 'message' => 'Successful..!', 'data' => $data]);
    }
}
