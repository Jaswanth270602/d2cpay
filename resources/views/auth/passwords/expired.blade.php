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

<div class="signup-form">
    @if (session('status'))
    <div class="alert alert-success">
        {{ session('status') }} :  <a href="/">Return to homepage</a>
    </div>

    @else
    <div class="alert alert-info">
        Your password has expired, please change it.
    </div>
    <form class="form-horizontal" method="POST" action="{{ url('password/post_expired') }}">
        {{ csrf_field() }}

        <div class="form-group{{ $errors->has('current_password') ? ' has-error' : '' }}">
            <label for="current_password" class="col-md-12 control-label">Current Password</label>

            <div class="col-md-12">
                <input id="current_password" type="password" class="form-control" name="current_password" placeholder="Current Password">
                @if ($errors->has('current_password'))
                <span class="help-block">
                    <strong style="color: red">{{ $errors->first('current_password') }}</strong>
                </span>
                @endif
            </div>
        </div>

        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
            <label for="password" class="col-md-12 control-label">New Password</label>
            <div class="col-md-12">
                <input id="password" type="password" class="form-control" name="password" placeholder="New Password">

                @if ($errors->has('password'))
                <span class="help-block">
                   <strong style="color: red">{{ $errors->first('password') }}</strong>
                </span>
                @endif
            </div>
        </div>

        <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
            <label for="password-confirm" class="col-md-12 control-label">Confirm New Password</label>
            <div class="col-md-12">
                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" placeholder="Confirm New Password">
                @if ($errors->has('password_confirmation'))
                <span class="help-block">
                    <strong style="color: red">{{ $errors->first('password_confirmation') }}</strong>
                </span>
                @endif
            </div>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-lg btn-block">
                Reset Password
            </button>
        </div>
    </form>
    @endif
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