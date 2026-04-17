<?php

namespace App\library {
    use App\Numberdata;
    use App\Circleprovider;
    use App\Backupapi;
    use App\Provider;
    use App\User;
    use App\Report;
    use App\Providerlimit;
    use App\Service;
    use DB;
    use Auth;
    use App\Library\GetcommissionLibrary;
    use App\Notifications\DatabseNotification;
    use Notification;
    use App\Denomination;
    use App\State;
    use App\Apicheckbalance;
    use App\Profile;
    use Mail;
    use Helpers;
    use App\Sitesetting;
    use Carbon\Carbon;
    use Maatwebsite\Excel\Facades\Excel;
    use App\Exports\ChildstatementExport;

    class AlermailLibrary {

        public function __construct()   {
            $this->company_id = 1;

            $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
            if ($sitesettings){
                $this->brand_name = $sitesettings->brand_name;
                $this->mail_from = $sitesettings->mail_from;
            }else{
                $this->brand_name = '';
                $this->mail_from = '';
            }
        }

        function send_day_book (){
            $user_id = Profile::where('day_book', 1)->get(['user_id']);
            $userLoop = User::whereIn('id', $user_id)->get();
            foreach ($userLoop as $value){
                $now = Carbon::now();
                $yesterday = Carbon::yesterday();
                $fromdate = $yesterday->format('Y-m-d');
                $todate = $yesterday->format('Y-m-d');

                // opening balance
                $openingbalance = Report::where('user_id', $value->id)
                    ->whereDate('created_at',  '<', $fromdate)
                    ->where('wallet_type', 1)
                    ->orderBy('id', 'DESC')
                    ->first();
                if ($openingbalance){
                    $opening_bal = number_format($openingbalance->total_balance,2);
                }else{
                    $opening_bal = 0;
                }

                // credit amount
                $credit = Report::where('user_id', $value->id)
                    ->whereDate('created_at', '>=', $fromdate)
                    ->whereDate('created_at', '<=', $todate)
                    ->where('wallet_type', 1)
                    ->where('status_id', 6)
                    ->sum('amount');

                // debit amout
                $debit = Report::where('user_id', $value->id)
                    ->whereDate('created_at', '>=', $fromdate)
                    ->whereDate('created_at', '<=', $todate)
                    ->where('wallet_type', 1)
                    ->where('status_id', 7)
                    ->sum('amount');

                // sales
                $sales = Report::where('user_id', $value->id)
                    ->whereDate('created_at', '>=', $fromdate)
                    ->whereDate('created_at', '<=', $todate)
                    ->where('wallet_type', 1)
                    ->where('status_id', 1)
                    ->sum('amount');

                // profit
                $profit = Report::where('user_id', $value->id)
                    ->whereDate('created_at', '>=', $fromdate)
                    ->whereDate('created_at', '<=', $todate)
                    ->where('wallet_type', 1)
                    ->where('profit', '>', 0)
                    ->where('status_id', 1)
                    ->sum('profit');

                // chages
                $charges = Report::where('user_id', $value->id)
                    ->whereDate('created_at', '>=', $fromdate)
                    ->whereDate('created_at', '<=', $todate)
                    ->where('wallet_type', 1)
                    ->where('profit', '<', 0)
                    ->where('status_id', 1)
                    ->sum('profit');

                $pending = Report::where('user_id', $value->id)
                    ->whereDate('created_at', '>=', $fromdate)
                    ->whereDate('created_at', '<=', $todate)
                    ->where('wallet_type', 1)
                    ->where('status_id', 3)
                    ->sum('amount');


                // closing balance
                $closing_balance = Report::where('user_id', $value->id)
                    ->whereDate('created_at', '>=', $fromdate)
                    ->whereDate('created_at', '<=', $todate)
                    ->where('wallet_type', 1)
                    ->orderBy('id', 'DESC')
                    ->first();
                if ($closing_balance){
                    $cl_bal = number_format($closing_balance->total_balance,2);
                }else{
                    $cl_bal = $value->balance->user_balance;
                }


                $userDetails = User::find($value->id);
                $data = array(
                    'customer_name' => $userDetails->name.' '.$userDetails->last_name,
                    'company_name' => $this->brand_name,
                    'company_logo' => $userDetails->company->company_logo,
                    'support_number' => $userDetails->company->support_number,
                    'company_address' => $userDetails->company->company_address,
                    'subject' => 'Your '.$this->brand_name .' day book for period '.$fromdate.' to '.$todate.'',

                    'daybook_opening_bal' => $opening_bal,
                    'daybook_credit' => $credit,
                    'daybook_debit' => $debit,
                    'daybook_sales' => $sales,
                    'daybook_profit' => $profit,
                    'daybook_charges' => $charges,
                    'daybook_pending' => $pending,
                    'daybook_cl_bal' => $cl_bal,
                );
                Mail::send('mail.daybook', $data, function ($m) use ($userDetails, $data) {
                    $m->to($userDetails['email'], $data['customer_name'])->subject($data['subject']);
                    $m->from($this->mail_from, $data['company_name']);
                });
            }
        }

        function send_statement (){
            $user_id = Profile::where('monthly_statement', 1)->get(['user_id']);
            $users = User::whereIn('id', $user_id)->whereIn('role_id', [8,9,10])->get();
            $yesterday = Carbon::yesterday();
            $fromdate = $yesterday->format('Y-m-d');
            $todate = $yesterday->format('Y-m-d');
            sleep(1);
            foreach ($users as $value){
                $excelFile = Excel::raw(new ChildstatementExport($value->id, $fromdate, $todate), \Maatwebsite\Excel\Excel::XLSX);
                $chilDetails = User::find($value->id);
                $data["email"] = $chilDetails->email;
                $data["subject"] = 'Your '.$this->brand_name . ' statement for period '.$fromdate.' to '.$todate.' (Excel)';
                $data["customer_name"] = $chilDetails->name.' '.$chilDetails->last_name;
                $data["body"] = 'We trust that your experience of using your '.$this->brand_name .' service has been enjoyable. We are pleased to provide you with a summary of the '.$this->brand_name .' service Account statement.';
                $data["file_name"] = 'statement '.$fromdate.' to '.$todate.'.xlsx';
                $data["brand_name"] = $this->brand_name;
                Mail::send('mail.statement_body', $data, function($message)use($data, $excelFile) {
                    $message->to($data["email"], $data["email"])
                        ->subject($data["subject"])
                        ->attachData($excelFile,  $data["file_name"]);
                    $message->from($this->mail_from, $data["brand_name"]);

                });
            }
        }


    }
}