<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use App\Models\Report;
use App\Models\Provider;
use App\Models\User;
use App\Models\Company;
use App\Models\Mreport;
use App\Models\Beneficiary;
use \Crypt;

class InvoiceController extends Controller {


    function transaction_receipt ($id){
        $report_id = Crypt::decrypt($id);
        $reports = Report::where('id', $report_id)->whereIn('status_id', [1,2,3,5,6])->first();
        if ($reports){
            $userdetails = User::find($reports->user_id);
            $company_id = $userdetails->company_id;
            $company = Company::where('id', $company_id)->first();
            $data = array(
                'page_title' => 'Receipt',
                'company_name' => $company->company_name,
                'company_email' => $company->company_email,
                'support_number' => $company->support_number,
                'company_address' => $company->company_address,
                'company_website' => $company->company_website,

                'agent_name' => $userdetails->member->shop_name,
                'agent_email' => $userdetails->email,
                'agent_number' => $userdetails->mobile,
                'office_address' => $userdetails->member->office_address,

                'report_id' => $report_id,
                'created_at' => "$reports->created_at",
                'provider_name' => $reports->provider->provider_name,
                'number' => $reports->number,
                'txnid' => $reports->txnid,
                'amount' => number_format($reports->amount, 2),
                'status' => $reports->status->status,
                );
            return view('agent.invoice.transaction_receipt')->with($data);
        }else{
            return Redirect::back();
        }
    }

    function mobile_receipt ($id){
        $report_id = Crypt::decrypt($id);
        $reports = Report::where('id', $report_id)->whereIn('status_id', [1,2,3,5,6])->first();
        if ($reports){
            $userdetails = User::find($reports->user_id);
            $company_id = $userdetails->company_id;
            $company = Company::where('id', $company_id)->first();
            $data = array(
                'page_title' => 'Receipt',
                'company_name' => $company->company_name,
                'company_email' => $company->company_email,
                'support_number' => $company->support_number,
                'company_address' => $company->company_address,
                'company_website' => $company->company_website,

                'agent_name' => $userdetails->member->shop_name,
                'agent_email' => $userdetails->email,
                'agent_number' => $userdetails->mobile,
                'office_address' => $userdetails->member->office_address,

                'report_id' => $report_id,
                'created_at' => "$reports->created_at",
                'provider_name' => $reports->provider->provider_name,
                'number' => $reports->number,
                'txnid' => $reports->txnid,
                'amount' => number_format($reports->amount, 2),
                'status' => $reports->status->status,
            );
            return view('agent.invoice.mobile_receipt')->with($data);
        }else{
            return Redirect::back();
        }
    }

    function money_receipt ($mreportid){
         $mreport = Mreport::find($mreportid);
         if ($mreportid){
             $reports  = Report::where('mreportid', $mreportid)->first();
             $total_amount = Report::where('mreportid', $mreportid)->sum('amount');
             $userdetails = User::find($mreport->user_id);
             $company_id = $userdetails->company_id;
             $company = Company::where('id', $company_id)->first();
             $beneficiary_id = $reports->beneficiary_id;
             $beneficiary = Beneficiary::find($beneficiary_id);
             $data = array(
                 'page_title' => 'Receipt',
                 'company_name' => $company->company_name,
                 'company_email' => $company->company_email,
                 'support_number' => $company->support_number,
                 'company_address' => $company->company_address,
                 'company_website' => $company->company_website,
                 'created_at' => "$reports->created_at",
                 'total_amount' => number_format($total_amount, 2),
                 'agent_name' => $userdetails->member->shop_name,
                 'agent_email' => $userdetails->email,
                 'agent_number' => $userdetails->mobile,
                 'office_address' => $userdetails->member->office_address,

                 // beneficiary details
                 'beneficiary_name' => $beneficiary->name,
                 'account_number' => $beneficiary->account_number,
                 'bank_name' => $beneficiary->bank_name,
                 'ifsc' => $beneficiary->ifsc,
                 'remiter_name' => $beneficiary->remiter_name,
                 'remiter_number' => $beneficiary->remiter_number,
                 'channel' => ($reports->channel == 2) ? 'IMPS' : 'NEFT',
                 'full_amount' => number_format($total_amount, 2),
             );
             $reports = Report::where('mreportid', $mreportid)->get();
             return view('agent.invoice.money_receipt', compact('reports'))->with($data);
         }else{
             return Redirect::back();
         }
    }

    function thermal_printer_receipt ($mreportid){
        $mreport = Mreport::find($mreportid);
        if ($mreportid){
            $reports  = Report::where('mreportid', $mreportid)->first();
            $total_amount = Report::where('mreportid', $mreportid)->sum('amount');
            $userdetails = User::find($mreport->user_id);
            $company_id = $userdetails->company_id;
            $company = Company::where('id', $company_id)->first();
            $beneficiary_id = $reports->beneficiary_id;
            $beneficiary = Beneficiary::find($beneficiary_id);

            $reportsdata = Report::where('mreportid', $mreport->id)->first();
            $data = array(
                'page_title' => 'Receipt',
                'company_name' => $company->company_name,
                'company_email' => $company->company_email,
                'support_number' => $company->support_number,
                'company_address' => $company->company_address,
                'company_website' => $company->company_website,
                'created_at' => "$reports->created_at",
                'total_amount' => number_format($total_amount, 2),
                'agent_name' => $userdetails->member->shop_name,
                'agent_email' => $userdetails->email,
                'agent_number' => $userdetails->mobile,
                'office_address' => $userdetails->member->office_address,
                'service_name' => $reportsdata->provider->service->service_name,

                // beneficiary details
                'beneficiary_name' => $beneficiary->name,
                'account_number' => $beneficiary->account_number,
                'bank_name' => $beneficiary->bank_name,
                'ifsc' => $beneficiary->ifsc,
                'remiter_name' => $beneficiary->remiter_name,
                'remiter_number' => $beneficiary->remiter_number,
                'channel' => ($reports->channel == 2) ? 'IMPS' : 'NEFT',
                'full_amount' => number_format($total_amount, 2),
            );
            $reports = Report::where('mreportid', $mreportid)->get();
            return view('agent.invoice.money_mobile_receipt', compact('reports'))->with($data);
        }else{
            return Redirect::back();
        }
    }


    function invoice (){
        return view('agent.invoice.gst_invoice');
    }
}
