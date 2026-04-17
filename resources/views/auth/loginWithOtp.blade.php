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
        function resendOTP() {
            $("#resendBtn").hide();
            var token = $("input[name=_token]").val();
            var username = $("#username").val();
            var dataString = 'username=' + username + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('resend-login-otp')}}",
                data: dataString,
                success: function (msg) {
                    $("#resendBtn").show();
                    if (msg.status == 'success') {
                        $("#successMessage").text(msg.message);
                        $("#successMessage").show();
                    }else {
                        alert(msg.message);
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

    <form action="{{url('login-with-otp')}}" method="post" id="login-script-form">
        {!! csrf_field() !!}
        <center><img src="{{ $cdnLink}}{{ $company_logo }}" style="height: 60px;"></center>
        <hr>
        @if($errors->any())
            <div class="alert alert-danger"><strong>Error!</strong> {{$errors->first()}}.</div>
        @endif
        <div class="alert alert-success" role="alert" id="successMessage" style="display: none;"></div>
        <input type="hidden" value="{{ $username  }}" name="username" id="username">
        <input type="hidden" value="{{ $password  }}" name="password">
        <input type="hidden" value="{{ $company_id  }}" name="company_id">
        <input type="hidden" value="{{ $latitude  }}" name="latitude">
        <input type="hidden" value="{{ $longitude  }}" name="longitude">


        <div class="form-group">
            <div class="row">
                <div class="col">
                    <label>Login OTP : </label>
                    <input type="password" class="form-control" name="otp" placeholder="Login OTP">
                    @if ($errors->has('otp'))
                        <span class="help-block"><strong
                                    style="color: red">{{ $errors->first('otp') }}</strong></span>
                    @endif
                </div>

            </div>
        </div>

        <div class="form-group">
            <a href="#" onclick="resendOTP()" id="resendBtn">RESEND OTP?</a>
        </div>

        <div class="form-group">
            <button class="btn btn-success btn-lg btn-block" type="submit">Confirm Login</button>
        </div>
    </form>


    <div class="text-center">Don't have an account? <a href="{{url('sign-up')}}">Sign Up</a></div>
</div>


<style>
    body {
        color: #fff;
        background: #63738a;
        font-family: 'Roboto', sans-serif;
    }

    .form-control {
        height: 40px;
        box-shadow: none;
        color: #969fa4;
    }

    .form-control:focus {
        border-color: #5cb85c;
    }

    .form-control, .btn {
        border-radius: 3px;
    }

    .signup-form {
        width: 500px;
        margin: 0 auto;
        padding: 30px 0;
        font-size: 15px;
    }

    .signup-form h2 {
        color: #636363;
        margin: 0 0 15px;
        position: relative;
        text-align: center;
    }

    .signup-form h2:before, .signup-form h2:after {
        content: "";
        height: 2px;
        width: 30%;
        background: #d4d4d4;
        position: absolute;
        top: 50%;
        z-index: 2;
    }

    .signup-form h2:before {
        left: 0;
    }

    .signup-form h2:after {
        right: 0;
    }

    .signup-form .hint-text {
        color: #999;
        margin-bottom: 30px;
        text-align: center;
    }

    .signup-form form {
        color: #999;
        border-radius: 3px;
        margin-bottom: 15px;
        background: #f7f7f7;
        box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);
        padding: 30px;
    }

    .signup-form .form-group {
        margin-bottom: 20px;
    }

    .signup-form input[type="checkbox"] {
        margin-top: 3px;
    }

    .signup-form .btn {
        font-size: 16px;
        font-weight: bold;
        min-width: 140px;
        outline: none !important;
    }

    .signup-form .row div:first-child {
        padding-right: 10px;
    }

    .signup-form .row div:last-child {
        padding-left: 10px;
    }

    .signup-form a {
        color: #fff;
        text-decoration: underline;
    }

    .signup-form a:hover {
        text-decoration: none;
    }

    .signup-form form a {
        color: #5cb85c;
        text-decoration: none;
    }

    .signup-form form a:hover {
        text-decoration: underline;
    }
</style>

{!! csrf_field() !!}
</body>
</html>