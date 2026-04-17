@extends('agent.layout.header')
@section('content')

    <script type="text/javascript">

        function send_otp() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = '_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/send-transaction-pin-otp')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                    }else{
                        swal("Faild", msg.message, "error");
                    }
                }
            });
        }

        function create_transaction_pin() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var login_password = $("#login_password").val();
            var transaction_pin = $("#transaction_pin").val();
            var otp = $("#otp").val();
            var dataString = 'password=' + login_password + '&transaction_pin=' + transaction_pin + '&otp=' + otp + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/create-transaction-pin')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#login_password_errors").text(msg.errors.password);
                        $("#transaction_pin_errors").text(msg.errors.transaction_pin);
                        $("#otp_errors").text(msg.errors.otp);
                    }else{
                        swal("Faild", msg.message, "error");
                    }
                }
            });
        }
    </script>

    <div class="main-content-body">

        <div class="row">
            <div class="col-lg-5 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">{{ $page_title }}</h6>
                            <hr>
                        </div>



                        <div class="mb-4">
                            <label>Login Password</label>
                            <input type="password" class="form-control" placeholder="Enter Login Pin" id="login_password">
                            <ul class="parsley-errors-list filled">
                                <li class="parsley-required" id="login_password_errors"></li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <label>Transaction Pin</label>
                            <input type="password" class="form-control" placeholder="Enter 6 Digit Pin" id="transaction_pin" onchange="send_otp(this)">
                            <ul class="parsley-errors-list filled">
                                <li class="parsley-required" id="transaction_pin_errors"></li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <div class="form-group">
                                <label for="name">One Time Password</label>
                                <div class="input-group">
                                    <input type="password" id="otp" placeholder="One Time Password" class="form-control">
                                    <span class="input-group-btn">
										<button class="btn ripple btn-danger br-tl-0 br-bl-0" type="button" onclick="send_otp()">Send OTP</button>
									</span>
                                </div>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="otp_errors"></li>
                                </ul>
                            </div>
                        </div>


                    </div>

                    <div class="modal-footer">
                        <button class="btn ripple btn-primary" type="button" onclick="create_transaction_pin()">Create Now</button>
                        <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                    </div>
                </div>
            </div>


        </div>

    </div>
    </div>
    </div>


@endsection