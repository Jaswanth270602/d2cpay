@extends('themes2.admin.layout.header')
@section('content')
    <script type="text/javascript">
        function update_permission() {
            $(".loader").show();
            var dataString = {
                _token: $("input[name=_token]").val(),
                user_id: $("#user_id").val(),
                bank_master_permission: $("#bank_master_permission").val(),
                role_master_permission: $("#role_master_permission").val(),
                status_master_permission: $("#status_master_permission").val(),
                service_master_permission: $("#service_master_permission").val(),
                payment_method_master_permission: $("#payment_method_master_permission").val(),
                payout_beneficiary_master_permission: $("#payout_beneficiary_master_permission").val(),
                agent_onboarding_list_permission: $("#agent_onboarding_list_permission").val(),
                contact_enquiry_permission: $("#contact_enquiry_permission").val(),
                provider_master_permission: $("#provider_master_permission").val(),
                api_master_permission: $("#api_master_permission").val(),
                add_api_permission: $("#add_api_permission").val(),
                update_api_permission: $("#update_api_permission").val(),
                denomination_wise_permission: $("#denomination_wise_permission").val(),
                number_series_permission: $("#number_series_permission").val(),
                state_wise_permission: $("#state_wise_permission").val(),
                backup_api_permission: $("#backup_api_permission").val(),
                api_switching_permission: $("#api_switching_permission").val(),
                user_operator_limit_permission: $("#user_operator_limit_permission").val(),
                company_settings_permission: $("#company_settings_permission").val(),
                site_settings_permission: $("#site_settings_permission").val(),
                sms_template_permission: $("#sms_template_permission").val(),
                package_settings_permission: $("#package_settings_permission").val(),
                bank_settings_permission: $("#bank_settings_permission").val(),
                logo_upload_permission: $("#logo_upload_permission").val(),
                service_banner_permission: $("#service_banner_permission").val(),
                notification_settings_permission: $("#notification_settings_permission").val(),
                dynamic_page_permission: $("#dynamic_page_permission").val(),
                front_banners_permission: $("#front_banners_permission").val(),
                whatsapp_notification_permission: $("#whatsapp_notification_permission").val(),
                member_permission: $("#member_permission").val(),
                create_member_permission: $("#create_member_permission").val(),
                update_member_permission: $("#update_member_permission").val(),
                reset_password_permission: $("#reset_password_permission").val(),
                viewUser_kyc_permission: $("#viewUser_kyc_permission").val(),
                update_kyc_permission: $("#update_kyc_permission").val(),
                download_member_permission: $("#download_member_permission").val(),
                member_statement_permission: $("#member_statement_permission").val(),
                send_statement_permission: $("#send_statement_permission").val(),
                suspended_user_permission: $("#suspended_user_permission").val(),
                not_working_users_permission: $("#not_working_users_permission").val(),
                all_transaction_report_permission: $("#all_transaction_report_permission").val(),
                update_transaction_permission: $("#update_transaction_permission").val(),
                view_api_logs_permission: $("#view_api_logs_permission").val(),
                recharge_report_permission: $("#recharge_report_permission").val(),
                pancard_report_permission: $("#pancard_report_permission").val(),
                auto_payment_report_permission: $("#auto_payment_report_permission").val(),
                pending_transaction_permission: $("#pending_transaction_permission").val(),
                profit_distribution_permission: $("#profit_distribution_permission").val(),
                refund_manager_permission: $("#refund_manager_permission").val(),
                api_summary_permission: $("#api_summary_permission").val(),
                operator_wise_sale_permission: $("#operator_wise_sale_permission").val(),
                aeps_report_permission: $("#aeps_report_permission").val(),
                payout_settlement_permission: $("#payout_settlement_permission").val(),
                aeps_operator_report_permission: $("#aeps_operator_report_permission").val(),
                account_validate_report_permission: $("#account_validate_report_permission").val(),
                money_transfer_report_permission: $("#money_transfer_report_permission").val(),
                money_operator_report_permission: $("#money_operator_report_permission").val(),
                balance_transfer_permission: $("#balance_transfer_permission").val(),
                balance_return_permission: $("#balance_return_permission").val(),
                payment_request_view_permission: $("#payment_request_view_permission").val(),
                payment_request_permission: $("#payment_request_permission").val(),
                pending_dispute_permission: $("#pending_dispute_permission").val(),
                dispute_chat_permission: $("#dispute_chat_permission").val(),
                dispute_update_permission: $("#dispute_update_permission").val(),
                solve_dispute_permission: $("#solve_dispute_permission").val(),
                reopen_dispute_permission: $("#reopen_dispute_permission").val(),
            };
            $.ajax({
                type: "post",
                url: "{{url('admin/company-staff/update-permission')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 2000);
                    }else{
                        swal("Faild", msg.message, "error");
                    }
                }
            });
        }
    </script>
    <!--  Content Area Starts  -->
    <div id="content" class="main-content">

        <!-- Main Body Starts -->
    @include('themes2.admin.layout.breadcrumb')
    <!-- Main Body Starts -->
        <div class="layout-px-spacing">
            <div class="row layout-top-spacing">
                <!-- REVENUE ENDS-->


                <input type="hidden" id="user_id" value="{{ $user_id }}">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
                    <div class="widget dashboard-table">
                        <div class="widget-heading">
                            <h5 class=""> {{ $page_title }}</h5>
                        </div>
                        <hr>
                        <div class="widget-content">

                            {{--master--}}
                            <div class="row">
                                <div class="col-lg-12 layout-spacing">
                                    <div class="card">
                                        <div class="widget-header">
                                            <div class="row">
                                                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                                                    <h4>Master</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="card-body">
                                            <div class="form-body">
                                                <div class="row">

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Bank Master</label>
                                                            <select class="form-control" id="bank_master_permission">
                                                                <option value="0" @if($bank_master_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($bank_master_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Role Master</label>
                                                            <select class="form-control" id="role_master_permission">
                                                                <option value="0" @if($role_master_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($role_master_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Status Master</label>
                                                            <select class="form-control" id="status_master_permission">
                                                                <option value="0" @if($status_master_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($status_master_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Service Master</label>
                                                            <select class="form-control" id="service_master_permission">
                                                                <option value="0" @if($service_master_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($service_master_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Payment Method Master</label>
                                                            <select class="form-control" id="payment_method_master_permission">
                                                                <option value="0" @if($payment_method_master_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($payment_method_master_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Payout Beneficiary Master</label>
                                                            <select class="form-control" id="payout_beneficiary_master_permission">
                                                                <option value="0" @if($payout_beneficiary_master_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($payout_beneficiary_master_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Agent Onboarding List</label>
                                                            <select class="form-control" id="agent_onboarding_list_permission">
                                                                <option value="0" @if($agent_onboarding_list_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($agent_onboarding_list_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Contact Enquiry</label>
                                                            <select class="form-control" id="contact_enquiry_permission">
                                                                <option value="0" @if($contact_enquiry_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($contact_enquiry_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>


                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--/div-->
                            </div>

                            {{-- master close--}}


                            {{--Api Master--}}
                            <div class="row">
                                <div class="col-lg-12 layout-spacing">
                                    <div class="card">
                                        <div class="widget-header">
                                            <div class="row">
                                                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                                                    <h4>Api Master</h4>
                                                </div>
                                            </div>
                                        </div>
                                       <hr>
                                        <div class="card-body">

                                            <div class="form-body">
                                                <div class="row">

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Provider Master</label>
                                                            <select class="form-control" id="provider_master_permission">
                                                                <option value="0" @if($provider_master_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($provider_master_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Api Master</label>
                                                            <select class="form-control" id="api_master_permission">
                                                                <option value="0" @if($api_master_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($api_master_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Add New Api</label>
                                                            <select class="form-control" id="add_api_permission">
                                                                <option value="0" @if($add_api_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($add_api_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Update Api</label>
                                                            <select class="form-control" id="update_api_permission">
                                                                <option value="0" @if($update_api_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($update_api_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>


                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Denomination Wise Api</label>
                                                            <select class="form-control" id="denomination_wise_permission">
                                                                <option value="0" @if($denomination_wise_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($denomination_wise_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>


                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Number Series Master</label>
                                                            <select class="form-control" id="number_series_permission">
                                                                <option value="0" @if($number_series_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($number_series_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">State Wise Api</label>
                                                            <select class="form-control" id="state_wise_permission">
                                                                <option value="0" @if($state_wise_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($state_wise_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>


                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Backup Api Master</label>
                                                            <select class="form-control" id="backup_api_permission">
                                                                <option value="0" @if($backup_api_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($backup_api_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>


                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Api Switching</label>
                                                            <select class="form-control" id="api_switching_permission">
                                                                <option value="0" @if($api_switching_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($api_switching_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">User Operator Limit</label>
                                                            <select class="form-control" id="user_operator_limit_permission">
                                                                <option value="0" @if($user_operator_limit_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($user_operator_limit_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>


                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--/div-->
                            </div>

                            {{--Api Master Close--}}

                            {{--Setting--}}
                            <div class="row row-sm">
                                <div class="col-lg-12 layout-spacing">
                                    <div class="card">
                                        <div class="widget-header">
                                            <div class="row">
                                                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                                                    <h4>Settings</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="card-body">

                                            <div class="form-body">
                                                <div class="row">

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Company Settings</label>
                                                            <select class="form-control" id="company_settings_permission">
                                                                <option value="0" @if($company_settings_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($company_settings_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Site Settings</label>
                                                            <select class="form-control" id="site_settings_permission">
                                                                <option value="0" @if($site_settings_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($site_settings_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Sms Template</label>
                                                            <select class="form-control" id="sms_template_permission">
                                                                <option value="0" @if($sms_template_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($sms_template_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Package Settings</label>
                                                            <select class="form-control" id="package_settings_permission">
                                                                <option value="0" @if($package_settings_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($package_settings_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Bank Settings</label>
                                                            <select class="form-control" id="bank_settings_permission">
                                                                <option value="0" @if($bank_settings_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($bank_settings_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Logo Upload</label>
                                                            <select class="form-control" id="logo_upload_permission">
                                                                <option value="0" @if($logo_upload_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($logo_upload_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Service Banner</label>
                                                            <select class="form-control" id="service_banner_permission">
                                                                <option value="0" @if($service_banner_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($service_banner_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Notification Settings</label>
                                                            <select class="form-control" id="notification_settings_permission">
                                                                <option value="0" @if($notification_settings_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($notification_settings_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>


                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--/div-->
                            </div>
                            {{--Setting end--}}

                            {{--Website Master--}}
                            <div class="row row-sm">
                                <div class="col-lg-12 layout-spacing">
                                    <div class="card">
                                        <div class="widget-header">
                                            <div class="row">
                                                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                                                    <h4>Website Master</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="card-body">

                                            <div class="form-body">
                                                <div class="row">

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Dynamic Page</label>
                                                            <select class="form-control" id="dynamic_page_permission">
                                                                <option value="0" @if($dynamic_page_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($dynamic_page_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Front Banners</label>
                                                            <select class="form-control" id="front_banners_permission">
                                                                <option value="0" @if($front_banners_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($front_banners_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Whatsapp Notification</label>
                                                            <select class="form-control" id="whatsapp_notification_permission">
                                                                <option value="0" @if($whatsapp_notification_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($whatsapp_notification_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>


                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--/div-->
                            </div>
                            {{--Website Master End--}}

                            {{--Member List--}}
                            <div class="row row-sm">
                                <div class="col-lg-12 layout-spacing">
                                    <div class="card">
                                        <div class="widget-header">
                                            <div class="row">
                                                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                                                    <h4>Member</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="card-body">

                                            <div class="form-body">
                                                <div class="row">

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Member List</label>
                                                            <select class="form-control" id="member_permission">
                                                                <option value="0" @if($member_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($member_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Create Member</label>
                                                            <select class="form-control" id="create_member_permission">
                                                                <option value="0" @if($create_member_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($create_member_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Update Member</label>
                                                            <select class="form-control" id="update_member_permission">
                                                                <option value="0" @if($update_member_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($update_member_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Reset Password</label>
                                                            <select class="form-control" id="reset_password_permission">
                                                                <option value="0" @if($reset_password_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($reset_password_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">View User KYC</label>
                                                            <select class="form-control" id="viewUser_kyc_permission">
                                                                <option value="0" @if($viewUser_kyc_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($viewUser_kyc_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Update User KYC</label>
                                                            <select class="form-control" id="update_kyc_permission">
                                                                <option value="0" @if($update_kyc_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($update_kyc_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Download User</label>
                                                            <select class="form-control" id="download_member_permission">
                                                                <option value="0" @if($download_member_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($download_member_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Member Statement</label>
                                                            <select class="form-control" id="member_statement_permission">
                                                                <option value="0" @if($member_statement_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($member_statement_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Send Statement (Mail)</label>
                                                            <select class="form-control" id="send_statement_permission">
                                                                <option value="0" @if($send_statement_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($send_statement_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Suspended User List</label>
                                                            <select class="form-control" id="suspended_user_permission">
                                                                <option value="0" @if($suspended_user_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($suspended_user_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Not Working Users List</label>
                                                            <select class="form-control" id="not_working_users_permission">
                                                                <option value="0" @if($not_working_users_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($not_working_users_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>


                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--/div-->
                            </div>

                            {{--Member List End--}}

                            {{--Report--}}
                            <div class="row row-sm">
                                <div class="col-lg-12 layout-spacing">
                                    <div class="card">
                                        <div class="widget-header">
                                            <div class="row">
                                                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                                                    <h4>Reports</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="card-body">

                                            <div class="form-body">
                                                <div class="row">

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">All Transaction Report</label>
                                                            <select class="form-control" id="all_transaction_report_permission">
                                                                <option value="0" @if($all_transaction_report_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($all_transaction_report_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Update Transaction (Refund or Success)</label>
                                                            <select class="form-control" id="update_transaction_permission">
                                                                <option value="0" @if($update_transaction_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($update_transaction_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">View Api Logs</label>
                                                            <select class="form-control" id="view_api_logs_permission">
                                                                <option value="0" @if($view_api_logs_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($view_api_logs_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Recharge Report</label>
                                                            <select class="form-control" id="recharge_report_permission">
                                                                <option value="0" @if($recharge_report_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($recharge_report_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Pancard Report</label>
                                                            <select class="form-control" id="pancard_report_permission">
                                                                <option value="0" @if($pancard_report_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($pancard_report_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>


                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Auto Payment Report</label>
                                                            <select class="form-control" id="auto_payment_report_permission">
                                                                <option value="0" @if($auto_payment_report_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($auto_payment_report_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Pending Transaction</label>
                                                            <select class="form-control" id="pending_transaction_permission">
                                                                <option value="0" @if($pending_transaction_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($pending_transaction_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>


                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Profit Distribution</label>
                                                            <select class="form-control" id="profit_distribution_permission">
                                                                <option value="0" @if($profit_distribution_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($profit_distribution_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Refund Manage</label>
                                                            <select class="form-control" id="refund_manager_permission">
                                                                <option value="0" @if($refund_manager_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($refund_manager_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Api Summary</label>
                                                            <select class="form-control" id="api_summary_permission">
                                                                <option value="0" @if($api_summary_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($api_summary_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Operator Wise Sale</label>
                                                            <select class="form-control" id="operator_wise_sale_permission">
                                                                <option value="0" @if($operator_wise_sale_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($operator_wise_sale_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Aeps Report</label>
                                                            <select class="form-control" id="aeps_report_permission">
                                                                <option value="0" @if($aeps_report_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($aeps_report_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Payout Settlement</label>
                                                            <select class="form-control" id="payout_settlement_permission">
                                                                <option value="0" @if($payout_settlement_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($payout_settlement_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Aeps Operator Report</label>
                                                            <select class="form-control" id="aeps_operator_report_permission">
                                                                <option value="0" @if($aeps_operator_report_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($aeps_operator_report_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Account Validate Report</label>
                                                            <select class="form-control" id="account_validate_report_permission">
                                                                <option value="0" @if($account_validate_report_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($account_validate_report_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Money Transfer Report</label>
                                                            <select class="form-control" id="money_transfer_report_permission">
                                                                <option value="0" @if($money_transfer_report_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($money_transfer_report_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Money Transfer Operator Report</label>
                                                            <select class="form-control" id="money_operator_report_permission">
                                                                <option value="0" @if($money_operator_report_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($money_operator_report_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>




                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--/div-->
                            </div>
                            {{--Report End--}}


                            {{--Payment--}}
                            <div class="row row-sm">
                                <div class="col-lg-12 layout-spacing">
                                    <div class="card">
                                        <div class="widget-header">
                                            <div class="row">
                                                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                                                    <h4>Payment</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="card-body">

                                            <div class="form-body">
                                                <div class="row">

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Balance Transfer</label>
                                                            <select class="form-control" id="balance_transfer_permission">
                                                                <option value="0" @if($balance_transfer_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($balance_transfer_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Balance Return</label>
                                                            <select class="form-control" id="balance_return_permission">
                                                                <option value="0" @if($balance_return_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($balance_return_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Payment Request View</label>
                                                            <select class="form-control" id="payment_request_view_permission">
                                                                <option value="0" @if($payment_request_view_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($payment_request_view_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>


                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Payment Request</label>
                                                            <select class="form-control" id="payment_request_permission">
                                                                <option value="0" @if($payment_request_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($payment_request_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>




                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--/div-->
                            </div>
                            {{--Payment End--}}

                            <div class="row row-sm">
                                <div class="col-lg-12 layout-spacing">
                                    <div class="card">
                                        <div class="widget-header">
                                            <div class="row">
                                                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                                                    <h4>Dispute</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="card-body">
                                            <div class="form-body">
                                                <div class="row">


                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Pending Dispute</label>
                                                            <select class="form-control" id="pending_dispute_permission">
                                                                <option value="0" @if($pending_dispute_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($pending_dispute_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Dispute Chat</label>
                                                            <select class="form-control" id="dispute_chat_permission">
                                                                <option value="0" @if($dispute_chat_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($dispute_chat_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Dispute Update</label>
                                                            <select class="form-control" id="dispute_update_permission">
                                                                <option value="0" @if($dispute_update_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($dispute_update_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Solve Dispute</label>
                                                            <select class="form-control" id="solve_dispute_permission">
                                                                <option value="0" @if($solve_dispute_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($solve_dispute_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>


                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label for="name">Reopen Dispute</label>
                                                            <select class="form-control" id="reopen_dispute_permission">
                                                                <option value="0" @if($reopen_dispute_permission == 0) selected @endif>Disabled</option>
                                                                <option value="1" @if($reopen_dispute_permission == 1) selected @endif>Enabled</option>
                                                            </select>
                                                        </div>
                                                    </div>


                                                </div>

                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <button type="submit" class="btn btn-danger waves-effect waves-light" onclick="update_permission()">Update Now</button>
                                        </div>
                                    </div>
                                </div>
                                <!--/div-->

                            </div>
                            {{--service detail close--}}

                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!-- Main Body Ends -->




@endsection