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
                                    <form action="{{url('admin/recharge-report')}}" method="get">
                                        <div class="row">
                                            <div class="col-lg-4 col-md-8 form-group mg-b-0">
                                                <label class="form-label">From: <span class="tx-danger">*</span></label>
                                                <input class="form-control fc-datepicker" value="{{ $fromdate }}" type="text" id="fromdate" name="fromdate" autocomplete="off">
                                            </div>

                                            <div class="col-lg-4 col-md-8 form-group mg-b-0">
                                                <label class="form-label">To: <span class="tx-danger">*</span></label>
                                                <input class="form-control fc-datepicker" value="{{ $todate }}" type="text" id="todate"  name="todate" autocomplete="off">
                                            </div>

                                            <div class="col-lg-4 col-md-8 form-group mg-b-0">
                                                <label class="form-label">Service: <span class="tx-danger">*</span></label>
                                                <select class="form-control select2" id="other_id" name="service_id"  style="width: 100%;">
                                                    <option value="0" @if($service_id == 0) selected @endif>All Service</option>
                                                    @foreach($services as $value)
                                                        <option value="{{ $value->id }}" @if($service_id == $value->id) selected @endif>{{ $value->service_name }}</option>
                                                    @endforeach();
                                                </select>
                                            </div>

                                            <div class="col-lg-6 col-md-4 mg-t-10 mg-sm-t-25">
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
                                            <th class="wd-15p border-bottom-0">ID</th>
                                            <th class="wd-15p border-bottom-0">Date Time</th>
                                            <th class="wd-15p border-bottom-0">User</th>
                                            <th class="wd-15p border-bottom-0">Provider</th>
                                            <th class="wd-15p border-bottom-0">Number</th>
                                            <th class="wd-15p border-bottom-0">Txnid</th>
                                            <th class="wd-15p border-bottom-0">Amount</th>
                                            <th class="wd-15p border-bottom-0">Profit</th>
                                            <th class="wd-15p border-bottom-0">Balance</th>
                                            <th class="wd-15p border-bottom-0">Status</th>
                                            <th class="wd-15p border-bottom-0">State</th>
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
                                                    { data: 'state_name' },
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