@extends('agent.layout.header')
@section('content')

    <script type="text/javascript">

        $(document).ready(function () {
            $('#bank_ids').select2({
                dropdownParent: $('#add_beneficiary_model')
            });

            get_customer ();
        });

        function get_customer() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var mobile_number = $("#mobile_number").val();
            var dataString = 'mobile_number=' + mobile_number + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/payout/v1/beneficiary-list')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        var recipient_list = msg.beneficiary_list;
                        var html = "";
                        for (var key in recipient_list) {
                            if (recipient_list[key].status_id == 1){
                                var action_btn = '<button class="btn btn-success btn-sm" onclick="transfer_model(' + recipient_list[key].sr_no + ')">Transfer</button>';
                            }else if (recipient_list[key].status_id == 2){
                                var action_btn = '<button class="btn btn-danger btn-sm">Rejected</button>';
                            }else{
                                var action_btn = '<button class="btn btn-warning btn-sm">Pending</button>';
                            }
                            html += '<input type="hidden" value="' + recipient_list[key].account_number + '" id="accountnumber_' + recipient_list[key].sr_no + '">';
                            html += '<input type="hidden" value="' + recipient_list[key].holder_name + '" id="holdername_' + recipient_list[key].sr_no + '">';
                            html += '<input type="hidden" value="' + recipient_list[key].bank_name + '" id="bankname_' + recipient_list[key].sr_no + '">';
                            html += '<input type="hidden" value="' + recipient_list[key].ifsc_code + '" id="ifsccode_' + recipient_list[key].sr_no + '">';
                            html += '<input type="hidden" value="' + recipient_list[key].beneficiary_id + '" id="recipientid_' + recipient_list[key].sr_no + '">';
                            html += "<tr>";
                            html += '<td><button class="btn btn-danger btn-sm" onclick="delete_beneficiary(' + recipient_list[key].sr_no + ')"><i class="far fa-trash-alt"></i></button></td>';
                            html += '<td>' + recipient_list[key].holder_name + '</td>';
                            html += '<td>' + recipient_list[key].ifsc_code + '</td>';
                            html += '<td>' + recipient_list[key].bank_name + ' - ' + recipient_list[key].account_number + ' </td>';
                            html += '<td>' + action_btn  +'</td>';
                            html += "</tr>";
                        }
                        $(".beneficiary_list").html(html);
                        $("#beneficiary-details-label").show();
                    } else if(msg.status == 'validation_error'){
                        $("#mobile_number_errors").text(msg.errors.mobile_number);
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function account_validate() {
            var token = $("input[name=_token]").val();
            var mobile_number = $("#mobile_number").val();
            var bank_id = $("#bank_ids").val();
            var ifsc_code = $("#ifsc_code").val();
            var account_number = $("#account_number").val();
            var latitude = $("#inputLatitude").val();
            var longitude = $("#inputLongitude").val();
            if (latitude && longitude){
                $("#validate_btn").hide();
                $("#validate_btn_loader").show();
                var dataString = 'bank_id=' + bank_id + '&ifsc_code=' + ifsc_code + '&account_number=' + account_number + '&mobile_number=' + mobile_number + '&latitude=' + latitude + '&longitude=' + longitude + '&_token=' + token;
                $.ajax({
                    type: "POST",
                    url: "{{url('agent/payout/v1/account-validate')}}",
                    data: dataString,
                    success: function (msg) {
                        $("#validate_btn").show();
                        $("#validate_btn_loader").hide();
                        if (msg.status == 'success') {
                            $("#beneficiary_name").val(msg.beneficiary_name);
                        } else if(msg.status == 'validation_error'){
                            $("#bank_id_errors").text(msg.errors.bank_id);
                            $("#ifsc_code_errors").text(msg.errors.ifsc_code);
                            $("#account_number_errors").text(msg.errors.account_number);
                        }else{
                            swal("Failed", msg.message, "error");
                        }
                    }
                });
            }else{
                getLocation();
                alert('Please allow this site to access your location');
            }
        }

        function add_beneficiary() {
            $("#beneficiary_btn").hide();
            $("#beneficiary_btn_loader").show();
            var token = $("input[name=_token]").val();
            var mobile_number = $("#mobile_number").val();
            var bank_id = $("#bank_ids").val();
            var ifsc_code = $("#ifsc_code").val();
            var account_number = $("#account_number").val();
            var beneficiary_name  = $("#beneficiary_name").val();
            var dataString = 'bank_id=' + bank_id + '&ifsc_code=' + ifsc_code + '&account_number=' + account_number + '&mobile_number=' + mobile_number + '&beneficiary_name=' + beneficiary_name + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/payout/v1/add-beneficiary')}}",
                data: dataString,
                success: function (msg) {
                    $("#beneficiary_btn").show();
                    $("#beneficiary_btn_loader").hide();
                    if (msg.status == 'success') {
                        $("#bank_id").val('');
                        $("#ifsc_code").val('');
                        $("#account_number").val('');
                        $("#beneficiary_name").val('');
                        $('#add_beneficiary_model').modal('hide');
                        get_customer();
                    } else if(msg.status == 'validation_error'){
                        $("#bank_id_errors").text(msg.errors.bank_id);
                        $("#ifsc_code_errors").text(msg.errors.ifsc_code);
                        $("#account_number_errors").text(msg.errors.account_number);
                        $("#beneficiary_name_errors").text(msg.errors.beneficiary_name);
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function delete_beneficiary(id) {
            var holder_name = $("#holdername_"+id).val();
            var account_number = $("#accountnumber_"+id).val();
            var recipient_id = $("#recipientid_"+id).val();
            swal({
                    title: "Are you sure?",
                    text: 'you want to delete this Beneficiary ('+ holder_name +' - '+ account_number +')',
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Yes, delete it!",
                    cancelButtonText: "No, cancel",
                    closeOnConfirm: false,
                    closeOnCancel: false
                },
                function(isConfirm) {
                    if (isConfirm) {
                        $(".loader").show();
                        var token = $("input[name=_token]").val();
                        var mobile_number = $("#mobile_number").val();
                        var dataString = 'recipient_id=' + recipient_id + '&mobile_number=' + mobile_number +  '&_token=' + token;
                        $.ajax({
                            type: "POST",
                            url: "{{url('agent/payout/v1/delete-beneficiary')}}",
                            data: dataString,
                            success: function (msg) {
                                $(".loader").hide();
                                if (msg.status == 'success') {
                                    swal("Deleted!", msg.message, "success");
                                    get_customer();
                                }else{
                                    swal("Failed", msg.message, "error");
                                }
                            }
                        });

                    }
                }
            );

        }

        function transfer_model(sr_no) {
            generate_millisecond();
            var accountnumber = $("#accountnumber_"+sr_no).val();
            var holdername = $("#holdername_"+sr_no).val();
            var bankname = $("#bankname_"+sr_no).val();
            var ifsccode = $("#ifsccode_"+sr_no).val();
            var recipientid = $("#recipientid_"+sr_no).val();

            $("#transfer_account_number").val(accountnumber);
            $("#transfer_holder_name").val(holdername);
            $("#transfer_bank_name").val(bankname);
            $("#transfer_ifsc_code").val(ifsccode);
            $("#transfer_recipient_id").val(recipientid);
            $("#transfer_model").modal('show');
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
            var token = $("input[name=_token]").val();
            var mobile_number = $("#mobile_number").val();
            var account_number = $("#transfer_account_number").val();
            var holder_name = $("#transfer_holder_name").val();
            var bank_name = $("#transfer_bank_name").val();
            var ifsc_code = $("#transfer_ifsc_code").val();
            var recipient_id = $("#transfer_recipient_id").val();
            var amount = $("#amount").val();
            var password = $("#password").val();
            var millisecond = $("#money_millisecond").val();
            var latitude = $("#inputLatitude").val();
            var longitude = $("#inputLongitude").val();
            if (latitude && longitude){
                $("#trnasfer_btn").hide();
                $("#trnasfer_btn_loader").show();
                var dataString = 'mobile_number=' + mobile_number + '&account_number=' + account_number + '&holder_name=' + holder_name + '&bank_name=' + bank_name + '&ifsc_code=' + ifsc_code + '&recipient_id=' + recipient_id +  '&amount=' + amount +  '&password=' + password + '&latitude=' + latitude + '&longitude=' + longitude + '&dupplicate_transaction=' + millisecond + '&_token=' + token;
                $.ajax({
                    type: "POST",
                    url: "{{url('agent/payout/v1/transfer-now')}}",
                    data: dataString,
                    success: function (msg) {
                        $("#trnasfer_btn").show();
                        $("#trnasfer_btn_loader").hide();
                        if (msg.status == 'success') {
                            $('#transfer_model').modal('hide');
                            swal("Success", msg.message, "success");
                            setTimeout(function () { location.reload(1); }, 3000);
                        } else if(msg.status == 'validation_error'){
                            $("#mobile_number_errors").text(msg.errors.mobile_number);
                            $("#transfer_account_number_errors").text(msg.errors.account_number);
                            $("#transfer_holder_name_errors").text(msg.errors.holder_name);
                            $("#transfer_bank_name_errors").text(msg.errors.bank_name);
                            $("#transfer_ifsc_code_errors").text(msg.errors.ifsc_code);
                            $("#transfer_recipient_id_errors").text(msg.errors.recipient_id);
                            $("#amount_errors").text(msg.errors.amount);
                            $("#password_errors").text(msg.errors.password);
                        }else{
                            swal("Failed", msg.message, "error");
                        }
                    }
                });
            }else{
                getLocation();
                alert('Please allow this site to access your location');
            }
        }
    </script>

    <!-- main-content-body -->
    <div class="main-content-body">

        <!-- row -->
        <div class="row row-sm">
            {{-- @include('agent.aeps.left_side')--}}

            <!-- Col -->
            <div class="col-lg-8 col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <div class="mb-4 main-content-label">{{ $page_title }}</div>
                        <hr>


                        <div class="form-body">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="input-group">
                                        <input type="text" id="mobile_number" class="form-control" value="{{ Auth::User()->mobile }}" readonly>
                                        <span class="input-group-btn">
										<button class="btn ripple btn-primary br-tl-0 br-bl-0" type="button" onclick="get_customer()">Search</button>

									</span>
                                    </div>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="mobile_number_errors"></li>
                                    </ul>
                                </div>


                            </div>
                        </div>


                    </div>


                </div>


                <div class="row" id="beneficiary-details-label" style="display: none;'">
                    <div class="col-lg-12 col-md-12">
                        <div class="card">
                            <div class="card-header pb-0">
                                <div class="d-flex justify-content-between">
                                    <h4 class="card-title mg-b-2 mt-2">Beneficiary List</h4>
                                    <button class="btn btn-danger btn-sm" data-target="#add_beneficiary_model" data-toggle="modal">Add Beneficiary</button>

                                    <i class="mdi mdi-dots-horizontal text-gray"></i>
                                </div>
                                <hr>
                            </div>
                            <div class="card-body">


                                <div class="table-responsive mb-0">
                                    <table class="table table-striped mg-b-0 text-md-nowrap">
                                        <thead>
                                        <tr>
                                            <th>Select</th>
                                            <th>Name</th>
                                            <th>IFsc Code</th>
                                            <th>Bank Name - Account Number</th>
                                            <th>Action</th>

                                        </tr>
                                        </thead>
                                        <tbody class="beneficiary_list">

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>



                </div>


            </div>
            <!-- /Col -->


        </div>
        <!-- /row -->

        <!-- row -->



    </div>
    <!-- /row -->
    </div>
    <!-- /container -->
    </div>
    <!-- /main-content -->




    <div class="modal  show" id="add_beneficiary_model"data-toggle="modal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Add Beneficiary</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">

                        <div class="row">

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Select Bank</label>
                                    <select class="form-control select2" id="bank_ids" style="width: 100%">
                                        <option value="">Select Bank</option>
                                        @foreach($masterbank as $value)
                                            <option value="{{ $value->bank_id }}">{{ $value->bank_name }}</option>
                                        @endforeach
                                    </select>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="bank_id_errors"></li>
                                    </ul>

                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">IFSC Code</label>
                                    <input type="text" class="form-control" id="ifsc_code" placeholder="IFSC Code">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="ifsc_code_errors"></li>
                                    </ul>

                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Account Number</label>
                                    <div class="input-group">
                                        <input type="text" id="account_number" placeholder="Account Number" class="form-control">
                                    </div>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="account_number_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Beneficiary Name</label>
                                    <input type="text" class="form-control" id="beneficiary_name" placeholder="Beneficiary Name">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="beneficiary_name_errors"></li>
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
                    <h6 class="modal-title"><i class="fas fa-plus-circle"></i> Payout Transfer</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <input type="hidden" id="transfer_recipient_id">
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Account Number</label>
                                    <input type="text" id="transfer_account_number" class="form-control" placeholder="Account Number" readonly>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="transfer_account_number_errors"></li>
                                        <li class="parsley-required" id="transfer_recipient_id_errors"></li>
                                    </ul>

                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Holder Name </label>
                                    <input type="text" id="transfer_holder_name" class="form-control" placeholder="Holder Name" readonly>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="transfer_holder_name_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Bank Name </label>
                                    <input type="text" id="transfer_bank_name" class="form-control" placeholder="Bank Name" readonly>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="transfer_bank_name_errors"></li>
                                    </ul>
                                </div>
                            </div>


                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">IFSC Code</label>
                                    <input type="text" id="transfer_ifsc_code" class="form-control" placeholder="IFSC Code" readonly>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="transfer_ifsc_code_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Amount</label>
                                    <input type="text" id="amount" class="form-control" placeholder="Amount">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="amount_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Login Password</label>
                                    <input type="password" id="password" class="form-control" placeholder="Login Password">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="password_errors"></li>
                                    </ul>
                                </div>
                            </div>






                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn ripple btn-primary" type="button" id="trnasfer_btn" onclick="transfer_now()">Tranfer Now</button>
                    <button class="btn btn-primary" type="button"  id="trnasfer_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                    <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" id="money_millisecond">
@endsection