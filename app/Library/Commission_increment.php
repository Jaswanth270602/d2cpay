<?php

namespace App\library {
    use App\Models\User;
    use App\Models\Commission;
    use App\Models\Report;
    use App\Models\Balance;
    use App\Models\Commissionreport;
    use App\Models\Moneycommission;
    use App\Models\Aepscommission;
    use App\Models\Aadharpaycommission;
    use App\Models\Payoutcommission;
    use App\Models\Sitesetting;
    use App\Models\Apicommreport;
    use Helpers;
    use App\Library\SmsLibrary;

    class Commission_increment {

        public function __construct()   {
           /* $this->company_id = Helpers::company_id()->id;
            $companies = Helpers::company_id();*/
            $this->company_id = 1;
            $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
            if ($sitesettings){
                $this->brand_name = $sitesettings->brand_name;
            }else{
                $this->brand_name = "";
            }
        }

        function parent_recharge_commission ($user_id, $number, $insert_id, $provider_id,$amount, $api_id, $retailer, $distributor, $sdistributor, $sales_team, $referral){
            $checkretailer = User::where('id', $user_id)->first();
            $label1_id = $checkretailer->parent_id;
            // get label 1 user details
            $checklabel1 = User::where('id', $label1_id)->first();
            $label2_id = $checklabel1->parent_id;
            $label1_role = $checklabel1->role_id;
            if ($label1_role == 8) {
                $final_comm = $referral;
                Commissionreport::where('report_id', $insert_id)->update(['referral' => $final_comm]);
                $this->update_commission($user_id, $label1_id, $final_comm, $insert_id, $number, $provider_id, $api_id);
            } elseif ($label1_role == 7) {
                $final_comm = $distributor;
                Commissionreport::where('report_id', $insert_id)->update(['distributor_comm' => $final_comm]);
                $this->update_commission($user_id, $label1_id, $final_comm, $insert_id, $number, $provider_id, $api_id);
            }elseif ($label1_role == 6) {
                $final_comm = $sdistributor;
                Commissionreport::where('report_id', $insert_id)->update(['super_distributor_comm' => $final_comm]);
                $this->update_commission($user_id, $label1_id, $final_comm, $insert_id, $number, $provider_id, $api_id);
            } elseif ($label1_role == 5) {
                $final_comm = $sales_team;
                Commissionreport::where('report_id', $insert_id)->update(['sales_team_comm' => $final_comm]);
                $this->update_commission($user_id, $label1_id, $final_comm, $insert_id, $number, $provider_id, $api_id);
            }

            // label 2 details
            $checklabel2 = User::where('id', $label2_id)->first();
            if ($checklabel2) {
                $label3_id = $checklabel2->parent_id;
                $label2_role = $checklabel2->role_id;
                if ($label2_role == 7) {
                    $final_comm = $distributor;
                    Commissionreport::where('report_id', $insert_id)->update(['distributor_comm' => $final_comm]);
                    $this->update_commission($user_id, $label2_id, $final_comm, $insert_id, $number, $provider_id, $api_id);
                } elseif ($label2_role == 6) {
                    $final_comm = $sdistributor;
                    Commissionreport::where('report_id', $insert_id)->update(['super_distributor_comm' => $final_comm]);
                    $this->update_commission($user_id, $label2_id, $final_comm, $insert_id, $number, $provider_id, $api_id);
                } elseif ($label2_role == 5) {
                    $final_comm = $sales_team;
                    Commissionreport::where('report_id', $insert_id)->update(['sales_team_comm' => $final_comm]);
                    $this->update_commission($user_id, $label2_id, $final_comm, $insert_id, $number, $provider_id, $api_id);
                }

            }
            // label 3 details
            if (!empty($label3_id)) {
                $checklabel3 = User::where('id', $label3_id)->first();
                if ($checklabel3) {
                    $label4_id = $checklabel3->parent_id;
                    $label3_role = $checklabel3->role_id;
                    if ($label3_role == 6) {
                        $final_comm = $sdistributor;
                        Commissionreport::where('report_id', $insert_id)->update(['super_distributor_comm' => $final_comm]);
                        $this->update_commission($user_id, $label3_id, $final_comm, $insert_id, $number, $provider_id, $api_id);
                    }elseif ($label3_role == 5){
                        $final_comm = $sales_team;
                        Commissionreport::where('report_id', $insert_id)->update(['sales_team_comm' => $final_comm]);
                        $this->update_commission($user_id, $label3_id, $final_comm, $insert_id, $number, $provider_id, $api_id);
                    }
                }
            }

            // label 5 details
            if (!empty($label4_id)) {
                $checklabel4 = User::where('id', $label4_id)->first();
                if ($checklabel4) {
                    $label5_id = $checklabel4->parent_id;
                    $label4_role = $checklabel4->role_id;
                    if ($label4_role == 5){
                        $final_comm = $sales_team;
                        Commissionreport::where('report_id', $insert_id)->update(['sales_team_comm' => $final_comm]);
                        $this->update_commission($user_id, $label4_id, $final_comm, $insert_id, $number, $provider_id, $api_id);
                    }
                }
            }

        }

        function update_commission($user_id, $label1_id, $final_comm, $insert_id, $number, $provider, $api_id){
            if ($final_comm == 0){

            }else{
                $checktxnid = Report::where('user_id', $label1_id)->where('txnid', $insert_id)->first();
                if (empty($checktxnid)){
                    $getuser = User::where('id', $label1_id)->first();
                    if($getuser->role_id == 2){
                        $remark = "profit";
                        Report::where('id', $insert_id)->update(['company_staff' => $final_comm]);
                    }elseif ($getuser->role_id == 3){
                        $remark = "profit";
                        Report::where('id', $insert_id)->update(['white_label_reseller_comm' => $final_comm]);
                    }elseif ($getuser->role_id == 4){
                        $remark = "profit";
                        Report::where('id', $insert_id)->update(['white_label_comm' => $final_comm]);
                    }elseif ($getuser->role_id == 5){
                        $remark = "profit";
                        Report::where('id', $insert_id)->update(['sales_team_comm' => $final_comm]);
                    }elseif ($getuser->role_id == 6){
                        $remark = "profit";
                        Report::where('id', $insert_id)->update(['super_distributor_comm' => $final_comm]);
                    }elseif ($getuser->role_id == 7){
                        $remark = "profit";
                        Report::where('id', $insert_id)->update(['distributor_comm' => $final_comm]);
                    }elseif ($getuser->role_id == 8 || $getuser->role_id == 9 || $getuser->role_id == 10){
                        $remark = "refer and earn";
                        Report::where('id', $insert_id)->update(['referral_comm' => $final_comm]);
                    }
                    $reports = Report::find($insert_id);
                    $opening_balance = $getuser->balance->user_balance;
                    $name = $getuser->name;
                    $description = 'Profit by ' . $name;
                    Balance::where('user_id', $label1_id)->increment('user_balance', $final_comm);
                    $now = new \DateTime();
                    $ctime = $now->format('Y-m-d H:i:s');
                    $getdistributor = Balance::where('user_id', $label1_id)->first();
                    $user_balance = $getdistributor->user_balance;
                    Report::insertGetId([
                        'number' => $number,
                        'provider_id' => $provider,
                        'amount' => ($getuser->role_id == 8 || $getuser->role_id == 9 || $getuser->role_id == 10) ? $final_comm : 0,
                        'profit' => ($getuser->role_id == 8 || $getuser->role_id == 9 || $getuser->role_id == 10) ? 0 : $final_comm,
                        'api_id' => $api_id,
                        'status_id' => 6,
                        'txnid' => $insert_id,
                        'description' => $description,
                        'created_at' => $ctime,
                        'remark' => $remark,
                        'user_id' => $label1_id,
                        'total_balance' => $user_balance,
                        'opening_balance' => $opening_balance,
                        'mode' => $reports->mode,
                        'wallet_type' => 1,
                    ]);
                    $message = "Dear $getuser->name $getuser->last_name, Your Team commission credit your wallet : $final_comm your current balance : $user_balance - Join new member & increase your profit thanks $this->brand_name";
                    $template_id = 16;
                    $library = new SmsLibrary();
                    $library->send_sms($getuser->mobile, $message, $template_id);
                }
            }
        }

        function updateApiComm($user_id, $provider_id, $api_id, $amount, $retailer, $d, $sd, $st, $rf, $apiCommission, $insert_id, $commissionType)
        {
            $apicommreports = Apicommreport::where('report_id', $insert_id)->first();
            if ($apicommreports) {
                return Response()->json(['status' => 'failure', 'message' => 'already update!']);
            }
            $reports = Report::find($insert_id);
            if ($commissionType == 'commission') {
                $totalExpenses = $retailer + $d + $sd + $st + $rf;
                $totalProfit = $apiCommission - $totalExpenses;
                $retailerComm = $retailer;
                $retailerCharge = 0;
                $apiCommissions = $apiCommission;
                $apiCharge = 0;
            } else {
                $totalExpenses = $apiCommission + $d + $sd + $st + $rf;
                $totalProfit = $retailer - $totalExpenses;
                $retailerComm = 0;
                $retailerCharge = $retailer;
                $apiCommissions = 0;
                $apiCharge = $apiCommission;
            }
            Apicommreport::insert([
                'user_id' => $user_id,
                'provider_id' => $provider_id,
                'api_id' => $api_id,
                'amount' => $amount,
                'retailerComm' => $retailerComm,
                'retailerCharge' => $retailerCharge,
                'apiCommission' => $apiCommissions,
                'apiCharge' => $apiCharge,
                'totalProfit' => $totalProfit,
                'report_id' => $insert_id,
                'status_id' => 1,
                'd' => $d,
                'sd' => $sd,
                'st' => $st,
                'rf' => $rf,
                'created_at' => $reports->created_at,
            ]);
        }


    }
}