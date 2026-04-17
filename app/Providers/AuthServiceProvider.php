<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\View\View;
use Illuminate\Support\Facades;
use Illuminate\Support\Facades\Auth;
use App\Models\Staffpermission;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    /*public function boot(): void
    {
        Facades\View::composer('welcome', function (View $view) {
            // ...
        });
    }*/

    public function boot() {
        Facades\View::composer('*', function(View $view) {
            $user_id = Auth::id();
            $staffpermissions = Staffpermission::where('user_id', $user_id)->first();
            if ($staffpermissions){
                $data = array(
                    // Master
                    'permission_bank_master' => $staffpermissions->bank_master_permission,
                    'permission_role_master' => $staffpermissions->role_master_permission,
                    'permission_status_master' => $staffpermissions->status_master_permission,
                    'permission_service_master' => $staffpermissions->service_master_permission,
                    'permission_payment_method_master' => $staffpermissions->payment_method_master_permission,
                    'permission_payout_beneficiary_master' => $staffpermissions->payout_beneficiary_master_permission,
                    'permission_agent_onboarding_list' => $staffpermissions->agent_onboarding_list_permission,
                    'permission_contact_enquiry' => $staffpermissions->contact_enquiry_permission,
                    // Api Master
                    'permission_provider_master' => $staffpermissions->provider_master_permission,
                    'permission_api_master' => $staffpermissions->api_master_permission,
                    'permission_add_api' => $staffpermissions->add_api_permission,
                    'permission_update_api' => $staffpermissions->update_api_permission,
                    'permission_denomination_wise' => $staffpermissions->denomination_wise_permission,
                    'permission_number_series' => $staffpermissions->number_series_permission,
                    'permission_state_wise' => $staffpermissions->state_wise_permission,
                    'permission_backup_api' => $staffpermissions->backup_api_permission,
                    'permission_api_switching' => $staffpermissions->api_switching_permission,
                    'permission_user_operator_limit' => $staffpermissions->user_operator_limit_permission,
                    //settings
                    'permission_company_settings' => $staffpermissions->company_settings_permission,
                    'permission_site_settings' => $staffpermissions->site_settings_permission,
                    'permission_sms_template' => $staffpermissions->sms_template_permission,
                    'permission_package_settings' => $staffpermissions->package_settings_permission,
                    'permission_bank_settings' => $staffpermissions->bank_settings_permission,
                    'permission_logo_upload' => $staffpermissions->logo_upload_permission,
                    'permission_service_banner' => $staffpermissions->service_banner_permission,
                    'permission_notification_settings' => $staffpermissions->notification_settings_permission,
                    //website Master
                    'permission_dynamic_page' => $staffpermissions->dynamic_page_permission,
                    'permission_front_banners' => $staffpermissions->front_banners_permission,
                    'permission_whatsapp_notification' => $staffpermissions->whatsapp_notification_permission,
                    //Member
                    'permission_member' => $staffpermissions->member_permission,
                    'permission_create_member' => $staffpermissions->create_member_permission,
                    'permission_update_member' => $staffpermissions->update_member_permission,
                    'permission_reset_password' => $staffpermissions->reset_password_permission,
                    'permission_viewUser_kyc' => $staffpermissions->viewUser_kyc_permission,
                    'permission_update_kyc' => $staffpermissions->update_kyc_permission,
                    'permission_download_member' => $staffpermissions->download_member_permission,
                    'permission_member_statement' => $staffpermissions->member_statement_permission,
                    'permission_send_statement' => $staffpermissions->send_statement_permission,
                    'permission_suspended_user' => $staffpermissions->suspended_user_permission,
                    'permission_not_working_users' => $staffpermissions->not_working_users_permission,
                    //reports
                    'permission_all_transaction_report' => $staffpermissions->all_transaction_report_permission,
                    'permission_update_transaction' => $staffpermissions->update_transaction_permission,
                    'permission_view_api_logs' => $staffpermissions->view_api_logs_permission,
                    'permission_recharge_report' => $staffpermissions->recharge_report_permission,
                    'permission_pancard_report' => $staffpermissions->pancard_report_permission,
                    'permission_auto_payment_report' => $staffpermissions->auto_payment_report_permission,
                    'permission_pending_transaction' => $staffpermissions->pending_transaction_permission,
                    'permission_profit_distribution' => $staffpermissions->profit_distribution_permission,
                    'permission_refund_manager' => $staffpermissions->refund_manager_permission,
                    'permission_api_summary' => $staffpermissions->api_summary_permission,
                    'permission_operator_wise_sale' => $staffpermissions->operator_wise_sale_permission,
                    'permission_aeps_report' => $staffpermissions->aeps_report_permission,
                    'permission_payout_settlement' => $staffpermissions->payout_settlement_permission,
                    'permission_aeps_operator_report' => $staffpermissions->aeps_operator_report_permission,
                    'permission_account_validate_report' => $staffpermissions->account_validate_report_permission,
                    'permission_money_transfer_report' => $staffpermissions->money_transfer_report_permission,
                    'permission_money_operator_report' => $staffpermissions->money_operator_report_permission,
                    //Payment
                    'permission_balance_transfer' => $staffpermissions->balance_transfer_permission,
                    'permission_balance_return' => $staffpermissions->balance_return_permission,
                    'permission_payment_request_view' => $staffpermissions->payment_request_view_permission,
                    'permission_payment_request' => $staffpermissions->payment_request_permission,
                    //Dispute
                    'permission_pending_dispute' => $staffpermissions->pending_dispute_permission,
                    'permission_dispute_chat' => $staffpermissions->dispute_chat_permission,
                    'permission_dispute_update' => $staffpermissions->dispute_update_permission,
                    'permission_solve_dispute' => $staffpermissions->solve_dispute_permission,
                    'permission_reopen_dispute' => $staffpermissions->reopen_dispute_permission,
                );
            }else{
                $data = array(
                    // Master
                    'permission_bank_master' => 0,
                    'permission_role_master' => 0,
                    'permission_status_master' => 0,
                    'permission_service_master' => 0,
                    'permission_payment_method_master' => 0,
                    'permission_payout_beneficiary_master' => 0,
                    'permission_agent_onboarding_list' => 0,
                    'permission_contact_enquiry' => 0,
                    // Api Master
                    'permission_provider_master' => 0,
                    'permission_api_master' => 0,
                    'permission_add_api' => 0,
                    'permission_update_api' => 0,
                    'permission_denomination_wise' => 0,
                    'permission_number_series' => 0,
                    'permission_state_wise' => 0,
                    'permission_backup_api' => 0,
                    'permission_api_switching' => 0,
                    'permission_user_operator_limit' => 0,
                    //settings
                    'permission_company_settings' => 0,
                    'permission_site_settings' => 0,
                    'permission_sms_template' => 0,
                    'permission_package_settings' => 0,
                    'permission_bank_settings' => 0,
                    'permission_logo_upload' => 0,
                    'permission_service_banner' => 0,
                    'permission_notification_settings' => 0,
                    //website Master
                    'permission_dynamic_page' => 0,
                    'permission_front_banners' => 0,
                    'permission_whatsapp_notification' => 0,
                    //Member
                    'permission_member' => 0,
                    'permission_create_member' => 0,
                    'permission_update_member' => 0,
                    'permission_reset_password' => 0,
                    'permission_viewUser_kyc' => 0,
                    'permission_update_kyc' => 0,
                    'permission_download_member' => 0,
                    'permission_member_statement' => 0,
                    'permission_send_statement' => 0,
                    'permission_suspended_user' => 0,
                    'permission_not_working_users' => 0,
                    //reports
                    'permission_all_transaction_report' => 0,
                    'permission_update_transaction' => 0,
                    'permission_view_api_logs' => 0,
                    'permission_recharge_report' => 0,
                    'permission_pancard_report' => 0,
                    'permission_auto_payment_report' => 0,
                    'permission_pending_transaction' => 0,
                    'permission_profit_distribution' => 0,
                    'permission_refund_manager' => 0,
                    'permission_api_summary' => 0,
                    'permission_operator_wise_sale' => 0,
                    'permission_aeps_report' => 0,
                    'permission_payout_settlement' => 0,
                    'permission_aeps_operator_report' => 0,
                    'permission_account_validate_report' => 0,
                    'permission_money_transfer_report' => 0,
                    'permission_money_operator_report' => 0,
                    //Payment
                    'permission_balance_transfer' => 0,
                    'permission_balance_return' => 0,
                    'permission_payment_request_view' => 0,
                    'permission_payment_request' => 0,
                    //Dispute
                    'permission_pending_dispute' => 0,
                    'permission_dispute_chat' => 0,
                    'permission_dispute_update' => 0,
                    'permission_solve_dispute' => 0,
                    'permission_reopen_dispute' => 0,
                );
            }
            $view->with($data);
        });

        //
    }
}
