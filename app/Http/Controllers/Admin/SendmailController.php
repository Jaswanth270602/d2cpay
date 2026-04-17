<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use DB;
use \Crypt;
use App\User;
use Hash;
use PDF;
use Mail;
use Helpers;
use App\Sitesetting;
use App\Library\MemberLibrary;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ChildstatementExport;

class SendmailController extends Controller
{

    public function __construct()   {
        $this->company_id = Helpers::company_id()->id;
        $companies = Helpers::company_id();
        $this->company_id = $companies->id;
        $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
        if ($sitesettings){
            $this->brand_name = $sitesettings->brand_name;
            $this->mail_from = $sitesettings->mail_from;
        }else{
            $this->brand_name = "";
            $this->mail_from = "";
        }
    }

    function send_statement (Request $request){
        $rules = array(
            'wallet_type' => 'required',
            'encrypt_id' => 'required',
            'fromdate' => 'required',
            'todate' => 'required',
            'format_type' => 'required',
            'password' => 'required',
            'mailMessage' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        DB::beginTransaction();
        try{
            $mailMessage = $request->mailMessage;
            $wallet_type = $request->wallet_type;
            $child_id = Crypt::decrypt($request->encrypt_id);
            $fromdate = $request->fromdate;
            $todate = $request->todate;
            $format_type = $request->format_type;
            $password = $request->password;
            $userDetails = User::find(Auth::id());
            $current_password = $userDetails->password;
            if (Hash::check($password, $current_password)) {
                //  normal wallet
                if ($format_type == 1){
                    return Self::send_pdf_file($child_id, $fromdate, $todate, $wallet_type, $mailMessage);
                }
                // aeps wallet
                elseif ($format_type == 2){
                    return Self::send_excel_file($child_id, $fromdate, $todate, $wallet_type, $mailMessage);
                }else{
                    return response()->json(['status' => 'failure', 'message' => 'Invalid Format Type']);
                }

            }else{
                return response()->json(['status' => 'failure', 'message' => 'Invalid Password']);
            }
            DB::commit();
        }catch (\Exception $ex) {
            DB::rollback();
             throw $ex;
            return response()->json(['status' => 'failure', 'message' => 'something went wrong']);
        }
    }

    function send_pdf_file ($child_id, $fromdate, $todate, $wallet_type, $mailMessage){
        return response()->json(['status' => 'success', 'message' => 'exit']);
        $chilDetails = User::find($child_id);
        $data["email"] = $chilDetails->email;
        $data["subject"] = 'Your '.$this->brand_name .' statement for period '.$fromdate.' to '.$todate.' (PDF)';
        $data["customer_name"] = $chilDetails->name.' '.$chilDetails->last_name;
        $data["body"] = 'We trust that your experience of using your '.$this->brand_name .' service has been enjoyable. We are pleased to provide you with a summary of the '.$this->brand_name .' service Account statement.';
        $data["file_name"] = 'statement '.$fromdate.' to '.$todate.'.pdf';
        $data["brand_name"] = $this->brand_name;
        $pdf = PDF::loadView('mail.statement_pdf', $data);
        Mail::send('mail.statement_body', $data, function($message)use($data, $pdf) {
            $message->to($data["email"], $data["email"])
                ->subject($data["subject"])
                ->attachData($pdf->output(),  $data["file_name"]);
        });
        return response()->json(['status' => 'success', 'message' => 'successful..!']);
    }

    function send_excel_file ($child_id, $fromdate, $todate, $wallet_type, $mailMessage){
        $excelFile = Excel::raw(new ChildstatementExport($child_id, $fromdate, $todate), \Maatwebsite\Excel\Excel::XLSX);
        $chilDetails = User::find($child_id);
        $data["email"] = $chilDetails->email;
        $data["subject"] = 'Your '.$this->brand_name . ' statement for period '.$fromdate.' to '.$todate.' (Excel)';
        $data["customer_name"] = $chilDetails->name.' '.$chilDetails->last_name;
        $data["body"] = $mailMessage;
        $data["file_name"] = 'statement '.$fromdate.' to '.$todate.'.xlsx';
        $data["brand_name"] = $this->brand_name;
        Mail::send('mail.statement_body', $data, function($message)use($data, $excelFile) {
            $message->to($data["email"], $data["email"])
                ->subject($data["subject"])
                ->attachData($excelFile,  $data["file_name"]);
            $message->from($this->mail_from, $data["brand_name"]);

        });
        if( count(Mail::failures()) > 0 ) {

            ///echo "There was one or more failures. They were: <br />";

            foreach(Mail::failures as $email_address) {
                return response()->json(['status' => 'success', 'message' => $email_address]);
            }

        } else {
            return response()->json(['status' => 'success', 'message' => 'successful..!']);
        }

    }



}
