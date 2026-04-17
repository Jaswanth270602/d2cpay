<script type="text/javascript">
    function dth_customer_info() {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var provider_id = $("#provider_id").val();
        var number = $("#mobile_number").val();
        var dataString = 'provider_id=' + provider_id + '&number=' + number +   '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/dth-customer-info')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    $(".tel").text(msg.tel);
                    $(".operator").text(msg.operator);
                    $(".MonthlyRecharge").text(msg.MonthlyRecharge);
                    $(".Balance").text(msg.Balance);
                    $(".customerName").text(msg.customerName);
                    $(".NextRechargeDate").text(msg.NextRechargeDate);
                    $(".planname").text(msg.planname);
                    $("#dth_customer_info_model").modal('show');
                } else if(msg.status == 'validation_error'){
                    $("#mobile_number_errors").text(msg.errors.number);
                }
            }
        });
    }


    function dth_refresh() {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var provider_id = $("#provider_id").val();
        var number = $("#mobile_number").val();
        var dataString = 'provider_id=' + provider_id + '&number=' + number +   '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/dth-refresh')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    swal("Success", msg.message, "success");
                } else if(msg.status == 'validation_error'){
                    $("#mobile_number_errors").text(msg.errors.number);
                    $("provider_id_errors").text(msg.errors.provider_id);
                }else{
                    swal("Failed", msg.message, "error");
                }
            }
        });
    }

    function dth_roffer() {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var provider_id = $("#provider_id").val();
        var number = $("#mobile_number").val();
        var dataString = 'provider_id=' + provider_id + '&number=' + number +   '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/dth-roffer')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    var html = "";
                    for (var key in msg.plans) {
                        html += "<tr>";
                        html += "<td>" + msg.plans[key].rs + "</td>";
                        html += "<td>" + msg.plans[key].desc + "</td>";
                        html += "<td><span class='btn btn-primary btn-sm' style='width:80px;' onclick='pickamt(" + msg.plans[key].rs + ")'>Rs. " + msg.plans[key].rs + " </span></td>";
                        html += "</tr>";
                    }
                    $('#dth_roffer_plans').html(html);
                    $("#dth_roffer_plans_model").modal('show');

                } else if(msg.status == 'validation_error'){
                    $("#mobile_number_errors").text(msg.errors.number);
                    $("provider_id_errors").text(msg.errors.provider_id);
                }else{
                    swal("Failed", msg.message, "error");
                }
            }
        });
    }

    function getDthPlans(plantype_id) {
        var provider_id = $("#provider_id").val();
        if (plantype_id == 19) {
            getMonthlyPlan(provider_id, plantype_id);
        }else if (plantype_id == 20){
            getThreeMonthPlan(provider_id, plantype_id);
        }else if (plantype_id == 21){
            getSixMonthPlan(provider_id, plantype_id);
        }
    }

    function getMonthlyPlan (provider_id, plantype_id){
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var dataString = 'provider_id=' + provider_id +  '&plantype_id=' + plantype_id + '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/plan/v1/dth-plan')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    var provider_id = msg.provider_id;
                    hideProviderTab(provider_id);
                    var monthly_plan = "";
                    for (var key in msg.plans) {
                        monthly_plan += "<tr>";
                        monthly_plan += "<td>" + msg.plans[key].talktime + "</td>";
                        monthly_plan += "<td>" + msg.plans[key].validity + "</td>";
                        monthly_plan += "<td>" + msg.plans[key].desc + "</td>";
                        monthly_plan += "<td><span class='btn btn-primary btn-sm' style='width:80px;' onclick='pickamt(" + msg.plans[key].rs + ")'>Rs. " + msg.plans[key].rs + " </span></td>";
                        monthly_plan += "</tr>";
                    }
                    $('#monthlyHtml').html(monthly_plan);
                    $("#dth_plan_model").modal('show');

                } else {
                    swal("Failed", msg.message, "error");
                }
            }
        });
    }


    function getThreeMonthPlan (provider_id, plantype_id){
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var dataString = 'provider_id=' + provider_id +  '&plantype_id=' + plantype_id + '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/plan/v1/dth-plan')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    var provider_id = msg.provider_id;
                    hideProviderTab(provider_id);
                    var three_month = "";
                    for (var key in msg.plans) {
                        three_month += "<tr>";
                        three_month += "<td>" + msg.plans[key].talktime + "</td>";
                        three_month += "<td>" + msg.plans[key].validity + "</td>";
                        three_month += "<td>" + msg.plans[key].desc + "</td>";
                        three_month += "<td><span class='btn btn-primary btn-sm' style='width:80px;' onclick='pickamt(" + msg.plans[key].rs + ")'>Rs. " + msg.plans[key].rs + " </span></td>";
                        three_month += "</tr>";
                    }
                    $('#threeMonthHtml').html(three_month);
                } else {
                    swal("Failed", msg.message, "error");
                }
            }
        });
    }

    function getSixMonthPlan (provider_id, plantype_id){
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var dataString = 'provider_id=' + provider_id +  '&plantype_id=' + plantype_id + '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/plan/v1/dth-plan')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    var provider_id = msg.provider_id;
                    hideProviderTab(provider_id);
                    var six_month = "";
                    for (var key in msg.plans) {
                        six_month += "<tr>";
                        six_month += "<td>" + msg.plans[key].talktime + "</td>";
                        six_month += "<td>" + msg.plans[key].validity + "</td>";
                        six_month += "<td>" + msg.plans[key].desc + "</td>";
                        six_month += "<td><span class='btn btn-primary btn-sm' style='width:80px;' onclick='pickamt(" + msg.plans[key].rs + ")'>Rs. " + msg.plans[key].rs + " </span></td>";
                        six_month += "</tr>";
                    }
                    $('#sixMonthHtml').html(six_month);
                } else {
                    swal("Failed", msg.message, "error");
                }
            }
        });
    }



    function hideProviderTab (provider_id){

    }


    function pickamt(id) {
        $("#dth_roffer_plans_model").modal('hide');
        $("#dth_plan_model").modal('hide');
        $("#amount").val(id);
    }
</script>

<div class="modal  show" id="dth_customer_info_model"data-toggle="modal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">DTH Customer Info</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <table class="table table-bordered mb-0">
                        <thead>
                         <tr>
                             <th>Provider Name</th>
                             <td><span class="float-right ml-auto operator"></span></td>
                         </tr>

                         <tr>
                             <th>DTH Number</th>
                             <td><span class="float-right ml-auto tel"></span></td>
                         </tr>

                         <tr>
                             <th>Customer Name</th>
                             <td><span class="float-right ml-auto customerName"></span></td>
                         </tr>

                         <tr>
                             <th>Monthly Recharge</th>
                             <td><span class="float-right ml-auto MonthlyRecharge"></span></td>
                         </tr>

                         <tr>
                             <th>Balance</th>
                             <td><span class="float-right ml-auto Balance"></span></td>
                         </tr>

                         <tr>
                             <th>Next Recharge Date</th>
                             <td><span class="float-right ml-auto NextRechargeDate"></span></td>
                         </tr>

                         <tr>
                             <th>Plan Name</th>
                             <td><span class="float-right ml-auto planname"></span></td>
                         </tr>
                        </thead>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>



{{--DTH plan Model--}}
<div class="modal  show" id="dth_plan_model" data-toggle="modal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Browse Plans</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                            aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">

                {{--Start Tab--}}
                <div class="m-4">
                    <ul class="nav nav-pills" id="myTab">
                        <li class="nav-item">
                            <a href="#monthly_plan" class="nav-link active" onclick="javascript:return getDthPlans(19);">Monthly</a>
                        </li>

                       {{-- <li  class="nav-item three_month" style="display: none;">
                            <a href="#three_month" class="nav-link" onclick="javascript:return getDthPlans(20);">3 Month</a>
                        </li>

                        <li class="nav-item special_offer" style="display: none;">
                            <a href="#special_offer" class="nav-link" onclick="javascript:return getDthPlans(21);">6 Month</a>
                        </li>

                        <li class="nav-item data_plan" style="display: none;">
                            <a href="#data_plan" class="nav-link" onclick="javascript:return getDthPlans(22);">Annual</a>
                        </li>
--}}
                    </ul>
                    <hr>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="monthly_plan">
                            <table class="table table-bordered mb-0">
                                <thead>
                                <tr>
                                    <th>Talktime</th>
                                    <th>Validity</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                </tr>
                                </thead>
                                <tbody id="monthlyHtml"></tbody>
                            </table>
                        </div>

                        <div class="tab-pane fade" id="three_month">
                            <table class="table table-bordered mb-0">
                                <thead>
                                <tr>
                                    <th>Talktime</th>
                                    <th>Validity</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                </tr>
                                </thead>
                                <tbody id="threeMonthHtml"></tbody>
                            </table>
                        </div>

                        <div class="tab-pane fade" id="special_offer">
                            <table class="table table-bordered mb-0">
                                <thead>
                                <tr>
                                    <th>Talktime</th>
                                    <th>Validity</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                </tr>
                                </thead>
                                <tbody id="specialOfferHtml"></tbody>
                            </table>
                        </div>

                        <div class="tab-pane fade" id="data_plan">
                            <table class="table table-bordered mb-0">
                                <thead>
                                <tr>
                                    <th>Talktime</th>
                                    <th>Validity</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                </tr>
                                </thead>
                                <tbody id="dataPlanHtml"></tbody>
                            </table>
                        </div>




                    </div>
                </div>
                {{--End Tab--}}


            </div>
        </div>
    </div>
</div>

{{--DTH plan Model End--}}

<div class="modal  show" id="dth_roffer_plans_model"data-toggle="modal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">DTH Roffer</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="d-md-flex">
                    <div class="tabs-style-12" style="margin-left: 2%; width: 100%;">
                        <div class="panel-body tabs-menu-body">
                            <div class="tab-content">
                                <div class="tab-pane active" id="roffer">
                                    <table class="table table-bordered mb-0">
                                        <thead>
                                        <tr>
                                            <th>Rs</th>
                                            <th>Description</th>
                                            <th>Action</th>
                                        </tr>
                                        </thead>
                                        <tbody id="dth_roffer_plans"></tbody>
                                    </table>
                                </div><!-- tab-pane -->
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
</div>