<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Hash;
use App\Models\User;
use App\Models\Report;
use App\Models\Provider;
use App\Library\MemberLibrary;
use App\Models\Commissionreport;
use App\Models\Status;
use App\Models\Beneficiary;
use App\Models\Role;
use App\Models\State;
use App\Models\Service;
use File;

class DownloadController extends Controller
{
    //

    function download_file(Request $request)
    {
       /* $currentTime = date('H', time());
        if ($currentTime > 17 && $currentTime < 22) {
            return Response()->json(['status' => 'failure', 'message' => 'From 6PM to 10PM, you cannot download any data.']);
        }*/
        $rules = array(
            'menu_name' => 'required',
            'password' => 'required',
            'fromdate' => 'required',
            'todate' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $this->delete_all_file();
        $menu_name = $request->menu_name;
        $password = $request->password;
        $fromdate = $request->fromdate;
        $todate = $request->todate;
        $optional1 = $request->optional1;
        $user_id = Auth::id();
        $userdetail = User::find($user_id);
        $current_password = $userdetail->password;
        if (Hash::check($password, $current_password)) {
            $services = Service::where('report_slug', $menu_name)->first();
            if (!empty($services)) {
                if ($services->servicegroup_id == 4) {
                    return Self::DownloadBankingReport($fromdate, $todate, $optional1, $services);
                } elseif ($services->servicegroup_id == 5) {
                    return Self::DownloadAepsReport($fromdate, $todate, $optional1, $services);
                } else {
                    return Self::DownloadOtherReport($fromdate, $todate, $optional1, $services);
                }
            } elseif ($menu_name == 'All Transaction Report') {
                return Self::DownloadAllTransactionReport($fromdate, $todate, $optional1);
            } elseif ($menu_name == 'Pending Report') {
                return Self::DownloadPendingReport($fromdate, $todate);
            } elseif ($menu_name == 'Api Profit Loss Report') {
                return Self::downloadApiProfitLossReport($fromdate, $todate);
            } elseif ($menu_name == 'Debit Report') {
                return Self::downloadDebitReport($fromdate, $todate);
            }elseif ($menu_name == 'Credit Report'){
                return Self::downloadCreditReport($fromdate, $todate);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Password does not match']);
        }
    }

    function DownloadBankingReport($fromdate, $todate, $status_id, $services)
    {
        if ($status_id == 0) {
            $status_id = Status::get(['id']);
        } else {
            $status_id = Status::where('id', $status_id)->get(['id']);
        }
        $user_id = Auth::id();
        $provider_id = Provider::where('service_id', $services->id)->get(['id']);
        $reports = Report::where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('provider_id', $provider_id)
            ->whereIn('status_id', $status_id)
            ->orderBy('id', 'DESC')
            ->get();
        $arr = array();
        foreach ($reports as $value) {
            $beneficiary = Beneficiary::find($value->beneficiary_id);
            $remiter_number = (empty($beneficiary)) ? '' : $beneficiary->remiter_number;
            $bene_name = (empty($beneficiary)) ? '' : $beneficiary->name;
            $bank_name = (empty($beneficiary)) ? '' : $beneficiary->bank_name;
            $payment_mode = ($value->channel == 2) ? 'IMPS' : 'NEFT';
            $data = array(
                $value->id,
                $value->created_at,
                $value->user->name . ' ' . $value->user->last_name,
                $value->provider->provider_name,
                $value->number,
                $remiter_number,
                $bene_name,
                $bank_name,
                $value->txnid,
                $value->amount,
                $value->profit,
                $payment_mode,
                $value->mode,
                $value->ip_address,
                ($value->wallet_type == 1) ? 'Normal' : 'Aeps',
                $value->status->status,
            );
            array_push($arr, $data);
        }
        $delimiter = ",";
        $filename = 'download/' . $services->report_slug . '_' . $user_id . '_' . mt_rand(10, 99) . '.csv';
        $fp = fopen($filename, 'w+');
        $col = ['Report Id', 'Date', 'User', 'Provider', 'Account Number', 'Remiter Number', 'Beneficiary Name', 'Bank Name', 'UTR Number', 'Amount', 'Charges', 'Type', 'Mode', 'Ip Address', 'Wallet', 'Status'];
        fputcsv($fp, $col, $delimiter);
        foreach ($arr as $line) {
            fputcsv($fp, $line, $delimiter);
        }
        fclose($fp);
        $path = url('') . '/' . $filename;
        return Response()->json(['status' => 'success', 'message' => 'success', 'download_link' => $path]);
    }


    function DownloadAepsReport($fromdate, $todate, $optional1, $services)
    {
        $user_id = Auth::id();
        $provider_id = Provider::where('service_id', $services->id)->get(['id']);
        $reports = Report::where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('provider_id', $provider_id)
            ->orderBy('id', 'DESC')
            ->get();
        $arr = array();
        foreach ($reports as $value) {
            $aepsreports = Aepsreport::where('report_id', $value->id)->first();
            $aadhar_number = (empty($aepsreports)) ? '' : $aepsreports->aadhar_number;
            $data = array(
                $value->id,
                $value->created_at,
                $value->user->name . ' ' . $value->user->last_name,
                $value->provider->provider_name,
                $value->number,
                $value->txnid,
                $value->opening_balance,
                $value->amount,
                $value->profit,
                $value->total_balance,
                $value->mode,
                $value->ip_address,
                ($value->wallet_type == 1) ? 'Normal' : 'Aeps',
                $aadhar_number,
                $value->status->status,
            );
            array_push($arr, $data);
        }
        $delimiter = ",";
        $filename = 'download/' . $services->report_slug . '_' . $user_id . '_' . mt_rand(10, 99) . '.csv';
        $fp = fopen($filename, 'w+');
        $col = ['Report Id', 'Date', 'User', 'Provider', 'Number', 'Txnid', 'Opening Balance', 'Amount', 'Profit', 'Closing Balance', 'Mode', 'Ip Address', 'Wallet', 'Aadhar Number', 'Status'];
        fputcsv($fp, $col, $delimiter);
        foreach ($arr as $line) {
            fputcsv($fp, $line, $delimiter);
        }
        fclose($fp);
        $path = url('') . '/' . $filename;
        return Response()->json(['status' => 'success', 'message' => 'success', 'download_link' => $path]);
    }


    function DownloadOtherReport($fromdate, $todate, $status_id, $services)
    {
        $user_id = Auth::id();
        if ($status_id == 0) {
            $status_id = Status::get(['id']);
        } else {
            $status_id = Status::where('id', $status_id)->get(['id']);
        }
        $provider_id = Provider::where('service_id', $services->id)->get(['id']);
        $reports = Report::where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('status_id', $status_id)
            ->whereIn('provider_id', $provider_id)
            ->orderBy('id', 'DESC')
            ->get();
        $arr = array();
        foreach ($reports as $value) {
            $data = array(
                $value->id,
                $value->created_at,
                $value->user->name . ' ' . $value->user->last_name,
                $value->provider->provider_name,
                $value->number,
                $value->txnid,
                $value->opening_balance,
                $value->amount,
                $value->profit,
                $value->total_balance,
                $value->mode,
                $value->ip_address,
                ($value->wallet_type == 1) ? 'Normal' : 'Aeps',
                $value->status->status,
            );
            array_push($arr, $data);
        }
        $delimiter = ",";
        $filename = 'download/' . $services->report_slug . '_' . $user_id . '_' . mt_rand(10, 99) . '.csv';
        $fp = fopen($filename, 'w+');
        $col = ['Report Id', 'Date', 'User', 'Provider', 'Number', 'Txnid', 'Opening Balance', 'Amount', 'Profit', 'Closing Balance', 'Mode', 'Ip Address', 'Wallet', 'Status'];
        fputcsv($fp, $col, $delimiter);
        foreach ($arr as $line) {
            fputcsv($fp, $line, $delimiter);
        }
        fclose($fp);
        $path = url('') . '/' . $filename;
        return Response()->json(['status' => 'success', 'message' => 'success', 'download_link' => $path]);
    }

    function DownloadAllTransactionReport($fromdate, $todate, $statusId)
    {
        $user_id = Auth::id();
        if ($statusId == 0) {
            $status_id = Status::get(['id']);
        } else {
            $status_id = Status::where('id', $statusId)->get(['id']);
        }
        $reports = Report::where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            //->whereIn('status_id', $status_id)
            ->orderBy('id', 'DESC')
            ->get();
        $arr = array();
        foreach ($reports as $value) {
            $data = array(
                $value->id,
                $value->created_at,
                $value->user->name . ' ' . $value->user->last_name,
                $value->provider->provider_name,
                $value->number,
                $value->txnid,
                $value->opening_balance,
                $value->amount,
                $value->profit,
                $value->total_balance,
                $value->mode,
                $value->ip_address,
                ($value->wallet_type == 1) ? 'Normal' : 'Aeps',
                $value->status->status,
                $value->reason,
                $value->client_id,
            );
            array_push($arr, $data);
        }
        $delimiter = ",";
        $filename = 'download/all-transaction-report' . $user_id . '_' . mt_rand(10, 99) . '.csv';
        $fp = fopen($filename, 'w+');
        $col = ['Report Id', 'Date', 'User', 'Provider', 'Number', 'Txnid', 'Opening Balance', 'Amount', 'Profit', 'Closing Balance', 'Mode', 'Ip Address', 'Wallet', 'Status', 'Failure Reason', 'Client ID'];
        fputcsv($fp, $col, $delimiter);
        foreach ($arr as $line) {
            fputcsv($fp, $line, $delimiter);
        }
        fclose($fp);
        $path = url('') . '/' . $filename;
        return Response()->json(['status' => 'success', 'message' => 'success', 'download_link' => $path]);
    }


    function delete_all_file()
    {
        $destinationPath = 'download';
        File::cleanDirectory($destinationPath);
    }
}
