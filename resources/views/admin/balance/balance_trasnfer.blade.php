@extends('admin.layout.header')
@section('content')

    <script type="text/javascript">

        function amountToWords() {
            var a = ['', 'one ', 'two ', 'three ', 'four ', 'five ', 'six ', 'seven ', 'eight ', 'nine ', 'ten ', 'eleven ', 'twelve ', 'thirteen ', 'fourteen ', 'fifteen ', 'sixteen ', 'seventeen ', 'eighteen ', 'nineteen '];
            var b = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];

            var num = $("#view_amount").val();
            if ((num = num.toString()).length > 9) return 'overflow';
            n = ('000000000' + num).substr(-9).match(/^(\d{2})(\d{2})(\d{2})(\d{1})(\d{2})$/);
            if (!n) return;
            var str = '';
            str += (n[1] != 0) ? (a[Number(n[1])] || b[n[1][0]] + ' ' + a[n[1][1]]) + 'crore ' : '';
            str += (n[2] != 0) ? (a[Number(n[2])] || b[n[2][0]] + ' ' + a[n[2][1]]) + 'lakh ' : '';
            str += (n[3] != 0) ? (a[Number(n[3])] || b[n[3][0]] + ' ' + a[n[3][1]]) + 'thousand ' : '';
            str += (n[4] != 0) ? (a[Number(n[4])] || b[n[4][0]] + ' ' + a[n[4][1]]) + 'hundred ' : '';
            str += (n[5] != 0) ? ((str != '') ? 'and ' : '') + (a[Number(n[5])] || b[n[5][0]] + ' ' + a[n[5][1]]) + 'only ' : '';
            $("#amountToWordsText").text(str);
        }

        function view_users(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/view-transfer-users')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        generate_millisecond();
                        $("#view_id").val(msg.details.id);
                        $("#view_mobile").val(msg.details.mobile);
                        $("#view_name").val(msg.details.name);
                        $("#view_balance").val(msg.details.balance);
                        $("#view_balance_model").modal('show');
                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }


        function balance_trasnfer() {
            var token = $("input[name=_token]").val();
            var id = $("#view_id").val();
            var remark = $("#view_remark").val();
            var amount = $("#view_amount").val();
            var confirm_amount = $("#view_confirm_amount").val();
            var password = $("#view_password").val();
            var millisecond = $("#trasnfer_millisecond").val();
            var latitude = $("#inputLatitude").val();
            var longitude = $("#inputLongitude").val();
            var wallet_type = $("#wallet_type").val();
            if (latitude && longitude){
                $("#transfer_btn").hide();
                $("#transfer_btn_loader").show();
                var dataString = 'id=' + id + '&remark=' + remark + '&amount=' + amount + '&confirm_amount=' + confirm_amount + '&latitude=' + latitude + '&longitude=' + longitude + '&dupplicate_transaction=' + millisecond + '&password=' + password +  '&wallet_type=' + wallet_type + '&_token=' + token;
                $.ajax({
                    type: "POST",
                    url: "{{url('admin/balance-transfer-now')}}",
                    data: dataString,
                    success: function (msg) {
                        $("#transfer_btn").show();
                        $("#transfer_btn_loader").hide();
                        if (msg.status == 'success') {
                            swal("Success", msg.message, "success");
                            setTimeout(function () {
                                location.reload(1);
                            }, 3000);
                        } else if (msg.status == 'validation_error') {
                            $("#view_remark_errors").text(msg.errors.remark);
                            $("#view_amount_errors").text(msg.errors.amount);
                            $("#view_confirm_amount_errors").text(msg.errors.confirm_amount);
                            $("#view_password_errors").text(msg.errors.password);
                            $("#dupplicate_transaction_errors").text(msg.errors.dupplicate_transaction);
                        } else {
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
                    $("#trasnfer_millisecond").val(msg.miliseconds);
                }
            });
        }
    </script>

    <div class="main-content-body">
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


                            <table id='my_table' class="table text-md-nowrap">
                                <thead>
                                <tr>
                                    <td>User Id</td>
                                    <td>name</td>
                                    <td>mobile</td>
                                    <td>Member type</td>
                                    <td>Payout balance</td>
                                    <td>Payin balance</td>
                                    <td>Action</td>

                                </tr>
                                </thead>
                            </table>

                            <!-- Script -->
                            <script type="text/javascript">
                                $(document).ready(function(){

                                    // DataTable
                                    $('#my_table').DataTable({
                                        processing: true,
                                        serverSide: true,
                                        ajax: "{{url('admin/balance-transfer-api')}}",
                                        columns: [
                                            { data: 'id' },
                                            { data: 'name' },
                                            { data: 'mobile' },
                                            { data: 'member_type' },
                                            { data: 'normal_balance' },
                                            { data: 'aeps_balance' },
                                            { data: 'action' },
                                        ],
                                    });
                                    $("input[type='search']").wrap("<form>");
                                    $("input[type='search']").closest("form").attr("autocomplete","off");
                                });
                            </script>
                        </div>
                    </div>
                </div>
            </div>
            <!--/div-->

        </div>

    </div>
    </div>
    </div>




    <div class="modal  show" id="view_balance_model"data-toggle="modal">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Balance Transfer</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">

                        <input type="hidden" id="view_id">
                        <input type="hidden" id="trasnfer_millisecond">

                        <div class="row">

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Wallet Type</label>
                                    <select class="form-control" id="wallet_type">
                                        <option value="1">Payout Wallet</option>
                                        <option value="2">Payin Wallet</option>
                                    </select>

                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="wallet_type_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">User Name</label>
                                    <input type="text" id="view_name" class="form-control" placeholder="User Name" disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Mobile Number</label>
                                    <input type="text" id="view_mobile" class="form-control" placeholder="Mobile Number" disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Current Balance</label>
                                    <input type="text" id="view_balance" class="form-control" placeholder="Current Balance" disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Remark</label>
                                    <input type="text" id="view_remark" class="form-control" placeholder="Payment Remark">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_remark_errors"></li>
                                    </ul>
                                </div>
                            </div>


                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Amount</label>
                                    <input type="text" id="view_amount" class="form-control" placeholder="Transfer Amount" onkeyup="amountToWords();">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_amount_errors"></li>
                                        <li class="parsley-required" id="dupplicate_transaction_errors"></li>
                                    </ul>
                                    <strong style="color: red;" id="amountToWordsText"></strong>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Confirm Amount</label>
                                    <input type="text" id="view_confirm_amount" class="form-control" placeholder="Confirm Amount">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_confirm_amount_errors"></li>
                                    </ul>
                                </div>
                            </div>


                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Login Password</label>
                                    <input type="password" id="view_password" class="form-control" placeholder="Login Password">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_password_errors"></li>
                                    </ul>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn ripple btn-primary" type="button" id="transfer_btn" onclick="balance_trasnfer()">Transfer Now</button>
                    <button class="btn btn-primary" type="button"  id="transfer_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                    <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
    </div>


@endsection