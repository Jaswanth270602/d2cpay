@extends('agent.layout.header')
@section('content')
<script type="text/javascript">
    function viewDetails() {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var card_number = $("#card_number").val();
        var amount = $("#amount").val();
        var name = $("#name").val();
        var dataString = 'card_number=' + card_number + '&amount=' + amount + '&name=' + name +  '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/credit-card/v1/view-transaction')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    generate_millisecond();
                    $("#confirm_provider_name").val(msg.data.provider_name);
                    $("#confirm_card_number").val(msg.data.card_number);
                    $("#confirm_amount").val(msg.data.amount);
                    $("#confirm_name").val(msg.data.name);
                    $("#view-confirm-model").modal('show');
                } else {
                    swal("Faild", msg.message, "error");
                }
            }
        });
    }

    function payNow (){
        var latitude = $("#inputLatitude").val();
        var longitude = $("#inputLongitude").val();
        if (latitude && longitude) {
            $("#confirmBtn").hide();
            $("#confirmBtn_loader").show();
            var token = $("input[name=_token]").val();
            var card_number = $("#confirm_card_number").val();
            var amount = $("#confirm_amount").val();
            var name = $("#name").val();
            var dupplicate_transaction = $("#recharge_millisecond").val();
            var dataString = 'card_number=' + card_number + '&amount=' + amount +  '&name=' + name +'&latitude=' + latitude + '&longitude=' + longitude + '&dupplicate_transaction=' + dupplicate_transaction +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/credit-card/v1/pay-now')}}",
                data: dataString,
                success: function (msg) {
                    $("#confirmBtn").show();
                    $("#confirmBtn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () {
                            location.reload(1);
                        }, 3000);
                    } else {
                        swal("Faild", msg.message, "error");
                    }
                }
            });
        } else {
            getLocation();
            alert('Please allow this site to access your location');
        }
    }

    function generate_millisecond() {
        var id = 1;
        var token = $("input[name=_token]").val();
        var dataString = 'id=' + id + '&_token=' + token;
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




<div class="main-content-body">

    <div class="row">
        <div class="col-lg-4 col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-1">{{ $page_title }}</h6>
                    </div>
                    <hr>

                    <div class="mb-4">
                        <label>Card Number</label>
                        <input type="text" class="form-control" placeholder="Card Number" id="card_number">
                        <ul class="parsley-errors-list filled">
                            <li class="parsley-required" id="mobile_number_errors"></li>
                        </ul>
                    </div>

                    <div class="mb-4">
                        <label>Credit Card Holder Name</label>
                        <input type="text" class="form-control" placeholder="Credit Card Holder Name" id="name">
                        <ul class="parsley-errors-list filled">
                            <li class="parsley-required" id="name_errors"></li>
                        </ul>
                    </div>


                    <div class="mb-4">
                        <label>Amount</label>
                        <input type="text" class="form-control" placeholder="Amount" id="amount">
                        <ul class="parsley-errors-list filled">
                            <li class="parsley-required" id="amount_errors"></li>
                        </ul>
                    </div>


                </div>

                <div class="modal-footer">
                    <button class="btn ripple btn-primary" type="button" onclick="viewDetails()">Pay Now</button>
                    <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>


    </div>

</div>
</div>
</div>


<div class="modal  show" id="view-confirm-model" data-toggle="modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Confirm Transaction</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="form-body">
                    <input type="hidden" id="recharge_millisecond">
                    <div class="row">

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Provider Name</label>
                                <input type="text" id="confirm_provider_name" class="form-control" disabled>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Card Number</label>
                                <input type="text" id="confirm_card_number" class="form-control" disabled>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" id="confirm_name" class="form-control" disabled>
                            </div>
                        </div>


                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Amount</label>
                                <input type="text" id="confirm_amount" class="form-control" disabled>
                            </div>
                        </div>

                        @if(Auth::User()->company->transaction_pin == 1)
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="name">Transaction Pin</label>
                                <input type="password" id="confirm_transaction_pin" class="form-control"
                                       placeholder="Transaction Pin">
                            </div>
                        </div>
                        @endif

                    </div>

                </div>

            </div>

            <div class="modal-footer">
                <button class="btn ripple btn-primary" type="button" id="confirmBtn" onclick="payNow()">Confirm Now</button>
                <button class="btn btn-primary" type="button" id="confirmBtn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection