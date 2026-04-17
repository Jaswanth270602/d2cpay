<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use http\Env\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use App\Models\User;
use \Crypt;
use Carbon;
use App\Models\Scheme;
use App\Models\Staffpermission;
use App\Models\Sitesetting;
use Illuminate\Support\Facades\Cache;
use App\Library\MemberLibrary;
use Helpers;

class CompanystaffController extends Controller
{

    public function __construct()
    {
        $this->company_id = Helpers::company_id()->id;
        $companies = Helpers::company_id();
        $this->company_id = $companies->id;
        $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
        if ($sitesettings) {
            $this->brand_name = $sitesettings->brand_name;
            $this->backend_template_id = $sitesettings->backend_template_id;
        } else {
            $this->brand_name = "";
            $this->backend_template_id = 1;
        }
    }

    function welcome (){
        if (Auth::User()->role_id == 1){
            $data = array(
                'page_title' => 'Company Staff Permission',
                'url' => url('admin/company-staff/get-users'),
            );
            if ($this->backend_template_id == 1) {
                return view('admin.company-staff.welcome')->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.company-staff.welcome')->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.company-staff.welcome')->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.company-staff.welcome')->with($data);
            } else {
                return redirect()->back();
            }

        }else{
            return redirect()->back();
        }
    }

    function get_users (Request $request){
        if (Auth::User()->role_id == 1){
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

            // Total records

            $totalRecords = User::select('count(*) as allcount')
                ->whereIn('id', $my_down_member)
                ->where('role_id', 2)
                ->count();
            $totalRecordswithFilter = User::select('count(*) as allcount')
                ->whereIn('id', $my_down_member)
                ->where('role_id', 2)
                ->where(function ($query) use ($searchValue) {
                    $query->where('name', 'like', '%' .$searchValue . '%')
                        ->orWhere('mobile', 'like', '%' .$searchValue . '%')
                        ->orWhere('email', 'like', '%' .$searchValue . '%');
                })->count();
            // Fetch records
            $records = User::orderBy($columnName,$columnSortOrder)
                ->whereIn('id', $my_down_member)
                ->where('role_id', 2)
                ->orderBy('id', 'DESC')
                ->where(function ($query) use ($searchValue) {
                    $query->where('name', 'like', '%' .$searchValue . '%')
                        ->orWhere('mobile', 'like', '%' .$searchValue . '%')
                        ->orWhere('email', 'like', '%' .$searchValue . '%');
                })->skip($start)
                ->take($rowperpage)
                ->get();


            $data_arr = array();
            foreach($records as $value){
                if ($value->status_id == 1){
                    $status = '<span class="badge badge-success">Enabled</span>';
                }else{
                    $status = '<span class="badge badge-danger">Disabled</span>';
                }
                if ($value->mobile_verified == 1){
                    $mobile = '<span>'. $value->mobile .'</span>';
                }else{
                    $mobile = '<span style="color:red;" alt="mobile number not verified">'. $value->mobile .'</span>';
                }

                $statement_url = url('admin/company-staff/permission').'/'.Crypt::encrypt($value->id);
                $data_arr[] = array(
                    "id" => $value->id,
                    "joining_date" => "$value->created_at",
                    "name" => $value->name.' '. $value->last_name,
                    "mobile_number" => $mobile,
                    "member_type" => $value->role->role_title,
                    "user_balance" => number_format($value->balance->user_balance, 2),
                    "status" => $status,
                    "permission" => '<a href="'.$statement_url.'" class="btn btn-primary btn-sm">Permission</a>',
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

        }else{
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function permission ($encrypt_id){
        if (Auth::User()->role_id == 1){
            $user_id = Crypt::decrypt($encrypt_id);
            $staffpermissions = Staffpermission::where('user_id', $user_id)->first();
            if (empty($staffpermissions)){
                $now = new \DateTime();
                $ctime = $now->format('Y-m-d H:i:s');
                Staffpermission::insert([
                    'user_id' => $user_id,
                    'created_at' => $ctime,
                    'status_id' => 1,
                ]);
            }
            $staffpermissions = Staffpermission::where('user_id', $user_id)->first();
            if ($staffpermissions){
                $data = array(
                    'page_title' => $staffpermissions->user->name.' '. $staffpermissions->user->last_name,
                    'user_id' => $staffpermissions->user_id,
                    'bank_master_permission' => $staffpermissions->bank_master_permission,
                    'role_master_permission' => $staffpermissions->role_master_permission,
                    'status_master_permission' => $staffpermissions->status_master_permission,
                    'service_master_permission' => $staffpermissions->service_master_permission,
                    'payment_method_master_permission' => $staffpermissions->payment_method_master_permission,
                    'payout_beneficiary_master_permission' => $staffpermissions->payout_beneficiary_master_permission,
                    'agent_onboarding_list_permission' => $staffpermissions->agent_onboarding_list_permission,
                    'contact_enquiry_permission' => $staffpermissions->contact_enquiry_permission,
                    'provider_master_permission' => $staffpermissions->provider_master_permission,
                    'api_master_permission' => $staffpermissions->api_master_permission,
                    'add_api_permission' => $staffpermissions->add_api_permission,
                    'update_api_permission' => $staffpermissions->update_api_permission,
                    'denomination_wise_permission' => $staffpermissions->denomination_wise_permission,
                    'number_series_permission' => $staffpermissions->number_series_permission,
                    'state_wise_permission' => $staffpermissions->state_wise_permission,
                    'backup_api_permission' => $staffpermissions->backup_api_permission,
                    'api_switching_permission' => $staffpermissions->api_switching_permission,
                    'user_operator_limit_permission' => $staffpermissions->user_operator_limit_permission,
                    'company_settings_permission' => $staffpermissions->company_settings_permission,
                    'site_settings_permission' => $staffpermissions->site_settings_permission,
                    'sms_template_permission' => $staffpermissions->sms_template_permission,
                    'package_settings_permission' => $staffpermissions->package_settings_permission,
                    'bank_settings_permission' => $staffpermissions->bank_settings_permission,
                    'logo_upload_permission' => $staffpermissions->logo_upload_permission,
                    'service_banner_permission' => $staffpermissions->service_banner_permission,
                    'notification_settings_permission' => $staffpermissions->notification_settings_permission,
                    'dynamic_page_permission' => $staffpermissions->dynamic_page_permission,
                    'front_banners_permission' => $staffpermissions->front_banners_permission,
                    'whatsapp_notification_permission' => $staffpermissions->whatsapp_notification_permission,
                    'member_permission' => $staffpermissions->member_permission,
                    'create_member_permission' => $staffpermissions->create_member_permission,
                    'update_member_permission' => $staffpermissions->update_member_permission,
                    'reset_password_permission' => $staffpermissions->reset_password_permission,
                    'viewUser_kyc_permission' => $staffpermissions->viewUser_kyc_permission,
                    'update_kyc_permission' => $staffpermissions->update_kyc_permission,
                    'download_member_permission' => $staffpermissions->download_member_permission,
                    'member_statement_permission' => $staffpermissions->member_statement_permission,
                    'send_statement_permission' => $staffpermissions->send_statement_permission,
                    'suspended_user_permission' => $staffpermissions->suspended_user_permission,
                    'not_working_users_permission' => $staffpermissions->not_working_users_permission,
                    'all_transaction_report_permission' => $staffpermissions->all_transaction_report_permission,
                    'update_transaction_permission' => $staffpermissions->update_transaction_permission,
                    'view_api_logs_permission' => $staffpermissions->view_api_logs_permission,
                    'recharge_report_permission' => $staffpermissions->recharge_report_permission,
                    'pancard_report_permission' => $staffpermissions->pancard_report_permission,
                    'auto_payment_report_permission' => $staffpermissions->auto_payment_report_permission,
                    'pending_transaction_permission' => $staffpermissions->pending_transaction_permission,
                    'profit_distribution_permission' => $staffpermissions->profit_distribution_permission,
                    'refund_manager_permission' => $staffpermissions->refund_manager_permission,
                    'api_summary_permission' => $staffpermissions->api_summary_permission,
                    'operator_wise_sale_permission' => $staffpermissions->operator_wise_sale_permission,
                    'aeps_report_permission' => $staffpermissions->aeps_report_permission,
                    'payout_settlement_permission' => $staffpermissions->payout_settlement_permission,
                    'aeps_operator_report_permission' => $staffpermissions->aeps_operator_report_permission,
                    'account_validate_report_permission' => $staffpermissions->account_validate_report_permission,
                    'money_transfer_report_permission' => $staffpermissions->money_transfer_report_permission,
                    'money_operator_report_permission' => $staffpermissions->money_operator_report_permission,
                    'balance_transfer_permission' => $staffpermissions->balance_transfer_permission,
                    'balance_return_permission' => $staffpermissions->balance_return_permission,
                    'payment_request_view_permission' => $staffpermissions->payment_request_view_permission,
                    'payment_request_permission' => $staffpermissions->payment_request_permission,
                    'pending_dispute_permission' => $staffpermissions->pending_dispute_permission,
                    'dispute_chat_permission' => $staffpermissions->dispute_chat_permission,
                    'dispute_update_permission' => $staffpermissions->dispute_update_permission,
                    'solve_dispute_permission' => $staffpermissions->solve_dispute_permission,
                    'reopen_dispute_permission' => $staffpermissions->reopen_dispute_permission,
                );
                if ($this->backend_template_id == 1) {
                    return view('admin.company-staff.permission')->with($data);
                } elseif ($this->backend_template_id == 2) {
                    return view('themes2.admin.company-staff.permission')->with($data);
                } elseif ($this->backend_template_id == 3) {
                    return view('themes3.admin.company-staff.permission')->with($data);
                } elseif ($this->backend_template_id == 4) {
                    return view('themes4.admin.company-staff.permission')->with($data);
                } else {
                    return redirect()->back();
                }
            }else{
                return redirect()->back();
            }
        }else{
            return redirect()->back();
        }
    }

    function update_permission (Request $request){
        if (Auth::User()->role_id == 1){
            $rules = array(
                'user_id' => 'required|exists:staffpermissions,user_id',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
            }
            $user_id = $request->user_id;
            Staffpermission::where('user_id', $user_id)->update([
                'bank_master_permission' => $request->bank_master_permission,
                'role_master_permission' => $request->role_master_permission,
                'status_master_permission' => $request->status_master_permission,
                'service_master_permission' => $request->service_master_permission,
                'payment_method_master_permission' => $request->payment_method_master_permission,
                'payout_beneficiary_master_permission' => $request->payout_beneficiary_master_permission,
                'agent_onboarding_list_permission' => $request->agent_onboarding_list_permission,
                'contact_enquiry_permission' => $request->contact_enquiry_permission,
                'provider_master_permission' => $request->provider_master_permission,
                'api_master_permission' => $request->api_master_permission,
                'add_api_permission' => $request->add_api_permission,
                'update_api_permission' => $request->update_api_permission,
                'denomination_wise_permission' => $request->denomination_wise_permission,
                'number_series_permission' => $request->number_series_permission,
                'state_wise_permission' => $request->state_wise_permission,
                'backup_api_permission' => $request->backup_api_permission,
                'api_switching_permission' => $request->api_switching_permission,
                'user_operator_limit_permission' => $request->user_operator_limit_permission,
                'company_settings_permission' => $request->company_settings_permission,
                'site_settings_permission' => $request->site_settings_permission,
                'sms_template_permission' => $request->sms_template_permission,
                'package_settings_permission' => $request->package_settings_permission,
                'bank_settings_permission' => $request->bank_settings_permission,
                'logo_upload_permission' => $request->logo_upload_permission,
                'service_banner_permission' => $request->service_banner_permission,
                'notification_settings_permission' => $request->notification_settings_permission,
                'dynamic_page_permission' => $request->dynamic_page_permission,
                'front_banners_permission' => $request->front_banners_permission,
                'whatsapp_notification_permission' => $request->whatsapp_notification_permission,
                'member_permission' => $request->member_permission,
                'create_member_permission' => $request->create_member_permission,
                'update_member_permission' => $request->update_member_permission,
                'reset_password_permission' => $request->reset_password_permission,
                'viewUser_kyc_permission' => $request->viewUser_kyc_permission,
                'update_kyc_permission' => $request->update_kyc_permission,
                'download_member_permission' => $request->download_member_permission,
                'member_statement_permission' => $request->member_statement_permission,
                'send_statement_permission' => $request->send_statement_permission,
                'suspended_user_permission' => $request->suspended_user_permission,
                'not_working_users_permission' => $request->not_working_users_permission,
                'all_transaction_report_permission' => $request->all_transaction_report_permission,
                'update_transaction_permission' => $request->update_transaction_permission,
                'view_api_logs_permission' => $request->view_api_logs_permission,
                'recharge_report_permission' => $request->recharge_report_permission,
                'pancard_report_permission' => $request->pancard_report_permission,
                'auto_payment_report_permission' => $request->auto_payment_report_permission,
                'pending_transaction_permission' => $request->pending_transaction_permission,
                'profit_distribution_permission' => $request->profit_distribution_permission,
                'refund_manager_permission' => $request->refund_manager_permission,
                'api_summary_permission' => $request->api_summary_permission,
                'operator_wise_sale_permission' => $request->operator_wise_sale_permission,
                'aeps_report_permission' => $request->aeps_report_permission,
                'payout_settlement_permission' => $request->payout_settlement_permission,
                'aeps_operator_report_permission' => $request->aeps_operator_report_permission,
                'account_validate_report_permission' => $request->account_validate_report_permission,
                'money_transfer_report_permission' => $request->money_transfer_report_permission,
                'money_operator_report_permission' => $request->money_operator_report_permission,
                'balance_transfer_permission' => $request->balance_transfer_permission,
                'balance_return_permission' => $request->balance_return_permission,
                'payment_request_view_permission' => $request->payment_request_view_permission,
                'payment_request_permission' => $request->payment_request_permission,
                'pending_dispute_permission' => $request->pending_dispute_permission,
                'dispute_chat_permission' => $request->dispute_chat_permission,
                'dispute_update_permission' => $request->dispute_update_permission,
                'solve_dispute_permission' => $request->solve_dispute_permission,
                'reopen_dispute_permission' => $request->reopen_dispute_permission,
            ]);
            return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
        }else{
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }
}
