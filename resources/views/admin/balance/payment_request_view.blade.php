@extends('admin.layout.header')
@section('content')

    <script type="text/javascript">
        $(document).ready(function () {
            $("#status_id").select2();
            $("#fromdate").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: ('yy-mm-dd'),
            });
            $("#todate").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: ('yy-mm-dd'),
            });
        });



        function view_request(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/view-payment-request')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        generate_millisecond();
                        $("#id").val(msg.details.id);
                        $("#user_id").val(msg.details.user_id);
                        $("#payment_date").val(msg.details.payment_date);
                        $("#paymentmethod_id").val(msg.details.paymentmethod_id);
                        $("#bankdetail_id").val(msg.details.bankdetail_id);
                        $("#amount").val(msg.details.amount);
                        $("#bankref").val(msg.details.bankref);
                        $("#status_id").val(msg.details.status_id);
                        $("#view_request_model").modal('show');
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });

        }

        function update_balance () {
            var token = $("input[name=_token]").val();
            var id = $("#id").val();
            var status_id = $("#status_ids").val();
            var password = $("#password").val();
            var millisecond = $("#trasnfer_millisecond").val();
            var latitude = $("#inputLatitude").val();
            var longitude = $("#inputLongitude").val();
            if (latitude && longitude){
                $("#transfer_btn").hide();
                $("#transfer_btn_loader").show();
                var dataString = 'id=' + id + '&status_id=' + status_id + '&password=' + password + '&latitude=' + latitude + '&longitude=' + longitude +  '&dupplicate_transaction=' + millisecond + '&_token=' + token;
                $.ajax({
                    type: "POST",
                    url: "{{url('admin/update-payment-request')}}",
                    data: dataString,
                    success: function (msg) {
                        $("#transfer_btn").show();
                        $("#transfer_btn_loader").hide();
                        if (msg.status == 'success') {
                            swal("Success", msg.message, "success");
                            setTimeout(function () { location.reload(1); }, 3000);
                        } else if(msg.status == 'validation_error'){
                            $("#status_ids_errors").text(msg.errors.status_id);
                            $("#password_errors").text(msg.errors.password);
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
                    $("#trasnfer_millisecond").val(msg.miliseconds);
                }
            });
        }

        function download_report() {
            $("#download_btn").hide();
            $("#download_btn_loader").show();
            var token = $("input[name=_token]").val();
            var download_menu_name = $("#download_menu_name").val();
            var download_password = $("#download_password").val();
            var fromdate = $("#fromdate").val();
            var todate = $("#todate").val();
            var status_id = $("#status_id").val();
            var dataString = 'menu_name=' + download_menu_name + '&password=' + download_password + '&fromdate=' + fromdate + '&todate=' + todate + '&status_id=' + status_id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/download/payment-request-view')}}",
                data: dataString,
                success: function (msg) {
                    $("#download_btn").show();
                    $("#download_btn_loader").hide();
                    if (msg.status == 'success') {
                        $("#download-label").show();
                        $("#download_link").attr('href', msg.download_link);
                    } else if(msg.status == 'validation_error'){
                        $("#download_menu_name_errors").text(msg.errors.menu_name);
                        $("#download_password_errors").text(msg.errors.password);
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function view_edit_request(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/view-payment-request')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        $("#edit_id").val(msg.edit_details.id);
                        $("#edit_paymentmethod_id").val(msg.edit_details.paymentmethod_id);
                        $("#edit_bankdetail_id").val(msg.edit_details.bankdetail_id);
                        $("#edit_bankref").val(msg.edit_details.bankref);
                        $("#view_edit_request_model").modal('show');
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }


        function edit_request_now() {
            $("#edit_request_btn").hide();
            $("#edit_request_btn_loader").show();
            var token = $("input[name=_token]").val();
            var id = $("#edit_id").val();
            var paymentmethod_id = $("#edit_paymentmethod_id").val();
            var bankdetail_id = $("#edit_bankdetail_id").val();
            var bankref = $("#edit_bankref").val();
            var password = $("#edit_password").val();
            var dataString = 'id=' + id + '&paymentmethod_id=' + paymentmethod_id +  '&bankdetail_id=' + bankdetail_id + '&bankref=' + bankref + '&password=' + password + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/payment-request-edit-now')}}",
                data: dataString,
                success: function (msg) {
                    $("#edit_request_btn").show();
                    $("#edit_request_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#edit_paymentmethod_id_errors").text(msg.errors.paymentmethod_id);
                        $("#edit_bankdetail_id_errors").text(msg.errors.bankdetail_id);
                        $("#edit_bankref_errors").text(msg.errors.bankref);
                        $("#edit_password_errors").text(msg.errors.password);
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

    </script>



    <div class="main-content-body">

        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card">
                    <div class="card-body">

                        <form action="{{url('admin/payment-request-view')}}" method="get">
                            <div class="row">
                                <div class="col-lg-3 col-md-8 form-group mg-b-0">
                                    <label class="form-label">From: <span class="tx-danger">*</span></label>
                                    <input class="form-control fc-datepicker" value="{{ $fromdate }}" type="text" id="fromdate" name="fromdate" autocomplete="off">
                                </div>

                                <div class="col-lg-3 col-md-8 form-group mg-b-0">
                                    <label class="form-label">To: <span class="tx-danger">*</span></label>
                                    <input class="form-control fc-datepicker" value="{{ $todate }}" type="text" id="todate"  name="todate" autocomplete="off">
                                </div>

                                <div class="col-lg-3 col-md-8 form-group mg-b-0">
                                    <label class="form-label">Status: <span class="tx-danger">*</span></label>
                                    <select class="form-control select2" id="status_id" name="status_id">
                                        <option value="1" @if($status_id == 1) selected @endif>Success</option>
                                        <option value="2" @if($status_id == 2) selected @endif>Failure</option>
                                        <option value="3" @if($status_id == 3) selected @endif>Pending</option>
                                    </select>
                                </div>

                                <div class="col-lg-3 col-md-4 mg-t-10 mg-sm-t-25">
                                    <button class="btn btn-main-primary pd-x-20" type="submit"><i class="fas fa-search"></i> Search</button>
                                    <button class="btn btn-danger pd-x-20" type="button"  data-toggle="modal" data-target="#transaction_download_model"><i class="fas fa-download"></i> Download</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>


        <div class="row row-sm">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">Payment Request View (Amount : {{ $total_amount }})</h4>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="@if(Auth::User()->company->table_format == 1)table text-md-nowrap @else display responsive nowrap @endif" id="my_table">
                                <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0">ID</th>
                                    <th class="wd-15p border-bottom-0">User</th>
                                    <th class="wd-15p border-bottom-0">Request Date</th>
                                    <th class="wd-15p border-bottom-0">Payment Date</th>
                                    <th class="wd-15p border-bottom-0">Bank</th>
                                    <th class="wd-15p border-bottom-0">Method</th>
                                    <th class="wd-15p border-bottom-0">Amount</th>
                                    <th class="wd-15p border-bottom-0">UTR</th>
                                    <th class="wd-15p border-bottom-0">Status</th>
                                    <th class="wd-15p border-bottom-0">Payment Type</th>
                                    <th class="wd-15p border-bottom-0">Action</th>
                                    <th class="wd-15p border-bottom-0">Edit</th>
                                </tr>
                                </thead>
                            </table>

                            <script type="text/javascript">
                                $(document).ready(function(){
                                    $('#my_table').DataTable({
                                        "order": [[ 1, "desc" ]],
                                        processing: true,
                                        serverSide: true,
                                        ajax: "{{ $urls }}",
                                        columns: [
                                            { data: 'id' },
                                            { data: 'user_id' },
                                            { data: 'created_at' },
                                            { data: 'payment_date' },
                                            { data: 'bank_name' },
                                            { data: 'payment_method' },
                                            { data: 'amount' },
                                            { data: 'bankref' },
                                            { data: 'status' },
                                            { data: 'payment_type' },
                                            { data: 'action' },
                                            { data: 'edit' },
                                        ]
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




    <div class="modal  show" id="view_request_model"data-toggle="modal">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Update Payment</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">

                        <input type="hidden" id="id">
                        <input type="hidden" id="trasnfer_millisecond">

                        <div class="row">

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">User Name</label>
                                    <input type="text" id="user_id" class="form-control" placeholder="User Name" disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Payment Date</label>
                                    <input type="text" id="payment_date" class="form-control" placeholder="Payment Date" disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Method</label>
                                    <input type="text" id="paymentmethod_id" class="form-control" placeholder="Payment Method" disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Bank</label>
                                    <input type="text" id="bankdetail_id" class="form-control" placeholder="Bank" disabled>
                                </div>
                            </div>


                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Amount</label>
                                    <input type="text" id="amount" class="form-control" placeholder="Amount" disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Bank Ref</label>
                                    <input type="text" id="bankref" class="form-control" placeholder="Bank Ref" disabled>
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

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Status</label>
                                    <select class="form-control" id="status_ids">
                                        <option value="1">Approve</option>
                                        <option value="2">Reject</option>
                                        <option value="3">Pending</option>
                                    </select>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="status_ids_errors"></li>
                                    </ul>
                                </div>
                            </div>



                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn ripple btn-primary" type="button" id="transfer_btn" onclick="update_balance()">Update Now</button>
                    <button class="btn btn-primary" type="button"  id="transfer_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                    <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
    </div>



    <div class="modal  show" id="transaction_download_model"data-toggle="modal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Download Data</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">

                        <div class="row">

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Menu Name</label>
                                    <input type="text" id="download_menu_name" class="form-control" value="{{ $page_title }}" readonly>

                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Your Login Password</label>
                                    <input type="password" id="download_password" class="form-control" placeholder="Login Password">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="download_password_errors"></li>
                                    </ul>

                                </div>
                            </div>


                        </div>

                    </div>

                    <div class="alert alert-outline-danger" role="alert" id="download-label" style="display: none;">
                        <strong> Download File :  <a href="" target="_blank" id="download_link">Click Here</a> </strong>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn ripple btn-primary" type="button" id="download_btn" onclick="download_report()">Verify And Download</button>
                    <button class="btn btn-primary" type="button"  id="download_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                    <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
    </div>



    <div class="modal  show" id="view_edit_request_model"data-toggle="modal">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Edit Request</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">

                        <input type="hidden" id="edit_id">
                        <div class="row">

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Bank</label>
                                    <select class="form-control" id="edit_bankdetail_id">
                                        @foreach($bankdetails as $value)
                                            <option value="{{ $value->id }}">{{ $value->bank_name }}</option>
                                        @endforeach
                                    </select>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="edit_bankdetail_id_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Payment Method</label>
                                    <select class="form-control" id="edit_paymentmethod_id">
                                        @foreach($methods as $value)
                                            <option value="{{ $value->id }}">{{ $value->payment_type }}</option>
                                        @endforeach
                                    </select>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="edit_paymentmethod_id_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Bank Ref Number</label>
                                    <input type="text" class="form-control" placeholder="Bank Ref Number" id="edit_bankref">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="edit_bankref_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Login Password</label>
                                    <input type="password" class="form-control" placeholder="Login Password" id="edit_password">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="edit_password_errors"></li>
                                    </ul>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn ripple btn-primary" type="button" id="edit_request_btn" onclick="edit_request_now()">Edit Now</button>
                    <button class="btn btn-primary" type="button"  id="edit_request_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                    <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection