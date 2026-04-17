<script>
    function provider_details() {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var provider_id = $("#provider_id").val();
        var dataString = 'provider_id=' + provider_id +   '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/check-provider-validation')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    if (msg.is_validation == 1){
                        $("#verify_btn").show();
                        $("#pay_btn").hide();
                    }else{
                        $("#verify_btn").hide();
                        $("#pay_btn").show();
                    }
                    var params = msg.params;
                    var html = "";
                    for (var key in params) {
                        html += '<div class="mb-4"><label>'+ params[key].name +'</label><input type="text" class="form-control" placeholder="'+ params[key].name +'" name="number[]"></div> <ul class="parsley-errors-list filled"><li class="parsley-required" id="number_errors"></li></ul>';
                    }
                    var payment_modes = msg.payment_modes;
                    var modes = "";
                    for (var key in payment_modes) {
                        modes += '<option value="' + payment_modes[key].mode + '">' + payment_modes[key].mode + ' </option>';
                    }
                    $("#number_input").html(html);
                    $("#payment_mode").html(modes);
                    $("#payment-mode-label").show();
                } else if(msg.status == 'validation_error'){
                    $("#provider_id_errors").text(msg.errors.provider_id);
                }else{
                    swal("Failed", msg.message, "error");
                }
            }
        });
    }

    function bill_verify() {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var provider_id = $("#provider_id").val();
        var payment_mode = $("#payment_mode").val();
        var all_location_id = document.querySelectorAll('input[name="number[]"]');
        var aIds = [];
        for(var x = 0, l = all_location_id.length; x < l;  x++) {
            aIds.push(all_location_id[x].value);
        }
        var dataString = 'provider_id=' + provider_id + '&number=' + aIds +  '&payment_mode=' + payment_mode + '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/bbps-bill-verify')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    $("#amount").val(msg.amount);
                    $(".provider_name").text(msg.provider_name);
                    $(".number").text(msg.number);
                    $(".amount").text(msg.amount);
                    $(".name").text(msg.name);
                    $(".duedate").text(msg.duedate);
                    $("#customer-details-label").show();
                    $("#amount-label").show();
                    $("#pay_btn").show();
                    $("#verify_btn").hide();

                    $("#mobile_number").val(msg.number);
                    $("#confirm_optional1").val(msg.optional1);
                    $("#confirm_optional2").val(msg.optional2);
                    $("#confirm_optional3").val(msg.optional3);
                    $("#confirm_optional4").val(msg.optional4);
                    $("#confirm_duedate").val(msg.duedate);
                    $("#confirm_name").val(msg.name);
                }else if(msg.status == 'validation_error'){
                    $("#number_errors").text(msg.errors.number);
                    $("#provider_id_errors").text(msg.errors.provider_id);
                    $("#payment_mode_errors").text(msg.errors.payment_mode);
                    $("#customer-details-label").hide();
                    $("#amount-label").hide();
                    $("#pay_btn").hide();
                } else{
                    $("#customer-details-label").hide();
                    swal("Failed", msg.message, "error");
                    $("#amount-label").hide();
                    $("#pay_btn").hide();
                }
            }
        });
    }

    function view_recharges() {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var mobile_number = $("#mobile_number").val();
        var provider_id = $("#provider_id").val();
        var amount = $("#amount").val();
        var dataString = 'mobile_number=' + mobile_number + '&provider_id=' + provider_id + '&amount=' + amount + '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/view-recharge-details')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    generate_millisecond();
                    $("#confirm_provider_name").val(msg.confirm_provider_name);
                    $("#confirm_mobile_number").val(msg.confirm_mobile_number);
                    $("#confirm_amount").val(msg.confirm_amount);
                    $("#confirm_provider_id").val(msg.confirm_provider_id);
                    $("#confirm_recharge_model").modal('show');
                } else if(msg.status == 'validation_error'){
                    $("#mobile_number_errors").text(msg.errors.mobile_number);
                    $("#provider_id_errors").text(msg.errors.provider_id);
                    $("#amount_errors").text(msg.errors.amount);
                }else{
                    swal("Failed", msg.message, "error");
                }
            }
        });
    }
</script>

<input type="hidden" id="mobile_number">

<div class="col-lg-4 col-md-12">
    <div class="card">
        <div class="card-body">
            <div>
                <h6 class="card-title mb-1">{{ $page_title }}</h6>
                <hr>
            </div>



            <div class="mb-4">
                <label>Provider</label>
                <select class="form-control select2" id="provider_id" onchange="provider_details(this)" style="width: 100%;">
                    <option value="">Select Provider</option>
                    @foreach($providers as $value)
                        <option value="{{ $value->id }}">{{ $value->provider_name }}</option>
                    @endforeach
                </select>
                <ul class="parsley-errors-list filled">
                    <li class="parsley-required" id="provider_id_errors"></li>
                </ul>
            </div>

            <div id="number_input"></div>

            <div class="mb-4" id="amount-label" style="display: none;">
                <label>Amount</label>
                <input type="text" class="form-control" placeholder="Amount" id="amount">
                <ul class="parsley-errors-list filled">
                    <li class="parsley-required" id="amount_errors"></li>
                </ul>
            </div>



            <div class="card" id="customer-details-label" style="display: none">
                <div class="task-stat pb-0">
                    <div class="d-flex tasks">
                        <div class="mb-0"><div class="h6 fs-15 mb-0">Provider </div></div>
                        <span class="float-right ml-auto provider_name"></span>
                    </div>

                    <div class="d-flex tasks">
                        <div class="mb-0"><div class="h6 fs-15 mb-0">Number </div></div>
                        <span class="float-right ml-auto number"></span>
                    </div>

                    <div class="d-flex tasks">
                        <div class="mb-0"><div class="h6 fs-15 mb-0">Name : </div></div>
                        <span class="float-right ml-auto name"></span>
                    </div>

                    <div class="d-flex tasks">
                        <div class="mb-0"><div class="h6 fs-15 mb-0">Amount : </div></div>
                        <span class="float-right ml-auto amount"></span>
                    </div>

                    <div class="d-flex tasks">
                        <div class="mb-0"><div class="h6 fs-15 mb-0">Due Date : </div></div>
                        <span class="float-right ml-auto duedate"></span>
                    </div>

                </div>
            </div>


        </div>

        <div class="modal-footer">
            <button class="btn ripple btn-danger" type="button" onclick="bill_verify()" id="verify_btn" style="display: none;">Verify Amount</button>
            <button class="btn ripple btn-primary" type="button" onclick="view_recharges()" id="pay_btn" style="display: none;">Pay Now</button>
            <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
        </div>
    </div>
</div>