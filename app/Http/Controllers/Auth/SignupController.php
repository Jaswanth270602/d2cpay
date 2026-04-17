<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use App\Models\User;
use App\Models\State;
use App\Models\District;
use App\Models\Company;
use Str;
use App\Models\Balance;
use App\Models\Profile;
use App\Models\Member;
use App\Models\Report;
use Helpers;
use DB;
use App\Models\Sitesetting;
use App\Library\SmsLibrary;
use App\Library\MemberLibrary;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;

class SignupController extends Controller
{

    public function __construct()
    {
        $this->company_id = Helpers::company_id()->id;
        $companies = Helpers::company_id();
        $this->company_id = $companies->id;
        $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
        if ($sitesettings) {
            $this->brand_name = $sitesettings->brand_name;
            $this->registration_status = $sitesettings->registration_status;
            $this->registration_scheme_id = $sitesettings->registration_scheme_id;
            $this->registration_role_id = $sitesettings->registration_role_id;
            $this->registration_parent_id = $sitesettings->registration_parent_id;
            $this->registration_state_id = $sitesettings->registration_state_id;
            $this->registration_district_id = $sitesettings->registration_district_id;
        } else {
            $this->brand_name = "";
            $this->registration_status = 0;
            $this->registration_scheme_id = 0;
            $this->registration_role_id = 9;
            $this->registration_parent_id = 1;
            $this->registration_state_id = 2;
            $this->registration_district_id = 38;
        }
    }

    function sign_up($slug = null)
    {
        if ($this->registration_status == 1) {
            $data = array(
                'referral_code' => (empty($slug) ? '' : $slug),
            );
            return view('auth.sign_up')->with($data);
        } else {
            return redirect()->back();
        }
    }

    function register_now(Request $request)
    {
      //  return Response()->json(['status' => 'failure', 'message' => 'Something went wrong']);
        if ($this->registration_status == 1) {
            $rules = array(
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email|unique:users',
                'mobile' => 'required|unique:users|digits:10',
                'shop_name' => 'required',
                'address' => 'required',
                'city' => 'required',
                'pin_code' => 'required|digits:6|integer',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $first_name = $request->first_name;
            $last_name = $request->last_name;
            $email = $request->email;
            $mobile = $request->mobile;
            $shop_name = $request->shop_name;
            $address = $request->address;
            $parentDetails = Self::validatereferralCode($request->referral_code);
            $company_id = $parentDetails['company_id'];
            $parent_id = $parentDetails['parent_id'];
            $role_id = $this->registration_role_id;
            $password = mt_rand();
            $state_id = $this->registration_state_id;
            $district_id = $this->registration_district_id;
            $pin_code = $request->pin_code;
            $scheme_id = $this->registration_scheme_id;
            $gst_type = 0;
            $user_gst_type = 0;
            $lock_amount = 0;
            $office_address = $address;
            $city = $request->city;
            $pan_number = "";
            $gst_number = "";
            $active_services = '1,2,3,4,5,6';
            $library = new MemberLibrary();
            return $library->storeMember($first_name, $last_name, $email, $password, $mobile, $role_id, $parent_id, $scheme_id, $company_id, $gst_type, $user_gst_type, $lock_amount, $address, $city, $state_id, $district_id, $pin_code, $shop_name, $office_address, $pan_number, $gst_number, $active_services);

        }else{
            return Response()->json(['status' => 'failure', 'message' => 'registration option is de-active from admin side']);
        }
        }


    function validatereferralCode($referral_code)
    {
        $host = $_SERVER['HTTP_HOST'];
        $compnies = Company::where('company_website', $host)->where('status_id', 1)->first();
        if ($compnies && !empty($referral_code)) {
            $referralMobile = base64_decode($referral_code);
            $referralDetails = User::where('mobile', $referralMobile)->first();
            if ($referralDetails){
                return ['company_id' => $referralDetails->company_id, 'parent_id' => $referralDetails->id];
            }else{
                return ['company_id' => 1, 'parent_id' => $this->registration_parent_id];
            }
        } else {
            return ['company_id' => 1, 'parent_id' => $this->registration_parent_id];
        }

    }
}
