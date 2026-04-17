<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Role;
use App\Models\User;
use App\Models\State;
use App\Models\Member;
use Validator;
use Hash;
use Helpers;
use App\Models\Sitesetting;
use App\Models\Company;
use App\Library\SmsLibrary;
use Carbon\Carbon;

class ProfileController extends Controller
{

    public function __construct()
    {
        $this->company_id = Helpers::company_id()->id;
        $companies = Helpers::company_id();
        $this->company_id = $companies->id;
        $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
        if ($sitesettings) {
            $this->brand_name = $sitesettings->brand_name;
        } else {
            $this->brand_name = "";
        }
        $companies = Company::find($this->company_id);
        $this->cdnLink = (empty($companies)) ? '' : $companies->cdn_link;
    }

    function my_profile()
    {
        $data = array('page_title' => 'My Profile');
        $roles = Role::get();
        $circles = State::where('status_id', 1)->get();
        return view('admin.my_profile', compact('roles', 'circles'))->with($data);
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
        if (Auth::User()->role_id == 1) {
            $user_id = $request->user_id;
        } else {
            $user_id = Auth::id();
        }
        $shop_photo = $request->shop_photo;
        $photo = base64_encode(file_get_contents($shop_photo));
        $url = $this->cdnLink . "api/file/v1/upload-user-kyc";
        $api_request_parameters = array(
            'image' => $photo,
            'name' => Auth::User()->company->company_website . '-' . $user_id,
            'type' => 2,
        );
        $method = 'POST';
        $header = ["Accept:application/json"];
        $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
        $res = json_decode($response);
        $status = $res->status;
        if ($status == 'success') {
            Member::where('user_id', $user_id)->update(['shop_photo' => $res->image_url]);
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
        if (Auth::User()->role_id == 1) {
            $user_id = $request->user_id;
        } else {
            $user_id = Auth::id();
        }
        $gst_regisration_photo = $request->gst_regisration_photo;
        $photo = base64_encode(file_get_contents($gst_regisration_photo));
        $url = $this->cdnLink . "api/file/v1/upload-user-kyc";
        $api_request_parameters = array(
            'image' => $photo,
            'name' => Auth::User()->company->company_website . '-' . $user_id,
            'type' => 3,
        );
        $method = 'POST';
        $header = ["Accept:application/json"];
        $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
        $res = json_decode($response);
        $status = $res->status;
        if ($status == 'success') {
            Member::where('user_id', $user_id)->update(['gst_regisration_photo' => $res->image_url]);
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
        if (Auth::User()->role_id == 1) {
            $user_id = $request->user_id;
        } else {
            $user_id = Auth::id();
        }
        $pancard_photo = $request->pancard_photo;
        $photo = base64_encode(file_get_contents($pancard_photo));
        $url = $this->cdnLink . "api/file/v1/upload-user-kyc";
        $api_request_parameters = array(
            'image' => $photo,
            'name' => Auth::User()->company->company_website . '-' . $user_id,
            'type' => 4,
        );
        $method = 'POST';
        $header = ["Accept:application/json"];
        $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
        $res = json_decode($response);
        $status = $res->status;
        if ($status == 'success') {
            Member::where('user_id', $user_id)->update(['pancard_photo' => $res->image_url]);
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
        if (Auth::User()->role_id == 1) {
            $user_id = $request->user_id;
        } else {
            $user_id = Auth::id();
        }
        $cancel_cheque = $request->cancel_cheque;
        $photo = base64_encode(file_get_contents($cancel_cheque));
        $url = $this->cdnLink . "api/file/v1/upload-user-kyc";
        $api_request_parameters = array(
            'image' => $photo,
            'name' => Auth::User()->company->company_website . '-' . $user_id,
            'type' => 5,
        );
        $method = 'POST';
        $header = ["Accept:application/json"];
        $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
        $res = json_decode($response);
        $status = $res->status;
        if ($status == 'success') {
            Member::where('user_id', $user_id)->update(['cancel_cheque' => $res->image_url]);
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
        if (Auth::User()->role_id == 1) {
            $user_id = $request->user_id;
        } else {
            $user_id = Auth::id();
        }
        $address_proof = $request->address_proof;
        $photo = base64_encode(file_get_contents($address_proof));
        $url = $this->cdnLink . "api/file/v1/upload-user-kyc";
        $api_request_parameters = array(
            'image' => $photo,
            'name' => Auth::User()->company->company_website . '-' . $user_id,
            'type' => 6,
        );
        $method = 'POST';
        $header = ["Accept:application/json"];
        $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
        $res = json_decode($response);
        $status = $res->status;
        if ($status == 'success') {
            Member::where('user_id', $user_id)->update(['address_proof' => $res->image_url]);
            \Session::flash('success', 'Address Proof Photo Successfully Updated');
            return redirect()->back();
        } else {
            \Session::flash('failure', $res->message);
            return redirect()->back();
        }
    }

    function transaction_pin()
    {
        if (Auth::User()->company->transaction_pin == 1) {
            $data = array('page_title' => 'Transaction Pin');
            return view('admin.transaction_pin')->with($data);
        } else {
            return redirect()->back();
        }
    }

    function send_transaction_pin_otp(Request $request)
    {
        if (Auth::User()->company->transaction_pin == 1) {
            $user_id = Auth::id();
            $userDetails = User::find($user_id);
            $otp = mt_rand(100000, 999999);
            User::where('id', $userDetails->id)->update(['login_otp' => $otp]);
            $message = "Dear $userDetails->name, your generate transaction pin one time password is $otp $this->brand_name";
            $template_id = 18;
            $library = new SmsLibrary();
            $library->send_sms($userDetails->mobile, $message, $template_id);
            return Response()->json(['status' => 'success', 'message' => 'OTP has been sent to authorised person mobile number']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Kindly contact customer care']);
        }
    }

    function create_transaction_pin(Request $request)
    {
        $rules = array(
            'password' => 'required',
            'transaction_pin' => 'required|digits:6',
            'otp' => 'required|digits:6',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $password = $request->password;
        $transaction_pin = $request->transaction_pin;
        $user_id = Auth::id();
        $userDetails = User::find($user_id);
        if ($request->otp == Auth::User()->login_otp) {
            $current_password = $userDetails->password;
            if (Hash::check($password, $current_password)) {
                User::where('id', $user_id)->update(['transaction_pin' => bcrypt($transaction_pin)]);
                return Response()->json(['status' => 'success', 'message' => 'Transaction pin created successful..!']);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Password is wrong']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Invalid One Time Password']);
        }
    }
}
