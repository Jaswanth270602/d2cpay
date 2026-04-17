<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('call-back')->group(function () {
    Route::post('/merchant-pay2all', [App\Http\Controllers\Agent\RefundController::class, 'merchant_pay2all']);
    Route::post('/smart-outlet', [App\Http\Controllers\Agent\SmartoutletController::class, 'smart_outlet']);
    Route::get('/recharge-response/{id}', [App\Http\Controllers\Agent\RefundController::class, 'dynamic_response']);
    Route::post('/recharge-response/{id}', [App\Http\Controllers\Agent\RefundController::class, 'dynamic_response']);
    Route::post('/cashfree-gateway', [App\Http\Controllers\Agent\CashfreeController::class, 'cashfree_callback']);
    Route::any('/mpayment-qrcode', [App\Http\Controllers\Agent\MpaymentQrcodeController::class, 'callbackUrl']);
    Route::any('/payu-upi-intent', [App\Http\Controllers\Agent\PayuController::class, 'callbackUrl']);
    Route::any('/accosis-payout', [App\Http\Controllers\Agent\RefundController::class, 'accosisPayout']);
    Route::any('/paywize-payout', [App\Http\Controllers\Agent\RefundController::class, 'paywizeCallback']);
    Route::any('/pockethub-payout', [App\Http\Controllers\Agent\RefundController::class, 'pockethubPayout']);
    Route::any('/pockethub-payin', [App\Http\Controllers\Agent\PockethubController::class, 'callbackUrl']);
    Route::any('/pockethub-redirect-url/{id}', [App\Http\Controllers\Agent\PockethubController::class, 'redirectUrl']);
    Route::any('/rmstrade-qrcode', [App\Http\Controllers\Agent\GrahakpayController::class, 'callbackUrl']);
    Route::post('/razorpay-payout', [App\Http\Controllers\Agent\RefundController::class, 'razorpayPayout']);
    Route::post('/punjikendra', [App\Http\Controllers\Agent\PunjikendraController::class, 'callbackUrl']);
    Route::any('/vtransact', [App\Http\Controllers\Agent\VtransactController::class, 'callbackUrl']);
    Route::any('/electrakart', [App\Http\Controllers\Agent\ElectraKartController::class, 'callbackUrl']);
    Route::any('/safeppay', [App\Http\Controllers\Agent\SafepPayController::class, 'callbackUrl']);
    Route::any('/zigpay-payin', [App\Http\Controllers\Agent\ZigPayController::class, 'callbackUrl']);
    Route::any('/zigpay-payout', [App\Http\Controllers\Agent\RefundController::class, 'zigpayPayout']);
    Route::any('/vtransact-payout', [App\Http\Controllers\Agent\RefundController::class, 'vtransactPayout']);
    Route::any('/safeppay-payout', [App\Http\Controllers\Agent\RefundController::class, 'safepPayout']);
});



Route::prefix('application/v1')->group(function () {
    Route::post('/login', [App\Http\Controllers\ApplicationController::class, 'login']);
    // Route::post('/api-login', [App\Http\Controllers\ApplicationController::class, 'apiLogin']);
    Route::post('/resend-login-otp', [App\Http\Controllers\Auth\LoginController::class, 'resend_login_otp_app']);
    Route::post('/validate-login', [App\Http\Controllers\ApplicationController::class, 'validate_login']);
    Route::post('/check-balance', [App\Http\Controllers\ApplicationController::class, 'check_balance'])->middleware('auth:api');
    Route::post('/state-list', [App\Http\Controllers\ApplicationController::class, 'state_list']);
    Route::post('/change-password', [App\Http\Controllers\ApplicationController::class, 'change_password'])->middleware('auth:api');
    Route::post('/update-profile', [App\Http\Controllers\ApplicationController::class, 'update_profile'])->middleware('auth:api');
    Route::post('/verify-mobile', [App\Http\Controllers\ApplicationController::class, 'verify_mobile'])->middleware('auth:api');
    Route::post('/confirm-verify-mobile', [App\Http\Controllers\ApplicationController::class, 'confirm_verify_mobile'])->middleware('auth:api');
    Route::post('/notification/mark-all-read', [App\Http\Controllers\ApplicationController::class, 'mark_all_read'])->middleware('auth:api');
    Route::post('/notification/read-notification', [App\Http\Controllers\ApplicationController::class, 'read_notification'])->middleware('auth:api');
    Route::post('/company-contact-details', [App\Http\Controllers\ApplicationController::class, 'company_contact_details'])->middleware('auth:api');
    Route::get('/ekyc-update', [App\Http\Controllers\ApplicationController::class, 'ekyc_update'])->middleware('auth:api');

    // kyc
    Route::post('/update-profile-photo', [App\Http\Controllers\ApplicationController::class, 'update_profile_photo'])->middleware('auth:api');
    Route::post('/update-shop-photo', [App\Http\Controllers\ApplicationController::class, 'update_shop_photo'])->middleware('auth:api');
    Route::post('/update-gst-regisration-photo', [App\Http\Controllers\ApplicationController::class, 'update_gst_regisration_photo'])->middleware('auth:api');
    Route::post('/update-pancard-photo', [App\Http\Controllers\ApplicationController::class, 'update_pancard_photo'])->middleware('auth:api');
    Route::post('/update-cancel-cheque-photo', [App\Http\Controllers\ApplicationController::class, 'cancel_cheque_photo'])->middleware('auth:api');
    Route::post('/update-address-proof-photo', [App\Http\Controllers\ApplicationController::class, 'address_proof_photo'])->middleware('auth:api');
    Route::post('/get-service', [App\Http\Controllers\ApplicationController::class, 'get_service'])->middleware('auth:api');
    Route::post('/get-provider', [App\Http\Controllers\ApplicationController::class, 'get_provider'])->middleware('auth:api');
    Route::post('/provider-validation', [App\Http\Controllers\Agent\RechargeController::class, 'check_provider_validation'])->middleware('auth:api');
    Route::post('/bill-verify', [App\Http\Controllers\Agent\RechargeController::class, 'bbps_bill_verify_app'])->middleware('auth:api');
    Route::post('/recharge-now', [App\Http\Controllers\Agent\RechargeController::class, 'app_recharge_now'])->middleware('auth:api');
    Route::post('/aeps-outlet-id', [App\Http\Controllers\ApplicationController::class, 'aeps_outlet_id'])->middleware('auth:api');
    Route::post('/sign-up', [App\Http\Controllers\Auth\SignupController::class, 'register_now']);
    Route::post('/forgot-password-otp', [App\Http\Controllers\Auth\LoginController::class, 'forgot_password_otp']);
    Route::post('/confirm-forgot-password', [App\Http\Controllers\Auth\LoginController::class, 'confirm_forgot_password']);
    Route::post('/agent-onboarding', [App\Http\Controllers\Agent\AepsController::class, 'save_agent_onboarding'])->middleware('auth:api');
    Route::get('/page-content', [App\Http\Controllers\ApplicationController::class, 'page_content']);

    // common list
    Route::post('/common-list', [App\Http\Controllers\ApplicationController::class, 'commonList']);
    Route::post('/send-transaction-pin-otp', [App\Http\Controllers\Admin\ProfileController::class, 'send_transaction_pin_otp'])->middleware('auth:api');
    Route::post('/create-transaction-pin', [App\Http\Controllers\Admin\ProfileController::class, 'create_transaction_pin'])->middleware('auth:api');
});

Route::prefix('dmt/v1')->group(function () {
    Route::post('/bank-list', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'bank_list'])->middleware('auth:api');
    Route::post('/get-customer', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'getCustomer'])->middleware('auth:api');
    Route::post('/add-sender', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'addSender'])->middleware('auth:api');
    Route::post('/confirm-sender', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'confirmSender'])->middleware('auth:api');
    Route::post('/sender-resend-otp', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'senderResendOtp'])->middleware('auth:api');
    Route::post('/get-all-beneficiary', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'getAllBeneficiary'])->middleware('auth:api');
    Route::post('/search-by-account', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'searchByAccount'])->middleware('auth:api');
    Route::post('/add-beneficiary', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'addBeneficiary'])->middleware('auth:api');
    Route::post('/confirm-beneficiary', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'confirmBeneficiary'])->middleware('auth:api');
    Route::post('/delete-beneficiary', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'deleteBeneficiary'])->middleware('auth:api');
    Route::post('/confirm-delete-beneficiary', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'confirmDeleteBeneficiary'])->middleware('auth:api');
    Route::post('/account-verify', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'accountVerifyApp'])->middleware('auth:api');
    Route::post('/transfer-now', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'transferNowApp'])->middleware('auth:api');
    Route::post('/get-transaction-charges', [App\Http\Controllers\Agent\Moneyv1Controller::class, 'getTransactionCharges'])->middleware('auth:api');
});

Route::prefix('dmt/v2')->group(function () {
    Route::post('/bank-list', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'bank_list'])->middleware('auth:api');
    Route::post('/get-customer', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'getCustomer'])->middleware('auth:api');
    Route::post('/add-sender', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'addSender'])->middleware('auth:api');
    Route::post('/confirm-sender', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'confirmSender'])->middleware('auth:api');
    Route::post('/sender-resend-otp', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'senderResendOtp'])->middleware('auth:api');
    Route::post('/get-all-beneficiary', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'getAllBeneficiary'])->middleware('auth:api');
    Route::post('/search-by-account', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'searchByAccount'])->middleware('auth:api');
    Route::post('/add-beneficiary', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'addBeneficiary'])->middleware('auth:api');
    Route::post('/confirm-beneficiary', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'confirmBeneficiary'])->middleware('auth:api');
    Route::post('/delete-beneficiary', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'deleteBeneficiary'])->middleware('auth:api');
    Route::post('/confirm-delete-beneficiary', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'confirmDeleteBeneficiary'])->middleware('auth:api');
    Route::post('/account-verify', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'accountVerifyApp'])->middleware('auth:api');
    Route::post('/transfer-now', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'transferNowApp'])->middleware('auth:api');
    Route::post('/get-transaction-charges', [App\Http\Controllers\Agent\Moneyv2Controller::class, 'getTransactionCharges'])->middleware('auth:api');
});

Route::prefix('reports/v1')->group(function () {
    Route::get('/all-transaction-report', [App\Http\Controllers\ReportController::class, 'all_transaction_report'])->middleware('auth:api');
    Route::get('/ledger-report', [App\Http\Controllers\ReportController::class, 'ledger_report'])->middleware('auth:api');
    Route::get('/welcome/{report_slug}', [App\Http\Controllers\ReportController::class, 'welcome'])->middleware('auth:api');
    Route::get('/operator-report', [App\Http\Controllers\ReportController::class, 'operator_report'])->middleware('auth:api');
    Route::get('/income-report', [App\Http\Controllers\ReportController::class, 'income_report'])->middleware('auth:api');
});

Route::prefix('fund-request')->group(function () {
    Route::get('/bank-list', [App\Http\Controllers\ApplicationController::class, 'fund_request_bank_list'])->middleware('auth:api');
    Route::get('/payment-method', [App\Http\Controllers\ApplicationController::class, 'payment_method'])->middleware('auth:api');
    Route::post('/payment-request-now', [App\Http\Controllers\ApplicationController::class, 'payment_request_now'])->middleware('auth:api');
    Route::get('/request-report', [App\Http\Controllers\ApplicationController::class, 'fund_request_report'])->middleware('auth:api');
});

Route::prefix('commission')->group(function () {
    Route::get('/service-list', [App\Http\Controllers\ApplicationController::class, 'commission_service_list'])->middleware('auth:api');
    Route::get('/providers', [App\Http\Controllers\ApplicationController::class, 'commission_providers'])->middleware('auth:api');
    Route::get('/my-commission', [App\Http\Controllers\ApplicationController::class, 'my_commission'])->middleware('auth:api');
});

Route::prefix('settlement')->group(function () {
    Route::post('/move-to-wallet', [App\Http\Controllers\Agent\PayoutController::class, 'move_to_wallet_app'])->middleware('auth:api');
    Route::post('/beneficiary-list', [App\Http\Controllers\Agent\PayoutController::class, 'beneficiary_list'])->middleware('auth:api');
    Route::post('/account-validate', [App\Http\Controllers\Agent\PayoutController::class, 'account_validate_app'])->middleware('auth:api');
    Route::post('/add-beneficiary', [App\Http\Controllers\Agent\PayoutController::class, 'add_beneficiary'])->middleware('auth:api');
    Route::post('/delete-beneficiary', [App\Http\Controllers\Agent\PayoutController::class, 'delete_beneficiary'])->middleware('auth:api');
    Route::post('/transfer-now', [App\Http\Controllers\Agent\PayoutController::class, 'transfer_now_app'])->middleware('auth:api');
});

Route::group(['prefix' => 'payout/v1'], function () {
    Route::post('/transfer-now', [App\Http\Controllers\Agent\DirectTransferController::class, 'transfer_now_api'])->middleware('auth:api');
});



Route::prefix('pancard')->group(function () {
    Route::post('/purchase-coupons', [App\Http\Controllers\Agent\PancardController::class, 'buy_coupons_app'])->middleware('auth:api');
});

Route::prefix('wallet')->group(function () {
    Route::post('/verify-user', [App\Http\Controllers\Agent\WalletController::class, 'verify_user'])->middleware('auth:api');
    Route::post('/transfer-now', [App\Http\Controllers\Agent\WalletController::class, 'transfer_now'])->middleware('auth:api');
});

Route::prefix('dispute')->group(function () {
    Route::post('/reason', [App\Http\Controllers\Agent\DisputeController::class, 'reason_application'])->middleware('auth:api');
    Route::post('/save-dispute', [App\Http\Controllers\Agent\DisputeController::class, 'dispute_transaction'])->middleware('auth:api');
    Route::post('/view-dispute-details', [App\Http\Controllers\Agent\DisputeController::class, 'view_dispute_conversation'])->middleware('auth:api');
    Route::post('/pending-dispute', [App\Http\Controllers\Agent\DisputeController::class, 'pending_dispute_app'])->middleware('auth:api');
    Route::post('/solve-dispute', [App\Http\Controllers\Agent\DisputeController::class, 'solve_dispute_app'])->middleware('auth:api');
    Route::post('/view-conversation', [App\Http\Controllers\Agent\DisputeController::class, 'view_conversation_application'])->middleware('auth:api');
    Route::post('/send-chat-message', [App\Http\Controllers\Agent\DisputeController::class, 'send_chat_message'])->middleware('auth:api');
});

Route::prefix('telecom/v1')->group(function () {
    Route::get('/payment', [App\Http\Controllers\Agent\RechargeController::class, 'api_recharge_now'])->middleware('auth:api');
    Route::get('/check-balance', [App\Http\Controllers\Agent\RechargeController::class, 'check_balance_api'])->middleware('auth:api');
    Route::get('/check-status', [App\Http\Controllers\Agent\RechargeController::class, 'check_status_api'])->middleware('auth:api');
    Route::post('/bill-verify', [App\Http\Controllers\Agent\RechargeController::class, 'bbps_bill_verify_api'])->middleware('auth:api');
    Route::post('/provider-validation', [App\Http\Controllers\Agent\RechargeController::class, 'check_provider_validation'])->middleware('auth:api');
});

Route::prefix('plan/v1')->group(function () {
    Route::get('/prepaid-plan', [App\Http\Controllers\Agent\PlanController::class, 'prepaid_plan'])->middleware('auth:api');
    Route::get('/r-offer', [App\Http\Controllers\Agent\PlanController::class, 'r_offer'])->middleware('auth:api');
    Route::get('/dth-customer-info', [App\Http\Controllers\Agent\PlanController::class, 'dth_customer_info'])->middleware('auth:api');
    Route::get('/dth-plans', [App\Http\Controllers\Agent\PlanController::class, 'dth_plans'])->middleware('auth:api');
    Route::get('/dth-roffer', [App\Http\Controllers\Agent\PlanController::class, 'dth_roffer'])->middleware('auth:api');
    Route::get('/dth-refresh', [App\Http\Controllers\Agent\PlanController::class, 'dth_refresh'])->middleware('auth:api');
});

Route::prefix('admin')->group(function () {
    Route::post('/get-roles', [App\Http\Controllers\ApplicationController::class, 'get_roles'])->middleware('auth:api');
    Route::post('/add-members', [App\Http\Controllers\Admin\MemberController::class, 'store_members'])->middleware('auth:api');
    Route::post('/get-users', [App\Http\Controllers\ApplicationController::class, 'get_users'])->middleware('auth:api');
    Route::post('/balance-transfer', [App\Http\Controllers\Admin\TransferController::class, 'balance_trasnfer_application'])->middleware('auth:api');
});

Route::prefix('aeps/v1')->group(function () {
    Route::post('/agent-onboarding', [App\Http\Controllers\Agent\AepsController::class, 'agent_onboarding_api'])->middleware('auth:api');
    Route::get('/aeps-landing', [App\Http\Controllers\Agent\AepsController::class, 'aeps_landing_api'])->middleware('auth:api');
    Route::get('/aeps-outlet-id', [App\Http\Controllers\Agent\AepsController::class, 'aeps_outlet_id'])->middleware('auth:api');
});

Route::prefix('ecommerce/v1')->group(function () {
    Route::get('/banners', [App\Http\Controllers\EcommerceController::class, 'banners']);
    Route::get('/get-category', [App\Http\Controllers\EcommerceController::class, 'get_category']);
    Route::get('/home-page-product', [App\Http\Controllers\EcommerceController::class, 'home_page_product']);
    Route::get('/product-by-category', [App\Http\Controllers\EcommerceController::class, 'product_by_category']);
    Route::get('/search-product', [App\Http\Controllers\EcommerceController::class, 'search_product']);
    // add to cart
    Route::post('add-to-cart', [App\Http\Controllers\EcommerceController::class, 'add_to_cart'])->middleware('auth:api');
    Route::post('view-cart-item', [App\Http\Controllers\EcommerceController::class, 'view_cart_item'])->middleware('auth:api');
    Route::post('delete-cart-item', [App\Http\Controllers\EcommerceController::class, 'delete_cart_item'])->middleware('auth:api');
    Route::post('update-cart-item', [App\Http\Controllers\EcommerceController::class, 'update_cart_item'])->middleware('auth:api');
    // add to wishlist
    Route::post('add-to-wishlist', [App\Http\Controllers\EcommerceController::class, 'add_to_wishlist'])->middleware('auth:api');
    Route::post('my-wishlist', [App\Http\Controllers\EcommerceController::class, 'my_wishlist'])->middleware('auth:api');
    // shipping address
    Route::post('save-delivery-addresses', [App\Http\Controllers\EcommerceController::class, 'save_delivery_addresses'])->middleware('auth:api');
    Route::post('my-delivery-addresses', [App\Http\Controllers\EcommerceController::class, 'my_delivery_addresses'])->middleware('auth:api');
    Route::post('update-delivery-addresses', [App\Http\Controllers\EcommerceController::class, 'update_delivery_addresses'])->middleware('auth:api');
    // buy product
    Route::post('payment-methods', [App\Http\Controllers\EcommerceController::class, 'payment_method'])->middleware('auth:api');
    Route::post('confirm-order', [App\Http\Controllers\EcommerceController::class, 'confirm_order'])->middleware('auth:api');
    // reports
    Route::post('order-report', [App\Http\Controllers\EcommerceController::class, 'order_report'])->middleware('auth:api');
});

Route::prefix('add-money/v1')->group(function () {
    Route::post('/create-order', [App\Http\Controllers\Agent\CashfreeController::class, 'createOrderApp'])->middleware('auth:api');
});

Route::prefix('add-money/v2')->group(function () {
    Route::post('/generate-qrcode', [App\Http\Controllers\Agent\PayuController::class, 'createOrderApi'])->middleware('auth:api');
    Route::post('/status-enquiry', [App\Http\Controllers\Agent\PayuController::class, 'statusEnquiryApi'])->middleware('auth:api');
});

Route::prefix('add-money/v3')->group(function () {
    Route::post('/createOrder', [App\Http\Controllers\Agent\PockethubController::class, 'createOrderApi'])->middleware('auth:api');
    Route::post('/status-enquiry', [App\Http\Controllers\Agent\PockethubController::class, 'statusEnquiryApi'])->middleware('auth:api');
});

Route::prefix('add-money/v4')->group(function () {
    Route::post('/createOrder', [App\Http\Controllers\Agent\PunjikendraController::class, 'createOrderApi'])->middleware('auth:api');
    Route::post('/status-enquiry', [App\Http\Controllers\Agent\PunjikendraController::class, 'statusEnquiryApi'])->middleware('auth:api');
});

Route::prefix('add-money/v5')->group(function () {
    Route::post('/createOrder', [App\Http\Controllers\Agent\VtransactController::class, 'createOrderApi'])->middleware('auth:api');
    Route::post('/status-enquiry', [App\Http\Controllers\Agent\VtransactController::class, 'statusEnquiryApi'])->middleware('auth:api');
});

Route::prefix('add-money/v6')->group(function () {
    Route::post('/createOrder', [App\Http\Controllers\Agent\ElectraKartController::class, 'createOrderApi'])->middleware('auth:api');
    Route::post('/status-enquiry', [App\Http\Controllers\Agent\ElectraKartController::class, 'statusEnquiryApi'])->middleware('auth:api');
});

Route::prefix('add-money/v7')->group(function () {
    Route::post('/createOrder', [App\Http\Controllers\Agent\SafepPayController::class, 'createOrderApi'])->middleware('auth:api');
    Route::post('/status-enquiry', [App\Http\Controllers\Agent\SafepPayController::class, 'statusEnquiryApi'])->middleware('auth:api');
});

Route::prefix('add-money/v8')->group(function () {
    Route::post('/createOrder', [App\Http\Controllers\Agent\ZigPayController::class, 'createOrderApi'])->middleware('auth:api');
    Route::post('/status-enquiry', [App\Http\Controllers\Agent\ZigPayController::class, 'statusEnquiryApi'])->middleware('auth:api');
});


Route::prefix('referral')->group(function () {
    Route::get('/details', [App\Http\Controllers\Agent\ReferralController::class, 'details_app'])->middleware('auth:api');
});

Route::prefix('credit-card/v1')->group(function () {
    Route::post('/pay-now', [App\Http\Controllers\Agent\CreditCardController::class, 'payNowApp'])->middleware('auth:api');
});

