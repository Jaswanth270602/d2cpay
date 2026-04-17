<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use App\Models\User;
use App\Models\Masterbank;
use Helpers;
use App\Models\Api;
use App\Models\Apiresponse;
use App\Models\Provider;
use App\Models\Report;
use App\Models\Aepsreport;
use App\Models\Balance;
use Validator;
use App\Models\State;
use App\Models\District;
use App\Models\Agentonboarding;
use App\Models\Service;
use App\Library\BasicLibrary;
use App\Library\Commission_increment;
use App\Library\GetcommissionLibrary;

class AepsController extends Controller
{

    public function __construct()
    {
        $this->company_id = Helpers::company_id()->id;
        $dt = Helpers::company_id();
        $this->company_id = $dt->id;
        $api = Api::where('vender_id', 10)->first();
        $this->key = (empty($api)) ? '' : 'Bearer ' . $api->api_key;
        $this->api_id = (empty($api)) ? 1 : $api->id;
    }

    function agent_onboarding(Request $request)
    {
        $provider_id = 319;
        $user_id = Auth::id();
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($serviceStatus == 1 && Auth::User()->role_id == 8) {
            $data = array('page_title' => 'Agent Onboarding');
            $states = State::where('status_id', 1)->get();
            return view('agent.aeps.agent_onboarding', compact('states'))->with($data);
        } else {
            return redirect()->back();
        }
    }

    function agent_onboarding_api(Request $request)
    {
        $rules = array(
            'first_name' => 'required',
            'last_name' => 'required',
            'mobile_number' => 'required|digits:10|unique:agentonboardings',
            'email' => 'required|email|unique:agentonboardings',
            'aadhar_number' => 'required|digits:12|unique:agentonboardings',
            'pan_number' => 'required|regex:/^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/|unique:agentonboardings',
            'company' => 'required',
            'pin_code' => 'required|digits:6|integer',
            'address' => 'required',
            'bank_account_number' => 'required',
            'ifsc' => 'required',
            'city' => 'required',
            'state_id' => 'required',
            'district_id' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $first_name = $request->first_name;
        $last_name = $request->last_name;
        $mobile_number = $request->mobile_number;
        $email = $request->email;
        $aadhar_number = $request->aadhar_number;
        $pan_number = $request->pan_number;
        $company = $request->company;
        $pin_code = $request->pin_code;
        $address = $request->address;
        $bank_account_number = $request->bank_account_number;
        $ifsc = $request->ifsc;
        $city = $request->city;
        $state_id = $request->state_id;
        $district_id = $request->district_id;
        $user_id = Auth::id();
        $company_id = Auth::User()->company_id;
        $now = new \DateTime();
        $ctime = $now->format('Y-m-d H:i:s');
        $insert_id = Agentonboarding::insertGetId([
            'first_name' => $first_name,
            'last_name' => $last_name,
            'mobile_number' => $mobile_number,
            'email' => $email,
            'aadhar_number' => $aadhar_number,
            'pan_number' => $pan_number,
            'company' => $company,
            'pin_code' => $pin_code,
            'address' => $address,
            'bank_account_number' => $bank_account_number,
            'ifsc' => $ifsc,
            'state_id' => $state_id,
            'district_id' => $district_id,
            'city' => $city,
            'created_at' => $ctime,
            'user_id' => $user_id,
            'company_id' => $company_id,
            'status_id' => 1,
        ]);
        $onboarding = new BasicLibrary();
        return $onboarding->agent_onboarding($first_name, $last_name, $mobile_number, $email, $aadhar_number, $pan_number, $company, $pin_code, $address, $bank_account_number, $ifsc, $state_id, $district_id, $city, $user_id, $company_id, $insert_id);
    }

    function save_agent_onboarding(Request $request)
    {
        $rules = array(
            'first_name' => 'required',
            'last_name' => 'required',
            'mobile_number' => 'required|digits:10|unique:agentonboardings',
            'email' => 'required|email|unique:agentonboardings',
            'aadhar_number' => 'required|digits:12|unique:agentonboardings',
            'pan_number' => 'required|regex:/^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/|unique:agentonboardings',
            'company' => 'required',
            'pin_code' => 'required|digits:6|integer',
            'address' => 'required',
            'bank_account_number' => 'required',
            'ifsc' => 'required',
            'city' => 'required',
            'state_id' => 'required',
            'district_id' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $first_name = $request->first_name;
        $last_name = $request->last_name;
        $mobile_number = $request->mobile_number;
        $email = $request->email;
        $aadhar_number = $request->aadhar_number;
        $pan_number = $request->pan_number;
        $company = $request->company;
        $pin_code = $request->pin_code;
        $address = $request->address;
        $bank_account_number = $request->bank_account_number;
        $ifsc = $request->ifsc;
        $city = $request->city;
        $state_id = $request->state_id;
        $district_id = $request->district_id;
        $user_id = Auth::id();
        $company_id = Auth::User()->company_id;
        $now = new \DateTime();
        $ctime = $now->format('Y-m-d H:i:s');

        $insert_id = Agentonboarding::insertGetId([
            'first_name' => $first_name,
            'last_name' => $last_name,
            'mobile_number' => $mobile_number,
            'email' => $email,
            'aadhar_number' => $aadhar_number,
            'pan_number' => $pan_number,
            'company' => $company,
            'pin_code' => $pin_code,
            'address' => $address,
            'bank_account_number' => $bank_account_number,
            'ifsc' => $ifsc,
            'state_id' => $state_id,
            'district_id' => $district_id,
            'city' => $city,
            'created_at' => $ctime,
            'user_id' => $user_id,
            'company_id' => $company_id,
            'status_id' => 1,
        ]);
        $onboarding = new BasicLibrary();
        return $onboarding->agent_onboarding($first_name, $last_name, $mobile_number, $email, $aadhar_number, $pan_number, $company, $pin_code, $address, $bank_account_number, $ifsc, $state_id, $district_id, $city, $user_id, $company_id, $insert_id);
    }

    function aeps_route_1(Request $request)
    {
        $provider_id = 319;
        $user_id = Auth::id();
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($serviceStatus == 1 && Auth::User()->role_id == 8) {
            $data = array('page_title' => 'Aeps Route 1');
            return view('agent.aeps.aeps_raute_1')->with($data);
        } else {
            return redirect()->back();
        }


    }

    function aeps_route_2(Request $request)
    {
        $provider_id = 319;
        $user_id = Auth::id();
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($serviceStatus == 1 && Auth::User()->role_id == 8) {
            $data = array('page_title' => 'Aeps Raute 2');
            return view('agent.aeps.aeps_raute_2')->with($data);
        } else {
            return redirect()->back();
        }

    }

    function aeps_route_1_landing(Request $request)
    {
        $url = "https://api.pay2all.in/v1/aeps/landing";
        $api_request_parameters = array(
            'mobile_number' => Auth::User()->mobile,
            'api_id' => 28,
        );
        $method = 'POST';
        $header = ["Accept:application/json", "Authorization:" . $this->key];
        $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
        return $response;
    }

    function aeps_route_2_landing(Request $request)
    {
        $url = "https://api.pay2all.in/v1/aeps/landing";
        $api_request_parameters = array(
            'mobile_number' => Auth::User()->mobile,
            'api_id' => 29,
        );
        $method = 'POST';
        $header = ["Accept:application/json", "Authorization:" . $this->key];
        $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
        return $response;
    }

    function aeps_landing_api(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required',
            'api_id' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $mobile_number = $request->mobile_number;
        $api_id = $request->api_id;
        $url = "https://api.pay2all.in/v1/aeps/landing";
        $api_request_parameters = array(
            'mobile_number' => $mobile_number,
            'api_id' => $api_id,
        );
        $method = 'POST';
        $header = ["Accept:application/json", "Authorization:" . $this->key];
        $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
        return $response;
    }

    function aeps_outlet_id(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $mobile_number = $request->mobile_number;
        $url = "https://api.pay2all.in/v1/outlet?search_val=$mobile_number";
        $api_request_parameters = array();
        $method = 'GET';
        $header = ["Accept:application/json", "Authorization:" . $this->key];
        $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
        $res = json_decode($response);
        $total = $res->total;
        if ($total == 1) {
            $data = $res->data;
            foreach ($data as $value) {
                $icici_agent_id = $value->remark;
                $outlet_id = $value->id;
            }
        } else {
            $icici_agent_id = "";
            $outlet_id = "";
        }
        return Response()->json([
            'status' => 'success',
            'icici_agent_id' => $icici_agent_id,
            'outlet_id' => $outlet_id,
        ]);

    }


}
