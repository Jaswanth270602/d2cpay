@extends('themes2.admin.layout.header')
@section('content')
    <script type="text/javascript">
        $(document).ready(function () {
            $("#other_id").select2();
            $("#child_id").select2();
            $("#provider_id").select2();
            $("#apiId").select2();
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
                                    <form action="{{url('admin/all-transaction-report')}}" method="get">
                                        <div class="row">

                                            <div class="col-sm-3">
                                                <div class="form-group">
                                                    <label class="form-label">From: <span class="tx-danger">*</span></label>
                                                    <input class="form-control fc-datepicker" value="{{ $fromdate }}" type="text" id="fromdate" name="fromdate" autocomplete="off">
                                                </div>
                                            </div>

                                            <div class="col-sm-3">
                                                <div class="form-group">
                                                    <label class="form-label">To: <span class="tx-danger">*</span></label>
                                                    <input class="form-control fc-datepicker" value="{{ $todate }}" type="text" id="todate"  name="todate" autocomplete="off">
                                                </div>
                                            </div>

                                            <div class="col-sm-3">
                                                <div class="form-group">
                                                    <label class="form-label">Status:</label>
                                                    <select class="form-control select2" id="other_id" name="status_id" style="width: 100%;">
                                                        <option value="0" @if($status_id == 0) selected @endif> All Status</option>
                                                        @foreach($status as $value)
                                                            <option value="{{ $value->id }}" @if($status_id == $value->id) selected @endif> {{ $value->status }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>



                                            <div class="col-sm-3">
                                                <div class="form-group">
                                                    <label class="form-label">Select User:</label>
                                                    <select class="form-control select2" id="child_id" name="child_id" style="width: 100%;">
                                                        <option value="0" @if($child_id == 0) selected @endif> All Users</option>
                                                        @foreach($users as $value)
                                                            <option value="{{ $value->id }}" @if($child_id == $value->id) selected @endif>{{ $value->name }} {{ $value->last_name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>


                                        </div>

                                        <div class="row">

                                            <div class="col-lg-3 col-md-8 form-group mg-b-0">
                                                <label class="form-label">Select Provider:</label>
                                                <select class="form-control select2" id="provider_id" name="provider_id" style="width: 100%;">
                                                    <option value="0" @if($provider_id == 0) selected @endif> All Provider</option>
                                                    @foreach($providers as $value)
                                                        <option value="{{ $value->id }}" @if($provider_id == $value->id) selected @endif> {{ $value->provider_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            @if(Auth::User()->role_id == 1)
                                                <div class="col-lg-3 col-md-8 form-group mg-b-0">
                                                    <label class="form-label">Select Api:</label>
                                                    <select class="form-control select2" id="apiId" name="api_id" style="width: 100%;">
                                                        <option value="0" @if($api_id == 0) selected @endif> Select Api</option>
                                                        @foreach($apis as $value)
                                                            <option value="{{ $value->id }}" @if($api_id == $value->id) selected @endif> {{ $value->api_name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endif


                                            <div class="col-lg-6" style="margin-top: 3%;">
                                                <button class="btn btn-primary pd-x-20" type="submit"><i class="fas fa-search"></i> Search</button>
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
                                            <th class="wd-15p border-bottom-0">Report ID</th>
                                            <th class="wd-15p border-bottom-0">Status</th>
                                            <th class="wd-15p border-bottom-0">Date</th>
                                            <th class="wd-15p border-bottom-0">User Name</th>
                                            <th class="wd-15p border-bottom-0">Provider Name</th>
                                            <th class="wd-15p border-bottom-0">Number</th>
                                            <th class="wd-15p border-bottom-0">Opening Balance</th>
                                            <th class="wd-15p border-bottom-0">Amount</th>
                                            <th class="wd-15p border-bottom-0">Profit</th>
                                            <th class="wd-15p border-bottom-0">Closing Balance</th>
                                            <th class="wd-15p border-bottom-0">Mode</th>
                                            <th class="wd-15p border-bottom-0">State</th>
                                            <th class="wd-15p border-bottom-0">Vendor</th>
                                            <th class="wd-15p border-bottom-0">Txnid</th>
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
                                                    { data: 'status' },
                                                    { data: 'created_at' },
                                                    { data: 'user' },
                                                    { data: 'provider' },
                                                    { data: 'number' },
                                                    { data: 'opening_balance' },
                                                    { data: 'amount' },
                                                    { data: 'profit' },
                                                    { data: 'total_balance' },
                                                    { data: 'mode' },
                                                    { data: 'state' },
                                                    { data: 'vendor' },
                                                    { data: 'txnid' },
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