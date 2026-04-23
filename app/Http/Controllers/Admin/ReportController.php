<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use App\Library\MemberLibrary;
use App\Library\RefundLibrary;
use App\Models\Report;
use App\Models\Api;
use App\Models\Status;
use App\Models\Provider;
use App\Models\User;
use App\Models\Beneficiary;
use \Crypt;
use App\Models\Commissionreport;
use App\Models\Apiresponse;
use App\Models\Service;
use App\Models\State;
use App\Models\Apicommreport;
use App\Models\Gatewayorder;
use File;
use Helpers;
use App\Models\Sitesetting;
use App\Library\PermissionLibrary;
use App\Library\BasicLibrary;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->company_id = Helpers::company_id()->id;
        $companies = Helpers::company_id();
        $this->company_id = $companies->id;
        $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
        $this->brand_name = (empty($sitesettings)) ? '' : $sitesettings->brand_name;
        $this->backend_template_id = (empty($sitesettings)) ? 1 : $sitesettings->backend_template_id;
    }

    function all_transaction_report(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['all_transaction_report_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);

        if ($request->fromdate && $request->todate) {
            $fromdate = $request->fromdate;
            $todate = $request->todate;
            $status_id = $request->status_id;
            $child_id = $request->child_id;
            $provider_id = $request->provider_id;
            $api_id = $request->api_id;
            $urls = url('admin/report/v1/all-transaction-report-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate . '&status_id=' . $status_id . '&child_id=' . $child_id . '&provider_id=' . $provider_id . '&api_id=' . $api_id;;
        } else {
            $status_id = 0;
            $child_id = 0;
            $provider_id = 0;
            $api_id = 0;
            $fromdate = date('Y-m-d', time());
            $todate = date('Y-m-d', time());
            $urls = url('admin/report/v1/all-transaction-report-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate . '&status_id=' . $status_id . '&child_id=' . $child_id . '&provider_id=' . $provider_id . '&api_id=' . $api_id;;
        }
        $data = array(
            'page_title' => 'All Transaction Report',
            'report_slug' => 'All Transaction Report',
            'fromdate' => $fromdate,
            'todate' => $todate,
            'urls' => $urls,
            'status_id' => $status_id,
            'child_id' => $child_id,
            'provider_id' => $provider_id,
            'api_id' => $api_id,
        );
        $apis = Api::where('status_id', 1)->select('id', 'api_name')->get();
        $status = Status::whereIn('id', [1, 2, 3, 4, 5, 6, 7])->select('id', 'status')->get();
        $users = User::whereIn('id', $my_down_member)->where('status_id', 1)->select('id', 'name', 'last_name')->get();
        $providers = Provider::where('status_id', 1)->select('id', 'provider_name')->get();
        if ($this->backend_template_id == 1) {
            return view('admin.report.all_transaction_report', compact('apis', 'status', 'users', 'providers'))->with($data);
        } elseif ($this->backend_template_id == 2) {
            return view('themes2.admin.report.all_transaction_report', compact('apis', 'status', 'users', 'providers'))->with($data);
        } elseif ($this->backend_template_id == 3) {
            return view('themes3.admin.report.all_transaction_report', compact('apis', 'status', 'users', 'providers'))->with($data);
        } elseif ($this->backend_template_id == 4) {
            return view('themes4.admin.report.all_transaction_report', compact('apis', 'status', 'users', 'providers'))->with($data);
        } else {
            return redirect()->back();
        }
    }

    function all_transaction_report_api(Request $request)
    {
        $fromdate = $request->get('fromdate');
        $todate = $request->get('amp;todate');
        $child_id = $request->get('amp;child_id');
        $status_id = $request->get('amp;status_id');
        $provider_id = $request->get('amp;provider_id');
        $api_id = $request->get('amp;api_id');
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

        if ($status_id == 0) {
            $status_id = Status::get('id');
        } else {
            $status_id = Status::where('id', $status_id)->get('id');
        }
        if ($child_id == 0) {
            $child_id = User::whereIn('id', $my_down_member)->get('id');
        } else {
            $child_id = User::whereIn('id', $my_down_member)->where('id', $child_id)->get('id');
        }

        if ($provider_id == 0) {
            $provider_id = Provider::get('id');
        } else {
            $provider_id = Provider::where('id', $provider_id)->get('id');
        }

        if (Auth::User()->role_id == 1) {
            if ($api_id == 0) {
                $database_api = Api::get(['id'])->toArray();
                $old_id = Status::where('id', 0)->get('id')->toArray();
                $api_id = array_merge($database_api, $old_id);
            } else {
                $api_id = Api::where('id', $api_id)->get('id');
            }
        } else {
            $api_id = Api::get('id');
        }

        $totalRecords = Report::select('count(*) as allcount')
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('user_id', $child_id)
            ->whereIn('status_id', $status_id)
            ->whereIn('provider_id', $provider_id)
            ->whereIn('api_id', $api_id)
            ->count();

        if (!empty($searchValue)) {
            $totalRecordswithFilter = Report::select('count(*) as allcount')
                ->whereIn('user_id', $child_id)
                ->whereIn('status_id', $status_id)
                ->whereIn('provider_id', $provider_id)
                ->whereIn('api_id', $api_id)
                ->where(function ($query) use ($searchValue) {
                    $query->where('id', 'like', '%' . $searchValue . '%')
                        ->orWhere('number', 'like', '%' . $searchValue . '%')
                        ->orWhere('txnid', 'like', '%' . $searchValue . '%');
                })->count();

            $records = Report::orderBy($columnName, $columnSortOrder)
                ->whereIn('user_id', $child_id)
                ->whereIn('status_id', $status_id)
                ->whereIn('provider_id', $provider_id)
                ->whereIn('api_id', $api_id)
                ->select('state_id', 'user_id', 'api_id', 'id', 'created_at', 'provider_id', 'number', 'txnid', 'opening_balance', 'amount', 'profit', 'total_balance', 'status_id', 'mode','reason','wallet_type', 'client_id')
                ->orderBy('id', 'DESC')
                ->where(function ($query) use ($searchValue) {
                    $query->where('id', 'like', '%' . $searchValue . '%')
                        ->orWhere('number', 'like', '%' . $searchValue . '%')
                        ->orWhere('txnid', 'like', '%' . $searchValue . '%');
                })->skip($start)
                ->take($rowperpage)
                ->get();
        } else {
            $totalRecordswithFilter = Report::select('count(*) as allcount')
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->where('number', 'like', '%' . $searchValue . '%')
                ->whereIn('user_id', $child_id)
                ->whereIn('status_id', $status_id)
                ->whereIn('provider_id', $provider_id)
                ->whereIn('api_id', $api_id)
                ->count();

            $records = Report::orderBy($columnName, $columnSortOrder)
                ->where('number', 'like', '%' . $searchValue . '%')
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->whereIn('status_id', $status_id)
                ->whereIn('provider_id', $provider_id)
                ->whereIn('user_id', $child_id)
                ->whereIn('api_id', $api_id)
                ->select('state_id', 'user_id', 'api_id', 'id', 'created_at', 'provider_id', 'number', 'txnid', 'opening_balance', 'amount', 'profit', 'total_balance', 'status_id', 'mode', 'reason','wallet_type', 'client_id')
                ->orderBy('id', 'DESC')
                ->skip($start)
                ->take($rowperpage)
                ->get();
        }
        $data_arr = array();
        foreach ($records as $value) {
            $statement_url = url('admin/report/v1/user-ledger-report') . '/' . Crypt::encrypt($value->user_id);
            $states = State::find($value->state_id);
            $state_name = ($states) ? $states->code : 'All Zone';
            if (Auth::User()->role_id == 1) {
                $apis = Api::find($value->api_id);
                $vendor = ($apis) ? $apis->api_name : $this->brand_name;
            } else {
                $vendor = $this->brand_name;
            }
            $wallet_type = match ($value->wallet_type) {
                1 => 'Payout',
                2 => 'Payin',
                default => '',
            };
            $data_arr[] = array(
                "id" => $value->id,
                "created_at" => "$value->created_at",
                "user" => '<a href="' . $statement_url . '">' . $value->user->name . ' ' . $value->user->last_name . '</a>',
                "provider" => $value->provider->provider_name,
                "number" => $value->number,
                "txnid" => $value->txnid,
                "opening_balance" => number_format($value->opening_balance, 2),
                "amount" => number_format($value->amount, 2),
                "profit" => number_format($value->profit, 2),
                "total_balance" => number_format($value->total_balance, 2),
                "status" => '<span class="' . $value->status->class . '" onclick="view_recharges(' . $value->id . ')">' . $value->status->status . '</span>',
                "mode" => $value->mode,
                "state" => $state_name,
                "vendor" => $vendor,
                "view" => '<button class="btn btn-danger btn-sm" onclick="view_recharges(' . $value->id . ')"><i class="fas fa-eye"></i> View</button>',
                "failure_reason" => $this->resolveFailureReason($value),
                'wallet_type' => $wallet_type,
                'client_id' => $value->client_id,

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


    function welcome(Request $request, $report_slug)
    {
        $library = new \App\Library\BasicLibrary;
        $companyActiveService = $library->getCompanyActiveService(Auth::id());
        $userActiveService = $library->getUserActiveService(Auth::id());
        $services = Service::whereIn('id', $companyActiveService)->whereIn('id', $userActiveService)->where('report_slug', $report_slug)->first();
        if ($services) {
            if ($request->fromdate && $request->todate) {
                $fromdate = $request->fromdate;
                $todate = $request->todate;
                $status_id = $request->status_id;
                $apiUrl = url('admin/report/v1/search') . '/' . $services->report_slug . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate . '&status_id=' . $status_id;
            } else {
                $fromdate = date('Y-m-d', time());
                $todate = date('Y-m-d', time());
                $status_id = 0;
                $apiUrl = url('admin/report/v1/search') . '/' . $services->report_slug . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate . '&status_id=' . $status_id;
            }
            $data = array(
                'page_title' => $services->service_name . ' History',
                'report_slug' => $report_slug,
                'fromdate' => $fromdate,
                'todate' => $todate,
                'status_id' => $status_id,
                'apiUrl' => $apiUrl,
                'searchURL' => url('admin/report/v1/welcome') . '/' . $report_slug,
            );
            $status = Status::select('id', 'status')->whereIn('id', [1, 2, 3, 4, 5, 6, 7])->get();
            $apis = Api::where('status_id', 1)->select('id', 'api_name')->get();
            if ($services->servicegroup_id == 4) {
                return view('admin.report.banking_reports', compact('status', 'apis'))->with($data);
            } elseif ($services->servicegroup_id == 5) {
                return view('admin.report.aeps_report', compact('status', 'apis'))->with($data);
            } else {
                return view('admin.report.dynamic_reports', compact('status', 'apis'))->with($data);
            }
        } else {
            return redirect()->back();
        }
    }

    function search_report(Request $request, $report_slug)
    {
        $fromdate = $request->get('fromdate');
        $todate = $request->get('amp;todate');
        $status_id = $request->get('amp;status_id');
        $library = new \App\Library\BasicLibrary;
        $companyActiveService = $library->getCompanyActiveService(Auth::id());
        $userActiveService = $library->getUserActiveService(Auth::id());
        $services = Service::whereIn('id', $companyActiveService)->whereIn('id', $userActiveService)->where('report_slug', $report_slug)->first();
        if (empty($services)) {
            return Response()->json(['status' => 'failure', 'message' => 'Service not active!']);
        }
        if ($services->servicegroup_id == 4) {
            Self::bankingReport($request, $fromdate, $todate, $status_id, $services);
        } elseif ($services->servicegroup_id == 5) {
            Self::aepsReport($request, $fromdate, $todate, $status_id, $services);
        } else {
            Self::otherReport($request, $fromdate, $todate, $status_id, $services);
        }
    }

    function otherReport($request, $fromdate, $todate, $status_id, $services)
    {
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
        $totalRecords = Report::select('count(*) as allcount')
            ->whereIn('user_id', $my_down_member)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('status_id', $status_id)
            ->whereIn('provider_id', $provider_id)
            ->count();

        $totalRecordswithFilter = Report::select('count(*) as allcount')
            ->whereIn('user_id', $my_down_member)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('number', 'like', '%' . $searchValue . '%')
            ->whereIn('status_id', $status_id)
            ->whereIn('provider_id', $provider_id)
            ->count();

        // Fetch records
        $records = Report::orderBy($columnName, $columnSortOrder)
            ->select('state_id', 'id', 'user_id', 'created_at', 'provider_id', 'number', 'txnid', 'opening_balance', 'amount', 'profit', 'total_balance', 'wallet_type', 'status_id')
            ->where('number', 'like', '%' . $searchValue . '%')
            ->whereIn('user_id', $my_down_member)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('status_id', $status_id)
            ->whereIn('provider_id', $provider_id)
            ->orderBy('id', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();
        $data_arr = array();
        foreach ($records as $value) {
            $wallet_type = match ($value->wallet_type) {
                1 => 'Payout',
                2 => 'Payin',
                default => '',
            };
            $states = State::find($value->state_id);
            $state_name = ($states) ? $states->name : 'All Zone';
            $statement_url = url('admin/report/v1/user-ledger-report') . '/' . Crypt::encrypt($value->user_id);
            $data_arr[] = array(
                "id" => $value->id,
                "created_at" => "$value->created_at",
                "user" => '<a href="' . $statement_url . '">' . $value->user->name . ' ' . $value->user->last_name . '</a>',
                "provider" => $value->provider->provider_name,
                "number" => $value->number,
                "txnid" => $value->txnid,
                "opening_balance" => number_format($value->opening_balance, 2),
                "amount" => number_format($value->amount, 2),
                "profit" => number_format($value->profit, 2),
                "total_balance" => number_format($value->total_balance, 2),
                "wallet_type" => $wallet_type,
                "state_name" => $state_name,
                "status" => '<span class="' . $value->status->class . '">' . $value->status->status . '</span>',
                "view" => '<button class="btn btn-danger btn-sm" onclick="view_recharges(' . $value->id . ')">View</button>',
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

    function bankingReport($request, $fromdate, $todate, $status_id, $services)
    {
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
        $provider_id = Provider::where('service_id', $services->id)->get(['id']);

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

        $totalRecords = Report::select('count(*) as allcount')
            ->whereIn('user_id', $my_down_member)
            ->whereIn('provider_id', $provider_id)
            ->whereIn('status_id', $status_id)
            ->count();

        $totalRecordswithFilter = Report::select('count(*) as allcount')
            ->whereIn('user_id', $my_down_member)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('number', 'like', '%' . $searchValue . '%')
            ->whereIn('provider_id', $provider_id)
            ->whereIn('status_id', $status_id)
            ->count();

        // Fetch records
        $records = Report::orderBy($columnName, $columnSortOrder)
            ->select('id', 'created_at', 'user_id', 'provider_id', 'number', 'txnid', 'amount', 'profit', 'total_balance', 'status_id', 'beneficiary_id', 'channel')
            ->where('number', 'like', '%' . $searchValue . '%')
            ->whereIn('user_id', $my_down_member)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('provider_id', $provider_id)
            ->whereIn('status_id', $status_id)
            ->orderBy('id', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();
        $data_arr = array();
        foreach ($records as $value) {
            $beneficiary = Beneficiary::find($value->beneficiary_id);
            $remiter_number = (empty($beneficiary)) ? '' : $beneficiary->remiter_number;
            $bene_name = (empty($beneficiary)) ? '' : $beneficiary->name;
            $bank_name = (empty($beneficiary)) ? '' : $beneficiary->bank_name;
            $payment_mode = ($value->channel == 2) ? 'IMPS' : 'NEFT';
            $statement_url = url('admin/report/v1/user-ledger-report') . '/' . Crypt::encrypt($value->user_id);
            $data_arr[] = array(
                "id" => $value->id,
                "created_at" => "$value->created_at",
                "user" => '<a href="' . $statement_url . '">' . $value->user->name . ' ' . $value->user->last_name . '</a>',
                "provider" => $value->provider->provider_name,
                "number" => $value->number,
                "remiter_number" => $remiter_number,
                "bene_name" => $bene_name,
                "bank_name" => $bank_name,
                "txnid" => $value->txnid,
                "amount" => number_format($value->amount, 2),
                "profit" => number_format($value->profit, 2),
                "balance" => number_format($value->total_balance, 2),
                "payment_mode" => $payment_mode,
                "status" => '<span class="' . $value->status->class . '">' . $value->status->status . '</span>',
                "view" => '<button class="btn btn-danger btn-sm" onclick="view_recharges(' . $value->id . ')"><i class="fas fa-eye"></i> View</button>',
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

    function aepsReport($request, $fromdate, $todate, $status_id, $services)
    {
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
        $user_id = Auth::id();
        $provider_id = Provider::where('service_id', $services->id)->get(['id']);

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

        $totalRecords = Report::select('count(*) as allcount')
            ->whereIn('user_id', $my_down_member)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('provider_id', $provider_id)
            ->whereIn('status_id', $status_id)
            ->where('wallet_type', 2)
            ->count();

        $totalRecordswithFilter = Report::select('count(*) as allcount')
            ->whereIn('user_id', $my_down_member)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('number', 'like', '%' . $searchValue . '%')
            ->whereIn('provider_id', $provider_id)
            ->whereIn('status_id', $status_id)
            ->where('wallet_type', 2)
            ->count();

        // Fetch records
        $records = Report::orderBy($columnName, $columnSortOrder)
            ->select('id', 'created_at', 'user_id', 'provider_id', 'number', 'txnid', 'amount', 'status_id')
            ->where('number', 'like', '%' . $searchValue . '%')
            ->whereIn('user_id', $my_down_member)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereIn('provider_id', $provider_id)
            ->whereIn('status_id', $status_id)
            ->where('wallet_type', 2)
            ->orderBy('id', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();
        $data_arr = array();
        foreach ($records as $value) {
            $statement_url = url('admin/report/v1/user-ledger-report') . '/' . Crypt::encrypt($value->user_id);
            $data_arr[] = array(
                "id" => $value->id,
                "created_at" => "$value->created_at",
                "user" => '<a href="' . $statement_url . '">' . $value->user->name . ' ' . $value->user->last_name . '</a>',
                "provider" => $value->provider->provider_name,
                "number" => $value->number,
                "txnid" => $value->txnid,
                "amount" => number_format($value->amount, 2),
                'bank_name' => (!empty($value->aepsreport->report_id)) ? $value->aepsreport->bank_name : '',
                'aadhar_number' => (!empty($value->aepsreport->report_id)) ? $value->aepsreport->aadhar_number : '',
                "status" => '<span class="' . $value->status->class . '">' . $value->status->status . '</span>',
                "view" => '<button class="btn btn-danger btn-sm" onclick="view_recharges(' . $value->id . ')"><i class="fas fa-eye"></i> View</button>',
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

    function view_recharge_details(Request $request)
    {
        $id = $request->id;
        $reports = Report::where('id', $id)->first();
        if ($reports) {
            $beneficiary = Beneficiary::find($reports->beneficiary_id);
            $account_number = (empty($beneficiary)) ? '' : $beneficiary->account_number;
            $ifsc = (empty($beneficiary)) ? '' : $beneficiary->ifsc;
            $bank_name = (empty($beneficiary)) ? '' : $beneficiary->bank_name;
            $name = (empty($beneficiary)) ? '' : $beneficiary->name;
            $remiter_number = (empty($beneficiary)) ? '' : $beneficiary->remiter_number;
            $remiter_name = (empty($beneficiary)) ? '' : $beneficiary->remiter_name;
            $moneydetails = array(
                'account_number' => $account_number,
                'ifsc' => $ifsc,
                'bank_name' => $bank_name,
                'name' => $name,
                'remiter_number' => $remiter_number,
                'remiter_name' => $remiter_name,
            );
            $details = array(
                'update_recharge_anchor' => 'view_refund_recharge(' . $reports->id . ')',
                'update_logs_anchor' => 'view_transaction_logs(' . $reports->id . ')',
                'id' => $reports->id,
                'company' => $reports->user->company->company_name,
                'created_at' => "$reports->created_at",
                'user' => $reports->user->name,
                'provider' => $reports->provider->provider_name,
                'number' => $reports->number,
                'txnid' => $reports->txnid,
                'opening_balance' => number_format($reports->opening_balance, 2),
                'amount' => number_format($reports->amount, 2),
                'profit' => number_format($reports->profit, 2),
                'total_balance' => number_format($reports->total_balance, 2),
                'mode' => $reports->mode,
                'api_id' => $reports->api_id,
                'client_id' => $reports->client_id,
                'ip_address' => $reports->ip_address,
                'status_id' => $reports->status_id,
                'wallet_type' => $reports->wallet_type,
                'moneydetails' => $moneydetails,
            );
            return Response()->json([
                'status' => 'success',
                'details' => $details
            ]);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'record not found']);
        }
    }

    function view_transaction_logs(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['view_api_logs_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $id = $request->id;
            $report = Report::find($id);
            if (!$report) {
                return Response()->json(['status' => 'failure', 'message' => 'Report not found']);
            }
            $apiresponse = Apiresponse::where('report_id', $id)->orderBy('id', 'ASC')->get();
            if ($apiresponse->isEmpty()) {
                $gatewayOrder = Gatewayorder::where('report_id', $id)
                    ->orWhere('id', $report->payid)
                    ->orWhere('client_id', $report->client_id)
                    ->orderBy('id', 'DESC')
                    ->first();
                if ($gatewayOrder) {
                    $apiresponse = Apiresponse::where('report_id', $gatewayOrder->id)->orderBy('id', 'ASC')->get();
                }
            }

            if ($apiresponse->isNotEmpty()) {
                $response = array();
                $i = 1;
                foreach ($apiresponse as $value) {
                    $product = array();
                    $product["id"] = $i++;
                    $product["request_message"] = $value->request_message ?: 'Request payload not captured';
                    $product["response"] = $this->prettifyApiPayload($value->message);
                    array_push($response, $product);
                }
                return Response()->json(['status' => 'success', 'logs' => $response]);
            }

            return Response()->json(['status' => 'failure', 'message' => 'Logs Not Found']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    private function resolveFailureReason(Report $report): string
    {
        $reason = trim((string)$report->reason);
        if ($reason !== '') {
            return $reason;
        }

        if ((int)$report->status_id === 1) {
            return '';
        }

        $txnid = trim((string)$report->txnid);
        if ((int)$report->status_id === 2 || (int)$report->status_id === 5) {
            if ($txnid !== '' && stripos($txnid, 'UTR') === false) {
                return $txnid;
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
            $apiMessage = trim($this->prettifyApiPayload($latestApi->message));
            if ($apiMessage !== '') {
                return $apiMessage;
            }
        }

        if ((int)$report->status_id === 3) {
            return 'Transaction is pending at provider side.';
        }

        return 'Failure reason not provided by provider.';
    }

    private function prettifyApiPayload($payload): string
    {
        $message = trim((string)$payload);
        if ($message === '') {
            return '';
        }

        $decoded = json_decode($message, true);
        if (!is_array($decoded)) {
            return $message;
        }

        foreach (['message', 'responseMessage', 'error', 'errors', 'status'] as $key) {
            if (!empty($decoded[$key])) {
                if (is_array($decoded[$key])) {
                    return json_encode($decoded[$key]);
                }
                return (string)$decoded[$key];
            }
        }

        return json_encode($decoded);
    }

    function recharge_update_for_refund(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['update_transaction_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $id = $request->id;
            $txnid = $request->txnid;
            $status_id = $request->status_id;
            $wallet_type = $request->wallet_type;
            $reports = Report::find($id);
            if ($reports) {
                $mode = Auth::User()->name . ' ' . Auth::User()->last_name;
                if ($wallet_type == 1) {
                    $library = new RefundLibrary();
                    return $library->update_transaction($status_id, $txnid, $id, $mode);
                } elseif ($wallet_type == 2) {
                    $library = new RefundLibrary();
                    return $library->update_transaction_aeps($status_id, $txnid, $id, $mode);
                } else {
                    return Response()->json(['status' => 'failure', 'message' => 'Select wallet type']);
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Record not found']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function update_selected_transaction(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $rules = array(
                'report_id' => 'required',
                'remark' => 'required',
                'status_id' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $report_id = $request->report_id;
            $remark = $request->remark;
            $status_id = $request->status_id;
            $exploadreport = explode(',', $report_id);
            foreach ($exploadreport as $value) {
                $report_id = $value;
                $reports = Report::where('id', $report_id)->where('id', $report_id)->first();
                if ($reports) {
                    $mode = Auth::User()->name . ' ' . Auth::User()->last_name;
                    if ($reports->wallet_type == 1) {
                        $library = new RefundLibrary();
                        $library->update_transaction($status_id, $remark, $report_id, $mode);
                    } else {
                        $library = new RefundLibrary();
                        $library->update_transaction_aeps($status_id, $remark, $report_id, $mode);
                    }
                }
            }
            return Response()->json(['status' => 'success', 'message' => 'Transaction update successfully']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function pending_transaction(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['pending_transaction_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        if ($request->fromdate && $request->todate) {
            $fromdate = $request->fromdate;
            $todate = $request->todate;
            $urls = url('admin/report/v1/pending-transaction-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate;
        } else {
            $fromdate = date('Y-m-d', strtotime('-15 days'));
            $todate = date('Y-m-d', time());
            $urls = url('admin/report/v1/pending-transaction-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate;
        }
        $data = array(
            'page_title' => 'Pending Report',
            'report_slug' => 'Pending Report',
            'fromdate' => $fromdate,
            'todate' => $todate,
            'urls' => $urls
        );
        $apis = Api::where('status_id', 1)->select('id', 'api_name')->get();
        $status = Status::whereIn('id', [1, 2, 3, 4, 5, 6, 7])->select('id', 'status')->get();
        if ($this->backend_template_id == 1) {
            return view('admin.report.pending_transaction', compact('apis', 'status'))->with($data);
        } elseif ($this->backend_template_id == 2) {
            return view('themes2.admin.report.pending_transaction', compact('apis', 'status'))->with($data);
        } elseif ($this->backend_template_id == 3) {
            return view('themes3.admin.report.pending_transaction', compact('apis', 'status'))->with($data);
        } elseif ($this->backend_template_id == 4) {
            return view('themes4.admin.report.pending_transaction', compact('apis', 'status'))->with($data);
        } else {
            return redirect()->back();
        }
    }

    function pending_transaction_api(Request $request)
    {
        $fromdate = $request->get('fromdate');
        $todate = $request->get('amp;todate');

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

        $totalRecords = Report::select('count(*) as allcount')
            ->whereIn('user_id', $my_down_member)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('status_id', 3)
            ->count();

        $totalRecordswithFilter = Report::select('count(*) as allcount')
            ->whereIn('user_id', $my_down_member)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('number', 'like', '%' . $searchValue . '%')
            ->where('status_id', 3)
            ->count();

        // Fetch records

        $records = Report::orderBy($columnName, $columnSortOrder)
            ->where('number', 'like', '%' . $searchValue . '%')
            ->whereIn('user_id', $my_down_member)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('status_id', 3)
            ->orderBy('id', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();
        $data_arr = array();
        foreach ($records as $value) {
            if (Auth::User()->role_id == 1) {
                $apis = Api::find($value->api_id);
                if ($apis) {
                    $vendor = $apis->api_name;
                } else {
                    $vendor = $this->brand_name;
                }
            } else {
                $vendor = $this->brand_name;
            }
            $statement_url = url('admin/report/v1/user-ledger-report') . '/' . Crypt::encrypt($value->user_id);
            $data_arr[] = array(
                "select" => '<input type="checkbox" name="report_id[]" value="' . $value->id . '">',
                "id" => $value->id,
                "created_at" => "$value->created_at",
                "user" => '<a href="' . $statement_url . '">' . $value->user->name . ' ' . $value->user->last_name . '</a>',
                "provider" => $value->provider->provider_name,
                "number" => $value->number,
                "amount" => number_format($value->amount, 2),
                "status" => '<span class="' . $value->status->class . '">' . $value->status->status . '</span>',
                "vendor_name" => $vendor,
                "view" => '<button class="btn btn-danger btn-sm" onclick="view_recharges(' . $value->id . ')"><i class="fas fa-eye"></i> View</button>',
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

    function profit_distribution(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['profit_distribution_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2) {
            if ($request->fromdate && $request->todate) {
                $fromdate = $request->fromdate;
                $todate = $request->todate;
                $urls = url('admin/report/v1/profit-distribution-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate;
            } else {
                $fromdate = date('Y-m-d', time());
                $todate = date('Y-m-d', time());
                $urls = url('admin/report/v1/profit-distribution-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate;
            }
            $data = array(
                'page_title' => 'Profit Distribution',
                'fromdate' => $fromdate,
                'todate' => $todate,
                'urls' => $urls
            );
            $apis = Api::get();
            $status = Status::get();
            if ($this->backend_template_id == 1) {
                return view('admin.report.profit_distribution', compact('apis', 'status'))->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.report.profit_distribution', compact('apis', 'status'))->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.report.profit_distribution', compact('apis', 'status'))->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.report.profit_distribution', compact('apis', 'status'))->with($data);
            } else {
                return redirect()->back();
            }
        } else {
            return redirect()->back();
        }
    }

    function profit_distribution_api(Request $request)
    {
        $fromdate = $request->get('fromdate');
        $todate = $request->get('amp;todate');

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


        $totalRecords = Commissionreport::select('count(*) as allcount')
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('status_id', 1)
            ->count();

        $totalRecordswithFilter = Commissionreport::select('count(*) as allcount')
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('report_id', 'like', '%' . $searchValue . '%')
            ->where('status_id', 1)
            ->count();

        // Fetch records

        $records = Commissionreport::orderBy($columnName, $columnSortOrder)
            ->where('report_id', 'like', '%' . $searchValue . '%')
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('status_id', 1)
            ->orderBy('id', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();
        $data_arr = array();
        foreach ($records as $value) {
            $apis = Api::find($value->api_id);
            $api_name = ($apis) ? $apis->api_name : '';
            $total_comm = $value->company_staff + $value->white_label_reseller_comm + $value->white_label_comm + $value->sales_team_comm + $value->super_distributor_comm + $value->distributor_comm + $value->retailer_comm;
            $myprofit = $value->api_comm - $total_comm;
            if ($myprofit < 0) {
                $profit = '<span style="color: red;"><i class="fas fa-minus-square"></i>  ' . number_format($myprofit, 2) . '</span>';
            } else {
                $profit = '<span style="color: green;"><i class="fas fa-plus-square"></i> ' . number_format($myprofit, 2) . '</span>';
            }
            $data_arr[] = array(
                "id" => $value->id,
                "created_at" => "$value->created_at",
                "user" => $value->user->name . ' ' . $value->user->last_name,
                "provider" => $value->provider->provider_name,
                "api_name" => $api_name,
                "report_id" => $value->report_id,
                "api_comm" => number_format($value->api_comm, 2),
                "company_staff" => number_format($value->company_staff, 2),
                "white_label_reseller_comm" => number_format($value->white_label_reseller_comm, 2),
                "white_label_comm" => number_format($value->white_label_comm, 2),
                "sales_team_comm" => number_format($value->sales_team_comm, 2),
                "super_distributor_comm" => number_format($value->super_distributor_comm, 2),
                "distributor_comm" => number_format($value->distributor_comm, 2),
                "retailer_comm" => number_format($value->retailer_comm, 2),
                "total_comm" => number_format($total_comm, 2),
                "my_profit" => $profit,
                "view" => '<button class="btn btn-danger btn-sm" onclick="view_recharges(' . $value->report_id . ')"><i class="fas fa-eye"></i> View</button>',
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

    function refund_manager(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['refund_manager_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2) {
            $data = array(
                'page_title' => 'Refund Manager',
                'report_slug' => 'Refund Manager',
                'search' => 'no',
                'search_type' => 1,
                'number' => '',
            );
            $apis = Api::where('status_id', 1)->select('id', 'api_name')->get();
            $status = Status::whereIn('id', [1, 2, 3, 4, 5, 6, 7])->select('id', 'status')->get();
            return view('admin.report.refund_manager', compact('apis', 'status'))->with($data);
        } else {
            return Redirect::back();
        }
    }

    function search_refund_manager(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['refund_manager_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2) {
            $search_type = $request->search_type;
            if ($search_type == 1) {
                $report = Report::where('number', $request->number)->whereIn('status_id', [1, 2, 3, 5])->get();
            } elseif ($search_type == 2) {
                $report = Report::where('id', $request->number)->whereIn('status_id', [1, 2, 3, 5])->get();
            } elseif ($search_type == 3) {
                $report = Report::where('txnid', $request->number)->whereIn('status_id', [1, 2, 3, 5])->get();
            } else {
                $report = Report::whereIn('status_id', [3])->get();
            }
            $data = array(
                'page_title' => 'Refund Manager',
                'report_slug' => 'Refund Manager',
                'search' => 'yes',
                'search_type' => $search_type,
                'number' => $request->number,

            );
            $apis = Api::where('status_id', 1)->select('id', 'api_name')->get();
            $status = Status::whereIn('id', [1, 2, 3, 4, 5, 6, 7])->select('id', 'status')->get();
            return view('admin.report.refund_manager', compact('report', 'apis', 'status'))->with($data);
        } else {
            return Redirect::back();
        }
    }

    function ledger_report(Request $request)
    {
        if ($request->fromdate && $request->todate) {
            $fromdate = $request->fromdate;
            $todate = $request->todate;
            $urls = url('admin/report/v1/ledger-report-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate;
        } else {
            $fromdate = date('Y-m-d', time());
            $todate = date('Y-m-d', time());
            $urls = url('admin/report/v1/ledger-report-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate;
        }
        $data = array(
            'page_title' => 'Ledger Report',
            'fromdate' => $fromdate,
            'todate' => $todate,
            'urls' => $urls
        );

        return view('admin.report.ledger_report')->with($data);
    }

    function ledger_report_api(Request $request)
    {

        $fromdate = $request->get('fromdate');
        $todate = $request->get('amp;todate');

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

        $user_id = Auth::id();
        $totalRecords = Report::select('count(*) as allcount')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->count();

        $totalRecordswithFilter = Report::select('count(*) as allcount')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('id', 'like', '%' . $searchValue . '%')
            ->count();

        // Fetch records
        $records = Report::orderBy($columnName, $columnSortOrder)
            ->where('id', 'like', '%' . $searchValue . '%')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->orderBy('id', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();
        $data_arr = array();
        foreach ($records as $value) {

            if ($value->status_id == 1 || $value->status_id == 3 || $value->status_id == 7) {
                $debit = '<span style="color: red;"><i class="fas fa-minus-square"></i> ' . number_format($value->amount, 2) . '</span>';
            } else {
                $debit = 0;
            }

            if ($value->status_id == 2 || $value->status_id == 4 || $value->status_id == 6) {
                $credit = '<span style="color: green;"><i class="fas fa-plus-square"></i> ' . number_format($value->amount, 2) . '</span>';
            } else {
                $credit = 0;
            }

            if ($value->profit < 0) {
                $profit = '<span style="color: red;"><i class="fas fa-minus-square"></i>  ' . number_format($value->profit, 2) . '</span>';
            } else {
                $profit = '<span style="color: green;"><i class="fas fa-plus-square"></i> ' . number_format($value->profit, 2) . '</span>';
            }


            $data_arr[] = array(
                "id" => $value->id,
                "created_at" => "$value->created_at",
                "txnid" => $value->txnid,
                "description" => $value->description,
                "opening_balance" => number_format($value->opening_balance, 2),
                "debit" => $debit,
                "credit" => $credit,
                "profit" => $profit,
                "total_balance" => number_format($value->total_balance, 2),
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

    function debit_report(Request $request)
    {
        if ($request->fromdate && $request->todate) {
            $fromdate = $request->fromdate;
            $todate = $request->todate;
            $urls = url('admin/report/v1/debit-report-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate;
        } else {
            $fromdate = date('Y-m-d', time());
            $todate = date('Y-m-d', time());
            $urls = url('admin/report/v1/debit-report-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate;
        }
        $data = array(
            'page_title' => 'Debit Report',
            'report_slug' => 'Debit Report',
            'fromdate' => $fromdate,
            'todate' => $todate,
            'urls' => $urls
        );
        $apis = Api::where('status_id', 1)->select('id', 'api_name')->get();
        $status = Status::whereIn('id', [1, 2, 3, 4, 5, 6, 7])->select('id', 'status')->get();
        return view('admin.report.debit_report', compact('apis', 'status'))->with($data);
    }

    function debit_report_api(Request $request)
    {
        $fromdate = $request->get('fromdate');
        $todate = $request->get('amp;todate');

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


        $user_id = Auth::id();
        $totalRecords = Report::select('count(*) as allcount')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('status_id', 7)
            ->whereNotIn('remark', ['profit'])
            ->count();

        $totalRecordswithFilter = Report::select('count(*) as allcount')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('number', 'like', '%' . $searchValue . '%')
            ->where('status_id', 7)
            ->whereNotIn('remark', ['profit'])
            ->count();

        // Fetch records

        $records = Report::orderBy($columnName, $columnSortOrder)
            ->where('number', 'like', '%' . $searchValue . '%')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('status_id', 7)
            ->whereNotIn('remark', ['profit'])
            ->orderBy('id', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();
        $data_arr = array();
        foreach ($records as $value) {
            $users = User::find($value->credit_by);
            if ($users) {
                $transfer_to = $users->name . ' ' . $users->last_name;
            } else {
                $transfer_to = "";
            }
            $data_arr[] = array(
                "id" => $value->id,
                "created_at" => "$value->created_at",
                "user" => $value->user->name . ' ' . $value->user->last_name,
                "transfer_to" => $transfer_to,
                "provider" => $value->provider->provider_name,
                "number" => $value->number,
                "txnid" => $value->txnid,
                "amount" => number_format($value->amount, 2),
                "balance" => number_format($value->total_balance, 2),
                "status" => '<span class="' . $value->status->class . '">' . $value->status->status . '</span>',
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

    function credit_report(Request $request)
    {
        if ($request->fromdate && $request->todate) {
            $fromdate = $request->fromdate;
            $todate = $request->todate;
            $urls = url('admin/report/v1/credit-report-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate;
        } else {
            $fromdate = date('Y-m-d', time());
            $todate = date('Y-m-d', time());
            $urls = url('admin/report/v1/credit-report-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate;
        }
        $data = array(
            'page_title' => 'Credit Report',
            'report_slug' => 'Credit Report',
            'fromdate' => $fromdate,
            'todate' => $todate,
            'urls' => $urls
        );
        $apis = Api::where('status_id', 1)->select('id', 'api_name')->get();
        $status = Status::whereIn('id', [1, 2, 3, 4, 5, 6, 7])->select('id', 'status')->get();
        return view('admin.report.credit_report', compact('apis', 'status'))->with($data);
    }

    function credit_report_api(Request $request)
    {
        $fromdate = $request->get('fromdate');
        $todate = $request->get('amp;todate');

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


        $user_id = Auth::id();
        $totalRecords = Report::select('count(*) as allcount')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('status_id', 6)
            ->whereNotIn('remark', ['profit'])
            ->count();

        $totalRecordswithFilter = Report::select('count(*) as allcount')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('number', 'like', '%' . $searchValue . '%')
            ->where('status_id', 6)
            ->whereNotIn('remark', ['profit'])
            ->count();

        // Fetch records

        $records = Report::orderBy($columnName, $columnSortOrder)
            ->where('number', 'like', '%' . $searchValue . '%')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('status_id', 6)
            ->whereNotIn('remark', ['profit'])
            ->orderBy('id', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();
        $data_arr = array();
        foreach ($records as $value) {
            $users = User::find($value->credit_by);
            if ($users) {
                $transfer_to = $users->name . ' ' . $users->last_name;
            } else {
                $transfer_to = "";
            }
            $data_arr[] = array(
                "id" => $value->id,
                "created_at" => "$value->created_at",
                "user" => $value->user->name . ' ' . $value->user->last_name,
                "transfer_to" => $transfer_to,
                "provider" => $value->provider->provider_name,
                "number" => $value->number,
                "txnid" => $value->txnid,
                "amount" => number_format($value->amount, 2),
                "balance" => number_format($value->total_balance, 2),
                "status" => '<span class="' . $value->status->class . '">' . $value->status->status . '</span>',
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

    function user_ledger_report(Request $request, $encrypt_id)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['member_statement_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        $user_id = Crypt::decrypt($encrypt_id);
        $userdetails = User::find($user_id);
        if ($userdetails) {
            if ($request->fromdate && $request->todate) {
                $fromdate = $request->fromdate;
                $todate = $request->todate;
                $wallet_type = $request->wallet_type;
                $urls = url('admin/report/v1/user-ledger-report-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate . '&user_id=' . $user_id . '&wallet_type=' . $wallet_type;
            } else {
                $fromdate = date('Y-m-d', time());
                $todate = date('Y-m-d', time());
                $wallet_type = 1;
                $urls = url('admin/report/v1/user-ledger-report-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate . '&user_id=' . $user_id . '&wallet_type=' . $wallet_type;
            }
            $data = array(
                'page_title' => $userdetails->name . ' ' . $userdetails->last_name . ' ' . 'Ledger Report',
                'report_slug' => 'Download User Ledger Report',
                'fromdate' => $fromdate,
                'todate' => $todate,
                'urls' => $urls,
                'encrypt_id' => $encrypt_id,
                'wallet_type' => $wallet_type,
                'mailMessage' => 'We trust that your experience of using your ' . $this->brand_name . ' service has been enjoyable. We are pleased to provide you with a summary of the ' . $this->brand_name . ' service Account statement.',
            );
            $apis = Api::where('status_id', 1)->select('id', 'api_name')->get();
            $status = Status::whereIn('id', [1, 2, 3, 4, 5, 6, 7])->select('id', 'status')->get();
            if ($this->backend_template_id == 1) {
                return view('admin.report.user_ledger_report', compact('apis', 'status'))->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.report.user_ledger_report')->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.report.user_ledger_report')->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.report.user_ledger_report')->with($data);
            } else {
                return redirect()->back();
            }
        } else {
            return redirect()->back();
        }
    }

    function user_ledger_report_api(Request $request)
    {
        $fromdate = $request->get('fromdate');
        $todate = $request->get('amp;todate');
        $user_id = $request->get('amp;user_id');
        $wallet_type = $request->get('amp;wallet_type');

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


        $totalRecords = Report::select('count(*) as allcount')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('wallet_type', $wallet_type)
            ->count();

        $totalRecordswithFilter = Report::select('count(*) as allcount')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('id', 'like', '%' . $searchValue . '%')
            ->where('wallet_type', $wallet_type)
            ->count();

        // Fetch records

        $records = Report::orderBy($columnName, $columnSortOrder)
            ->where('id', 'like', '%' . $searchValue . '%')
            ->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->orderBy('id', 'DESC')
            ->where('wallet_type', $wallet_type)
            ->skip($start)
            ->take($rowperpage)
            ->get();
        $data_arr = array();
        foreach ($records as $value) {

            if ($value->status_id == 1 || $value->status_id == 3 || $value->status_id == 7 || $value->status_id == 5) {
                $debit = '<span style="color: red;"><i class="fas fa-minus-square"></i> ' . number_format($value->amount, 2) . '</span>';
            } else {
                $debit = 0;
            }

            if ($value->status_id == 2 || $value->status_id == 4 || $value->status_id == 6) {
                $credit = '<span style="color: green;"><i class="fas fa-plus-square"></i> ' . number_format($value->amount, 2) . '</span>';
            } else {
                $credit = 0;
            }

            if ($value->profit < 0) {
                $profit = '<span style="color: red;"><i class="fas fa-minus-square"></i>  ' . number_format($value->profit, 2) . '</span>';
            } else {
                $profit = '<span style="color: green;"><i class="fas fa-plus-square"></i> ' . number_format($value->profit, 2) . '</span>';
            }
            $data_arr[] = array(
                "id" => $value->id,
                "created_at" => "$value->created_at",
                "txnid" => $value->txnid,
                "description" => $value->description,
                "opening_balance" => number_format($value->opening_balance, 2),
                "debit" => $debit,
                "credit" => $credit,
                "profit" => $profit,
                "total_balance" => number_format($value->total_balance, 2),
                "status" => '<span class="' . $value->status->class . '">' . $value->status->status . '</span>',
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

    function find_ip_location(Request $request)
    {
        $rules = array(
            'ip_address' => 'required|ip',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $ip_address = $request->ip_address;
        $locationData = \Location::get($ip_address);
        $details = array(
            'ip_address' => $ip_address,
            'country_name' => $locationData->countryName,
            'country_code' => $locationData->countryCode,
            'region_code' => $locationData->regionCode,
            'region_name' => $locationData->regionName,
            'city_name' => $locationData->cityName,
            'zip_code' => $locationData->zipCode,
            'latitude' => $locationData->latitude,
            'longitude' => $locationData->longitude,
        );
        return Response()->json(['status' => 'success', 'message' => 'Successful..!', 'details' => $details]);
    }

    function apiProfitLossReport(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $role_id = Auth::User()->role_id;
            $company_id = Auth::User()->company_id;
            $user_id = Auth::id();
            $library = new MemberLibrary();
            $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);
            if ($request->fromdate && $request->todate) {
                $fromdate = $request->fromdate;
                $todate = $request->todate;
                $urls = url('admin/report/v1/api-profit-loss-report-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate;
            } else {
                $fromdate = date('Y-m-d', time());
                $todate = date('Y-m-d', time());
                $urls = url('admin/report/v1/api-profit-loss-report-api') . '?' . 'fromdate=' . $fromdate . '&todate=' . $todate;
            }
            $data = array(
                'page_title' => 'Api Profit Loss Report',
                'report_slug' => 'Api Profit Loss Report',
                'fromdate' => $fromdate,
                'todate' => $todate,
                'apiUrl' => $urls,
            );
            $apis = Api::where('status_id', 1)->get();
            $status = Status::get();
            return view('admin.report.api_profit_loss_report', compact( 'apis', 'status'))->with($data);
        } else {
            return redirect()->back();
        }
    }


   public function apiProfitLossReportApi(Request $request)
{
    try {
        $fromdate = $request->get('fromdate');
        $todate = $request->get('todate');

        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length");

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column'];
        $columnName = $columnName_arr[$columnIndex]['data'];
        $columnSortOrder = $order_arr[0]['dir'];
        $searchValue = $search_arr['value'];

        // Total records
        $totalRecords = Apicommreport::whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereNotIn('api_id', [0])
            ->count();

        // Total filtered
        $totalRecordswithFilter = Apicommreport::whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->where('api_id', 'like', '%' . $searchValue . '%')
            ->whereNotIn('api_id', [0])
            ->count();

        // Fetch records
        $records = Apicommreport::where('api_id', 'like', '%' . $searchValue . '%')
            ->where('status_id', 1)
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->whereNotIn('api_id', [0])
            ->groupBy('api_id')
            ->selectRaw('api_id, sum(amount) as amount, sum(apiCharge) as apiCharge, sum(apiCommission) as apiCommission, sum(retailerCharge) as retailerCharge, sum(retailerComm) as retailerComm, sum(totalProfit) as totalProfit')
            ->orderBy($columnName, $columnSortOrder)
            ->skip($start)
            ->take($rowperpage)
            ->get();

        // Map data
        $data_arr = [];
        foreach ($records as $value) {
            $data_arr[] = [
                "api_name" => optional($value->api)->api_name ?? 'N/A',
                "amount" => $value->amount,
                "apiCharge" => $value->apiCharge,
                "apiCommission" => $value->apiCommission,
                "retailerCharge" => $value->retailerCharge,
                "retailerComm" => $value->retailerComm,
                "totalProfit" => $value->totalProfit,
            ];
        }

        // Return proper response
        return response()->json([
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordswithFilter,
            "aaData" => $data_arr
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'error' => true,
            'message' => $e->getMessage()
        ], 500);
    }
}

}
