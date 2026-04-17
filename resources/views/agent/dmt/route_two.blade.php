@extends('agent.layout.header')
@section('content')

    <script type="text/javascript">

        $(document).ready(function () {
            $("#bank_id").select2();
        });
        function getCustomer() {
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
                    $("#ad1").val(msg.ad1);
                    $("#ad2").val(msg.ad2);
                    if (msg.status == 'error') {
                        $("#sender-details-label").hide();
                        $("#add-sender-model").modal('hide');
                        $("#mobile_number_errors").text(msg.errors.mobile_number);
                    } else if (msg.status == 'success') {
                        closeInputs();
                        $("#sender_name").val(msg.data.name);
                        $(".name").text(msg.data.name);
                        $(".mobile_number").text(msg.data.mobile_number);
                        $(".total_limit").text(msg.data.total_limit);
                        $("#sender-details-label").show();
                        $("#add-sender-model").modal('hide');
                        $("#beneficiary-details-label").show();
                        getAllBeneficiary();
                    } else if (msg.status == 'pending') {
                        if (msg.data.is_otp == 1){
                            $(".add-sender-otp-label").show();
                            $("#addSederBtn").attr('onclick', 'confirmSender()');
                            $("#addSederBtn").text('Confirm Sender');
                        }
                        $("#sender-details-label").hide();
                        $("#add-sender-model").modal('show');
                    } else {
                        $("#sender-details-label").hide();
                    }
                }
            });
        }

        function getAllBeneficiary(){
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
                        var beneficiaries = msg.beneficiaries;
                        var html = "";
                        for (var key in beneficiaries) {

                            if (beneficiaries[key].is_verify == 1){
                                var is_verify = '<i class="fas fa-check-square" style="color: green;"></i>';
                            }else{
                                var is_verify = '<i class="fas fa-times" style="color: red;"></i>';
                            }

                            if (beneficiaries[key].status_id == 1){
                                var status_id = '<button type="button" class="btn btn-success btn-sm" onclick="viewTransfer(' + beneficiaries[key].id + ')">Transfer</button>';
                            }else{
                                var status_id = '<button type="button" class="btn btn-warning btn-sm" onclick="activateBeneficiary(' + beneficiaries[key].id + ')">Activate</button>';
                            }

                            html += '<input type="hidden" value="' + beneficiaries[key].beneficiary_id + '" id="beneficiaryId_' + beneficiaries[key].id + '">';
                            html += '<input type="hidden" value="' + beneficiaries[key].account_number + '" id="accountNumber_' + beneficiaries[key].id + '">';
                            html += '<input type="hidden" value="' + beneficiaries[key].ifsc_code + '" id="ifscCode_' + beneficiaries[key].id + '">';
                            html += '<input type="hidden" value="' + beneficiaries[key].beneficiary_name + '" id="beneficiaryName_' + beneficiaries[key].id + '">';
                            html += '<input type="hidden" value="' + beneficiaries[key].bank_name + '" id="bankName_' + beneficiaries[key].id + '">';

                            html += "<tr>";
                            html += '<td>' + beneficiaries[key].beneficiary_name + ' ' + is_verify + '</td>';
                            html += '<td>' + beneficiaries[key].ifsc_code + '</td>';
                            html += '<td>' + beneficiaries[key].bank_name + ' - ' + beneficiaries[key].account_number + ' </td>';
                            html += '<td>' + status_id + '</td>';
                            html += '<td><button type="button" class="btn btn-danger btn-sm" onclick="deleteBeneficiary(' + beneficiaries[key].id + ')">Delete</button></td>';
                            html += "</tr>";
                        }
                        $(".beneficiary_list").html(html);
                    }
                }
            });
        }

        function addSender() {
            $("#addSederBtn").hide();
            $("#addSederBtn_loader").show();
            var token = $("input[name=_token]").val();
            var mobile_number = $("#mobile_number").val();
            var first_name = $("#first_name").val();
            var last_name = $("#last_name").val();
            var pincode = $("#pincode").val();
            var state = $("#state").val();
            var address = $("#address").val();
            var ad1 = $("#ad1").val();
            var ad2 = $("#ad2").val();
            var dataString = 'mobile_number=' + mobile_number + '&first_name=' + first_name + '&last_name=' + last_name + '&pincode=' + pincode + '&state=' + state + '&address=' + address + '&ad1=' + ad1 + '&ad2=' + ad2 + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/money/v2/add-sender')}}",
                data: dataString,
                success: function (msg) {
                    $("#addSederBtn").show();
                    $("#addSederBtn_loader").hide();
                    if (msg.status == 'error') {
                        $("#first_name_errors").text(msg.errors.first_name);
                        $("#last_name_errors").text(msg.errors.last_name);
                        $("#pincode_errors").text(msg.errors.pincode);
                        $("#state_errors").text(msg.errors.state);
                        $("#address_errors").text(msg.errors.address);
                    }else if (msg.status == 'success'){
                        swal("Success", msg.message, "success");
                        getCustomer();
                    }else if (msg.status == 'pending'){
                        $("#ad1").val(msg.ad1);
                        $("#ad2").val(msg.ad2);
                        $(".add-sender-otp-label").show();
                        $("#addSederBtn").attr('onclick', 'confirmSender()');
                        $("#addSederBtn").text('Confirm Sender');
                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function confirmSender (){
            $("#addSederBtn").hide();
            $("#addSederBtn_loader").show();
            var token = $("input[name=_token]").val();
            var mobile_number = $("#mobile_number").val();
            var otp = $("#sender_otp").val();
            var ad1 = $("#ad1").val();
            var ad2 = $("#ad2").val();
            var dataString = 'mobile_number=' + mobile_number + '&otp=' + otp + '&ad1=' + ad1 + '&ad2=' + ad2 + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/money/v2/confirm-sender')}}",
                data: dataString,
                success: function (msg) {
                    $("#addSederBtn").show();
                    $("#addSederBtn_loader").hide();
                    if (msg.status == 'error') {
                        $("#sender_otp_errors").text(msg.errors.otp);
                    }else if (msg.status == 'success'){
                        $(".add-sender-otp-label").hide();
                        $("#add-sender-model").modal('show');
                        $("#addSederBtn").attr('onclick', 'addSender()');
                        $("#addSederBtn").text('Add Sender');
                        swal("Success", msg.message, "success");
                        getCustomer();
                    }else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function resendOtp (){
            $("#resendOtpBtn").hide();
            $("#resendOtpBtn_loader").show();
            var token = $("input[name=_token]").val();
            var mobile_number = $("#mobile_number").val();
            var first_name = $("#first_name").val();
            var last_name = $("#last_name").val();
            var pincode = $("#pincode").val();
            var state = $("#state").val();
            var address = $("#address").val();
            var ad1 = $("#ad1").val();
            var ad2 = $("#ad2").val();
            var dataString = 'mobile_number=' + mobile_number + '&first_name=' + first_name + '&last_name=' + last_name + '&pincode=' + pincode + '&state=' + state + '&address=' + address + '&ad1=' + ad1 + '&ad2=' + ad2 + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/money/v2/sender-resend-otp')}}",
                data: dataString,
                success: function (msg) {
                    $("#resendOtpBtn").show();
                    $("#resendOtpBtn_loader").hide();
                    if (msg.status == 'error') {
                        $("#first_name_errors").text(msg.errors.first_name);
                        $("#last_name_errors").text(msg.errors.last_name);
                        $("#pincode_errors").text(msg.errors.pincode);
                        $("#state_errors").text(msg.errors.state);
                        $("#address_errors").text(msg.errors.address);
                    }else if (msg.status == 'success'){
                        swal("Success", msg.message, "success");
                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function getIfscCode() {
            var token = $("input[name=_token]").val();
            var bank_id = $("#bank_id").val();
            var dataString = 'bank_id=' + bank_id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/money/v2/get-ifsc-code')}}",
                data: dataString,
                success: function (msg) {
                    if (msg.status == 'success') {
                        $("#ifsc_code").val(msg.data.ifsc);
                    }
                }
            });
        }

        function activateBeneficiary (id){
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var mobile_number = $("#mobile_number").val();
            var beneficiary_name = $("#beneficiaryName_" + id).val();
            var account_number = $("#accountNumber_" + id).val();
            var ifsc_code = $("#ifscCode_" + id).val();
            var dataString = 'mobile_number=' + mobile_number +  '&ifsc_code=' + ifsc_code + '&account_number=' + account_number + '&beneficiary_name=' + beneficiary_name + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/money/v2/add-beneficiary')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'error') {
                        $("#mobile_number_errors").text(msg.errors.mobile_number);
                        $("#bank_id_errors").text(msg.errors.bank_id);
                        $("#ifsc_code_errors").text(msg.errors.ifsc_code);
                        $("#account_number_errors").text(msg.errors.account_number);
                        $("#beneficiary_name_errors").text(msg.errors.beneficiary_name);
                    }else if (msg.status == 'success'){
                        swal("Success", msg.message, "success");
                        getCustomer();
                    }else if (msg.status == 'pending'){
                        $("#ad1").val(msg.ad1);
                        $("#ad2").val(msg.ad2);
                        $("#beneficiary-confirm-model").modal('show');
                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function addBeneficiary (){
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var mobile_number = $("#mobile_number").val();
            var bank_id = $("#bank_id").val();
            var ifsc_code = $("#ifsc_code").val();
            var account_number = $("#account_number").val();
            var beneficiary_name = $("#beneficiary_name").val();
            var dataString = 'mobile_number=' + mobile_number + '&bank_id=' + bank_id + '&ifsc_code=' + ifsc_code + '&account_number=' + account_number + '&beneficiary_name=' + beneficiary_name + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/money/v2/add-beneficiary')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'error') {
                        $("#mobile_number_errors").text(msg.errors.mobile_number);
                        $("#bank_id_errors").text(msg.errors.bank_id);
                        $("#ifsc_code_errors").text(msg.errors.ifsc_code);
                        $("#account_number_errors").text(msg.errors.account_number);
                        $("#beneficiary_name_errors").text(msg.errors.beneficiary_name);
                    }else if (msg.status == 'success'){
                        swal("Success", msg.message, "success");
                        getCustomer();
                    }else if (msg.status == 'pending'){
                        $("#ad1").val(msg.ad1);
                        $("#ad2").val(msg.ad2);
                        $("#beneficiary-confirm-model").modal('show');
                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function verifyBeneficiary (){
            $("#beneficiaryVerifyBtn").hide();
            $("#beneficiaryVerifyBtn_loader").show();
            var token = $("input[name=_token]").val();
            var mobile_number = $("#mobile_number").val();
            var ad1 = $("#ad1").val();
            var ad2 = $("#ad2").val();
            var otp = $("#beneficiary_otp").val();
            var dataString = 'mobile_number=' + mobile_number + '&ad1=' + ad1 + '&ad2=' + ad2 + '&otp=' + otp + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/money/v2/confirm-beneficiary')}}",
                data: dataString,
                success: function (msg) {

                    $("#beneficiaryVerifyBtn").show();
                    $("#beneficiaryVerifyBtn_loader").hide();
                    if (msg.status == 'error') {
                        $("#mobile_number_errors").text(msg.errors.mobile_number);
                        $("#beneficiary_otp_errors").text(msg.errors.otp);
                    }else if (msg.status == 'success') {
                        $("#beneficiary-confirm-model").modal('hide');
                        swal("Success", msg.message, "success");
                        getCustomer();
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function deleteBeneficiary (id){
            var result = confirm("Are you sure you want to delete this beneficiary?");
            if (result) {
                $(".loader").show();
                var token = $("input[name=_token]").val();
                var beneficiary_id = $("#beneficiaryId_" + id).val();
                var mobile_number = $("#mobile_number").val();
                var dataString = 'mobile_number=' + mobile_number + '&beneficiary_id=' + beneficiary_id +  '&_token=' + token;
                $.ajax({
                    type: "POST",
                    url: "{{url('agent/money/v2/delete-beneficiary')}}",
                    data: dataString,
                    success: function (msg) {
                        $(".loader").hide();
                        if (msg.status == 'success'){
                            swal("Success", msg.message, "success");
                            getCustomer();
                        }else if (msg.status == 'pending'){
                            $("#ad1").val(msg.ad1);
                            $("#ad2").val(msg.ad2);
                            $("#beneficiary-delete-otp-model").modal('show');
                        } else {
                            swal("Failed", msg.message, "error");
                        }
                    }
                });
            }
        }

        function confirmDeleteBeneficiary (){
            $("#deleteBeneficiaryVerifyBtn").hide();
            $("#deleteBeneficiaryVerifyBtn_loader").show();
            var token = $("input[name=_token]").val();
            var mobile_number = $("#mobile_number").val();
            var ad1 = $("#ad1").val();
            var ad2 = $("#ad2").val();
            var otp = $("#delete_beneficiary_otp").val();
            var dataString = 'mobile_number=' + mobile_number + '&ad1=' + ad1 + '&ad2=' + ad2 + '&otp=' + otp + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/money/v2/confirm-delete-beneficiary')}}",
                data: dataString,
                success: function (msg) {
                    $("#deleteBeneficiaryVerifyBtn").show();
                    $("#deleteBeneficiaryVerifyBtn_loader").hide();
                    if (msg.status == 'error') {
                        $("#mobile_number_errors").text(msg.errors.mobile_number);
                        $("#delete_beneficiary_otp_errors").text(msg.errors.otp);
                    }else if (msg.status == 'success') {
                        $("#beneficiary-delete-otp-model").modal('hide');
                        swal("Success", msg.message, "success");
                        getCustomer();
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function accountVerify() {
            var latitude = $("#inputLatitude").val();
            var longitude = $("#inputLongitude").val();
            if (latitude && longitude) {
                $(".loader").show();
                var token = $("input[name=_token]").val();
                var mobile_number = $("#mobile_number").val();
                var bank_id = $("#bank_id").val();
                var ifsc_code = $("#ifsc_code").val();
                var account_number = $("#account_number").val();
                var dataString = 'mobile_number=' + mobile_number + '&bank_id=' + bank_id + '&ifsc_code=' + ifsc_code + '&account_number=' + account_number + '&latitude=' + latitude + '&longitude=' + longitude + '&_token=' + token;
                $.ajax({
                    type: "POST",
                    url: "{{url('agent/money/v2/account-verify')}}",
                    data: dataString,
                    success: function (msg) {
                        getWalletBal();
                        $(".loader").hide();
                        if (msg.status == 'error') {
                            $("#bank_id_errors").text(msg.errors.bank_id);
                            $("#ifsc_code_errors").text(msg.errors.ifsc_code);
                            $("#account_number_errors").text(msg.errors.account_number);
                        } else if (msg.status == 'success') {
                            $("#beneficiary_name").val(msg.data.beneficiary_name);
                        } else if (msg.status == 'pending') {
                            swal("Pending", msg.message, "warning");
                        } else {
                            swal("Failed", msg.message, "error");
                        }
                    }
                });
            } else {
                getLocation();
                alert('Please allow this site to access your location');
            }
        }

        function viewTransfer (id){
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var mobile_number = $("#mobile_number").val();
            var beneficiary_id = $("#beneficiaryId_" + id).val();
            var account_number = $("#accountNumber_" + id).val();
            var ifsc_code = $("#ifscCode_" + id).val();
            var beneficiary_name = $("#beneficiaryName_" + id).val();
            var bank_name = $("#bankName_" + id).val();
            var dataString = 'mobile_number=' + mobile_number + '&beneficiary_id=' + beneficiary_id + '&account_number=' + account_number + '&account_number=' + account_number + '&ifsc_code=' + ifsc_code + '&beneficiary_name=' + beneficiary_name + '&bank_name=' + bank_name +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/money/v2/view-account-transfer')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        generate_millisecond();
                        $(".account_number").text(msg.data.account_number);
                        $(".ifsc_code").text(msg.data.ifsc_code);
                        $(".beneficiary_name").text(msg.data.beneficiary_name);
                        $(".bank_name").text(msg.data.bank_name);
                        // input details
                        $("#amount").val('');
                        $("#transfer_account_number").val(msg.data.account_number);
                        $("#transfer_ifsc_code").val(msg.data.ifsc_code);
                        $("#transfer_beneficiary_id").val(msg.data.beneficiary_id);
                        $("#amount").attr('placeholder', msg.data.placeholder);
                        $("#view-transaction-confirm-model").modal('show');
                        $(".transactionChargesListDiv").hide();
                    }else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }


        function transferNow (){
            var latitude = $("#inputLatitude").val();
            var longitude = $("#inputLongitude").val();
            if (latitude && longitude) {
                $("#transferBtn").hide();
                $("#transferBtn_loader").show();
                var token = $("input[name=_token]").val();
                var mobile_number = $("#mobile_number").val();
                var beneficiary_id = $("#transfer_beneficiary_id").val();
                var account_number = $("#transfer_account_number").val();
                var ifsc_code = $("#transfer_ifsc_code").val();
                var channel_id = $("#channel_id").val();
                var amount = $("#amount").val();
                var transaction_pin = $("#transaction_pin").val();
                var transactionMiliseconds = $("#transactionMiliseconds").val();
                var dataString = 'mobile_number=' + mobile_number + '&beneficiary_id=' + beneficiary_id + '&account_number=' + account_number + '&ifsc_code=' + ifsc_code + '&channel_id=' + channel_id + '&amount=' + amount + '&transaction_pin=' + transaction_pin + '&dupplicate_transaction=' + transactionMiliseconds + '&latitude=' + latitude + '&longitude=' + longitude +  '&_token=' + token;
                $.ajax({
                    type: "POST",
                    url: "{{url('agent/money/v2/transfer-now')}}",
                    data: dataString,
                    success: function (msg) {
                        getWalletBal();
                        $("#transferBtn").show();
                        $("#transferBtn_loader").hide();
                        if (msg.status == 'success') {
                            $("#view-transaction-confirm-model").modal('hide');
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
                                if (status == 'Success') {
                                    var status_call = '<span class="badge badge-success">Success</span>';
                                } else if (status == 'Failure' || status == 'Failed') {
                                    var status_call = '<span class="badge badge-danger">Failure</span>';
                                }else if (status == 'Pending') {
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
                            $("#transaction-receipt-model").modal('show');
                        }else {
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
            var dataString = 'id=' + id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/generate-millisecond')}}",
                data: dataString,
                success: function (msg) {
                    $("#transactionMiliseconds").val(msg.miliseconds);
                }
            });
        }

        function SearchByAccount() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var account_number = $("#search_account_number").val();
            var dataString = 'account_number=' + account_number + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/money/v2/search-by-account')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        var beneficiaries = msg.beneficiaries;
                        var html = "";
                        for (var key in beneficiaries) {
                            html += "<tr>";
                            html += '<td>' + beneficiaries[key].remiter_number + '</td>';
                            html += '<td>' + beneficiaries[key].account_number + '</td>';
                            html += '<td>' + beneficiaries[key].ifsc + '</td>';
                            html += '<td>' + beneficiaries[key].bank_name + '</td>';
                            html += '<td>' + beneficiaries[key].name + '</td>';
                            html += '<td><button class="btn btn-danger" onclick="SearchByAccountConfirm(' + beneficiaries[key].remiter_number + ')">Select</td>';
                            html += "</tr>";
                        }
                        $(".searchByAccountBeneficiaries").html(html);
                        $("#search-by-account-beneficiaries").modal('show');
                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function SearchByAccountConfirm (mobile_number){
            $("#mobile_number").val(mobile_number);
            $("#search_account_number").val('');
            $("#search-by-account-beneficiaries").modal('hide');
            document.getElementById("search_btn").click();
        }



        function getCharges (){
            $("#getChargesBtn").hide();
            $("#getChargesBtn_loader").show();
            var token = $("input[name=_token]").val();
            var amount = $("#amount").val();
            var dataString = 'amount=' + amount + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/money/v2/get-transaction-charges')}}",
                data: dataString,
                success: function (msg) {
                    $("#getChargesBtn").show();
                    $("#getChargesBtn_loader").hide();
                    if (msg.status == 'success') {
                        var list = msg.list;
                        var html = "";
                        for (var key in list) {
                            html += '<div class="d-flex tasks"><div class="mb-0"><div class="h6 fs-15 mb-0">' + list[key].amount + '</div></div><span class="float-right ml-auto">' + list[key].charges + '</span><span class="float-right ml-auto">' + list[key].total_amount + '</span></div>';
                        }
                        $(".transactionChargesListDiv").show();
                        $(".transactionChargesList").html(html);
                        $("#get_charges_model").modal('show');
                    } else if (msg.status == 'validation_error') {
                        $("#amount_errors").text(msg.errors.amount);
                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

    </script>


    @include('agent.dmt.common')
    <div class="main-content-body">

        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">{{ $page_title }}</h4>

                            <div style="display: none;" id="sender-details-label">
                                <button class="btn btn-warning">Name : <span class="name"></span></button>
                                <button class="btn btn-primary">Mobile Number : <span class="mobile_number"></span></button>
                                <button class="btn btn-danger">Total Limit : <span class="total_limit"></span></button>
                            </div>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>

                    <div class="card-body">
                        <div class="form-body">
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="input-group">
                                        <input type="text" id="mobile_number" placeholder="Mobile Number"
                                               class="form-control"
                                               oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
                                               maxlength="10">
                                        <span class="input-group-btn">
										    <button class="btn ripple btn-primary br-tl-0 br-bl-0" type="button" onclick="getCustomer()" id="search_btn">Search</button>
									    </span>
                                    </div>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="mobile_number_errors"></li>
                                    </ul>
                                </div>

                                <div class="col-sm-4"></div>

                                <div class="col-sm-4">
                                    <div class="input-group">
                                        <input type="text" id="search_account_number"
                                               placeholder="Search By Account Number"
                                               class="form-control">
                                        <span class="input-group-btn">
										<button class="btn ripple btn-primary br-tl-0 br-bl-0" type="button"
                                                onclick="SearchByAccount()" id="searchByaccountBtn">Search</button>
									</span>
                                    </div>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="search_account_number_errors"></li>
                                    </ul>
                                </div>


                            </div>
                        </div>

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
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">


                        <div class="table-responsive mb-0">
                            <table class="table table-striped mg-b-0 text-md-nowrap">
                                <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>IFsc Code</th>
                                    <th>Bank Name - Account Number</th>
                                    <th>Action</th>
                                    <th>Delete</th>

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
                            <h6 class="card-title mb-1">Add Beneficiary</h6>
                            <hr>
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Bank Name</label>
                                    <select class="form-control select2" id="bank_id" style="width: 100%" onchange="getIfscCode()">
                                        <option value="">Select Bank</option>
                                        @foreach($masterbank as $value)
                                            <option value="{{ $value->id }}">{{ $value->bank_name }}</option>
                                        @endforeach
                                    </select>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="bank_id_errors"></li>
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

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Account Number</label>
                                    <div class="input-group">
                                        <input type="text" id="account_number" placeholder="Account Number" class="form-control">
                                        <span class="input-group-btn">
                                            <button class="btn ripple btn-danger br-tl-0 br-bl-0" type="button"  onclick="accountVerify()">Verify</button>
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
                    <div class="modal-footer">
                        <button class="btn ripple btn-primary" type="button"  onclick="addBeneficiary()" id="addBeneficiaryBtn">Add Beneficiary</button>
                    </div>
                </div>
            </div>

        </div>


        <!-- /row -->
    </div>
    </div>
    </div>
@endsection