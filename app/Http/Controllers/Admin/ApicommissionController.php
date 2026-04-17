<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use App\Models\Api;
use App\Models\Service;
use App\Models\Provider;
use App\Models\Apicommission;

class ApicommissionController extends Controller
{
    //

    function welcome($api_id)
    {
        if (Auth::User()->role_id == 1) {
            $apis = Api::find($api_id);
            if ($apis) {
                $service_id = Service::where('status_id', 1)->get(['id']);
                $data = array(
                    'page_title' => 'Api Commission Setup',
                    'api_id' => $apis->id,
                    'api_name' => $apis->api_name,
                );
                $library = new \App\Library\BasicLibrary;
                $companyActiveService = $library->getCompanyActiveService(Auth::id());
                $userActiveService = $library->getUserActiveService(Auth::id());
                $providers = Provider::whereIn('service_id', $service_id)->whereIn('service_id', $companyActiveService)->whereIn('service_id', $userActiveService)->where('status_id', 1)->get();
                $services = Service::where('status_id', 1)->whereIn('id', $companyActiveService)->whereIn('id', $userActiveService)->get();
                return view('admin.api-commission.welcome', compact('providers', 'services'))->with($data);
            } else {
                return redirect()->back();
            }
        } else {
            return redirect()->back();
        }
    }


    function view_providers(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $rules = array(
                'api_id' => 'required|exists:apis,id',
                'provider_id' => 'required|exists:providers,id',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return redirect()->back();
            }
            $api_id = $request->api_id;
            $provider_id = $request->provider_id;
            $apis = Api::find($api_id);
            $providers = Provider::find($api_id);
            $data = array(
                'page_title' => 'Api Wise Commission Setup',
                'api_name' => $apis->api_name,
                'provider_name' => $providers->provider_name,
                'api_id' => $api_id,
                'provider_id' => $provider_id,
            );
            $apicommissions = Apicommission::where('api_id', $api_id)->where('provider_id', $provider_id)->get();
            return view('admin.api-commission.view_providers', compact('apicommissions'))->with($data);
        } else {
            return redirect()->back();
        }
    }

    function save_commission(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $rules = array(
                'api_id' => 'required|exists:apis,id',
                'provider_id' => 'required|exists:providers,id',
                'min_amount' => 'required',
                'max_amount' => 'required',
                'type' => 'required',
                'commission_type' => 'required',
                'commission' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $api_id = $request->api_id;
            $provider_id = $request->provider_id;
            $min_amount = $request->min_amount;
            $max_amount = $request->max_amount;
            $type = $request->type;
            $commission_type = $request->commission_type;
            $commission = $request->commission;
            $apicommissions = Apicommission::where('provider_id', $provider_id)->where('api_id', $api_id)->where('min_amount', '<=', $min_amount)->where('max_amount', '>=', $max_amount)->first();
            if ($apicommissions) {
                return Response()->json(['status' => 'failure', 'message' => 'Kinldy check min amount or max amount already added in slab']);
            }
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            $providers = Provider::find($provider_id);
            Apicommission::insertGetId([
                'provider_id' => $provider_id,
                'api_id' => $api_id,
                'service_id' => $providers->service_id,
                'min_amount' => $min_amount,
                'max_amount' => $max_amount,
                'commission' => $commission,
                'user_id' => Auth::id(),
                'type' => $type,
                'commission_type' => $commission_type,
                'created_at' => $ctime,
            ]);
            return Response()->json(['status' => 'success', 'message' => 'Commission Successfully Updated']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission!']);
        }
    }

    function view_provider_commission(Request $request)
    {
        $rules = array(
            'id' => 'required|exists:apicommissions,id',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $id = $request->id;
        $apicommissions = Apicommission::find($id);
        if ($apicommissions) {
            $commissions = array(
                'id' => $id,
                'provider_name' => $apicommissions->provider->provider_name,
                'min_amount' => $apicommissions->min_amount,
                'max_amount' => $apicommissions->max_amount,
                'type' => $apicommissions->type,
                'commission_type' => $apicommissions->commission_type,
                'commission' => $apicommissions->commission,
            );
            return Response()->json([
                'status' => 'success',
                'message' => 'Successful..',
                'commission' => $commissions,
            ]);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Record not found!!']);
        }
    }

    function update_commission(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $rules = array(
                'id' => 'required|exists:apicommissions,id',
                'min_amount' => 'required',
                'max_amount' => 'required',
                'type' => 'required',
                'commission_type' => 'required',
                'commission' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
            }
            $id = $request->id;
            $min_amount = $request->min_amount;
            $max_amount = $request->max_amount;
            $type = $request->type;
            $commission_type = $request->commission_type;
            $commission = $request->commission;
            Apicommission::where('id', $id)->update([
                'min_amount' => $min_amount,
                'max_amount' => $max_amount,
                'commission' => $commission,
                'type' => $type,
                'commission_type' => $commission_type,
            ]);
            return Response()->json(['status' => 'success', 'message' => 'commission successfully updated']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission!']);
        }
    }

    function delete_record(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $rules = array(
                'id' => 'required|exists:apicommissions,id',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
            }
            $id = $request->id;
            Apicommission::where('id', $id)->delete();
            return Response()->json(['status' => 'success', 'message' => 'Slab Successfully deleted']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission!']);
        }

    }
}
