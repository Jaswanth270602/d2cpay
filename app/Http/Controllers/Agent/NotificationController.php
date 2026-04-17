<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use App\Notification;
use Carbon;

class NotificationController extends Controller
{


    function view_notification ($id){
     $notifications = Notification::find($id);
     if ($notifications){
         $now = new \DateTime();
         $ctime = $now->format('Y-m-d H:i:s');
         Notification::where('id', $id)->update(['read_at' => $ctime]);
         $datas = $notifications->data;
         $res = json_decode($datas);
         $data = array(
             'page_title' => 'Notification',
             'notitication_title' => $res->letter->title,
             'notitication_body' => $res->letter->body,
             'time' => Carbon\Carbon::parse($notifications->created_at)->diffForHumans(),
             'created_at' => $notifications->created_at,
         );
         return view('agent.notification.view_notification')->with($data);

     }else{
         return redirect()->back();
     }
    }

    function mark_all_read (Request $request){
        Auth::User()->notifications->markAsRead();
        return redirect()->back();
    }
}
