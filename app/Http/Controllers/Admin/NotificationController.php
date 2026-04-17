<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use App\Models\Role;
use App\Notifications\DatabseNotification;
use Notification;
use App\Models\User;
use App\Models\Sitesetting;
use DB;
use Carbon;
use Helpers;
use App\Library\MemberLibrary;
use App\Library\SmsLibrary;
use App\Library\PermissionLibrary;

class NotificationController extends Controller
{
    //

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

    function welcome (){
        // get staff permission
        if (Auth::User()->role_id == 2){
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['notification_settings_permission'];
            if (!$myPermission == 1){
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2){
            if (Auth::User()->role_id <= 2) {
                $roledetails = Role::where('id', '>', Auth::user()->role_id)->where('status_id', 1)->get();
            }else{
                $roledetails = Role::where('id', '>', Auth::user()->role_id)->whereNotIn('id', [9,10])->where('status_id', 1)->get();
            }
            $data = array('page_title' => 'Notifications');
            if ($this->backend_template_id == 1) {
                return view('admin.notification.welcome', compact('roledetails'))->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.notification.welcome', compact('roledetails'))->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.notification.welcome', compact('roledetails'))->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.notification.welcome', compact('roledetails'))->with($data);
            } else {
                return redirect()->back();
            }
        }else{
            return redirect()->back();
        }

    }

    function send_notification (Request $request){
        $rules = array(
            'notification_title' => 'required',
            'notification_message' => 'required',
            'role_id' => 'required',
            'notification_type' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }


        $message = $request->notification_message;
        $exploadnotificationtype = explode(',', $request->notification_type);
        foreach ($exploadnotificationtype as $notification_type){
            if ($notification_type == 1){
                $exploadroles = explode(',', $request->role_id);
                foreach ($exploadroles as $role_id){
                    $users = User::where('role_id', $role_id)->get();
                    foreach ($users as $value){
                        $template_id = 15;
                        $library = new SmsLibrary();
                        $library->send_sms_api($value->mobile, $message, $template_id);
                    }
                }

            }

            if ($notification_type == 2){
                $exploadroles = explode(',', $request->role_id);
                foreach ($exploadroles as $role_id){
                    $users = User::where('role_id', $role_id)->get();
                    foreach ($users as $value){
                        $template_id = 15;
                        $library = new SmsLibrary();
                        $library->send_whatsapp_api($value->mobile, $message, $template_id);
                    }
                }
            }
        }
        return Response()->json(['status' => 'success',  'message' => 'Notification successfully sent to selected users']);
    }



    function mark_all_read (Request $request){
        Auth::User()->notifications->markAsRead();
        return redirect()->back();
    }

    function view_notification ($id){
        $notifications = DB::table('notifications')->where('id', $id)->first();
        if ($notifications){
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            DB::table('notifications')->where('id', $id)->update(['read_at' => $ctime]);
            $datas = $notifications->data;
            $res = json_decode($datas);
            $data = array(
                'page_title' => 'Notification',
                'notitication_title' => $res->letter->title,
                'notitication_body' => $res->letter->body,
                'time' => Carbon\Carbon::parse($notifications->created_at)->diffForHumans(),
                'created_at' => $notifications->created_at,
            );
            return view('admin.notification.view_notification')->with($data);

        }else{
            return redirect()->back();
        }
    }
}
