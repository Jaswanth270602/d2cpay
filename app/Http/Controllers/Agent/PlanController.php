<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Helpers;
use App\Models\Api;
use App\Models\Provider;
use App\Models\State;
use App\Models\Apiprovider;
use Validator;

class PlanController extends Controller
{


    public function __construct()
    {
        $this->api_id = 1;
        $apis = Api::find($this->api_id);
        $this->api_token = $apis->api_key ?? '';
        $this->base_url = 'https://mpayment.in/';
    }

    function providerList()
    {
        $masterbank = Provider::where('status_id', 1)->whereIn('service_id', [1, 2])->select('id', 'provider_name')->get();
        $response = array();
        foreach ($masterbank as $value) {
            $product = array();
            $product["provider_id"] = $value->id;
            $product["provider_name"] = $value->provider_name;
            array_push($response, $product);
        }
        return Response()->json(['status' => 'success', 'message' => 'Fatch success', 'providers' => $response]);
    }

    function stateList()
    {
        $masterbank = State::where('status_id', 1)->select('id', 'name')->get();
        $response = array();
        foreach ($masterbank as $value) {
            $product = array();
            $product["state_id"] = $value->id;
            $product["state_name"] = $value->name;
            array_push($response, $product);
        }
        return Response()->json(['status' => 'success', 'message' => 'Fatch success', 'states' => $response]);
    }

    function prepaid_plan(Request $request)
    {
        $rules = array(
            'provider_id' => 'required|exists:providers,id',
            'state_id' => 'required|exists:states,id',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $provider_id = $request->provider_id;
        $state_id = $request->state_id;
        $apiproviders = Apiprovider::where('provider_id', $provider_id)->where('api_id', $this->api_id)->first();
        if (empty($apiproviders->operator_code)) {
            return Response()->json(['status' => 'failure', 'message' => 'provider code not added!']);
        }
        $operator = urlencode($apiproviders->operator_code);
        $url = $this->base_url . "api/plan/v1/prepaid-plan?api_token=$this->api_token&provider_id=$operator&state_id=$state_id";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($curl);
        curl_close($curl);
        $res = json_decode($response, true);
        $status = $res['status'] ?? 'failure';
        if ($status === 'success') {
            return response()->json([
                'status' => 'success',
                'topup' => $res['topup'] ?? '',
                'full_talktime' => $res['full_talktime'] ?? '',
                'internet_3g' => $res['internet_3g'] ?? '',
                'rate_cutter' => $res['rate_cutter'] ?? '',
                'internet_2g' => $res['internet_2g'] ?? '',
                'sms' => $res['sms'] ?? '',
                'combo' => $res['combo'] ?? '',
            ]);
        }
        // If the status is not successful
        return response()->json([
            'status' => 'failure',
            'message' => 'Plan not found',
        ]);

    }

    function r_offer(Request $request)
    {
        $rules = array(
            'provider_id' => 'required|exists:providers,id',
            'mobile_number' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $mobile_number = $request->mobile_number;
        $provider_id = $request->provider_id;
        $apiproviders = Apiprovider::where('provider_id', $provider_id)->where('api_id', $this->api_id)->first();
        if (empty($apiproviders->operator_code)) {
            return Response()->json(['status' => 'failure', 'message' => 'provider code not added!']);
        }
        $operator = urlencode($apiproviders->operator_code);
        $url = $this->base_url . "api/plan/v1/r-offer?api_token=$this->api_token&provider_id=$operator&mobile_number=$mobile_number";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($curl);
        curl_close($curl);
        // Decode the API response
        $res = json_decode($response, true);
        // Check the response status
        $status = $res['status'] ?? 'failure';
        if ($status === 'success') {
            return response()->json([
                'status' => 'success',
                'plans' => $res['plans'] ?? [],
            ]);
        }
        // If the status is not successful
        return response()->json([
            'status' => 'failure',
            'message' => 'R Offer not found',
        ]);
    }


    function dth_customer_info(Request $request)
    {
        $rules = array(
            'provider_id' => 'required|exists:providers,id',
            'number' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $provider_id = $request->provider_id;
        $number = $request->number;
        $providers = Provider::find($provider_id);
        $apiproviders = Apiprovider::where('provider_id', $provider_id)->where('api_id', $this->api_id)->first();
        if (empty($apiproviders->operator_code)) {
            return Response()->json(['status' => 'failure', 'message' => 'provider code not added!']);
        }
        $operator = urlencode($apiproviders->operator_code);
        $url = $this->base_url . "api/plan/v1/dth-customer-info?api_token=$this->api_token&provider_id=$operator&number=$number";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($curl);
        curl_close($curl);
        // Decode the API response
        $res = json_decode($response, true);
        // Check the response status
        $status = $res['status'] ?? 'failure';
        if ($status === 'success') {
            return response()->json([
                'status' => 'success',
                'tel' => $number,
                'operator' => $providers->provider_name ?? '',
                'MonthlyRecharge' => $res['MonthlyRecharge'] ?? '',
                'Balance' => $res['Balance'] ?? '',
                'customerName' => $res['customerName'] ?? '',
                'NextRechargeDate' => $res['NextRechargeDate'] ?? '',
                'planname' => $res['planname'] ?? '',
            ]);
            // Return failure if no valid customer data is found
            return response()->json(['status' => 'failure', 'message' => 'No valid customer info found',]);
        }
        // If the status is not successful
        return response()->json(['status' => 'failure', 'message' => 'Customer Info not found',]);
    }


    function dth_plans(Request $request)
    {
        $rules = array(
            'provider_id' => 'required|exists:providers,id',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $provider_id = $request->provider_id;
        $apiproviders = Apiprovider::where('provider_id', $provider_id)->where('api_id', $this->api_id)->first();
        if (empty($apiproviders->operator_code)) {
            return Response()->json(['status' => 'failure', 'message' => 'provider code not added!']);
        }
        $operator = urlencode($apiproviders->operator_code);
        // API request URL
        $url = $this->base_url . "api/plan/v1/dth-plans?api_token=$this->api_token&provider_id=$operator";

        // Initialize cURL
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($curl);
        curl_close($curl);
        // Decode response
        $res = json_decode($response, true);
        // Check if the response status is success
        $status = $res['status'] ?? 'falure';
        if ($status == 'success') {
            // Return success with plan records
            return response()->json(['status' => 'success', 'plans' => $res['plans'] ?? [],]);
        }
        // Return failure if no plans found
        return response()->json(['status' => 'failure', 'message' => 'Plan not found',]);
    }

    function dth_refresh(Request $request)
    {
        $rules = array(
            'number' => 'required',
            'provider_id' => 'required|exists:providers,id',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $provider_id = $request->provider_id;
        $number = $request->number;
        $apiproviders = Apiprovider::where('provider_id', $provider_id)->where('api_id', $this->api_id)->first();
        if (empty($apiproviders->operator_code)) {
            return Response()->json(['status' => 'failure', 'message' => 'provider code not added!']);
        }
        $operator = urlencode($apiproviders->operator_code);
        $url = $this->base_url . "api/plan/v1/dth-refresh?api_token=$this->api_token&number=$number&provider_id=$operator";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($curl);
        // Decode the response
        $res = json_decode($response);
        // Check if response is valid and records exist
        if ($res->status == 'success') {
            return response()->json([
                'status' => 'success',
                'message' => $res->message ?? 'No description available',
            ]);
        }
        // Return failure if no valid response
        return response()->json([
            'status' => 'failure',
            'message' => 'Failed to refresh DTH',
        ]);
    }

    function dth_roffer(Request $request)
    {
        $rules = array(
            'number' => 'required',
            'provider_id' => 'required|exists:providers,id',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $provider_id = $request->provider_id;
        $number = $request->number;
        $apiproviders = Apiprovider::where('provider_id', $provider_id)->where('api_id', $this->api_id)->first();
        if (empty($apiproviders->operator_code)) {
            return Response()->json(['status' => 'failure', 'message' => 'provider code not added!']);
        }
        $operator = urlencode($apiproviders->operator_code);
        $url = $this->base_url . "api/plan/v1/dth-roffer?api_token=$this->api_token&number=$number&provider_id=$operator";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($curl);
        $res = json_decode($response, true);
        // Check the API response status
        $status = $res['status'] ?? 'failure';
        // If the "desc" field in "records" indicates an issue (e.g., "Plan Not Available")
        if ($status == 'success') {
            return response()->json([
                'status' => 'success',
                'plans' => $res['plans'] ?? [],
            ]);
        }
        // Return failure if the R offer is not found or status is not 1
        return response()->json([
            'status' => 'failure',
            'message' => 'R Offer not found',
        ]);
    }


    function find_operator(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $url = "http://operatorcheck.mplan.in/api/operatorinfo.php?apikey=$this->apikey&tel=$mobile_number";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($curl);
        curl_close($curl);
        $res = json_decode($response);
        $records = $res->records;
        if ($records->status == 1) {
            $providers = Provider::where('mplan', $records->Operator)->first();
            $states = State::where('mplan', 'LIKE', '%' . $records->circle . '%')->first();
            $details = array(
                'provider_id' => (empty($providers)) ? 0 : $providers->id,
                'provider_name' => (empty($providers)) ? '' : $providers->provider_name,
                'state_id' => (empty($states)) ? 0 : $states->id,
                'state_name' => (empty($states)) ? '' : $states->name,
            );
            return Response()->json(['status' => 'success', 'message' => 'Successful..', 'details' => $details]);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Failed']);
        }
    }


    function postpaid_bill_fatch(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',
            'provider_id' => 'required|exists:providers,id',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $provider_id = $request->provider_id;
        $providers = Provider::find($provider_id);
        $operator = urlencode($providers->mplan);
        $url = "https://www.mplan.in/api/Bsnl.php?apikey=$this->apikey&offer=roffer&tel=$mobile_number&operator=$operator";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
}
