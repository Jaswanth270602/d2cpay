<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use App\Models\Commission;
use App\Models\Moneycommission;
use App\Models\Aepscommission;
use App\Models\User;
use App\Models\Aadharpaycommission;
use App\Models\Payoutcommission;
use App\Models\Provider;
use App\Models\Service;
class ProfitController extends Controller
{

    function recharge_commission (){

        $data = array(
            'page_title' => 'Prepaid Mobile'
        );
        $providers = Provider::where('service_id', 1)->where('status_id', 1)->get();
        return view('admin.profit.recharge_commission', compact('providers'))->with($data);
    }

    function service_wise_commission ($id){
        $service = Service::find($id);
        $data = array(
            'page_title' => $service->service_name,
        );
        $providers = Provider::where('service_id', $id)->where('status_id', 1)->get();
        return view('admin.profit.recharge_commission', compact('providers'))->with($data);
    }

    function view_my_comm_slab (Request $request){
        $provider_id = $request->id;
        $scheme_id = Auth::User()->scheme_id;
        $providers = Provider::find($provider_id);
        $commission = Commission::where('provider_id', $provider_id)->where('scheme_id', $scheme_id)->get();
        if (count($commission) == 0){
            return Response()->json(['status' => 'failure', 'message' => 'Slab Not Found']);
        }else{
            $response = array();
            foreach ($commission as $value) {
                if(Auth::User()->role_id == 8 || Auth::User()->role_id == 9 || Auth::User()->role_id == 10){
                    $comm = $value->r;
                }elseif (Auth::User()->role_id == 7){
                    $comm = $value->d;
                }elseif (Auth::User()->role_id == 6){
                    $comm = $value->sd;
                }elseif (Auth::User()->role_id == 5){
                    $comm = $value->st;
                }
                $product = array();
                $product["min_amount"] = $value->min_amount;
                $product["max_amount"] = $value->max_amount;
                $product["type"] = ($value->type == 0) ? '%' : 'Rs';
                $product["commission"] = $comm;
                array_push($response, $product);
            }
            return Response()->json([
                'status' => 'success',
                'provider_name' => $providers->provider_name,
                'commission' => $response,
            ]);

        }
    }
}
