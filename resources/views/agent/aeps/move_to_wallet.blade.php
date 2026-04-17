@extends('agent.layout.header')
@section('content')

    <script type="text/javascript">
        function transfer_amount() {
            var token = $("input[name=_token]").val();
            var amount = $("#amount").val();
            var remark = $("#remark").val();
            var password = $("#password").val();
            var transaction_pin = $("#transaction_pin").val();
            var latitude = $("#inputLatitude").val();
            var longitude = $("#inputLongitude").val();
            if (latitude && longitude){
                $(".loader").show();
                var dataString = 'amount=' + amount + '&remark=' + remark + '&password=' + password + '&transaction_pin=' + transaction_pin + '&latitude=' + latitude + '&longitude=' + longitude +  '&_token=' + token;
                $.ajax({
                    type: "POST",
                    url: "{{url('agent/payout/v1/move-to-wallet')}}",
                    data: dataString,
                    success: function (msg) {
                        $(".loader").hide();
                        if (msg.status == 'success') {
                            swal("Success", msg.message, "success");
                            setTimeout(function () { location.reload(1); }, 3000);
                        } else if(msg.status == 'validation_error'){
                            $("#amount_errors").text(msg.errors.amount);
                            $("#remark_errors").text(msg.errors.remark);
                            $("#password_errors").text(msg.errors.password);
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
    </script>

    <!-- main-content-body -->
    <div class="main-content-body">

        <!-- row -->
        <div class="row row-sm">
        @include('agent.aeps.left_side')

        <!-- Col -->
            <div class="col-lg-8 col-xl-9">
                <div class="card">
                    <div class="card-body">
                        <form class="form-horizontal">
                            <div class="mb-4 main-content-label">{{ $page_title }}</div>
                            <hr>

                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Aeps Balance</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control"  placeholder="Aeps Balance" value="{{ number_format(Auth::User()->balance->aeps_balance, 2) }}" disabled>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Amount</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control"  placeholder="Amount" id="amount">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="amount_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Remark</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control"  placeholder="Remark" id="remark">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="remark_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Login Password</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="password" class="form-control"  placeholder="Login Password" id="password">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="password_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            @if(Auth::User()->company->transaction_pin == 1)
                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Transaction Pin</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="password" class="form-control"  placeholder="Transaction Pin" id="transaction_pin">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="transaction_pin_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            @endif

                        </form>
                    </div>


                    <div class="card-footer bg-white">
                        <div class="float-right">
                            <button  class="btn btn-success" id="submit_btn" onclick="transfer_amount()">Transfer Now</button>
                        </div>

                    </div>
                </div>
            </div>
            <!-- /Col -->


        </div>
        <!-- /row -->

        <!-- row -->



    </div>
    <!-- /row -->
    </div>
    <!-- /container -->
    </div>
    <!-- /main-content -->





@endsection