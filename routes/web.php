<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [App\Http\Controllers\FrontController::class, 'welcome'])->name('home');
Route::get('/pages/{company_id}/{slug}', [App\Http\Controllers\FrontController::class, 'dynamic_page'])->name('dynamic_page');
Route::get('/contact-us', [App\Http\Controllers\FrontController::class, 'contact_us'])->name('contact_us');
Auth::routes(['register' => false, 'reset' => false, 'verify' => false, 'confirm' => false]);
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index']);
Route::post('/save-contact-enquiry', [App\Http\Controllers\FrontController::class, 'save_contact_enquiry'])->name('save_contact_enquiry');
// register here
Route::get('/sign-up/{slug?}', [App\Http\Controllers\Auth\SignupController::class, 'sign_up'])->name('sign_up');
Route::post('/sign-up', [App\Http\Controllers\Auth\SignupController::class, 'register_now'])->name('register_now');
// login here
Route::post('/login-now', [App\Http\Controllers\Auth\LoginController::class, 'login_now'])->name('login_now');
Route::post('/resend-login-otp', [App\Http\Controllers\Auth\LoginController::class, 'resend_login_otp'])->name('resend_login_otp');
Route::post('/login-with-otp', [App\Http\Controllers\Auth\LoginController::class, 'login_with_otp'])->name('login_with_otp');
Route::get('/forgot-password', [App\Http\Controllers\Auth\LoginController::class, 'forgot_password']);
Route::post('/forgot-password-otp', [App\Http\Controllers\Auth\LoginController::class, 'forgot_password_otp']);
Route::post('/confirm-forgot-password', [App\Http\Controllers\Auth\LoginController::class, 'confirm_forgot_password']);

Route::group(['prefix' => 'sign-up/v1'], function () {
    Route::get('/welcome', [App\Http\Controllers\Auth\SignupWithAadharController::class, 'sign_up']);
    Route::post('/send-aadhar-otp', [App\Http\Controllers\Auth\SignupWithAadharController::class, 'sendAadharOTP']);
    Route::post('/aadhar-otp-verify', [App\Http\Controllers\Auth\SignupWithAadharController::class, 'aadharOtpVerify']);
    Route::post('/register-now', [App\Http\Controllers\Auth\SignupWithAadharController::class, 'registerNow']);
});

Route::middleware(['auth'])->group(function () {
    // for admin dashboard
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'dashboard']);
        Route::get('/dashboard-data-api', [App\Http\Controllers\Admin\DashboardController::class, 'dashboard_data_api']);
        Route::get('/dashboard-chart-api', [App\Http\Controllers\Admin\DashboardController::class, 'dashboard_chart_api']);
        Route::get('/dashboard-details-api', [App\Http\Controllers\Admin\DashboardController::class, 'dashboard_details_api']);
        Route::get('/activity-logs', [App\Http\Controllers\Admin\DashboardController::class, 'activity_logs']);
        Route::get('/top-seller', [App\Http\Controllers\Admin\DashboardController::class, 'top_seller']);
        Route::get('/get-service-wise-sales', [App\Http\Controllers\Admin\DashboardController::class, 'getServiceWiseSales']);
        // my profile here
        Route::get('/my-profile', [App\Http\Controllers\Admin\ProfileController::class, 'my_profile']);
        Route::post('/change-password', [App\Http\Controllers\Admin\ProfileController::class, 'change_password']);
        Route::post('/update-profile', [App\Http\Controllers\Admin\ProfileController::class, 'update_profile']);
        Route::post('/update-profile-photo', [App\Http\Controllers\Admin\ProfileController::class, 'update_profile_photo']);
        Route::post('/update-shop-photo', [App\Http\Controllers\Admin\ProfileController::class, 'update_shop_photo']);
        Route::post('/update-gst-regisration-photo', [App\Http\Controllers\Admin\ProfileController::class, 'update_gst_regisration_photo']);
        Route::post('/update-pancard-photo', [App\Http\Controllers\Admin\ProfileController::class, 'update_pancard_photo']);
        Route::post('/cancel-cheque-photo', [App\Http\Controllers\Admin\ProfileController::class, 'cancel_cheque_photo']);
        Route::post('/address-proof-photo', [App\Http\Controllers\Admin\ProfileController::class, 'address_proof_photo']);
        // generate transaction pin
        Route::get('/transaction-pin', [App\Http\Controllers\Admin\ProfileController::class, 'transaction_pin']);
        Route::post('/send-transaction-pin-otp', [App\Http\Controllers\Admin\ProfileController::class, 'send_transaction_pin_otp']);
        Route::post('/create-transaction-pin', [App\Http\Controllers\Admin\ProfileController::class, 'create_transaction_pin']);
        // admin company settings
        Route::get('/company-settings', [App\Http\Controllers\Admin\CompanyController::class, 'company_settings']);
        Route::post('/update-company-seeting', [App\Http\Controllers\Admin\CompanyController::class, 'update_company_seeting']);
        Route::get('/logo-upload', [App\Http\Controllers\Admin\CompanyController::class, 'logo_upload']);
        Route::post('/store-logo', [App\Http\Controllers\Admin\CompanyController::class, 'store_logo']);
        Route::post('/view-company-active-services', [App\Http\Controllers\Admin\CompanyController::class, 'view_company_active_services']);
        Route::get('/service-banner', [App\Http\Controllers\Admin\CompanyController::class, 'service_banner']);
        Route::post('/store-service-banner', [App\Http\Controllers\Admin\CompanyController::class, 'store_service_banner']);
        Route::post('/delete-service-banner', [App\Http\Controllers\Admin\CompanyController::class, 'delete_service_banner']);

        // WhitelabelController routes
        Route::get('/white-label', [App\Http\Controllers\Admin\WhitelabelController::class, 'white_label']);
        Route::post('/view-white-label-details', [App\Http\Controllers\Admin\WhitelabelController::class, 'view_white_label_details']);
        Route::post('/create-white-label', [App\Http\Controllers\Admin\WhitelabelController::class, 'create_white_label']);
        Route::post('/update-white-label', [App\Http\Controllers\Admin\WhitelabelController::class, 'update_white_label']);

        // PackageController routes
        Route::get('/package-settings', [App\Http\Controllers\Admin\PackageController::class, 'package_settings']);
        Route::post('/view-package-details', [App\Http\Controllers\Admin\PackageController::class, 'view_package_details']);
        Route::post('/update-package', [App\Http\Controllers\Admin\PackageController::class, 'update_package']);
        Route::post('/create-new-package', [App\Http\Controllers\Admin\PackageController::class, 'create_new_package']);
        Route::post('/delete-package', [App\Http\Controllers\Admin\PackageController::class, 'delete_package']);
        Route::post('/copy-package', [App\Http\Controllers\Admin\PackageController::class, 'copy_package']);

        // CommissionController routes
        Route::post('/commission-setup', [App\Http\Controllers\Admin\CommissionController::class, 'commission_setup']);
        Route::post('/set-operator-commission', [App\Http\Controllers\Admin\CommissionController::class, 'set_operator_commission']);
        Route::post('/view-operator-commission', [App\Http\Controllers\Admin\CommissionController::class, 'view_operator_commission']);
        Route::post('/update-operator-commission', [App\Http\Controllers\Admin\CommissionController::class, 'update_operator_commission']);
        Route::post('/store-commission', [App\Http\Controllers\Admin\CommissionController::class, 'store_commission']);
        Route::post('/delete-commission-slab', [App\Http\Controllers\Admin\CommissionController::class, 'delete_commission_slab']);
        Route::post('/store-bulk-commission', [App\Http\Controllers\Admin\CommissionController::class, 'store_bulk_commission']);

        // BankController routes
        Route::get('/bank-settings', [App\Http\Controllers\Admin\BankController::class, 'bank_settings']);
        Route::post('/view-bank-details', [App\Http\Controllers\Admin\BankController::class, 'view_bank_details']);
        Route::post('/update-bank', [App\Http\Controllers\Admin\BankController::class, 'update_bank']);
        Route::post('/add-bank', [App\Http\Controllers\Admin\BankController::class, 'add_bank']);

        // MemberController routes
        Route::get('/member-list/{id}', [App\Http\Controllers\Admin\MemberController::class, 'member_list']);
        Route::get('/parent-down-users/{role_slug}/{parent_id}', [App\Http\Controllers\Admin\MemberController::class, 'parent_down_users']);
        Route::get('/member-list-api', [App\Http\Controllers\Admin\MemberController::class, 'member_list_api']);
        Route::post('/view-members-details', [App\Http\Controllers\Admin\MemberController::class, 'view_members_details']);
        Route::post('/get-distric-by-state', [App\Http\Controllers\Admin\MemberController::class, 'get_distric_by_state']);
        Route::get('/create-user/{id}', [App\Http\Controllers\Admin\MemberController::class, 'create_user']);
        Route::get('/view-update-users/{id}', [App\Http\Controllers\Admin\MemberController::class, 'view_update_users']);
        Route::post('/update-members', [App\Http\Controllers\Admin\MemberController::class, 'update_members']);
        Route::post('/reset-password', [App\Http\Controllers\Admin\MemberController::class, 'reset_password']);
        Route::post('/store-members', [App\Http\Controllers\Admin\MemberController::class, 'store_members']);
        Route::get('/view-user-kyc/{id}', [App\Http\Controllers\Admin\MemberController::class, 'view_user_kyc']);
        Route::post('/update-kyc', [App\Http\Controllers\Admin\MemberController::class, 'update_kyc']);
        Route::get('/suspended-users', [App\Http\Controllers\Admin\MemberController::class, 'suspended_users']);
        Route::get('/suspended-user-api', [App\Http\Controllers\Admin\MemberController::class, 'suspended_user_api']);
        Route::post('/create-pancard-id', [App\Http\Controllers\Admin\MemberController::class, 'create_pancard_id']);
        Route::post('/update-dropdown-package', [App\Http\Controllers\Admin\MemberController::class, 'update_dropdown_package']);
        Route::post('/update-dropdown-parent', [App\Http\Controllers\Admin\MemberController::class, 'update_dropdown_parent']);
        Route::get('/not-working-users', [App\Http\Controllers\Admin\MemberController::class, 'not_working_users']);
        Route::get('/not-working-users-api', [App\Http\Controllers\Admin\MemberController::class, 'not_working_users_api']);
        Route::post('/refresh-scheme', [App\Http\Controllers\Admin\MemberController::class, 'refresh_scheme']);
        Route::get('/export-member', [App\Http\Controllers\Admin\MemberController::class, 'export_member']);
        Route::get('/all-user-list/{slug?}', [App\Http\Controllers\Admin\MemberController::class, 'all_user_list']);
        Route::get('/all-user-list-api', [App\Http\Controllers\Admin\MemberController::class, 'all_user_list_api']);
        Route::post('/force-logout-all-users', [App\Http\Controllers\Admin\MemberController::class, 'force_logout_all_users']);
        Route::post('/view-user-active-services', [App\Http\Controllers\Admin\MemberController::class, 'view_user_active_services']);

        // ApimasterController routes
        Route::get('/provider-master', [App\Http\Controllers\Admin\ApimasterController::class, 'provider_master']);
        Route::get('/provider-master-api', [App\Http\Controllers\Admin\ApimasterController::class, 'provider_master_api']);
        Route::post('/view-provider', [App\Http\Controllers\Admin\ApimasterController::class, 'view_provider']);
        Route::post('/update-provider', [App\Http\Controllers\Admin\ApimasterController::class, 'update_provider']);
        Route::post('/add-provider', [App\Http\Controllers\Admin\ApimasterController::class, 'add_provider']);
        Route::post('/store-provider-logo', [App\Http\Controllers\Admin\ApimasterController::class, 'store_provider_logo']);
        Route::get('/api-master', [App\Http\Controllers\Admin\ApimasterController::class, 'api_master']);
        Route::post('/view-api-credentials', [App\Http\Controllers\Admin\ApimasterController::class, 'viewApiCredentials']);
        Route::post('/update-api-credentials', [App\Http\Controllers\Admin\ApimasterController::class, 'updateApiCredentials']);
        Route::post('/create-new-api', [App\Http\Controllers\Admin\ApimasterController::class, 'create_new_api']);
        Route::post('/view-api-details', [App\Http\Controllers\Admin\ApimasterController::class, 'view_api_details']);
        Route::post('/update-api-details', [App\Http\Controllers\Admin\ApimasterController::class, 'update_api_details']);
        Route::get('/webhook-setting/{id}', [App\Http\Controllers\Admin\ApimasterController::class, 'webhook_setting']);
        Route::post('/update-webhook-url', [App\Http\Controllers\Admin\ApimasterController::class, 'update_webhook_url']);
        Route::get('/response-setting/{id}', [App\Http\Controllers\Admin\ApimasterController::class, 'response_setting']);
        Route::post('/add-new-responses', [App\Http\Controllers\Admin\ApimasterController::class, 'add_new_responses']);
        Route::post('/view-api-responses', [App\Http\Controllers\Admin\ApimasterController::class, 'view_api_responses']);
        Route::post('/update-api-responses', [App\Http\Controllers\Admin\ApimasterController::class, 'update_api_responses']);
        Route::post('/delete-api-responses', [App\Http\Controllers\Admin\ApimasterController::class, 'delete_api_responses']);
        Route::get('/webhooks-logs/{id}', [App\Http\Controllers\Admin\ApimasterController::class, 'webhooks_logs']);
        Route::get('/denomination-wise-api', [App\Http\Controllers\Admin\ApimasterController::class, 'denomination_wise_api']);
        Route::post('/save-denomination-wise-api', [App\Http\Controllers\Admin\ApimasterController::class, 'save_denomination_wise_api']);
        Route::post('/view-denomination-wise-api', [App\Http\Controllers\Admin\ApimasterController::class, 'view_denomination_wise_api']);
        Route::post('/update-denomination-wise-api', [App\Http\Controllers\Admin\ApimasterController::class, 'update_denomination_wise_api']);
        Route::post('/delete-denomination-wise-api', [App\Http\Controllers\Admin\ApimasterController::class, 'delete_denomination_wise_api']);
        Route::post('/view-check-balance-api', [App\Http\Controllers\Admin\ApimasterController::class, 'view_check_balance_api']);
        Route::post('/update-check-balance-api', [App\Http\Controllers\Admin\ApimasterController::class, 'update_check_balance_api']);
        Route::post('/get-api-balance', [App\Http\Controllers\Admin\ApimasterController::class, 'get_api_balance']);
        Route::get('/view-api-provider/{id}', [App\Http\Controllers\Admin\ApimasterController::class, 'view_api_provider']);
        Route::post('/view-api-master-provider', [App\Http\Controllers\Admin\ApimasterController::class, 'view_api_master_provider']);
        Route::post('/update-api-provider', [App\Http\Controllers\Admin\ApimasterController::class, 'update_api_provider']);
        Route::get('/number-series-master', [App\Http\Controllers\Admin\ApimasterController::class, 'number_series_master']);
        Route::post('/view-number-series', [App\Http\Controllers\Admin\ApimasterController::class, 'view_number_series']);
        Route::post('/update-number-series', [App\Http\Controllers\Admin\ApimasterController::class, 'update_number_series']);
        Route::post('/add-number-series', [App\Http\Controllers\Admin\ApimasterController::class, 'add_number_series']);
        Route::get('/state-wise-api', [App\Http\Controllers\Admin\ApimasterController::class, 'state_wise_api']);
        Route::get('/state-provider-setting/{id}', [App\Http\Controllers\Admin\ApimasterController::class, 'state_provider_setting']);
        Route::post('/update-state-wise-api-status', [App\Http\Controllers\Admin\ApimasterController::class, 'update_state_wise_api_status']);
        Route::post('/update-state-wise-api-id', [App\Http\Controllers\Admin\ApimasterController::class, 'update_state_wise_api_id']);
        Route::get('/backup-api-master', [App\Http\Controllers\Admin\ApimasterController::class, 'backup_api_master']);
        Route::post('/save-backup-api', [App\Http\Controllers\Admin\ApimasterController::class, 'save_backup_api']);
        Route::post('/delete-backup-api', [App\Http\Controllers\Admin\ApimasterController::class, 'delete_backup_api']);
        Route::post('/view-backup-api', [App\Http\Controllers\Admin\ApimasterController::class, 'view_backup_api']);
        Route::post('/update-backup-api', [App\Http\Controllers\Admin\ApimasterController::class, 'update_backup_api']);
        Route::get('/api-switching', [App\Http\Controllers\Admin\ApimasterController::class, 'api_switching']);
        Route::post('/update-api-switching', [App\Http\Controllers\Admin\ApimasterController::class, 'update_api_switching']);
        Route::get('/user-operator-limit', [App\Http\Controllers\Admin\ApimasterController::class, 'user_operator_limit']);
        Route::get('/user-operator-limit-api', [App\Http\Controllers\Admin\ApimasterController::class, 'user_operator_limit_api']);
        Route::post('/get-user-by-role', [App\Http\Controllers\Admin\ApimasterController::class, 'get_user_by_role']);
        Route::get('/view-operator-limit/{id}', [App\Http\Controllers\Admin\ApimasterController::class, 'view_operator_limit']);
        Route::post('/update-operator-limit', [App\Http\Controllers\Admin\ApimasterController::class, 'update_operator_limit']);

        Route::get('/bank-transfer-switching', [App\Http\Controllers\Admin\ApimasterController::class, 'bankTransferSwitching']);
        Route::post('/bank-transfer-switching-store', [App\Http\Controllers\Admin\ApimasterController::class, 'bankTransferSwitchingStore']);
        Route::post('/bank-transfer-switching-delete', [App\Http\Controllers\Admin\ApimasterController::class, 'bankTransferSwitchingDelete']);

        Route::get('/all-api-balance', [App\Http\Controllers\Admin\ApibalanceController::class, 'allApiBal']);

        // MasterController routes
        Route::get('/bank-master', [App\Http\Controllers\Admin\MasterController::class, 'bank_master']);
        Route::post('/view-bank-master', [App\Http\Controllers\Admin\MasterController::class, 'view_bank_master']);
        Route::post('/update-bank-master', [App\Http\Controllers\Admin\MasterController::class, 'update_bank_master']);
        Route::post('/add-banks', [App\Http\Controllers\Admin\MasterController::class, 'add_banks']);
        Route::get('/role-master', [App\Http\Controllers\Admin\MasterController::class, 'role_master']);
        Route::post('/view-role-master', [App\Http\Controllers\Admin\MasterController::class, 'view_role_master']);
        Route::post('/update-role-master', [App\Http\Controllers\Admin\MasterController::class, 'update_role_master']);
        Route::get('/status-master', [App\Http\Controllers\Admin\MasterController::class, 'status_master']);
        Route::post('/view-status-master', [App\Http\Controllers\Admin\MasterController::class, 'view_status_master']);
        Route::post('/update-status-master', [App\Http\Controllers\Admin\MasterController::class, 'update_status_master']);
        Route::get('/service-master', [App\Http\Controllers\Admin\MasterController::class, 'service_master']);
        Route::post('/view-serivce-master', [App\Http\Controllers\Admin\MasterController::class, 'view_serivce_master']);
        Route::post('/update-service-master', [App\Http\Controllers\Admin\MasterController::class, 'update_service_master']);
        Route::post('/upload-service-master-icon', [App\Http\Controllers\Admin\MasterController::class, 'upload_service_master_icon']);
        Route::get('/payment-method', [App\Http\Controllers\Admin\MasterController::class, 'payment_method']);
        Route::post('/view-payment-method', [App\Http\Controllers\Admin\MasterController::class, 'view_payment_method']);
        Route::post('/update-payment-method', [App\Http\Controllers\Admin\MasterController::class, 'update_payment_method']);
        Route::post('/add-payment-method', [App\Http\Controllers\Admin\MasterController::class, 'add_payment_method']);
        Route::get('/payout-beneficiary-master', [App\Http\Controllers\Admin\MasterController::class, 'payout_beneficiary_master']);
        Route::post('/update-payout-beneficiary', [App\Http\Controllers\Admin\MasterController::class, 'update_payout_beneficiary']);
        Route::get('/contact-enquiry', [App\Http\Controllers\Admin\MasterController::class, 'contact_enquiry']);
        Route::post('/delete-contact-enquiry', [App\Http\Controllers\Admin\MasterController::class, 'delete_contact_enquiry']);
        Route::get('/cashfree-gateway-master', [App\Http\Controllers\Admin\MasterController::class, 'cashfree_gateway_master']);
        Route::post('/view-cashfree-gateway-master', [App\Http\Controllers\Admin\MasterController::class, 'view_cashfree_gateway_master']);
        Route::post('/update-cashfree-gateway-master', [App\Http\Controllers\Admin\MasterController::class, 'update_cashfree_gateway_master']);

        Route::group(['prefix' => 'gateway-charges'], function () {
            Route::get('/welcome', [App\Http\Controllers\Admin\MasterController::class, 'gateway_charges']);
            Route::post('/view-charges-details', [App\Http\Controllers\Admin\MasterController::class, 'view_gateway_charges']);
            Route::post('/update-charges-details', [App\Http\Controllers\Admin\MasterController::class, 'update_gateway_charges_details']);
        });

        Route::get('/agent-onboarding-list', [App\Http\Controllers\Admin\MasterController::class, 'agent_onboarding_list']);
        Route::get('/agent-onboarding-list-api', [App\Http\Controllers\Admin\MasterController::class, 'agent_onboarding_list_api']);
        Route::post('/agent-onboarding-user-details', [App\Http\Controllers\Admin\MasterController::class, 'agent_onboarding_user_details']);
        Route::post('/save-agent-onboarding', [App\Http\Controllers\Admin\MasterController::class, 'save_agent_onboarding']);
        Route::post('/view-agent-onboarding', [App\Http\Controllers\Admin\MasterController::class, 'view_agent_onboarding']);
        Route::post('/update-agent-onboarding', [App\Http\Controllers\Admin\MasterController::class, 'update_agent_onboarding']);

        // broadcast by admin
        Route::get('/broadcast', [App\Http\Controllers\Admin\MasterController::class, 'broadcast']);
        Route::post('/save-broadcast', [App\Http\Controllers\Admin\MasterController::class, 'save_broadcast']);

        Route::group(['prefix' => 'report/v1', 'middleware' => 'auth'], function () {
            Route::get('/all-transaction-report', [App\Http\Controllers\Admin\ReportController::class, 'all_transaction_report']);
            Route::get('/all-transaction-report-api', [App\Http\Controllers\Admin\ReportController::class, 'all_transaction_report_api']);
            // Dynamic Report
            Route::get('/welcome/{report_slug}', [App\Http\Controllers\Admin\ReportController::class, 'welcome']);
            Route::get('/search/{report_slug}', [App\Http\Controllers\Admin\ReportController::class, 'search_report']);
            // Close dynamic report
            Route::post('/view-recharge-details', [App\Http\Controllers\Admin\ReportController::class, 'view_recharge_details']);
            Route::post('/view-transaction-logs', [App\Http\Controllers\Admin\ReportController::class, 'view_transaction_logs']);
            Route::post('/recharge-update-for-refund', [App\Http\Controllers\Admin\ReportController::class, 'recharge_update_for_refund']);
            Route::post('/update-selected-transaction', [App\Http\Controllers\Admin\ReportController::class, 'update_selected_transaction']);
            Route::get('/pending-transaction', [App\Http\Controllers\Admin\ReportController::class, 'pending_transaction']);
            Route::get('/pending-transaction-api', [App\Http\Controllers\Admin\ReportController::class, 'pending_transaction_api']);
            Route::get('/profit-distribution', [App\Http\Controllers\Admin\ReportController::class, 'profit_distribution']);
            Route::get('/profit-distribution-api', [App\Http\Controllers\Admin\ReportController::class, 'profit_distribution_api']);
            Route::get('/refund-manager', [App\Http\Controllers\Admin\ReportController::class, 'refund_manager']);
            Route::get('/search-refund-manager', [App\Http\Controllers\Admin\ReportController::class, 'search_refund_manager']);
            Route::get('/ledger-report', [App\Http\Controllers\Admin\ReportController::class, 'ledger_report']);
            Route::get('/ledger-report-api', [App\Http\Controllers\Admin\ReportController::class, 'ledger_report_api']);
            Route::get('/user-ledger-report/{id}', [App\Http\Controllers\Admin\ReportController::class, 'user_ledger_report']);
            Route::get('/user-ledger-report-api', [App\Http\Controllers\Admin\ReportController::class, 'user_ledger_report_api']);
            Route::get('/debit-report', [App\Http\Controllers\Admin\ReportController::class, 'debit_report']);
            Route::get('/debit-report-api', [App\Http\Controllers\Admin\ReportController::class, 'debit_report_api']);
            Route::get('/credit-report', [App\Http\Controllers\Admin\ReportController::class, 'credit_report']);
            Route::get('/credit-report-api', [App\Http\Controllers\Admin\ReportController::class, 'credit_report_api']);
            Route::post('/find-ip-location', [App\Http\Controllers\Admin\ReportController::class, 'find_ip_location']);
            Route::get('/api-profit-loss-report', [App\Http\Controllers\Admin\ReportController::class, 'apiProfitLossReport']);
            Route::get('/api-profit-loss-report-api', [App\Http\Controllers\Admin\ReportController::class, 'apiProfitLossReportApi']);
        });
        // Transfer controller
        Route::get('/purchase-balance', [App\Http\Controllers\Admin\TransferController::class, 'purchase_balance']);
        Route::get('/purchase-balance-api', [App\Http\Controllers\Admin\TransferController::class, 'purchase_balance_api']);
        Route::post('/purchase-balance-now', [App\Http\Controllers\Admin\TransferController::class, 'purchase_balance_now']);

        Route::get('/balance-transfer', [App\Http\Controllers\Admin\TransferController::class, 'balance_transfer']);
        Route::get('/balance-transfer-api', [App\Http\Controllers\Admin\TransferController::class, 'balance_transfer_api']);
        Route::post('/view-transfer-users', [App\Http\Controllers\Admin\TransferController::class, 'view_transfer_users']);
        Route::post('/balance-transfer-now', [App\Http\Controllers\Admin\TransferController::class, 'balance_transfer_now']);

        Route::get('/balance-return', [App\Http\Controllers\Admin\TransferController::class, 'balance_return']);
        Route::post('/balance-return-now', [App\Http\Controllers\Admin\TransferController::class, 'balance_return_now']);

        Route::get('/balance-return-request', [App\Http\Controllers\Admin\TransferController::class, 'balance_return_request']);
        Route::post('/view-return-request', [App\Http\Controllers\Admin\TransferController::class, 'view_return_request']);
        Route::post('/approve-payment-return-request', [App\Http\Controllers\Admin\TransferController::class, 'approve_payment_return_request']);
        // payin to payout
        Route::get('/payin-to-payout', [App\Http\Controllers\Admin\TransferController::class, 'payinToPayout']);
        Route::post('/payin-to-payout', [App\Http\Controllers\Admin\TransferController::class, 'payinToPayoutStore']);

        // Payment request controller
        Route::get('/payment-request', [App\Http\Controllers\Admin\PaymentrequestController::class, 'payment_request']);
        Route::post('/save-payment-request', [App\Http\Controllers\Admin\PaymentrequestController::class, 'save_payment_request']);

        Route::get('/payment-request-view', [App\Http\Controllers\Admin\PaymentrequestController::class, 'payment_request_view']);
        Route::get('/payment-request-view-api', [App\Http\Controllers\Admin\PaymentrequestController::class, 'payment_request_view_api']);
        Route::post('/view-payment-request', [App\Http\Controllers\Admin\PaymentrequestController::class, 'view_payment_request']);
        Route::post('/update-payment-request', [App\Http\Controllers\Admin\PaymentrequestController::class, 'update_payment_request']);
        Route::post('/payment-request-edit-now', [App\Http\Controllers\Admin\PaymentrequestController::class, 'payment_request_edit_now']);

        // Dispute controller
        Route::get('/pending-dispute', [App\Http\Controllers\Admin\DisputeController::class, 'pending_dispute']);
        Route::post('/dispute-transaction', [App\Http\Controllers\Admin\DisputeController::class, 'dispute_transaction']);
        Route::post('/view-dispute-conversation', [App\Http\Controllers\Admin\DisputeController::class, 'view_dispute_conversation']);
        Route::post('/get-dispute-chat', [App\Http\Controllers\Admin\DisputeController::class, 'get_dispute_chat']);
        Route::post('/send-chat-message', [App\Http\Controllers\Admin\DisputeController::class, 'send_chat_message']);
        Route::post('/update-complaint-status', [App\Http\Controllers\Admin\DisputeController::class, 'update_complaint_status']);
        Route::get('/solve-dispute', [App\Http\Controllers\Admin\DisputeController::class, 'solve_dispute']);
        Route::get('/solve-dispute-api', [App\Http\Controllers\Admin\DisputeController::class, 'solve_dispute_api']);


        // Website master controller
        Route::get('/home-page-content', [App\Http\Controllers\Admin\WebsiteMasterController::class, 'home_page_content']);
        Route::get('/dynamic-page', [App\Http\Controllers\Admin\WebsiteMasterController::class, 'dynamic_page']);
        Route::get('/front-banners', [App\Http\Controllers\Admin\WebsiteMasterController::class, 'front_banners']);
        Route::post('/store-front-banner', [App\Http\Controllers\Admin\WebsiteMasterController::class, 'store_front_banner']);
        Route::post('/delete-front-banner', [App\Http\Controllers\Admin\WebsiteMasterController::class, 'delete_front_banner']);
        Route::get('/create-navigation', [App\Http\Controllers\Admin\WebsiteMasterController::class, 'create_navigation']);
        Route::post('/store-navigation', [App\Http\Controllers\Admin\WebsiteMasterController::class, 'store_navigation']);
        Route::get('/edit-navigation/{id}', [App\Http\Controllers\Admin\WebsiteMasterController::class, 'edit_navigation']);
        Route::post('/update-navigation', [App\Http\Controllers\Admin\WebsiteMasterController::class, 'update_navigation']);
        Route::post('/delete-navigation', [App\Http\Controllers\Admin\WebsiteMasterController::class, 'delete_navigation']);
        Route::get('/add-content/{id}', [App\Http\Controllers\Admin\WebsiteMasterController::class, 'add_content']);
        Route::post('/update-content', [App\Http\Controllers\Admin\WebsiteMasterController::class, 'update_content']);

        // Income controller
        Route::group(['prefix' => 'income'], function () {
            Route::get('/user-income/{id}', [App\Http\Controllers\Admin\IncomeController::class, 'user_income']);
            Route::get('/user-income-api/{id}', [App\Http\Controllers\Admin\IncomeController::class, 'user_income_api']);
            Route::get('/my-income', [App\Http\Controllers\Admin\IncomeController::class, 'my_income']);
            Route::get('/my-income-api', [App\Http\Controllers\Admin\IncomeController::class, 'my_income_api']);
            Route::get('/operator-wise-sale', [App\Http\Controllers\Admin\IncomeController::class, 'operator_wise_sale']);
            Route::get('/api-summary-report', [App\Http\Controllers\Admin\IncomeController::class, 'api_summary_report']);
        });

        // Profit controller
        Route::get('/my-recharge-commission', [App\Http\Controllers\Admin\ProfitController::class, 'recharge_commission']);
        Route::get('/service-wise-commission/{id}', [App\Http\Controllers\Admin\ProfitController::class, 'service_wise_commission']);
        Route::post('/view-my-comm-slab', [App\Http\Controllers\Admin\ProfitController::class, 'view_my_comm_slab']);

        // Notification controller
        Route::group(['prefix' => 'notification'], function () {
            Route::get('/welcome', [App\Http\Controllers\Admin\NotificationController::class, 'welcome']);
            Route::post('/send-notification', [App\Http\Controllers\Admin\NotificationController::class, 'send_notification']);
            Route::get('/mark-all-read', [App\Http\Controllers\Admin\NotificationController::class, 'mark_all_read']);
            Route::get('/view/{id}', [App\Http\Controllers\Admin\NotificationController::class, 'view_notification']);
        });

        Route::group(['prefix' => 'download/v1', 'middleware' => 'auth'], function () {
            Route::post('/file-download', [App\Http\Controllers\Admin\DownloadController::class, 'download_file']);
            Route::post('/member-download', [App\Http\Controllers\Admin\DownloadController::class, 'member_download']);
            Route::post('/payment-request-view', [App\Http\Controllers\Admin\DownloadController::class, 'payment_request_view']);
            Route::post('/agent-onboarding-download', [App\Http\Controllers\Admin\DownloadController::class, 'agent_onboarding_download']);
        });

        Route::group(['prefix' => 'send-mail', 'middleware' => 'auth'], function () {
            Route::post('/send-statement', [App\Http\Controllers\Admin\SendmailController::class, 'send_statement']);
        });

        Route::group(['prefix' => 'invoice', 'middleware' => 'auth'], function () {
            Route::get('/gst-invoice', [App\Http\Controllers\Admin\InvoiceController::class, 'gst_invoice']);
            Route::post('/create-invoice', [App\Http\Controllers\Admin\InvoiceController::class, 'create_invoice']);
            Route::get('/generate-invoice/{id}', [App\Http\Controllers\Admin\InvoiceController::class, 'generate_invoice']);
        });

        Route::group(['prefix' => 'site-setting', 'middleware' => 'auth'], function () {
            Route::get('/welcome', [App\Http\Controllers\Admin\SitesettingController::class, 'welcome']);
            Route::post('/update-settings', [App\Http\Controllers\Admin\SitesettingController::class, 'update_settings']);
        });

        Route::group(['prefix' => 'sms-template', 'middleware' => 'auth'], function () {
            Route::get('/welcome', [App\Http\Controllers\Admin\SmstemplateController::class, 'welcome']);
            Route::post('/view-template', [App\Http\Controllers\Admin\SmstemplateController::class, 'view_template']);
            Route::post('/update-template', [App\Http\Controllers\Admin\SmstemplateController::class, 'update_template']);
        });

        Route::group(['prefix' => 'ecommerce', 'middleware' => 'auth'], function () {
            // EcommerceController routes
            Route::get('/main-category', [App\Http\Controllers\Admin\EcommerceController::class, 'main_category']);
            Route::post('/save-category', [App\Http\Controllers\Admin\EcommerceController::class, 'save_category']);
            Route::post('/view-category', [App\Http\Controllers\Admin\EcommerceController::class, 'view_category']);
            Route::post('/update-category', [App\Http\Controllers\Admin\EcommerceController::class, 'update_category']);

            Route::get('/sub-category', [App\Http\Controllers\Admin\EcommerceController::class, 'sub_category']);
            Route::post('/save-sub-category', [App\Http\Controllers\Admin\EcommerceController::class, 'save_sub_category']);
            Route::post('/view-sub-category', [App\Http\Controllers\Admin\EcommerceController::class, 'view_sub_category']);
            Route::post('/update-sub-category', [App\Http\Controllers\Admin\EcommerceController::class, 'update_sub_category']);

            Route::get('/shopping-banners', [App\Http\Controllers\Admin\EcommerceController::class, 'shopping_banners']);
            Route::post('/store-shopping-banners', [App\Http\Controllers\Admin\EcommerceController::class, 'store_shopping_banners']);
            Route::post('/delete-shopping-banners', [App\Http\Controllers\Admin\EcommerceController::class, 'delete_shopping_banners']);

            // BrandController routes
            Route::get('/brands', [App\Http\Controllers\Admin\BrandController::class, 'brands']);
            Route::post('/save-brands', [App\Http\Controllers\Admin\BrandController::class, 'save_brands']);
            Route::post('/view-brand', [App\Http\Controllers\Admin\BrandController::class, 'view_brand']);
            Route::post('/update-brands', [App\Http\Controllers\Admin\BrandController::class, 'update_brands']);

            // ProductController routes
            Route::get('/product-list', [App\Http\Controllers\Admin\ProductController::class, 'product_list']);
            Route::get('/product-list-api', [App\Http\Controllers\Admin\ProductController::class, 'product_list_api']);
            Route::get('/add-products', [App\Http\Controllers\Admin\ProductController::class, 'add_products']);
            Route::post('/get-sub-category', [App\Http\Controllers\Admin\ProductController::class, 'get_sub_category']);
            Route::post('/save-products', [App\Http\Controllers\Admin\ProductController::class, 'save_products']);
            Route::get('/update-product/{id}', [App\Http\Controllers\Admin\ProductController::class, 'update_product']);
            Route::post('/products-update-now', [App\Http\Controllers\Admin\ProductController::class, 'products_update_now']);

            Route::get('/add-product-image/{id}', [App\Http\Controllers\Admin\ProductController::class, 'add_product_image']);
            Route::post('/save-product-image', [App\Http\Controllers\Admin\ProductController::class, 'save_product_image']);
            Route::post('/delete-product-image', [App\Http\Controllers\Admin\ProductController::class, 'delete_product_image']);
            Route::post('/view-product-image', [App\Http\Controllers\Admin\ProductController::class, 'view_product_image']);
            Route::post('/update-product-image', [App\Http\Controllers\Admin\ProductController::class, 'update_product_image']);

            // OrderController routes
            Route::get('/order-report', [App\Http\Controllers\Admin\OrderController::class, 'order_report']);
            Route::get('/order-report-api', [App\Http\Controllers\Admin\OrderController::class, 'order_report_api']);
            Route::post('/view-order-product', [App\Http\Controllers\Admin\OrderController::class, 'view_order_product']);
            Route::post('/view-track-order', [App\Http\Controllers\Admin\OrderController::class, 'view_track_order']);
            Route::get('/product-report', [App\Http\Controllers\Admin\OrderController::class, 'product_report']);
            Route::get('/product-report-api', [App\Http\Controllers\Admin\OrderController::class, 'product_report_api']);
            Route::post('/view-order-product-details', [App\Http\Controllers\Admin\OrderController::class, 'view_order_product_details']);
            Route::post('/view-update-product', [App\Http\Controllers\Admin\OrderController::class, 'view_update_product']);
            Route::post('/update-product-delivery-status', [App\Http\Controllers\Admin\OrderController::class, 'update_product_delivery_status']);
            Route::get('/track-order', [App\Http\Controllers\Admin\OrderController::class, 'track_order']);
        });
        Route::group(['prefix' => 'vendor-payment', 'middleware' => 'auth'], function () {
            Route::get('/welcome', [App\Http\Controllers\Admin\VendorpaymentController::class, 'welcome']);
            Route::post('/add-api', [App\Http\Controllers\Admin\VendorpaymentController::class, 'add_api']);
            Route::post('/view-beneficiary', [App\Http\Controllers\Admin\VendorpaymentController::class, 'view_beneficiary']);
            Route::post('/add-beneficiary', [App\Http\Controllers\Admin\VendorpaymentController::class, 'add_beneficiary']);
            Route::post('/delete-beneficiary', [App\Http\Controllers\Admin\VendorpaymentController::class, 'delete_beneficiary']);
            Route::post('/view-transfer-details', [App\Http\Controllers\Admin\VendorpaymentController::class, 'view_transfer_details']);
            Route::post('/transfer-now', [App\Http\Controllers\Admin\VendorpaymentController::class, 'transfer_now']);
        });

        Route::group(['prefix' => 'whatsapp', 'middleware' => 'auth'], function () {
            Route::get('/role-wise', [App\Http\Controllers\Admin\WhatsappController::class, 'role_wise']);
            Route::post('/role-wise', [App\Http\Controllers\Admin\WhatsappController::class, 'role_wise_send']);
            Route::post('/role-wise-image', [App\Http\Controllers\Admin\WhatsappController::class, 'role_wise_send_image']);
        });

        Route::group(['prefix' => 'company-staff', 'middleware' => 'auth'], function () {
            Route::get('/welcome', [App\Http\Controllers\Admin\CompanystaffController::class, 'welcome']);
            Route::get('/get-users', [App\Http\Controllers\Admin\CompanystaffController::class, 'get_users']);
            Route::get('/permission/{id}', [App\Http\Controllers\Admin\CompanystaffController::class, 'permission']);
            Route::post('/update-permission', [App\Http\Controllers\Admin\CompanystaffController::class, 'update_permission']);
        });

        Route::group(['prefix' => 'api-commission/v1', 'middleware' => 'auth'], function () {
            Route::get('/welcome/{api_id}', [App\Http\Controllers\Admin\ApicommissionController::class, 'welcome']);
            Route::post('/view-providers', [App\Http\Controllers\Admin\ApicommissionController::class, 'view_providers']);
            Route::post('/save-commission', [App\Http\Controllers\Admin\ApicommissionController::class, 'save_commission']);
            Route::post('/view-provider-commission', [App\Http\Controllers\Admin\ApicommissionController::class, 'view_provider_commission']);
            Route::post('/update-commission', [App\Http\Controllers\Admin\ApicommissionController::class, 'update_commission']);
            Route::post('/delete-record', [App\Http\Controllers\Admin\ApicommissionController::class, 'delete_record']);
        });

    });

    // for user dashboard
    Route::prefix('agent')->group(function () {
        // Dashboard routes
        Route::get('/dashboard', [App\Http\Controllers\Agent\DashboardController::class, 'dashboard']);
        Route::get('/dashboard-details-api', [App\Http\Controllers\Agent\DashboardController::class, 'dashboard_details_api']);
        Route::get('/dashboard-chart-api', [App\Http\Controllers\Agent\DashboardController::class, 'dashboard_chart_api']);
        Route::get('/activity-logs', [App\Http\Controllers\Agent\DashboardController::class, 'activity_logs']);
        Route::get('/send-mail', [App\Http\Controllers\Agent\DashboardController::class, 'send_mail']);
        Route::get('/check-cashe', [App\Http\Controllers\Agent\DashboardController::class, 'check_cashe']);
        Route::post('/get-wallet-balance', [App\Http\Controllers\Agent\DashboardController::class, 'getWalletBalance']);

        // My Profile routes
        Route::get('/my-profile', [App\Http\Controllers\Agent\ProfileController::class, 'my_profile']);
        Route::post('/change-password', [App\Http\Controllers\Agent\ProfileController::class, 'change_password']);
        Route::post('/update-profile', [App\Http\Controllers\Agent\ProfileController::class, 'update_profile']);
        Route::post('/update-profile-photo', [App\Http\Controllers\Agent\ProfileController::class, 'update_profile_photo']);
        Route::post('/update-shop-photo', [App\Http\Controllers\Agent\ProfileController::class, 'update_shop_photo']);
        Route::post('/update-gst-regisration-photo', [App\Http\Controllers\Agent\ProfileController::class, 'update_gst_regisration_photo']);
        Route::post('/update-pancard-photo', [App\Http\Controllers\Agent\ProfileController::class, 'update_pancard_photo']);
        Route::post('/cancel-cheque-photo', [App\Http\Controllers\Agent\ProfileController::class, 'cancel_cheque_photo']);
        Route::post('/address-proof-photo', [App\Http\Controllers\Agent\ProfileController::class, 'address_proof_photo']);
        Route::post('/get-distric-by-state', [App\Http\Controllers\Agent\ProfileController::class, 'get_distric_by_state']);
        Route::post('/update-verify-profile', [App\Http\Controllers\Agent\ProfileController::class, 'update_verify_profile']);
        Route::post('/verify-mobile', [App\Http\Controllers\Agent\ProfileController::class, 'verify_mobile']);
        Route::post('/verify-mobile-otp', [App\Http\Controllers\Agent\ProfileController::class, 'verify_mobile_otp']);
        Route::get('/view-kyc', [App\Http\Controllers\Agent\ProfileController::class, 'view_kyc']);
        Route::get('/my-settings', [App\Http\Controllers\Agent\ProfileController::class, 'my_settings']);
        Route::post('/save-settings', [App\Http\Controllers\Agent\ProfileController::class, 'save_settings']);
        Route::get('/transaction-pin', [App\Http\Controllers\Agent\ProfileController::class, 'transaction_pin']);
        Route::get('/latlong-security', [App\Http\Controllers\Agent\ProfileController::class, 'latlongSecurity']);

        Route::group(['prefix' => 'telecom/v1'], function () {
            Route::get('/welcome/{slug}', [App\Http\Controllers\Agent\ServiceController::class, 'welcome']);
        });

        // epr pay2all bbps
        Route::group(['prefix' => 'bbps/v1'], function () {
            Route::get('/welcome/{slug}', [App\Http\Controllers\Agent\BbpsV1Controller::class, 'welcome']);
            Route::post('/biller-params', [App\Http\Controllers\Agent\BbpsV1Controller::class, 'billerParams']);
            Route::post('/fatch-bill', [App\Http\Controllers\Agent\BbpsV1Controller::class, 'fatchBill']);
            Route::post('/view-bill', [App\Http\Controllers\Agent\BbpsV1Controller::class, 'viewBill']);
            Route::post('/pay-now', [App\Http\Controllers\Agent\BbpsV1Controller::class, 'payNow']);
        });


        Route::get('/prepaid-mobile', [App\Http\Controllers\Agent\ServiceController::class, 'prepaid_mobile']);
        Route::get('/dth', [App\Http\Controllers\Agent\ServiceController::class, 'dth']);
        Route::get('/postpaid', [App\Http\Controllers\Agent\ServiceController::class, 'postpaid']);
        Route::get('/electricity', [App\Http\Controllers\Agent\ServiceController::class, 'electricity']);
        Route::get('/landline', [App\Http\Controllers\Agent\ServiceController::class, 'landline']);
        Route::get('/water', [App\Http\Controllers\Agent\ServiceController::class, 'water']);
        Route::get('/gas', [App\Http\Controllers\Agent\ServiceController::class, 'gas']);
        Route::get('/fastag-recharge', [App\Http\Controllers\Agent\ServiceController::class, 'fastag_recharge']);
        Route::get('/insurance', [App\Http\Controllers\Agent\ServiceController::class, 'insurance']);
        Route::get('/loan-payment', [App\Http\Controllers\Agent\ServiceController::class, 'loan_payment']);
        Route::get('/broadband', [App\Http\Controllers\Agent\ServiceController::class, 'broadband']);
        Route::get('/subscription', [App\Http\Controllers\Agent\ServiceController::class, 'subscription']);
        Route::get('/housing-society', [App\Http\Controllers\Agent\ServiceController::class, 'housing_society']);
        Route::get('/cable-tv', [App\Http\Controllers\Agent\ServiceController::class, 'cable_tv']);
        Route::get('/lpg-gas', [App\Http\Controllers\Agent\ServiceController::class, 'lpg_gas']);
        Route::post('/generate-millisecond', [App\Http\Controllers\Agent\ServiceController::class, 'generate_millisecond']);
        Route::get('/certificate', [App\Http\Controllers\Agent\ServiceController::class, 'certificate']);

        // Recharge controller
        Route::post('/view-recharge-details', [App\Http\Controllers\Agent\RechargeController::class, 'view_recharge_details']);
        Route::post('/web-recharge-now', [App\Http\Controllers\Agent\RechargeController::class, 'web_recharge_now']);
        Route::post('/bbps-bill-verify', [App\Http\Controllers\Agent\RechargeController::class, 'bbps_bill_verify']);
        Route::post('/check-provider-validation', [App\Http\Controllers\Agent\RechargeController::class, 'check_provider_validation']);
        Route::get('/get-provider', [App\Http\Controllers\Agent\RechargeController::class, 'get_provider']);


        // Report controller
        Route::group(['prefix' => 'report/v1'], function () {
            Route::get('/all-transaction-report', [App\Http\Controllers\Agent\ReportController::class, 'all_transaction_report']);
            Route::get('/all-transaction-report-api', [App\Http\Controllers\Agent\ReportController::class, 'all_transaction_report_api']);
            Route::get('/ledger-report', [App\Http\Controllers\Agent\ReportController::class, 'ledger_report']);
            Route::get('/ledger-report-api', [App\Http\Controllers\Agent\ReportController::class, 'ledger_report_api']);
            Route::post('/view-transaction-details', [App\Http\Controllers\Agent\ReportController::class, 'view_recharge_details']); // Assuming this method is correctly defined
            Route::get('/welcome/{report_slug}', [App\Http\Controllers\Agent\ReportController::class, 'welcome']);
            Route::get('/search/{report_slug}', [App\Http\Controllers\Agent\ReportController::class, 'search_report']);
        });

        // Income Controller
        Route::get('/income-report', [App\Http\Controllers\Agent\SalesController::class, 'income_report']);
        Route::get('/income-report-api', [App\Http\Controllers\Agent\SalesController::class, 'income_report_api']);
        Route::get('/income-report-aeps-api', [App\Http\Controllers\Agent\SalesController::class, 'income_report_aeps_api']);

        Route::get('/operator-report', [App\Http\Controllers\Agent\SalesController::class, 'operator_report']);
        Route::get('/operator-report-api', [App\Http\Controllers\Agent\SalesController::class, 'operator_report_api']);

        // AEPS Report
        Route::get('/aeps-ledger-report', [App\Http\Controllers\Agent\AepsreportController::class, 'ledger_report']);
        Route::get('/aeps-ledger-report-api', [App\Http\Controllers\Agent\AepsreportController::class, 'ledger_report_api']);
        Route::get('/aeps-report', [App\Http\Controllers\Agent\AepsreportController::class, 'aeps_report']);
        Route::get('/aeps-report-api', [App\Http\Controllers\Agent\AepsreportController::class, 'aeps_report_api']);
        Route::get('/payout-settlement-report', [App\Http\Controllers\Agent\AepsreportController::class, 'payout_settlement_report']);
        Route::get('/payout-settlement-report-api', [App\Http\Controllers\Agent\AepsreportController::class, 'payout_settlement_report_api']);

        // Invoice
        Route::get('/transaction-receipt/{id}', [App\Http\Controllers\Agent\InvoiceController::class, 'transaction_receipt']);
        Route::get('/mobile-receipt/{id}', [App\Http\Controllers\Agent\InvoiceController::class, 'mobile_receipt']);
        Route::get('/money-receipt/{id}', [App\Http\Controllers\Agent\InvoiceController::class, 'money_receipt']);
        Route::get('/thermal-printer-receipt/{id}', [App\Http\Controllers\Agent\InvoiceController::class, 'thermal_printer_receipt']);

        // Payment Request Controller
        Route::get('/payment-request', [App\Http\Controllers\Agent\PaymentrequestController::class, 'payment_request']);
        Route::get('/balance-return-request', [App\Http\Controllers\Agent\PaymentrequestController::class, 'balance_return_request']);

        // Plan Controller
        Route::group(['prefix' => 'plan/v1'], function () {
            Route::post('/prepaid-plan', [App\Http\Controllers\Agent\PlanController::class, 'prepaid_plan']);
            Route::post('/r-offer', [App\Http\Controllers\Agent\PlanController::class, 'r_offer']);
            Route::post('/dth-customer-info', [App\Http\Controllers\Agent\PlanController::class, 'dth_customer_info']);
            Route::post('/dth-plans', [App\Http\Controllers\Agent\PlanController::class, 'dth_plans']);
            Route::post('/dth-refresh', [App\Http\Controllers\Agent\PlanController::class, 'dth_refresh']);
            Route::post('/dth-roffer', [App\Http\Controllers\Agent\PlanController::class, 'dth_roffer']);
        });
        // Dispute Controller
        Route::get('/pending-dispute', [App\Http\Controllers\Agent\DisputeController::class, 'pending_dispute']);
        Route::post('/dispute-transaction', [App\Http\Controllers\Agent\DisputeController::class, 'dispute_transaction']);
        Route::post('/view-dispute-conversation', [App\Http\Controllers\Agent\DisputeController::class, 'view_dispute_conversation']);
        Route::post('/get-dispute-chat', [App\Http\Controllers\Agent\DisputeController::class, 'get_dispute_chat']);
        Route::post('/send-chat-message', [App\Http\Controllers\Agent\DisputeController::class, 'send_chat_message']);
        Route::get('/solve-dispute', [App\Http\Controllers\Agent\DisputeController::class, 'solve_dispute']);
        Route::post('/reopen-dispute', [App\Http\Controllers\Agent\DisputeController::class, 'reopen_dispute']);

        // Profit Controller
        Route::get('/my-recharge-commission', [App\Http\Controllers\Agent\ProfitController::class, 'recharge_commission']);
        Route::get('/service-wise-commission/{id}', [App\Http\Controllers\Agent\ProfitController::class, 'service_wise_commission']);
        Route::post('/view-my-comm-slab', [App\Http\Controllers\Agent\ProfitController::class, 'view_my_comm_slab']);

        // AEPS Controller
        Route::group(['prefix' => 'aeps/v1'], function () {
            Route::get('/agent-onboarding', [App\Http\Controllers\Agent\AepsController::class, 'agent_onboarding']);
            Route::post('/save-agent-onboarding', [App\Http\Controllers\Agent\AepsController::class, 'save_agent_onboarding']);
            Route::get('/route-1', [App\Http\Controllers\Agent\AepsController::class, 'aeps_route_1']);
            Route::get('/route-2', [App\Http\Controllers\Agent\AepsController::class, 'aeps_route_2']);
            Route::get('/route-1-landing', [App\Http\Controllers\Agent\AepsController::class, 'aeps_route_1_landing']);
            Route::get('/route-2-landing', [App\Http\Controllers\Agent\AepsController::class, 'aeps_route_2_landing']);
        });

        Route::group(['prefix' => 'payout/v1', 'middleware' => 'auth'], function () {
            Route::get('/move-to-wallet', [App\Http\Controllers\Agent\PayoutController::class, 'move_to_wallet']);
            Route::get('/move-to-bank', [App\Http\Controllers\Agent\PayoutController::class, 'move_to_bank']);
            Route::post('/move-to-wallet', [App\Http\Controllers\Agent\PayoutController::class, 'move_to_wallet_web']);
            Route::post('/beneficiary-list', [App\Http\Controllers\Agent\PayoutController::class, 'beneficiary_list']);
            Route::post('/account-validate', [App\Http\Controllers\Agent\PayoutController::class, 'account_validate']);
            Route::post('/add-beneficiary', [App\Http\Controllers\Agent\PayoutController::class, 'add_beneficiary']);
            Route::post('/delete-beneficiary', [App\Http\Controllers\Agent\PayoutController::class, 'delete_beneficiary']);
            Route::post('/transfer-now', [App\Http\Controllers\Agent\PayoutController::class, 'transfer_now']);
        });

        Route::group(['prefix' => 'payout/v2', 'middleware' => 'auth'], function () {
            Route::get('/welcome', [App\Http\Controllers\Agent\DirectTransferController::class, 'welcome']);
            Route::get('/bulk-upload', [App\Http\Controllers\Agent\DirectTransferController::class, 'bulkUpload']);
            Route::post('/bulk-upload', [App\Http\Controllers\Agent\DirectTransferController::class, 'bulkUploadStore']);
            Route::post('/get-ifsc-code', [App\Http\Controllers\Agent\DirectTransferController::class, 'getIfscCode']);
            Route::post('/account-verify', [App\Http\Controllers\Agent\DirectTransferController::class, 'accountVerifyWeb']);
            Route::post('/transfer-now', [App\Http\Controllers\Agent\DirectTransferController::class, 'transferNowWeb']);
        });

        Route::group(['prefix' => 'money/v1', 'middleware' => 'auth'], function () {
            Route::get('/welcome', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'welcome']);
            Route::post('/get-customer', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'getCustomer']);
            Route::post('/add-sender', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'addSender']);
            Route::post('/confirm-sender', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'confirmSender']);
            Route::post('/sender-resend-otp', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'senderResendOtp']);
            Route::post('/get-all-beneficiary', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'getAllBeneficiary']);
            Route::post('/search-by-account', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'searchByAccount']);
            Route::post('/get-ifsc-code', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'getIfscCode']);
            Route::post('/add-beneficiary', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'addBeneficiary']);
            Route::post('/confirm-beneficiary', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'confirmBeneficiary']);
            Route::post('/delete-beneficiary', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'deleteBeneficiary']);
            Route::post('/confirm-delete-beneficiary', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'confirmDeleteBeneficiary']);
            Route::post('/account-verify', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'accountVerifyWeb']);
            Route::post('/view-account-transfer', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'viewAccountTransfer']);
            Route::post('/transfer-now', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'transferNowWeb']);
            Route::post('/get-transaction-charges', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'getTransactionCharges']);
        });

        Route::group(['prefix' => 'money/v2', 'middleware' => 'auth'], function () {
            Route::get('/welcome', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'welcome']);
            Route::post('/get-customer', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'getCustomer']);
            Route::post('/add-sender', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'addSender']);
            Route::post('/confirm-sender', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'confirmSender']);
            Route::post('/sender-resend-otp', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'senderResendOtp']);
            Route::post('/get-all-beneficiary', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'getAllBeneficiary']);
            Route::post('/search-by-account', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'searchByAccount']);
            Route::post('/get-ifsc-code', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'getIfscCode']);
            Route::post('/add-beneficiary', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'addBeneficiary']);
            Route::post('/confirm-beneficiary', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'confirmBeneficiary']);
            Route::post('/delete-beneficiary', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'deleteBeneficiary']);
            Route::post('/confirm-delete-beneficiary', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'confirmDeleteBeneficiary']);
            Route::post('/account-verify', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'accountVerifyWeb']);
            Route::post('/view-account-transfer', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'viewAccountTransfer']);
            Route::post('/transfer-now', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'transferNowWeb']);
            Route::post('/get-transaction-charges', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'getTransactionCharges']);
        });

        Route::group(['prefix' => 'notification', 'middleware' => 'auth'], function () {
            Route::get('/view/{id}', [App\Http\Controllers\Agent\NotificationController::class, 'view_notification']);
            Route::get('/mark-all-read', [App\Http\Controllers\Agent\NotificationController::class, 'mark_all_read']);
        });

        Route::group(['prefix' => 'developer', 'middleware' => 'auth'], function () {
            Route::get('/settings', [App\Http\Controllers\Agent\DeveloperController::class, 'settings']);
            Route::post('/generate-token-otp', [App\Http\Controllers\Agent\DeveloperController::class, 'generate_token_otp']);
            Route::post('/generate-token-save', [App\Http\Controllers\Agent\DeveloperController::class, 'generate_token_save']);
            Route::post('/add-ipaddress-otp', [App\Http\Controllers\Agent\DeveloperController::class, 'add_ipaddress_otp']);
            Route::post('/ip-address-save', [App\Http\Controllers\Agent\DeveloperController::class, 'ip_address_save']);
            Route::post('/update-call-back-url', [App\Http\Controllers\Agent\DeveloperController::class, 'update_call_back_url']);
            Route::get('/provider-list', [App\Http\Controllers\Agent\DeveloperController::class, 'provider_list']);
            Route::get('/call-back-logs', [App\Http\Controllers\Agent\DeveloperController::class, 'call_back_logs']);
            Route::post('/view-callback-logs', [App\Http\Controllers\Agent\DeveloperController::class, 'view_callback_logs']);
            Route::post('/resend-callback-url', [App\Http\Controllers\Agent\DeveloperController::class, 'resend_callback_url']);
            Route::get('/prepaid-and-dth', [App\Http\Controllers\Agent\DeveloperController::class, 'prepaid_and_dth']);
            Route::get('/bill-payment', [App\Http\Controllers\Agent\DeveloperController::class, 'bill_payment']);
            Route::get('/money-transfer-docs', [App\Http\Controllers\Agent\DeveloperController::class, 'money_transfer_docs']);
            Route::get('/bank-transfer-docs', [App\Http\Controllers\Agent\DeveloperController::class, 'bank_transfer_docs']);
            Route::get('/outlet-list', [App\Http\Controllers\Agent\DeveloperController::class, 'outlet_list']);
            Route::get('/outlet-list-api', [App\Http\Controllers\Agent\DeveloperController::class, 'outlet_list_api']);
            Route::post('/remove-ip-address-otp', [App\Http\Controllers\Agent\DeveloperController::class, 'remove_ip_address_otp']);
            Route::post('/remove-ip-address-save', [App\Http\Controllers\Agent\DeveloperController::class, 'remove_ip_address_save']);
            Route::get('/payout-docs', [App\Http\Controllers\Agent\DeveloperController::class, 'payoutDocs']);
            Route::get('/collect-payment', [App\Http\Controllers\Agent\DeveloperController::class, 'collectPayment']);
            Route::get('/payin-docs', [App\Http\Controllers\Agent\DeveloperController::class, 'payinDocs']);
            Route::get('/payin-two-docs', [App\Http\Controllers\Agent\DeveloperController::class, 'payinTwoDocs']);
            Route::get('/payin-five-1-docs', [App\Http\Controllers\Agent\DeveloperController::class, 'payinTwoDocs']);
            Route::get('/payin-five-docs', [App\Http\Controllers\Agent\DeveloperController::class, 'payinfiveDocs']);
            Route::get('/payin-seven-docs', [App\Http\Controllers\Agent\DeveloperController::class, 'payinSevenDocs']);
            Route::get('/payin-eight-docs', [App\Http\Controllers\Agent\DeveloperController::class, 'payinEightDocs']);

        });

        Route::group(['prefix' => 'pancard', 'middleware' => 'auth'], function () {
            Route::get('/welcome', [App\Http\Controllers\Agent\PancardController::class, 'welcome']);
            Route::post('/buy-coupons', [App\Http\Controllers\Agent\PancardController::class, 'buy_coupons']);
            Route::get('/reports', [App\Http\Controllers\Agent\PancardController::class, 'reports']);
            Route::get('/reports-api', [App\Http\Controllers\Agent\PancardController::class, 'reports_api']);
        });

        Route::group(['prefix' => 'giftcard', 'middleware' => 'auth'], function () {
            Route::get('/amazon-coupons', [App\Http\Controllers\Agent\GiftcardController::class, 'amazon_coupons']);
            Route::post('/purchase-amazon-coupons', [App\Http\Controllers\Agent\GiftcardController::class, 'purchase_amazon_coupons']);
            Route::get('/reports', [App\Http\Controllers\Agent\GiftcardController::class, 'reports']);
            Route::get('/reports-api', [App\Http\Controllers\Agent\GiftcardController::class, 'reports_api']);
        });

        Route::group(['prefix' => 'download/v1', 'middleware' => 'auth'], function () {
            Route::post('/file-download', [App\Http\Controllers\Agent\DownloadController::class, 'download_file']);
        });

        Route::group(['prefix' => 'gst', 'middleware' => 'auth'], function () {
            Route::get('/invoice', [App\Http\Controllers\Agent\InvoiceController::class, 'invoice']);
        });

        Route::group(['prefix' => 'ecommerce', 'middleware' => 'auth'], function () {
            Route::get('/page/{slug}', [App\Http\Controllers\Agent\ShopController::class, 'shop_page']);
            Route::get('/welcome', [App\Http\Controllers\Agent\ShopController::class, 'welcome']);
            Route::get('/product-details/{id}', [App\Http\Controllers\Agent\ShopController::class, 'product_details']);
            Route::post('/add-to-cart', [App\Http\Controllers\Agent\ShopController::class, 'add_to_cart']);
            Route::get('/view-cart', [App\Http\Controllers\Agent\ShopController::class, 'view_cart']);
            Route::post('/delete-product-from-cart', [App\Http\Controllers\Agent\ShopController::class, 'delete_product_from_cart']);
            Route::post('/update-quantity-in-cart', [App\Http\Controllers\Agent\ShopController::class, 'update_quantity_in_cart']);
            Route::post('/save-to-wishlist', [App\Http\Controllers\Agent\ShopController::class, 'save_to_wishlist']);
            Route::get('/my-wishlist', [App\Http\Controllers\Agent\ShopController::class, 'my_wishlist']);
            Route::get('/searchProductAjax', [App\Http\Controllers\Agent\ShopController::class, 'searchProductAjax']);
            Route::get('/search-product', [App\Http\Controllers\Agent\ShopController::class, 'search_product']);

            // Checkout
            Route::get('/checkout', [App\Http\Controllers\Agent\CheckoutController::class, 'checkout']);
            Route::post('/save-delivery-addresses', [App\Http\Controllers\Agent\CheckoutController::class, 'save_delivery_addresses']);
            Route::post('/view-delivery-addresses', [App\Http\Controllers\Agent\CheckoutController::class, 'view_delivery_addresses']);
            Route::post('/update-delivery-addresses', [App\Http\Controllers\Agent\CheckoutController::class, 'update_delivery_addresses']);
            Route::post('/place-order', [App\Http\Controllers\Agent\CheckoutController::class, 'place_order']);

            // My Orders
            Route::get('/my-orders', [App\Http\Controllers\Agent\OrderController::class, 'my_orders']);
            Route::get('/my-orders-api', [App\Http\Controllers\Agent\OrderController::class, 'my_orders_api']);
            Route::post('/view-order-product', [App\Http\Controllers\Agent\OrderController::class, 'view_order_product']);
            Route::post('/view-track-order', [App\Http\Controllers\Agent\OrderController::class, 'view_track_order']);

            // Track Orders
            Route::get('/track-orders', [App\Http\Controllers\Agent\OrderController::class, 'track_orders']);
        });

        Route::group(['prefix' => 'ecommerce-seller', 'middleware' => 'auth'], function () {
            Route::get('/product-list', [App\Http\Controllers\Agent\SellerController::class, 'product_list']);
            Route::get('/product-list-api', [App\Http\Controllers\Agent\SellerController::class, 'product_list_api']);
            Route::get('/add-products', [App\Http\Controllers\Agent\SellerController::class, 'add_products']);
            Route::get('/update-product/{id}', [App\Http\Controllers\Agent\SellerController::class, 'update_product']);
            Route::get('/add-product-image/{id}', [App\Http\Controllers\Agent\SellerController::class, 'add_product_image']);
            Route::get('/my-product', [App\Http\Controllers\Agent\SellerController::class, 'my_product']);

            // Order Request
            Route::get('/order-request', [App\Http\Controllers\Agent\OrderrequestController::class, 'order_request']);
            Route::get('/order-request-api', [App\Http\Controllers\Agent\OrderrequestController::class, 'order_request_api']);
            Route::post('/view-order-product-details', [App\Http\Controllers\Agent\OrderrequestController::class, 'view_order_product_details']);
            Route::post('/view-update-product', [App\Http\Controllers\Agent\OrderrequestController::class, 'view_update_product']);
            Route::post('/update-product-delivery-status', [App\Http\Controllers\Agent\OrderrequestController::class, 'update_product_delivery_status']);
        });

        Route::group(['prefix' => 'add-money/v1', 'middleware' => 'auth'], function () {
            Route::get('/welcome', [App\Http\Controllers\Agent\MpaymentQrcodeController::class, 'welcome']);
            Route::post('/create-order', [App\Http\Controllers\Agent\MpaymentQrcodeController::class, 'create_order']);
            Route::get('/view-qrcode', [App\Http\Controllers\Agent\MpaymentQrcodeController::class, 'viewQrcode']);
        });

        Route::group(['prefix' => 'add-money/v2', 'middleware' => 'auth'], function () {
            Route::get('/welcome', [App\Http\Controllers\Agent\PayuController::class, 'welcome']);
            Route::post('/create-order', [App\Http\Controllers\Agent\PayuController::class, 'create_order']);
            Route::get('/view-qrcode', [App\Http\Controllers\Agent\PayuController::class, 'viewQrcode']);
        });

        Route::group(['prefix' => 'add-money/v3', 'middleware' => 'auth'], function () {
            Route::get('/welcome', [App\Http\Controllers\Agent\PockethubController::class, 'welcome']);
            Route::post('/create-order', [App\Http\Controllers\Agent\PockethubController::class, 'createOrderWeb']);
            Route::get('/view-qrcode', [App\Http\Controllers\Agent\PockethubController::class, 'viewQrcode']);
        });

        Route::group(['prefix' => 'add-money/v4', 'middleware' => 'auth'], function () {
            Route::get('/welcome', [App\Http\Controllers\Agent\PunjikendraController::class, 'welcome']);
            Route::post('/create-order', [App\Http\Controllers\Agent\PunjikendraController::class, 'createOrderWeb']);
            Route::get('/view-qrcode', [App\Http\Controllers\Agent\PunjikendraController::class, 'viewQrcode']);
        });

        Route::group(['prefix' => 'add-money/v5', 'middleware' => 'auth'], function () {
            Route::get('/welcome', [App\Http\Controllers\Agent\VtransactController::class, 'welcome']);
            Route::post('/create-order', [App\Http\Controllers\Agent\VtransactController::class, 'createOrderWeb']);
            Route::get('/view-qrcode', [App\Http\Controllers\Agent\VtransactController::class, 'viewQrcode']);
        });
        
        
        Route::group(['prefix' => 'add-money/v6', 'middleware' => 'auth'], function () {
            Route::get('/welcome', [App\Http\Controllers\Agent\ElectraKartController::class, 'welcome']);
            Route::post('/create-order', [App\Http\Controllers\Agent\ElectraKartController::class, 'createOrderWeb']);
            Route::get('/view-qrcode', [App\Http\Controllers\Agent\ElectraKartController::class, 'viewQrcode']);
        });
        
        Route::group(['prefix' => 'add-money/v7', 'middleware' => 'auth'], function () {
            Route::get('/welcome', [App\Http\Controllers\Agent\SafepPayController::class, 'welcome']);
            Route::post('/create-order', [App\Http\Controllers\Agent\SafepPayController::class, 'createOrderWeb']);
            Route::get('/view-qrcode', [App\Http\Controllers\Agent\SafepPayController::class, 'viewQrcode']);
        });

        Route::group(['prefix' => 'add-money/v8', 'middleware' => 'auth'], function () {
            Route::get('/welcome', [App\Http\Controllers\Agent\ZigPayController::class, 'welcome']);
            Route::post('/create-order', [App\Http\Controllers\Agent\ZigPayController::class, 'createOrderWeb']);
            Route::post('/order-status', [App\Http\Controllers\Agent\ZigPayController::class, 'webOrderStatus']);
            Route::get('/view-qrcode', [App\Http\Controllers\Agent\ZigPayController::class, 'viewQrcode']);
        });


        /* Route::group(['prefix' => 'add-money/v4', 'middleware' => 'auth'], function () {
             Route::get('/welcome', [App\Http\Controllers\Agent\GrahakpayController::class, 'welcome']);
             Route::post('/create-order', [App\Http\Controllers\Agent\GrahakpayController::class, 'createOrderWeb']);
             Route::get('/view-qrcode', [App\Http\Controllers\Agent\GrahakpayController::class, 'viewQrcode']);
         });*/

      /*  Route::group(['prefix' => 'add-money/v4', 'middleware' => 'auth'], function () {
            Route::get('/welcome', [App\Http\Controllers\Agent\LetspeController::class, 'welcome']);
            Route::post('/create-order', [App\Http\Controllers\Agent\LetspeController::class, 'createOrderWeb']);
            Route::get('/view-qrcode', [App\Http\Controllers\Agent\LetspeController::class, 'viewQrcode']);
        });*/

        Route::group(['prefix' => 'referral', 'middleware' => 'auth'], function () {
            Route::get('/refer-and-earn', [App\Http\Controllers\Agent\ReferralController::class, 'welcome']);
        });

        Route::group(['prefix' => 'upi-transfer/v1', 'middleware' => 'auth'], function () {
            Route::get('/welcome', [App\Http\Controllers\Agent\UpitransferController::class, 'welcome']);
            Route::post('/getUpiextensions', [App\Http\Controllers\Agent\UpitransferController::class, 'getUpiextensions']);
            Route::post('/fatch-name', [App\Http\Controllers\Agent\UpitransferController::class, 'fatchNameWeb']);
            Route::post('/view-transaction', [App\Http\Controllers\Agent\UpitransferController::class, 'viewTransaction']);
        });

        Route::group(['prefix' => 'credit-card/v1', 'middleware' => 'auth'], function () {
            Route::get('/welcome', [App\Http\Controllers\Agent\CreditCardController::class, 'welcome']);
            Route::post('/view-transaction', [App\Http\Controllers\Agent\CreditCardController::class, 'view_transaction']);
            Route::post('/pay-now', [App\Http\Controllers\Agent\CreditCardController::class, 'payNowWeb']);
        });

        Route::group(['prefix' => 'aadhaar-verification/v1', 'middleware' => 'auth'], function () {
            Route::post('/send-otp', [App\Http\Controllers\Agent\AadhaarVerificationController::class, 'sendOTP']);
            Route::post('/confirm-otp', [App\Http\Controllers\Agent\AadhaarVerificationController::class, 'confirmOtpWeb']);
        });

        Route::group(['prefix' => 'pan-verify/v1', 'middleware' => 'auth'], function () {
            Route::post('/verify-now', [App\Http\Controllers\Agent\PanVerifyController::class, 'verifyNow']);
        });
        Route::get('/log-test', function () {
    \Log::debug('✅ Laravel log test working from /log-test route');
    return 'Log written!';
});


    });
});


