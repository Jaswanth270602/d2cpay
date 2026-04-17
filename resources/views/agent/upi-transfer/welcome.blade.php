@extends('agent.layout.header')
@section('content')
    <script type="text/javascript">
        function getUpiextensions() {
            var token = $("input[name=_token]").val();
            var provider_id = $("#provider_id").val();
            if (provider_id == 1) {
                $('#upi_id').attr('placeholder', 'Enter Bank Upi id');
                $(".extension-label").hide();
                $(".example-text").text('Example : 1234567890@icici');
            } else {
                $(".loader").show();
                var dataString = 'provider_id=' + provider_id + '&_token=' + token;
                $.ajax({
                    type: "post",
                    url: "{{url('agent/upi-transfer/v1/getUpiextensions')}}",
                    data: dataString,
                    success: function (msg) {
                        $(".loader").hide();
                        if (msg.status == 'success') {
                            var extensions = msg.extensions;
                            var html = "";
                            for (var key in extensions) {
                                html += '<option value="' + extensions[key].name + '">' + extensions[key].name + ' </option>';
                            }
                            $('#upi_id').attr('placeholder', msg.placeholder);
                            $(".example-text").text(msg.example_text);
                            $("#extension_id").html(html);
                            $(".extension-label").show();
                        } else {
                            alert(msg.message);
                        }
                    }
                });
            }
        }

        function fatchName() {
            var latitude = $("#inputLatitude").val();
            var longitude = $("#inputLongitude").val();
            if (latitude && longitude){
                var token = $("input[name=_token]").val();
                var provider_id = $("#provider_id").val();
                if (provider_id == 1) {
                    var upi_id = $("#upi_id").val();
                    var upiId = $("#upi_id").val();
                } else {
                    var upi_id = $("#upi_id").val();
                    var extension_id = $("#extension_id").val();
                    var upiId = upi_id + '@' + extension_id;
                }
                if (upi_id){
                    $(".loader").show();
                    var dataString = 'upi_id=' + upiId + '&latitude=' + latitude + '&longitude=' + longitude + '&_token=' + token;
                    $.ajax({
                        type: "POST",
                        url: "{{url('agent/upi-transfer/v1/fatch-name')}}",
                        data: dataString,
                        success: function (msg) {
                            $(".loader").hide();
                            if (msg.status == 'success') {
                                $("#beneficiary_name").val(msg.beneficiary_name);
                            }else{
                                swal("Faild", msg.message, "error");
                            }
                        }
                    });
                }else{
                    alert('Upi id field required!');
                }
            }else{
                getLocation();
                alert('Please allow this site to access your location');
            }
        }

        function viewTransaction (){

            var latitude = $("#inputLatitude").val();
            var longitude = $("#inputLongitude").val();
            if (latitude && longitude){
                var token = $("input[name=_token]").val();
                var provider_id = $("#provider_id").val();
                if (provider_id == 1) {
                    var upi_id = $("#upi_id").val();
                    var upiId = $("#upi_id").val();
                } else {
                    var upi_id = $("#upi_id").val();
                    var extension_id = $("#extension_id").val();
                    var upiId = upi_id + '@' + extension_id;
                }
                if (upi_id){
                    $(".loader").show();
                    var beneficiary_name = $("#beneficiary_name").val();
                    var customer_mobile = $("#customer_mobile").val();
                    var amount = $("#amount").val();
                    var dataString = 'upi_id=' + upiId + '&beneficiary_name=' + beneficiary_name + '&customer_mobile=' + customer_mobile + '&amount=' + amount + '&latitude=' + latitude + '&longitude=' + longitude + '&_token=' + token;
                    $.ajax({
                        type: "POST",
                        url: "{{url('agent/upi-transfer/v1/view-transaction')}}",
                        data: dataString,
                        success: function (msg) {
                            $(".loader").hide();
                            if (msg.status == 'success') {
                                $("#confirm_upi_id").val(msg.details.upi_id);
                                $("#confirm_beneficiary_name").val(msg.details.beneficiary_name);
                                $("#confirm_customer_mobile").val(msg.details.customer_mobile);
                                $("#confirm_amount").val(msg.details.amount);
                                $("#view-confirm-model").modal('show');
                            }else{
                                swal("Faild", msg.message, "error");
                            }
                        }
                    });
                }else{
                    alert('Upi id field required!');
                }
            }else{
                getLocation();
                alert('Please allow this site to access your location');
            }
        }
    </script>

    <div class="main-content-body">
        <div class="row">
            <div class="col-lg-6 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">{{ $page_title }}</h6>
                            <hr>
                        </div>



                        <div class="form-body">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="name">Providers</label>
                                        <select class="form-control" id="provider_id" onchange="getUpiextensions()">
                                            @foreach($upiproviders as $value)
                                                <option value="{{ $value->id }}">{{ $value->provider_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="row row-sm">
                            <div class="col-lg-6">
                                <div class="input-group mb-3">
                                    <input  class="form-control" placeholder="Enter Bank Upi id" type="text" id="upi_id">
                                    <div class="input-group-append extension-label" style="display: none;">
                                       <select class="form-control" id="extension_id"></select>
                                    </div>
                                </div>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required example-text">Example : 1234567890@icici</li>
                                </ul>
                            </div>
                            <div class="col-lg-6">
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend"></div>
                                    <input  class="form-control" type="text" placeholder="Beneficiary Name" id="beneficiary_name">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-danger btn-sm" onclick="fatchName()">Fatch Name</button>
                                    </div>
                                </div><!-- input-group -->
                            </div>
                        </div>
                        <hr>

                        <div class="form-body">
                            <div class="row">

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Customer Mobile</label>
                                        <input  class="form-control" type="text" placeholder="Customer Mobile" id="customer_mobile">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Amount</label>
                                        <input  class="form-control" type="text" placeholder="Amount" id="amount">
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn ripple btn-primary" type="button" onclick="viewTransaction()">Trasnfer Amount</button>
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
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <input type="hidden" id="recharge_millisecond">
                        <div class="row">

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Beneficiary Name</label>
                                    <input type="text" id="confirm_beneficiary_name" class="form-control"  disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Upi Id</label>
                                    <input type="text" id="confirm_upi_id" class="form-control"  disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Customer Mobile</label>
                                    <input type="text" id="confirm_customer_mobile" class="form-control"  disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Amount</label>
                                    <input type="text" id="confirm_amount" class="form-control"  disabled>
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
                    <button class="btn ripple btn-primary" type="button" id="confirm_transaction_btn"
                            onclick="confirm_transaction()">Confirm Now
                    </button>
                    <button class="btn btn-primary" type="button" id="confirm_transaction_btn_loader" disabled
                            style="display: none;"><span class="spinner-border spinner-border-sm" role="status"
                                                         aria-hidden="true"></span> Loading...
                    </button>
                    <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
    </div>

@endsection