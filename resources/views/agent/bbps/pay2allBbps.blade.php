@extends('agent.layout.header')
@section('content')
    <script type="text/javascript">
        $(document).ready(function () {
            $("#provider_id").select2();
        });

        function getBillerParams (){
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var provider_id = $("#provider_id").val();
            var dataString = 'provider_id=' + provider_id +   '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/bbps/v1/biller-params')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {

                        if (msg.fetchRequirement == 1){
                            $("#amountContainer").hide();
                            $("#fatchBtn").show();
                            $("#payBtn").hide();
                        }else{
                            $("#amountContainer").show();
                            $("#fatchBtn").hide();
                            $("#payBtn").show();
                        }
                        const customerParams = msg.customerParams;
                        let container = document.getElementById('inputContainer');
                        container.innerHTML = '';
                        customerParams.forEach(param => {
                            let div = document.createElement('div');
                            div.classList.add('mb-4');

                            let label = document.createElement('label');
                            label.textContent = param.paramName;

                            let input = document.createElement('input');
                            input.setAttribute('type', 'text');
                            input.classList.add('form-control');
                            input.setAttribute('placeholder', param.paramName);
                            input.setAttribute('maxlength', param.maxLength);
                            input.setAttribute('pattern', param.regex);

                            div.appendChild(label);
                            div.appendChild(input);

                            container.appendChild(div);
                        });
                    }else{
                        $("#fatchBtn").hide();
                        $("#payBtn").hide();
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function fatchBill (){
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var provider_id = $("#provider_id").val();
            var customerParams = getParamsValue();
            $.ajax({
                url: "{{url('agent/bbps/v1/fatch-bill')}}",
                type: 'POST',
                dataType: 'json',
                data: {
                    _token: '{{ csrf_token() }}', // Add CSRF token for Laravel
                    provider_id: provider_id,
                    customerParams: JSON.stringify(customerParams)
                },
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        $("#amountContainer").show();
                        $("#amount").val(msg.data.amount);
                        $("#number").val(msg.data.number);
                        $("#reference_id").val(msg.data.reference_id);
                        $(".provider_name").text(msg.data.provider_name);
                        $(".number").text(msg.data.number);
                        $(".amount").text(msg.data.amount);
                        $(".name").text(msg.data.name);
                        $(".dueDate").text(msg.data.dueDate);
                        $(".billDate").text(msg.data.billDate);
                        $(".bill-details").show();
                        $("#fatchBtn").hide();
                        $("#payBtn").show();
                    }else{
                        $("#fatchBtn").show();
                        $("#payBtn").hide();
                        $(".bill-details").hide();
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }


        function viewBillDetails (){
            $(".loader").show();
            var provider_id = $("#provider_id").val();
            var amount = $("#amount").val();
            var number = $("#number").val();
            var customerParams = getParamsValue();
            $.ajax({
                url: "{{url('agent/bbps/v1/view-bill')}}",
                type: 'POST',
                dataType: 'json',
                data: {
                    _token: '{{ csrf_token() }}', // Add CSRF token for Laravel
                    provider_id: provider_id,
                    amount: amount,
                    customerParams: JSON.stringify(customerParams)
                },
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        generate_millisecond();
                        $("#confirm_provider_name").val(msg.data.provider_name);
                        $("#confirm_amount").val(msg.data.amount);
                        // for loop
                        var customerParams = msg.customerParams;
                        var count = Object.keys(customerParams).length;
                        var html = "";
                        for (var key in customerParams) {
                            var columnClass = count === 1 ? 'col-sm-12' : 'col-sm-6';
                            html += '<div class="' + columnClass + '"><div class="form-group"><label for="name">'+ customerParams[key].name +'</label><input type="text"  class="form-control"  value="'+ customerParams[key].value +'" disabled></div></div>';
                        }
                        $("#customerParamsInput").html(html);
                        $("#confirm_recharge_model").modal('show');
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function payNow (){
            var latitude = $("#inputLatitude").val();
            var longitude = $("#inputLongitude").val();
            if (latitude && longitude){
                $("#payNowBtn").hide();
                $("#payNowBtn_loader").show();
                var token = $("input[name=_token]").val();
                var provider_id = $("#provider_id").val();
                var amount = $("#amount").val();
                var number = $("#number").val();
                var transaction_pin = $("#transaction_pin").val();
                var reference_id = $("#reference_id").val();
                var customerParams = getParamsValue();
                $.ajax({
                    url: "{{url('agent/bbps/v1/pay-now')}}",
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        _token: '{{ csrf_token() }}', // Add CSRF token for Laravel
                        provider_id: provider_id,
                        amount: amount,
                        transaction_pin: transaction_pin,
                        reference_id: reference_id,
                        latitude: latitude,
                        longitude: longitude,
                        customerParams: JSON.stringify(customerParams)
                    },
                    success: function (msg) {
                        $("#payNowBtn").show();
                        $("#payNowBtn_loader").hide();
                        if (msg.status == 'success') {
                            $("#confirm_recharge_model").modal('hide');
                            $(".receipt_provider_name").text(msg.data.provider_name);
                            $(".receipt_payid").text(msg.data.payid);
                            $(".receipt_date").text(msg.data.date);
                            $(".receipt_number").text(msg.data.number);
                            $(".receipt_amount").text(msg.data.amount);
                            $(".receipt_profit").text(msg.data.profit);
                            $(".receipt_txnid").text(msg.data.operator_ref);
                            $(".receipt_message").text(msg.message);
                            $("#print_url").attr('href', msg.data.print_url);
                            $("#mobile_anchor").attr('href', msg.data.mobile_anchor);
                            $("#recharge_receipt_model").modal('show');
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

        function getParamsValue (){
            let params = [];

            let inputs = document.querySelectorAll('#inputContainer input');
            inputs.forEach(input => {
                let paramName = input.getAttribute('placeholder');
                let paramValue = input.value.trim();

                // Push name-value pair to jsonData
                params.push({
                    "name": paramName,
                    "value": paramValue
                });
            });
            return params;  // Return array of parameter objects

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


    <div class="main-content-body">
        <input type="hidden" id="reference_id">
        <input type="hidden" id="number">
        <div class="row">
            <div class="col-lg-4 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">{{ $page_title }}</h6>
                            <hr>
                        </div>

                        <div class="mb-4">
                            <label>Provider</label>
                            <select class="form-control select2" id="provider_id" onchange="getBillerParams(this)" style="width: 100%;">
                                <option value="">Select Biller</option>
                                @foreach($providers as $value)
                                    <option value="{{ $value->id }}">{{ $value->provider_name }}</option>
                                @endforeach
                            </select>
                            <ul class="parsley-errors-list filled">
                                <li class="parsley-required" id="provider_id_errors"></li>
                            </ul>
                        </div>

                        <div class="input-container" id="inputContainer">
                            <!-- Input fields will be dynamically added here -->
                        </div>

                        <div  id="amountContainer" style="display: none;">
                            <div class="mb-4">
                                <label>Amount</label>
                                <input type="text" class="form-control" placeholder="Amount" id="amount">
                            </div>
                        </div>


                    </div>

                    <div class="modal-footer">
                        <button class="btn ripple btn-primary" type="button" id="payBtn" onclick="viewBillDetails()" style="display: none">Pay Now</button>
                        <button class="btn ripple btn-danger" type="button" id="fatchBtn" onclick="fatchBill()" style="display: none">Fatch Bill</button>
                    </div>
                </div>
            </div>


            <div class="col-lg-8 col-md-12 bill-details" style="display: none;">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Bill Details</h6>
                            <hr>
                        </div>



                        <table class="table table-bordered">
                            <tr>
                                <th scope="col">Provider Name</th>
                                <td><span class="provider_name"></span></td>
                            </tr>

                            <tr>
                                <th scope="col">Number</th>
                                <td><span class="number"></span></td>
                            </tr>

                            <tr>
                                <th scope="col">Amount</th>
                                <td><span class="amount"></span></td>
                            </tr>

                            <tr>
                                <th scope="col">Name</th>
                                <td><span class="name"></span></td>
                            </tr>

                            <tr>
                                <th scope="col">Due Date</th>
                                <td><span class="dueDate"></span></td>
                            </tr>

                            <tr>
                                <th scope="col">Bill Date</th>
                                <td><span class="billDate"></span></td>
                            </tr>

                        </table>



                    </div>

                </div>
            </div>

        </div>

    </div>
    </div>
    </div>



    <div class="modal  show" id="confirm_recharge_model"data-toggle="modal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Confirm Details</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <input type="hidden" id="recharge_millisecond">

                        <div class="row" id="customerParamsInput"></div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Provider Name</label>
                                    <input type="text" id="confirm_provider_name" class="form-control" placeholder="Provider Name" disabled>
                                    <span class="invalid-feedback d-block" id="confirm_provider_id_errors"></span>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Amount</label>
                                    <input type="text" id="confirm_amount" class="form-control" placeholder="Amount" disabled>
                                    <span class="invalid-feedback d-block" id="confirm_amount_errors"></span>
                                </div>
                            </div>




                            @if(Auth::User()->company->transaction_pin == 1)
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="name">Transaction Pin</label>
                                        <input type="password" id="transaction_pin" class="form-control" placeholder="Transaction Pin">
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn ripple btn-primary" type="button" id="payNowBtn" onclick="payNow()">Confirm Now</button>
                    <button class="btn btn-primary" type="button"  id="payNowBtn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                    <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal  show" id="recharge_receipt_model" data-toggle="modal">
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
@endsection