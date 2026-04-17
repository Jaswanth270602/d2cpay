@extends('themes2.admin.layout.header')
@section('content')

    <script>
        $(document).ready(function () {
            showGraph();
            dashboard_details();
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
    </script>



    <!--  Content Area Starts  -->
    <div id="content" class="main-content">

        {{--Dashboard popup start--}}
         @include('common.dashboard_popup')
        {{--Dashboard popup End--}}

        <!-- Main Body Starts -->
        @include('themes2.admin.layout.breadcrumb')
        <!-- Main Body Starts -->
        <div class="layout-px-spacing">
            <div class="row layout-top-spacing">
                <!-- 4 AREAS -->
                <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12 col-12 layout-spacing">
                    <div class="widget 4-areas">
                        <div class="f-100">
                            <div class="card-box">
                                <h6 class="mt-0 font-16"> Distributed Balance</h6>
                                <h2 class="text-primary my-3 text-center">
                                    <span id="normal_distributed_balance">0</span>
                                </h2>
                                <p class="text-muted mb-0"> Normal Balance</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12 col-12 layout-spacing">
                    <div class="widget 4-areas">
                        <div class="f-100">
                            <div class="card-box">
                                <h6 class="mt-0 font-16"> Distributed Balance</h6>
                                <h2 class="text-primary my-3 text-center">
                                    <span id="aeps_distributed_balance">0</span>
                                </h2>
                                <p class="text-muted mb-0"> Aeps Balance</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12 col-12 layout-spacing">
                    <div class="widget 4-areas">
                        <div class="f-100">
                            <div class="card-box">
                                <h6 class="mt-0 font-16"> Members</h6>
                                <h2 class="text-primary my-3 text-center">
                                    <span id="dashboard_total_members">0</span>
                                </h2>
                                <p class="text-muted mb-0"> Total Members</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12 col-12 layout-spacing">
                    <div class="widget 4-areas">
                        <div class="f-100">
                            <div class="card-box">
                                <h6 class="mt-0 font-16"> Members</h6>
                                <h2 class="text-primary my-3 text-center">
                                    <span id="dashboard_total_suspended_users">0</span>
                                </h2>
                                <p class="text-muted mb-0"> Suspended Members
                                    <span class="float-right">
                                            <a href="{{url('admin/suspended-users')}}">Click Here</a>
                                        </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- 4 AREAS -->
                <!-- REVENUE -->
                <div class="col-xl-7 col-lg-7 col-md-7 col-sm-12 col-12 layout-spacing">
                    <div class="widget widget-chart-one">
                        <div class="widget-heading">
                            <h5 class=""> Today Sales</h5>
                        </div>
                        <hr>
                        <div class="widget-content">
                            <canvas id="graphCanvas"></canvas>
                        </div>
                    </div>
                </div>
                <!-- REVENUE ENDS-->
                <!-- TARGET VS ACTUAL -->
                <div class="col-xl-5 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
                    <div class="widget dashboard-table">
                        <div class="widget-heading">
                            <h5 class=""> Today Overview</h5>
                        </div>
                        <hr>
                        <div class="widget-content">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <th><div class="th-content"> Status</div></th>
                                        <th><div class="th-content"> Amount</div></th>
                                        <th><div class="th-content"> Percentage</div></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td><span class="badge outline-badge-success"> Success </span></td>
                                        <td>
                                            <span id="dashboard_today_success">0.00</span>
                                        </td>
                                        <td>
                                            <span id="success_percentage">Null</span>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td><span class="badge outline-badge-danger"> Failure </span></td>
                                        <td>
                                            <span id="dashboard_today_failure">0.00</span>
                                        </td>
                                        <td>
                                            <span id="failure_percentage">Null</span>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td><span class="badge outline-badge-warning"> Pending </span></td>
                                        <td>
                                            <span id="dashboard_today_pending">0.00</span>
                                        </td>
                                        <td>
                                            <span id="pending_percentage">Null</span>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td><span class="badge outline-badge-danger"> Refunded </span></td>
                                        <td>
                                            <span  id="dashboard_today_refunded">0.00</span>
                                        </td>
                                        <td>Null</td>
                                    </tr>

                                    <tr>
                                        <td><span class="badge outline-badge-warning"> Debit </span></td>
                                        <td>
                                            <span id="dashboard_today_debit">0.00</span>
                                        </td>
                                        <td>Null</td>
                                    </tr>

                                    <tr>
                                        <td><span class="badge outline-badge-warning"> Credit </span></td>
                                        <td>
                                            <span id="dashboard_today_credit"0.00></span>
                                        </td>
                                        <td>Null</td>
                                    </tr>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- TARGET VS ACTUAL ENDS-->


                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
                    <div class="widget dashboard-table">
                        <div class="widget-heading">
                            <h5 class=""> This Month Top 10 Sellers</h5>
                        </div>
                        <hr>
                        <div class="widget-content">
                            <div class="table-responsive">
                                <table id="my_table" class="table table-hover" style="width:100%">
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
            </div>
        </div>
        <!-- Main Body Ends -->

@endsection