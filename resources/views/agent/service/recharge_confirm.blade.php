<script type="text/javascript">

    $(document).ready(function () {
        $("#provider_id").select2();
        $("#state_id").select2();
        $("#payment_mode").select2();
    });


    function confirm_recharges() {
        $("#confirm_recharges_btn").hide();
        $("#confirm_recharges_btn_loader").show();
        var token = $("input[name=_token]").val();
        var provider_id = $("#confirm_provider_id").val();
        var amount = $("#confirm_amount").val();
        var mobile_number = $("#confirm_mobile_number").val();
        var millisecond = $("#recharge_millisecond").val();
        var optional1 = $("#confirm_optional1").val();
        var optional2 = $("#confirm_optional2").val();
        var optional3 = $("#confirm_optional3").val();
        var optional4 = $("#confirm_optional4").val();
        var duedate = $("#confirm_duedate").val();
        var customer_name = $("#confirm_name").val();
        var transaction_pin = $("#confirm_transaction_pin").val();
        var latitude = $("#inputLatitude").val();
        var longitude = $("#inputLongitude").val();
        if (latitude && longitude){
            var dataString = 'provider_id=' + provider_id + '&amount=' + amount + '&mobile_number=' + mobile_number + '&optional1=' + optional1 + '&optional2=' + optional2 + '&optional3=' + optional3 + '&optional4=' + optional4 +  '&dupplicate_transaction=' + millisecond + '&transaction_pin=' + transaction_pin + '&duedate=' + duedate + '&customer_name=' + customer_name + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/web-recharge-now')}}",
                data: dataString,
                success: function (msg) {
                    $("#confirm_recharges_btn").show();
                    $("#confirm_recharges_btn_loader").hide();
                    if (msg.status == 'success') {
                        $("#confirm_recharge_model").modal('hide');
                        $(".receipt_provider_name").text(msg.transaction_details.provider_name);
                        $(".receipt_payid").text(msg.transaction_details.payid);
                        $(".receipt_date").text(msg.transaction_details.date);
                        $(".receipt_number").text(msg.transaction_details.number);
                        $(".receipt_amount").text(msg.transaction_details.amount);
                        $(".receipt_profit").text(msg.transaction_details.profit);
                        $(".receipt_txnid").text(msg.transaction_details.operator_ref);
                        $(".receipt_message").text(msg.message);
                        $("#print_url").attr('href', msg.transaction_details.print_url);
                        $("#mobile_anchor").attr('href', msg.transaction_details.mobile_anchor);
                        $("#recharge_receipt_model").modal('show');
                    } else if(msg.status == 'validation_error'){
                        $("#confirm_provider_id_errors").text(msg.errors.confirm_provider_id);
                        $("#confirm_amount_errors").text(msg.errors.confirm_amount);
                        $("#confirm_mobile_number_errors").text(msg.errors.confirm_mobile_number);
                        $("#dupplicate_transaction_errors").text(msg.errors.dupplicate_transaction);
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }else{
            getLocation();
            alert('Please allow this site to access your location');
        }
    }


    function generate_millisecond() {
        var id = 1;
        var token = $("input[name=_token]").val();
        var dataString = 'id=' + id +  '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/generate-millisecond')}}",
            data: dataString,
            success: function (msg) {
                $("#recharge_millisecond").val(msg.miliseconds);
            }
        });
    }
</script>


<div class="modal  show" id="confirm_recharge_model"data-toggle="modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Confirm Details</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="form-body">
                    <input type="hidden" id="confirm_optional1">
                    <input type="hidden" id="confirm_optional2">
                    <input type="hidden" id="confirm_optional3">
                    <input type="hidden" id="confirm_optional4">
                    <input type="hidden" id="confirm_provider_id">
                    <input type="hidden" id="confirm_duedate">
                    <input type="hidden" id="confirm_name">
                    <input type="hidden" id="recharge_millisecond">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Provider Name</label>
                                <input type="text" id="confirm_provider_name" class="form-control" placeholder="Provider Name" disabled>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="confirm_provider_id_errors"></li>
                                </ul>


                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Amount</label>
                                <input type="text" id="confirm_amount" class="form-control" placeholder="Amount" disabled>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="confirm_amount_errors"></li>
                                </ul>

                            </div>
                        </div>


                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="name">Number</label>
                                <input type="text" id="confirm_mobile_number" class="form-control" placeholder="Number" disabled>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="confirm_mobile_number_errors"></li>
                                    <li class="parsley-required" id="dupplicate_transaction_errors"></li>
                                </ul>
                            </div>
                        </div>

                        @if(Auth::User()->company->transaction_pin == 1)
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="name">Transaction Pin</label>
                                <input type="password" id="confirm_transaction_pin" class="form-control" placeholder="Transaction Pin">
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn ripple btn-primary" type="button" id="confirm_recharges_btn" onclick="confirm_recharges()">Confirm Now</button>
                <button class="btn btn-primary" type="button"  id="confirm_recharges_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal  show" id="recharge_receipt_model"data-toggle="modal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title"><img src="{{$cdnLink}}{{ $company_logo }}" style="height: 40px;"></h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="task-stat pb-0">
                        <div class="d-flex tasks">
                            <div class="mb-0">
                                <div class="h6 fs-15 mb-0">Provider Name: </div>
                            </div>
                            <span class="float-right ml-auto receipt_provider_name"></span>
                        </div>

                        <div class="d-flex tasks">
                            <div class="mb-0">
                                <div class="h6 fs-15 mb-0">Order Id : <span class="receipt_payid"></span></div>
                            </div>
                            <span class="float-right ml-auto">Date : <span class="receipt_date"></span></span>
                        </div>

                        <div class="d-flex tasks">
                            <div class="mb-0">
                                <div class="h6 fs-15 mb-0">Number : <span class="receipt_number"></span></div>
                            </div>
                            <span class="float-right ml-auto">Amount: <span class="receipt_amount"></span></span>
                        </div>

                        <div class="d-flex tasks">
                            <div class="mb-0">
                                <div class="h6 fs-15 mb-0">Profit : <span class="receipt_profit"></span></div>
                            </div>
                            <span class="float-right ml-auto">Txnid : <span class="receipt_txnid"></span></span>
                        </div>

                    </div>
                </div>

                <div class="alert alert-success" role="alert">
                    <span class="receipt_message"></span>
                </div>

            </div>
            <div class="modal-footer">
                <a href="" class="btn ripple btn-primary" target="_blank" id="print_url">Print</a>
                <a href="" class="btn ripple btn-primary" target="_blank" id="mobile_anchor">Mobile Print</a>
                <a href="{{ request()->fullUrl() }}" class="btn ripple btn-danger">Another Transaction</a>

            </div>
        </div>
    </div>
</div>