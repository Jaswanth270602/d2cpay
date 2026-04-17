<?php

namespace App\Http\Controllers;

use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Validator;
use App\Models\Loginlog;
use App\Models\State;
use App\Models\District;
use Hash;
use App\Library\SmsLibrary;
use App\Helpers\UserSystemInfoHelper;
use App\Models\Frontbanner;
use App\Models\Notification;
use App\Models\Tableotp;
use App\Models\Member;
use App\Models\Service;
use App\Models\Provider;
use App\Models\Report;
use App\Models\Bankdetail;
use App\Models\Paymentmethod;
use App\Models\Loadcash;
use App\Models\Commission;
use App\Models\Company;
use App\Models\Role;
use App\Models\Api;
use App\Models\Sitesetting;
use App\Models\Masterbank;
use Helpers;
use DB;
use App\Library\BasicLibrary;
use App\Library\MemberLibrary;
use App\Balance;
use App\Profile;
use App\Models\Agentonboarding;
use App\Models\Userloginattempt;
use Str;
use \Crypt;
use App\Library\ValidationLibrary;
use App\Library\LocationRestrictionsLibrary;
use Carbon\Carbon;
use App\Services\AppEncryption;


class ApplicationController extends Controller
{


    public function __construct()
    {
        $this->company_id = Helpers::company_id()->id;
        $dt = Helpers::company_id();
        $this->company_id = $dt->id;
        $api = Api::where('vender_id', 10)->first();
        if ($api) {
            $this->key = 'Bearer ' . $api->api_key;
            $this->url = "https://api.pay2all.in/v1/money/";
            $this->api_id = $api->id;
        }
        $companies = Company::find($this->company_id);
        $this->cdnLink = (empty($companies)) ? '' : $companies->cdn_link;
        $this->encryptionKey = (empty($companies)) ? '' : $companies->encryptionKey;
        $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
        $this->brand_name = (empty($sitesettings)) ? '' : $sitesettings->brand_name;
    }

    function login(Request $request)
    {
        $decryptData = AppEncryption::decryptText($request->data, $this->encryptionKey);
        if ($decryptData['status_id'] == false) {
            return Response()->json(['status' => 'failure', 'message' => $decryptData['message']]);
        }
        $data = json_decode($decryptData['data'], true);
        $username = $data['username'];
        $password = $data['password'];
        $device_id = $data['device_id'];
        $latitude = $data['latitude'];
        $longitude = $data['longitude'];
        $request->merge([
            'username' => strtoupper($username),
            'password' => strtoupper($password),
            'device_id' => strtoupper($device_id),
            'latitude' => strtoupper($latitude),
            'longitude' => strtoupper($longitude),
        ]);
        $rules = array(
            'username' => 'required|exists:users,mobile|digits:10',
            'password' => 'required',
            'device_id' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first(), 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $request_ip = request()->ip();
        $userdetails = User::where('mobile', $username)->first();
        if ($userdetails) {
            $loginTime = time() - 60 * 5;
            $totalLoginAttempts = Userloginattempt::where('login_time', '>', $loginTime)->where('ip_address', $request_ip)->where('attempt_type', 'login')->count();
            if ($totalLoginAttempts >= 3) {
                return Response()->json(['status' => 'failure', 'message' => 'Too many failed login attempts. please login after 5 minutes!']);
            }
            if (Hash::check(trim($password), $userdetails->password)) {
                $user_id = $userdetails->id;
                $locationrestrictionsLibrary = new LocationRestrictionsLibrary();
                $isLoginValid = $locationrestrictionsLibrary->loginRestrictions($user_id, $latitude, $longitude);
                if ($isLoginValid == 0) {
                    $kilometer = $userdetails->company->login_restrictions_km;
                    return Response()->json(['status' => 'failure', 'message' => "You must be within $kilometer kilometer to access this service."]);
                }
                $companies = Company::find($userdetails->company_id);
                if ($companies->login_type == 1) {
                    $checkdevice = User::where('mobile', $username)->where('device_id', $device_id)->first();
                    if ($checkdevice) {
                        return $this->login_process($username, $password, $latitude, $longitude);
                    } else {
                        $otp = mt_rand(100000, 999999);
                        $otp = 123456;
                        User::where('mobile', $username)->update(['login_otp' => bcrypt($otp)]);
                        $message = "Dear $userdetails->name, your login verification code is $otp $this->brand_name";
                        $template_id = 1;
                        $library = new SmsLibrary();
                        $library->send_sms($username, $message, $template_id);
                        $mobile = substr($username, 0, 1) . '*******' . substr($username, -2);
                        $alertMessage = "To keep your account safe, we need to be sure it was you who just tried to sign in. We sent a text with a one-time code to: +91 $mobile";
                        return Response()->json(['status' => 'pending', 'message' => $alertMessage]);
                    }
                } else {
                    return $this->login_process($username, $password, $latitude, $longitude);
                }
            } else {
                $now = new \DateTime();
                $ctime = $now->format('Y-m-d H:i:s');
                Userloginattempt::insert([
                    'ip_address' => $request_ip,
                    'login_time' => time(),
                    'attempt_type' => 'login',
                    'created_at' => $ctime,
                ]);
                $totalLoginAttempts = $totalLoginAttempts++;
                $remainingAttempts = 3 - $totalLoginAttempts;
                return Response()->json(['status' => 'failure', 'message' => "Please enter valid login details. $remainingAttempts attempts remaining!"]);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Invalid username']);
        }
    }


    function validate_login(Request $request)
    {
        $decryptData = AppEncryption::decryptText($request->data, $this->encryptionKey);
        if ($decryptData['status_id'] == false) {
            return Response()->json(['status' => 'failure', 'message' => $decryptData['message']]);
        }
        $data = json_decode($decryptData['data'], true);
        $username = $data['username'];
        $password = $data['password'];
        $device_id = $data['device_id'];
        $otp = $data['otp'];
        $latitude = $data['latitude'];
        $longitude = $data['longitude'];
        $request->merge([
            'username' => strtoupper($username),
            'password' => strtoupper($password),
            'device_id' => strtoupper($device_id),
            'otp' => strtoupper($otp),
            'latitude' => strtoupper($latitude),
            'longitude' => strtoupper($longitude),
        ]);
        $rules = array(
            'username' => 'required|exists:users,mobile|digits:10',
            'password' => 'required',
            'device_id' => 'required',
            'otp' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'message' => 'validation errors', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $request_ip = request()->ip();
        $userdetails = User::where('mobile', $username)->first();
        if ($userdetails) {
            $loginTime = time() - 60 * 5;
            $totalLoginAttempts = Userloginattempt::where('login_time', '>', $loginTime)->where('ip_address', $request_ip)->where('attempt_type', 'login')->count();
            if ($totalLoginAttempts >= 3) {
                return Response()->json(['status' => 'failure', 'message' => 'Too many failed login attempts. please login after 5 minutes!']);
            }
            if (Hash::check(trim($password), $userdetails->password)) {
                $user_id = $userdetails->id;
                $locationrestrictionsLibrary = new LocationRestrictionsLibrary();
                $isLoginValid = $locationrestrictionsLibrary->loginRestrictions($user_id, $latitude, $longitude);
                if ($isLoginValid == 0) {
                    $kilometer = $userdetails->company->login_restrictions_km;
                    return Response()->json(['status' => 'failure', 'message' => "You must be within $kilometer kilometer to access this service."]);
                }

                if (Hash::check(trim($otp), $userdetails->login_otp)) {
                    User::where('mobile', $username)->update(['device_id' => $device_id]);
                    return $this->login_process($username, $password, $latitude, $longitude);
                } else {
                    return Response()->json(['status' => 'failure', 'message' => 'Invalid OTP']);
                }
            } else {
                $now = new \DateTime();
                $ctime = $now->format('Y-m-d H:i:s');
                Userloginattempt::insert([
                    'ip_address' => $request_ip,
                    'login_time' => time(),
                    'created_at' => $ctime,
                ]);
                $totalLoginAttempts = $totalLoginAttempts++;
                $remainingAttempts = 3 - $totalLoginAttempts;
                return Response()->json(['status' => 'failure', 'message' => "Please enter valid login details. $remainingAttempts attempts remaining!"]);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Invalid username']);
        }
    }

    function login_process($mobile, $password, $latitude, $longitude)
    {
        if (Auth::attempt(array('mobile' => $mobile, 'password' => $password, 'status_id' => 1))) {
            $request_ip = request()->ip();
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            $loginlogs = Loginlog::where('user_id', Auth::id())->where('ip_address', $request_ip)->first();
            if (empty($loginlogs)) {
                $template_id = 22;
                $message = "You Log in to $this->brand_name at $ctime with IP: $request_ip $this->brand_name";
                $library = new SmsLibrary();
                $library->send_sms($mobile, $message, $template_id);
            }
            Userloginattempt::where('ip_address', $request_ip)->whereIn('attempt_type', ['login', 'resendLoginOTP'])->delete();
            $new_session_id = \Session::getId(); //get new session_id after user sign in
            $user = User::where('mobile', $mobile)->first();
            if ($user->session_id != '') {
                $last_session = \Session::getHandler()->read($user->session_id);
                if ($last_session) {
                    if (\Session::getHandler()->destroy($user->session_id)) {

                    }
                }
            }
            User::where('id', $user->id)->update(['session_id' => $new_session_id]);
            $getip = UserSystemInfoHelper::get_ip();
            $getbrowser = UserSystemInfoHelper::get_browsers();
            $getdevice = UserSystemInfoHelper::get_device();
            $getos = UserSystemInfoHelper::get_os();
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            $locationData = \Location::get($getip);
            Loginlog::insertGetId([
                'user_id' => Auth::id(),
                'ip_address' => $getip,
                'get_browsers' => $getbrowser,
                'get_device' => $getdevice,
                'get_os' => $getos,
                'country_name' => $locationData->countryName,
                'country_code' => $locationData->countryCode,
                'region_code' => $locationData->regionCode,
                'region_name' => $locationData->regionName,
                'city_name' => $locationData->cityName,
                'zip_code' => $locationData->zipCode,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'mode' => 'APP',
                'created_at' => $ctime,
                'status_id' => 1,
            ]);
            $user_id = Auth::id();
            $library = new MemberLibrary();
            return $library->appUserDetails($user_id, $type = 'login');
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Username or Password Wrong']);
        }
    }

    function check_balance(Request $request)
    {
        $user_id = Auth::id();
        $library = new MemberLibrary();
        return $library->appUserDetails($user_id, $type = 'check-balance');
    }

    function get_virtual_account($mobile_number)
    {
        $url = "https://api.pay2all.in/v1/outlet?search_val=$mobile_number";
        $api_request_parameters = array();
        $method = 'GET';
        $header = ["Accept:application/json", "Authorization:" . $this->key];
        $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
        $res = json_decode($response);
        $total = $res->total;
        if ($total == 1) {
            $data = $res->data;
            foreach ($data as $value) {
                $icici_agent_id = $value->remark;
                $outlet_id = $value->id;
            }
        } else {
            $icici_agent_id = "";
            $outlet_id = "";
        }

        return ['icici_agent_id' => $icici_agent_id, 'outlet_id' => $outlet_id];
    }


    function change_password(Request $request)
    {
        $rules = array(
            'old_password' => 'required',
            'new_password' => 'required|different:old_password|string|min:8|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/',
            'confirm_password' => 'same:new_password',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'message' => 'validation error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $old_password = $request->old_password;
        $new_password = $request->new_password;
        $userdetail = User::find(Auth::id());
        $current_password = $userdetail->password;
        if (Hash::check($old_password, $current_password)) {
            $userdetail->password = Hash::make($new_password);
            $userdetail->password_changed_at = Carbon::now()->toDateTimeString();
            $userdetail->save();
            $user_id = Auth::id();
            $new_session_id = \Session::getId(); //get new session_id after user sign in
            User::where('id', $user_id)->update(['session_id' => $new_session_id]);
            return Response()->json(['status' => 'success', 'message' => 'password successfully changed']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'password not match']);
        }
    }

    function update_profile(Request $request)
    {
        $rules = array(
            'address' => 'required',
            'city' => 'required',
            'district_id' => 'required|exists:districts,id',
            'state_id' => 'required|exists:states,id',
            'pin_code' => 'required',
            'shop_name' => 'required',
            'office_address' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $address = $request->address;
        $city = $request->city;
        $district_id = $request->district_id;
        $state_id = $request->state_id;
        $pin_code = $request->pin_code;
        $shop_name = $request->shop_name;
        $office_address = $request->office_address;
        Member::where('user_id', Auth::id())->update([
            'address' => $address,
            'city' => $city,
            'district_id' => $district_id,
            'state_id' => $state_id,
            'pin_code' => $pin_code,
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
        $template_id = 1;
        $library = new SmsLibrary();
        $library->send_sms($mobile_number, $message, $template_id);
        return response()->json([
            'status' => 'success',
            'mobile_number' => $mobile_number,
            'message' => 'OTP successfully sent to your register mobile number',
        ]);
    }

    function confirm_verify_mobile(Request $request)
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

    function mark_all_read(Request $request)
    {
        Auth::User()->notifications->markAsRead();
        return Response()->json(['status' => 'success', 'message' => 'Success']);
    }

    function read_notification(Request $request)
    {
        $id = $request->id;
        $notifications = Notification::find($id);
        if ($notifications) {
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            Notification::where('id', $id)->update(['read_at' => $ctime]);
            return Response()->json(['status' => 'success', 'message' => 'Success']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'not found']);
        }
    }


    function update_profile_photo(Request $request)
    {
        $rules = array(
            'profile_photo' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $profile_photo = $request->profile_photo;
        $url = $this->cdnLink . "api/file/v1/upload-user-kyc";
        $api_request_parameters = array(
            'image' => $profile_photo,
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
            return Response()->json(['status' => 'success', 'message' => 'Profile Photo Successfully Updated']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => $res->message]);
        }
    }


    function update_shop_photo(Request $request)
    {
        $rules = array(
            'shop_photo' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $shop_photo = $request->shop_photo;
        $url = $this->cdnLink . "api/file/v1/upload-user-kyc";
        $api_request_parameters = array(
            'image' => $shop_photo,
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
            return Response()->json(['status' => 'success', 'message' => 'Shop Photo Successfully Updated']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => $res->message]);
        }
    }

    function update_gst_regisration_photo(Request $request)
    {
        $rules = array(
            'gst_regisration_photo' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $gst_regisration_photo = $request->gst_regisration_photo;
        $url = $this->cdnLink . "api/file/v1/upload-user-kyc";
        $api_request_parameters = array(
            'image' => $gst_regisration_photo,
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
            return Response()->json(['status' => 'success', 'message' => 'GST Regisration Photo Successfully Updated']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => $res->message]);
        }
    }

    function update_pancard_photo(Request $request)
    {
        $rules = array(
            'pancard_photo' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $pancard_photo = $request->pancard_photo;
        $url = $this->cdnLink . "api/file/v1/upload-user-kyc";
        $api_request_parameters = array(
            'image' => $pancard_photo,
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
            return Response()->json(['status' => 'success', 'message' => 'Pancard Photo Successfully Updated']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => $res->message]);
        }
    }

    function cancel_cheque_photo(Request $request)
    {
        $rules = array(
            'cancel_cheque' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $cancel_cheque = $request->cancel_cheque;
        $url = $this->cdnLink . "api/file/v1/upload-user-kyc";
        $api_request_parameters = array(
            'image' => $cancel_cheque,
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
            return Response()->json(['status' => 'success', 'message' => 'Cancel Cheque Photo Successfully Updated']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => $res->message]);
        }
    }


    function address_proof_photo(Request $request)
    {
        $rules = array(
            'address_proof' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $address_proof = $request->address_proof;
        $url = $this->cdnLink . "api/file/v1/upload-user-kyc";
        $api_request_parameters = array(
            'image' => $address_proof,
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
            return Response()->json(['status' => 'success', 'message' => 'Address Proof Photo Successfully Updated']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => $res->message]);
        }
    }


    function get_provider(Request $request)
    {
        $library = new BasicLibrary();
        $serviceIds = $library->getTelecomServiceId();
        $providers = Provider::whereIn('service_id', $serviceIds)
            ->where('status_id', 1)
            ->select('id', 'provider_name', 'service_id', 'help_line', 'provider_image')
            ->get();
        $response = array();
        foreach ($providers as $value) {
            $product = array();
            $product["provider_id"] = $value->id;
            $product["provider_name"] = $value->provider_name;
            $product["service_id"] = $value->service_id;
            $product["service_name"] = $value->service->service_name;
            $product["help_line"] = $value->help_line;
            $product["provider_icon"] = $this->cdnLink . $value->provider_image;
            array_push($response, $product);
        }
        return Response()->json(['status' => 'success', 'providers' => $response]);
    }

    function fund_request_bank_list(Request $request)
    {
        $bankdetails = Bankdetail::where('company_id', Auth::User()->company_id)
            ->where('status_id', 1)
            ->select('id', 'bank_name', 'bank_account_number', 'bank_ifsc', 'bank_branch')
            ->get();
        $response = array();
        foreach ($bankdetails as $value) {
            $product = array();
            $product["bankdetail_id"] = $value->id;
            $product["bank_name"] = $value->bank_name;
            $product["holder_name"] = $value->bank_account_name;
            $product["account_number"] = $value->bank_account_number;
            $product["ifsc_code"] = $value->bank_ifsc;
            $product["branch"] = $value->bank_branch;
            array_push($response, $product);
        }
        return Response()->json(['status' => 'success', 'banks' => $response]);
    }

    function payment_method(Request $request)
    {
        $methods = Paymentmethod::where('status_id', 1)->select('id', 'payment_type')->get();
        $response = array();
        foreach ($methods as $value) {
            $product = array();
            $product["paymentmethod_id"] = $value->id;
            $product["payment_type"] = $value->payment_type;
            array_push($response, $product);
        }
        return Response()->json(['status' => 'success', 'methods' => $response]);
    }

    function payment_request_now(Request $request)
    {
        $min_amount = 10;
        $max_amount = 1000000;
        $rules = array(
            'bankdetail_id' => 'required|digits_between:1,6',
            'paymentmethod_id' => 'required|digits_between:1,6',
            'payment_date' => 'required|string|min:3|max:20',
            'amount' => 'required|numeric|between:' . $min_amount . ',' . $max_amount . '',
            'bankref' => 'required|unique:loadcashes|alpha_dash|string|min:3|max:20',
            'latitude' => 'required',
            'longitude' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $bankdetail_id = $request->bankdetail_id;
        $paymentmethod_id = $request->paymentmethod_id;
        $payment_date = $request->payment_date;
        $amount = $request->amount;
        $bankref = $request->bankref;
        // $parent_id = Auth::User()->parent_id;
        $parent_id = 1;
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $request_ip = request()->ip();
        $locationrestrictionsLibrary = new LocationRestrictionsLibrary();
        $isLoginValid = $locationrestrictionsLibrary->loginRestrictions($user_id, $latitude, $longitude);
        if ($isLoginValid == 0) {
            $kilometer = Auth::User()->company->login_restrictions_km;
            return Response()->json(['status' => 'failure', 'message' => "You must be within $kilometer kilometer to access this service."]);
        }
        $reports = Report::where('txnid', $bankref)->first();
        if ($reports) {
            return Response()->json(['status' => 'failure', 'message' => 'duplicate utr number']);
        }
        $now = new \DateTime();
        $ctime = $now->format('Y-m-d H:i:s');
        Loadcash::insertGetId([
            'user_id' => Auth::id(),
            'payment_date' => $payment_date,
            'paymentmethod_id' => $paymentmethod_id,
            'bankdetail_id' => $bankdetail_id,
            'amount' => $amount,
            'bankref' => $bankref,
            'parent_id' => $parent_id,
            'created_at' => $ctime,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'ip_address' => $request_ip,
            'status_id' => 3,
        ]);
        $user_id = array($parent_id);
        $bankdetails = Bankdetail::find($bankdetail_id);
        $parentdetails = User::find($parent_id);
        $username = Auth::User()->name;
        $letter = collect([
            'title' => "Payment Rquest Amount $amount",
            'body' => "Dear $parentdetails->name you received  rs $amount payment request in $bankdetails->bank_name Ref Number is : $bankref request send by $username kindly verify amount with your bank and update ASAP thanks",
        ]);
        $library = new BasicLibrary();
        $library->send_notification($user_id, $letter);
        return Response()->json(['status' => 'success', 'message' => 'payment request successfully submited']);
    }

    function fund_request_report(Request $request)
    {
        $reports = Loadcash::where('user_id', Auth::id())->orderBy('id', 'DESC')->paginate(20);
        $response = array();
        foreach ($reports as $value) {
            $product = array();
            $product["id"] = $value->id;
            $product["created_at"] = $value->created_at->format('Y-m-d h:m:s');
            $product["payment_date"] = $value->payment_date;
            $product["bank_name"] = $value->bankdetail->bank_name;
            $product["payment_type"] = $value->paymentmethod->payment_type;
            $product["amount"] = number_format($value->amount, 2);
            $product["bankref"] = $value->bankref;
            $product["status"] = $value->status->status;
            array_push($response, $product);
        }
        return response()->json([
            'total' => $reports->total(),
            'pageNumber' => $reports->currentPage(),
            'nextPageUrl' => $reports->nextPageUrl(),
            'page' => $reports->currentPage(),
            'pages' => $reports->lastPage(),
            'perpage' => $reports->perPage(),
            'reports' => $response,
            'status' => 'success',
        ]);
    }

    function commission_service_list(Request $request)
    {
        $recharges = Service::where('status_id', 1)->select('id', 'service_name', 'service_image', 'bbps')->get();
        $recharge = array();
        foreach ($recharges as $value) {
            $product = array();
            $product["service_id"] = $value->id;
            $product["service_name"] = $value->service_name;
            $product["service_image"] = $this->cdnLink . '' . $value->service_image;
            $product["bbps"] = $value->bbps;
            array_push($recharge, $product);
        }
        return Response()->json(['status' => 'success', 'services' => $recharge]);
    }

    function commission_providers(Request $request)
    {
        $rules = array(
            'service_id' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $service_id = $request->service_id;
        $providers = Provider::where('service_id', $service_id)
            ->where('status_id', 1)
            ->select('id', 'provider_name', 'service_id', 'provider_image')
            ->get();
        $response = array();
        foreach ($providers as $value) {
            $product = array();
            $product["provider_id"] = $value->id;
            $product["provider_name"] = $value->provider_name;
            $product["service_id"] = $value->service_id;
            $product["service_name"] = $value->service->service_name;
            $product["provider_icon"] = $this->cdnLink . $value->provider_image;
            array_push($response, $product);
        }
        return Response()->json(['status' => 'success', 'providers' => $response]);
    }

    function my_commission(Request $request)
    {
        $rules = array(
            'provider_id' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $provider_id = $request->provider_id;
        $scheme_id = Auth::User()->scheme_id;
        $commission = Commission::where('provider_id', $provider_id)
            ->where('scheme_id', $scheme_id)
            ->select('r', 'd', 'sd', 'st', 'min_amount', 'max_amount', 'type')
            ->get();
        if (count($commission) == 0) {
            return Response()->json(['status' => 'failure', 'message' => 'Slab Not Found']);
        } else {
            $response = array();
            foreach ($commission as $value) {
                if (Auth::User()->role_id == 8 || Auth::User()->role_id == 9 || Auth::User()->role_id == 10) {
                    $comm = $value->r;
                } elseif (Auth::User()->role_id == 7) {
                    $comm = $value->d;
                } elseif (Auth::User()->role_id == 6) {
                    $comm = $value->sd;
                } elseif (Auth::User()->role_id == 5) {
                    $comm = $value->st;
                }
                $product = array();
                $product["min_amount"] = $value->min_amount;
                $product["max_amount"] = $value->max_amount;
                $product["type"] = ($value->type == 0) ? '%' : 'Rs';
                $product["commission"] = $comm;
                array_push($response, $product);
            }
            return Response()->json(['status' => 'success', 'commission' => $response]);
        }
    }

    function company_contact_details(Request $request)
    {
        $company = Company::where('id', Auth::User()->company_id)
            ->select('company_name', 'company_email', 'company_address', 'company_address_two', 'support_number', 'whatsapp_number')
            ->first();
        if ($company) {
            $details = array(
                'company_name' => $company->company_name,
                'company_email' => $company->company_email,
                'company_address' => $company->company_address,
                'company_address_two' => $company->company_address_two,
                'support_number' => $company->support_number,
                'whatsapp_number' => $company->whatsapp_number,
            );
            return Response()->json([
                'status' => 'success',
                'message' => 'success',
                'details' => $details
            ]);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Details not found']);
        }
    }

    function aeps_outlet_id(Request $request)
    {
        $services = Service::where('id', 13)->where('status_id', 1)->first();
        if ($services) {
            $aeps = $this->get_aeps_outlet_id();
            $icici_agent_id = $aeps['icici_agent_id'];
            $outlet_id = $aeps['outlet_id'];
            return Response()->json([
                'status' => 'success',
                'icici_agent_id' => $icici_agent_id,
                'outlet_id' => $outlet_id,
            ]);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Service not activate kindly contact customer care']);
        }


    }

    function get_aeps_outlet_id()
    {
        $mobile_number = Auth::User()->mobile;
        $url = "https://api.pay2all.in/v1/outlet?search_val=$mobile_number";
        $api_request_parameters = array();
        $method = 'GET';
        $header = ["Accept:application/json", "Authorization:" . $this->key];
        $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
        $res = json_decode($response);
        $total = $res->total;
        if ($total == 1) {
            $data = $res->data;
            foreach ($data as $value) {
                $icici_agent_id = $value->remark;
                $outlet_id = $value->id;
            }
        } else {
            $icici_agent_id = "";
            $outlet_id = "";
        }

        return ['icici_agent_id' => $icici_agent_id, 'outlet_id' => $outlet_id];
    }

    function get_users(Request $request)
    {
        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);
        $users = User::whereIn('id', $my_down_member)
            ->whereNotIn('id', [Auth::id()])
            ->select('id', 'name', 'last_name', 'mobile', 'email', 'role_id', 'balance_id')
            ->get();
        $response = array();
        foreach ($users as $value) {
            $product = array();
            $product["id"] = $value->id;
            $product["name"] = $value->name . ' ' . $value->last_name;
            $product["mobile"] = $value->mobile;
            $product["email"] = $value->email;
            $product["member_type"] = $value->role->role_title;
            $product["normal_balance"] = number_format($value->balance->user_balance, 2);
            array_push($response, $product);
        }
        return Response()->json(['status' => 'success', 'users' => $response]);
    }

    function get_roles(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $roles = Role::where('id', '>', Auth::user()->role_id)->where('status_id', 1)->get();
        } else {
            $roles = Role::where('id', '>', Auth::user()->role_id)
                ->whereNotIn('id', [9, 10])
                ->select('id', 'role_title')
                ->where('status_id', 1)->get();
        }
        $response = array();
        foreach ($roles as $value) {
            $product = array();
            $product["role_id"] = $value->id;
            $product["role_title"] = $value->role_title;
            array_push($response, $product);
        }
        return Response()->json(['status' => 'success', 'roles' => $response]);
    }

    function ekyc_update(Request $request)
    {
        $rules = array(
            'status_id' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $status_id = $request->status_id;
        if ($status_id == 1) {
            User::where('id', Auth::id())->update(['ekyc' => 1]);
            Profile::where('user_id', Auth::id())->update(['money' => 1, 'aeps' => 1]);
            Member::where('user_id', Auth::id())->update(['kyc_status' => 1, 'kyc_remark' => 'Ekyc']);
        }
        return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
    }

    function page_content()
    {
        if (!empty($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
        } else {
            $host = "localhost:8888";
        }
        $company = Company::where('company_website', $host)->where('status_id', 1)->first();
        if ($company) {
            $about = "$company->company_name is a tool to deliver leading payment solutions reliably to its customers by acing infrastructure and equipment. With a team of passionate and skilled professionals. $company->company_name provides an opportunity to generate good revenue with less efforts. $company->company_name believes in the growing Digital India notion and is taking the step into the future with a vision of advancement. A decade ago, we were unaware of the concept of smart phones. Trace another decade backwards and internet was an alien concept. Another decade and computers were big boxes of complex machinery that people looked at with awe! Such exponential growth is phenomenal and technology will keep on improving in the future. Our team at demo.bceres.com strives towards growth by providing the best opportunities with complete transparency in the process.We work towards consistency, reliability, and fidelity.";
            return Response()->json(['status' => 'success', 'about' => $about, 'company_address' => $company->company_address, 'company_email' => $company->company_email, 'company_address_two' => $company->company_address_two, 'support_number' => $company->support_number, 'whatsapp_number' => $company->whatsapp_number, 'company_name' => $company->company_name]);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Username Or Password Wrong']);
        }
    }

    function commonList()
    {
        $stateList = Self::state_list();
        $bankList = Self::getBankList();
        $providerList = Self::getProviderList();
        return Response()->json([
            'status' => 'Success',
            'stateList' => $stateList,
            'bankList' => $bankList,
            'providerList' => $providerList,
        ]);
    }

    function getProviderList()
    {
        $library = new BasicLibrary();
        $serviceIds = $library->getTelecomServiceId();
        $providers = Provider::whereIn('service_id', $serviceIds)
            ->where('status_id', 1)
            ->select('id', 'provider_name', 'service_id', 'help_line', 'provider_image')
            ->get();
        $response = array();
        foreach ($providers as $value) {
            $product = array();
            $product["provider_id"] = $value->id;
            $product["provider_name"] = $value->provider_name;
            $product["service_id"] = $value->service_id;
            $product["service_name"] = $value->service->service_name;
            $product["help_line"] = $value->help_line;
            $product["provider_icon"] = $this->cdnLink . $value->provider_image;
            array_push($response, $product);
        }
        return $response;
    }

    function getBankList()
    {
        $masterbank = Masterbank::where('status_id', 1)->select('bank_id', 'bank_name', 'ifsc')->get();
        $response = array();
        foreach ($masterbank as $value) {
            $product = array();
            $product["bank_id"] = $value->bank_id;
            $product["bank_name"] = $value->bank_name;
            $product["ifsc_code"] = $value->ifsc;
            array_push($response, $product);
        }
        return $response;
    }


    function state_list()
    {
        $state = State::where('status_id', 1)->select('id', 'name')->get();
        $response = array();
        foreach ($state as $value) {
            $state_id = $value->id;
            $district_list = $this->district_list($state_id);
            $product = array();
            $product["state_id"] = $value->id;
            $product["state_name"] = $value->name;
            $product["district_list"] = $district_list;
            array_push($response, $product);
        }
        return $response;
    }

    function district_list($state_id)
    {
        $district = District::select('id', 'district_name')->where('status_id', 1)->where('state_id', $state_id)->get();
        $response = array();
        foreach ($district as $value) {
            $product = array();
            $product["district_id"] = $value->id;
            $product["district_name"] = $value->district_name;
            array_push($response, $product);
        }
        return $response;
    }

    public function logoutOtherDevices($password, $attribute = 'password')
    {
        if (!Auth::user()) {
            return;
        }
        return tap(Auth::user()->forceFill([
            $attribute => Hash::make($password),
        ]))->save();
    }

    public function apiLogin(Request $request)
    {
        $credentials = $request->only('mobile', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $user->tokens()->delete();
            $token = $user->createToken('Personal Access Token')->accessToken;
            return response()->json(['token' => $token], 200);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

}
