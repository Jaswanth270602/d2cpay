<?php

namespace App\library {

    use http\Client\Response;
    use Illuminate\Support\Facades\Auth;
    use App\Models\User;
    use App\Models\Balance;
    use App\Models\Profile;
    use App\Models\Member;
    use App\Models\Sitesetting;
    use App\Models\Agentonboarding;
    use App\Models\Frontbanner;
    use App\Models\Service;
    use App\Models\Report;
    use App\Models\Userbroadcast;
    use App\Models\Company;
    use App\Models\Servicegroup;
    use DB;
    use Str;
    use Helpers;
    use App\Library\SmsLibrary;
    use App\Library\BasicLibrary;
    use App\Services\AppEncryption;

    class MemberLibrary
    {

        public function __construct()
        {
            $this->company_id = Helpers::company_id()->id;
            $companies = Helpers::company_id();
            $this->company_id = $companies->id;
            $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
            $this->brand_name = (empty($sitesettings)) ? '' : $sitesettings->brand_name;

            $companies = Company::find($this->company_id);
            $this->cdnLink = (empty($companies)) ? '' : $companies->cdn_link;
            $this->encryptionKey = (empty($companies)) ? '' : $companies->encryptionKey;
        }

        public function my_down_member($role_id, $company_id, $user_id)
        {
            switch ($role_id) {
                case 1:
                    return User::get(['id'])->toArray();
                    break;
                case 2:
                    return User::get(['id'])->toArray();
                    break;
                case 3:
                    $user = $this->getdownuser($role_id, $company_id, $user_id);
                    $my_down_member = User::where('company_id', $company_id)->whereIn('parent_id', $user)->where('role_id', '>', $role_id)->get(['id'])->toArray();
                    $user_id = array($user_id);
                    return array_merge($user_id, $my_down_member);
                    break;

                case 4:
                    $user = $this->getdownuser($role_id, $company_id, $user_id);
                    $my_down_member = User::where('company_id', $company_id)->whereIn('parent_id', $user)->where('role_id', '>', $role_id)->get(['id'])->toArray();
                    $user_id = array($user_id);
                    return array_merge($user_id, $my_down_member);
                    break;
                case 5:
                    $user = $this->getdownuser($role_id, $company_id, $user_id);
                    $my_down_member = User::where('company_id', $company_id)->whereIn('parent_id', $user)->where('role_id', '>', $role_id)->get(['id'])->toArray();
                    $user_id = array($user_id);
                    return array_merge($user_id, $my_down_member);
                    break;
                case 6:
                    $user = $this->getdownuser($role_id, $company_id, $user_id);
                    $my_down_member = User::where('company_id', $company_id)->whereIn('parent_id', $user)->where('role_id', '>', $role_id)->get(['id'])->toArray();
                    $user_id = array($user_id);
                    return array_merge($user_id, $my_down_member);
                    break;
                case 7:
                    $user = $this->getdownuser($role_id, $company_id, $user_id);
                    $my_down_member = User::where('company_id', $company_id)->whereIn('parent_id', $user)->where('role_id', '>', $role_id)->get(['id'])->toArray();
                    $user_id = array($user_id);
                    return array_merge($user_id, $my_down_member);
                    break;
                case 8:
                    $user = $this->getdownuser($role_id, $company_id, $user_id);
                    $my_down_member = User::where('company_id', $company_id)->whereIn('parent_id', $user)->where('role_id', '>', $role_id)->get(['id'])->toArray();
                    $user_id = array($user_id);
                    return array_merge($user_id, $my_down_member);
                    break;
                case 9:
                    $user = $this->getdownuser($role_id, $company_id, $user_id);
                    $my_down_member = User::where('company_id', $company_id)->whereIn('parent_id', $user)->where('role_id', '>', $role_id)->get(['id'])->toArray();
                    $user_id = array($user_id);
                    return array_merge($user_id, $my_down_member);
                    break;
                case 10:
                    $user = $this->getdownuser($role_id, $company_id, $user_id);
                    $my_down_member = User::where('company_id', $company_id)->whereIn('parent_id', $user)->where('role_id', '>', $role_id)->get(['id'])->toArray();
                    $user_id = array($user_id);
                    return array_merge($user_id, $my_down_member);
                    break;
                default:
                    echo "Your favorite color is neither red, blue, nor green!";
            }

        }

        public function getdownuser($role_id, $company_id, $user_id)
        {
            $user_id = array($user_id);
            for ($i = $role_id; $i <= 7; $i++) {
                $user_detail = $user_id;
                $user_detail = User::where('company_id', $company_id)->where('role_id', '>', $i)->whereIn('parent_id', $user_detail)->get(['id'])->toArray();
                foreach ($user_detail as $inner) {
                    $user_id[] = $inner;
                }
            }
            return $user_id;
        }

        function storeMember($name, $last_name, $email, $password, $mobile, $role_id, $parent_id, $scheme_id, $company_id, $gst_type, $user_gst_type, $lock_amount, $address, $city, $state_id, $district_id, $pin_code, $shop_name, $office_address, $pan_number, $gst_number, $active_services)
        {
            DB::beginTransaction();
            try {
                $api_token = Str::random(60);
                $now = new \DateTime();
                $ctime = $now->format('Y-m-d H:i:s');
                $user_id = User::insertGetId([
                    'name' => $name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'password' => bcrypt($password),
                    'mobile' => $mobile,
                    'role_id' => $role_id,
                    'created_at' => $ctime,
                    'parent_id' => $parent_id,
                    'status_id' => 1,
                    'scheme_id' => $scheme_id,
                    'api_token' => $api_token,
                    'active' => 1,
                    'company_id' => $company_id,
                    'mobile_verified' => 1,
                    'gst_type' => 0,
                    'user_gst_type' => 0,
                    'lock_amount' => $lock_amount,
                    'password_changed_at' => date('Y-m-d H:i:s', strtotime('-31 days')),
                ]);
                $balance_id = Balance::insertGetId([
                    'user_id' => $user_id,
                    'user_balance' => 0,
                    'sms_balance' => 0,
                    'aeps_balance' => 0,
                    'lien_amount' => 0,
                    'balance_alert' => 0,
                ]);
                $profile_id = Profile::insertGetId([
                    'user_id' => $user_id,
                    'active_services' => $active_services,
                ]);

                $member_id = Member::insertGetId([
                    'user_id' => $user_id,
                    'address' => $address,
                    'city' => $city,
                    'state_id' => $state_id,
                    'district_id' => $district_id,
                    'pin_code' => $pin_code,
                    'shop_name' => $shop_name,
                    'office_address' => $office_address,
                    'pan_number' => $pan_number,
                    'gst_number' => $gst_number,
                ]);
                if ($member_id) {
                    $usern = User::find($user_id);
                    $usern->balance_id = $balance_id;
                    $usern->profile_id = $profile_id;
                    $usern->member_id = $member_id;
                    $usern->save();
                    DB::commit();
                    $message = "Dear $name, your Profile is now Created on our System, Username - $mobile, Password - $password, $this->brand_name";
                    $template_id = 2;
                    $library = new SmsLibrary();
                    $library->send_sms($mobile, $message, $template_id);
                    return response()->json(['status' => 'success', 'message' => 'Successfully']);
                }
            } catch (\Exception $ex) {
                DB::rollback();
                // throw $ex;
                return response()->json(['status' => 'failure', 'message' => $ex->getMessage()]);
            }
        }


        function appUserDetails($user_id, $type)
        {
            $notification = $this->notification_list();
            $agentonboardings = Agentonboarding::where('user_id', Auth::id())->first();
            $agentonboarding = (empty($agentonboardings)) ? 0 : 1;
            $users = User::find($user_id);

            $userdetails = array(
                'first_name' => Auth::User()->name,
                'last_name' => Auth::User()->last_name,
                'email' => Auth::User()->email,
                'mobile' => Auth::User()->mobile,
                'role_id' => Auth::User()->role_id,
                'scheme_id' => Auth::User()->scheme_id,
                'joining_date' => Auth::User()->created_at->format('Y-m-d h:m:s'),
                'address' => Auth::User()->member->address,
                'city' => Auth::User()->member->city,
                'state_id' => Auth::User()->member->state_id,
                'district_id' => Auth::User()->member->district_id,
                'pin_code' => Auth::User()->member->pin_code,
                'shop_name' => Auth::User()->member->shop_name,
                'office_address' => Auth::User()->member->office_address,
                'call_back_url' => Auth::User()->member->call_back_url,
                'profile_photo' => Auth::User()->member->profile_photo == '' ? '' : $this->cdnLink . '' . Auth::User()->member->profile_photo,
                'shop_photo' => Auth::User()->member->shop_photo == '' ? '' : $this->cdnLink . '' . Auth::User()->member->shop_photo,
                'gst_regisration_photo' => Auth::User()->member->gst_regisration_photo == '' ? '' : $this->cdnLink . '' . Auth::User()->member->gst_regisration_photo,
                'pancard_photo' => Auth::User()->member->pancard_photo == '' ? '' : $this->cdnLink . '' . Auth::User()->member->pancard_photo,
                'cancel_cheque' => Auth::User()->member->cancel_cheque == '' ? '' : $this->cdnLink . '' . Auth::User()->member->cancel_cheque,
                'address_proof' => Auth::User()->member->address_proof == '' ? '' : $this->cdnLink . '' . Auth::User()->member->address_proof,
                'kyc_status' => Auth::User()->member->kyc_status,
                'kyc_remark' => Auth::User()->member->kyc_remark,
                'mobile_verified' => Auth::User()->mobile_verified,
                'lock_amount' => Auth::User()->lock_amount,
                'session_id' => $users->session_id,
                'active' => Auth::User()->active,
                'reason' => Auth::User()->reason,
                'user_balance' => number_format(Auth::User()->balance->user_balance, 2),
                'aeps_balance' => number_format(Auth::User()->balance->aeps_balance, 2),
                'lien_amount' => number_format(Auth::User()->balance->lien_amount, 2),
                'account_number' => Auth::User()->company->icici_code . '' . Auth::User()->mobile,
                'ifsc_code' => Auth::User()->company->ifsc_code,
                'pan_username' => Auth::User()->company->pan_username,
                'ekyc' => Auth::User()->ekyc,
                'pan_number' => Auth::User()->member->pan_number,
                'agentonboarding' => $agentonboarding,
                'api_token' => $users->api_token,
            );
            $companydetails = array(
                'company_name' => Auth::User()->company->company_name,
                'company_email' => Auth::User()->company->company_email,
                'company_address' => Auth::User()->company->company_address,
                'company_address_two' => Auth::User()->company->company_address_two,
                'support_number' => Auth::User()->company->support_number,
                'whatsapp_number' => Auth::User()->company->whatsapp_number,
                'company_logo' => $this->cdnLink . '' . Auth::User()->company->company_logo,
                'company_website' => Auth::User()->company->company_website,
                'news' => Auth::User()->company->news,
                'sender_id' => Auth::User()->company->sender_id,
                'view_plan' => Auth::User()->company->view_plan,
                'color_start' => Auth::User()->company->color_start,
                'color_end' => Auth::User()->company->color_end,
                'transaction_pin' => Auth::User()->company->transaction_pin,
            );
            $banner = $this->get_banners(Auth::User()->company_id);
            $recharge_badge = Self::getServicegroups();
            $sales = $this->get_today_sales();
            $broadcast = Self::getBroadcast();
            $data = [
                'userdetails' => $userdetails,
                'companydetails' => $companydetails,
                'banner' => $banner,
                'notification' => $notification,
                'recharge_badge' => $recharge_badge,
                'broadcast' => $broadcast,
                'sales' => $sales,
            ];
            $encryptData = AppEncryption::encryptText(json_encode($data), $this->encryptionKey);
            return Response()->json(['status' => 'success', 'message' => 'Successfull..', 'data' => $encryptData['data']]);
        }

        function getBroadcast()
        {
            $userbroadcasts = Userbroadcast::where('company_id', Auth::User()->company_id)->first();
            $broadcasts = array(
                'heading' => (empty($userbroadcasts) ? '' : $userbroadcasts->heading),
                'image_url' => (empty($userbroadcasts) ? '' : $this->cdnLink . $userbroadcasts->image_url),
                'img_status' => (empty($userbroadcasts) ? 2 : $userbroadcasts->img_status),
                'message' => (empty($userbroadcasts) ? '' : $userbroadcasts->message),
                'status_id' => (empty($userbroadcasts) ? 2 : $userbroadcasts->status_id),
            );
            return $broadcasts;
        }

        function notification_list()
        {
            $response = array();
            foreach (Auth::User()->unreadNotifications as $value) {
                $product = array();
                $product["notification_id"] = $value->id;
                $product["notification_title"] = $value->data['letter']['title'];
                $product["notification_data"] = $value->data['letter']['body'];
                array_push($response, $product);
            }
            return $response;
        }

        function get_banners($company_id)
        {
            $banners = Frontbanner::where('company_id', $company_id)->where('type', 'APP')->select('id', 'banners')->get();
            $response = array();
            foreach ($banners as $value) {
                $product = array();
                $product["id"] = $value->id;
                $product["image"] = $this->cdnLink . '' . $value->banners;
                array_push($response, $product);
            }
            return $response;
        }

        function getServicegroups()
        {
            $servicegroups = Servicegroup::where('status_id', 1)
                ->whereNotIn('id', [3, 8])
                ->select('id', 'group_name')
                ->get();
            $recharge = array();
            foreach ($servicegroups as $value) {
                $services = Self::getServices($value->id);
                if (!empty($services)) {
                    $product = array();
                    $product["id"] = $value->id;
                    $product["title"] = $value->group_name;
                    $product["data"] = $services;
                    array_push($recharge, $product);
                }
            }
            return $recharge;
        }

        function getServices($servicegroup_id)
        {
            $library = new \App\Library\BasicLibrary;
            $companyActiveService = $library->getCompanyActiveService(Auth::id());
            $userActiveService = $library->getUserActiveService(Auth::id());
            $services = Service::where('servicegroup_id', $servicegroup_id)
                ->whereIn('id', $companyActiveService)
                ->whereIn('id', $userActiveService)
                ->select('id', 'service_name', 'service_image', 'bbps', 'report_slug', 'report_is_static')
                ->get();
            $recharge = array();
            foreach ($services as $value) {
                $product = array();
                $product["service_id"] = $value->id;
                $product["service_name"] = $value->service_name;
                $product["service_image"] = $this->cdnLink . $value->service_image;
                $product["bbps"] = $value->bbps;
                $product["report_title"] = $value->service_name . ' History';
                $product["report_url"] = url('api/reports/v1') . '/welcome/' . $value->report_slug;
                $product["report_is_static"] = $value->report_is_static;
                array_push($recharge, $product);
            }
            return $recharge;
        }

        function get_today_sales()
        {
            $role_id = Auth::User()->role_id;
            $company_id = Auth::User()->company_id;
            $user_id = Auth::id();
            $library = new MemberLibrary();
            $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);
            $today_sale = Report::whereIn('user_id', $my_down_member)->whereIn('status_id', [1, 3, 8])->whereDate('created_at', '=', date('Y-m-d'))->sum('amount');
            if (Auth::User()->role_id == 8 || Auth::User()->role_id == 9 || Auth::User()->role_id == 10) {
                $today_profit = Report::where('user_id', Auth::id())->whereIn('status_id', [1])->whereDate('created_at', '=', date('Y-m-d'))->sum('profit');
            } else {
                $today_profit = Report::where('user_id', Auth::id())->whereIn('status_id', [6])->whereDate('created_at', '=', date('Y-m-d'))->sum('profit');
            }
            return array(
                'today_sale' => number_format($today_sale, 2),
                'today_profit' => number_format($today_profit, 2),
            );
        }
        // the end
    }
}
