<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Hash;
use App\Models\User;
use App\Models\Report;
use App\Models\Provider;
use App\Models\Commissionreport;
use App\Models\Status;
use App\Models\Beneficiary;
use App\Models\Role;
use App\Models\State;
use App\Models\Loadcash;
use File;
use Helpers;
use \Crypt;
use App\Models\Sitesetting;
use App\Models\Api;
use App\Models\Aepsreport;
use App\Models\Purchase;
use App\Models\Agentonboarding;
use App\Models\Service;
use App\Models\Apicommreport;
use App\Library\MemberLibrary;
use App\Library\BasicLibrary;


class DownloadController extends Controller
{


    public function __construct()
    {
        $this->company_id = Helpers::company_id()->id;
        $companies = Helpers::company_id();
        $this->company_id = $companies->id;
        $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
        $this->brand_name = (empty($sitesettings)) ? '' : $sitesettings->brand_name;
    }


    function download_file(Request $request)
    {
        /*  $currentTime = date('H', time());
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
        $fromdate = $request->fromdate;
        $todate = $request->todate;
        $password = $request->password;
        $optional1 = $request->download_optional1;
        $optional2 = $request->download_optional2;
        $optional3 = $request->download_optional3;
        $optional4 = $request->download_optional4;
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
                return Self::DownloadAllTransactionReport($fromdate, $todate, $optional1, $optional2, $optional3, $optional4);
            } elseif ($menu_name == 'Pending Report') {
                return Self::DownloadPendingReport($fromdate, $todate);
            } elseif ($menu_name == 'Api Profit Loss Report') {
                return Self::downloadApiProfitLossReport($fromdate, $todate);
            } elseif ($menu_name == 'Debit Report') {
                return Self::downloadDebitReport($fromdate, $todate);
            } elseif ($menu_name == 'Credit Report') {
                return Self::downloadCreditReport($fromdate, $todate);
            } elseif ($menu_name == 'Download User Ledger Report') {
                $child_id = Crypt::decrypt($optional2);
                return Self::DownloadUserLedgerReport($fromdate, $todate, $optional1, $child_id);
            } elseif ($menu_name == 'Purchase Balance') {
                return Self::downloadPurchaseBalance($fromdate, $todate);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Something went wrong!']);
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
        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);
        $provider_id = Provider::where('service_id', $services->id)->get(['id']);
        $reports = Report::whereIn('user_id', $my_down_member)
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
                ($value->wallet_type == 1) ? 'Payout' : 'Payin',
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
        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);
        $provider_id = Provider::where('service_id', $services->id)->get(['id']);
        $reports = Report::whereIn('user_id', $my_down_member)
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
                ($value->wallet_type == 1) ? 'Payout' : 'Payin',
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
        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);
        if ($status_id == 0) {
            $status_id = Status::get(['id']);
        } else {
            $status_id = Status::where('id', $status_id)->get(['id']);
        }
        $provider_id = Provider::where('service_id', $services->id)->get(['id']);
        $reports = Report::whereIn('user_id', $my_down_member)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('status_id', $status_id)
            ->whereIn('provider_id', $provider_id)
            ->orderBy('id', 'DESC')
            ->get();
        $arr = array();
        foreach ($reports as $value) {
            if (Auth::User()->role_id == 1) {
                $apis = Api::find($value->api_id);
                $vendor = (empty($apis)) ? $this->brand_name : $apis->api_name;
            } else {
                $vendor = $this->brand_name;
            }
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
                ($value->wallet_type == 1) ? 'Payout' : 'Payin',
                $value->status->status,
                $vendor,
            );
            array_push($arr, $data);
        }
        $delimiter = ",";
        $filename = 'download/' . $services->report_slug . '_' . $user_id . '_' . mt_rand(10, 99) . '.csv';
        $fp = fopen($filename, 'w+');
        $col = ['Report Id', 'Date', 'User', 'Provider', 'Number', 'Txnid', 'Opening Balance', 'Amount', 'Profit', 'Closing Balance', 'Mode', 'Ip Address', 'Wallet', 'Status', 'Vendor'];
        fputcsv($fp, $col, $delimiter);
        foreach ($arr as $line) {
            fputcsv($fp, $line, $delimiter);
        }
        fclose($fp);
        $path = url('') . '/' . $filename;
        return Response()->json(['status' => 'success', 'message' => 'success', 'download_link' => $path]);
    }

    function DownloadAllTransactionReport($fromdate, $todate, $statusId, $childId, $providerId, $apiId)
    {
        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);
        if ($statusId == 0) {
            $status_id = Status::get(['id']);
        } else {
            $status_id = Status::where('id', $statusId)->get(['id']);
        }
        if ($childId == 0) {
            $child_id = User::whereIn('id', $my_down_member)->get(['id']);
        } else {
            $child_id = User::whereIn('id', $my_down_member)->where('id', $childId)->get(['id']);
        }
        if ($providerId == 0) {
            $provider_id = Provider::get(['id']);
        } else {
            $provider_id = Provider::where('id', $providerId)->get(['id']);
        }
        if ($apiId == 0) {
            $api_id = Api::get(['id']);
        } else {
            $api_id = Api::where('id', $apiId)->get(['id']);
        }
        $reports = Report::whereIn('user_id', $my_down_member)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('status_id', $status_id)
            ->whereIn('user_id', $child_id)
            ->whereIn('provider_id', $provider_id)
            ->whereIn('api_id', $api_id)
            ->orderBy('id', 'DESC')
            ->get();
        $arr = array();
        foreach ($reports as $value) {
            if (Auth::User()->role_id == 1) {
                $apis = Api::find($value->api_id);
                $vendor = (empty($apis)) ? $this->brand_name : $apis->api_name;
            } else {
                $vendor = $this->brand_name;
            }
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
                ($value->wallet_type == 1) ? 'Payout' : 'Payin',
                $value->status->status,
                $vendor,
            );
            array_push($arr, $data);
        }
        $delimiter = ",";
        $filename = 'download/all-transaction-report' . $user_id . '_' . mt_rand(10, 99) . '.csv';
        $fp = fopen($filename, 'w+');
        $col = ['Report Id', 'Date', 'User', 'Provider', 'Number', 'Txnid', 'Opening Balance', 'Amount', 'Profit', 'Closing Balance', 'Mode', 'Ip Address', 'Wallet', 'Status', 'Vendor'];
        fputcsv($fp, $col, $delimiter);
        foreach ($arr as $line) {
            fputcsv($fp, $line, $delimiter);
        }
        fclose($fp);
        $path = url('') . '/' . $filename;
        return Response()->json(['status' => 'success', 'message' => 'success', 'download_link' => $path]);
    }

    function DownloadPendingReport($fromdate, $todate)
    {
        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);
        $reports = Report::whereIn('user_id', $my_down_member)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('status_id', [3])
            ->orderBy('id', 'DESC')
            ->get();
        $arr = array();
        foreach ($reports as $value) {
            if (Auth::User()->role_id == 1) {
                $apis = Api::find($value->api_id);
                $vendor = (empty($apis)) ? $this->brand_name : $apis->api_name;
            } else {
                $vendor = $this->brand_name;
            }
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
                ($value->wallet_type == 1) ? 'Payout' : 'Payin',
                $value->status->status,
                $vendor,
            );
            array_push($arr, $data);
        }
        $delimiter = ",";
        $filename = 'download/pending-report' . $user_id . '_' . mt_rand(10, 99) . '.csv';
        $fp = fopen($filename, 'w+');
        $col = ['Report Id', 'Date', 'User', 'Provider', 'Number', 'Txnid', 'Opening Balance', 'Amount', 'Profit', 'Closing Balance', 'Mode', 'Ip Address', 'Wallet', 'Status', 'Vendor'];
        fputcsv($fp, $col, $delimiter);
        foreach ($arr as $line) {
            fputcsv($fp, $line, $delimiter);
        }
        fclose($fp);
        $path = url('') . '/' . $filename;
        return Response()->json(['status' => 'success', 'message' => 'success', 'download_link' => $path]);
    }

    function downloadApiProfitLossReport($fromdate, $todate)
    {
        if (Auth::User()->role_id == 1) {
            $user_id = Auth::id();
            $reports = Apicommreport::where('status_id', 1)
                ->groupBy('api_id')
                ->selectRaw('*, sum(amount) as amount, sum(apiCharge) as apiCharge, sum(apiCommission) as apiCommission, sum(retailerCharge) as retailerCharge, sum(retailerComm) as retailerComm, sum(totalProfit) as totalProfit')
                ->orderBy('id', 'DESC')
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->whereNotIn('api_id', [0])
                ->get();
            $arr = array();
            foreach ($reports as $value) {
                $data = array(
                    $value->api->api_name,
                    $value->amount,
                    $value->apiCharge,
                    $value->apiCommission,
                    $value->retailerCharge,
                    $value->retailerComm,
                    $value->totalProfit,
                );
                array_push($arr, $data);
            }
            $delimiter = ",";
            $filename = 'download/api-profit-loss-report' . $user_id . '_' . mt_rand(10, 99) . '.csv';
            $fp = fopen($filename, 'w+');
            $col = ['Api Name', 'Amount', 'Api Charges', 'Api Commission', 'Our Charges', 'Our Commission', 'Net Profit'];
            fputcsv($fp, $col, $delimiter);
            foreach ($arr as $line) {
                fputcsv($fp, $line, $delimiter);
            }
            fclose($fp);
            $path = url('') . '/' . $filename;
            return Response()->json(['status' => 'success', 'message' => 'success', 'download_link' => $path]);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function downloadDebitReport($fromdate, $todate)
    {
        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);
        $reports = Report::whereIn('user_id', [Auth::id()])
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('status_id', 7)
            ->whereNotIn('remark', ['profit'])
            ->orderBy('id', 'DESC')
            ->get();
        $arr = array();
        foreach ($reports as $value) {
            $users = User::find($value->credit_by);
            $transfer_to = ($users) ? $users->name . ' ' . $users->last_name : '';
            $data = array(
                $value->id,
                $value->created_at,
                $value->user->name . ' ' . $value->user->last_name,
                $transfer_to,
                $value->provider->provider_name,
                $value->number,
                $value->txnid,
                $value->amount,
                $value->total_balance,
                $value->status->status,
            );
            array_push($arr, $data);
        }
        $delimiter = ",";
        $filename = 'download/debit_report_' . $user_id . '_' . mt_rand(10, 99) . '.csv';
        $fp = fopen($filename, 'w+');
        $col = ['Report Id', 'Date', 'User', 'Transfer To', 'Provider', 'Number', 'Txnid', 'Amount', 'Balance', 'Status'];
        fputcsv($fp, $col, $delimiter);
        foreach ($arr as $line) {
            fputcsv($fp, $line, $delimiter);
        }
        fclose($fp);
        $path = url('') . '/' . $filename;
        return Response()->json(['status' => 'success', 'message' => 'success', 'download_link' => $path]);
    }

    function downloadCreditReport($fromdate, $todate)
    {
        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);
        $reports = Report::whereIn('user_id', [Auth::id()])
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('status_id', 6)
            ->whereNotIn('remark', ['profit'])
            ->orderBy('id', 'DESC')
            ->get();
        $arr = array();
        foreach ($reports as $value) {
            $users = User::find($value->credit_by);
            $transfer_to = ($users) ? $users->name . ' ' . $users->last_name : '';
            $data = array(
                $value->id,
                $value->created_at,
                $value->user->name . ' ' . $value->user->last_name,
                $transfer_to,
                $value->provider->provider_name,
                $value->number,
                $value->txnid,
                $value->amount,
                $value->total_balance,
                $value->status->status,
            );
            array_push($arr, $data);
        }
        $delimiter = ",";
        $filename = 'download/credit_report_' . $user_id . '_' . mt_rand(10, 99) . '.csv';
        $fp = fopen($filename, 'w+');
        $col = ['Report Id', 'Date', 'User', 'Transfer By', 'Provider', 'Number', 'Txnid', 'Amount', 'Balance', 'Status'];
        fputcsv($fp, $col, $delimiter);
        foreach ($arr as $line) {
            fputcsv($fp, $line, $delimiter);
        }
        fclose($fp);
        $path = url('') . '/' . $filename;
        return Response()->json(['status' => 'success', 'message' => 'success', 'download_link' => $path]);
    }

    function member_download(Request $request)
    {
        $rules = array(
            'menu_name' => 'required',
            'password' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $this->delete_all_file();
        $menu_name = $request->menu_name;
        $password = $request->password;
        $user_id = Auth::id();
        $userdetail = User::find($user_id);
        $current_password = $userdetail->password;
        if (Hash::check($password, $current_password)) {
            $roles = Role::where('role_title', $menu_name)->first();
            if ($roles) {
                $role_id = Auth::User()->role_id;
                $company_id = Auth::User()->company_id;
                $user_id = Auth::id();
                $library = new MemberLibrary();
                $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);
                $users = User::where('role_id', $roles->id)->whereIn('id', $my_down_member)->get();
                $arr = array();
                foreach ($users as $value) {
                    $parent_details = User::where('id', $value->parent_id)->first();
                    $parent_name = ($parent_details) ? $parent_details->name . ' ' . $parent_details->last_name : '';

                    $permanents = State::find($value->member->state_id);
                    $state_name = ($permanents) ? $permanents->name : '';
                    $data = array(
                        $value->id,
                        $value->created_at,
                        $value->name,
                        $value->last_name,
                        $value->mobile,
                        $value->email,
                        $value->member->shop_name,
                        $value->role->role_title,
                        number_format($value->balance->user_balance, 2),
                        number_format($value->balance->aeps_balance, 2),
                        $parent_name,
                        $value->member->address,
                        $value->member->city,
                        $state_name,
                        $value->member->pin_code,
                        $value->member->office_address,
                        ($value->profile->recharge == 1) ? 'Active' : 'De Active',
                        ($value->profile->money == 1) ? 'Active' : 'De Active',
                        ($value->profile->aeps == 1) ? 'Active' : 'De Active',
                        ($value->profile->payout == 1) ? 'Active' : 'De Active',
                        ($value->profile->pancard == 1) ? 'Active' : 'De Active',
                        ($value->profile->giftcard == 1) ? 'Active' : 'De Active',
                    );
                    array_push($arr, $data);
                }
                $delimiter = ",";
                $filename = 'download/' . $menu_name . '_' . $user_id . '_' . mt_rand(10, 99) . '.csv';
                $fp = fopen($filename, 'w+');
                $col = ['User Id', 'Joining Date', 'First Name', 'Last Name', 'Mobile', 'Email Id', 'Shop Name', 'Member Type', 'Normal Balance', 'Aeps Balance', 'Parent Name', 'Address', 'City', 'State', 'Pincode', 'Office Address', 'Recharge', 'Money', 'Aeps', 'Payout', 'Pancard', 'Giftcard'];

                fputcsv($fp, $col, $delimiter);
                foreach ($arr as $line) {
                    fputcsv($fp, $line, $delimiter);
                }
                fclose($fp);
                $path = url('') . '/' . $filename;
                return Response()->json(['status' => 'success', 'message' => 'success', 'download_link' => $path]);

            } else {
                return Response()->json(['status' => 'failure', 'message' => "sorry you can't download this file at this time"]);
            }

        } else {
            return Response()->json(['status' => 'failure', 'message' => 'password does not match']);
        }
    }

    function payment_request_view(Request $request)
    {
        $rules = array(
            'menu_name' => 'required',
            'password' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $user_id = Auth::id();
        $menu_name = $request->menu_name;
        $password = $request->password;
        $fromdate = $request->fromdate;
        $todate = $request->todate;
        $status_id = $request->status_id;
        $userdetail = User::find($user_id);
        $current_password = $userdetail->password;
        if (Hash::check($password, $current_password)) {
            $loadcash = Loadcash::where('parent_id', Auth::id())->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)->where('status_id', $status_id)->orderBy('id', 'DESC')->get();
            $arr = array();
            foreach ($loadcash as $value) {

                $data = array(
                    $value->id,
                    $value->user->name . ' ' . $value->user->last_name,
                    $value->created_at,
                    $value->payment_date,
                    $value->bankdetail->bank_name,
                    $value->paymentmethod->payment_type,
                    number_format($value->amount, 2),
                    $value->bankref,
                    ($value->payment_type == 1) ? 'Auto' : 'Manaul',
                    $value->status->status,
                );
                array_push($arr, $data);
            }
            $delimiter = ",";
            $filename = 'download/' . $menu_name . '_' . $user_id . '_' . mt_rand(10, 99) . '.csv';
            $fp = fopen($filename, 'w+');
            $col = ['Id', 'User', 'Request Date', 'Payment Date', 'Bank', 'Method', 'Amount', 'UTR', 'Payment Type', 'Status'];

            fputcsv($fp, $col, $delimiter);
            foreach ($arr as $line) {
                fputcsv($fp, $line, $delimiter);
            }
            fclose($fp);
            $path = url('') . '/' . $filename;
            return Response()->json(['status' => 'success', 'message' => 'success', 'download_link' => $path]);

        } else {
            return Response()->json(['status' => 'failure', 'message' => 'password does not match']);
        }
    }

    function agent_onboarding_download(Request $request)
    {
        if (Auth::User()->company->aeps == 1 && Auth::User()->role_id == 1) {
            $rules = array(
                'password' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $user_id = Auth::id();
            $menu_name = $request->menu_name;
            $password = $request->password;
            $fromdate = $request->fromdate;
            $todate = $request->todate;
            $status_id = $request->status_id;
            $userdetail = User::find($user_id);
            $current_password = $userdetail->password;
            if (Hash::check($password, $current_password)) {
                $loadcash = Agentonboarding::orderBy('id', 'DESC')->get();
                $arr = array();
                foreach ($loadcash as $value) {

                    $data = array(
                        $value->id,
                        $value->created_at,
                        $value->user->name . ' ' . $value->user->last_name,
                        $value->first_name,
                        $value->last_name,
                        $value->mobile_number,
                        $value->email,
                        $value->aadhar_number,
                        $value->pan_number,
                        $value->company,
                        $value->pin_code,
                        $value->address,
                        $value->bank_account_number,
                        $value->ifsc,
                        $value->state->name,
                        $value->district->district_name,
                        $value->city,
                        $value->status->status,
                    );
                    array_push($arr, $data);
                }
                $delimiter = ",";
                $filename = 'download/outlet_list_' . mt_rand(10, 99) . '.csv';
                $fp = fopen($filename, 'w+');
                $col = ['ID', 'Date Time', 'User Name', 'First Name', 'Last Name', 'Mobile Number', 'Email', 'Aadhar Number', 'Pan Number', 'Shop Name', 'Pin Code', 'Address', 'Account Number', 'IFSC Code', 'State Name', 'District Name', 'City', 'Status'];

                fputcsv($fp, $col, $delimiter);
                foreach ($arr as $line) {
                    fputcsv($fp, $line, $delimiter);
                }
                fclose($fp);
                $path = url('') . '/' . $filename;
                return Response()->json(['status' => 'success', 'message' => 'success', 'download_link' => $path]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'password does not match']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function DownloadUserLedgerReport($fromdate, $todate, $wallet_type, $child_id)
    {
        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);
        $reports = Report::whereIn('user_id', $my_down_member)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('user_id', $child_id)
            ->where('wallet_type', $wallet_type)
            ->orderBy('id', 'DESC')
            ->get();
        $arr = array();
        foreach ($reports as $value) {
            if (Auth::User()->role_id == 1) {
                $apis = Api::find($value->api_id);
                $vendor = (empty($apis)) ? $this->brand_name : $apis->api_name;
            } else {
                $vendor = $this->brand_name;
            }
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
                ($value->wallet_type == 1) ? 'Payout' : 'Payin',
                $value->status->status,
                $vendor,
            );
            array_push($arr, $data);
        }
        $delimiter = ",";
        $filename = 'download/all-transaction-report' . $user_id . '_' . mt_rand(10, 99) . '.csv';
        $fp = fopen($filename, 'w+');
        $col = ['Report Id', 'Date', 'User', 'Provider', 'Number', 'Txnid', 'Opening Balance', 'Amount', 'Profit', 'Closing Balance', 'Mode', 'Ip Address', 'Wallet', 'Status', 'Vendor'];
        fputcsv($fp, $col, $delimiter);
        foreach ($arr as $line) {
            fputcsv($fp, $line, $delimiter);
        }
        fclose($fp);
        $path = url('') . '/' . $filename;
        return Response()->json(['status' => 'success', 'message' => 'success', 'download_link' => $path]);
    }

    function downloadPurchaseBalance($fromdate, $todate)
    {
        if (Auth::User()->role_id == 1) {
            $reports = Purchase::orderBy('id', 'DESC')
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->get();
            $arr = array();
            foreach ($reports as $value) {
                $data = array(
                    $value->id,
                    $value->created_at,
                    $value->user->name,
                    $value->api->api_name,
                    $value->masterbank->bank_name,
                    number_format($value->amount, 2),
                    $value->utr,
                    $value->purchase_type,
                    $value->status->status,
                );
                array_push($arr, $data);
            }
            $delimiter = ",";
            $filename = 'download/purchase_balance_' . mt_rand(10, 99) . '.csv';
            $fp = fopen($filename, 'w+');
            $col = ['ID', 'Date', 'User Name', 'Api Name', 'Bank Name', 'Amount', 'UTR', 'Purchase Type', 'Status'];
            fputcsv($fp, $col, $delimiter);
            foreach ($arr as $line) {
                fputcsv($fp, $line, $delimiter);
            }
            fclose($fp);
            $path = url('') . '/' . $filename;
            return Response()->json(['status' => 'success', 'message' => 'success', 'download_link' => $path]);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function delete_all_file()
    {
        $destinationPath = 'download';
        File::cleanDirectory($destinationPath);
    }
}
