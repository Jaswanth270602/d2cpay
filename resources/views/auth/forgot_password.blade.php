<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,700">
    <title>{{ $company_name }}</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
    <script type="text/javascript">
        function forgotPassword() {
            $("#forgotBtn").hide();
            $("#forgotBtn_loader").show();
            var token = $("input[name=_token]").val();
            var mobile = $("#mobile").val();
            var dataString = 'mobile=' + mobile + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('forgot-password-otp')}}",
                data: dataString,
                success: function (msg) {
                    $("#forgotBtn").show();
                    $("#forgotBtn_loader").hide();
                    if (msg.status == 'success') {
                        $("#successMessage").text(msg.message);
                        $("#successMessage").show();
                        $("#otp-label").show();
                        $("#fogot-password-btn-label").hide();
                        $("#confirm-forgotPassword-label").show();
                    } else if (msg.status == 'validation_error') {
                        $("#mobile_errors").text(msg.errors.mobile);
                    } else {
                        $("#otp-label").hide();
                        $("#fogot-password-btn-label").show();
                        $("#confirm-forgotPassword-label").hide();
                        $("#failureMessage").text(msg.message);
                        $("#failureMessage").show();
                    }
                }
            });
        }

        function confirmForgotPassword() {
            $("#confirmForgotBtn").hide();
            $("#confirmForgotBtn_loader").show();
            var token = $("input[name=_token]").val();
            var mobile = $("#mobile").val();
            var otp = $("#otp").val();
            var dataString = 'mobile=' + mobile + '&otp=' + otp + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('confirm-forgot-password')}}",
                data: dataString,
                success: function (msg) {
                    $("#confirmForgotBtn").show();
                    $("#confirmForgotBtn_loader").hide();
                    if (msg.status == 'success') {
                        $("#successMessage").text(msg.message);
                        $("#successMessage").show();
                        window.setTimeout(function () {
                            window.location.href = "{{url('login')}}";
                        }, 3000);
                    } else if (msg.status == 'validation_error') {
                        $("#mobile_errors").text(msg.errors.mobile);
                        $("#otp_errors").text(msg.errors.otp);
                    } else {
                        $("#failureMessage").text(msg.message);
                        $("#failureMessage").show();
                    }
                }
            });
        }
    </script>
</head>
<body>
@if (Auth::guest())
@else
    @if(Auth::user()->role_id <= 10)
        <script type="text/javascript">
            document.location.href = "admin/dashboard";
        </script>
    @endif
@endif

<div class="signup-form">
    <form action="#" method="post" id="login-script-form">
        {!! csrf_field() !!}

        <div class="logo-container text-center">
            <img src="{{ $cdnLink}}{{ $company_logo }}" style="height: 60px;">
        </div>

        <div class="alert alert-success" role="alert" id="successMessage" style="display: none;"></div>
        <div class="alert alert-danger" role="alert" id="failureMessage" style="display: none;"></div>

        <div class="form-group">
            <label style="color:white;">Mobile Number :</label>
            <input type="text" class="form-control" id="mobile" placeholder="Mobile Number">
            <span style="color: red;" id="mobile_errors"></span>
        </div>

        <div class="form-group" id="otp-label" style="display: none;">
            <label>Enter OTP :</label>
            <input type="password" class="form-control" id="otp" placeholder="Enter OTP">
            <span style="color: red;" id="otp_errors"></span>
        </div>

        <div class="form-group" id="fogot-password-btn-label">
            <center>
                <button class="btn signin-btn" type="button" id="forgotBtn" onclick="forgotPassword()">
                    Forgot Password
                </button>
            </center>
            <center>
                <button class="btn signin-btn" type="button" id="forgotBtn_loader" disabled style="display: none;">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...
                </button>
            </center>
        </div>

        <div class="form-group" id="confirm-forgotPassword-label" style="display: none;">
            <button class="btn btn-success btn-lg btn-block" type="button" id="confirmForgotBtn"
                    onclick="confirmForgotPassword()">Confirm OTP
            </button>
            <button class="btn btn-success btn-lg btn-block" type="button" id="confirmForgotBtn_loader" disabled style="display: none;">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...
            </button>
        </div>
    </form>

    <div class="text-center text-light mt-2">Don’t have an account? <a href="{{url('sign-up')}}">Sign up</a></div>
</div>

<style>
    body {
        background:#ccd6e0;
        font-family: 'Roboto', sans-serif;
    }

    .logo-container {
        padding-bottom: 15px;
        border-bottom: 2px solid #ffffff;
        margin-bottom: 20px;
    }

    .signup-form {
        width: 500px;
        margin: 50px auto;
    }

    .signup-form form {
        background: #001f4d; /* Card is now navy-blue */
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    .form-control {
        height: 45px;
        background: #ffffff; /* input field white */
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .signin-btn {
        background:linear-gradient(to right, #a445b2, #fa4299); /* pink-orange gradient */
        color: white;
        font-weight: bold;
        border: none;
        padding: 10px 20px;
        border-radius: 25px;
        cursor: pointer;
        width: 40%;
        margin-bottom: 10px;
        transition: 0.3s;
    }

    .signin-btn:hover {
        opacity: 0.9;
    }

    .alert {
        font-size: 14px;
        padding: 10px;
    }

    .text-center a {
        color: #ffc107;
        font-weight: bold;
    }

</style>

</body>
</html>
