@extends('agent.layout.header')
@section('content')

    <script type="text/javascript">


        function createOrder (){
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var mobile_number = $("#mobile_number").val();
            var amount = $("#amount").val();
            var dataString = 'mobile_number=' + mobile_number + '&amount=' + amount +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/add-money/v4/create-order')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        window.open(msg.data.payment_link, '_blank');
                        /*  $("#qrCodeUrl").attr('src', msg.data.qrCodeUrl);
                          $("#qrStringBtn").attr('href', msg.data.qrString);
                          $("#view-qrcode-model").modal('show');*/
                    } else {
                        swal("Faild", msg.message, "error");
                    }
                }
            });
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


                        <div class="mb-4" style="display: none;">
                            <label>Mobile Number</label>
                            <input type="text" class="form-control" placeholder="Mobile Number" id="mobile_number" value="{{ Auth::User()->mobile }}">
                            <ul class="parsley-errors-list filled">
                                <li class="parsley-required" id="mobile_number_errors"></li>
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
                        <button class="btn ripple btn-primary" type="button" onclick="createOrder()">Generate Qrcode</button>
                        <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                    </div>

                </div>
            </div>




        </div>


    </div>
    </div>
    </div>




    <div class="modal  show" id="view-qrcode-model" data-toggle="modal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Scan & Pay</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <center>
                        <b>
                            <h4>Open any UPI or Bank's mobile app and scan this QR code</h4>
                            <h5> You will be prompted to enter your UPI PIN on the app</h5>
                        </b>
                        <br>
                        <img src="" class="qr_code" id="qrCodeUrl" style="width: 200px;">

                        <hr>
                        Post successful payment, balance will reflect in your {{ $company_name }} within 5 minutes
                    </center>

                    <a class="btn btn-primary btn-lg btn-block"  href="" role="button" id="qrStringBtn">
                        <i class=""></i>Pay <span id="amountString"></span> Using App
                    </a>
                </div>

            </div>
        </div>
    </div>

@endsection