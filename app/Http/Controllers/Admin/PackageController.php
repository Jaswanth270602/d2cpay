<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Models\Scheme;
use App\Models\Sitesetting;
use App\Models\Commission;
use App\Library\PermissionLibrary;
use Helpers;

class PackageController extends Controller
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

    function package_settings()
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['package_settings_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2) {
            $schemes = Scheme::where('user_id', Auth::id())->get();
            $data = array('page_title' => 'Package Setting');
            if ($this->backend_template_id == 1) {
                return view('admin.package_settings', compact('schemes'))->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.package_settings', compact('schemes'))->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.package_settings', compact('schemes'))->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.package_settings', compact('schemes'))->with($data);
            } else {
                return redirect()->back();
            }
        } else {
            return redirect()->back();
        }
    }

    function view_package_details(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['package_settings_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $id = $request->id;
            $schemes = Scheme::where('id', $id)->where('user_id', Auth::id())->first();
            if ($schemes) {

                $details = array(
                    'scheme_id' => $schemes->id,
                    'scheme_name' => $schemes->scheme_name,
                    'created_at' => "$schemes->created_at",
                    'created_by' => $schemes->user->name ?? '',
                );
                return Response()->json([
                    'status' => 'success',
                    'details' => $details,

                ]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Package not found']);
            }

        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry Not Permission']);
        }
    }

    function update_package(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['package_settings_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $rules = array(
                'scheme_name' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $scheme_id = $request->scheme_id;
            $scheme_name = $request->scheme_name;
            $schemes = Scheme::where('id', $scheme_id)->where('user_id', Auth::id())->first();
            if ($schemes) {
                Scheme::where('id', $scheme_id)->update(['scheme_name' => $scheme_name]);
                return Response()->json(['status' => 'success', 'message' => 'Package successfully updated']);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Package not found']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry Not Permission']);
        }
    }

    function create_new_package(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['package_settings_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $rules = array(
                'scheme_name' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }

            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            $scheme_name = $request->scheme_name;
            Scheme::insertGetId([
                'user_id' => Auth::id(),
                'scheme_name' => $scheme_name,
                'created_at' => $ctime,
                'company_id' => Auth::User()->company_id,
                'status_id' => 1,

            ]);
            return Response()->json(['status' => 'success', 'message' => 'Package successfully created']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry Not Permission']);
        }
    }

    function delete_package(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $rules = array(
                'id' => 'required|exists:schemes,id',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
            }
            $id = $request->id;
            Commission::where('scheme_id', $id)->delete();
            Scheme::where('id', $id)->delete();
            return Response()->json(['status' => 'success', 'message' => 'Package delete successful..!']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry Not Permission']);
        }
    }

    function copy_package(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $rules = array(
                'scheme_id' => 'required|exists:schemes,id',
                'scheme_name' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
            }
            $old_scheme_id = $request->scheme_id;
            $scheme_name = $request->scheme_name;
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            $scheme_name = $request->scheme_name;
            $new_scheme_id = Scheme::insertGetId([
                'user_id' => Auth::id(),
                'scheme_name' => $scheme_name,
                'created_at' => $ctime,
                'company_id' => Auth::User()->company_id,
                'status_id' => 1,
            ]);
            $commissions = Commission::where('scheme_id', $old_scheme_id)->get();
            foreach ($commissions as $value) {
                Commission::insert([
                    'provider_id' => $value->provider_id,
                    'scheme_id' => $new_scheme_id,
                    'service_id' => $value->service_id,
                    'min_amount' => $value->min_amount,
                    'max_amount' => $value->max_amount,
                    'st' => $value->st,
                    'sd' => $value->sd,
                    'd' => $value->d,
                    'r' => $value->r,
                    'referral' => $value->referral,
                    'status_id' => $value->status_id,
                    'user_id' => Auth::id(),
                    'type' => $value->type,
                    'created_at' => $ctime,
                ]);
            }
            return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry Not Permission']);
        }
    }
}
