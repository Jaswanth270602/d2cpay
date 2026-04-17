<script type="text/javascript">
    function prepad_plan() {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var provider_id = $("#provider_id").val();
        var state_id = $("#state_id").val();
        var dataString = 'provider_id=' + provider_id + '&state_id=' + state_id +   '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/plan/v1/prepaid-plan')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    var full_talktime = "";
                    for (var key in msg.full_talktime) {
                        full_talktime += "<tr>";
                        full_talktime += "<td>" + msg.full_talktime[key].desc + "</td>";
                        full_talktime += "<td>" + msg.full_talktime[key].validity + "</td>";
                        full_talktime += "<td><span class='btn btn-primary btn-sm' style='width:80px;' onclick='pickamt(" + msg.full_talktime[key].rs + ")'>Rs. " + msg.full_talktime[key].rs + " </span></td>";
                        full_talktime += "</tr>";
                    }


                    var internet_3g = "";
                    for (var key in msg.internet_3g) {
                        internet_3g += "<tr>";
                        internet_3g += "<td>" + msg.internet_3g[key].desc + "</td>";
                        internet_3g += "<td>" + msg.internet_3g[key].validity + "</td>";
                        internet_3g += "<td><span class='btn btn-primary btn-sm' style='width:80px;' onclick='pickamt(" + msg.internet_3g[key].rs + ")'>Rs. " + msg.internet_3g[key].rs + " </span></td>";
                        internet_3g += "</tr>";
                    }

                    var rate_cutter = "";
                    for (var key in msg.rate_cutter) {
                        rate_cutter += "<tr>";
                        rate_cutter += "<td>" + msg.rate_cutter[key].desc + "</td>";
                        rate_cutter += "<td>" + msg.rate_cutter[key].validity + "</td>";
                        rate_cutter += "<td><span class='btn btn-primary btn-sm' style='width:80px;' onclick='pickamt(" + msg.rate_cutter[key].rs + ")'>Rs. " + msg.rate_cutter[key].rs + " </span></td>";
                        rate_cutter += "</tr>";
                    }

                    var internet_2g = "";
                    for (var key in msg.internet_2g) {
                        internet_2g += "<tr>";
                        internet_2g += "<td>" + msg.internet_2g[key].desc + "</td>";
                        internet_2g += "<td>" + msg.internet_2g[key].validity + "</td>";
                        internet_2g += "<td><span class='btn btn-primary btn-sm' style='width:80px;' onclick='pickamt(" + msg.internet_2g[key].rs + ")'>Rs. " + msg.internet_2g[key].rs + " </span></td>";
                        internet_2g += "</tr>";
                    }

                    var sms = "";
                    for (var key in msg.sms) {
                        sms += "<tr>";
                        sms += "<td>" + msg.sms[key].desc + "</td>";
                        sms += "<td>" + msg.sms[key].validity + "</td>";
                        sms += "<td><span class='btn btn-primary btn-sm' style='width:80px;' onclick='pickamt(" + msg.sms[key].rs + ")'>Rs. " + msg.sms[key].rs + " </span></td>";
                        sms += "</tr>";
                    }

                    var combo = "";
                    for (var key in msg.combo) {
                        combo += "<tr>";
                        combo += "<td>" + msg.combo[key].desc + "</td>";
                        combo += "<td>" + msg.combo[key].validity + "</td>";
                        combo += "<td><span class='btn btn-primary btn-sm' style='width:80px;' onclick='pickamt(" + msg.combo[key].rs + ")'>Rs. " + msg.combo[key].rs + " </span></td>";
                        combo += "</tr>";
                    }


                    var topup = "";
                    for (var key in msg.topup) {
                        topup += "<tr>";
                        topup += "<td>" + msg.topup[key].desc + "</td>";
                        topup += "<td>" + msg.topup[key].validity + "</td>";
                        topup += "<td><span class='btn btn-primary btn-sm' style='width:80px;' onclick='pickamt(" + msg.topup[key].rs +")'>Rs. " + msg.topup[key].rs  + " </span></td>";
                        topup += "</tr>";
                    }

                    $('#full_talktimes').html(full_talktime);
                    $('#internet_3gs').html(internet_3g);
                    $('#rate_cutters').html(rate_cutter);
                    $('#internet_2gs').html(internet_2g);
                    $('#smss').html(sms);
                    $('#combos').html(combo);
                    $('#topups').html(topup);
                    $("#prepaid_plan_model").modal('show');
                }
            }
        });
    }
    function pickamt(id) {
        $("#prepaid_plan_model").modal('hide');
        $("#roffer_plan_model").modal('hide');
        $("#amount").val(id);
    }

    function r_offer() {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var provider_id = $("#provider_id").val();
        var mobile_number = $("#mobile_number").val();
        var dataString = 'provider_id=' + provider_id + '&mobile_number=' + mobile_number +   '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/plan/v1/r-offer')}}",
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
                    $('#r_offer_plans').html(html);
                    $("#roffer_plan_model").modal('show');
                }else if(msg.status == 'validation_error'){
                    $("#mobile_number_errors").text(msg.errors.mobile_number);
                    $("#provider_id_errors").text(msg.errors.provider_id);
                }
            }
        });
    }
</script>


<div class="modal  show" id="prepaid_plan_model"data-toggle="modal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Browse Plans</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="d-md-flex">

                    <div class="panel panel-primary tabs-style-4">
                        <div class="tab-menu-heading">
                            <div class="tabs-menu ">
                                <!-- Tabs -->
                                <ul class="nav panel-tabs">
                                    <li><a href="#Top_up" class="active" data-toggle="tab">Top up</a></li>
                                    <li><a href="#Full_Talktime" data-toggle="tab">Full Talktime</a></li>
                                    <li><a href="#threeg" data-toggle="tab">3G/4G</a></li>
                                    <li><a href="#Rate_Cutter" data-toggle="tab">Rate Cutter</a></li>
                                    <li><a href="#two_g" data-toggle="tab">2G</a></li>
                                    <li><a href="#Sms" data-toggle="tab">Sms</a></li>
                                    <li><a href="#Combo" data-toggle="tab">Combo</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="tabs-style-4" style="margin-left: 2%; width: 100%;">
                        <div class="panel-body tabs-menu-body">
                            <div class="tab-content">
                                <div class="tab-pane active" id="Top_up">
                                    <table class="table table-hover table-bordered mb-0 text-md-nowrap text-lg-nowrap text-xl-nowrap table-striped">
                                        <thead>
                                        <tr>
                                            <th>Description</th>
                                            <th>Validity</th>
                                            <th>Action</th>
                                        </tr>
                                        </thead>
                                        <tbody id="topups"></tbody>
                                    </table>
                                </div><!-- tab-pane -->

                                <div class="tab-pane" id="Full_Talktime">
                                    <table class="table table-hover table-bordered mb-0 text-md-nowrap text-lg-nowrap text-xl-nowrap table-striped">
                                        <thead>
                                        <tr>
                                            <th>Description</th>
                                            <th>Validity</th>
                                            <th>Action</th>
                                        </tr>
                                        </thead>
                                        <tbody id="full_talktimes"></tbody>
                                    </table>
                                </div>

                                <div class="tab-pane" id="threeg">
                                    <table class="table table-hover table-bordered mb-0 text-md-nowrap text-lg-nowrap text-xl-nowrap table-striped">
                                        <thead>
                                        <tr>
                                            <th>Description</th>
                                            <th>Validity</th>
                                            <th>Action</th>
                                        </tr>
                                        </thead>
                                        <tbody id="internet_3gs"></tbody>
                                    </table>
                                </div>

                                <div class="tab-pane" id="Rate_Cutter">
                                    <table class="table table-hover table-bordered mb-0 text-md-nowrap text-lg-nowrap text-xl-nowrap table-striped">
                                        <thead>
                                        <tr>
                                            <th>Description</th>
                                            <th>Validity</th>
                                            <th>Action</th>
                                        </tr>
                                        </thead>
                                        <tbody id="rate_cutters"></tbody>
                                    </table>
                                </div>

                                <div class="tab-pane" id="two_g">
                                    <table class="table table-hover table-bordered mb-0 text-md-nowrap text-lg-nowrap text-xl-nowrap table-striped">
                                        <thead>
                                        <tr>
                                            <th>Description</th>
                                            <th>Validity</th>
                                            <th>Action</th>
                                        </tr>
                                        </thead>
                                        <tbody id="internet_2gs"></tbody>
                                    </table>
                                </div>

                                <div class="tab-pane" id="Sms">
                                    <table class="table table-hover table-bordered mb-0 text-md-nowrap text-lg-nowrap text-xl-nowrap table-striped">
                                        <thead>
                                        <tr>
                                            <th>Description</th>
                                            <th>Validity</th>
                                            <th>Action</th>
                                        </tr>
                                        </thead>
                                        <tbody id="smss"></tbody>
                                    </table>
                                </div>

                                <div class="tab-pane" id="Combo">
                                    <table class="table table-hover table-bordered mb-0 text-md-nowrap text-lg-nowrap text-xl-nowrap table-striped">
                                        <thead>
                                        <tr>
                                            <th>Description</th>
                                            <th>Validity</th>
                                            <th>Action</th>
                                        </tr>
                                        </thead>
                                        <tbody id="combos"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
</div>



<div class="modal  show" id="roffer_plan_model"data-toggle="modal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">R Offer</h6>
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
                                        <tbody id="r_offer_plans"></tbody>
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