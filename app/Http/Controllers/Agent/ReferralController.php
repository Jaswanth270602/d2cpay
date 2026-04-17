<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Models\User;
use App\Models\Report;

class ReferralController extends Controller
{
    //

    function welcome (Request $request){
        $data = array('page_title' => 'Refer & Earn');
        return view('agent.referral.refer_and_earn')->with($data);
    }

    function details_app (){
        $totalRefers = User::where('parent_id', Auth::id())->count();
        $totalEarnings = Report::where('user_id', Auth::id())->where('status_id', 1)->sum('referral_comm');
        $memberList = Self::getMemberList();
        return Response()->json([
            'status' => 'success',
            'referral_code' => base64_encode(Auth::User()->mobile),
            'total_refers' => $totalRefers,
            'total_earnings' => number_format($totalEarnings, 2),
            'memberList' => $memberList,
        ]);
    }

    function getMemberList (){
        $users = User::where('parent_id', Auth::id())->orderBy('id', 'DESC')->paginate(5);
        $response = array();
        foreach ($users as $value) {
            $phone = $value->mobile;
            $phoneNumber = substr($phone, 0, 3);
            $phoneNumber .= "*****";
            $phoneNumber .= substr($phone, 3, 2);
            $product = array();
            $product["user_name"] = $value->name.' '.$value->last_name;
            $product["number"] = $phoneNumber;
            array_push($response, $product);
        }
        return $response;
    }
}
