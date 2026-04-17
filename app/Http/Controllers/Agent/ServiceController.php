<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use App\Models\User;
use App\Models\Provider;
use App\Models\State;
use App\Models\Servicebanner;
use Helpers;
use App\Models\Sitesetting;
use App\Models\Service;
use Illuminate\Support\Facades\Cache;

class ServiceController extends Controller
{
    //
    public function __construct()
    {
        $this->company_id = Helpers::company_id()->id;
        $companies = Helpers::company_id();
        $this->company_id = $companies->id;
        $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
        $this->brand_name = (empty($sitesettings)) ? '' : $sitesettings->brand_name;
        $this->backend_template_id = (empty($sitesettings)) ? 1 : $sitesettings->backend_template_id;
    }


    function welcome($slug)
    {
        $services = Service::where('sub_slug', $slug)->where('status_id', 1)->first();
        if ($services) {
            if (Auth::User()->role_id <= 9) {
                $service_id = $services->id;
                $providers = Provider::where('service_id', $service_id)->where('status_id', 1)->select('id', 'provider_name')->get();
                $state = State::where('status_id', 1)->select('id', 'name')->get();
                $servicebanner = Servicebanner::where('company_id', Auth::User()->company_id)->where('service_id', $service_id)->where('status_id', 1)->select('service_banner')->get();
                $data = array(
                    'page_title' => $services->service_name
                );
                if ($services->bbps == 1) {
                    return view('agent.service.bharat_bill_payment_system', compact('providers', 'servicebanner'))->with($data);
                }
                if ($service_id == 1) {
                    return view('agent.service.prepaid_mobile', compact('providers', 'state', 'servicebanner'))->with($data);
                }

                if ($service_id == 2) {
                    return view('agent.service.dth', compact('providers', 'servicebanner'))->with($data);
                }

                if ($service_id == 3) {
                    return view('agent.service.postpaid', compact('providers', 'servicebanner'))->with($data);
                }
            } else {
                return redirect()->back();
            }
        } else {
            return redirect()->back();
        }
    }


    function certificate(Request $request)
    {
        $data = array('brand_name' => $this->brand_name);
        return view('agent.service.certificate')->with($data);
    }

    function generate_millisecond()
    {
        $mt = explode(' ', microtime());
        $mili = ((int)$mt[1]) * 1000 + ((int)round($mt[0] * 1000));
        return Response()->json(['miliseconds' => $mili]);

    }
}
