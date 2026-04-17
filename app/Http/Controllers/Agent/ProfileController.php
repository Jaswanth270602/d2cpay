<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Role;
use App\Models\User;
use App\Models\State;
use App\Models\District;
use App\Models\Member;
use App\Models\Tableotp;
use App\Models\Profile;
use App\Models\Company;
use Validator;
use Hash;
use Helpers;
use App\Library\SmsLibrary;
use App\Library\BasicLibrary;
use Carbon\Carbon;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->company_id = Helpers::company_id()->id;
        $dt = Helpers::company_id();
        $this->company_id = $dt->id;
        $companies = Company::find($this->company_id);
        $this->cdnLink = (empty($companies)) ? '' : $companies->cdn_link;
    }

    function my_profile()
    {
        $data = array('page_title' => 'My Profile');
        $roles = Role::get();
        $circles = State::where('status_id', 1)->get();
        $district = District::get();
        return view('agent.my_profile', compact('roles', 'circles', 'district'))->with($data);
    }

    function change_password(Request $request)
    {
        $rules = array(
            'old_password' => 'required',
            'new_password' => 'required|different:old_password|string|min:8|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/',
            'confirm_password' => 'required|same:new_password',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $old_password = $request->old_password;
        $new_password = $request->new_password;
        $userdetail = User::find(Auth::id());
        $current_password = $userdetail->password;
        if (Hash::check($old_password, $current_password)) {
            $userdetail->password = Hash::make($new_password);
            $userdetail->password_changed_at = Carbon::now()->toDateTimeString();
            $userdetail->save();
            \Session::flush();
            return Response()->json(['status' => 'success', 'message' => 'password successfully changed']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'password not match']);
        }

    }


    function update_profile(Request $request)
    {
        $rules = array(
            'shop_name' => 'required',
            'office_address' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $shop_name = $request->shop_name;
        $office_address = $request->office_address;
        $user_id = Auth::id();
        Member::where('user_id', $user_id)->update([
            'shop_name' => $shop_name,
            'office_address' => $office_address,
        ]);
        return Response()->json(['status' => 'success', 'message' => 'Profile Successfully Updated']);
    }

    function update_profile_photo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:20480',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $profile_photo = $request->profile_photo;
        $photo = base64_encode(file_get_contents($profile_photo));
        $url = $this->cdnLink . "api/file/v1/upload-user-kyc";
        $api_request_parameters = array(
            'image' => $photo,
            'name' => Auth::User()->company->company_website . '-' . Auth::id(),
            'type' => 1,
        );
        $method = 'POST';
        $header = ["Accept:application/json"];
        $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
        $res = json_decode($response);
        $status = $res->status;
        if ($status == 'success') {
            Member::where('user_id', Auth::id())->update(['profile_photo' => $res->image_url]);
            \Session::flash('success', 'Profile Photo Successfully Updated');
            return redirect()->back();
        } else {
            \Session::flash('failure', $res->message);
            return redirect()->back();
        }
    }

    function update_shop_photo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shop_photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:20480',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $shop_photo = $request->shop_photo;
        $photo = base64_encode(file_get_contents($shop_photo));
        $url = $this->cdnLink . "api/file/v1/upload-user-kyc";
        $api_request_parameters = array(
            'image' => $photo,
            'name' => Auth::User()->company->company_website . '-' . Auth::id(),
            'type' => 2,
        );
        $method = 'POST';
        $header = ["Accept:application/json"];
        $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
        $res = json_decode($response);
        $status = $res->status;
        if ($status == 'success') {
            Member::where('user_id', Auth::id())->update(['shop_photo' => $res->image_url]);
            $parent_id = array(1);
            $userdetails = User::find(Auth::id());
            $letter = collect([
                'title' => "Kyc Notification",
                'body' => "$userdetails->name $userdetails->last_name  updated his KYC kindly check",
            ]);
            $library = new BasicLibrary();
            $library->send_notification($parent_id, $letter);
            \Session::flash('success', 'Shop Photo Successfully Updated');
            return redirect()->back();
        } else {
            \Session::flash('failure', $res->message);
            return redirect()->back();
        }

    }

    function update_gst_regisration_photo(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'gst_regisration_photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:20480',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $gst_regisration_photo = $request->gst_regisration_photo;
        $photo = base64_encode(file_get_contents($gst_regisration_photo));
        $url = $this->cdnLink . "api/file/v1/upload-user-kyc";
        $api_request_parameters = array(
            'image' => $photo,
            'name' => Auth::User()->company->company_website . '-' . Auth::id(),
            'type' => 3,
        );
        $method = 'POST';
        $header = ["Accept:application/json"];
        $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
        $res = json_decode($response);
        $status = $res->status;
        if ($status == 'success') {
            Member::where('user_id', Auth::id())->update(['gst_regisration_photo' => $res->image_url]);
            $parent_id = array(1);
            $userdetails = User::find(Auth::id());
            $letter = collect([
                'title' => "Kyc Notification",
                'body' => "$userdetails->name $userdetails->last_name  updated his KYC kindly check",
            ]);
            $library = new BasicLibrary();
            $library->send_notification($parent_id, $letter);
            \Session::flash('success', 'GST Registration Photo Successfully Updated');
            return redirect()->back();
        } else {
            \Session::flash('failure', $res->message);
            return redirect()->back();
        }
    }

    function update_pancard_photo(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'pancard_photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:20480',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $pancard_photo = $request->pancard_photo;
        $photo = base64_encode(file_get_contents($pancard_photo));
        $url = $this->cdnLink . "api/file/v1/upload-user-kyc";
        $api_request_parameters = array(
            'image' => $photo,
            'name' => Auth::User()->company->company_website . '-' . Auth::id(),
            'type' => 4,
        );
        $method = 'POST';
        $header = ["Accept:application/json"];
        $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
        $res = json_decode($response);
        $status = $res->status;
        if ($status == 'success') {
            Member::where('user_id', Auth::id())->update(['pancard_photo' => $res->image_url]);
            $parent_id = array(1);
            $userdetails = User::find(Auth::id());
            $letter = collect([
                'title' => "Kyc Notification",
                'body' => "$userdetails->name $userdetails->last_name  updated his KYC kindly check",
            ]);
            $library = new BasicLibrary();
            $library->send_notification($parent_id, $letter);
            \Session::flash('success', 'Pancard Photo Successfully Updated');
            return redirect()->back();
        } else {
            \Session::flash('failure', $res->message);
            return redirect()->back();
        }
    }

    function cancel_cheque_photo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cancel_cheque' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:20480',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $cancel_cheque = $request->cancel_cheque;
        $photo = base64_encode(file_get_contents($cancel_cheque));
        $url = $this->cdnLink . "api/file/v1/upload-user-kyc";
        $api_request_parameters = array(
            'image' => $photo,
            'name' => Auth::User()->company->company_website . '-' . Auth::id(),
            'type' => 5,
        );
        $method = 'POST';
        $header = ["Accept:application/json"];
        $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
        $res = json_decode($response);
        $status = $res->status;
        if ($status == 'success') {
            Member::where('user_id', Auth::id())->update(['cancel_cheque' => $res->image_url]);
            $parent_id = array(1);
            $userdetails = User::find(Auth::id());
            $letter = collect([
                'title' => "Kyc Notification",
                'body' => "$userdetails->name $userdetails->last_name  updated his KYC kindly check",
            ]);
            $library = new BasicLibrary();
            $library->send_notification($parent_id, $letter);
            \Session::flash('success', 'Cancel Cheque Photo Successfully Updated');
            return redirect()->back();
        } else {
            \Session::flash('failure', $res->message);
            return redirect()->back();
        }
    }

    function address_proof_photo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address_proof' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:20480',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $address_proof = $request->address_proof;
        $photo = base64_encode(file_get_contents($address_proof));
        $url = $this->cdnLink . "api/file/v1/upload-user-kyc";
        $api_request_parameters = array(
            'image' => $photo,
            'name' => Auth::User()->company->company_website . '-' . Auth::id(),
            'type' => 6,
        );
        $method = 'POST';
        $header = ["Accept:application/json"];
        $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
        $res = json_decode($response);
        $status = $res->status;
        if ($status == 'success') {
            Member::where('user_id', Auth::id())->update(['address_proof' => $res->image_url]);
            $parent_id = array(1);
            $userdetails = User::find(Auth::id());
            $letter = collect([
                'title' => "Kyc Notification",
                'body' => "$userdetails->name $userdetails->last_name  updated his KYC kindly check",
            ]);
            $library = new BasicLibrary();
            $library->send_notification($parent_id, $letter);
            \Session::flash('success', 'Address Proof Photo Successfully Updated');
            return redirect()->back();
        } else {
            \Session::flash('failure', $res->message);
            return redirect()->back();
        }

    }


    function get_distric_by_state(Request $request)
    {
        if ($request->state_id) {
            $state_id = $request->state_id;
            $districts = District::where('state_id', $state_id)->get();
            $response = array();
            foreach ($districts as $value) {
                $product = array();
                $product["district_id"] = $value->id;
                $product["district_name"] = $value->district_name;
                array_push($response, $product);
            }
            return Response()->json(['status' => 'success', 'districts' => $response]);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'select state']);
        }
    }

    function update_verify_profile(Request $request)
    {
        $rules = array(
            'permanent_address' => 'required',
            'permanent_state' => 'required',
            'permanent_district' => 'required',
            'permanent_city' => 'required',
            'permanent_pin_code' => 'required',
            'shop_name' => 'required',
            'office_address' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $permanent_address = $request->permanent_address;
        $permanent_state = $request->permanent_state;
        $permanent_district = $request->permanent_district;
        $permanent_city = $request->permanent_city;
        $permanent_pin_code = $request->permanent_pin_code;
        $shop_name = $request->shop_name;
        $office_address = $request->office_address;
        Member::where('user_id', Auth::id())->update([
            'permanent_address' => $permanent_address,
            'permanent_city' => $permanent_city,
            'permanent_state' => $permanent_state,
            'permanent_district' => $permanent_district,
            'permanent_pin_code' => $permanent_pin_code,
            'shop_name' => $shop_name,
            'office_address' => $office_address,
        ]);
        return Response()->json(['status' => 'success', 'message' => 'Profile details update successfully']);
    }

    function verify_mobile(Request $request)
    {
        $otp = mt_rand(100000, 999999);
        $mobile_number = Auth::User()->mobile;
        $request_ip = request()->ip();
        $now = new \DateTime();
        $ctime = $now->format('Y-m-d H:i:s');
        $tableotp = Tableotp::where('mobile_number', $mobile_number)->first();
        if ($tableotp) {
            Tableotp::where('mobile_number', $mobile_number)->update([
                'otp' => $otp,
                'ip_address' => $request_ip,
                'status_id' => 3,
            ]);
        } else {
            Tableotp::insertGetId([
                'mobile_number' => $mobile_number,
                'ip_address' => $request_ip,
                'created_at' => $ctime,
                'otp' => $otp,
                'status_id' => 3,
            ]);
        }
        $message = "Dear partnter your profile activation otp is : $otp";
        $library = new SmsLibrary();
        $library->send_sms($mobile_number, $message);
        return response()->json([
            'status' => 'success',
            'mobile_number' => $mobile_number,
            'message' => 'Successfully',
        ]);
    }


    function verify_mobile_otp(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|exists:tableotps,mobile_number',
            'otp' => 'required|exists:tableotps,otp',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $otp = $request->otp;
        $tableotp = Tableotp::where('mobile_number', $mobile_number)->where('otp', $otp)->where('status_id', 3)->first();
        if ($tableotp) {
            Tableotp::where('id', $tableotp->id)->update(['status_id' => 1]);
            User::where('mobile', $tableotp->mobile_number)->update(['mobile_verified' => 1]);
            return Response()->json(['status' => 'success', 'message' => 'your profile is activated now please wait few moments']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'invalid otp']);
        }
    }

    function view_kyc()
    {

        $user_id = Auth::id();
        $userdetails = User::where('id', $user_id)->first();

        if ($userdetails->member->shop_photo) {
            $shop_photo = $userdetails->member->shop_photo;
        } else {
            $shop_photo = url('assets/img/no_image_available.jpeg');
        }
        if ($userdetails->member->gst_regisration_photo) {
            $gst_regisration_photo = $userdetails->member->gst_regisration_photo;
        } else {
            $gst_regisration_photo = url('assets/img/no_image_available.jpeg');
        }

        if ($userdetails->member->pancard_photo) {
            $pancard_photo = $userdetails->member->pancard_photo;
        } else {
            $pancard_photo = url('assets/img/no_image_available.jpeg');
        }

        if ($userdetails->member->cancel_cheque) {
            $cancel_cheque = $userdetails->member->cancel_cheque;
        } else {
            $cancel_cheque = url('assets/img/no_image_available.jpeg');
        }

        if ($userdetails->member->address_proof) {
            $address_proof = $userdetails->member->address_proof;
        } else {
            $address_proof = url('assets/img/no_image_available.jpeg');
        }

        if ($userdetails->member->profile_photo) {
            $profile_photo = $userdetails->member->profile_photo;
        } else {
            $profile_photo = url('assets/img/profile-pic.jpg');
        }
        $details = array(
            'shop_photo' => $shop_photo,
            'gst_regisration_photo' => $gst_regisration_photo,
            'pancard_photo' => $pancard_photo,
            'cancel_cheque' => $cancel_cheque,
            'address_proof' => $address_proof,
            'profile_photo' => $profile_photo,
            'name' => $userdetails->name . ' ' . $userdetails->last_name,
            'role_type' => $userdetails->role->role_title,
            'website_name' => $userdetails->company->company_name,
            'email' => $userdetails->email,
            'mobile' => $userdetails->mobile,
            'joining_date' => "$userdetails->created_at",
            'kyc_status' => $userdetails->member->kyc_status,
            'user_id' => $userdetails->id,
            'kyc_remark' => $userdetails->member->kyc_remark,


        );
        $page_title = $userdetails->name . ' Kyc';
        $data = array('page_title' => $page_title);
        return view('agent.view_kyc')->with($data)->with($details);
    }

    function my_settings()
    {
        $data = array('page_title' => 'Settings');
        return view('agent.my_settings')->with($data);
    }

    function save_settings(Request $request)
    {
        $rules = array(
            'day_book' => 'required',
            'daily_statement' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $day_book = $request->day_book;
        $daily_statement = $request->daily_statement;
        Profile::where('user_id', Auth::id())->update(['day_book' => $day_book, 'monthly_statement' => $daily_statement]);
        return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
    }

    function transaction_pin()
    {
        if (Auth::User()->company->transaction_pin == 1) {
            $data = array('page_title' => 'Transaction Pin');
            return view('agent.transaction_pin')->with($data);
        } else {
            return redirect()->back();
        }
    }

    function latlongSecurity()
    {
        $data = array('page_title' => 'Transaction Pin');
        return view('agent.latlongSecurity')->with($data);
    }


}
