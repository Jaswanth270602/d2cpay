<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use Helpers;
use Str;
use DB;
use App\Models\Api;
use App\Models\Registerlog;
use App\Models\Sitesetting;
use App\Library\MemberLibrary;
use Illuminate\Support\Facades\File;


class SignupWithAadharController extends Controller
{

    public function __construct()
    {
        $this->api_id = 1;
        $apis = Api::find($this->api_id);
        $this->api_token = $apis->api_key ?? '';
        $this->base_url = 'https://mpayment.in/api/aadhaar-verification/v1/';

        $this->state_id = 9;
        $this->district_id = 326;

        $this->company_id = 1;
        $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
        $this->brand_name = (empty($sitesettings)) ? '' : $sitesettings->brand_name;


    }

    function sign_up()
    {
        return view('auth.signUpWithAadhar');
    }

    function sendAadharOTP(Request $request)
    {
        $rules = array(
            'aadhar_aumber' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $aadhar_aumber = $request->aadhar_aumber;
        $url = $this->base_url . 'send-otp';
        $api_request_parameters = array(
            'api_token' => $this->api_token,
            'aadhaar_number' => $aadhar_aumber,
        );
        $method = 'POST';
        $header = ["Accept:application/json"];
        $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
        $responseData = json_decode($response);
        $status = $responseData->status ?? 'failure';
        if ($status === 'success') {
            return response()->json([
                'status' => 'success',
                'message' => $responseData->message ?? 'Operation completed successfully.',
                'client_id' => $responseData->client_id ?? null,
                'reference_id' => $responseData->reference_id ?? null,
            ]);
        }
        return response()->json([
            'status' => 'failure',
            'message' => $responseData->message ?? 'An error occurred during the process. Please try again later.',
        ]);
    }

    function aadharOtpVerify(Request $request)
    {
        $rules = array(
            'aadhar_aumber' => 'required',
            'client_id' => 'required',
            'reference_id' => 'required',
            'otp' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $aadhar_aumber = $request->aadhar_aumber;
        $client_id = $request->client_id;
        $reference_id = $request->reference_id;
        $otp = $request->otp;
        $mode = "WEB";
        return Self::confirmOtpMiddle($aadhar_aumber, $client_id, $reference_id, $otp, $mode);
    }

    function confirmOtpMiddle($aadhar_aumber, $client_id, $reference_id, $otp, $mode)
    {
        $url = $this->base_url . 'verify-otp';
        $api_request_parameters = array(
            'api_token' => $this->api_token,
            'aadhaar_number' => $aadhar_aumber,
            'client_id' => $client_id,
            'reference_id' => $reference_id,
            'otp' => $otp,
        );
        $method = 'POST';
        $header = ["Accept:application/json"];
        $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
        //$response = '{"status":"success","message":"Successful..!","data":{"ref_id":"28470576","status":"VALID","message":"Aadhaar Card Exists","care_of":"S/O Sankaraiah","address":"6-75A, 2nd ward, mohiddin puram, Mohiddin Puram, Ardhaveedu, Prakasam, Bogolu, Andhra Pradesh, India, 523336","dob":"16-04-1993","email":"34754f32efd1f76374971fa73b9624a92bcd216d14a2092a229d426db076ce56","gender":"M","name":"Golla Siva","split_address":{"country":"India","dist":"Prakasam","house":"6-75A","landmark":"","pincode":"523336","po":"Mohiddin Puram","state":"Andhra Pradesh","street":"2nd ward","subdist":"Ardhaveedu","vtc":"Bogolu","locality":"mohiddin puram"},"year_of_birth":"1993","mobile_hash":"743589839f093a90e2bed5fe780f449b7238054b966030d72b97d8e4070f3dd0","photo_link":"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","share_code":"2345","xml_file":""}}';
        $res = json_decode($response);
        $status = $res->status ?? 'failure';
        if ($status === 'success') {
            $data = $res->data;
            if ($data->status == 'VALID') {
                Self::saveRegisterLogs($data, $aadhar_aumber);
                $registerlogs = Registerlog::where('aadhar_aumber', $aadhar_aumber)->orderBy('id', 'desc')->first();
                $data = array(
                    'verify_id' => $registerlogs->id,
                    'full_name' => $registerlogs->full_name,
                    'first_name' => $registerlogs->first_name,
                    'last_name' => $registerlogs->last_name,
                    'care_of' => $registerlogs->care_of,
                    'address' => $registerlogs->address,
                    'dob' => $registerlogs->dob,
                    'gender' => $registerlogs->gender,
                    'pincode' => $registerlogs->pincode,
                    'profile_photo' => url('profile-pic') . '/' . $registerlogs->profile_photo,
                );
                return Response()->json(['status' => 'success', 'message' => 'Successful..', 'data' => $data]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => $res->message]);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Aadhar verification failed!!']);
        }
    }

    function saveRegisterLogs($data, $aadhar_aumber)
    {
        $splitName = Self::splitName($data->name);
        $profile_photo = Self::uploadAadharImage($data->photo_link, $data->name);
        $now = new \DateTime();
        $ctime = $now->format('Y-m-d H:i:s');
        $registerlogs = Registerlog::where('aadhar_aumber', $aadhar_aumber)->first();
        if ($registerlogs) {
            Registerlog::where('aadhar_aumber', $aadhar_aumber)->update([
                'full_name' => $data->name,
                'first_name' => $splitName['first_name'],
                'last_name' => $splitName['last_name'],
                'care_of' => $data->care_of,
                'address' => $data->address,
                'dob' => $data->dob,
                'gender' => $data->gender,
                'state' => $data->split_address->state,
                'dist' => $data->split_address->dist,
                'pincode' => $data->split_address->pincode,
                'profile_photo' => $profile_photo,
                'status_id' => 3,
            ]);
        } else {
            Registerlog::insert([
                'aadhar_aumber' => $aadhar_aumber,
                'full_name' => $data->name,
                'first_name' => $splitName['first_name'],
                'last_name' => $splitName['last_name'],
                'care_of' => $data->care_of,
                'address' => $data->address,
                'dob' => $data->dob,
                'gender' => $data->gender,
                'state' => $data->split_address->state,
                'dist' => $data->split_address->dist,
                'pincode' => $data->split_address->pincode,
                'profile_photo' => $profile_photo,
                'created_at' => $ctime,
                'status_id' => 3,
            ]);
        }
    }

    function splitName(string $name): array
    {
        $nameParts = array_filter(explode(' ', trim($name))); // Remove extra spaces and filter empty values
        $first_name = $nameParts[0] ?? $name; // Default to the full name if no spaces
        $last_name = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '';
        return [
            'first_name' => $first_name,
            'last_name' => $last_name,
        ];
    }

    function uploadAadharImage($image, $name)
    {
        // Sanitize the name by replacing special characters with '-'
        $name = preg_replace('/[\'\/",;<> ]/', '-', $name);

        // Ensure the image string is properly formatted
        $image = str_replace(['data:image/png;base64,', ' '], ['', '+'], $image);

        // Generate a unique name for the image file
        $imageName = Str::random(10) . '-' . $name . '.png';

        // Define the directory path
        $directoryPath = public_path('profile-pic');

        // Ensure the directory exists
        if (!File::exists($directoryPath)) {
            File::makeDirectory($directoryPath, 0755, true);
        }

        // Save the decoded image to the directory
        $filePath = $directoryPath . '/' . $imageName;
        if (File::put($filePath, base64_decode($image)) === false) {
            throw new \Exception("Failed to save the image to the specified directory.");
        }

        return $imageName;
    }

    function registerNow(Request $request)
    {
        $rules = array(
            'aadhar_aumber' => 'required',
            'verify_id' => 'required|exists:registerlogs,id',
            'mobile' => 'required|unique:users|digits:10',
            'email' => 'required|email|unique:users',
            'pan_number' => 'required|unique:members',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $aadhar_aumber = $request->aadhar_aumber;
        $verify_id = $request->verify_id;
        $mobile = $request->mobile;
        $email = $request->email;
        $pan_number = $request->pan_number;
        $registerlogs = Registerlog::find($verify_id);
        $full_name = $registerlogs->full_name;
        $response = Self::panVerify($full_name, $pan_number, $verify_id);
        $status_id = $response['status_id'];
        if ($status_id == 1) {
            $father_name = $response['father_name'];
            DB::beginTransaction();
            try {
                $states = State::where('name', $registerlogs->state)->first();
                $state_id = (isset($states)) ? $states->id : $this->state_id;

                $districts = District::where('district_name', $registerlogs->dist)->first();
                $district_id = (isset($districts)) ? $districts->id : $this->district_id;

                $password = mt_rand();
                $api_token = Str::random(60);
                $role_id = 9;
                $parent_id = 1;
                $now = new \DateTime();
                $ctime = $now->format('Y-m-d H:i:s');
                $first_name = $registerlogs->first_name;
                $last_name = $registerlogs->last_name;
                $scheme_id = 0;
                $company_id = 1;
                $gst_type = 0;
                $user_gst_type = 0;
                $lock_amount = 0;
                $address = $registerlogs->address;
                $city = $registerlogs->dist;
                $pin_code = $registerlogs->pincode;
                $shop_name = $first_name .' '. $last_name;
                $office_address = $registerlogs->address;
                $gst_number = '';
                $active_services = '1,2,5,11,12,25';
                $library = new MemberLibrary();
                return $library->storeMember($first_name, $last_name, $email, $password, $mobile, $role_id, $parent_id, $scheme_id, $company_id, $gst_type, $user_gst_type, $lock_amount, $address, $city, $state_id, $district_id, $pin_code, $shop_name, $office_address, $pan_number, $gst_number, $active_services);

                $user_id = User::insertGetId([
                    'name' => $registerlogs->first_name,
                    'last_name' => $registerlogs->last_name,
                    'email' => $email_address,
                    'password' => bcrypt($password),
                    'mobile' => $mobile_number,
                    'role_id' => $role_id,
                    'created_at' => $ctime,
                    'parent_id' => $parent_id,
                    'status_id' => 1,
                    'scheme_id' => 0,
                    'api_token' => $api_token,
                    'active' => 1,
                    'company_id' => 1,
                    'mobile_verified' => 1,
                ]);
                $balance_id = Balance::insertGetId([
                    'user_id' => $user_id,
                    'user_balance' => 0,
                ]);
                $profile_id = Profile::insertGetId([
                    'user_id' => $user_id,
                    'recharge' => 1,
                    'money' => 1,
                    'credit_card' => 1,
                    'credit_card_two' => 1,
                    'wallet_transfer' => 1,
                    'upigateway' => 1,
                ]);
                $member_id = Member::insertGetId([
                    'user_id' => $user_id,
                    'permanent_address' => $registerlogs->address,
                    'permanent_city' => $registerlogs->dist,
                    'permanent_state' => $state_id,
                    'permanent_district' => $district_id,
                    'permanent_pin_code' => $registerlogs->pincode,
                    'shop_name' => $registerlogs->full_name,
                    'office_address' => $registerlogs->address,
                    'pan_number' => $pan_number,
                    'aadhar_number' => $registerlogs->aadhar_aumber,
                    'care_of' => $registerlogs->care_of,
                    'dob' => $registerlogs->dob,
                    'gender' => $registerlogs->gender,
                    'aadhar_profile_photo' => $registerlogs->profile_photo,
                    'father_name' => $father_name,
                ]);
                if ($member_id) {
                    $usern = User::find($user_id);
                    $usern->balance_id = $balance_id;
                    $usern->profile_id = $profile_id;
                    $usern->member_id = $member_id;
                    $usern->save();
                    DB::commit();
                    $message = "Dear $full_name, your Profile is now Created on our System, Username - $mobile_number, Password - $password, $this->brand_name";
                    $template_id = 2;
                    $library = new SmsLibrary();
                    $library->send_sms($mobile_number, $message, $template_id);
                    return response()->json(['status' => 'success', 'message' => 'Successfully']);
                }
            } catch (\Exception $ex) {
                DB::rollback();
                // throw $ex;
                return response()->json(['status' => 'failure', 'message' => 'something went wrong']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => $response['message']]);
        }
    }
}
