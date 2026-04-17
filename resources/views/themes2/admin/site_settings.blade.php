@extends('themes2.admin.layout.header')
@section('content')
    <script type="text/javascript">
        $(document).ready(function () {
            $("#sms").select2();
            $("#whatsapp").select2();
            $("#send_mail").select2();
        });

        function update_details() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var setting_id = $("#setting_id").val();
            var brand_name = $("#brand_name").val();
            var sms = $("#sms").val();
            var sms_key = $("#sms_key").val();
            var whatsapp = $("#whatsapp").val();
            var whatsapp_key = $("#whatsapp_key").val();
            var whatsapp_number = $("#whatsapp_number").val();
            var alert_amount = $("#alert_amount").val();
            var send_mail = $("#send_mail").val();
            var mail_transport = $("#mail_transport").val();
            var mail_host = $("#mail_host").val();
            var mail_port = $("#mail_port").val();
            var mail_encryption = $("#mail_encryption").val();
            var mail_username = $("#mail_username").val();
            var mail_password = $("#mail_password").val();
            var mail_from = $("#mail_from").val();
            // registration
            var registration_status = $("#registration_status").val();
            var registration_scheme_id = $("#registration_scheme_id").val();
            var registration_role_id = $("#registration_role_id").val();
            var registration_parent_id = $("#registration_parent_id").val();
            var registration_state_id = $("#registration_state_id").val();
            var registration_district_id = $("#registration_district_id").val();
            var dataString = 'setting_id=' + setting_id + '&brand_name=' + brand_name + '&sms=' + sms + '&sms_key=' + sms_key + '&whatsapp=' + whatsapp + '&whatsapp_key=' + whatsapp_key + '&whatsapp_number=' + whatsapp_number + '&alert_amount=' + alert_amount + '&mail_transport=' + mail_transport + '&mail_host=' + mail_host + '&mail_port=' + mail_port + '&mail_encryption=' + mail_encryption + '&mail_username=' + mail_username + '&mail_password=' + mail_password + '&mail_from=' + mail_from + '&send_mail=' + send_mail + '&registration_status=' + registration_status + '&registration_scheme_id=' + registration_scheme_id + '&registration_role_id=' + registration_role_id + '&registration_parent_id=' + registration_parent_id + '&registration_state_id=' + registration_state_id + '&registration_district_id=' + registration_district_id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/site-setting/update-settings')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () {
                            location.reload(1);
                        }, 3000);
                    } else if (msg.status == 'validation_error') {
                        $("#brand_name_errors").text(msg.errors.brand_name);
                        $("#mail_username_errors").text(msg.errors.mail_username);
                        $("#mail_from_errors").text(msg.errors.mail_from);
                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function get_distric() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var registration_state_id = $("#registration_state_id").val();
            var dataString = 'state_id=' + registration_state_id + '&_token=' + token;
            $.ajax({
                type: "post",
                url: "{{url('admin/get-distric-by-state')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        var districts = msg.districts;
                        var html = "";
                        for (var key in districts) {
                            html += '<option value="' + districts[key].district_id + '">' + districts[key].district_name + ' </option>';
                        }
                        $("#registration_district_id").html(html);

                    } else {
                        alert(msg.message);
                    }
                }
            });

        }
    </script>
    <input type="hidden" id="setting_id" value="{{ $id }}">
    <!--  Content Area Starts  -->
    <div id="content" class="main-content">
        <!-- Main Body Starts -->
    @include('themes2.admin.layout.breadcrumb')
    <!-- Main Body Starts -->
        <div class="layout-px-spacing">
            <div class="row layout-top-spacing">
                <!-- REVENUE ENDS-->
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
                    <div class="widget dashboard-table">
                        <div class="widget-heading">
                            <h5 class=""> {{ $page_title }}</h5>
                        </div>
                        <hr>
                        <div class="widget-content">
                            <div class="card-body">
                                <div class="form-body">
                                    <div class="row">

                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">Brand Name (for sms)</label>
                                                <input type="text" id="brand_name" class="form-control" placeholder="Brand Name"
                                                       value="{{ $brand_name }}">
                                                <span class="invalid-feedback d-block" id="brand_name_errors"></span>
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">Sms</label>
                                                <select class="form-control select2" id="sms">
                                                    <option value="0" @if($sms == 0) selected @endif>Disabled</option>
                                                    <option value="1" @if($sms == 1) selected @endif>Enabled</option>
                                                </select>
                                                <span class="invalid-feedback d-block" id="sms_errors"></span>
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">Sms Key</label>
                                                <input type="text" id="sms_key" class="form-control" placeholder="Sms Key" value="{{ $sms_key }}">
                                                <span class="invalid-feedback d-block"><a href="http://sms.sms21.co.in/welcome/" target="_blank" style="color: blue">Sms Api Click Here</a></span>
                                                <span class="invalid-feedback d-block" id="sms_key_errors"></span>
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">Whatsapp Number</label>
                                                <input type="text" id="whatsapp_number" class="form-control" placeholder="Whatsapp Number" value="{{ $whatsapp_number }}">
                                                <span class="invalid-feedback d-block" id="whatsapp_number_errors"></span>
                                            </div>
                                        </div>


                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">Whatsapp</label>
                                                <select class="form-control select2" id="whatsapp">
                                                    <option value="0" @if($whatsapp == 0) selected @endif>Disabled</option>
                                                    <option value="1" @if($whatsapp == 1) selected @endif>Enabled</option>
                                                </select>
                                                <span class="invalid-feedback d-block" id="whatsapp_errors"></span>
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">Whatsapp Api Token</label>
                                                <input type="text" id="whatsapp_key" class="form-control" placeholder="Whatsapp Api Token" value="{{ $whatsapp_key }}">
                                                <span class="invalid-feedback d-block"><a href="https://whatsbot.tech/register?ref=MPEJRO3I" target="_blank" style="color: blue;">Whatsapp Api Click Here</a></span>
                                                <span class="invalid-feedback d-block" id="whatsapp_key_errors"></span>
                                            </div>
                                        </div>


                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">Alert Amount</label>
                                                <input type="text" id="alert_amount" class="form-control" placeholder="Alert Amount" value="{{ $alert_amount }}">
                                                <span class="invalid-feedback d-block" id="alert_amount_errors"></span>
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">Mail</label>
                                                <select class="form-control select2" id="send_mail">
                                                    <option value="0" @if($send_mail == 0) selected @endif>Disabled</option>
                                                    <option value="1" @if($send_mail == 1) selected @endif>Enabled</option>
                                                </select>
                                                <span class="invalid-feedback d-block" id="send_mail_errors"></span>
                                            </div>
                                        </div>


                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                {{--Start Guest Registration Setting--}}

                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
                    <div class="widget dashboard-table">
                        <div class="widget-heading">
                            <h5 class=""> Guest Registration Setting </h5>
                        </div>
                        <hr>
                        <div class="widget-content">
                            <div class="card-body">
                                <div class="form-body">
                                    <div class="row">

                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">Registration Status</label>
                                                <select class="form-control" id="registration_status">
                                                    <option value="0" @if($registration_status == 0) selected @endif>Disabled</option>
                                                    <option value="1" @if($registration_status == 1) selected @endif>Enabled</option>
                                                </select>
                                                <span class="invalid-feedback d-block" id="registration_status_errors"></span>
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">Default Scheme</label>
                                                <select class="form-control" id="registration_scheme_id">
                                                    <option value="0" @if($registration_status == 0) selected @endif>Select Scheme</option>
                                                    @foreach($schemes as $value)
                                                        <option value="{{ $value->id }}"
                                                                @if($registration_scheme_id == $value->id) selected @endif>{{ $value->scheme_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <span class="invalid-feedback d-block" id="registration_scheme_id_errors"></span>
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">Default Member Type</label>
                                                <select class="form-control" id="registration_role_id">
                                                    <option value="0" @if($registration_role_id == 0) selected @endif>
                                                        Select Member Type
                                                    </option>
                                                    @foreach($roles as $value)
                                                        <option value="{{ $value->id }}"
                                                                @if($registration_role_id == $value->id) selected @endif>{{ $value->role_title }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <span class="invalid-feedback d-block" id="registration_role_id_errors"></span>
                                            </div>
                                        </div>


                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">Default Parents</label>
                                                <select class="form-control" id="registration_parent_id">
                                                    <option value="0" @if($registration_parent_id == 0) selected @endif>
                                                        Select Parents
                                                    </option>
                                                    @foreach($users as $value)
                                                        <option value="{{ $value->id }}"
                                                                @if($registration_parent_id == $value->id) selected @endif>{{ $value->name }} {{ $value->last_name }}</option>
                                                    @endforeach
                                                </select>
                                                <span class="invalid-feedback d-block" id="registration_parent_id_errors"></span>

                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">Default State</label>
                                                <select class="form-control" id="registration_state_id" onchange="get_distric(this)">
                                                    <option value="0" @if($registration_state_id == 0) selected @endif>
                                                        Select State
                                                    </option>
                                                    @foreach($states as $value)
                                                        <option value="{{ $value->id }}"
                                                                @if($registration_state_id == $value->id) selected @endif>{{ $value->name }}</option>
                                                    @endforeach
                                                </select>
                                                <span class="invalid-feedback d-block" id="registration_state_id_errors"></span>

                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">Default District</label>
                                                <select class="form-control" id="registration_district_id">
                                                    <option value="0" @if($registration_district_id == 0) selected @endif>
                                                        Select District
                                                    </option>
                                                    @foreach($districts as $value)
                                                        <option value="{{ $value->id }}"
                                                                @if($registration_district_id == $value->id) selected @endif>{{ $value->district_name }}</option>
                                                    @endforeach
                                                </select>
                                                <span class="invalid-feedback d-block" id="rregistration_district_id_errors"></span>

                                            </div>
                                        </div>

                                    </div>

                                </div>


                            </div>
                        </div>
                    </div>
                </div>
                {{--End Guest Registration Setting--}}



                {{--Start Mail Setting--}}
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
                    <div class="widget dashboard-table">
                        <div class="widget-heading">
                            <h5 class=""> Mail Setting </h5>
                        </div>
                        <hr>
                        <div class="widget-content">
                            <div class="card-body">

                                <div class="form-body">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">Transport</label>
                                                <input type="text" id="mail_transport" class="form-control"
                                                       placeholder="Example : (mail)" value="{{ $mail_transport }}">
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">Host</label>
                                                <input type="text" id="mail_host" class="form-control"
                                                       placeholder="Example : (smtp.gmail.com)" value="{{ $mail_host }}">
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">Port</label>
                                                <input type="text" id="mail_port" class="form-control"
                                                       placeholder="Example : (587)" value="{{ $mail_port }}">
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">Encryption</label>
                                                <input type="text" id="mail_encryption" class="form-control"
                                                       placeholder="Example : (tls)" value="{{ $mail_encryption }}">
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">Mail Username</label>
                                                <input type="text" id="mail_username" class="form-control"
                                                       placeholder="Mail Username" value="{{ $mail_username }}">
                                                <span class="invalid-feedback d-block" id="mail_username_errors"></span>
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">Mail Password</label>
                                                <input type="password" id="mail_password" class="form-control"
                                                       placeholder="Password" value="{{ $mail_password }}">
                                                <span class="invalid-feedback d-block" id="mail_password_errors"></span>
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">Mail From</label>
                                                <input type="text" id="mail_from" class="form-control"
                                                       placeholder="noreply@domain.com" value="{{ $mail_from }}">
                                                <span class="invalid-feedback d-block" id="mail_from_errors"></span>
                                            </div>
                                        </div>


                                        <div class="col-sm-12">
                                            <div class="card">
                                                <div class="card-header tx-medium bd-0 tx-white bg-primary">
                                                    Gmail Settings
                                                </div>
                                                <div class="card-body ">
                                                    <p class="mg-b-0">Did you turn on the "Allow less secure apps" on? go to
                                                        this link</p>
                                                    <p class="mg-b-0"><a
                                                                href="https://myaccount.google.com/security#connectedapps"
                                                                target="_blank" style="color: blue;">https://myaccount.google.com/security#connectedapps</a>
                                                    </p>
                                                    <p class="mg-b-0">Take a look at the Sign-in & security -> Apps with account
                                                        access menu. You must turn the option "Allow less secure apps" ON.</p>
                                                    <p class="mg-b-0">If is still doesn't work try one of these:</p>
                                                    <ul>
                                                        <li>Go to <a href="https://accounts.google.com/UnlockCaptcha"
                                                                     target="_blank" style="color: blue;">https://accounts.google.com/UnlockCaptcha</a>,
                                                            and click continue and unlock your account for access through other
                                                            media/sites.
                                                        </li>
                                                        <li>Use double quote in your password: "your password"</li>
                                                    </ul>
                                                    <p class="mg-b-0">And change your site setting mail password</p>
                                                    <p class="mg-b-0">Something's gone wrong. We're working to get it fixed as
                                                        soon as we can.</p>

                                                </div>
                                            </div>
                                        </div>


                                    </div>

                                </div>

                                <hr>
                                <div class="widget-footer">
                                    <button type="reset" class="btn btn-primary mr-2" onclick="update_details()">Submit</button>
                                    <button type="reset" class="btn btn-outline-primary">Cancel</button>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                {{--End Mail Setting--}}
            </div>
        </div>
        <!-- Main Body Ends -->


@endsection