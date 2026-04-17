@extends('agent.layout.header')
@section('content')
    <script type="text/javascript">

        function amountToWords (){
            var a = ['','one ','two ','three ','four ', 'five ','six ','seven ','eight ','nine ','ten ','eleven ','twelve ','thirteen ','fourteen ','fifteen ','sixteen ','seventeen ','eighteen ','nineteen '];
            var b = ['', '', 'twenty','thirty','forty','fifty', 'sixty','seventy','eighty','ninety'];

            var num = $("#amount").val();
            if ((num = num.toString()).length > 9) return 'overflow';
            n = ('000000000' + num).substr(-9).match(/^(\d{2})(\d{2})(\d{2})(\d{1})(\d{2})$/);
            if (!n) return; var str = '';
            str += (n[1] != 0) ? (a[Number(n[1])] || b[n[1][0]] + ' ' + a[n[1][1]]) + 'crore ' : '';
            str += (n[2] != 0) ? (a[Number(n[2])] || b[n[2][0]] + ' ' + a[n[2][1]]) + 'lakh ' : '';
            str += (n[3] != 0) ? (a[Number(n[3])] || b[n[3][0]] + ' ' + a[n[3][1]]) + 'thousand ' : '';
            str += (n[4] != 0) ? (a[Number(n[4])] || b[n[4][0]] + ' ' + a[n[4][1]]) + 'hundred ' : '';
            str += (n[5] != 0) ? ((str != '') ? 'and ' : '') + (a[Number(n[5])] || b[n[5][0]] + ' ' + a[n[5][1]]) + 'only ' : '';
            $("#amountToWordsText").text(str);
        }

        function count_account_number (){
            var account_number = $("#account_number").val();
            var account_number_digit = account_number.length;
            $("#account_number_digit").text(account_number_digit);
        }

        $(document).ready(function () {
            $('#bank_id').select2({
                dropdownParent: $('#add_beneficiary_model')
            });

            $('#mobile_number').keypress(function(e){
                if(e.keyCode==13)
                // alert('ok');
                    $('#search_btn').click();
            });
        });
        function get_customer() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var mobile_number = $("#mobile_number").val();
            var dataString = 'mobile_number=' + mobile_number + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/money/v2/get-customer')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        $(".name").text(msg.name);
                        $(".mobile_number").text(msg.mobile_number);
                        $(".total_limit").text(msg.total_limit);
                        $("#sender_name").val(msg.name);
                        $("#sender-details-label").show();
                        $("#beneficiary-details-label").show();
                        $("#add-sender-label").hide();
                        get_all_beneficiary();
                    } else if(msg.status == 'validation_error'){
                        $("#mobile_number_errors").text(msg.errors.mobile_number);
                        $("#sender-details-label").hide();
                        $("#beneficiary-details-label").hide();
                    }else{
                        $("#add-sender-label").show();
                        $("#sender-details-label").hide();
                        $("#beneficiary-details-label").hide();
                    }
                }
            });
        }

        function get_all_beneficiary() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var mobile_number = $("#mobile_number").val();
            var sender_name = $("#sender_name").val();
            var dataString = 'mobile_number=' + mobile_number + '&sender_name=' + sender_name + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/money/v2/get-all-beneficiary')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                         $(".beneficiary_list").show('');
                        var recipient_list = msg.recipient_list;
                        var html = "";
                        for (var key in recipient_list) {
                            html += '<input type="hidden" value="' + recipient_list[key].recipient_account + '" id="accountnumber_' + recipient_list[key].sr_no + '">';
                            html += '<input type="hidden" value="' + recipient_list[key].recipient_name + '" id="holdername_' + recipient_list[key].sr_no + '">';
                            html += '<input type="hidden" value="' + recipient_list[key].recipient_bank + '" id="bankname_' + recipient_list[key].sr_no + '">';
                            html += '<input type="hidden" value="' + recipient_list[key].recipient_ifsc + '" id="ifsccode_' + recipient_list[key].sr_no + '">';
                            html += '<input type="hidden" value="' + recipient_list[key].recipient_id + '" id="recipientid_' + recipient_list[key].sr_no + '">';
                            html += "<tr>";
                            html += '<td><input type="radio" name="selectBene" id="selectBene" value="' + recipient_list[key].sr_no + '" onchange="select_beneficiary()"></td>';
                            html += '<td>' + recipient_list[key].recipient_name + '</td>';
                            html += '<td>' + recipient_list[key].recipient_ifsc + '</td>';
                            html += '<td>' + recipient_list[key].recipient_bank + ' - ' + recipient_list[key].recipient_account + ' </td>';
                            html += '<td><button class="btn btn-danger" onclick="delete_beneficiary(' + recipient_list[key].sr_no + ')"><i class="far fa-trash-alt"></i></button></td>';
                            html += "</tr>";
                        }
                        $(".beneficiary_list").html(html);
                    } else if(msg.status == 'validation_error'){
                        $("#mobile_number_errors").text(msg.errors.mobile_number);
                    }else{
                         $(".beneficiary_list").hide('');
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function select_beneficiary() {
            $('#transfer_confirm_btn').prop('disabled', false);
            generate_millisecond();
            var sr_no = $("#selectBene:checked").val();
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
        }

        function get_ifsc_code() {
            var token = $("input[name=_token]").val();
            var bank_id = $("#bank_id").val();
            var dataString = 'bank_id=' + bank_id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/money/v2/get-ifsc-code')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        $("#ifsc_code").val(msg.ifsc_code);
                    } else if(msg.status == 'validation_error'){
                        $("#bank_id_errors").text(msg.errors.bank_id);
                    }
                }
            });

        }

        function account_validate() {
            var token = $("input[name=_token]").val();
            var mobile_number = $("#mobile_number").val();
            var bank_id = $("#bank_id").val();
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
                    url: "{{url('agent/money/v2/account-validate')}}",
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
            var bank_id = $("#bank_id").val();
            var ifsc_code = $("#ifsc_code").val();
            var account_number = $("#account_number").val();
            var beneficiary_name  = $("#beneficiary_name").val();
            var dataString = 'bank_id=' + bank_id + '&ifsc_code=' + ifsc_code + '&account_number=' + account_number + '&mobile_number=' + mobile_number + '&beneficiary_name=' + beneficiary_name + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/money/v2/add-beneficiary')}}",
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
                            url: "{{url('agent/money/v2/delete-beneficiary')}}",
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

        function add_sender() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var mobile_number = $("#mobile_number").val();
            var first_name = $("#first_name").val();
            var last_name = $("#last_name").val();
            var pin_code = $("#pin_code").val();
            var address = $("#address").val();
            var state = $("#state").val();
            var dataString = 'mobile_number=' + mobile_number + '&first_name=' + first_name + '&last_name=' + last_name + '&pin_code=' + pin_code + '&address=' + address + '&state=' + state + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/money/v2/add-sender')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        $("#sender_confirm_model").modal('show');
                    } else if(msg.status == 'validation_error'){
                        $("#first_name_errors").text(msg.errors.first_name);
                        $("#last_name_errors").text(msg.errors.last_name);
                        $("#pin_code_errors").text(msg.errors.pin_code);
                        $("#address_errors").text(msg.errors.address);
                        $("#state_errors").text(msg.errors.state);
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function resend_otp() {
            $("#resend_otp_btn").hide();
            $("#resend_otp_btn_loader").show();
            var token = $("input[name=_token]").val();
            var mobile_number = $("#mobile_number").val();
            var first_name = $("#first_name").val();
            var last_name = $("#last_name").val();
            var pin_code = $("#pin_code").val();
            var address = $("#address").val();
            var state = $("#state").val();
            var dataString = 'mobile_number=' + mobile_number + '&first_name=' + first_name + '&last_name=' + last_name + '&pin_code=' + pin_code + '&address=' + address + '&state=' + state + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/money/v2/resend-otp')}}",
                data: dataString,
                success: function (msg) {
                    $("#resend_otp_btn").show();
                    $("#resend_otp_btn_loader").hide();
                    if (msg.status == 'success') {
                        alert(msg.message);
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function sender_confirmation() {
            $("#sender_confirm_btn").hide();
            $("#sender_confirm_btn_loader").show();
            var token = $("input[name=_token]").val();
            var mobile_number = $("#mobile_number").val();
            var otp = $("#sender_otp").val();
            var dataString = 'mobile_number=' + mobile_number + '&otp=' + otp + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/money/v2/sender-confirmation')}}",
                data: dataString,
                success: function (msg) {
                    $("#sender_confirm_btn").show();
                    $("#sender_confirm_btn_loader").hide();
                    if (msg.status == 'success') {
                        alert(msg.message);
                        get_customer();
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function get_charges() {
            $("#getcharge_btn").hide();
            $("#getcharge_btn_loader").show();
            var token = $("input[name=_token]").val();
            var amount = $("#amount").val();
            var dataString = 'amount=' + amount +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/money/v2/get-charges')}}",
                data: dataString,
                success: function (msg) {
                    $("#getcharge_btn").show();
                    $("#getcharge_btn_loader").hide();
                    if (msg.status == 'success') {
                        var list = msg.list;
                        var html = "";
                        for (var key in list) {
                            html += '<div class="d-flex tasks"><div class="mb-0"><div class="h6 fs-15 mb-0">' + list[key].amount + '</div></div><span class="float-right ml-auto">' + list[key].charges + '</span><span class="float-right ml-auto">' + list[key].total_amount + '</span></div>';
                        }
                        $(".charges_list").html(html);
                        $("#get_charges_model").modal('show');
                    }else if(msg.status == 'validation_error'){
                        $("#amount_errors").text(msg.errors.amount);
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function transfer_confirm_model() {
            var account_number = $("#transfer_account_number").val();
            var ifsc_code = $("#transfer_ifsc_code").val();
            var amount = $("#amount").val();
            var mobile_number = $("#mobile_number").val();
            var holder_name = $("#transfer_holder_name").val();
            var bank_name = $("#transfer_bank_name").val();
            $(".confirm_account_number").text(account_number);
            $(".confirm_ifsc_code").text(ifsc_code);
            $(".confirm_amount").text(amount);
            $(".confirm_mobile_number").text(mobile_number);
            $(".confirm_holder_name").text(holder_name);
            $(".confirm_bank_name").text(bank_name);
            $("#transaction_confirmation_model").modal('show');
        }

        function transfer_now() {
            var token = $("input[name=_token]").val();
            var recipient_id = $("#transfer_recipient_id").val();
            var account_number = $("#transfer_account_number").val();
            var ifsc_code = $("#transfer_ifsc_code").val();
            var mode = $("#transfer_mode").val();
            var amount = $("#amount").val();
            var mobile_number = $("#mobile_number").val();
            var millisecond = $("#money_millisecond").val();
            var transaction_pin = $("#transaction_pin").val();
            var latitude = $("#inputLatitude").val();
            var longitude = $("#inputLongitude").val();
            if (latitude && longitude){
                $("#transfer_btn").hide();
                $("#transfer_btn_loader").show();
                var dataString = 'recipient_id=' + recipient_id + '&account_number=' + account_number + '&ifsc_code=' + ifsc_code + '&mode=' + mode + '&amount=' + amount + '&mobile_number=' + mobile_number +  '&dupplicate_transaction=' + millisecond + '&transaction_pin=' + transaction_pin + '&latitude=' + latitude + '&longitude=' + longitude + '&_token=' + token;
                $.ajax({
                    type: "POST",
                    url: "{{url('agent/money/v2/transfer-now')}}",
                    data: dataString,
                    success: function (msg) {
                        $("#transfer_btn").show();
                        $("#transfer_btn_loader").hide();
                        if (msg.status == 'success') {
                            $("#transaction_confirmation_model").modal('hide');
                            $("#amount").val('');
                            $("#print_url").attr('href', msg.benedetails.print_url);
                            $("#thermal_print").attr('href', msg.benedetails.thermal_print);
                            $(".receipt_beneficiary_name").html(msg.benedetails.beneficiary_name);
                            $(".receipt_account_number").html(msg.benedetails.account_number);
                            $(".receipt_bank_name").html(msg.benedetails.bank_name);
                            $(".receipt_ifsc").html(msg.benedetails.ifsc);
                            $(".receipt_remiter_name").html(msg.benedetails.remiter_name);
                            $(".receipt_remiter_number").html(msg.benedetails.remiter_number);
                            $(".receipt_payment_mode").html(msg.benedetails.payment_mode);
                            $(".receipt_full_amount").html(msg.benedetails.full_amount);
                            var html = "";
                            var re = msg.reports;
                            for (var key in re) {
                                var status = re[key].status;
                                if (status == 'Success'){
                                    var status_call = '<span class="badge badge-success">Success</span>';
                                }else if(status == 'Failure'){
                                    var status_call = '<span class="badge badge-danger">Failure</span>';
                                }else if(status == 'Fail'){
                                    var status_call = '<span class="badge badge-danger">Failure</span>';
                                }else if(status == 'Pending'){
                                    var status_call = '<span class="badge badge-warning">Pending</span>';
                                }
                                html += "<tr>";
                                html += "<td>" + re[key].report_id + "</td>";
                                html += "<td>" + re[key].utr_number + "</td>";
                                html += "<td>" + re[key].amount + "</td>";
                                html += "<td>" + re[key].charges + "</td>";
                                html += "<td>" + status_call + "</td>";
                                html += "</tr>";
                            }

                            $("#receipt_html").html(html);
                            $("#money_receipt_model").modal('show');
                        } else if(msg.status == 'validation_error'){
                            $("#transfer_recipient_id_errors").text(msg.errors.recipient_id);
                            $("#transfer_account_number_errors").text(msg.errors.account_number);
                            $("#transfer_ifsc_code_errors").text(msg.errors.ifsc_code);
                            $("#transfer_mode_errors").text(msg.errors.mode);
                            $("#mobile_number_errors").text(msg.errors.mobile_number);
                            $("#amount_errors").text(msg.errors.amount);
                            $("#dupplicate_transaction_errors").text(msg.errors.dupplicate_transaction);
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
    </script>
    @include('agent.money-transfer.money_receipt')
    <input type="hidden" id="sender_name">
    <div class="main-content-body">

        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">{{ $page_title }}</h6>
                            <hr>
                        </div>
                        <div class="form-body">
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="input-group">
                                        <input type="text" id="mobile_number" placeholder="Mobile Number" class="form-control"
                                               oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
                                               maxlength="10"
                                        >
                                        <span class="input-group-btn">
										<button class="btn ripple btn-primary br-tl-0 br-bl-0" type="button" onclick="get_customer()" id="search_btn">Search</button>
									</span>
                                    </div>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="mobile_number_errors"></li>
                                    </ul>
                                </div>

                                <div class="col-sm-8" style="display: none;" id="sender-details-label">
                                    <div class="row">
                                        <div class="col-md-4 col-6 text-center">
                                            <div class="task-box primary mb-0">
                                                <p class="mb-0 tx-12">Name</p>
                                                <h3 class="mb-0 name"></h3>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-6 text-center">
                                            <div class="task-box   mb-0">
                                                <p class="mb-0 tx-12">Mobile Number</p>
                                                <h3 class="mb-0 mobile_number"></h3>
                                            </div>
                                        </div>

                                        <div class="col-md-4 col-6 text-center">
                                            <div class="task-box danger  mb-0">
                                                <p class="mb-0 tx-12">Total Limit</p>
                                                <h3 class="mb-0 total_limit"></h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>


        <div class="row" id="add-sender-label" style="display: none;'">
            <div class="col-lg-12 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Add Sender</h6>
                            <hr>
                        </div>
                        <div class="row row-sm mg-b-20">
                            <div class="col-lg-4">
                                <p class="mg-b-10">First Name</p>
                                <input type="text" class="form-control" placeholder="First Name" id="first_name">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="first_name_errors"></li>
                                </ul>
                            </div>

                            <div class="col-lg-4">
                                <p class="mg-b-10">Last Name</p>
                                <input type="text" class="form-control" placeholder="Last Name" id="last_name">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="last_name_errors"></li>
                                </ul>
                            </div>

                            <div class="col-lg-4">
                                <p class="mg-b-10">Pin Code</p>
                                <input type="text" class="form-control" placeholder="Pin Code" id="pin_code">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="pin_code_errors"></li>
                                </ul>
                            </div>

                            <div class="col-lg-4">
                                <p class="mg-b-10">Address</p>
                                <input type="text" class="form-control" placeholder="Address" id="address">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="address_errors"></li>
                                </ul>
                            </div>

                            <div class="col-lg-4">
                                <p class="mg-b-10">State</p>
                                <input type="text" class="form-control" placeholder="State" id="state">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="state_errors"></li>
                                </ul>
                            </div>


                        </div></div>
                    <div class="modal-footer">
                        <button class="btn ripple btn-primary" type="button" onclick="add_sender()">Add Sender</button>
                    </div>
                </div>
            </div>

        </div>

        <div class="row" id="beneficiary-details-label" style="display: none;'">
            <div class="col-lg-8 col-md-12">
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

            <div class="col-lg-4 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Transaction</h6>
                            <hr>
                        </div>
                        <div class="mb-4">
                            <p class="mg-b-10">Account Number</p>
                            <input type="hidden" id="transfer_recipient_id">
                            <input type="text" class="form-control" placeholder="Account Number" id="transfer_account_number" disabled>
                            <ul class="parsley-errors-list filled">
                                <li class="parsley-required" id="transfer_account_number_errors"></li>
                                <li class="parsley-required" id="transfer_recipient_id_errors"></li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <p class="mg-b-10">Holder Name</p>
                            <input type="text" class="form-control" placeholder="Holder Name" id="transfer_holder_name" readonly>
                            <ul class="parsley-errors-list filled">
                                <li class="parsley-required" id="transfer_holder_name_errors"></li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <p class="mg-b-10">Bank Name</p>
                            <input type="text" class="form-control" placeholder="Bank Name" id="transfer_bank_name" disabled>
                            <ul class="parsley-errors-list filled">
                                <li class="parsley-required" id="transfer_bank_name_errors"></li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <p class="mg-b-10">IFSC Code</p>
                            <input type="text" class="form-control" placeholder="IFSC Code" id="transfer_ifsc_code" disabled>
                            <ul class="parsley-errors-list filled">
                                <li class="parsley-required" id="transfer_ifsc_code_errors"></li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <p class="mg-b-10">Payment Mode</p>
                            <select class="form-control" id="transfer_mode">
                                <option value="2">IMPS</option>
                                <option value="1">NEFT</option>
                            </select>
                            <ul class="parsley-errors-list filled">
                                <li class="parsley-required" id="transfer_mode_errors"></li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <p class="mg-b-10">Amount</p>
                            <input type="text" class="form-control" placeholder="Amount" id="amount" onkeyup="amountToWords();">
                            <ul class="parsley-errors-list filled">
                                <li class="parsley-required" id="amount_errors"></li>
                                <li class="parsley-required" id="dupplicate_transaction_errors"></li>
                            </ul>
                            <strong style="color: red;" id="amountToWordsText"></strong>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button class="btn ripple btn-primary" type="button"  onclick="transfer_confirm_model()" id="transfer_confirm_btn" disabled>Transfer Now</button>
                        <button class="btn ripple btn-danger" type="button" id="getcharge_btn" onclick="get_charges()">Get Charge</button>
                        <button class="btn ripple btn-danger" type="button" id="getcharge_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                    </div>
                </div>
            </div>



        </div>


        <!-- /row -->
    </div>
    </div>
    </div>

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
                                    <select class="form-control select2" id="bank_id" style="width: 100%" onchange="get_ifsc_code(this)">
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
                                    <label for="name">Account Number (<strong id="account_number_digit" style="color: red;">0</strong>)</label>
                                    <div class="input-group">
                                        <input type="text" id="account_number" placeholder="Account Number" class="form-control" onkeyup="count_account_number();">
                                        <span class="input-group-btn">
										<button class="btn ripple btn-danger br-tl-0 br-bl-0" type="button" id="validate_btn" onclick="account_validate()">Validate</button>
                                        <button class="btn ripple btn-danger br-tl-0 br-bl-0" type="button"  id="validate_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
									</span>
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



    <div class="modal  show" id="sender_confirm_model"data-toggle="modal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Confirm Sender</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">


                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">OTP</label>
                                    <input type="text" class="form-control" id="sender_otp" placeholder="Enter OTP">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="sender_otp_errors"></li>
                                    </ul>

                                </div>
                            </div>


                        </div>

                    </div>


                </div>

                <div class="modal-footer">
                    <button class="btn ripple btn-primary" type="button" id="sender_confirm_btn" onclick="sender_confirmation()">Confirm Sender</button>
                    <button class="btn ripple btn-primary" type="button" id="sender_confirm_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                    <button class="btn ripple btn-danger" type="button" id="resend_otp_btn" onclick="resend_otp()">Resend Otp</button>
                    <button class="btn ripple btn-danger" type="button"  id="resend_otp_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                </div>
            </div>
        </div>
    </div>
@endsection