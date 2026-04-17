<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use App\Models\User;
use App\Models\Role;
use App\Models\Scheme;
use App\Models\State;
use App\Models\Balance;
use App\Models\Profile;
use App\Models\Member;
use App\Models\Company;
use App\Models\Report;
use App\Models\Servicegroup;
use Hash;
use App\Models\District;
use \Crypt;
use Str;
use Carbon;
use File;
use Helpers;
use DB;
use App\Models\Sitesetting;
use App\Models\Service;
use App\Models\Api;
use Illuminate\Support\Facades\Cache;
use App\Library\MemberLibrary;
use App\Library\SmsLibrary;
use App\Library\PermissionLibrary;

class MemberController extends Controller
{


    public function __construct()
    {
        $this->company_id = Helpers::company_id()->id;
        $companies = Helpers::company_id();
        $this->company_id = $companies->id;
        $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
        $this->brand_name = (empty($sitesettings)) ? '' : $sitesettings->brand_name;
        $this->backend_template_id = (empty($sitesettings)) ? 1 : $sitesettings->backend_template_id;

        $apis = Api::where('vender_id', 10)->first();
        $this->key = (empty($apis)) ? '' : 'Bearer ' . $apis->api_key;
    }

    function member_list($role_slug)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['member_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        $roles = Role::where('role_slug', $role_slug)->first();
        if ($roles) {
            if ($roles->id > Auth::User()->role_id) {
                $role_title = $roles->role_title;
                $data = array(
                    'page_title' => $role_title,
                    'role_slug' => $role_slug,
                    'url' => url('admin/member-list-api') . '?' . 'role_slug=' . $role_slug . '&parent_id=0',
                );
                $states = State::where('status_id', 1)->get();
                if ($this->backend_template_id == 1) {
                    return view('admin.member_list', compact('states'))->with($data);
                } elseif ($this->backend_template_id == 2) {
                    return view('themes2.admin.member_list', compact('states'))->with($data);
                } elseif ($this->backend_template_id == 3) {
                    return view('themes3.admin.member_list', compact('states'))->with($data);
                } elseif ($this->backend_template_id == 4) {
                    return view('themes4.admin.member_list', compact('states'))->with($data);
                } else {
                    return redirect()->back();
                }
            } else {
                return redirect()->back();
            }

        } else {
            return redirect()->back();
        }
    }

    function parent_down_users($role_slug, $parent_id)
    {
        $parent_id = Crypt::decrypt($parent_id);
        $roles = Role::where('role_slug', $role_slug)->first();
        if ($roles) {
            if ($roles->id > Auth::User()->role_id) {
                $role_title = $roles->role_title;
                $data = array(
                    'page_title' => $role_title,
                    'role_slug' => $role_slug,
                    'url' => url('admin/member-list-api') . '?' . 'role_slug=' . $role_slug . '&parent_id=' . $parent_id . '',
                );
                $states = State::where('status_id', 1)->get();
                if ($this->backend_template_id == 1) {
                    return view('admin.member_list', compact('states'))->with($data);
                } elseif ($this->backend_template_id == 2) {
                    return view('themes2.admin.member_list', compact('states'))->with($data);
                } elseif ($this->backend_template_id == 3) {
                    return view('themes3.admin.member_list', compact('states'))->with($data);
                } elseif ($this->backend_template_id == 4) {
                    return view('themes4.admin.member_list', compact('states'))->with($data);
                } else {
                    return redirect()->back();
                }
            } else {
                return redirect()->back();
            }
        } else {
            return redirect()->back();
        }
    }

    function suspended_users()
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['suspended_user_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 7) {
            $data = array(
                'page_title' => 'Suspended User',
                'url' => url('admin/suspended-user-api'),
            );
            $states = State::where('status_id', 1)->get();
            if ($this->backend_template_id == 1) {
                return view('admin.suspended_users', compact('states'))->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.suspended_users', compact('states'))->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.suspended_users', compact('states'))->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.suspended_users', compact('states'))->with($data);
            } else {
                return redirect()->back();
            }
        } else {
            return Redirect::back();
        }

    }


    function create_user($role_slug)
    {
        $roles = Role::where('role_slug', $role_slug)->first();
        if ($roles) {
            if ($roles->id > Auth::User()->role_id) {
                $roledetails = Role::where('id', $roles->id)->get();
                $schemes = Scheme::where('user_id', Auth::id())->get();
                $state = State::where('status_id', 1)->get();
                $district = District::where('status_id', 1)->get();
                $companies = Company::where('status_id', 1)->where('parent_id', Auth::id())->get();
                $companies_id = array();
                foreach ($companies as $value) {
                    $companies_id[] = $value->id;
                }
                $my_company_id = array(Auth::User()->company_id);
                $company_id = array_merge($companies_id, $my_company_id);
                $company = Company::whereIn('id', $company_id)->where('status_id', 1)->get();
                $servicegroup_id = Servicegroup::where('status_id', 1)->get(['id']);
                $services = Service::whereIn('status_id', [1])->whereIn('servicegroup_id', $servicegroup_id)->get();
                $data = array('page_title' => $roles->role_title);
                if ($this->backend_template_id == 1) {
                    return view('admin.create_user', compact('roledetails', 'schemes', 'state', 'district', 'company', 'services'))->with($data);
                } elseif ($this->backend_template_id == 2) {
                    return view('themes2.admin.create_user', compact('roledetails', 'schemes', 'state', 'district', 'company'))->with($data);
                } elseif ($this->backend_template_id == 3) {
                    return view('themes3.admin.create_user', compact('roledetails', 'schemes', 'state', 'district', 'company'))->with($data);
                } elseif ($this->backend_template_id == 4) {
                    return view('themes4.admin.create_user', compact('roledetails', 'schemes', 'state', 'district', 'company'))->with($data);
                } else {
                    return redirect()->back();
                }
            } else {
                return Redirect::back();
            }

        } else {
            return Redirect::back();
        }
    }

    function store_members(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['create_member_permission'];
            if (!$myPermission == 1) {
                return response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        $rules = array(
            'name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users',
            'mobile' => 'required|unique:users|digits:10',
            'role_id' => 'required',
            'shop_name' => 'required',
            'office_address' => 'required',
            'address' => 'required',
            'city' => 'required',
            'state_id' => 'required|exists:states,id',
            'district_id' => 'required|exists:districts,id',
            'pin_code' => 'required|digits:6|integer',
            'pan_number' => 'required|regex:/^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/|unique:members',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $name = $request->name;
        $last_name = $request->last_name;
        $email = $request->email;
        $mobile = $request->mobile;
        $shop_name = $request->shop_name;
        $office_address = $request->office_address;
        $address = $request->address;
        $city = $request->city;
        $pin_code = $request->pin_code;
        $state_id = $request->state_id;
        $district_id = $request->district_id;
        $lock_amount = (empty($request->lock_amount) ? 0 : $request->lock_amount);
        $gst_type = (empty($request->gst_type) ? 0 : $request->gst_type);
        $user_gst_type = (empty($request->user_gst_type) ? 0 : $request->user_gst_type);
        $pan_number = $request->pan_number;
        $gst_number = $request->gst_number;
        $active_services = $request->active_services;
        if ($request->role_id > Auth::user()->role_id) {
            $role_id = $request->role_id;
        } else {
            $role_id = Auth::user()->role_id + 1;
        }
        $password = mt_rand();

        $company_id = Auth::User()->company_id;
        if (Auth::User()->role_id == 1) {
            $scheme_id = $request->scheme_id;
        } else {
            $scheme_id = Auth::User()->scheme_id;
        }
        $parent_id = Auth::id();
        $library = new MemberLibrary();
        return $library->storeMember($name, $last_name, $email, $password, $mobile, $role_id, $parent_id, $scheme_id, $company_id, $gst_type, $user_gst_type, $lock_amount, $address, $city, $state_id, $district_id, $pin_code, $shop_name, $office_address, $pan_number, $gst_number, $active_services);
    }


    function update_members(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['update_member_permission'];
            if (!$myPermission == 1) {
                return response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        $user_id = Crypt::decrypt($request->user_id);
        //$user_id = $request->user_id;
        $rules = array(
            'name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users,email,' . $user_id, 'email',
            'mobile' => 'required|digits:10|unique:users,mobile,' . $user_id, 'mobile',
            'role_id' => 'required',
            'shop_name' => 'required',
            'office_address' => 'required',
            'address' => 'required',
            'city' => 'required',
            'state_id' => 'required|exists:states,id',
            'district_id' => 'required|exists:districts,id',
            'pin_code' => 'required|digits:6|integer',
           // 'pan_number' => 'required|regex:/^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }

        DB::beginTransaction();
        try {
            $shop_name = $request->shop_name;
            $office_address = $request->office_address;
            $lock_amount = $request->lock_amount;
            $status_id = $request->status_id;

            $address = $request->address;
            $city = $request->city;
            $state_id = $request->state_id;
            $district_id = $request->district_id;
            $pin_code = $request->pin_code;
            $pan_number = $request->pan_number;
            $gst_number = $request->gst_number;
            $userdetails = User::where('id', $user_id)->first();
            if ($request->role_id > Auth::user()->role_id) {
                $role_id = $request->role_id;
            } else {
                $role_id = $userdetails->role->id;
            }
            $scheme_id = (Auth::User()->role_id == 1) ? $request->scheme_id : $userdetails->scheme_id;
            $mobile = (Auth::User()->role_id == 1) ? $request->mobile : $userdetails->mobile;
            $parent_id = (Auth::User()->role_id == 1) ? $request->parent_id : $userdetails->parent_id;
            $name = (Auth::User()->role_id == 1) ? $request->name : $userdetails->name;
            $last_name = (Auth::User()->role_id == 1) ? $request->last_name : $userdetails->last_name;
            $email = (Auth::User()->role_id == 1) ? $request->email : $userdetails->email;
            $gst_type = (Auth::User()->role_id == 1) ? $request->gst_type : $userdetails->gst_type;
            $user_gst_type = (Auth::User()->role_id == 1) ? $request->user_gst_type : $userdetails->user_gst_type;
            $day_book = (Auth::User()->role_id == 1) ? $request->day_book : $userdetails->profile->day_book;
            $monthly_statement = (Auth::User()->role_id == 1) ? $request->monthly_statement : $userdetails->profile->monthly_statement;
            $active_services = (Auth::User()->role_id == 1) ? $request->active_services : $userdetails->profile->active_services;
            User::where('id', $user_id)->update([
                'name' => $name,
                'last_name' => $last_name,
                'email' => $email,
                'mobile' => $mobile,
                'role_id' => $role_id,
                'scheme_id' => $scheme_id,
                'lock_amount' => $lock_amount,
                'parent_id' => $parent_id,
                'gst_type' => 0,
                'user_gst_type' => 0,
                'status_id' => (empty($status_id) ? 0 : $status_id),
                'login_restrictions' => (Auth::User()->role_id == 1 && Auth::User()->company->login_restrictions == 1) ? $request->login_restrictions : $userdetails->login_restrictions,
                'latitude' => (Auth::User()->role_id == 1 && Auth::User()->company->login_restrictions == 1) ? $request->latitude : $userdetails->latitude,
                'longitude' => (Auth::User()->role_id == 1 && Auth::User()->company->login_restrictions == 1) ? $request->longitude : $userdetails->longitude,
            ]);

            Member::where('user_id', $user_id)->update([
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

            Profile::where('user_id', $user_id)->update([
                'day_book' => $day_book,
                'monthly_statement' => $monthly_statement,
                'active_services' => $active_services,
            ]);
            DB::commit();
            return Response()->json(['status' => 'success', 'message' => 'user successfully updated']);
        } catch (\Exception $ex) {
            DB::rollback();
            // throw $ex;
            return response()->json(['status' => 'failure', 'message' => $ex->getMessage()]);
        }

    }


    function view_members_details(Request $request)
    {
        $id = $request->id;
        $users = User::where('id', $id)->first();
        if ($users) {
            if (Cache::has('is_online' . $users->id)) {
                $is_online = 'online';
            } else {
                $is_online = Carbon\Carbon::parse($users->last_seen)->diffForHumans();
            }

            if (Auth::User()->role_id == 1) {
                $login_otp = $users->login_otp;
                $pan_username = $users->pan_username;
                $pan_password = $users->pan_password;
            } else {
                $login_otp = 000000;
                $pan_username = "";
                $pan_password = "";
            }
            $states = State::find($users->member->state_id);
            $districts = District::find($users->member->district_id);
            $details = array(
                'user_id' => $users->id,
                'update_anchor_url' => url('admin/view-update-users') . '/' . Crypt::encrypt($users->id),
                'kyc_anchor_url' => url('admin/view-user-kyc') . '/' . Crypt::encrypt($users->id),
                'reset_password_anchor' => 'reset_password(' . $users->id . ')',
                'name' => $users->name,
                'last_name' => $users->last_name,
                'mobile' => $users->mobile,
                'email' => $users->email,
                'lock_amount' => $users->lock_amount,
                'shop_name' => $users->member->shop_name,
                'address' => $users->member->address,
                'city' => $users->member->city,
                'state_name' => (empty($states) ? '' : $states->name),
                'district_name' => (empty($districts) ? '' : $districts->district_name),
                'pin_code' => $users->member->pin_code,
                'office_address' => $users->member->office_address,
                'pan_number' => $users->member->pan_number,
                'gst_number' => $users->member->gst_number,
                'recharge' => $users->profile->recharge,
                'money' => $users->profile->money,
                'aeps' => $users->profile->aeps,
                'payout' => $users->profile->payout,
                'pancard' => $users->profile->pancard,
                'ecommerce' => $users->profile->ecommerce,
                'is_online' => $is_online,
                'reason' => $users->reason,
                'user_balance' => number_format($users->balance->user_balance, 2),
                'aeps_balance' => number_format($users->balance->aeps_balance, 2),
                'login_otp' => $login_otp,
                'pan_username' => $pan_username,
                'pan_password' => $pan_password,

            );
            return Response()->json([
                'status' => 'success',
                'details' => $details,
            ]);
        } else {
            return Response()->json([
                'status' => 'failure',
                'message' => 'User not found'
            ]);
        }
    }

    function view_update_users($encrypt_id)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['update_member_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        $user_id = Crypt::decrypt($encrypt_id);
        $userdetails = User::where('id', $user_id)->first();
        if ($userdetails) {
            if (Auth::User()->role_id <= 2 || $userdetails->parent_id == Auth::id()) {
                $details = array(
                    'user_id' => $encrypt_id,
                    'name' => $userdetails->name,
                    'last_name' => $userdetails->last_name,
                    'email' => $userdetails->email,
                    'mobile' => $userdetails->mobile,
                    'role_id' => $userdetails->role_id,
                    'scheme_id' => $userdetails->scheme_id,
                    'lock_amount' => $userdetails->lock_amount,
                    'company_id' => $userdetails->company_id,
                    'parent_id' => $userdetails->parent_id,
                    'gst_type' => $userdetails->gst_type,
                    'user_gst_type' => $userdetails->user_gst_type,
                    'pan_username' => $userdetails->pan_username,
                    'pan_password' => $userdetails->pan_password,
                    'status_id' => $userdetails->status_id,
                    'login_restrictions' => $userdetails->login_restrictions,
                    'latitude' => $userdetails->latitude,
                    'longitude' => $userdetails->longitude,

                    'member_id' => $userdetails->member->id,
                    'address' => $userdetails->member->address,
                    'city' => $userdetails->member->city,
                    'state_id' => $userdetails->member->state_id,
                    'district_id' => $userdetails->member->district_id,
                    'pin_code' => $userdetails->member->pin_code,
                    'shop_name' => $userdetails->member->shop_name,
                    'office_address' => $userdetails->member->office_address,
                    'pan_number' => $userdetails->member->pan_number,
                    'gst_number' => $userdetails->member->gst_number,

                    'profile_id' => $userdetails->profile->id,
                    'seller' => $userdetails->profile->seller,
                    'day_book' => $userdetails->profile->day_book,
                    'monthly_statement' => $userdetails->profile->monthly_statement,
                    'active_services' => $userdetails->profile->active_services,
                );

                if (Auth::User()->role_id == 1) {
                    $roledetails = Role::where('id', '>', Auth::user()->role_id)->where('status_id', 1)->get();
                } else {
                    $roledetails = Role::where('id', '>', Auth::user()->role_id)->whereNotIn('id', [9, 10])->where('status_id', 1)->get();
                }
                $schemes = Scheme::where('user_id', Auth::id())->get();
                $state = State::where('status_id', 1)->get();
                $permanentdistrict = District::where('status_id', 1)->where('state_id', $userdetails->member->state_id)->get();
                $companies = Company::where('status_id', 1)->where('parent_id', Auth::id())->get();
                $companies_id = array();
                foreach ($companies as $value) {
                    $companies_id[] = $value->id;
                }
                $my_company_id = array(Auth::User()->company_id);
                $company_id = array_merge($companies_id, $my_company_id);
                $company = Company::whereIn('id', $company_id)->where('status_id', 1)->get();
                $parents = User::whereIn('role_id', [1, 2, 3, 4, 5, 6, 7, 8])->where('company_id', Auth::user()->company_id)->get();
                $servicegroup_id = Servicegroup::where('status_id', 1)->get(['id']);
                $services = Service::whereIn('status_id', [1])->whereIn('servicegroup_id', $servicegroup_id)->get();
                $data = array('page_title' => 'Update User : ' . $userdetails->name . ' ' . $userdetails->last_name . '');
                if ($this->backend_template_id == 1) {
                    return view('admin.view_update_users', compact('roledetails', 'schemes', 'state', 'permanentdistrict', 'company', 'parents', 'services'))->with($data)->with($details);
                } elseif ($this->backend_template_id == 2) {
                    return view('themes2.admin.view_update_users', compact('roledetails', 'schemes', 'state', 'permanentdistrict', 'company', 'parents'))->with($data)->with($details);
                } elseif ($this->backend_template_id == 3) {
                    return view('themes3.admin.view_update_users', compact('roledetails', 'schemes', 'state', 'permanentdistrict', 'company', 'parents'))->with($data)->with($details);
                } elseif ($this->backend_template_id == 4) {
                    return view('themes4.admin.view_update_users', compact('roledetails', 'schemes', 'state', 'permanentdistrict', 'company', 'parents'))->with($data)->with($details);
                } else {
                    return redirect()->back();
                }

            } else {
                return redirect()->back();
            }
        } else {
            return Redirect::back();
        }
    }

    function view_user_kyc($encrypt_id)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['viewUser_kyc_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        $user_id = Crypt::decrypt($encrypt_id);
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
            'edit_url' => url('admin/view-update-users') . '/' . Crypt::encrypt($encrypt_id),
            'kyc_status' => $userdetails->member->kyc_status,
            'user_id' => $userdetails->id,
            'kyc_remark' => $userdetails->member->kyc_remark,


        );
        $page_title = $userdetails->name . ' Kyc';
        $data = array('page_title' => $page_title);
        if ($this->backend_template_id == 1) {
            return view('admin.view_user_kyc')->with($data)->with($details);
        } elseif ($this->backend_template_id == 2) {
            return view('themes2.admin.view_user_kyc')->with($data)->with($details);
        } elseif ($this->backend_template_id == 3) {
            return view('themes3.admin.view_user_kyc')->with($data)->with($details);
        } elseif ($this->backend_template_id == 4) {
            return view('themes4.admin.view_user_kyc')->with($data)->with($details);
        } else {
            return redirect()->back();
        }
    }

    function update_kyc(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['update_kyc_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $rules = array(
                'user_id' => 'required',
                'kyc_remark' => 'required',
                'kyc_status' => 'required',

            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $user_id = $request->user_id;
            $kyc_remark = $request->kyc_remark;
            $kyc_status = $request->kyc_status;
            Member::where('user_id', $user_id)->update([
                'kyc_status' => $kyc_status,
                'kyc_remark' => $kyc_remark,
            ]);
            User::where('id', $user_id)->update(['active' => 1]);
            return Response()->json(['status' => 'success', 'message' => 'kyc update successfull']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'sorry not permission']);
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


    function reset_password(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['company_settings_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $rules = array(
                'admin_password' => 'required',
                'password' => 'required|string|min:8|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/',

            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $user_id = $request->user_id;
            $admin_password = $request->admin_password;
            $password = $request->password;
            $userdetail = User::find(Auth::id());
            $current_password = $userdetail->password;
            if (Hash::check($admin_password, $current_password)) {
                User::where('id', $user_id)->update(['password' => Hash::make($password)]);
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
                $customerdetails = User::where('id', $user_id)->first();
                $message = "Dear $customerdetails->name Your password reset by $userdetail->name now your new password is : $password $this->brand_name";
                $template_id = 3;
                $library = new SmsLibrary();
                $library->send_sms($customerdetails->mobile, $message, $template_id);
                return Response()->json(['status' => 'success', 'message' => 'Password successfully reset']);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Your login password is wrong']);
            }
        }


    }

    function refresh_scheme(Request $request)
    {
        $schemes = Scheme::where('user_id', Auth::id())->orderBy('id', 'DESC')->get();
        $response = array();
        foreach ($schemes as $value) {
            $product = array();
            $product["scheme_id"] = $value->id;
            $product["scheme_name"] = $value->scheme_name;
            array_push($response, $product);
        }
        return Response()->json(['status' => 'success', 'scheme' => $response]);
    }


    function export_member(Request $request)
    {
        $users = User::all();
        $arr = array();
        foreach ($users as $value) {
            $data = array(
                $value->id,
            );
            array_push($arr, $data);
        }

        $delimiter = ",";
        $filename = 'download/member_export_' . mt_rand(10, 99) . '.csv';
        $fp = fopen($filename, 'w+');
        $col = ['User Id'];
        fputcsv($fp, $col, $delimiter);
        foreach ($arr as $line) {
            fputcsv($fp, $line, $delimiter);
        }
        fclose($fp);
        $url = url('') . '/' . $filename;
        echo "<a href='$url' download>download</a>";
        exit();
    }


    function not_working_users()
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['not_working_users_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 7) {
            $data = array('page_title' => 'Not Working Users',);
            $states = State::where('status_id', 1)->get();
            if ($this->backend_template_id == 1) {
                return view('admin.not_working_users', compact('states'))->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.not_working_users', compact('states'))->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.not_working_users', compact('states'))->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.not_working_users', compact('states'))->with($data);
            } else {
                return redirect()->back();
            }
        } else {
            return Redirect::back();
        }
    }

    function not_working_users_api(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length"); // Rows display per page

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc
        $searchValue = $search_arr['value']; // Search value
        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);

        $datefrom = Carbon\Carbon::now()->startOfMonth()->subMonth()->toDateString();
        $dateto = date('Y-m-d', time());
        $report = Report::whereDate('created_at', '>=', $datefrom)->whereDate('created_at', '<=', $dateto)->distinct()->select(['user_id'])->get();
        $user_id = array();
        foreach ($report as $member) {
            $user_id[] = $member->user_id;
        }

        // Total records
        $totalRecords = User::select('count(*) as allcount')
            ->whereIn('id', $my_down_member)
            ->whereNotIn('id', $user_id)
            ->count();

        $totalRecordswithFilter = User::select('count(*) as allcount')
            ->whereIn('id', $my_down_member)
            ->whereNotIn('id', $user_id)
            ->where(function ($query) use ($searchValue) {
                $query->where('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('mobile', 'like', '%' . $searchValue . '%')
                    ->orWhere('email', 'like', '%' . $searchValue . '%');
            })->count();

        // Fetch records

        $records = User::orderBy($columnName, $columnSortOrder)
            ->whereIn('id', $my_down_member)
            ->whereNotIn('id', $user_id)
            ->where(function ($query) use ($searchValue) {
                $query->where('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('mobile', 'like', '%' . $searchValue . '%')
                    ->orWhere('email', 'like', '%' . $searchValue . '%');
            })->skip($start)
            ->take($rowperpage)
            ->get();

        $data_arr = array();
        foreach ($records as $value) {
            $report = Report::where('user_id', $value->id)->orderBy('id', 'DESC')->first();
            if ($report) {
                $last_date = $report->created_at->format('Y-m-d h:m:s');
            } else {
                $last_date = "Not working til now";
            }
            $statement_url = url('admin/report/v1/user-ledger-report') . '/' . Crypt::encrypt($value->id);
            $data_arr[] = array(
                "id" => '<button class="btn btn-danger btn-sm" onclick="view_members(' . $value->id . ')"> ' . $value->id . ' View</button>',
                "username" => $value->name . ' ' . $value->last_name,
                "mobile" => $value->mobile,
                "balance" => number_format($value->balance->user_balance, 2),
                "member_type" => $value->role->role_title,
                "last_date" => "$last_date",
                "statement" => '<a href="' . $statement_url . '" class="btn btn-primary btn-sm">Statement</a>',

            );
        }

        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordswithFilter,
            "aaData" => $data_arr
        );

        echo json_encode($response);
        exit;
    }


    function create_pancard_id(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $user_id = Crypt::decrypt($request->user_id);
            $userdetails = User::where('id', $user_id)->first();
            $sender_id = Auth::User()->company->sender_id;
            if ($userdetails) {
                $url = "https://api.pay2all.in//v1/pan/agent?mobile_number=$userdetails->mobile";
                $api_request_parameters = array();
                $method = 'GET';
                $header = ["Accept:application/json", "Authorization:" . $this->key];
                $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
                $res = json_decode($response);
                if ($res->message == 'Success') {
                    User::where('id', $userdetails->id)->update(['pan_username' => $res->username, 'pan_password' => $res->password]);
                    return Response(['status' => 'success', 'message' => 'Successful..!']);
                } else {
                    $url = "https://api.pay2all.in//v1/pan/agent/create";
                    $api_request_parameters = array('mobile_number' => $userdetails->mobile, 'username' => $sender_id . '' . $userdetails->id);
                    $method = 'POST';
                    $header = ["Accept:application/json", "Authorization:" . $this->key];
                    $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
                    $res = json_decode($response);
                    if ($res->status == 0) {
                        User::where('id', $userdetails->id)->update(['pan_username' => $res->username, 'pan_password' => $res->password]);
                        return Response(['status' => 'success', 'message' => 'Successful..!']);
                    } else {
                        return Response()->json(['status' => 'failure', 'message' => $res->message]);
                    }
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'record not found']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function update_dropdown_package(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $rules = array(
                'scheme_id' => 'required|exists:schemes,id',
                'user_id' => 'required|exists:users,id',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
            }
            $scheme_id = $request->scheme_id;
            $user_id = $request->user_id;
            User::where('id', $user_id)->update(['scheme_id' => $scheme_id]);
            return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function update_dropdown_parent(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $rules = array(
                'parent_id' => 'required|exists:users,id',
                'user_id' => 'required|exists:users,id',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
            }
            $parent_id = $request->parent_id;
            $user_id = $request->user_id;
            User::where('id', $user_id)->update(['parent_id' => $parent_id]);
            return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function all_user_list($slug = null)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['member_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        $optional1 = (empty($slug)) ? '' : Crypt::decrypt($slug);
        $type = (empty($slug)) ? 0 : 1;
        $data = array(
            'page_title' => 'User By Package',
            'url' => url('admin/all-user-list-api') . '?' . 'optional1=' . $optional1 . '&type=' . $type,
        );
        $states = State::where('status_id', 1)->get();
        if ($this->backend_template_id == 1) {
            return view('admin.all_user_list', compact('states'))->with($data);
        } elseif ($this->backend_template_id == 2) {
            return view('themes2.admin.all_user_list', compact('states'))->with($data);
        } elseif ($this->backend_template_id == 3) {
            return view('themes3.admin.all_user_list', compact('states'))->with($data);
        } elseif ($this->backend_template_id == 4) {
            return view('themes4.admin.all_user_list', compact('states'))->with($data);
        } else {
            return redirect()->back();
        }
    }

    function all_user_list_api(Request $request)
    {
        $optional1 = $request->optional1;
        $type = $request->get('amp;type');
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length"); // Rows display per page

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc
        $searchValue = $search_arr['value']; // Search value
        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);

        // Total records
        if ($type == 1) {
            $totalRecords = User::select('count(*) as allcount')
                ->whereIn('id', $my_down_member)
                ->where('scheme_id', $optional1)
                ->count();

            $totalRecordswithFilter = User::select('count(*) as allcount')
                ->whereIn('id', $my_down_member)
                ->where('scheme_id', $optional1)
                ->where(function ($query) use ($searchValue) {
                    $query->where('name', 'like', '%' . $searchValue . '%')
                        ->orWhere('mobile', 'like', '%' . $searchValue . '%')
                        ->orWhere('email', 'like', '%' . $searchValue . '%');
                })->count();
            // Fetch records
            $records = User::orderBy($columnName, $columnSortOrder)
                ->whereIn('id', $my_down_member)
                ->where('scheme_id', $optional1)
                ->orderBy('id', 'DESC')
                ->where(function ($query) use ($searchValue) {
                    $query->where('name', 'like', '%' . $searchValue . '%')
                        ->orWhere('mobile', 'like', '%' . $searchValue . '%')
                        ->orWhere('email', 'like', '%' . $searchValue . '%');
                })->skip($start)
                ->take($rowperpage)
                ->get();
        } else {
            $totalRecords = User::select('count(*) as allcount')
                ->whereIn('id', $my_down_member)
                ->whereNotIn('id', [Auth::id()])
                ->count();
            $totalRecordswithFilter = User::select('count(*) as allcount')
                ->whereIn('id', $my_down_member)
                ->whereNotIn('id', [Auth::id()])
                ->where(function ($query) use ($searchValue) {
                    $query->where('name', 'like', '%' . $searchValue . '%')
                        ->orWhere('mobile', 'like', '%' . $searchValue . '%')
                        ->orWhere('email', 'like', '%' . $searchValue . '%');
                })->count();
            // Fetch records
            $records = User::orderBy($columnName, $columnSortOrder)
                ->whereIn('id', $my_down_member)
                ->whereNotIn('id', [Auth::id()])
                ->orderBy('id', 'DESC')
                ->where(function ($query) use ($searchValue) {
                    $query->where('name', 'like', '%' . $searchValue . '%')
                        ->orWhere('mobile', 'like', '%' . $searchValue . '%')
                        ->orWhere('email', 'like', '%' . $searchValue . '%');
                })->skip($start)
                ->take($rowperpage)
                ->get();
        }
        $role_slug = "";
        Self::userListCommon($records, $draw, $totalRecords, $totalRecordswithFilter, $role_slug);
    }

    function member_list_api(Request $request)
    {
        $role_slug = $request->role_slug;
        $parent_id = $request->get('amp;parent_id');
        $roles = Role::where('role_slug', $role_slug)->first();
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length"); // Rows display per page

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc
        $searchValue = $search_arr['value']; // Search value
        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);

        // Total records
        if ($parent_id == 0) {
            $totalRecords = User::select('count(*) as allcount')
                ->whereIn('id', $my_down_member)
                ->where('role_id', $roles->id)
                ->count();
            $totalRecordswithFilter = User::select('count(*) as allcount')
                ->whereIn('id', $my_down_member)
                ->where('role_id', $roles->id)
                ->where(function ($query) use ($searchValue) {
                    $query->where('name', 'like', '%' . $searchValue . '%')
                        ->orWhere('mobile', 'like', '%' . $searchValue . '%')
                        ->orWhere('email', 'like', '%' . $searchValue . '%');
                })->count();
            // Fetch records
            $records = User::orderBy($columnName, $columnSortOrder)
                ->whereIn('id', $my_down_member)
                ->where('role_id', $roles->id)
                ->orderBy('id', 'DESC')
                ->where(function ($query) use ($searchValue) {
                    $query->where('name', 'like', '%' . $searchValue . '%')
                        ->orWhere('mobile', 'like', '%' . $searchValue . '%')
                        ->orWhere('email', 'like', '%' . $searchValue . '%');
                })->skip($start)
                ->take($rowperpage)
                ->get();
        } else {
            $totalRecords = User::select('count(*) as allcount')
                ->whereIn('id', $my_down_member)
                ->where('parent_id', $parent_id)
                ->count();
            $totalRecordswithFilter = User::select('count(*) as allcount')
                ->whereIn('id', $my_down_member)
                ->where('parent_id', $parent_id)
                ->where(function ($query) use ($searchValue) {
                    $query->where('name', 'like', '%' . $searchValue . '%')
                        ->orWhere('mobile', 'like', '%' . $searchValue . '%')
                        ->orWhere('email', 'like', '%' . $searchValue . '%');
                })->count();
            // Fetch records
            $records = User::orderBy($columnName, $columnSortOrder)
                ->whereIn('id', $my_down_member)
                ->where('parent_id', $parent_id)
                ->orderBy('id', 'DESC')
                ->where(function ($query) use ($searchValue) {
                    $query->where('name', 'like', '%' . $searchValue . '%')
                        ->orWhere('mobile', 'like', '%' . $searchValue . '%')
                        ->orWhere('email', 'like', '%' . $searchValue . '%');
                })->skip($start)
                ->take($rowperpage)
                ->get();
        }
        Self::userListCommon($records, $draw, $totalRecords, $totalRecordswithFilter, $role_slug);
    }


    function suspended_user_api(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length"); // Rows display per page

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc
        $searchValue = $search_arr['value']; // Search value
        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);

        // Total records
        $totalRecords = User::select('count(*) as allcount')
            ->whereIn('id', $my_down_member)
            ->whereNotIn('active', [1])
            ->count();

        $totalRecordswithFilter = User::select('count(*) as allcount')
            ->whereIn('id', $my_down_member)
            ->whereNotIn('active', [1])
            ->where(function ($query) use ($searchValue) {
                $query->where('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('mobile', 'like', '%' . $searchValue . '%')
                    ->orWhere('email', 'like', '%' . $searchValue . '%');
            })->count();

        // Fetch records

        $records = User::orderBy($columnName, $columnSortOrder)
            ->whereIn('id', $my_down_member)
            ->whereNotIn('active', [1])
            ->where(function ($query) use ($searchValue) {
                $query->where('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('mobile', 'like', '%' . $searchValue . '%')
                    ->orWhere('email', 'like', '%' . $searchValue . '%');
            })->skip($start)
            ->take($rowperpage)
            ->get();
        $role_slug = "";
        Self::userListCommon($records, $draw, $totalRecords, $totalRecordswithFilter, $role_slug);
    }

    function userListCommon($records, $draw, $totalRecords, $totalRecordswithFilter, $role_slug)
    {
        $data_arr = array();
        foreach ($records as $value) {
            if ($value->status_id == 1) {
                $status = '<span class="badge badge-success">Enabled</span>';
            } else {
                $status = '<span class="badge badge-danger">Disabled</span>';
            }
            if ($value->mobile_verified == 1) {
                $mobile = '<span>' . $value->mobile . '</span>';
            } else {
                $mobile = '<span style="color:red;" alt="mobile number not verified">' . $value->mobile . '</span>';
            }
            if (Cache::has('is_online' . $value->id)) {
                $is_online = '<span class="badge badge-success">Online</span>';
            } else {
                $is_online = Carbon\Carbon::parse($value->last_seen)->diffForHumans();
            }
            // this is for scheme
            $schemes = Scheme::find($value->scheme_id);
            if ($schemes) {
                $package_id = $schemes->id;
                $package_name = $schemes->scheme_name;
            } else {
                $package_id = 0;
                $package_name = "No Package";
            }
            $schemeLoop = Scheme::where('status_id', 1)->whereNotIn('id', [$package_id])->get();
            $newPackageName = '<select class="form-control" id="packageId_' . $value->id . '" onchange="adminUpdatePackage(' . $value->id . ')" style="width:100%;">
            <option value="' . $package_id . '">' . $package_name . '</option>';
            foreach ($schemeLoop as $scheme):
                $newPackageName .= '<option value="' . $scheme->id . '"> ' . $scheme->scheme_name . '</option>';
            endforeach;
            $newPackageName .= '</select>';
            // end scheme
            $role_slug = (empty($role_slug)) ? $value->role->role_slug : $role_slug;
            $parent_down_users = url('admin/parent-down-users') . '/' . $role_slug . '/' . Crypt::encrypt($value->id);
            $parent_name = User::find($value->parent_id)->name;
            $parent_last_name = User::find($value->parent_id)->last_name;
            $statement_url = url('admin/report/v1/user-ledger-report') . '/' . Crypt::encrypt($value->id);
            $countmyusers = User::where('parent_id', $value->id)->count();
            // parent dropdown for admin
            $parentLoop = User::whereIn('role_id', [1, 2, 3, 4, 5, 6, 7, 8])->whereNotIn('id', [$value->parent_id])->where('company_id', Auth::user()->company_id)->get();
            $parentDropDown = '<select class="form-control" id="parentId_' . $value->id . '" onchange="adminUpdateParent(' . $value->id . ')">
            <option value="' . $value->parent_id . '">' . $parent_name . ' ' . $parent_last_name . '</option>';
            foreach ($parentLoop as $par):
                $parentDropDown .= '<option value="' . $par->id . '"> ' . $par->name . ' ' . $par->last_name . '</option>';
            endforeach;
            $parentDropDown .= '</select>';
            //close parent dropdown for admin
            $isAadharVerify = ($value->isAadharVerify == 1) ? '<i class="fas fa-check-square" style="color: green;"></i>' : '<i class="fas fa-times" style="color: red;"></i>';
            $isPanVerify = ($value->isPanVerify == 1) ? '<i class="fas fa-check-square" style="color: green;"></i>' : '<i class="fas fa-times" style="color: red;"></i>';
            $data_arr[] = array(
                "id" => '<button class="btn btn-danger btn-sm" onclick="view_members(' . $value->id . ')"> ' . $value->id . ' View</button>',
                "joining_date" => "$value->created_at",
                "name" => '<a href="' . $parent_down_users . '">' . $value->name . ' ' . $value->last_name . ' (' . $countmyusers . ')</a>',
                "mobile_number" => $mobile,
                "member_type" => $value->role->role_title,
                "user_balance" => number_format($value->balance->user_balance, 2),
                "parent_name" => (Auth::User()->role_id != 1) ? $parent_name . ' ' . $parent_last_name : $parentDropDown,
                "package_name" => (Auth::User()->role_id != 1) ? $package_name : $newPackageName,
                "status" => $status,
                "isAadharVerify" => $isAadharVerify,
                "isPanVerify" => $isPanVerify,
                "care_of" => $value->member->care_of,
                "dob" => $value->member->dob,
                "gender" => ($value->member->gender == 'M') ? 'Male' : 'Female',
                'is_online' => ($is_online == '1 second ago') ? 'not logged in yet' : $is_online,
                "statement" => '<a href="' . $statement_url . '" class="btn btn-primary btn-sm">Statement</a>',
            );
        }

        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordswithFilter,
            "aaData" => $data_arr
        );

        echo json_encode($response);
        exit;
    }

    function force_logout_all_users(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            try {
                $role_id = Auth::User()->role_id;
                $company_id = Auth::User()->company_id;
                $user_id = Auth::id();
                $library = new MemberLibrary();
                $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);
                $users = User::whereIn('id', $my_down_member)->get();
                foreach ($users as $value) {
                    $child_id = $value->id;
                    $new_session_id = \Session::getId(); //get new session_id after user sign in
                    $userDetails = User::find($child_id);
                    if ($userDetails->session_id != '') {
                        $last_session = \Session::getHandler()->read($userDetails->session_id);
                        if ($last_session) {
                            if (\Session::getHandler()->destroy($userDetails->session_id)) {
                            }
                        }
                    }
                    User::where('id', $child_id)->update(['session_id' => $new_session_id]);
                }
                return Response()->json(['status' => 'success', 'message' => 'All users successfully logouts!']);
            } catch (ModelNotFoundException $exception) {
                return Response()->json(['status' => 'failure', 'message' => $exception->getMessage()]);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function view_user_active_services(Request $request)
    {
        $user_id = Crypt::decrypt($request->user_id);
        $profiles = Profile::where('user_id', $user_id)->first();
        return Response()->json([
            'status' => 'success',
            'message' => 'Successful..!',
            'active_services' => $profiles->active_services,
        ]);
    }

}
