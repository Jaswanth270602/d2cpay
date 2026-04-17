<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use App\Models\Role;
use App\Models\User;
use App\Models\Company;
use App\Models\Sitesetting;
use Helpers;
use App\Library\SmsLibrary;
use App\Library\PermissionLibrary;

class WhatsappController extends Controller
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
    function role_wise (){
        // get staff permission
        if (Auth::User()->role_id == 2){
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['whatsapp_notification_permission'];
            if (!$myPermission == 1){
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2){
            $data = array('page_title' => 'Send Role Wise');
            $roledetails = Role::where('id', '>', Auth::user()->role_id)->where('status_id', 1)->get();
            $companies = Company::get();
            if ($this->backend_template_id == 1) {
                return view('admin.whatsapp.role_wise', compact('roledetails', 'companies'))->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.whatsapp.role_wise', compact('roledetails', 'companies'))->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.whatsapp.role_wise', compact('roledetails', 'companies'))->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.whatsapp.role_wise', compact('roledetails', 'companies'))->with($data);
            } else {
                return redirect()->back();
            }
        }else{
            return redirect()->back();
        }
    }

    function role_wise_send (Request $request){
        // get staff permission
        if (Auth::User()->role_id == 2){
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['whatsapp_notification_permission'];
            if (!$myPermission == 1){
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2){
            $rules = array(
                'role_id' => 'required',
                'message' => 'required',
                'company_id' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }

            $exploadcompany = explode(',', $request->company_id);
            $company_id  = array();
            foreach ($exploadcompany as $company) {
                $company_id[] = $company;
            }


            $role_id = $request->role_id;
            $message = $request->message;
            $exploadrole = explode(',', $role_id);
            foreach ($exploadrole as $role_id){
                $userdetails =  User::where('role_id', $role_id)->whereIn('company_id', $company_id)->get();
                foreach ($userdetails as $value){
                    $library = new SmsLibrary();
                    $library->send_whatsapp($value->mobile, $message);
                }
            }
            return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
        }else{
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function role_wise_send_image (Request $request){
        // get staff permission
        if (Auth::User()->role_id == 2){
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['whatsapp_notification_permission'];
            if (!$myPermission == 1){
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2){
            $this->validate($request, [
                'photo' => 'required|mimes:jpeg,png,jpg,gif,pdf',
                'role_id' => 'required',
                'image_caption' => 'required',
                'company_id' => 'required',
            ]);
            $company_id = Auth::User()->company_id;
            $company_name = Auth::User()->company->company_website;
            $photo = $request->photo;
            $roles = $request->role_id;
            $image_caption = $request->image_caption;
            $photo = base64_encode(file_get_contents($photo));
            $extension = $request->file('photo')->extension();
            if ($extension == 'pdf'){
                $url = "https://cdn.bceres.com/api/file/v1/pdf-file";
            }else{
                $url = "https://cdn.bceres.com/api/file/v1/company-logo";
            }
            $api_request_parameters = array(
                'image' => $photo,
                'name' => $company_name,
                'type' => 4,
            );
            $method = 'POST';
            $header = ["Accept:application/json"];
            $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
            $res = json_decode($response);
            $status = $res->status;
            if ($status == 'success'){
                $image_url = $res->image_url;
                foreach ($roles as $role_id){
                    $userdetails =  User::where('role_id', $role_id)->where('company_id', $company_id)->get();
                    foreach ($userdetails as $value){
                        $library = new SmsLibrary();
                        $library->send_whatsapp_image($value->mobile, $image_caption, $image_url);
                    }
                }
                \Session::flash('success', 'Successful..!');
                return redirect()->back();
            }else{
                \Session::flash('failure', $res->message);
                return redirect()->back();
            }


        }else{
            return redirect()->back();
        }
    }
}
