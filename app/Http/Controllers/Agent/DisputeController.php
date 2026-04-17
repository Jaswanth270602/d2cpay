<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;

use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use App\Models\Dispute;
use App\Models\Report;
use App\Models\Disputechat;
use App\Models\User;
use App\Models\Disputereason;
use App\Models\Api;
use Cache;
use Carbon;
use Helpers;
use App\Models\Sitesetting;
use App\Library\SmsLibrary;

class DisputeController extends Controller
{
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
        $dispute = Dispute::where('user_id', Auth::id())->where('status_id', 3)->get();
        $data = array('page_title' => 'Pending Dispute',);
        return view('agent.dispute.pending_dispute', compact('dispute'))->with($data);
    }

    function solve_dispute (){
        $dispute = Dispute::where('user_id', Auth::id())->where('status_id', 1)->get();
        $data = array('page_title' => 'Solve Dispute',);
        return view('agent.dispute.solve_dispute', compact('dispute'))->with($data);
    }

    function reason_application (){
        $reasons = Disputereason::where('company_id', Auth::User()->company_id)->where('status_id', 1)->get();
        $response = array();
        foreach ($reasons as $value) {
            $product = array();
            $product["reason_id"] = $value->id;
            $product["reason"] = $value->reason;
            array_push($response, $product);
        }
        return Response()->json(['status' => 'success', 'reason' => $response]);
    }

    function pending_dispute_app (Request $request){
        $reports = Dispute::where('user_id', Auth::id())->where('status_id', 3)->paginate(20);
        $response = array();
        foreach ($reports as $value) {
            $product = array();
            $product["ticket_id"] = $value->id;
            $product["user"] = $value->user->name;
            $product["date"] = $value->created_at->format('Y-m-d h:m:s');
            $product["provider"] = $value->report->provider->provider_name;
            $product["number"] = $value->report->number;
            $product["reason"] = $value->disputereason->reason;
            $product["message"] = $value->message;
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

    function solve_dispute_app (Request $request){
        $reports = Dispute::where('user_id', Auth::id())->where('status_id', 3)->paginate(20);
        $response = array();
        foreach ($reports as $value) {
            $product = array();
            $product["ticket_id"] = $value->id;
            $product["user"] = $value->user->name;
            $product["date"] = $value->created_at->format('Y-m-d h:m:s');
            $product["provider"] = $value->report->provider->provider_name;
            $product["number"] = $value->report->number;
            $product["reason"] = $value->disputereason->reason;
            $product["message"] = $value->message;
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

            $apis = Api::find($report->api_id);
            if ($apis){
                $created_at = $report->created_at;
                $message = "Complaint Number : $report->number, of Rs.$report->amount Date : $created_at  $this->brand_name";
                $template_id = 13;
                $library = new SmsLibrary();
                $library->send_sms($apis->support_number, $message, $template_id);
            }
            return Response()->json(['status' => 'success', 'message' => 'Transaction dispute successfully!']);
        }else{
            return Response()->json(['status' => 'failure', 'message' => 'Sorry this type of status not allowed']);
        }
    }

    function view_dispute_conversation (Request $request){
    $id = $request->id;
    $user_id = Auth::id();
    $dispute = Dispute::where('id', $id)->where('user_id', $user_id)->first();
    if ($dispute){
        $transaction_date = date('d-m-Y', strtotime($dispute->report->created_at));
        $userdetails = User::where('role_id', 1)->first();
        if (Cache::has('is_online' . $userdetails->id)){
            $is_online = 'Online';
        }else{
            $is_online = 'Last seen:  '.Carbon\Carbon::parse($userdetails->last_seen)->diffForHumans();
        }

        $users = array(
            'name' => $userdetails->name.' '.$userdetails->last_name,
            'is_online' => $is_online,
        );
        if ($dispute->status_id == 1){
            $complaint_status = 'Solve';
        }elseif ($dispute->status_id == 3){
            $complaint_status = 'Pending';
        }else{
            $complaint_status = 'Pending';
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
        );
        return Response()->json(['status' => 'success', 'recharge' => $recharge, 'users' => $users]);
    }else{
        return Response()->json(['status' => 'failure', 'message' => 'record not found']);
    }
    }

    function view_conversation_application (Request $request){
        $dispute_id = $request->dispute_id;
        $chats = Disputechat::where('dispute_id', $dispute_id)->orderBy('id', 'ASC')->get();
        Disputechat::where('dispute_id', $dispute_id)->whereNotIn('user_id', [Auth::id()])->update(['is_read' => 1]);
        $response = array();
        foreach ($chats as $value){
            $product = array();
            $product["id"] = $value->id;
            $product["user_id"] = $value->user_id;
            $product["dispute_id"] = $value->dispute_id;
            $product["message"] = $value->message;
            $product["created_at"] = $value->created_at->format('Y-m-d h:m:s');
            $product["is_read"] = $value->is_read;
            array_push($response, $product);
        }
        return Response()->json(['status' => 'success', 'chat' => $response]);
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

    function reopen_dispute (Request $request){
    $dispute_id = $request->dispute_id;
    $complaint_status = $request->complaint_status;
    Dispute::where('id', $dispute_id)->update(['status_id' => $complaint_status]);
   return Response()->json(['status' => 'success', 'message' => 'Complaint Re open successfully']);
    }
}
