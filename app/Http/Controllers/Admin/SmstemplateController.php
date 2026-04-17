<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use App\Models\Smstemplate;
use App\Models\Sitesetting;
use Validator;
use Helpers;
use App\Library\PermissionLibrary;

class SmstemplateController extends Controller
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

    function welcome (){
        // get staff permission
        if (Auth::User()->role_id == 2){
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['sms_template_permission'];
            if (!$myPermission == 1){
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2){
            $company_id = Auth::User()->company_id;
            $sitesettings = Sitesetting::where('company_id', $company_id)->first();
            if ($sitesettings){
                $data = array(
                    'page_title' => 'Sms Template',
                    'brand_name' => $sitesettings->brand_name,
                );
                $smstemplates = Smstemplate::get();
                if ($this->backend_template_id == 1) {
                    return view('admin.sms_template', compact('smstemplates'))->with($data);
                } elseif ($this->backend_template_id == 2) {
                    return view('themes2.admin.sms_template', compact('smstemplates'))->with($data);
                } elseif ($this->backend_template_id == 3) {
                    return view('themes3.admin.sms_template', compact('smstemplates'))->with($data);
                } elseif ($this->backend_template_id == 4) {
                    return view('themes4.admin.sms_template', compact('smstemplates'))->with($data);
                } else {
                    return redirect()->back();
                }
            }else{
                return Redirect::back();
            }
        }else{
            return Redirect::back();
        }
    }

    function view_template (Request $request){
        // get staff permission
        if (Auth::User()->role_id == 2){
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['sms_template_permission'];
            if (!$myPermission == 1){
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2){
            $id = $request->id;
            $smstemplates = Smstemplate::find($id);
            if ($smstemplates){
                $company_id = Auth::User()->company_id;
                $sitesettings = Sitesetting::where('company_id', $company_id)->first();
                $data = array(
                    'id' => $smstemplates->id,
                    'template_id' => $smstemplates->template_id,
                    'template_name' => $smstemplates->template_name,
                    'template_message' => $smstemplates->template_message.' '.$sitesettings->brand_name,
                    'whatsapp' => $smstemplates->whatsapp,
                    'sms' => $smstemplates->sms,
                    'send_mail' => $smstemplates->send_mail,
                );
                return Response()->json(['status' => 'success', 'message' => 'Successful..!', 'details' => $data]);
            }else{
                return Response()->json(['status' => 'failure', 'message' => 'Record not found']);
            }
        }else{
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function update_template (Request $request){
        // get staff permission
        if (Auth::User()->role_id == 2){
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['sms_template_permission'];
            if (!$myPermission == 1){
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2){
                $id = $request->id;
                $whatsapp = $request->whatsapp;
                $sms = $request->sms;
                $template_id = $request->template_id;
                $send_mail = $request->send_mail;
                Smstemplate::where('id', $id)->update(['whatsapp' => $whatsapp, 'sms' => $sms, 'template_id' => $template_id, 'send_mail' => $send_mail]);
            return Response()->json(['status' => 'success', 'message' => 'Update successful..!']);
        }else{
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }
}
