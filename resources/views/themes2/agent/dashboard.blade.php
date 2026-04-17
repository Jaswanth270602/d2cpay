@extends('themes2.agent.layout.header')
@section('content')

    <script type="text/javascript">
        $(document).ready(function () {
            showGraph();
            dashboard_details();
        });

        function showGraph() {
            var id = 1;
            var dataString = 'id=' + id;
            $.ajax({
                type: "GET",
                url: "{{url('agent/dashboard-chart-api')}}",
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
                url: "{{url('agent/dashboard-details-api')}}",
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
                        $("#dashboard_total_active_users").text(msg.balances.dashboard_total_active_users);
                    }

                }
            });
        }

        function collect_upi_payment() {
            $("#collectUpiBtn").hide();
            $("#collectUpiBtn_loader").show();
            var token = $("input[name=_token]").val();
            var collect_upi_id = $("#collect_upi_id").val();
            var collect_amount = $("#collect_amount").val();
            var collect_remark = $("#collect_remark").val();
            var dataString = 'collect_upi_id=' + collect_upi_id + '&collect_amount=' + collect_amount + '&collect_remark=' + collect_remark + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/collect-payment/upi-payment')}}",
                data: dataString,
                success: function (msg) {
                    $("#collectUpiBtn").show();
                    $("#collectUpiBtn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () {
                            location.reload(1);
                        }, 3000);
                    } else if (msg.status == 'validation_error') {
                        $("#collect_upi_id_errors").text(msg.errors.collect_upi_id);
                        $("#collect_amount_errors").text(msg.errors.collect_amount);
                        $("#collect_remark_errors").text(msg.errors.collect_remark);
                    } else {
                        swal("Faild", msg.message, "error");
                    }
                }
            });
        }

        function generate_qrcode() {
            $("#QrcodeBtn").hide();
            $("#QrcodeBtn_loader").show();
            var token = $("input[name=_token]").val();
            var mobile_number = $("#qrcode_mobile_number").val();
            var dataString = 'mobile_number=' + mobile_number + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/collect-payment/generate-qrcode')}}",
                data: dataString,
                success: function (msg) {
                    $("#QrcodeBtn").show();
                    $("#QrcodeBtn_loader").hide();
                    if (msg.status == 'success') {
                        $("#QrcodeGenerateLabel").hide();
                        $("#qr_value_url").attr('src', msg.qr_value_url);
                        $("#viewQrcodeLabel").show();
                    } else if (msg.status == 'validation_error') {
                        $("#qrcode_mobile_number_errors").text(msg.errors.mobile_number);
                    } else {
                        swal("Faild", msg.message, "error");
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
    @include('themes2.agent.layout.breadcrumb')
    <!-- Main Body Starts -->
        <div class="layout-px-spacing">
            <div class="row layout-top-spacing">
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
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td><span class="badge outline-badge-success"> Success </span></td>
                                        <td>
                                            <span id="dashboard_today_success">0.00</span>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td><span class="badge outline-badge-danger"> Failure </span></td>
                                        <td>
                                            <span id="dashboard_today_failure">0.00</span>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td><span class="badge outline-badge-warning"> Pending </span></td>
                                        <td>
                                            <span id="dashboard_today_pending">0.00</span>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td><span class="badge outline-badge-danger"> Refunded </span></td>
                                        <td>
                                            <span  id="dashboard_today_refunded">0.00</span>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td><span class="badge outline-badge-warning"> Debit </span></td>
                                        <td>
                                            <span id="dashboard_today_debit">0.00</span>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td><span class="badge outline-badge-warning"> Credit </span></td>
                                        <td>
                                            <span id="dashboard_today_credit">0.00</span>
                                        </td>
                                    </tr>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- TARGET VS ACTUAL ENDS-->

            </div>
        </div>
        <!-- Main Body Ends -->

@endsection