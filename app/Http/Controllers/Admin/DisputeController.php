<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use App\Models\Dispute;
use App\Models\Report;
use App\Models\Disputechat;
use App\Models\Sitesetting;
use App\Models\Provider;
use App\Models\User;
use Cache;
use Carbon;
use Helpers;
use \Crypt;
use App\Library\MemberLibrary;
use App\Library\SmsLibrary;
use App\Library\PermissionLibrary;

class DisputeController extends Controller {

    public function __construct()   {
        $this->company_id = Helpers::company_id()->id;
        $companies = Helpers::company_id();
        $this->company_id = $companies->id;
        $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
        if ($sitesettings){
            $this->brand_name = $sitesettings->brand_name;
        }else{
            $this->brand_name = "";
        }
    }

    function pending_dispute (){
        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);
        $dispute = Dispute::whereIn('user_id', $my_down_member)->where('status_id', 3)->get();
        $data = array('page_title' => 'Pending Dispute',);
        return view('admin.dispute.pending_dispute', compact('dispute'))->with($data);
    }

    function solve_dispute (){
        // get staff permission
        if (Auth::User()->role_id == 2){
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['solve_dispute_permission'];
            if (!$myPermission == 1){
                return redirect()->back();
            }
        }
        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);
        $dispute = Dispute::whereIn('user_id', $my_down_member)->where('status_id', 1)->get();
        $data = array(
            'page_title' => 'Solve Dispute',
            'url' => url('admin/solve-dispute-api'),
            );
        return view('admin.dispute.solve_dispute', compact('dispute'))->with($data);
    }

    function solve_dispute_api (Request $request){
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

        $user_id = Auth::id();
        $totalRecords = Dispute::select('count(*) as allcount')
            ->whereIn('user_id', $my_down_member)
            ->where('status_id', 1)
            ->count();

        $totalRecordswithFilter = Dispute::select('count(*) as allcount')
            ->whereIn('user_id', $my_down_member)
            ->where('message', 'like', '%' .$searchValue . '%')
            ->where('status_id', 1)
            ->count();

        // Fetch records

        $records = Dispute::orderBy($columnName,$columnSortOrder)
            ->where('message', 'like', '%' .$searchValue . '%')
            ->whereIn('user_id', $my_down_member)
            ->where('status_id', 1)
            ->orderBy('id', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();
        $data_arr = array();
        foreach($records as $value){
            $statement_url = url('admin/user-ledger-report').'/'.Crypt::encrypt($value->user_id);
            $txn_date = $value->report->created_at;
            $chat_count = Disputechat::where('dispute_id',  $value->id)->whereNotIn('user_id', [Auth::id()])->where('is_read', 0)->count();
            $data_arr[] = array(
                "id" => $value->id,
                "txn_date" => "$txn_date",
                "user" => '<a href="'.$statement_url.'">'.$value->user->name . ' '. $value->user->last_name.'</a>',
                "dispute_date" => "$value->created_at",
                "provider" => $value->report->provider->provider_name,
                "number" => $value->report->number,
                "amount" => number_format($value->report->amount,2),
                "reason" => $value->disputereason->reason,
                "status" => '<span class="'. $value->status->class.'">'. $value->status->status.'</span>',
                "recharge_status" => '<span class="'. $value->report->status->class.'">'. $value->report->status->status.'</span>',
                "view" => '<button class="btn btn-danger btn-sm" onclick="view_conversation('.$value->id .')">Chat '.$chat_count.'</button>',
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

    function dispute_transaction (Request $request){
        $rules = array(
            'report_id' => 'required|exists:reports,id|unique:disputes',
            'reason' => 'required|exists:disputereasons,id',
            'message' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $report_id = $request->report_id;
        $reason = $request->reason;
        $message = $request->message;
        $now = new \DateTime();
        $ctime = $now->format('Y-m-d H:i:s');
        $report = Report::where('id', $report_id)->whereIn('status_id', [1,2,3,5])->first();
        if ($report){
            Dispute::insertGetId([
                'user_id' => Auth::id(),
                'disputereason_id' => $reason,
                'report_id' => $report_id,
                'api_id' => $report->api_id,
                'message' => $message,
                'created_at' => $ctime,
                'company_id' => Auth::User()->company_id,
                'status_id' => 3,
            ]);
            return Response()->json(['status' => 'success', 'message' => 'Transaction dispute successfully!']);
        }else{
            return Response()->json(['status' => 'failure', 'message' => 'Sorry this type of status not allowed']);
        }
    }

    function view_dispute_conversation (Request $request){
        $id = $request->id;
        $user_id = Auth::id();
        $company_id = Auth::User()->company_id;
        $role_id = Auth::User()->role_id;
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);
        $dispute = Dispute::where('id', $id)->whereIn('user_id', $my_down_member)->first();
        if ($dispute){
            $transaction_date = date('d-m-Y', strtotime($dispute->report->created_at));
            if (Cache::has('is_online' . $dispute->user_id)){
                $is_online = 'Online';
            }else{
                $is_online = 'Last seen:  '.Carbon\Carbon::parse($dispute->user->last_seen)->diffForHumans();
            }

            $users = array(
                'name' => $dispute->user->name.' '.$dispute->user->last_name,
                'is_online' => $is_online,
            );
            $refund_anchor = url('admin/search-refund-manager?search_type=2&number='. $dispute->report_id .'');
            if ($dispute->status_id == 3){
                $complaint_status = 'Pending';
            }elseif ($dispute->status_id == 1){
                $complaint_status = 'Solve';
            }else{
                $complaint_status = '';
            }
            $recharge = array(
                'dispute_id' => $dispute->id,
                'report_id' => $dispute->report_id,
                'provider' => $dispute->report->provider->provider_name,
                'amount' => number_format($dispute->report->amount,2),
                'txnid' => $dispute->report->txnid,
                'transaction_date' => "$transaction_date",
                'transaction_status' => $dispute->report->status->status,
                'number' => $dispute->report->number,
                'complaint_status' => $complaint_status,
                'complaint_reason' => $dispute->disputereason->reason,
                'refund_anchor' => $refund_anchor,
            );
            return Response()->json(['status' => 'success', 'recharge' => $recharge, 'users' => $users]);
        }else{
            return Response()->json(['status' => 'failure', 'message' => 'record not found']);
        }
    }

    function get_dispute_chat (Request $request){
        $dispute_id = $request->dispute_id;
        $chats = Disputechat::where('dispute_id', $dispute_id)->orderBy('id', 'ASC')->get();
        Disputechat::where('dispute_id', $dispute_id)->whereNotIn('user_id', [Auth::id()])->update(['is_read' => 1]);
        $results = '';
        foreach ($chats  as $value){
            if ($value->user_id == Auth::id()){
                $results .= '<div class="d-flex justify-content-end mb-4">
                            <div class="msg_cotainer_send">
                               '. $value->message.'
                              
                            </div>
                            <div class="img_cont_msg">
                                <img src="https://static.turbosquid.com/Preview/001292/481/WV/_D.jpg" class="rounded-circle user_img_msg">
                            </div>
                        </div>';
            }else{
                $results .= '<div class="d-flex justify-content-start mb-4">
                            <div class="img_cont_msg">
                                <img src="https://static.turbosquid.com/Preview/001292/481/WV/_D.jpg" class="rounded-circle user_img_msg">
                            </div>
                            <div class="msg_cotainer">
                                 '. $value->message.'
                           
                            </div>
                        </div>';
            }

        }
        return $results;
    }

    function send_chat_message (Request $request){
        // get staff permission
        if (Auth::User()->role_id == 2){
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['dispute_chat_permission'];
            if (!$myPermission == 1){
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        $rules = array(
            'chat_message' => 'required',
            'dispute_id' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $chat_message = $request->chat_message;
        $dispute_id = $request->dispute_id;
        $now = new \DateTime();
        $ctime = $now->format('Y-m-d H:i:s');
        Disputechat::insertGetId([
            'user_id' => Auth::id(),
            'dispute_id' => $dispute_id,
            'message' => $chat_message,
            'created_at' => $ctime,
            'is_read' => 0,
        ]);
        return Response()->json(['status' => 'success', 'message' => 'success']);
    }

    function update_complaint_status (Request $request){
        // get staff permission
        if (Auth::User()->role_id == 2){
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['dispute_update_permission'];
            if (!$myPermission == 1){
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
       if (Auth::User()->role_id <= 2){
        if ($request->complaint_status == 0){
            return Response()->json(['status' => 'failure', 'message' => 'kindly select status']);
        }else{
        $complaint_status = $request->complaint_status;
        $dispute_id = $request->dispute_id;
        Dispute::where('id', $dispute_id)->update(['status_id' => $complaint_status]);
        if ($complaint_status == 1){
            $disputes = Dispute::find($dispute_id);
            $userdetails = User::find($disputes->user_id);
            $reports = Report::find($disputes->report_id);
            $providers = Provider::find($reports->provider_id);
            $recharge_status = $reports->status->status;
            $message = "Your complaint request for  $providers->provider_name  number $reports->number of Rs.$reports->amount has been resolved status : $recharge_status Thanks $this->brand_name";
            $template_id = 9;
            $library = new SmsLibrary();
            $library->send_sms($userdetails->mobile, $message, $template_id);
        }
        return Response()->json(['status' => 'success', 'message' => 'Dispute status successfully updated']);
        }
       }else{
           return Response()->json(['status' => 'failure', 'message' => 'sorry not permission']);
       }
    }


}
