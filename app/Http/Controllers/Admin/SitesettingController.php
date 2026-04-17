<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use App\Models\Sitesetting;
use Validator;
use App\Models\Scheme;
use App\Models\Role;
use App\Models\User;
use App\Models\State;
use App\Models\District;
use Helpers;
use App\Library\PermissionLibrary;

class SitesettingController extends Controller
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

    function welcome()
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['site_settings_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2) {
            $company_id = Auth::User()->company_id;
            $sitesettings = Sitesetting::where('company_id', $company_id)->first();
            if ($sitesettings) {
                $data = array(
                    'page_title' => 'Site Settings',
                    'id' => $sitesettings->id,
                    'brand_name' => $sitesettings->brand_name,
                    'sms' => $sitesettings->sms,
                    'sms_key' => $sitesettings->sms_key,
                    'whatsapp' => $sitesettings->whatsapp,
                    'whatsapp_key' => $sitesettings->whatsapp_key,
                    'whatsapp_number' => $sitesettings->whatsapp_number,
                    'alert_amount' => $sitesettings->alert_amount,
                    'send_mail' => $sitesettings->send_mail,
                    'mail_transport' => $sitesettings->mail_transport,
                    'mail_host' => $sitesettings->mail_host,
                    'mail_port' => $sitesettings->mail_port,
                    'mail_encryption' => $sitesettings->mail_encryption,
                    'mail_username' => $sitesettings->mail_username,
                    'mail_password' => $sitesettings->mail_password,
                    'mail_from' => $sitesettings->mail_from,
                    //registration
                    'registration_status' => $sitesettings->registration_status,
                    'registration_scheme_id' => $sitesettings->registration_scheme_id,
                    'registration_role_id' => $sitesettings->registration_role_id,
                    'registration_parent_id' => $sitesettings->registration_parent_id,
                    'registration_state_id' => $sitesettings->registration_state_id,
                    'registration_district_id' => $sitesettings->registration_district_id,
                    'password_expires_days' => $sitesettings->password_expires_days,
                );
                $schemes = Scheme::where('company_id', Auth::User()->company_id)->get();
                $roles = Role::whereIn('id', [8, 9])->where('status_id', 1)->get();
                $users = User::whereIn('role_id', [1, 2, 3, 4, 5, 6, 7])->where('status_id', 1)->get();
                $states = State::where('status_id', 1)->get();
                $districts = District::where('status_id', 1)->where('state_id', $sitesettings->registration_state_id)->get();
                if ($this->backend_template_id == 1) {
                    return view('admin.site_settings', compact('schemes', 'roles', 'users', 'states', 'districts'))->with($data);
                } elseif ($this->backend_template_id == 2) {
                    return view('themes2.admin.site_settings', compact('schemes', 'roles', 'users', 'states', 'districts'))->with($data);
                } elseif ($this->backend_template_id == 3) {
                    return view('themes3.admin.site_settings', compact('schemes', 'roles', 'users', 'states', 'districts'))->with($data);
                } elseif ($this->backend_template_id == 4) {
                    return view('themes4.admin.site_settings', compact('schemes', 'roles', 'users', 'states', 'districts'))->with($data);
                } else {
                    return redirect()->back();
                }

            } else {
                return Redirect::back();
            }

        } else {
            return Redirect::back();
        }
    }

    function update_settings(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['site_settings_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $rules = array(
                'setting_id' => 'required',
                'brand_name' => 'required',
                'sms' => 'required',
                'alert_amount' => 'required',
                'mail_username' => 'nullable|email',
                'mail_from' => 'nullable|email',
                'registration_status' => 'required',
                'registration_scheme_id' => 'required',
                'registration_role_id' => 'required',
                'registration_parent_id' => 'required',
                'registration_state_id' => 'required',
                'registration_district_id' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $setting_id = $request->setting_id;
            $brand_name = $request->brand_name;
            $sms = $request->sms;
            $sms_key = $request->sms_key;
            $whatsapp = $request->whatsapp;
            $whatsapp_key = $request->whatsapp_key;
            $whatsapp_number = $request->whatsapp_number;
            $alert_amount = $request->alert_amount;
            $send_mail = $request->send_mail;
            $mail_transport = $request->mail_transport;
            $mail_host = $request->mail_host;
            $mail_port = $request->mail_port;
            $mail_encryption = $request->mail_encryption;
            $mail_username = $request->mail_username;
            $mail_password = $request->mail_password;
            $mail_from = $request->mail_from;
            // registration
            $registration_status = $request->registration_status;
            $registration_scheme_id = $request->registration_scheme_id;
            $registration_role_id = $request->registration_role_id;
            $registration_parent_id = $request->registration_parent_id;
            $registration_state_id = $request->registration_state_id;
            $registration_district_id = $request->registration_district_id;
            $password_expires_at = $request->password_expires_at;
            if ($password_expires_at >= 40){
                $password_expires_at = 30;
            }
            Sitesetting::where('id', $setting_id)->update([
                'brand_name' => $brand_name,
                'sms_key' => $sms_key,
                'sms' => $sms,
                'whatsapp' => $whatsapp,
                'whatsapp_key' => $whatsapp_key,
                'whatsapp_number' => $whatsapp_number,
                'alert_amount' => $alert_amount,
                'mail_transport' => $mail_transport,
                'mail_host' => $mail_host,
                'mail_port' => $mail_port,
                'mail_encryption' => $mail_encryption,
                'mail_username' => $mail_username,
                'mail_password' => $mail_password,
                'mail_from' => $mail_from,
                'send_mail' => $send_mail,
                'registration_status' => $registration_status,
                'registration_scheme_id' => $registration_scheme_id,
                'registration_role_id' => $registration_role_id,
                'registration_parent_id' => $registration_parent_id,
                'registration_state_id' => $registration_state_id,
                'registration_district_id' => $registration_district_id,
                'password_expires_days' => $password_expires_at,
            ]);
            return Response()->json(['status' => 'success', 'message' => 'Update successful..!']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }
}
