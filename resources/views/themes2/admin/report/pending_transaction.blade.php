@extends('themes2.admin.layout.header')
@section('content')
    <script type="text/javascript">
        $(document).ready(function () {
            $("#other_id").select2();
            $("#fromdate").flatpickr({
                enableTime: false,
                dateFormat: "Y-m-d",
            });

            $("#todate").flatpickr({
                enableTime: false,
                dateFormat: "Y-m-d",
            });

        });

        function update_transaction() {
            $("#update_transaction_btn").hide();
            $("#update_transaction_btn_loader").show();
            var token = $("input[name=_token]").val();
            var all_report_id = document.querySelectorAll('input[name="report_id[]"]:checked');
            var report_id = [];
            for (var x = 0, l = all_report_id.length; x < l; x++) {
                report_id.push(all_report_id[x].value);
            }
            var remark = $("#update_remark").val();
            var status_id = $("#update_status_id").val();
            var dataString = 'report_id=' + report_id + '&remark=' + remark + '&status_id=' + status_id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/update-selected-transaction')}}",
                data: dataString,
                success: function (msg) {
                    $("#update_transaction_btn").show();
                    $("#update_transaction_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () {
                            location.reload(1);
                        }, 3000);
                    } else if (msg.status == 'validation_error') {
                        $("#update_remark_errors").text(msg.errors.remark);
                        $("#update_report_id_errors").text(msg.errors.report_id);
                        $("#update_status_id_errors").text(msg.errors.status_id);
                    } else {
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
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
                    <div class="widget dashboard-table">
                        <div class="widget-content">
                            <div class="card-body">
                                <div class="widget-content widget-content-area">
                                    <form action="{{url('admin/pending-transaction')}}" method="get">
                                        <div class="row">
                                            <div class="col-lg-4 col-md-8 form-group mg-b-0">
                                                <label class="form-label">From: <span class="tx-danger">*</span></label>
                                                <input class="form-control fc-datepicker" value="{{ $fromdate }}" type="text" id="fromdate" name="fromdate" autocomplete="off">
                                            </div>

                                            <div class="col-lg-4 col-md-8 form-group mg-b-0">
                                                <label class="form-label">To: <span class="tx-danger">*</span></label>
                                                <input class="form-control fc-datepicker" value="{{ $todate }}" type="text" id="todate"  name="todate" autocomplete="off">
                                            </div>


                                            <div class="col-lg-4 col-md-4 mg-t-10 mg-sm-t-25" style="margin-top: 3%;">
                                                <button class="btn btn-main-primary pd-x-20" type="submit"><i class="fas fa-search"></i> Search</button>
                                                <button class="btn btn-danger pd-x-20" type="button"  data-toggle="modal" data-target="#transaction_download_model"><i class="fas fa-download"></i> Download</button>
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
                            @if(Auth::User()->role_id == 1)
                                <button class="btn btn-danger btn-sm" data-target="#update_selected_transaction_model" data-toggle="modal">Update Transaction</button>
                            @endif
                        </div>
                        <hr>
                        <div class="widget-content">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="@if(Auth::User()->company->table_format == 1)table text-md-nowrap @else display responsive nowrap @endif" id="my_table">
                                        <thead>
                                        <tr>
                                            <th class="wd-15p border-bottom-0">Select</th>
                                            <th class="wd-15p border-bottom-0">ID</th>
                                            <th class="wd-15p border-bottom-0">Date</th>
                                            <th class="wd-15p border-bottom-0">User Name</th>
                                            <th class="wd-15p border-bottom-0">Provider Name</th>
                                            <th class="wd-15p border-bottom-0">Number</th>
                                            <th class="wd-15p border-bottom-0">Amount</th>
                                            <th class="wd-15p border-bottom-0">Status</th>
                                            <th class="wd-15p border-bottom-0">Vendor</th>
                                            <th class="wd-15p border-bottom-0">Action</th>

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
                                                    { data: 'select' },
                                                    { data: 'id' },
                                                    { data: 'created_at' },
                                                    { data: 'user' },
                                                    { data: 'provider' },
                                                    { data: 'number' },
                                                    { data: 'amount' },
                                                    { data: 'status' },
                                                    { data: 'vendor_name' },
                                                    { data: 'view' },
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
                </div>

            </div>
        </div>
        <!-- Main Body Ends -->
        <div class="modal  show" id="update_selected_transaction_model" data-toggle="modal">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content modal-content-demo">
                    <div class="modal-header">
                        <h6 class="modal-title">Update Select Transaction</h6>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                                    aria-hidden="true">×</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-body">
                            <div class="row">

                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="name">Remark</label>
                                        <input type="text" id="update_remark" class="form-control" placeholder="Remark">
                                        <span class="invalid-feedback d-block" id="update_remark_errors"></span>
                                    </div>
                                </div>

                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="name">Select Status</label>
                                        <select class="form-control" id="update_status_id">
                                            <option value="3">Pending</option>
                                            <option value="2">Failure</option>
                                            <option value="1">Success</option>
                                        </select>
                                        <span class="invalid-feedback d-block" id="update_status_id_errors"></span>

                                    </div>
                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="modal-footer">
                        <button class="btn ripple btn-primary" type="button" id="update_transaction_btn"
                                onclick="update_transaction()">Update Now
                        </button>
                        <button class="btn btn-primary" type="button" id="update_transaction_btn_loader" disabled
                                style="display: none;"><span class="spinner-border spinner-border-sm" role="status"
                                                             aria-hidden="true"></span> Loading...
                        </button>
                        <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                    </div>
                </div>
            </div>
        </div>


    @include('admin.report.transaction_refund_model')

@endsection