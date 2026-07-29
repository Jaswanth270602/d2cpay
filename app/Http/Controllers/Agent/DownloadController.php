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
use App\Models\Apiresponse;
use App\Models\Gatewayorder;
use App\Library\RojgaarPeLibrary;
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
        [$filename, $filepath, $path] = $this->prepareDownloadTarget($services->report_slug . '_' . $user_id . '_' . mt_rand(10, 99) . '.csv');
        $fp = fopen($filepath, 'w+');
        $col = ['Report Id', 'Date', 'User', 'Provider', 'Account Number', 'Remiter Number', 'Beneficiary Name', 'Bank Name', 'UTR Number', 'Amount', 'Charges', 'Type', 'Mode', 'Ip Address', 'Wallet', 'Status'];
        fputcsv($fp, $col, $delimiter);
        foreach ($arr as $line) {
            fputcsv($fp, $line, $delimiter);
        }
        fclose($fp);
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
        [$filename, $filepath, $path] = $this->prepareDownloadTarget($services->report_slug . '_' . $user_id . '_' . mt_rand(10, 99) . '.csv');
        $fp = fopen($filepath, 'w+');
        $col = ['Report Id', 'Date', 'User', 'Provider', 'Number', 'Txnid', 'Opening Balance', 'Amount', 'Profit', 'Closing Balance', 'Mode', 'Ip Address', 'Wallet', 'Aadhar Number', 'Status'];
        fputcsv($fp, $col, $delimiter);
        foreach ($arr as $line) {
            fputcsv($fp, $line, $delimiter);
        }
        fclose($fp);
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
        [$filename, $filepath, $path] = $this->prepareDownloadTarget($services->report_slug . '_' . $user_id . '_' . mt_rand(10, 99) . '.csv');
        $fp = fopen($filepath, 'w+');
        $col = ['Report Id', 'Date', 'User', 'Provider', 'Number', 'Txnid', 'Opening Balance', 'Amount', 'Profit', 'Closing Balance', 'Mode', 'Ip Address', 'Wallet', 'Status'];
        fputcsv($fp, $col, $delimiter);
        foreach ($arr as $line) {
            fputcsv($fp, $line, $delimiter);
        }
        fclose($fp);
        return Response()->json(['status' => 'success', 'message' => 'success', 'download_link' => $path]);
    }

    function DownloadAllTransactionReport($fromdate, $todate, $statusId)
    {
        $user_id = Auth::id();
        $statusId = is_numeric($statusId) ? (int)$statusId : 0;

        $query = Report::where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate);

        if ($statusId > 0) {
            $query->where('status_id', $statusId);
        }

        $reports = $query->orderBy('id', 'DESC')->get();
        $arr = array();
        foreach ($reports as $value) {
            $wallet_type = match ((int)$value->wallet_type) {
                1 => 'Payout',
                2 => 'Payin',
                default => '',
            };
            $data = array(
                $value->id,
                (string)$value->created_at,
                optional($value->provider)->provider_name,
                $value->number,
                $value->txnid,
                number_format((float)$value->opening_balance, 2, '.', ''),
                number_format((float)$value->amount, 2, '.', ''),
                number_format((float)$value->profit, 2, '.', ''),
                number_format((float)$value->total_balance, 2, '.', ''),
                $value->client_id,
                optional($value->status)->status,
                $this->resolveFailureReason($value),
                $wallet_type,
            );
            array_push($arr, $data);
        }
        $delimiter = ",";
        [, $filepath, $path] = $this->prepareDownloadTarget('all-transaction-report' . $user_id . '_' . mt_rand(10, 99) . '.csv');
        $fp = fopen($filepath, 'w+');
        $col = [
            'ID',
            'Date Time',
            'Provider',
            'Number',
            'UTR',
            'Opening Balance',
            'Amount',
            'Platform Fee',
            'Closing Balance',
            'Client Id',
            'Status',
            'Failure Reason',
            'Wallet',
        ];
        fputcsv($fp, $col, $delimiter);
        foreach ($arr as $line) {
            fputcsv($fp, $line, $delimiter);
        }
        fclose($fp);
        return Response()->json(['status' => 'success', 'message' => 'success', 'download_link' => $path]);
    }

    private function resolveFailureReason(Report $report): string
    {
        $reason = trim((string)$report->reason);
        if ($reason !== '') {
            return $this->maskInsufficientReason($reason);
        }

        if (in_array((int)$report->status_id, [1, 6], true)) {
            return '';
        }

        if ((int)$report->status_id === 3) {
            return RojgaarPeLibrary::pendingDisplayReason((int)$report->wallet_type);
        }

        $txnid = trim((string)$report->txnid);
        if (in_array((int)$report->status_id, [2, 5], true)) {
            if ($txnid !== '' && stripos($txnid, 'UTR') === false && !str_starts_with($txnid, '{')) {
                return $this->maskInsufficientReason($txnid);
            }
        }

        $latestApi = Apiresponse::where('report_id', $report->id)->orderBy('id', 'DESC')->first();
        if (!$latestApi) {
            $gatewayOrder = Gatewayorder::where('report_id', $report->id)
                ->orWhere('id', $report->payid)
                ->orWhere('client_id', $report->client_id)
                ->orderBy('id', 'DESC')
                ->first();
            if ($gatewayOrder) {
                $latestApi = Apiresponse::where('report_id', $gatewayOrder->id)->orderBy('id', 'DESC')->first();
            }
        }

        if ($latestApi) {
            $apiMessage = trim(RojgaarPeLibrary::prettifyApiLogMessage($latestApi->message));
            $responseType = (string)($latestApi->response_type ?? '');
            if ($apiMessage !== '' && !RojgaarPeLibrary::isPayinStatusPollNoise($apiMessage, $responseType)) {
                return $this->maskInsufficientReason($apiMessage);
            }
        }

        return '';
    }

    private function maskInsufficientReason(string $reason): string
    {
        return \App\Library\LockHoldPayoutLibrary::merchantFacingFailureReason($reason);
    }


    function delete_all_file()
    {
        $destinationPath = public_path('download');
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
            return;
        }
        File::cleanDirectory($destinationPath);
    }

    function serve_file(string $filename)
    {
        $filename = basename($filename);
        if (!preg_match('/^[A-Za-z0-9_\-\.]+\.csv$/', $filename)) {
            abort(404);
        }

        $path = public_path('download/' . $filename);
        if (!File::exists($path)) {
            abort(404);
        }

        return response()->download($path, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function prepareDownloadTarget(string $name): array
    {
        $destinationPath = public_path('download');
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }

        $safeName = basename($name);
        $relative = 'download/' . $safeName;
        $filepath = public_path($relative);
        $url = url('agent/download/v1/serve/' . rawurlencode($safeName));

        return [$relative, $filepath, $url];
    }
}
