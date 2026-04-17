<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign In - {{ $company_name }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Fonts and Bootstrap -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        body, html {
            margin: 0;
            height: 100%;
            font-family: 'Roboto', sans-serif;
            background-color: #0d1b2a;
        }

        .login-wrapper {
            display: flex;
            height: 100vh;
        }

        .login-left {
            flex: 1;
            background-color: #0d1b2a;
            color: white;
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-left h1 {
            font-size: 32px;
            font-weight: 700;
        }

        .login-left ul {
            margin-top: 20px;
            padding-left: 20px;
        }

        .login-left ul li {
            list-style: none;
            margin-bottom: 15px;
            position: relative;
            padding-left: 25px;
            font-size: 16px;
        }

        .login-left ul li::before {
            content: '✔';
            position: absolute;
            left: 0;
            color: #dd6808ff;
            font-weight: bold;
        }

        .login-right {
            flex: 1;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }

        .login-box {
            background: #fff;
            padding: 50px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
            border-radius: 8px;
        }

        .login-box .icon {
            text-align: center;
            font-size: 50px;
            color: #8e44ad;
        }

        .login-box h3 {
            text-align: center;
            font-weight: 700;
            margin: 20px 0;
        }

        .form-control {
            height: 45px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 15px;
        }

        .form-control:focus {
            border-color: #8e44ad;
            box-shadow: 0 0 0 0.2rem rgba(142, 68, 173, 0.25);
        }

        .btn-signin {
            background: linear-gradient(to right, #a445b2, #fa4299);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            width: 50%;
            border-radius:25px;
            margin: 20px auto 10px auto;
            display: block;
        }

        .btn-signin:hover {
            background: linear-gradient(to right, #9226a8, #e73682);
        }

        .signup-link {
            text-align: center;
            font-size: 14px;
            margin-top: 10px;
        }

        .signup-link a {
            color: #8e44ad;
            text-decoration: none;
        }

        .signup-link a:hover {
            text-decoration: underline;
        }

        .form-check label {
            margin-left: 5px;
        }

        @media (max-width: 768px) {
            .login-wrapper {
                flex-direction: column;
            }

            .login-left, .login-right {
                padding: 30px;
            }

            .login-left {
                align-items: center;
                text-align: center;
            }
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <!-- Left Info Panel -->
    <div class="login-left">
        <img src="{{ $cdnLink}}{{ $company_logo }}" alt="MarsPay Logo" style="width: 250px; margin-bottom: 40px; margin-left:22%;">
        <h1 style= margin-left:23%;font-size:20px;>WELCOME to {{ $company_name }}</h1>
        <ul style= margin-left:20%;>
            <li>Fast & secure payment gateway</li>
            <li>Built for high-volume B2B merchants</li>
            <li>Scalable with your business</li>
            <li>Easy onboarding & dashboard access</li>
            <li>Advanced fraud protection & support</li>
        </ul>
    </div>

    <!-- Right Sign-in Form -->
    <div class="login-right">
        <div class="login-box">
            <div class="icon">
                <i class="fas fa-user-circle"></i>
            </div>
            <h3>Sign In</h3>

            <form action="{{ url('login-now') }}" method="post" id="login-form">
                {!! csrf_field() !!}

                @if($errors->any())
                    <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
                @endif

                <input type="hidden" name="company_id" value="{{ $company_id }}">
                <input type="hidden" name="latitude" id="inputLatitude" value="0.00">
                <input type="hidden" name="longitude" id="inputLongitude" value="0.00">

                <div class="form-group">
                    <input type="text" name="username" class="form-control" placeholder="Mobile Number" maxlength="10">
                    @if ($errors->has('username'))
                        <span class="text-danger">{{ $errors->first('username') }}</span>
                    @endif
                </div>

                <div class="form-group">
                    <input type="password" name="password" class="form-control" placeholder="Password">
                    @if ($errors->has('password'))
                        <span class="text-danger">{{ $errors->first('password') }}</span>
                    @endif
                </div>

               <div class="form-check d-flex justify-content-between align-items-center mb-3" style="width: 100%;">
    <div class="d-flex align-items-center">
        <input type="checkbox" id="remember">
        <label for="remember" class="ml-2 mb-0">Remember me</label>
    </div>
    <a href="{{ url('forgot-password') }}" class="ml-auto">Forgot Password?</a>
</div>


                <div class="alert alert-danger location-div mt-2" style="display: none;">
                    <span class="locationMessage"></span>
                </div>

                <button type="submit" class="btn-signin" onclick="return validateLocation();">Sign In</button>

                <div class="signup-link">
                    Don’t have an account? <a href="{{ url('sign-up') }}">Sign up</a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Font Awesome for icon -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

<!-- JS for Location -->
<script>
    $(document).ready(function () {
        getLocation();
    });

    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(showPosition, handleError);
        } else {
            $(".locationMessage").text("Geolocation not supported.");
            $(".location-div").show();
        }
    }

    function showPosition(position) {
        $("#inputLatitude").val(position.coords.latitude);
        $("#inputLongitude").val(position.coords.longitude);
    }

    function handleError(error) {
        $(".location-div").show();
        $(".locationMessage").text("Location access denied.");
    }

    function validateLocation() {
        let lat = $("#inputLatitude").val();
        let lng = $("#inputLongitude").val();

        if (lat && lng && lat !== "0.00" && lng !== "0.00") {
            return true;
        } else {
            $(".location-div").show();
            $(".locationMessage").text("Please allow location access.");
            return false;
        }
    }
</script>

</body>
</html>
