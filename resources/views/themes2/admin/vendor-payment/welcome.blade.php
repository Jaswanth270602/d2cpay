@extends('themes2.admin.layout.header')
@section('content')
    <script type="text/javascript">
        $(document).ready(function () {
            $("#api_id").select2();
            $('#masterbank_id').select2({
                dropdownParent: $('#add_beneficiary_model')
            });
        });

        function count_account_number() {
            var account_number = $("#account_number").val();
            var account_number_digit = account_number.length;
            $("#account_number_digit").text(account_number_digit);
        }

        function add_api() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var api_id = $("#api_id").val();
            var dataString = 'api_id=' + api_id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/vendor-payment/add-api')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () {
                            location.reload(1);
                        }, 3000);
                    } else if (msg.status == 'validation_error') {
                        $("#api_id_errors").text(msg.errors.api_id);
                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function view_beneficiary(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/vendor-payment/view-beneficiary')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        $("#beneficiary_id").val(msg.details.id);
                        $("#beneficiary_name").val(msg.details.beneficiary_name);
                        $("#account_number").val(msg.details.account_number);
                        $("#confirm_account_number").val(msg.details.account_number);
                        $("#ifsc_code").val(msg.details.ifsc_code);
                        $("#masterbank_id").val(msg.details.masterbank_id);
                        $("#status_id").val(msg.details.status_id);
                        $("#add_beneficiary_model").modal('show');
                        count_account_number();
                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function add_beneficiary() {
            $("#beneficiary_btn").hide();
            $("#beneficiary_btn_loader").show();
            var token = $("input[name=_token]").val();
            var beneficiary_id = $("#beneficiary_id").val();
            var beneficiary_name = $("#beneficiary_name").val();
            var account_number = $("#account_number").val();
            var confirm_account_number = $("#confirm_account_number").val();
            var ifsc_code = $("#ifsc_code").val();
            var masterbank_id = $("#masterbank_id").val();
            var status_id = $("#status_id").val();
            var dataString = 'beneficiary_id=' + beneficiary_id + '&beneficiary_name=' + beneficiary_name + '&account_number=' + account_number + '&confirm_account_number=' + confirm_account_number + '&ifsc_code=' + ifsc_code + '&masterbank_id=' + masterbank_id + '&status_id=' + status_id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/vendor-payment/add-beneficiary')}}",
                data: dataString,
                success: function (msg) {
                    $("#beneficiary_btn").show();
                    $("#beneficiary_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () {
                            location.reload(1);
                        }, 3000);
                    } else if (msg.status == 'validation_error') {
                        $("#beneficiary_id_errors").text(msg.errors.beneficiary_id);
                        $("#beneficiary_name_errors").text(msg.errors.beneficiary_name);
                        $("#account_number_errors").text(msg.errors.account_number);
                        $("#confirm_account_number_errors").text(msg.errors.confirm_account_number);
                        $("#ifsc_code_errors").text(msg.errors.ifsc_code);
                        $("#masterbank_id_errors").text(msg.errors.masterbank_id);
                        $("#status_id_errors").text(msg.errors.status_id);
                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function view_transfer(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/vendor-payment/view-transfer-details')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        generate_millisecond();
                        $(".api_name").text(msg.details.api_name);
                        $("#transfer_api_name").val(msg.details.api_name);
                        $("#transfer_id").val(msg.details.id);
                        $("#transfer_beneficiary_name").val(msg.details.beneficiary_name);
                        $("#transfer_account_number").val(msg.details.account_number);
                        $("#transfer_ifsc_code").val(msg.details.ifsc_code);
                        $("#transfer_bank_name").val(msg.details.bank_name);
                        $("#transfer_model").modal('show');
                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });

        }

        function generate_millisecond() {
            var id = 1;
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/generate-millisecond')}}",
                data: dataString,
                success: function (msg) {
                    $("#money_millisecond").val(msg.miliseconds);
                }
            });
        }

        function transfer_now() {
            $("#transferBtn").hide();
            $("#transferBtn_loader").show();
            var token = $("input[name=_token]").val();
            var id = $("#transfer_id").val();
            var beneficiary_name = $("#transfer_beneficiary_name").val();
            var account_number = $("#transfer_account_number").val();
            var ifsc_code = $("#transfer_ifsc_code").val();
            var bank_name = $('#transfer_bank_name').val();
            var amount = $("#transfer_amount").val();
            var transaction_pin = $("#transaction_pin").val();
            var payment_mode = $("#transfer_payment_mode").val();
            var millisecond = $("#money_millisecond").val();
            var otp = $("#transaction_otp").val();
            var dataString = 'id=' + id + '&beneficiary_name=' + beneficiary_name + '&account_number=' + account_number + '&ifsc_code=' + ifsc_code + '&bank_name=' + bank_name + '&amount=' + amount + '&transaction_pin=' + transaction_pin + '&payment_mode=' + payment_mode + '&otp=' + otp + '&dupplicate_transaction=' + millisecond + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/vendor-payment/transfer-now')}}",
                data: dataString,
                success: function (msg) {
                    $("#transferBtn").show();
                    $("#transferBtn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () {
                            location.reload(1);
                        }, 3000);
                    } else if (msg.status == 'validation_error') {
                        $("#transfer_id_errors").text(msg.errors.transfer_id);
                        $("#transfer_beneficiary_name_errors").text(msg.errors.beneficiary_name);
                        $("#transfer_account_number_errors").text(msg.errors.account_number);
                        $("#transfer_ifsc_code_errors").text(msg.errors.ifsc_code);
                        $("#transfer_bank_name_errors").text(msg.errors.bank_name);
                        $("#transfer_amount_errors").text(msg.errors.amount);
                        $("#transaction_pin_errors").text(msg.errors.transaction_pin);
                        $("#transfer_payment_mode_errors").text(msg.errors.payment_mode);
                        $("#transaction_otp_errors").text(msg.errors.otp);
                        $("#dupplicate_transaction_errors").text(msg.errors.dupplicate_transaction);
                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function delete_beneficiary(id) {
            var token = $("input[name=_token]").val();
            var r = confirm("Are you sure you want to delete this beneficiary");
            if (r == true) {
                $(".loader").show();
                var dataString = 'id=' + id + '&_token=' + token;
                $.ajax({
                    type: "POST",
                    url: "{{url('admin/vendor-payment/delete-beneficiary')}}",
                    data: dataString,
                    success: function (msg) {
                        $(".loader").hide();
                        if (msg.status == 'success') {
                            swal("Success", msg.message, "success");
                            setTimeout(function () {
                                location.reload(1);
                            }, 3000);
                        } else {
                            swal("Failed", msg.message, "error");
                        }
                    }
                });
            } else {
                $(".loader").hide();
            }

        }
    </script>
    <!--  Content Area Starts  -->
    <div id="content" class="main-content">
        <!-- Main Body Starts -->
    @include('themes2.admin.layout.breadcrumb')
    <!-- Main Body Starts -->
        <div class="layout-px-spacing">
            <div class="row layout-top-spacing">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
                    <div class="widget dashboard-table">
                        <div class="widget-content">
                                <div class="form-group">
                                    <label>Select Api</label>
                                    <div class="input-group">
                                        <select class="form-control select2" id="api_id">
                                            <option value="">Select Api</option>
                                            @foreach($apis as $value)
                                                <option value="{{ $value->id }}">{{ $value->api_name }} </option>
                                            @endforeach
                                        </select>
                                        <div class="input-group-append">
                                            <button class="input-group-text btn btn-primary" type="button" onclick="add_api()">Add</button>
                                        </div>
                                    </div>
                                    <span class="invalid-feedback d-block" id="api_id_errors"></span>
                                </div>
                        </div>
                    </div>
                </div>
                <!-- REVENUE ENDS-->
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
                    <div class="widget dashboard-table">
                        <div class="widget-heading">
                            <h5 class=""> {{ $page_title }}</h5>
                        </div>
                        <hr>
                        <div class="widget-content">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table text-md-nowrap" id="example1">
                                        <thead>
                                        <tr>
                                            <th class="wd-15p border-bottom-0">Date</th>
                                            <th class="wd-25p border-bottom-0">Api Name</th>
                                            <th class="wd-25p border-bottom-0">Action</th>
                                            <th class="wd-25p border-bottom-0">Transfer</th>
                                            <th class="wd-25p border-bottom-0">Beneficiary Name</th>
                                            <th class="wd-25p border-bottom-0">Account Number</th>
                                            <th class="wd-25p border-bottom-0">IFSC Code</th>
                                            <th class="wd-25p border-bottom-0">Delete</th>
                                            <th class="wd-25p border-bottom-0">Status</th>

                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($vendorpayments as $value)
                                            <tr>
                                                <td>{{ $value->created_at }}</td>
                                                <td>{{ $value->api->api_name }}</td>
                                                <td><a href="#" class="badge badge-info" onclick="view_beneficiary({{ $value->id }})">Add Beneficiary</a></td>
                                                <td><a href="#" class="badge badge-primary" onclick="view_transfer({{ $value->id }})">Transfer Now</a></td>
                                                <td>{{ $value->beneficiary_name }}</td>
                                                <td>{{ $value->account_number }}</td>
                                                <td>{{ $value->ifsc_code }}</td>
                                                <td><button class="btn btn-danger btn-sm" onclick="delete_beneficiary({{ $value->id }})">Delete</button></td>
                                                <td>@if($value->status_id == 1)<span class="badge badge-success">Enabled</span> @else <span class="badge badge-danger">Disabled</span>  @endif</td>

                                            </tr>
                                        @endforeach

                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!-- Main Body Ends -->

    {{--Start add beneficiary model--}}
        <div class="modal  show" id="add_beneficiary_model"data-toggle="modal">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content modal-content-demo">
                    <div class="modal-header">
                        <h6 class="modal-title">Add Beneficiary</h6>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-body">

                            <div class="row">

                                <input type="hidden" id="beneficiary_id">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Select Bank</label>
                                        <select class="form-control select2" id="masterbank_id" style="width: 100%">
                                            <option value="0">Select Bank</option>
                                            @foreach($masterbanks as $value)
                                                <option value="{{ $value->id }}">{{ $value->bank_name }}</option>
                                            @endforeach
                                        </select>
                                        <span class="invalid-feedback d-block" id="masterbank_id_errors"></span>

                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Beneficiary Name</label>
                                        <input type="text" class="form-control" id="beneficiary_name" placeholder="Beneficiary Name">
                                        <span class="invalid-feedback d-block" id="beneficiary_name_errors"></span>
                                    </div>
                                </div>


                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Account Number (<strong id="account_number_digit" style="color: red;">0</strong>)</label>
                                        <input type="password" class="form-control" id="account_number" placeholder="Account Number" onkeyup="count_account_number();">
                                        <span class="invalid-feedback d-block" id="account_number_errors"></span>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Confirm Account Number</label>
                                        <input type="text" class="form-control" id="confirm_account_number" placeholder="Confirm Account Number">
                                        <span class="invalid-feedback d-block" id="confirm_account_number_errors"></span>
                                    </div>
                                </div>


                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">IFSC Code</label>
                                        <input type="text" class="form-control" id="ifsc_code" placeholder="IFSC Code">
                                        <span class="invalid-feedback d-block" id="ifsc_code_errors"></span>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Status</label>
                                        <select class="form-control" id="status_id">
                                            <option value="1">Enabled</option>
                                            <option value="0">Disabled</option>
                                        </select>
                                        <span class="invalid-feedback d-block" id="status_id_errors"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn ripple btn-primary" type="button" id="beneficiary_btn" onclick="add_beneficiary()">Add Beneficiary</button>
                        <button class="btn btn-primary" type="button"  id="beneficiary_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                        <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                    </div>
                </div>
            </div>
        </div>
    {{--End add beneficiary model--}}


    {{--Start Transfer Amount Model--}}
        <div class="modal  show" id="transfer_model"data-toggle="modal">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content modal-content-demo">
                    <div class="modal-header">
                        <h6 class="modal-title">Transfer Amount (<span class="api_name"></span>)</h6>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-body">
                            <div class="row">
                                <input type="hidden" id="transfer_id">

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Vendor Name</label>
                                        <input type="text" class="form-control" id="transfer_api_name" placeholder="Vendor Name" disabled>
                                        <span class="invalid-feedback d-block" id="transfer_api_name_errors"></span>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Bank Name</label>
                                        <input type="text" class="form-control" id="transfer_bank_name" placeholder="Bank Name" disabled>
                                        <span class="invalid-feedback d-block" id="transfer_bank_name_errors"></span>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Beneficiary Name</label>
                                        <input type="text" class="form-control" id="transfer_beneficiary_name" placeholder="Beneficiary Name" disabled>
                                        <span class="invalid-feedback d-block" id="transfer_beneficiary_name_errors"></span>
                                    </div>
                                </div>


                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Account Number</label>
                                        <input type="text" class="form-control" id="transfer_account_number" placeholder="Account Number" disabled>
                                        <span class="invalid-feedback d-block" id="transfer_account_number_errors"></span>
                                    </div>
                                </div>


                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">IFSC Code</label>
                                        <input type="text" class="form-control" id="transfer_ifsc_code" placeholder="IFSC Code" disabled>
                                        <span class="invalid-feedback d-block" id="transfer_ifsc_code_errors"></span>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Payment Mode</label>
                                        <select class="form-control" id="transfer_payment_mode">
                                            <option value="2">IMPS</option>
                                            <option value="1">NEFT</option>
                                            <option value="7">RTGS</option>
                                        </select>
                                        <span class="invalid-feedback d-block" id="transfer_payment_mode_errors"></span>
                                    </div>
                                </div>


                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Amount</label>
                                        <input type="text" class="form-control" id="transfer_amount" placeholder="Amount">
                                        <span class="invalid-feedback d-block" id="transfer_amount_errors"></span>
                                        <span class="invalid-feedback d-block" id="dupplicate_transaction_errors"></span>
                                    </div>
                                </div>


                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Transaction Pin</label>
                                        <input type="password" class="form-control" id="transaction_pin" placeholder="Transaction Pin">
                                        <span class="invalid-feedback d-block" id="transaction_pin_errors"></span>
                                    </div>
                                </div>

                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="name">One Time Password</label>
                                        <input type="password" class="form-control" id="transaction_otp" placeholder="One Time Password">
                                        <span class="invalid-feedback d-block">OTP has been sent to Authorised Person mobile number</span>
                                        <span class="invalid-feedback d-block" id="transaction_otp_errors"></span>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn ripple btn-primary" type="button" id="transferBtn" onclick="transfer_now()">Transfer Now</button>
                        <button class="btn btn-primary" type="button"  id="transferBtn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                        <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" id="money_millisecond">
    {{--End Transfer Amount Model--}}


@endsection