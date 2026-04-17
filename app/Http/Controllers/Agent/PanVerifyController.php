<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Hash;
use Helpers;
use Str;
use App\Models\Api;
use App\Models\User;
use App\Models\Member;
use App\Models\Sitesetting;

class PanVerifyController extends Controller
{
    public function __construct()
    {
        $this->api_id = 1;
        $apis = Api::find($this->api_id);
        $this->api_token = $apis->api_key ?? '';
        $this->base_url = 'api/pan-verification/v1/verify';

        $this->company_id = 1;
        $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
        $this->brand_name = (empty($sitesettings)) ? '' : $sitesettings->brand_name;
    }

    function verifyNow(Request $request)
    {
        $rules = array(
            'pan_number' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        if (Auth::User()->isPanVerify == 1) {
            return Response()->json(['status' => 'failure', 'message' => 'pan number already verified']);
        }
        $pan_number = $request->pan_number;
        $url = 'https://mpayment.in/api/pan-verification/v1/verify';
        $api_request_parameters = array(
            'api_token' => $this->api_token,
            'pan_number' => $pan_number,
        );
        $method = 'POST';
        $header = ["Accept:application/json"];
        $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
        if (empty($response)){
            return Response()->json(['status' => 'failure', 'message' => 'something went wrong!']);
        }
        $res = json_decode($response);
        $status = $res->status ?? 'failure';
        if ($status == 'success') {
            $data = $res->data ?? '';
            Member::where('user_id', Auth::id())->update(['pan_number' => $pan_number]);
            User::where('id', Auth::id())->update(['pan_name' => $data->name ?? '', 'isPanVerify' => 1]);
            return Response()->json(['status' => 'success', 'message' => 'PAN verified successfully', 'data' => $data]);
        } else {
            return Response()->json(['status' => 'failure', 'message' => $res->message]);
        }
    }


}
