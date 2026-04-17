@extends('admin.layout.header')
@section('content')
<script>
    $(document).ready(function () {
        showGraph();
        dashboard_details();
        getServiceWiseSale();
    });

    function showGraph() {
        var id = 1;
        var dataString = 'id=' + id;
        $.ajax({
            type: "GET",
            url: "{{url('admin/dashboard-chart-api')}}",
            data: dataString,
            success: function (msg) {
                var provider_name = [];
                var amount = [];
                for (var i in msg.provider) {
                    provider_name.push(msg.provider[i].provider_name);
                    amount.push(msg.provider[i].amount);
                }
                var chartdata = {
                    labels: provider_name,
                    datasets: [
                        {
                            label: 'Provider Wise Chart',
                            backgroundColor: '#49e2ff',
                            borderColor: '#46d5f1',
                            hoverBackgroundColor: '#CCCCCC',
                            hoverBorderColor: '#666666',
                            data: amount
                        }
                    ]
                };
                var graphTarget = $("#graphCanvas");
                var barGraph = new Chart(graphTarget, {
                    type: 'bar',
                    data: chartdata
                });
            }
        });
    }

    function dashboard_details() {
        var id = 1;
        var dataString = 'id=' + id;
        $.ajax({
            type: "GET",
            url: "{{url('admin/dashboard-details-api')}}",
            data: dataString,
            success: function (msg) {
                if (msg.status == 'success') {
                    $("#dashboard_today_success").text(msg.sales_overview.today_success);
                    $("#dashboard_today_failure").text(msg.sales_overview.today_failure);
                    $("#dashboard_today_pending").text(msg.sales_overview.today_pending);
                    $("#dashboard_today_refunded").text(msg.sales_overview.today_refunded);
                    $("#dashboard_today_credit").text(msg.sales_overview.today_credit);
                    $("#dashboard_today_debit").text(msg.sales_overview.today_debit);

                    $("#normal_distributed_balance").text(msg.balances.normal_distributed_balance);
                    $("#aeps_distributed_balance").text(msg.balances.aeps_distributed_balance);
                    $("#my_balances").text(msg.balances.my_balances);
                    $("#dashboard_total_members").text(msg.balances.dashboard_total_members);
                    $("#dashboard_total_suspended_users").text(msg.balances.dashboard_total_suspended_users);

                    $("#success_percentage").text(msg.percentage.success_percentage);
                    $("#failure_percentage").text(msg.percentage.failure_percentage);
                    $("#pending_percentage").text(msg.percentage.pending_percentage);
                }
            }
        });
    }


    function getServiceWiseSale() {
        var token = $("input[name=_token]").val();
        var dataString = '_token=' + token;
        $.ajax({
            type: "GET",
            url: "{{url('admin/get-service-wise-sales')}}",
            data: dataString,
            success: function (msg) {
                $('#get-service-wise-sale').append(msg);
            }
        });
    }
</script>


<!-- main-content-body -->
<div class="main-content-body">


    {{--Dashboard popup start--}}
         @include('common.dashboard_popup')
    {{--Dashboard popup End--}}

    <div class="row row-sm">

        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="card-order">
                        <h6 class="mb-2">Distributed Balance</h6>
                        <h2 class="text-right ">
                            <i class="fas fa-wallet icon-size float-left text-primary text-primary-shadow"></i><span
                                    id="normal_distributed_balance">0</span></h2>
                        <p class="mb-0">Normal Balance<span class="float-right">{{--50%--}}</span></p>
                    </div>
                </div>
            </div>
        </div>


        @if(Auth::User()->company->aeps == 1)
        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="card-order">
                        <h6 class="mb-2">Distributed Balance</h6>
                        <h2 class="text-right ">
                            <i class="fas fa-wallet icon-size float-left text-primary text-primary-shadow"></i><span
                                    id="aeps_distributed_balance">0</span></h2>
                        <p class="mb-0">Aeps Balance<span class="float-right">{{--50%--}}</span></p>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="card-order">
                        <h6 class="mb-2">My Balance</h6>
                        <h2 class="text-right ">
                            <i class="fas fa-wallet icon-size float-left text-primary text-primary-shadow"></i><span
                                    id="my_balances">0</span></h2>
                        <p class="mb-0">Balance<span class="float-right">{{--50%--}}</span></p>
                    </div>
                </div>
            </div>
        </div>
        @endif


        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="card-order">
                        <h6 class="mb-2">Members</h6>
                        <h2 class="text-right ">
                            <i class="fas fa-users icon-size float-left text-primary text-primary-shadow"></i><span
                                    id="dashboard_total_members">0</span></h2>
                        <p class="mb-0">Total Members
                            <span class="float-right"><a href="{{url('admin/all-user-list')}}">Click Here</a> </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="card-order">
                        <h6 class="mb-2">Members</h6>
                        <h2 class="text-right ">
                            <i class="fas fa-user-times icon-size float-left text-primary text-primary-shadow"></i><span
                                    id="dashboard_total_suspended_users">0</span></h2>
                        <p class="mb-0">Suspended Members
                            <span class="float-right"><a href="{{url('admin/suspended-users')}}">Click Here</a> </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>


    </div>

    <!-- row -->
    <div class="row row-sm ">
        <div class="col-xl-8 col-lg-12 col-md-12 col-sm-12">
            <div class="card overflow-hidden">
                <div class="card-header bg-transparent pd-b-0 pd-t-20 bd-b-0">
                    <div class="d-flex justify-content-between">
                        <h4 class="card-title mg-b-10">Today Sales</h4>
                        <i class="mdi mdi-dots-horizontal text-gray"></i>
                    </div>
                </div>
                <div class="card-body pd-y-7">
                    <!--<canvas id="graphCanvas"></canvas>-->
                    <div class="row row-sm">
                       <div id="get-service-wise-sale"></div>
                    </div>

                </div>
            </div>

            @if(Auth::User()->role_id == 1)
            <div class="row row-sm">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header pb-0">
                            <div class="d-flex justify-content-between">
                                <h4 class="card-title mg-b-2 mt-2">This month top 10 sellers</h4>
                                <i class="mdi mdi-dots-horizontal text-gray"></i>
                            </div>
                            <hr>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table text-md-nowrap" id="my_table">
                                    <thead>
                                    <tr>
                                        <th class="wd-15p border-bottom-0">Sr No</th>
                                        <th class="wd-15p border-bottom-0">User</th>
                                        <th class="wd-15p border-bottom-0">Total Sale</th>
                                        <th class="wd-15p border-bottom-0">Total Profit</th>
                                    </tr>
                                    </thead>
                                </table>

                                <script type="text/javascript">
                                    $(document).ready(function () {

                                        // DataTable
                                        var todate = $("#todate").val();
                                        $('#my_table').DataTable({
                                            "order": [[1, "desc"]],
                                            processing: true,
                                            serverSide: true,
                                            ajax: "{{ $urls }}",
                                            columns: [
                                                {data: 'sr_no'},
                                                {data: 'username'},
                                                {data: 'total_amount'},
                                                {data: 'total_profit'},

                                            ]
                                        });

                                    });
                                </script>
                            </div>
                        </div>
                    </div>
                </div>
                <!--/div-->
            </div>
            @endif
        </div>

        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-4">
            <div class="card">
                <div class="card-header pb-0 pt-4">
                    <div class="d-flex justify-content-between">
                        <h4 class="card-title mg-b-10">Last 5 login records</h4>
                        <i class="mdi mdi-dots-horizontal text-gray"></i>
                    </div>
                </div>
                <div class="card-body p-0 m-scroll mh-350 mt-2">
                    <div class="list-group projects-list">

                        @foreach(App\Models\Loginlog::where('user_id', Auth::id())->orderBy('id', 'DESC')->paginate(5) as $value)
                        <a href="{{ url('admin/activity-logs') }}" class="list-group-item list-group-item-action flex-column align-items-start border-top-0">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1 font-weight-semibold ">{{ $value->get_device }} - {{ $value->get_browsers }} - {{ $value->get_os }}</h6>
                                <small class="text-danger">{{ \Carbon\Carbon::parse($value->created_at)->diffForHumans() }}</small>
                            </div>
                            <p class="mb-0 text-muted mb-0 tx-12">IP Address: {{ $value->ip_address }}</p>
                            <small class="text-muted">Latitude: {{ $value->latitude }}, Longitude: {{ $value->longitude }}</small>
                        </a>
                        @endforeach

                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header pb-0 pt-4">
                    <div class="d-flex justify-content-between">
                        <h4 class="card-title mg-b-10">Today Overview</h4>
                        <i class="mdi mdi-dots-horizontal text-gray"></i>
                    </div>
                </div>
                <div class="card-body p-0 m-scroll mh-350 mt-2">
                    <div class="list-group projects-list">

                        <a href="#" class="list-group-item list-group-item-action flex-column align-items-start border-top-0">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1 font-weight-semibold ">Success</h6>
                                <small class="text-success" id="dashboard_today_success"></small>
                            </div>
                        </a>

                        <a href="#" class="list-group-item list-group-item-action flex-column align-items-start border-top-0">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1 font-weight-semibold ">Failure</h6>
                                <small class="text-danger" id="dashboard_today_failure"></small>
                            </div>
                        </a>

                        <a href="#" class="list-group-item list-group-item-action flex-column align-items-start border-top-0">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1 font-weight-semibold ">Pending</h6>
                                <small class="text-warning" id="dashboard_today_pending"></small>
                            </div>
                        </a>

                        <a href="#" class="list-group-item list-group-item-action flex-column align-items-start border-top-0">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1 font-weight-semibold ">Refunded</h6>
                                <small class="text-danger" id="dashboard_today_refunded"></small>
                            </div>
                        </a>

                        <a href="#" class="list-group-item list-group-item-action flex-column align-items-start border-top-0">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1 font-weight-semibold ">Debit</h6>
                                <small class="text-warning" id="dashboard_today_debit"></small>
                            </div>
                        </a>

                        <a href="#" class="list-group-item list-group-item-action flex-column align-items-start border-top-0">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1 font-weight-semibold ">Credit</h6>
                                <small class="text-warning" id="dashboard_today_credit"></small>
                            </div>
                        </a>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /row -->



    <!-- row -->


</div>
<!-- /row -->
</div>
<!-- /container -->
</div>
<!-- /main-content -->


@endsection