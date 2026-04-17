<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Models\Commission;
use App\Models\Scheme;
use App\Models\Provider;
use App\Models\Aepsslab;
use App\Models\Aepscommission;
use App\Models\Moneycommission;
use App\Models\Moneyslab;
use App\Models\Aadharpaycommission;
use App\Models\Aadharpayslab;
use App\Models\Payoutcommission;
use App\Models\Payoutslab;
use App\Models\Service;
use App\Models\Sitesetting;
use Helpers;

class CommissionController extends Controller
{

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

    function commission_setup(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $scheme_id = $request->scheme_id;
            $schemes = Scheme::find($scheme_id);
            $service_id = Service::where('status_id', 1)->get(['id']);
            $data = array(
                'page_title' => 'Commission Setup',
                'scheme_id' => $scheme_id,
                'scheme_name' => $schemes->scheme_name,
            );
            $library = new \App\Library\BasicLibrary;
            $companyActiveService = $library->getCompanyActiveService(Auth::id());
            $userActiveService = $library->getUserActiveService(Auth::id());
            $providers = Provider::whereIn('service_id', $service_id)->whereIn('service_id', $companyActiveService)->whereIn('service_id', $userActiveService)->where('status_id', 1)->get();
            $services = Service::where('status_id', 1)->whereIn('id', $companyActiveService)->whereIn('id', $userActiveService)->get();
            if ($this->backend_template_id == 1) {
                return view('admin.commission.commission_setup', compact('providers', 'services'))->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.commission.commission_setup', compact('providers', 'services'))->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.commission.commission_setup', compact('providers', 'services'))->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.commission.commission_setup', compact('providers', 'services'))->with($data);
            } else {
                return redirect()->back();
            }
        } else {
            return redirect()->back();
        }

    }


    function set_operator_commission(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $scheme_id = $request->scheme_id;
            $provider_id = $request->provider_id;
            $schemes = Scheme::find($scheme_id);
            $providers = Provider::find($provider_id);
            $data = array(
                'page_title' => 'Commission Setup',
                'scheme_name' => $schemes->scheme_name,
                'provider_name' => $providers->provider_name,
                'scheme_id' => $scheme_id,
                'provider_id' => $provider_id,
            );
            $commission = Commission::where('scheme_id', $scheme_id)->where('provider_id', $provider_id)->get();
            if ($this->backend_template_id == 1) {
                return view('admin.commission.set_operator_commission', compact('commission'))->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.commission.set_operator_commission', compact('commission'))->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.commission.set_operator_commission', compact('commission'))->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.commission.set_operator_commission', compact('commission'))->with($data);
            } else {
                return redirect()->back();
            }
        } else {
            return redirect()->back();
        }
    }

    function view_operator_commission(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $id = $request->id;
            $commission = Commission::find($id);
            if ($commission) {
                $commissions = array(
                    'id' => $id,
                    'provider_name' => $commission->provider->provider_name,
                    'min_amount' => $commission->min_amount,
                    'max_amount' => $commission->max_amount,
                    'st' => $commission->st,
                    'sd' => $commission->sd,
                    'd' => $commission->d,
                    'r' => $commission->r,
                    'referral' => $commission->referral,
                    'type' => $commission->type,
                );
                return Response()->json([
                    'status' => 'success',
                    'commission' => $commissions,
                ]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Record not found']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }


    function update_operator_commission(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $id = $request->id;
            $min_amount = $request->min_amount;
            $max_amount = $request->max_amount;
            $st = $request->st;
            $sd = $request->sd;
            $d = $request->d;
            $r = $request->r;
            $referral = $request->referral;
            $type = $request->type;
            Commission::where('id', $id)->update([
                'min_amount' => $min_amount,
                'max_amount' => $max_amount,
                'st' => $st,
                'sd' => $sd,
                'd' => $d,
                'r' => $r,
                'referral' => $referral,
                'type' => $type,
            ]);
            return Response()->json(['status' => 'success', 'message' => 'commission successfully updated']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function store_commission(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $rules = array(
                'min_amount' => 'required',
                'max_amount' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $scheme_id = $request->scheme_id;
            $provider_id = $request->provider_id;
            $min_amount = $request->min_amount;
            $max_amount = $request->max_amount;
            $type = $request->type;
            $st = $request->st;
            $sd = $request->sd;
            $d = $request->d;
            $r = $request->r;
            $referral = $request->referral;
            $commission = Commission::where('provider_id', $provider_id)->where('scheme_id', $scheme_id)->where('min_amount', '<=', $min_amount)->where('max_amount', '>=', $max_amount)->first();
            if ($commission) {
                return Response()->json(['status' => 'failure', 'message' => 'Kinldy check min amount or max amount already added in slab']);
            } else {
                $now = new \DateTime();
                $ctime = $now->format('Y-m-d H:i:s');
                $providers = Provider::find($provider_id);
                Commission::insertGetId([
                    'provider_id' => $provider_id,
                    'scheme_id' => $scheme_id,
                    'service_id' => $providers->service_id,
                    'min_amount' => $min_amount,
                    'max_amount' => $max_amount,
                    'st' => $st,
                    'sd' => $sd,
                    'd' => $d,
                    'r' => $r,
                    'referral' => $referral,
                    'user_id' => Auth::id(),
                    'type' => $type,
                    'created_at' => $ctime,
                    'status_id' => 1,
                ]);
                return Response()->json(['status' => 'success', 'message' => 'Commission Successfully Updated']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry Not Permission']);
        }
    }

    function delete_commission_slab(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $id = $request->id;
            Commission::where('id', $id)->delete();
            return Response()->json(['status' => 'success', 'message' => 'Slab Successfully deleted']);
        } else {
            return Response()->json();
        }
    }

    function store_bulk_commission(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $rules = array(
                'scheme_id' => 'required|exists:schemes,id',
                'service_id' => 'required|exists:services,id',
                'min_amount' => 'required',
                'max_amount' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $scheme_id = $request->scheme_id;
            $service_id = $request->service_id;
            $min_amount = $request->min_amount;
            $max_amount = $request->max_amount;
            $type = $request->type;
            $st = $request->st;
            $sd = $request->sd;
            $d = $request->d;
            $r = $request->r;
            $referral = $request->referral;
            $providers = Provider::where('service_id', $service_id)->where('status_id', 1)->get();
            foreach ($providers as $value) {
                $commission = Commission::where('provider_id', $value->id)->where('scheme_id', $scheme_id)->where('min_amount', '<=', $min_amount)->where('max_amount', '>=', $max_amount)->first();
                if (empty($commission)) {
                    $now = new \DateTime();
                    $ctime = $now->format('Y-m-d H:i:s');
                    Commission::insertGetId([
                        'provider_id' => $value->id,
                        'scheme_id' => $scheme_id,
                        'service_id' => $service_id,
                        'min_amount' => $min_amount,
                        'max_amount' => $max_amount,
                        'st' => $st,
                        'sd' => $sd,
                        'd' => $d,
                        'r' => $r,
                        'referral' => $referral,
                        'user_id' => Auth::id(),
                        'type' => $type,
                        'created_at' => $ctime,
                        'status_id' => 1,
                    ]);
                }
            }
            return Response()->json(['status' => 'success', 'message' => 'Commission Successfully Updated']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry Not Permission']);
        }
    }


}
