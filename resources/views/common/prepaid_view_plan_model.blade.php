<script type="text/javascript">
    function getprepaidPlan(plantype_id) {
        var provider_id = $("#provider_id").val();
        var state_id = $("#state_id").val();
        if (plantype_id == 3) {
            getTopupPlan(provider_id, state_id, plantype_id);
        } else if (plantype_id == 2) {
            getfullTalktimePlan(provider_id, state_id, plantype_id);
        } else if (plantype_id == 17) {
            getSpecialOfferPlan(provider_id, state_id, plantype_id);
        } else if (plantype_id == 9) {
            getDataPlan(provider_id, state_id, plantype_id);
        } else if (plantype_id == 16) {
            getRateCutterPlan(provider_id, state_id, plantype_id);
        } else if (plantype_id == 18) {
            getForGPlan(provider_id, state_id, plantype_id);
        } else if (plantype_id == 15) {
            getisdPlan(provider_id, state_id, plantype_id);
        } else if (plantype_id == 41) {
            getjioPhonePlan(provider_id, state_id, plantype_id);
        } else if (plantype_id == 48) {
            getPopularPlan(provider_id, state_id, plantype_id);
        } else if (plantype_id == 42) {
            getSmartPhonePlan(provider_id, state_id, plantype_id);
        } else if (plantype_id == 45) {
            getDataAddonPlan(provider_id, state_id, plantype_id);
        } else if (plantype_id == 46) {
            getInternationalRoamingPlan(provider_id, state_id, plantype_id);
        }
    }

    function getTopupPlan(provider_id, state_id, plantype_id) {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var dataString = 'provider_id=' + provider_id + '&state_id=' + state_id + '&plantype_id=' + plantype_id + '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/plan/v1/prepaid-plan')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    var provider_id = msg.provider_id;
                    hideProviderTab(provider_id);

                    var topup = "";
                    for (var key in msg.plans) {
                        topup += "<tr>";
                        topup += "<td>" + msg.plans[key].talktime + "</td>";
                        topup += "<td>" + msg.plans[key].validity + "</td>";
                        topup += "<td>" + msg.plans[key].desc + "</td>";
                        topup += "<td><span class='btn btn-primary btn-sm' style='width:80px;' onclick='pickamt(" + msg.plans[key].rs + ")'>Rs. " + msg.plans[key].rs + " </span></td>";
                        topup += "</tr>";
                    }
                    $('#topupHtml').html(topup);
                    $("#prepaid_plan_model").modal('show');

                } else {
                    swal("Failed", msg.message, "error");
                }
            }
        });
    }


    function getfullTalktimePlan(provider_id, state_id, plantype_id) {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var dataString = 'provider_id=' + provider_id + '&state_id=' + state_id + '&plantype_id=' + plantype_id + '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/plan/v1/prepaid-plan')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    var provider_id = msg.provider_id;
                    hideProviderTab(provider_id);
                    var full_talktime = "";
                    for (var key in msg.plans) {
                        full_talktime += "<tr>";
                        full_talktime += "<td>" + msg.plans[key].talktime + "</td>";
                        full_talktime += "<td>" + msg.plans[key].validity + "</td>";
                        full_talktime += "<td>" + msg.plans[key].desc + "</td>";
                        full_talktime += "<td><span class='btn btn-primary btn-sm' style='width:80px;' onclick='pickamt(" + msg.plans[key].rs + ")'>Rs. " + msg.plans[key].rs + " </span></td>";
                        full_talktime += "</tr>";
                    }
                    $('#fullTalktimeHtml').html(full_talktime);
                } else {
                    swal("Failed", msg.message, "error");
                }
            }
        });
    }


    function getSpecialOfferPlan(provider_id, state_id, plantype_id) {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var dataString = 'provider_id=' + provider_id + '&state_id=' + state_id + '&plantype_id=' + plantype_id + '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/plan/v1/prepaid-plan')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    var provider_id = msg.provider_id;
                    hideProviderTab(provider_id);
                    var special_offer = "";
                    for (var key in msg.plans) {
                        special_offer += "<tr>";
                        special_offer += "<td>" + msg.plans[key].talktime + "</td>";
                        special_offer += "<td>" + msg.plans[key].validity + "</td>";
                        special_offer += "<td>" + msg.plans[key].desc + "</td>";
                        special_offer += "<td><span class='btn btn-primary btn-sm' style='width:80px;' onclick='pickamt(" + msg.plans[key].rs + ")'>Rs. " + msg.plans[key].rs + " </span></td>";
                        special_offer += "</tr>";
                    }
                    $('#specialOfferHtml').html(special_offer);
                } else {
                    swal("Failed", msg.message, "error");
                }
            }
        });
    }


    function getDataPlan(provider_id, state_id, plantype_id) {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var dataString = 'provider_id=' + provider_id + '&state_id=' + state_id + '&plantype_id=' + plantype_id + '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/plan/v1/prepaid-plan')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    var provider_id = msg.provider_id;
                    hideProviderTab(provider_id);
                    var data_plan = "";
                    for (var key in msg.plans) {
                        data_plan += "<tr>";
                        data_plan += "<td>" + msg.plans[key].talktime + "</td>";
                        data_plan += "<td>" + msg.plans[key].validity + "</td>";
                        data_plan += "<td>" + msg.plans[key].desc + "</td>";
                        data_plan += "<td><span class='btn btn-primary btn-sm' style='width:80px;' onclick='pickamt(" + msg.plans[key].rs + ")'>Rs. " + msg.plans[key].rs + " </span></td>";
                        data_plan += "</tr>";
                    }
                    $('#dataPlanHtml').html(data_plan);
                } else {
                    swal("Failed", msg.message, "error");
                }
            }
        });
    }

    function getRateCutterPlan(provider_id, state_id, plantype_id) {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var dataString = 'provider_id=' + provider_id + '&state_id=' + state_id + '&plantype_id=' + plantype_id + '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/plan/v1/prepaid-plan')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    var provider_id = msg.provider_id;
                    hideProviderTab(provider_id);
                    var rate_cutter = "";
                    for (var key in msg.plans) {
                        rate_cutter += "<tr>";
                        rate_cutter += "<td>" + msg.plans[key].talktime + "</td>";
                        rate_cutter += "<td>" + msg.plans[key].validity + "</td>";
                        rate_cutter += "<td>" + msg.plans[key].desc + "</td>";
                        rate_cutter += "<td><span class='btn btn-primary btn-sm' style='width:80px;' onclick='pickamt(" + msg.plans[key].rs + ")'>Rs. " + msg.plans[key].rs + " </span></td>";
                        rate_cutter += "</tr>";
                    }
                    $('#rateCutterHtml').html(rate_cutter);
                } else {
                    swal("Failed", msg.message, "error");
                }
            }
        });
    }

    function getForGPlan(provider_id, state_id, plantype_id) {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var dataString = 'provider_id=' + provider_id + '&state_id=' + state_id + '&plantype_id=' + plantype_id + '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/plan/v1/prepaid-plan')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    var provider_id = msg.provider_id;
                    hideProviderTab(provider_id);
                    var for_g = "";
                    for (var key in msg.plans) {
                        for_g += "<tr>";
                        for_g += "<td>" + msg.plans[key].talktime + "</td>";
                        for_g += "<td>" + msg.plans[key].validity + "</td>";
                        for_g += "<td>" + msg.plans[key].desc + "</td>";
                        for_g += "<td><span class='btn btn-primary btn-sm' style='width:80px;' onclick='pickamt(" + msg.plans[key].rs + ")'>Rs. " + msg.plans[key].rs + " </span></td>";
                        for_g += "</tr>";
                    }
                    $('#forGHtml').html(for_g);
                } else {
                    swal("Failed", msg.message, "error");
                }
            }
        });
    }

    function getisdPlan(provider_id, state_id, plantype_id) {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var dataString = 'provider_id=' + provider_id + '&state_id=' + state_id + '&plantype_id=' + plantype_id + '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/plan/v1/prepaid-plan')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    var provider_id = msg.provider_id;
                    hideProviderTab(provider_id);
                    var isd = "";
                    for (var key in msg.plans) {
                        isd += "<tr>";
                        isd += "<td>" + msg.plans[key].talktime + "</td>";
                        isd += "<td>" + msg.plans[key].validity + "</td>";
                        isd += "<td>" + msg.plans[key].desc + "</td>";
                        isd += "<td><span class='btn btn-primary btn-sm' style='width:80px;' onclick='pickamt(" + msg.plans[key].rs + ")'>Rs. " + msg.plans[key].rs + " </span></td>";
                        isd += "</tr>";
                    }
                    $('#isdHtml').html(isd);
                } else {
                    swal("Failed", msg.message, "error");
                }
            }
        });
    }

    function getjioPhonePlan(provider_id, state_id, plantype_id) {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var dataString = 'provider_id=' + provider_id + '&state_id=' + state_id + '&plantype_id=' + plantype_id + '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/plan/v1/prepaid-plan')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    var provider_id = msg.provider_id;
                    hideProviderTab(provider_id);
                    var jio_phone = "";
                    for (var key in msg.plans) {
                        jio_phone += "<tr>";
                        jio_phone += "<td>" + msg.plans[key].talktime + "</td>";
                        jio_phone += "<td>" + msg.plans[key].validity + "</td>";
                        jio_phone += "<td>" + msg.plans[key].desc + "</td>";
                        jio_phone += "<td><span class='btn btn-primary btn-sm' style='width:80px;' onclick='pickamt(" + msg.plans[key].rs + ")'>Rs. " + msg.plans[key].rs + " </span></td>";
                        jio_phone += "</tr>";
                    }
                    $('#jioPhoneHtml').html(jio_phone);
                } else {
                    swal("Failed", msg.message, "error");
                }
            }
        });
    }

    function getPopularPlan(provider_id, state_id, plantype_id) {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var dataString = 'provider_id=' + provider_id + '&state_id=' + state_id + '&plantype_id=' + plantype_id + '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/plan/v1/prepaid-plan')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    var provider_id = msg.provider_id;
                    hideProviderTab(provider_id);
                    var popular = "";
                    for (var key in msg.plans) {
                        popular += "<tr>";
                        popular += "<td>" + msg.plans[key].talktime + "</td>";
                        popular += "<td>" + msg.plans[key].validity + "</td>";
                        popular += "<td>" + msg.plans[key].desc + "</td>";
                        popular += "<td><span class='btn btn-primary btn-sm' style='width:80px;' onclick='pickamt(" + msg.plans[key].rs + ")'>Rs. " + msg.plans[key].rs + " </span></td>";
                        popular += "</tr>";
                    }
                    $('#popularHtml').html(popular);
                } else {
                    swal("Failed", msg.message, "error");
                }
            }
        });
    }

    function getSmartPhonePlan(provider_id, state_id, plantype_id) {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var dataString = 'provider_id=' + provider_id + '&state_id=' + state_id + '&plantype_id=' + plantype_id + '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/plan/v1/prepaid-plan')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    var provider_id = msg.provider_id;
                    hideProviderTab(provider_id);
                    var smart_phone = "";
                    for (var key in msg.plans) {
                        smart_phone += "<tr>";
                        smart_phone += "<td>" + msg.plans[key].talktime + "</td>";
                        smart_phone += "<td>" + msg.plans[key].validity + "</td>";
                        smart_phone += "<td>" + msg.plans[key].desc + "</td>";
                        smart_phone += "<td><span class='btn btn-primary btn-sm' style='width:80px;' onclick='pickamt(" + msg.plans[key].rs + ")'>Rs. " + msg.plans[key].rs + " </span></td>";
                        smart_phone += "</tr>";
                    }
                    $('#smartPhoneHtml').html(smart_phone);
                } else {
                    swal("Failed", msg.message, "error");
                }
            }
        });
    }

    function getDataAddonPlan(provider_id, state_id, plantype_id) {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var dataString = 'provider_id=' + provider_id + '&state_id=' + state_id + '&plantype_id=' + plantype_id + '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/plan/v1/prepaid-plan')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    var provider_id = msg.provider_id;
                    hideProviderTab(provider_id);
                    var data_addon = "";
                    for (var key in msg.plans) {
                        data_addon += "<tr>";
                        data_addon += "<td>" + msg.plans[key].talktime + "</td>";
                        data_addon += "<td>" + msg.plans[key].validity + "</td>";
                        data_addon += "<td>" + msg.plans[key].desc + "</td>";
                        data_addon += "<td><span class='btn btn-primary btn-sm' style='width:80px;' onclick='pickamt(" + msg.plans[key].rs + ")'>Rs. " + msg.plans[key].rs + " </span></td>";
                        data_addon += "</tr>";
                    }
                    $('#dataAddonHtml').html(data_addon);
                } else {
                    swal("Failed", msg.message, "error");
                }
            }
        });
    }

    function getInternationalRoamingPlan(provider_id, state_id, plantype_id) {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var dataString = 'provider_id=' + provider_id + '&state_id=' + state_id + '&plantype_id=' + plantype_id + '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/plan/v1/prepaid-plan')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    var provider_id = msg.provider_id;
                    hideProviderTab(provider_id);
                    var international_roaming = "";
                    for (var key in msg.plans) {
                        international_roaming += "<tr>";
                        international_roaming += "<td>" + msg.plans[key].talktime + "</td>";
                        international_roaming += "<td>" + msg.plans[key].validity + "</td>";
                        international_roaming += "<td>" + msg.plans[key].desc + "</td>";
                        international_roaming += "<td><span class='btn btn-primary btn-sm' style='width:80px;' onclick='pickamt(" + msg.plans[key].rs + ")'>Rs. " + msg.plans[key].rs + " </span></td>";
                        international_roaming += "</tr>";
                    }
                    $('#internationalRoamingHtml').html(international_roaming);
                } else {
                    swal("Failed", msg.message, "error");
                }
            }
        });
    }

    function hideProviderTab(provider_id) {
        if (provider_id == 1 || provider_id == 2 || provider_id == 4) {
            $(".full_talktime").show();
        } else {
            $(".full_talktime").hide();
        }

        if (provider_id == 1 || provider_id == 2 || provider_id == 4) {
            $(".special_offer").show();
        } else {
            $(".special_offer").hide();
        }

        if (provider_id == 1 || provider_id == 2 || provider_id == 4) {
            $(".data_plan").show();
        } else {
            $(".data_plan").hide();
        }

        if (provider_id == 1 || provider_id == 2 || provider_id == 4) {
            $(".rate_cutter").show();
        } else {
            $(".rate_cutter").hide();
        }

        if (provider_id == 1 || provider_id == 2) {
            $(".for_g").show();
        } else {
            $(".for_g").hide();
        }

        if (provider_id == 6) {
            $(".isd").show();
        } else {
            $(".isd").hide();
        }

        if (provider_id == 6) {
            $(".jio_phone").show();
        } else {
            $(".jio_phone").hide();
        }

        if (provider_id == 6) {
            $(".popular").show();
        } else {
            $(".popular").hide();
        }

        if (provider_id == 6) {
            $(".smart_phone").show();
        } else {
            $(".smart_phone").hide();
        }
        if (provider_id == 6) {
            $(".data_addon").show();
        } else {
            $(".data_addon").hide();
        }

        if (provider_id == 6) {
            $(".international_roaming").show();
        } else {
            $(".international_roaming").hide();
        }

    }

    function pickamt(id) {
        $("#prepaid_plan_model").modal('hide');
        $("#roffer_plan_model").modal('hide');
        $("#amount").val(id);
    }

    $(document).ready(function () {
        $("#myTab a").click(function (e) {
            e.preventDefault();
            $(this).tab("show");
        });
    });

    function getRofferPlan() {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var provider_id = $("#provider_id").val();
        var mobile_number = $("#mobile_number").val();
        var state_id = $("#state_id").val();
        var dataString = 'provider_id=' + provider_id + '&state_id=' + state_id + '&mobile_number=' + mobile_number + '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/plan/v1/roffer-plan')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    var html = "";
                    for (var key in msg.plans) {
                        html += "<tr>";
                        html += "<td>" + msg.plans[key].talktime + "</td>";
                        html += "<td>" + msg.plans[key].validity + "</td>";
                        html += "<td>" + msg.plans[key].desc + "</td>";
                        html += "<td><span class='btn btn-primary btn-sm' style='width:80px;' onclick='pickamt(" + msg.plans[key].rs + ")'>Rs. " + msg.plans[key].rs + " </span></td>";
                        html += "</tr>";
                    }
                    $('#r_offer_plans').html(html);
                    $("#roffer_plan_model").modal('show');
                } else if (msg.status == 'validation_error') {
                    $("#mobile_number_errors").text(msg.errors.mobile_number);
                    $("#provider_id_errors").text(msg.errors.provider_id);
                }
            }
        });
    }
</script>

<div class="modal  show" id="prepaid_plan_model" data-toggle="modal">
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
                            <a href="#topup" class="nav-link active" onclick="javascript:return getprepaidPlan(3);">Top up</a>
                        </li>

                        <li  class="nav-item full_talktime" style="display: none;">
                            <a href="#full_talktime" class="nav-link" onclick="javascript:return getprepaidPlan(2);">Full Talktime</a>
                        </li>

                        <li class="nav-item special_offer" style="display: none;">
                            <a href="#special_offer" class="nav-link" onclick="javascript:return getprepaidPlan(17);">Special Offer</a>
                        </li>

                        <li class="nav-item data_plan" style="display: none;">
                            <a href="#data_plan" class="nav-link" onclick="javascript:return getprepaidPlan(9);">Data</a>
                        </li>

                        <li class="nav-item rate_cutter" style="display: none;">
                            <a href="#rate_cutter" class="nav-link" onclick="javascript:return getprepaidPlan(16);">Rate Cutter</a>
                        </li>

                        <li class="nav-item for_g" style="display: none;">
                            <a href="#for_g" class="nav-link" onclick="javascript:return getprepaidPlan(18);">4G</a>
                        </li>

                        <li class="nav-item isd" style="display: none;">
                            <a href="#isd" class="nav-link" onclick="javascript:return getprepaidPlan(15);">ISD</a>
                        </li>

                        <li class="nav-item jio_phone" style="display: none;">
                            <a href="#jio_phone" class="nav-link" onclick="javascript:return getprepaidPlan(41);">Jio Phone</a>
                        </li>

                        <li class="nav-item popular" style="display: none;">
                            <a href="#popular" class="nav-link" onclick="javascript:return getprepaidPlan(48);">Popular</a>
                        </li>

                        <li class="nav-item smart_phone" style="display: none;">
                            <a href="#smart_phone" class="nav-link" onclick="javascript:return getprepaidPlan(42);">Smart Phone</a>
                        </li>

                        <li class="nav-item data_addon" style="display: none;">
                            <a href="#data_addon" class="nav-link" onclick="javascript:return getprepaidPlan(45);">DataAddon</a>
                        </li>

                        <li class="nav-item international_roaming" style="display: none;">
                            <a href="#international_roaming" class="nav-link" onclick="javascript:return getprepaidPlan(46);">International Roaming</a>
                        </li>

                    </ul>
                    <hr>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="topup">
                            <table class="table table-bordered mb-0">
                                <thead>
                                <tr>
                                    <th>Talktime</th>
                                    <th>Validity</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                </tr>
                                </thead>
                                <tbody id="topupHtml"></tbody>
                            </table>
                        </div>

                        <div class="tab-pane fade" id="full_talktime">
                            <table class="table table-bordered mb-0">
                                <thead>
                                <tr>
                                    <th>Talktime</th>
                                    <th>Validity</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                </tr>
                                </thead>
                                <tbody id="fullTalktimeHtml"></tbody>
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

                        <div class="tab-pane fade" id="rate_cutter">
                            <table class="table table-bordered mb-0">
                                <thead>
                                <tr>
                                    <th>Talktime</th>
                                    <th>Validity</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                </tr>
                                </thead>
                                <tbody id="rateCutterHtml"></tbody>
                            </table>
                        </div>

                        <div class="tab-pane fade" id="for_g">
                            <table class="table table-bordered mb-0">
                                <thead>
                                <tr>
                                    <th>Talktime</th>
                                    <th>Validity</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                </tr>
                                </thead>
                                <tbody id="forGHtml"></tbody>
                            </table>
                        </div>

                        <div class="tab-pane fade" id="isd">
                            <table class="table table-bordered mb-0">
                                <thead>
                                <tr>
                                    <th>Talktime</th>
                                    <th>Validity</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                </tr>
                                </thead>
                                <tbody id="isdHtml"></tbody>
                            </table>
                        </div>

                        <div class="tab-pane fade" id="jio_phone">
                            <table class="table table-bordered mb-0">
                                <thead>
                                <tr>
                                    <th>Talktime</th>
                                    <th>Validity</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                </tr>
                                </thead>
                                <tbody id="jioPhoneHtml"></tbody>
                            </table>
                        </div>

                        <div class="tab-pane fade" id="popular">
                            <table class="table table-bordered mb-0">
                                <thead>
                                <tr>
                                    <th>Talktime</th>
                                    <th>Validity</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                </tr>
                                </thead>
                                <tbody id="popularHtml"></tbody>
                            </table>
                        </div>

                        <div class="tab-pane fade" id="smart_phone">
                            <table class="table table-bordered mb-0">
                                <thead>
                                <tr>
                                    <th>Talktime</th>
                                    <th>Validity</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                </tr>
                                </thead>
                                <tbody id="smartPhoneHtml"></tbody>
                            </table>
                        </div>

                        <div class="tab-pane fade" id="data_addon">
                            <table class="table table-bordered mb-0">
                                <thead>
                                <tr>
                                    <th>Talktime</th>
                                    <th>Validity</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                </tr>
                                </thead>
                                <tbody id="dataAddonHtml"></tbody>
                            </table>
                        </div>

                        <div class="tab-pane fade" id="international_roaming">
                            <table class="table table-bordered mb-0">
                                <thead>
                                <tr>
                                    <th>Talktime</th>
                                    <th>Validity</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                </tr>
                                </thead>
                                <tbody id="internationalRoamingHtml"></tbody>
                            </table>
                        </div>


                    </div>
                </div>
                {{--End Tab--}}


            </div>
        </div>
    </div>
</div>


{{--Start Roffer model--}}
<div class="modal  show" id="roffer_plan_model" data-toggle="modal">
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
                                    <table class="table table-bordered mb-0">
                                        <thead>
                                        <tr>
                                            <th>Talktime</th>
                                            <th>Validity</th>
                                            <th>Description</th>
                                            <th>Price</th>
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
{{--END Roffer model--}}
