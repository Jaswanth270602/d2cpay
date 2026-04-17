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


</head>
<body>
<script type="text/javascript">
    function sendOTP (){
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var aadhar_aumber = $("#aadhar_aumber").val();
        var dataString = 'aadhar_aumber=' + aadhar_aumber + '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('sign-up/v1/send-aadhar-otp')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    $(".AlertMessage").text(msg.message);
                    $("#client_id").val(msg.client_id);
                    $("#reference_id").val(msg.reference_id);
                    $(".otp-label").show();
                    $(".register-label").hide();
                    document.getElementById("aadhar_aumber").disabled = true;
                }else{
                    $(".otp-label").hide();
                    $(".register-label").hide();
                    document.getElementById("aadhar_aumber").disabled = false;
                    alert(msg.message);
                }
            }
        });
    }

    function aadharVerifyOTP (){
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var aadhar_aumber = $("#aadhar_aumber").val();
        var client_id = $("#client_id").val();
        var reference_id = $("#reference_id").val();
        var otp = $("#aadhar_otp").val();
        var dataString = 'aadhar_aumber=' + aadhar_aumber + '&client_id=' + client_id + '&reference_id=' + reference_id + '&otp=' + otp + '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('sign-up/v1/aadhar-otp-verify')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    $(".otp-label").hide();
                    $("#verify_id").val(msg.data.verify_id);
                    $("#full_name").val(msg.data.full_name);
                    $("#first_name").val(msg.data.first_name);
                    $("#last_name").val(msg.data.last_name);
                    $("#care_of").val(msg.data.care_of);
                    $("#address").val(msg.data.address);
                    $("#dob").val(msg.data.dob);
                    $("#gender").val(msg.data.gender);
                    $("#pincode").val(msg.data.pincode);
                    $("#profile_photo").attr('src', msg.data.profile_photo);
                    $(".register-label").show();
                }else{
                    alert(msg.message);
                }
            }
        });
    }

    function registerNow (){
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var aadhar_aumber = $("#aadhar_aumber").val();
        var verify_id = $("#verify_id").val();
        var mobile = $("#mobile_number").val();
        var email = $("#email_address").val();
        var pan_number = $("#pan_number").val();
        var dataString = 'aadhar_aumber=' + aadhar_aumber + '&verify_id=' + verify_id + '&mobile=' + mobile + '&email=' + email + '&pan_number=' + pan_number + '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('sign-up/v1/register-now')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    alert(msg.message);
                    window.setTimeout(function () {
                        window.location.href = "{{url('login')}}";
                    }, 3000);
                }else{
                    alert(msg.message);
                }
            }
        });
    }
</script>
<div class="loader" style="display: none;"></div>


<div class="signup-form">
    <form action="/examples/actions/confirmation.php" method="post">
        <center><img src="{{ $cdnLink}}{{ $company_logo }}" style="height: 60px;"></center>
        <hr>

        <div class="alert alert-success" role="alert" id="successMessage" style="display: none;"></div>
        <div class="form-group">
            <div class="row">
                <div class="col">
                    <label>Aadhar Number : </label>
                    <input type="text" class="form-control" id="aadhar_aumber" placeholder="Aadhar Number">
                    <span style="position: relative;top: -37px; float: right;"  class="btn btn-danger btn-sm">
                        <a style="cursor: pointer; color: white;" onclick="sendOTP()">Send OTP</a>
                    </span>
                </div>
            </div>
        </div>

        <div class="otp-label" style="display: none;">
            <span class="AlertMessage" style="color: green;"></span>
            <input type="hidden" id="client_id">
            <input type="hidden" id="reference_id">
            <hr>
            <div class="form-group">
                <div class="row">
                    <div class="col">
                        <label>Aadhar OTP : </label>
                        <input type="text" class="form-control" id="aadhar_otp" placeholder="Aadhar OTP">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <button class="btn btn-success btn-lg btn-block" type="button" onclick="aadharVerifyOTP()">Verify OTP</button>
            </div>
        </div>

        <div class="register-label" style="display: none;">
            <input type="hidden" id="verify_id">
            <input type="hidden" id="full_name">
            <hr>
            <div class="form-group">
                <div class="row">
                    <div class="col">
                        <label>First Name: </label>
                        <input type="text" class="form-control" id="first_name" placeholder="First Name" readonly>
                    </div>

                    <div class="col">
                        <label>Last Name: </label>
                        <input type="text" class="form-control" id="last_name" placeholder="Last Name" readonly>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col">
                        <label>Care Of: </label>
                        <input type="text" class="form-control" id="care_of" placeholder="Care Of" readonly>
                    </div>

                    <div class="col">
                        <label>Pincode: </label>
                        <input type="text" class="form-control" id="pincode" placeholder="Pincode" readonly>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col">
                        <label>Date Of Birth: </label>
                        <input type="text" class="form-control" id="dob" placeholder="Date Of birth" readonly>
                    </div>

                    <div class="col">
                        <label>Gender: </label>
                        <select class="form-control" id="gender" disabled>
                            <option value="M">Male</option>
                            <option value="F">Female</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col">
                        <label>Address: </label>
                        <textarea type="text" class="form-control" id="address" readonly></textarea>
                    </div>

                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col">
                        <label>Mobile Number: </label>
                        <input type="text" class="form-control" id="mobile_number" placeholder="Mobile Number">
                    </div>

                    <div class="col">
                        <label>Email Address: </label>
                        <input type="text" class="form-control" id="email_address" placeholder="Email Address">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col">
                        <label>Pan Number: </label>
                        <input type="text" class="form-control" id="pan_number" placeholder="Pan Number">
                    </div>


                </div>
            </div>

            <div class="form-group">
                <button class="btn btn-success btn-lg btn-block" type="button" onclick="registerNow()">Register Now</button>
            </div>
        </div>



        {{-- <div class="form-group">
             <button class="btn btn-success btn-lg btn-block" type="button" onclick="sign_up()" id="registerBtn" disabled>Register Now</button>
         </div>--}}
    </form>
    <div class="text-center">Already have an account? <a href="{{url('login')}}">Sign in</a></div>
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

    .loader {
        position: fixed !important;
        left: 0px !important;
        top: 0px !important;
        width: 100% !important;
        height: 100% !important;
        z-index: 9999 !important;
        background: url('https://media.giphy.com/media/y1ZBcOGOOtlpC/giphy.gif') 50% 50% no-repeat rgb(249,249,249) !important;
        opacity: .8 !important;
    }
</style>


{!! csrf_field() !!}
</body>
</html>