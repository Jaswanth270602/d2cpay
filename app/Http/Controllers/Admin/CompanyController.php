<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;
use App\Models\Service;
use App\Models\Servicebanner;
use App\Models\State;
use App\Models\Sitesetting;
use App\Models\Servicegroup;
use Helpers;
use \Crypt;
use Validator;
use App\Library\PermissionLibrary;


class CompanyController extends Controller
{
    public function __construct (){
        $this->company_id = Helpers::company_id()->id;
        $dt = Helpers::company_id();
        $this->company_id = $dt->id;
        $companies = Company::find($this->company_id);
        $this->cdnLink = (empty($companies)) ? '' : $companies->cdn_link;

        $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
        if ($sitesettings) {
            $this->brand_name = $sitesettings->brand_name;
            $this->backend_template_id = $sitesettings->backend_template_id;
        } else {
            $this->brand_name = "";
            $this->backend_template_id = 1;
        }
    }

    function company_settings (){
        // get staff permission
        if (Auth::User()->role_id == 2){
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['company_settings_permission'];
            if (!$myPermission == 1){
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2){
            $companydetails = Company::where('id', Auth::User()->company_id)->first();
            $company = array(
                'update_company_id' => Crypt::encrypt($companydetails->id),
                'update_company_name' => $companydetails->company_name,
                'update_company_email' => $companydetails->company_email,
                'update_company_address' => $companydetails->company_address,
                'update_company_address_two' => $companydetails->company_address_two,
                'update_support_number' => $companydetails->support_number,
                'update_whatsapp_number' => $companydetails->whatsapp_number,
                'update_company_logo' => $companydetails->company_logo,
                'update_company_website' => $companydetails->company_website,
                'update_news' => $companydetails->news,
                'update_sender_id' => $companydetails->sender_id,
                'update_same_amount' => $companydetails->same_amount,
                'server_down' => $companydetails->server_down,
                'server_message' => $companydetails->server_message,
                'state_id' => $companydetails->state_id,
                'pin_code' => $companydetails->pin_code,
                'pan_number' => $companydetails->pan_number,
                'gst_number' => $companydetails->gst_number,
                'login_type' => $companydetails->login_type,
                'color_start' => $companydetails->color_start,
                'color_end' => $companydetails->color_end,
                'table_format' => $companydetails->table_format,
                'transaction_pin' => $companydetails->transaction_pin,
                'facebook_link' => $companydetails->facebook_link,
                'instagram_link' => $companydetails->instagram_link,
                'twitter_link' => $companydetails->twitter_link,
                'youtube_link' => $companydetails->youtube_link,
                'active_services' => $companydetails->active_services,
                'payout_route' => $companydetails->payout_route,
            );
            $data = array('page_title' => 'Company Settings');
            $states = State::where('status_id', 1)->get();
            $servicegroup_id = Servicegroup::where('status_id', 1)->get(['id']);
            $services = Service::whereIn('status_id', [1])->whereIn('servicegroup_id', $servicegroup_id)->get();
            if ($this->backend_template_id == 1) {
                return view('admin.company_settings', compact('states','services'))->with($data)->with($company);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.company_settings', compact('states'))->with($data)->with($company);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.company_settings', compact('states'))->with($data)->with($company);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.company_settings', compact('states'))->with($data)->with($company);
            } else {
                return redirect()->back();
            }

        }else{
            return Redirect::back();
        }
    }

    function update_company_seeting (Request $request){
        // get staff permission
        if (Auth::User()->role_id == 2){
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['company_settings_permission'];
            if (!$myPermission == 1){
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        $rules = array(
            'company_id' => 'required',
            'company_name' => 'required',
            'company_email' => 'required|email',
            'company_address' => 'required',
            'support_number' => 'required|digits:10',
            'whatsapp_number' => 'required|digits:10',
            'news' => 'required',
            'sender_id' => 'required',
            'same_amount' => 'required',
            'state_id' => 'required',
            'pin_code' => 'required|digits:6|integer',
            //'pan_number' => 'required|regex:/^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/',
           // 'gst_number' => 'required|regex:/^([0-9]){2}([A-Za-z]){5}([0-9]){4}([A-Za-z]){1}([0-9]{1})([A-Za-z]){2}?$/',
            'login_type' => 'required',
            'color_start' => 'required',
            'color_end' => 'required',
            'transaction_pin' => 'required',
            'active_services' => 'required',

        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $company_id = Crypt::decrypt($request->company_id);
        $company_name = $request->company_name;
        $company_email = $request->company_email;
        $company_address = $request->company_address;
        $company_address_two = $request->company_address_two;
        $support_number = $request->support_number;
        $whatsapp_number = $request->whatsapp_number;
        $news = $request->news;
        $sender_id = $request->sender_id;
        $same_amount = $request->same_amount;
        $server_down = $request->server_down;
        $server_message = $request->server_message;
        $state_id = $request->state_id;
        $pin_code = $request->pin_code;
        $pan_number = $request->pan_number;
        $gst_number = $request->gst_number;
        $login_type = $request->login_type;
        $color_start = $request->color_start;
        $color_end = $request->color_end;
        $transaction_pin = $request->transaction_pin;
        $companydetails = Company::where('id', $company_id)->first();
        if ($companydetails){
            Company::where('id', $company_id)->update([
                'company_name' => $company_name,
                'company_email' => $company_email,
                'company_address' => $company_address,
                'company_address_two' => $company_address_two,
                'support_number' => $support_number,
                'whatsapp_number' => $whatsapp_number,
                'news' => $news,
                'sender_id' => $sender_id,
                'same_amount' => $same_amount,
                'server_down' => $server_down,
                'server_message' => $server_message,
                'state_id' => $state_id,
                'pin_code' => $pin_code,
                'pan_number' => $pan_number,
                'gst_number' => $gst_number,
                'login_type' => $login_type,
                'color_start' => $color_start,
                'color_end' => $color_end,
                'table_format' => $request->table_format,
                'transaction_pin' => $transaction_pin,
                'facebook_link' => $request->facebook_link,
                'instagram_link' => $request->instagram_link,
                'twitter_link' => $request->twitter_link,
                'youtube_link' => $request->youtube_link,
                'active_services' => $request->active_services,
                'payout_route' => $request->payout_route,
            ]);
            return Response()->json(['status' => 'success', 'message' => 'Company details successfully update']);
        }else{
            return Response()->json(['status' => 'failure', 'message' => 'details not match']);
        }
    }

    function logo_upload (){
        // get staff permission
        if (Auth::User()->role_id == 2){
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['logo_upload_permission'];
            if (!$myPermission == 1){
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2){
            $data = array('page_title' => 'Logo Upload');
            if ($this->backend_template_id == 1) {
                return view('admin.logo_upload')->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.logo_upload')->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.logo_upload')->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.logo_upload')->with($data);
            } else {
                return redirect()->back();
            }
        }else{
            return Redirect::back();
        }
    }

    function store_logo (Request $request){
        // get staff permission
        if (Auth::User()->role_id == 2){
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['logo_upload_permission'];
            if (!$myPermission == 1){
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2){
            $company_id = Auth::User()->company_id;
            $company_name = Auth::User()->company->company_website;
            if ($request->photo) {
                $this->validate($request, [
                    'photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:20480',
                ]);
                $photo = $request->photo;
                $photo = base64_encode(file_get_contents($photo));
                $url = $this->cdnLink."api/file/v1/company-logo";
                $api_request_parameters = array(
                    'image' => $photo,
                    'name' => $company_name,
                    'type' => 1,
                );
                $method = 'POST';
                $header = ["Accept:application/json"];
                $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
                $res = json_decode($response);
                $status = $res->status;
                if ($status == 'success'){
                     Company::where('id', $company_id)->update(['company_logo' => $res->image_url]);
                    \Session::flash('msg', 'Your Logo Successfully Updated' );
                    return redirect()->back();
                }else{
                    \Session::flash('failure', $res->message);
                    return redirect()->back();
                }
            }else{
                \Session::flash('failure', 'Please Select Logo');
                return redirect()->back();
            }
        }else{
            return Redirect::back();
        }
    }

    function service_banner (){
        // get staff permission
        if (Auth::User()->role_id == 2){
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['service_banner_permission'];
            if (!$myPermission == 1){
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2){
            $service = Service::whereIn('id', [1,2,3,4,5,6,7,8,9,10,11,19,20,21,22])->get();
            $servicebanner = Servicebanner::where('company_id', Auth::User()->company_id)->get();
            $data = array('page_title' => 'Service Banner');
            if ($this->backend_template_id == 1) {
                return view('admin.service_banner', compact('service', 'servicebanner'))->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.service_banner', compact('service', 'servicebanner'))->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.service_banner', compact('service', 'servicebanner'))->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.service_banner', compact('service', 'servicebanner'))->with($data);
            } else {
                return redirect()->back();
            }
        }else{
            return Redirect::back();
        }
    }

    function store_service_banner (Request $request){
        // get staff permission
        if (Auth::User()->role_id == 2){
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['service_banner_permission'];
            if (!$myPermission == 1){
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2){
            $company_id = Auth::User()->company_id;
            $company_name = Auth::User()->company->company_website;
            $validator =  $this->validate($request, [
                'service_banner' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:20480',
                'service_id' => 'required',
            ]);
            $photo = $request->service_banner;
            $photo = base64_encode(file_get_contents($photo));
            $url = $this->cdnLink."api/file/v1/company-logo";
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
                $now = new \DateTime();
                $ctime = $now->format('Y-m-d H:i:s');
               Servicebanner::insertGetId([
                   'user_id' => Auth::id(),
                   'service_banner' => $res->image_url,
                   'service_id' => $request->service_id,
                   'created_at' => $ctime,
                   'company_id' => Auth::User()->company_id,
                   'status_id' => 1,
               ]);
                \Session::flash('msg', 'Service banner successfully uploaded!' );
                return redirect()->back();
            }else{
                \Session::flash('failure', $res->message);
                return redirect()->back();
            }
        }else{
            return Redirect::back();
        }
    }

    function delete_service_banner (Request $request){
        // get staff permission
        if (Auth::User()->role_id == 2){
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['service_banner_permission'];
            if (!$myPermission == 1){
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2){
          $id = $request->id;
          $banner = Servicebanner::where('id', $id)->where('company_id', Auth::User()->company_id)->first();
          if ($banner){
              Servicebanner::where('id', $id)->delete();
              return Response()->json(['status' => 'success', 'message' => 'Banner successfully deleted!']);
          }else{
              return Response()->json(['status' => 'failure', 'message' => 'record not found']);
          }
        }else{
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function view_company_active_services(Request $request)
    {
        if (Auth::User()->role_id == 1){
            $companies = Company::find(Auth::User()->company_id);
            return Response()->json([
                'status' => 'success',
                'message' => 'Successful..!',
                'active_services' => $companies->active_services,
                ]);
        }else{
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }

    }
}
