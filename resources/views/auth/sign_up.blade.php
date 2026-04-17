<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up - MarsPay</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">

    <!-- Font Awesome for icons -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

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
        function sign_up() {
            $("#registerBtn").hide();
            $("#registerBtn_loader").show();
            var token = $("input[name=_token]").val();
            var first_name = $("#first_name").val();
            var last_name = $("#last_name").val();
            var mobile = $("#mobile").val();
            var email = $("#email").val();
            var shop_name = $("#shop_name").val();
            var address = $("#address").val();
            var pin_code = $("#pin_code").val();
            var city = $("#city").val();
            var referral_code = $("#referral_code").val();
            var dataString = 'first_name=' + first_name + '&last_name=' + last_name + '&mobile=' + mobile + '&email=' + email + '&shop_name=' + shop_name + '&address=' + address + '&pin_code=' + pin_code + '&city=' + city + '&referral_code=' + referral_code + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('sign-up')}}",
                data: dataString,
                success: function (msg) {
                    $("#registerBtn").show();
                    $("#registerBtn_loader").hide();
                    if (msg.status == 'success') {
                        $("#successMessage").text(msg.message);
                        $("#successMessage").show();
                        window.setTimeout(function () {
                            window.location.href = "{{url('login')}}";
                        }, 3000);
                    } else if (msg.status == 'validation_error') {
                        $("#first_name_errors").text(msg.errors.first_name);
                        $("#last_name_errors").text(msg.errors.last_name);
                        $("#mobile_errors").text(msg.errors.mobile);
                        $("#email_errors").text(msg.errors.email);
                        $("#shop_name_errors").text(msg.errors.shop_name);
                        $("#address_errors").text(msg.errors.address);
                        $("#pin_code_errors").text(msg.errors.pin_code);
                        $("#city_errors").text(msg.errors.city);
                    } else {
                        alert(msg.message);
                    }
                }
            });
        }
    </script>
    <style>
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background-color: #f5f7fa;
        }

        .signup-container {
            display: flex;
            min-height: 100vh;
        }

        .left-section {
            background-color: #0e1b4d;
            color: #fff;
            padding: 50px;
            width: 50%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .left-section h2 {
            font-weight: 600;
            margin-bottom: 20px;
        }

        .left-section ul {
            list-style: none;
            padding: 0;
        }

        .left-section ul li {
             list-style: none;
            margin-bottom: 15px;
            position: relative;
            padding-left: 25px;
            font-size: 16px;
        }

        .left-section ul li::before {
            content: '✔';
            font-family: 'Font Awesome 5 Free';
            color: #dd6808ff;
            font-weight: bold;
            font-size: 10px;
            border-radius: 50%;
            position: absolute;
            left: 0;
            top: 6px;
        }

        .right-section {
            background-color: #ffffff;
            width: 60%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 0;
        }

        .signup-form {
            width: 55%;
            max-width: 500px;
        }

        .form-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-header i {
            font-size: 40px;
            color: #7f56d9;
            margin-bottom: 10px;
        }

        .form-header h4 {
            font-size: 22px;
            font-weight: 600;
            color: #333;
        }

        .form-group input {
            font-size: 14px;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid #dcdcdc;
        }

        .form-group input::placeholder {
            color: #777;
        }

        .signup-btn {
            background-color: #7f56d9;
            color: #fff;
            padding: 10px;
            border-radius: 8px;
            font-size: 14px;
            border: none;
            width: 30%;
            margin-top: 10px;
            margin-left: 35%;
        }

        .signup-btn:hover {
            background-color: #6b45c6;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            margin-left:10%;
        }

        .login-link a {
            color: #7f56d9;
            font-weight: 500;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="signup-container">
    <!-- Left Section -->
    <div class="left-section">
        <img src="{{ $cdnLink}}{{ $company_logo }}" alt="MarsPay Logo" style="width: 250px; margin-bottom: 40px; margin-left:22%;">
        <h2 style= margin-left:23%;font-size:20px;>Why Choose Mars-Pay?</h2>
        <ul style= margin-left:23%;>
            <li>Scalable B2B payment gateway</li>
            <li>Fast & secure online transactions</li>
            <li>Quick integration & onboarding</li>
            <li>Analytics and insights tools</li>
            <li>Trusted by 200+ businesses</li>
        </ul>
    </div>

    <!-- Right Section -->
    <div class="right-section">
        <div class="signup-form">
            <form action="/examples/actions/confirmation.php" method="POST">
                @csrf

                
    <div class="form-header">
        <i class="fa fa-user-circle"></i>
        <h4>Register Here</h4>
    </div>

    <div class="form-group">
        <input type="text" class="form-control" name="first_name" id="first_name" placeholder="First Name">
        <span style="color: red;" id="first_name_errors"></span>
    </div>
    <div class="form-group">
        <input type="text" class="form-control" name="last_name" id="last_name" placeholder="Last Name">
        <span style="color: red;" id="last_name_errors"></span>
    </div>
    <div class="form-group">
        <input type="text" class="form-control" name="organization_name" id="shop_name" placeholder="Organization Name">
        <span style="color: red;" id="shop_name_errors"></span>
    </div>
    <div class="form-group">
        <input type="email" class="form-control" name="email" id="email" placeholder="Email Address">
        <span style="color: red;" id="email_errors"></span>
    </div>
    <div class="form-group">
        <input type="text" class="form-control" name="mobile" id="mobile" placeholder="Mobile Number">
        <span style="color: red;" id="mobile_errors"></span>
    </div>
    <div class="form-group">
        <input type="text" class="form-control" name="address" id="address" placeholder="Address">
        <span style="color: red;" id="address_errors"></span>
    </div>
    <div class="form-group">
        <input type="text" class="form-control" name="pincode" id="pin_code" placeholder="Pin Code">
        <span style="color: red;" id="pin_code_errors"></span>
    </div>
    <div class="form-group">
        <input type="text" class="form-control" name="city" id="city" placeholder="City">
        <span style="color: red;" id="city_errors"></span>
    </div>

    <input type="hidden" class="form-control" id="referral_code" placeholder="Referral Code"
        value="{{ $referral_code }}" @if($referral_code) disabled @endif>

    <button type="button" id="registerBtn" onclick="sign_up()"
        style="background-color: #b149b1ff; color: white; width: 30%; padding: 10px 16px; border: none; border-radius: 8px; font-size: 14px; margin-left:40%;">
       sign up
    </button>
      <button class="btn btn-success btn-lg btn-block" type="button" id="registerBtn_loader" disabled
                    style="display: none;"><span class="spinner-border spinner-border-sm" role="status"
                                                 aria-hidden="true"></span> Loading...
            </button>
</form>


            <div class="login-link">
                Already have an account? <a href="{{ url('login') }}">Sign in</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
