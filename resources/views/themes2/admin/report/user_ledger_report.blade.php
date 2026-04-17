@extends('themes2.admin.layout.header')
@section('content')
    <script type="text/javascript">
       $(document).ready(function () {

           $("#fromdate").flatpickr({
               enableTime: false,
               dateFormat: "Y-m-d",
           });

           $("#todate").flatpickr({
               enableTime: false,
               dateFormat: "Y-m-d",
           });

        });

        function send_mail() {
            $("#sendMail_btn").hide();
            $("#sendMail_btn_loader").show();
            var token = $("input[name=_token]").val();
            var fromdate = $("#fromdate").val();
            var todate = $("#todate").val();
            var wallet_type = $("#wallet_type").val();
            var encrypt_id = $("#encrypt_id").val();
            var format_type = $("#format_type").val();
            var mail_password = $("#mail_password").val();
            var mailMessage = $("#mailMessage").val();
            var dataString = 'wallet_type=' + wallet_type + '&encrypt_id=' + encrypt_id + '&fromdate=' + fromdate + '&todate=' + todate + '&format_type=' + format_type + '&password=' + mail_password + '&mailMessage=' + mailMessage + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/send-mail/send-statement')}}",
                data: dataString,
                success: function (msg) {
                    $("#sendMail_btn").show();
                    $("#sendMail_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#mail_password_errors").text(msg.errors.password);
                        $("#format_type_errors").text(msg.errors.format_type);
                        $("#mailMessage_errors").text(msg.errors.mailMessage);
                    }else{
                        swal("Failed", msg.message, "error");
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
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
                    <div class="widget dashboard-table">
                        <div class="widget-content">
                            <div class="card-body">
                                <input type="hidden" id="encrypt_id" value="{{$encrypt_id}}">
                                <div class="widget-content widget-content-area">
                                    <form action="{{url('admin/user-ledger-report')}}/{{$encrypt_id}}" method="get">
                                    <div class="form-group row">
                                        <div class="col-lg-12 col-md-12 col-sm-12">
                                            <div class="form-row">
                                                <div class="col-md-4 mb-4">
                                                    <label class="form-label">From: <span class="tx-danger">*</span></label>
                                                    <input class="form-control flatpickr flatpickr-input active" value="{{ $fromdate }}" type="text" id="fromdate" name="fromdate" autocomplete="off">
                                                </div>

                                                <div class="col-md-4 mb-4">
                                                    <label class="form-label">To: <span class="tx-danger">*</span></label>
                                                    <input class="form-control fc-datepicker" value="{{ $todate }}" type="text" id="todate"  name="todate" autocomplete="off">
                                                </div>

                                                <div class="col-md-4 mb-4">
                                                    <label class="form-label">Wallet Type: <span class="tx-danger">*</span></label>
                                                    <select class="form-control" id="wallet_type" name="wallet_type">
                                                        <option value="1" @if($wallet_type == 1) selected @endif>Normal Wallet</option>
                                                        @if(Auth::User()->company->aeps == 1 && Auth::User()->profile->aeps == 1)
                                                            <option value="2" @if($wallet_type == 2) selected @endif>Aeps Wallet</option>
                                                        @endif
                                                    </select>
                                                </div>

                                            </div>

                                            <button class="btn btn-main-success pd-x-20" type="submit"><i class="fas fa-search"></i> Search</button>

                                            @if(Auth::User()->role_id == 2 && $permission_send_statement == 1)
                                                <button class="btn btn-danger pd-x-20" type="button"  data-toggle="modal" data-target="#send_mail_model"><i class="fas fa-envelope"></i> Send Mail</button>
                                            @elseif(Auth::User()->role_id == 1)
                                                <button class="btn btn-danger pd-x-20" type="button"  data-toggle="modal" data-target="#send_mail_model"><i class="fas fa-envelope"></i> Send Mail</button>
                                            @endif
                                        </div>
                                    </div>
                                    </form>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
                    <div class="widget dashboard-table">
                        <div class="widget-heading">
                            <h5 class=""> {{ $page_title }}</h5>
                        </div>
                        <hr>
                        <div class="widget-content">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id='my_table' class="display responsive nowrap" data-order='[[ 0, "desc" ]]'>
                                        <thead>
                                        <tr>
                                            <th class="wd-15p border-bottom-0">ID</th>
                                            <th class="wd-15p border-bottom-0">Date Time</th>
                                            <th class="wd-15p border-bottom-0">Txnid</th>
                                            <th class="wd-15p border-bottom-0">Description</th>
                                            <th class="wd-15p border-bottom-0">Opening Balance</th>
                                            <th class="wd-15p border-bottom-0">Debit</th>
                                            <th class="wd-15p border-bottom-0">Credit</th>
                                            <th class="wd-15p border-bottom-0">Profit</th>
                                            <th class="wd-15p border-bottom-0">Closing Balance</th>
                                            <th class="wd-15p border-bottom-0">Status</th>
                                        </tr>
                                        </thead>
                                    </table>

                                    <!-- Script -->
                                    <script type="text/javascript">
                                        $(document).ready(function(){
                                            $('#my_table').DataTable({
                                                processing: true,
                                                serverSide: true,
                                                ajax: "{{ $urls }}",
                                                columns: [
                                                    { data: 'id' },
                                                    { data: 'created_at' },
                                                    { data: 'txnid' },
                                                    { data: 'description' },
                                                    { data: 'opening_balance' },
                                                    { data: 'debit' },
                                                    { data: 'credit' },
                                                    { data: 'profit' },
                                                    { data: 'total_balance' },
                                                    { data: 'status' },
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
                </div>

            </div>
        </div>
        <!-- Main Body Ends -->


        <div class="modal  show" id="send_mail_model"data-toggle="modal">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content modal-content-demo">
                    <div class="modal-header">
                        <h6 class="modal-title">Send Mail</h6>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-body">

                            <div class="row">

                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="name">Format Type</label>
                                        <select class="form-control" id="format_type">
                                            <option value="2">Excel</option>
                                        </select>
                                        <span class="invalid-feedback d-block" id="format_type_errors"></span>

                                    </div>
                                </div>

                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="name">Your Login Password</label>
                                        <input type="password" id="mail_password" class="form-control" placeholder="Login Password">
                                        <span class="invalid-feedback d-block" id="mail_password_errors"></span>

                                    </div>
                                </div>

                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="name">Mail Content</label>
                                        <textarea type="text" id="mailMessage" class="form-control" placeholder="Mail Content" rows="6">{{ $mailMessage }}</textarea>
                                        <span class="invalid-feedback d-block" id="mailMessage_errors"></span>

                                    </div>
                                </div>


                            </div>

                        </div>

                    </div>

                    <div class="modal-footer">
                        <button class="btn ripple btn-primary" type="button" id="sendMail_btn" onclick="send_mail()">Send Now</button>
                        <button class="btn btn-primary" type="button"  id="sendMail_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                        <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                    </div>
                </div>
            </div>
        </div>

@endsection