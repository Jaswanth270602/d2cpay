<script type="text/javascript">
    function dth_customer_info() {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var provider_id = $("#provider_id").val();
        var number = $("#mobile_number").val();
        var dataString = 'provider_id=' + provider_id + '&number=' + number +   '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/plan/v1/dth-customer-info')}}",
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
                }else{
                    swal("Failed", msg.message, "error");
                }
            }
        });
    }

    function dth_plans() {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var provider_id = $("#provider_id").val();
        var number = $("#mobile_number").val();
        var dataString = 'provider_id=' + provider_id + '&number=' + number +   '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/plan/v1/dth-plans')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    var html = "";
                    for (var key in msg.plans) {
                        if (msg.plans[key].rs['1 MONTHS']){
                            var month1 = "1 MONTHS "+ msg.plans[key].rs['1 MONTHS']+ " | ";
                        }else{
                            var month1 = "";
                        }

                        if (msg.plans[key].rs['3 MONTHS']){
                            var month3 = "3 MONTHS "+ msg.plans[key].rs['3 MONTHS']+ " | ";
                        }else{
                            var month3 = "";
                        }

                        if (msg.plans[key].rs['6 MONTHS']){
                            var month6 = "6 MONTHS "+ msg.plans[key].rs['6 MONTHS']+ " | ";
                        }else{
                            var month6 = '';
                        }

                        if (msg.plans[key].rs['1 YEAR']){
                            var month12 = "1 YEAR "+ msg.plans[key].rs['1 YEAR']+ " | ";
                        }else{
                            var month12 =  '';
                        }

                        html += "<tr>";
                        html += "<td>"+ month1 +" "+ month3 +" "+ month6 +" "+ month12 +"</td>";
                        html += "<td>" + msg.plans[key].desc + "</td>";
                        html += "<td>" + msg.plans[key].plan_name + "</td>";
                        html += "</tr>";
                    }
                    $('#dthplans').html(html);
                    $("#dth_plan_model").modal('show');
                } else if(msg.status == 'validation_error'){
                    $("#mobile_number_errors").text(msg.errors.number);
                }else{
                    swal("Failed", msg.message, "error");
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
            url: "{{url('agent/plan/v1/dth-refresh')}}",
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
            url: "{{url('agent/plan/v1/dth-roffer')}}",
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
                    <div class="task-stat pb-0">
                        <div class="d-flex tasks">
                            <div class="mb-0">
                                <div class="h6 fs-15 mb-0">Provider Name: </div>
                            </div>
                            <span class="float-right ml-auto operator"></span>
                        </div>

                        <div class="d-flex tasks">
                            <div class="mb-0">
                                <div class="h6 fs-15 mb-0">DTH Number: </div>
                            </div>
                            <span class="float-right ml-auto tel"></span>
                        </div>

                        <div class="d-flex tasks">
                            <div class="mb-0">
                                <div class="h6 fs-15 mb-0">Customer Name: </div>
                            </div>
                            <span class="float-right ml-auto customerName"></span>
                        </div>

                        <div class="d-flex tasks">
                            <div class="mb-0">
                                <div class="h6 fs-15 mb-0">Monthly Recharge: </div>
                            </div>
                            <span class="float-right ml-auto MonthlyRecharge"></span>
                        </div>

                        <div class="d-flex tasks">
                            <div class="mb-0">
                                <div class="h6 fs-15 mb-0">Balance: </div>
                            </div>
                            <span class="float-right ml-auto Balance"></span>
                        </div>

                        <div class="d-flex tasks">
                            <div class="mb-0">
                                <div class="h6 fs-15 mb-0">Next Recharge Date: </div>
                            </div>
                            <span class="float-right ml-auto NextRechargeDate"></span>
                        </div>

                        <div class="d-flex tasks">
                            <div class="mb-0">
                                <div class="h6 fs-15 mb-0">Plan Name: </div>
                            </div>
                            <span class="float-right ml-auto planname"></span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>



<div class="modal  show" id="dth_plan_model" data-toggle="modal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">DTH Plans</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="d-md-flex">



                    <div class="tabs-style-12" style="margin-left: 2%; width: 100%;">
                        <div class="panel-body tabs-menu-body">
                            <div class="tab-content">
                                <div class="tab-pane active" id="roffer">
                                    <table class="table table-hover table-bordered mb-0 text-md-nowrap text-lg-nowrap text-xl-nowrap table-striped">
                                        <thead>
                                        <tr>
                                            <th>Rs</th>
                                            <th>Description</th>
                                            <th>Plan Name</th>
                                        </tr>
                                        </thead>
                                        <tbody id="dthplans"></tbody>
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
                                    <table class="table table-hover table-bordered mb-0 text-md-nowrap text-lg-nowrap text-xl-nowrap table-striped">
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