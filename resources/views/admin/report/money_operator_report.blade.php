@extends('admin.layout.header')
@section('content')

    <script type="text/javascript">
        $(document).ready(function () {
            $("#other_id").select2();
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

            $("#wallet_type").select2();
        });



    </script>



    <div class="main-content-body">

        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card">
                    <div class="card-body">

                        <form action="{{url('admin/money-operator-report')}}" method="get">
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
                                    <label class="form-label">User: <span class="tx-danger">*</span></label>
                                    <select class="form-control select2" id="other_id" name="child_id">
                                        <option value="0" @if($child_id == 0) selected @endif>All User</option>
                                        @foreach($users as $value)
                                            <option value="{{ $value->id }}" @if($child_id == $value->id) selected @endif>{{ $value->name }} {{ $value->last_name }} - {{ $value->role->role_title }}</option>
                                        @endforeach
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
                            <h4 class="card-title mg-b-2 mt-2">{{ $page_title }}</h4>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table text-md-nowrap" id="example1">
                                <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0">Sr No</th>
                                    <th class="wd-15p border-bottom-0">Provider</th>
                                    <th class="wd-15p border-bottom-0">Amount</th>
                                    <th class="wd-15p border-bottom-0">User Charges</th>

                                    @if(Auth::User()->role_id == 1)
                                        <th class="wd-15p border-bottom-0">D</th>
                                        <th class="wd-15p border-bottom-0">SD</th>
                                        <th class="wd-15p border-bottom-0">ST</th>
                                        <th class="wd-15p border-bottom-0">CS</th>
                                    @endif

                                    <th class="wd-15p border-bottom-0">count</th>
                                </tr>
                                </thead>
                                <tbody>

                                @php
                                    $i = 1;
                                    $total_amount = 0;
                                    $total_profit = 0;
                                    $total_dcomm = 0;
                                    $total_sdcomm = 0;
                                    $total_stcomm = 0;
                                    $total_cscomm = 0;
                                    $all_count = 0;
                                @endphp
                                @foreach($reports as $value)
                                    <tr>
                                        <td>{{ $i++ }}</td>
                                        <td>{{ $value->provider->provider_name }}</td>
                                        <td>{{ number_format($value->total_amount, 2) }}</td>
                                        <td>{{ number_format($value->total_profit, 2) }}</td>

                                        @if(Auth::User()->role_id == 1)
                                            <td>{{ number_format($value->total_dcomm, 2) }}</td>
                                            <td>{{ number_format($value->total_sdcomm, 2) }}</td>
                                            <td>{{ number_format($value->total_stcomm, 2) }}</td>
                                            <td>{{ number_format($value->total_cscomm, 2) }}</td>
                                        @endif

                                        <td>{{ $value->all_count }}</td>
                                    </tr>
                                    @php
                                        $total_amount +=  $value->total_amount;
                                        $total_profit +=  $value->total_profit;
                                        $total_dcomm +=  $value->total_dcomm;
                                        $total_sdcomm +=  $value->total_sdcomm;
                                        $total_stcomm +=  $value->total_stcomm;
                                        $total_cscomm +=  $value->total_cscomm;
                                        $all_count +=  $value->all_count;
                                    @endphp
                                @endforeach
                                <tr style="background: linear-gradient(45deg, #f33057, #3858f9); color: white;">
                                    <td style="color: #3b4863">{{ $row_count + 1 }}</td>
                                    <td>Total</td>
                                    <td>{{ number_format($total_amount, 2) }}</td>
                                    <td>{{number_format($total_profit,2)}}</td>

                                    @if(Auth::User()->role_id == 1)
                                        <td>{{number_format($total_dcomm, 2)}}</td>
                                        <td>{{ number_format($total_sdcomm, 2) }}</td>
                                        <td>{{ number_format($total_stcomm, 2) }}</td>
                                        <td>{{ number_format($total_cscomm, 2) }}</td>
                                    @endif

                                    <td>{{ $all_count }}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!--/div-->

        </div>

    </div>
    </div>
    </div>




    @include('admin.report.transaction_refund_model')
@endsection