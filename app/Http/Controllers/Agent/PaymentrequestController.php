<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Hash;
use Validator;
use App\Models\Bankdetail;
use App\Models\Paymentmethod;
use App\Models\Loadcash;
use App\Models\Balance;
use App\Models\Report;
use App\Models\User;
use App\Models\Returnrequest;
use DB;
use App\Library\SmsLibrary;

class PaymentrequestController extends Controller
{


    function payment_request(Request $request)
    {
        $bankdetails = Bankdetail::where('company_id', Auth::user()->company_id)
            ->where('status_id', 1)
            ->where(function($query) {
                $query->where('child_id', 0)
                    ->orWhere('child_id', Auth::id());
            })->get();
        $methods = Paymentmethod::where('status_id', 1)->get();
        $data = array('page_title' => 'Payment Request');
        $loadcash = Loadcash::where('user_id', Auth::id())->get();
        return view('agent.balance.payment_request', compact('bankdetails', 'methods', 'loadcash'))->with($data);
    }


    function balance_return_request()
    {
        $returnrequest = Returnrequest::where('user_id', Auth::id())->where('status_id', 3)->get();
        $data = array('page_title' => 'Balance Return Request');
        return view('agent.balance.balance_return_request', compact('returnrequest'))->with($data);

    }
}
