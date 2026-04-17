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
                                    <form action="{{url('admin/pancard-report')}}" method="get">
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
                        </div>
                        <hr>
                        <div class="widget-content">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id='my_table' class="display responsive nowrap" data-order='[[ 0, "desc" ]]'>
                                        <thead>
                                        <tr>
                                            <th class="wd-15p border-bottom-0">Id</th>
                                            <th class="wd-15p border-bottom-0">Date Time</th>
                                            <th class="wd-15p border-bottom-0">User</th>
                                            <th class="wd-15p border-bottom-0">Provider</th>
                                            <th class="wd-15p border-bottom-0">Number</th>
                                            <th class="wd-15p border-bottom-0">Txnid</th>
                                            <th class="wd-15p border-bottom-0">Amount</th>
                                            <th class="wd-15p border-bottom-0">Profit</th>
                                            <th class="wd-15p border-bottom-0">Balance</th>
                                            <th class="wd-15p border-bottom-0">Status</th>
                                            <th class="wd-15p border-bottom-0">Action</th>
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
                                                    { data: 'user' },
                                                    { data: 'provider' },
                                                    { data: 'number' },
                                                    { data: 'txnid' },
                                                    { data: 'amount' },
                                                    { data: 'profit' },
                                                    { data: 'balance' },
                                                    { data: 'status' },
                                                    { data: 'view' },
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


    @include('admin.report.transaction_refund_model')

@endsection