<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\Loginlog;
use http\Env\Response;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\RateLimiter;
use Nette\Schema\ValidationException;
use Psy\Util\Str;
use Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use App\Models\Company;
use Carbon\Carbon;
use Auth;
use DB;
use App\Models\User;
use App\Models\Userloginattempt;
use Hash;
use Helpers;
use App\Models\Sitesetting;
use App\Helpers\UserSystemInfoHelper;
use App\Library\BrowserloginLibray;
use App\Library\SmsLibrary;
use App\Library\LocationRestrictionsLibrary;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->company_id = Helpers::company_id()->id;
        $companies = Helpers::company_id();
        $this->company_id = $companies->id;
        $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
        $this->brand_name = (empty($sitesettings)) ? '' : $sitesettings->brand_name;
    }

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;
    protected $maxAttempts = 1; // Default is 5
    protected $decayMinutes = 1; // Default is 1


    function login_now(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|exists:users,mobile|digits:10',
            'password' => 'required',
            'company_id' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $username = $request->username;
        $password = $request->password;
        $company_id = $request->company_id;
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $request_ip = request()->ip();
        $companies = Company::find($company_id);
        if ($companies) {
            $userDetails = User::where('mobile', $username)->first();
            if ($userDetails) {
                $loginTime = time() - 60 * 5;
                $totalLoginAttempts = Userloginattempt::where('login_time', '>', $loginTime)->where('ip_address', $request_ip)->where('attempt_type', 'login')->count();
                if ($totalLoginAttempts >= 3) {
                    return back()->withErrors(['Too many failed login attempts. please login after 5 minutes!']);
                }
                if (Hash::check(trim($password), $userDetails->password)) {
                    $user_id = $userDetails->id;
                    $locationrestrictionsLibrary = new LocationRestrictionsLibrary();
                    $isLoginValid = $locationrestrictionsLibrary->loginRestrictions($user_id, $latitude, $longitude);
                    if ($isLoginValid == 0) {
                        return back()->withErrors(["You must be within $companies->login_restrictions_km kilometer to login."]);
                    }

                    if ($companies->login_type == 1) {
                        $all = $request->all();
                        $library = new BrowserloginLibray();
                        $checkingcookie = $library->checkingcookie($userDetails, $all);
                        if ($checkingcookie == 0) {
                            $data = array(
                                'username' => base64_encode($username),
                                'password' => base64_encode($password),
                                'company_id' => base64_encode($company_id),
                                'latitude' => base64_encode($latitude),
                                'longitude' => base64_encode($longitude),
                            );
                            return view('auth.loginWithOtp')->with($data);
                        }
                    }
                    return Self::loginProcess($username, $password, $company_id, $latitude, $longitude);
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
                    return back()->withErrors(["Please enter valid login details. $remainingAttempts attempts remaining!"]);
                }
            } else {
                return back()->withErrors(['Invalid username or password']);
            }
        } else {
            return back()->withErrors(['Company not found']);
        }
    }

    function login_with_otp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
            'company_id' => 'required',
            'otp' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $username = base64_decode($request->username);
        $password = base64_decode($request->password);
        $company_id = base64_decode($request->company_id);
        $latitude = base64_decode($request->latitude);
        $longitude = base64_decode($request->longitude);
        $otp = $request->otp;
        $request_ip = request()->ip();
        $userDetails = User::where('mobile', $username)->first();
        $loginTime = time() - 60 * 5;
        $totalLoginAttempts = Userloginattempt::where('login_time', '>', $loginTime)->where('ip_address', $request_ip)->where('attempt_type', 'login')->count();
        if ($totalLoginAttempts >= 3) {
            return back()->withErrors(['Too many failed login attempts. please login after 5 minutes!']);
        }
        if (Hash::check(trim($password), $userDetails->password)) {
            $user_id = $userDetails->id;
            $loginRestrictionsKm = $userDetails->company->login_restrictions_km;
            $locationrestrictionsLibrary = new LocationRestrictionsLibrary();
            $isLoginValid = $locationrestrictionsLibrary->loginRestrictions($user_id, $latitude, $longitude);
            if ($isLoginValid == 0) {
                return back()->withErrors(["You must be within $loginRestrictionsKm kilometer to login."]);
            }
            if (Hash::check(trim($otp), $userDetails->login_otp)) {
                setcookie('username', $username, strtotime('+30 days'));
                setcookie('password', $password, time() + 5);
                setcookie('company_id', $userDetails->comapny_id, time() + 5);
                setcookie('status_id', 1, time() + 5);
                setcookie('first_login', 1, time() + 5);
                return Self::loginProcess($username, $password, $company_id, $latitude, $longitude);
            }
        }
        $now = new \DateTime();
        $ctime = $now->format('Y-m-d H:i:s');
        Userloginattempt::insert([
            'ip_address' => $request_ip,
            'login_time' => time(),
            'created_at' => $ctime,
        ]);
        $data = array(
            'username' => base64_encode($username),
            'password' => base64_encode($password),
            'company_id' => base64_encode($company_id),
            'latitude' => base64_encode($latitude),
            'longitude' => base64_encode($longitude),
        );
        return view('auth.loginWithOtp')->with($data);
    }


    function loginProcess($username, $password, $company_id, $latitude, $longitude)
    {
        if (Auth::attempt(['mobile' => $username, 'password' => $password, 'company_id' => $company_id, 'status_id' => 1])) {
            $request_ip = request()->ip();
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            $loginlogs = Loginlog::where('user_id', Auth::id())->where('ip_address', $request_ip)->first();
            if (empty($loginlogs)) {
                $template_id = 22;
                $message = "You Log in to $this->brand_name at $ctime with IP: $request_ip $this->brand_name";
                $library = new SmsLibrary();
                $library->send_sms($username, $message, $template_id);
            }
            Userloginattempt::where('ip_address', $request_ip)->whereIn('attempt_type', ['login', 'resendLoginOTP'])->delete();
            Self::saveLoginLogs(Auth::id(), $latitude, $longitude);
            return redirect()->intended('');
        } else {
            return back()->withErrors(['Invalid username or password']);
        }
    }


    function saveLoginLogs($user_id, $latitude, $longitude)
    {
        $new_session_id = \Session::getId(); //get new session_id after user sign in
        $userDetails = User::find($user_id);
        if ($userDetails->session_id != '') {
            $last_session = \Session::getHandler()->read($userDetails->session_id);
            if ($last_session) {
                if (\Session::getHandler()->destroy($userDetails->session_id)) {

                }
            }
        }
        User::where('id', $user_id)->update(['session_id' => $new_session_id]);
        $getip = UserSystemInfoHelper::get_ip();
        $getbrowser = UserSystemInfoHelper::get_browsers();
        $getdevice = UserSystemInfoHelper::get_device();
        $getos = UserSystemInfoHelper::get_os();
        $now = new \DateTime();
        $ctime = $now->format('Y-m-d H:i:s');
        $locationData = \Location::get($getip);
        $countryName = is_object($locationData) ? ($locationData->countryName ?? '') : '';
        $countryCode = is_object($locationData) ? ($locationData->countryCode ?? '') : '';
        $regionCode = is_object($locationData) ? ($locationData->regionCode ?? '') : '';
        $regionName = is_object($locationData) ? ($locationData->regionName ?? '') : '';
        $cityName = is_object($locationData) ? ($locationData->cityName ?? '') : '';
        $zipCode = is_object($locationData) ? ($locationData->zipCode ?? '') : '';
        Loginlog::insertGetId([
            'user_id' => Auth::id(),
            'ip_address' => $getip,
            'get_browsers' => $getbrowser,
            'get_device' => $getdevice,
            'get_os' => $getos,
            'country_name' => $countryName,
            'country_code' => $countryCode,
            'region_code' => $regionCode,
            'region_name' => $regionName,
            'city_name' => $cityName,
            'zip_code' => $zipCode,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'mode' => 'WEB',
            'created_at' => $ctime,
            'status_id' => 1,
        ]);
    }

    function resend_login_otp(Request $request)
    {
        $rules = array(
            'username' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $username = base64_decode($request->username);
        return Self::resendLoginOTP($username);
    }

    function resend_login_otp_app(Request $request)
    {
        $rules = array(
            'username' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $username = $request->username;
        return Self::resendLoginOTP($username);
    }

    function resendLoginOTP($username)
    {
        $request_ip = request()->ip();
        $userDetails = User::where('mobile', $username)->first();
        if ($userDetails) {
            $loginTime = time() - 60 * 2;
            $totalLoginAttempts = Userloginattempt::where('login_time', '>', $loginTime)->where('ip_address', $request_ip)->where('attempt_type', 'resendLoginOTP')->count();
            if ($totalLoginAttempts >= 3) {
                return Response()->json(['status' => 'failure', 'message' => 'Too many failed resend otp attempts. please resend after 2 minutes!']);
            }
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            Userloginattempt::insert([
                'ip_address' => $request_ip,
                'login_time' => time(),
                'attempt_type' => 'resendLoginOTP',
                'created_at' => $ctime,
            ]);

            $otp = mt_rand(100000, 999999);
            $message = "Dear $userDetails->name, your login verification code is $otp $this->brand_name";
            $template_id = 1;
            $library = new SmsLibrary();
            $library->send_sms($username, $message, $template_id);
            User::where('id', $userDetails->id)->update(['login_otp' => bcrypt($otp)]);
            return Response()->json(['status' => 'success', 'message' => 'OTP successfully sent to your mobile number']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Invalid mobile number']);
        }
    }

    function forgot_password()
    {
        return view('auth.forgot_password');
    }

    function forgot_password_otp(Request $request)
    {
        $rules = array(
            'mobile' => 'required|exists:users,mobile|digits:10',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $request_ip = request()->ip();
        $mobile = $request->mobile;
        $userDetails = User::where('mobile', $mobile)->first();
        if ($userDetails) {
            $loginTime = time() - 60 * 5;
            $totalLoginAttempts = Userloginattempt::where('login_time', '>', $loginTime)->where('ip_address', $request_ip)->where('attempt_type', 'forgot-password-otp')->count();
            if ($totalLoginAttempts >= 3) {
                return Response()->json(['status' => 'failure', 'message' => 'Too many failed forgot password attempts. please try after 5 minutes!']);
            }
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            Userloginattempt::insert([
                'ip_address' => $request_ip,
                'login_time' => time(),
                'attempt_type' => 'forgot-password-otp',
                'created_at' => $ctime,
            ]);
            $otp = mt_rand(100000, 999999);
            User::where('mobile', $mobile)->update(['login_otp' => bcrypt($otp)]);
            $message = "Dear $userDetails->name, your forgot password verification code is $otp $this->brand_name";
            $template_id = 19;
            $library = new SmsLibrary();
            $library->send_sms($mobile, $message, $template_id);
            $mobile = substr($mobile, 0, 1) . '*******' . substr($mobile, -2);
            $alertMessage = "Enter the 6-digit OTP sent on +91 $mobile";
            return Response()->json(['status' => 'success', 'message' => $alertMessage]);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Invalid mobile number']);
        }
    }

    function confirm_forgot_password(Request $request)
    {
        $rules = array(
            'mobile' => 'required|exists:users,mobile|digits:10',
            'otp' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile = $request->mobile;
        $otp = $request->otp;
        $request_ip = request()->ip();
        $userDetails = User::where('mobile', $mobile)->first();
        if ($userDetails) {
            if (Hash::check(trim($otp), $userDetails->login_otp)) {
                Userloginattempt::where('ip_address', $request_ip)->whereIn('attempt_type', ['forgot-password-otp'])->delete();
                $password = bin2hex(random_bytes(8));
                $user_id = $userDetails->id;
                $mm = User::find($user_id);
                $mm->password = Hash::make($password);
                $mm->save();
                $message = "Dear $userDetails->name your password has been changed, now your new password is $password $this->brand_name";
                $template_id = 8;
                $library = new SmsLibrary();
                $library->send_sms($userDetails->mobile, $message, $template_id);
                return Response()->json(['status' => 'success', 'message' => "Dear $userDetails->name  your new password successfully sent to your regsiter mobile number"]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Invalid OTP']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Invalid OTP']);
        }
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

}
