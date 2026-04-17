@extends('admin.layout.header')
@section('content')

    <script type="text/javascript">
        $(document).ready(function ()
        {
            $("#api_id").select2();
            $('#masterbank_id').select2({
                dropdownParent: $('#add_beneficiary_model')
            });

        });

        function count_account_number (){
            var account_number = $("#account_number").val();
            var account_number_digit = account_number.length;
            $("#account_number_digit").text(account_number_digit);
        }

        function add_api() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var api_id = $("#api_id").val();
            var dataString = 'api_id=' + api_id +   '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/vendor-payment/add-api')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#api_id_errors").text(msg.errors.api_id);
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }
        
        function view_beneficiary(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id +   '&_token=' + token;
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
                    }else{
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
            var dataString = 'beneficiary_id=' + beneficiary_id + '&beneficiary_name=' + beneficiary_name +  '&account_number=' + account_number + '&confirm_account_number=' + confirm_account_number + '&ifsc_code=' + ifsc_code + '&masterbank_id=' + masterbank_id + '&status_id=' + status_id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/vendor-payment/add-beneficiary')}}",
                data: dataString,
                success: function (msg) {
                    $("#beneficiary_btn").show();
                    $("#beneficiary_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#beneficiary_id_errors").text(msg.errors.beneficiary_id);
                        $("#beneficiary_name_errors").text(msg.errors.beneficiary_name);
                        $("#account_number_errors").text(msg.errors.account_number);
                        $("#confirm_account_number_errors").text(msg.errors.confirm_account_number);
                        $("#ifsc_code_errors").text(msg.errors.ifsc_code);
                        $("#masterbank_id_errors").text(msg.errors.masterbank_id);
                        $("#status_id_errors").text(msg.errors.status_id);
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }
        
        function view_transfer(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id +   '&_token=' + token;
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
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });

        }

        function generate_millisecond() {
            var id = 1;
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id +  '&_token=' + token;
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
            var dataString = 'id=' + id + '&beneficiary_name=' + beneficiary_name + '&account_number=' + account_number + '&ifsc_code=' + ifsc_code + '&bank_name=' + bank_name + '&amount=' + amount + '&transaction_pin=' + transaction_pin + '&payment_mode=' + payment_mode + '&otp=' + otp +  '&dupplicate_transaction=' + millisecond + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/vendor-payment/transfer-now')}}",
                data: dataString,
                success: function (msg) {
                    $("#transferBtn").show();
                    $("#transferBtn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
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
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }
        
        function delete_beneficiary(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var r = confirm("R u sure delete this id");
            if (r == true) {
                var dataString = 'id=' + id + '&_token=' + token;
                $.ajax({
                    type: "POST",
                    url: "{{url('admin/vendor-payment/delete-beneficiary')}}",
                    data: dataString,
                    success: function (msg) {
                        $(".loader").hide();
                        if (msg.status == 'success') {
                            swal("Success", msg.message, "success");
                            setTimeout(function () { location.reload(1); }, 3000);
                        }else{
                            swal("Failed", msg.message, "error");
                        }
                    }
                });
            }else{
                $(".loader").hide();
            }

        }

    </script>


    <div class="main-content-body">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card">
                    <div class="card-body">

                        <div class="row">


                            <div class="col-lg-5 col-md-8 form-group mg-b-0">
                                <label class="form-label">Api: <span class="tx-danger">*</span></label>
                                <select class="form-control select2" id="api_id" style="width: 100%;">
                                    <option value="">Select Api</option>
                                    @foreach($apis as $value)
                                        <option value="{{ $value->id }}">{{ $value->api_name }} </option>
                                    @endforeach
                                </select>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="api_id_errors"></li>
                                </ul>
                            </div>

                            <div class="col-lg-2 col-md-4 mg-t-10 mg-sm-t-25">
                                <button class="btn btn-main-primary pd-x-20" type="button" onclick="add_api()"><i class="fas fa-plus-square"></i> Add Now</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row row-sm">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">{{ $page_title }}</h4>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="display responsive nowrap" id="example1">
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
                                        <td><button class="btn btn-primary btn-sm" onclick="view_beneficiary({{ $value->id }})">Add Beneficiary</button></td>
                                        <td><button class="btn btn-info btn-sm" onclick="view_transfer({{ $value->id }})">Transfer Now</button></td>
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
            <!--/div-->

        </div>

    </div>
    </div>
    </div>



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
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="masterbank_id_errors"></li>
                                    </ul>

                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Beneficiary Name</label>
                                    <input type="text" class="form-control" id="beneficiary_name" placeholder="Beneficiary Name">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="beneficiary_name_errors"></li>
                                    </ul>
                                </div>
                            </div>


                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Account Number (<strong id="account_number_digit" style="color: red;">0</strong>)</label>
                                    <input type="password" class="form-control" id="account_number" placeholder="Account Number" onkeyup="count_account_number();">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="account_number_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Confirm Account Number</label>
                                    <input type="text" class="form-control" id="confirm_account_number" placeholder="Confirm Account Number">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="confirm_account_number_errors"></li>
                                    </ul>
                                </div>
                            </div>


                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">IFSC Code</label>
                                    <input type="text" class="form-control" id="ifsc_code" placeholder="IFSC Code">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="ifsc_code_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Status</label>
                                    <select class="form-control" id="status_id">
                                        <option value="1">Enabled</option>
                                        <option value="0">Disabled</option>
                                    </select>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="status_id_errors"></li>
                                    </ul>
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
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="transfer_api_name_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Bank Name</label>
                                    <input type="text" class="form-control" id="transfer_bank_name" placeholder="Bank Name" disabled>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="transfer_bank_name_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Beneficiary Name</label>
                                    <input type="text" class="form-control" id="transfer_beneficiary_name" placeholder="Beneficiary Name" disabled>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="transfer_beneficiary_name_errors"></li>
                                    </ul>
                                </div>
                            </div>


                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Account Number</label>
                                    <input type="text" class="form-control" id="transfer_account_number" placeholder="Account Number" disabled>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="transfer_account_number_errors"></li>
                                    </ul>
                                </div>
                            </div>


                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">IFSC Code</label>
                                    <input type="text" class="form-control" id="transfer_ifsc_code" placeholder="IFSC Code" disabled>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="transfer_ifsc_code_errors"></li>
                                    </ul>
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
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="transfer_payment_mode_errors"></li>
                                    </ul>
                                </div>
                            </div>


                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Amount</label>
                                    <input type="text" class="form-control" id="transfer_amount" placeholder="Amount">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="transfer_amount_errors"></li>
                                        <li class="parsley-required" id="dupplicate_transaction_errors"></li>
                                    </ul>
                                </div>
                            </div>


                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Transaction Pin</label>
                                    <input type="password" class="form-control" id="transaction_pin" placeholder="Transaction Pin">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="transaction_pin_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">One Time Password</label>
                                    <input type="password" class="form-control" id="transaction_otp" placeholder="One Time Password">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required">OTP has been sent to Authorised Person mobile number</li>
                                        <li class="parsley-required" id="transaction_otp_errors"></li>
                                    </ul>
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
@endsection