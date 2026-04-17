<?php

namespace App\library {

    use DB;
    use Auth;
    use Notification;
    use App\Models\Apicheckbalance;
    use Mail;
    use Validator;
    use App\Models\Staffpermission;
    use Maatwebsite\Excel\Facades\Excel;
    use App\Exports\ChildstatementExport;

    class PermissionLibrary
    {


        function getPermission()
        {
            $user_id = Auth::id();
            $staffpermissions = Staffpermission::where('user_id', $user_id)->first();
            if ($staffpermissions) {
                return [
                    //Master
                    'bank_master_permission' => $staffpermissions->bank_master_permission,
                    'role_master_permission' => $staffpermissions->role_master_permission,
                    'status_master_permission' => $staffpermissions->status_master_permission,
                    'service_master_permission' => $staffpermissions->service_master_permission,
                    'payment_method_master_permission' => $staffpermissions->payment_method_master_permission,
                    'payout_beneficiary_master_permission' => $staffpermissions->payout_beneficiary_master_permission,
                    'agent_onboarding_list_permission' => $staffpermissions->agent_onboarding_list_permission,
                    'contact_enquiry_permission' => $staffpermissions->contact_enquiry_permission,
                    //Api Master
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
                    //Settings
                    'company_settings_permission' => $staffpermissions->company_settings_permission,
                    'site_settings_permission' => $staffpermissions->site_settings_permission,
                    'sms_template_permission' => $staffpermissions->sms_template_permission,
                    'package_settings_permission' => $staffpermissions->package_settings_permission,
                    'bank_settings_permission' => $staffpermissions->bank_settings_permission,
                    'logo_upload_permission' => $staffpermissions->logo_upload_permission,
                    'service_banner_permission' => $staffpermissions->service_banner_permission,
                    'notification_settings_permission' => $staffpermissions->notification_settings_permission,
                    //Website Master
                    'dynamic_page_permission' => $staffpermissions->dynamic_page_permission,
                    'front_banners_permission' => $staffpermissions->front_banners_permission,
                    'whatsapp_notification_permission' => $staffpermissions->whatsapp_notification_permission,
                    // Member
                    'member_permission' => $staffpermissions->member_permission,
                    'create_member_permission' => $staffpermissions->create_member_permission,
                    'update_member_permission' => $staffpermissions->update_member_permission,
                    'reset_password_permission' => $staffpermissions->reset_password_permission,
                    'viewUser_kyc_permission' => $staffpermissions->viewUser_kyc_permission,
                    'update_kyc_permission' => $staffpermissions->update_kyc_permission,
                    'download_member_permission' => $staffpermissions->download_member_permission,
                    'member_statement_permission' => $staffpermissions->member_statement_permission,
                    'suspended_user_permission' => $staffpermissions->suspended_user_permission,
                    'not_working_users_permission' => $staffpermissions->not_working_users_permission,
                    //Report
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
                    //Payment
                    'balance_transfer_permission' => $staffpermissions->balance_transfer_permission,
                    'balance_return_permission' => $staffpermissions->balance_return_permission,
                    'payment_request_view_permission' => $staffpermissions->payment_request_view_permission,
                    'payment_request_permission' => $staffpermissions->payment_request_permission,
                    //Dispute
                    'pending_dispute_permission' => $staffpermissions->pending_dispute_permission,
                    'dispute_chat_permission' => $staffpermissions->dispute_chat_permission,
                    'dispute_update_permission' => $staffpermissions->dispute_update_permission,
                    'solve_dispute_permission' => $staffpermissions->solve_dispute_permission,
                    'reopen_dispute_permission' => $staffpermissions->reopen_dispute_permission,
                ];
            } else {
                return [
                    //Master
                    'bank_master_permission' => 0,
                    'role_master_permission' => 0,
                    'status_master_permission' => 0,
                    'service_master_permission' => 0,
                    'payment_method_master_permission' => 0,
                    'payout_beneficiary_master_permission' => 0,
                    'agent_onboarding_list_permission' => 0,
                    'contact_enquiry_permission' => 0,
                    //Api Master
                    'provider_master_permission' => 0,
                    'api_master_permission' => 0,
                    'add_api_permission' => 0,
                    'update_api_permission' => 0,
                    'denomination_wise_permission' => 0,
                    'number_series_permission' => 0,
                    'state_wise_permission' => 0,
                    'backup_api_permission' => 0,
                    'api_switching_permission' => 0,
                    'user_operator_limit_permission' => 0,
                    //Settings
                    'company_settings_permission' => 0,
                    'site_settings_permission' => 0,
                    'sms_template_permission' => 0,
                    'package_settings_permission' => 0,
                    'bank_settings_permission' => 0,
                    'logo_upload_permission' => 0,
                    'service_banner_permission' => 0,
                    'notification_settings_permission' => 0,
                    //Website Master
                    'dynamic_page_permission' => 0,
                    'front_banners_permission' => 0,
                    'whatsapp_notification_permission' => 0,
                    // Member
                    'member_permission' => 0,
                    'create_member_permission' => 0,
                    'update_member_permission' => 0,
                    'reset_password_permission' => 0,
                    'viewUser_kyc_permission' => 0,
                    'update_kyc_permission' => 0,
                    'download_member_permission' => 0,
                    'member_statement_permission' => 0,
                    'suspended_user_permission' => 0,
                    'not_working_users_permission' => 0,
                    //Report
                    'all_transaction_report_permission' => 0,
                    'update_transaction_permission' => 0,
                    'view_api_logs_permission' => 0,
                    'recharge_report_permission' => 0,
                    'pancard_report_permission' => 0,
                    'auto_payment_report_permission' => 0,
                    'pending_transaction_permission' => 0,
                    'profit_distribution_permission' => 0,
                    'refund_manager_permission' => 0,
                    'api_summary_permission' => 0,
                    'operator_wise_sale_permission' => 0,
                    'aeps_report_permission' => 0,
                    'payout_settlement_permission' => 0,
                    'aeps_operator_report_permission' => 0,
                    'account_validate_report_permission' => 0,
                    'money_transfer_report_permission' => 0,
                    'money_operator_report_permission' => 0,
                    //Payment
                    'balance_transfer_permission' => 0,
                    'balance_return_permission' => 0,
                    'payment_request_view_permission' => 0,
                    'payment_request_permission' => 0,
                    //Dispute
                    'pending_dispute_permission' => 0,
                    'dispute_chat_permission' => 0,
                    'dispute_update_permission' => 0,
                    'solve_dispute_permission' => 0,
                    'reopen_dispute_permission' => 0,
                ];
            }
        }


    }
}