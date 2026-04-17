<script type="text/javascript">

    function change_password() {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var old_password = $("#old_password").val();
        var new_password = $("#new_password").val();
        var confirm_password = $("#confirm_password").val();
        var dataString = 'old_password=' + old_password + '&new_password=' + new_password + '&confirm_password=' + confirm_password +  '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/change-password')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    swal("Success", msg.message, "success");
                    setTimeout(function () { location.reload(1); }, 5000);
                } else if(msg.status == 'validation_error'){
                    $("#old_password_errors").text(msg.errors.old_password);
                    $("#new_password_errors").text(msg.errors.new_password);
                    $("#confirm_password_errors").text(msg.errors.confirm_password);
                }else{
                    swal("Faild", msg.message, "error");
                }
            }
        });
    }

    function get_permanent_distric() {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var state_id = $("#permanent_state").val();
        var dataString = 'state_id=' + state_id +  '&_token=' + token;
        $.ajax({
            type: "post",
            url: "{{url('agent/get-distric-by-state')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    var districts = msg.districts;
                    var html = "";
                    for (var key in districts) {
                        html += '<option value="' + districts[key].district_id + '">' + districts[key].district_name + ' </option>';
                    }
                    $("#permanent_district").html(html);

                }else{
                    alert(msg.message);
                }
            }
        });
    }

    function get_present_distric() {

        $(".loader").show();
        var token = $("input[name=_token]").val();
        var state_id = $("#present_state").val();
        var dataString = 'state_id=' + state_id +  '&_token=' + token;
        $.ajax({
            type: "post",
            url: "{{url('agent/get-distric-by-state')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    var districts = msg.districts;
                    var html = "";
                    for (var key in districts) {
                        html += '<option value="' + districts[key].district_id + '">' + districts[key].district_name + ' </option>';
                    }
                    $("#present_district").html(html);

                }else{
                    alert(msg.message);
                }
            }
        });
    }
    
    function update_profile() {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var permanent_address = $("#permanent_address").val();
        var permanent_state = $("#permanent_state").val();
        var permanent_district = $("#permanent_district").val();
        var permanent_city = $("#permanent_city").val();
        var permanent_pin_code = $("#permanent_pin_code").val();
        var present_address = $("#present_address").val();
        var present_state = $("#present_state").val();
        var present_district = $("#present_district").val();
        var present_city = $("#present_city").val();
        var present_pin_code = $("#present_pin_code").val();
        var shop_name = $("#shop_name").val();
        var office_address = $("#office_address").val();
        var dataString = 'permanent_address=' + permanent_address + '&permanent_state=' + permanent_state + '&permanent_district=' + permanent_district + '&permanent_city=' + permanent_city + '&permanent_pin_code=' + permanent_pin_code + '&present_address=' + present_address + '&present_state=' + present_state + '&present_district=' + present_district + '&present_city=' + present_city + '&present_pin_code=' + present_pin_code + '&shop_name=' + shop_name + '&office_address=' + office_address + '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/update-verify-profile')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    swal("Success", msg.message, "success");
                    setTimeout(function () { location.reload(1); }, 5000);
                } else if(msg.status == 'validation_error'){
                    $("#permanent_address_errors").text(msg.errors.permanent_address);
                    $("#permanent_state_errors").text(msg.errors.permanent_state);
                    $("#permanent_district_errors").text(msg.errors.permanent_district);
                    $("#permanent_city_errors").text(msg.errors.permanent_city);
                    $("#permanent_pin_code_errors").text(msg.errors.permanent_pin_code);
                    $("#present_address_errors").text(msg.errors.present_address);
                    $("#present_state_errors").text(msg.errors.present_state);
                    $("#present_district_errors").text(msg.errors.present_district);
                    $("#present_city_errors").text(msg.errors.present_city);
                    $("#present_pin_code_errors").text(msg.errors.present_pin_code);
                    $("#shop_name_errors").text(msg.errors.shop_name);
                    $("#office_address_errors").text(msg.errors.office_address);
                }else{
                    swal("Faild", msg.message, "error");
                }
            }
        });
    }
    
    function verify_mobile() {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var mobile_number = $("#mobile_number").val();
        var dataString = 'mobile_number=' + mobile_number +  '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/verify-mobile')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    $("#verify_mobile_number").val(msg.mobile_number);
                    $("#profile_verify_otp_model").modal('show');
                }else{
                    swal("Faild", msg.message, "error");
                }
            }
        });
    }

    function activate_profile() {
        $("#activate_profile_btn").hide();
        $("#activate_profile_btn_loader").show();
        var token = $("input[name=_token]").val();
        var mobile_number = $("#verify_mobile_number").val();
        var verify_otp = $("#verify_otp").val();
        var dataString = 'mobile_number=' + mobile_number + '&otp=' + verify_otp +  '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/verify-mobile-otp')}}",
            data: dataString,
            success: function (msg) {
                //$(".loader").hide();
                $("#activate_profile_btn").show();
                $("#activate_profile_btn_loader").hide();
                if (msg.status == 'success') {
                    swal("Success", msg.message, "success");
                    setTimeout(function () { location.reload(1); }, 3000);
                } else if(msg.status == 'validation_error'){
                    $("#verify_mobile_number_errors").text(msg.errors.mobile_number);
                    $("#verify_otp_errors").text(msg.errors.otp);
                }else{
                    swal("Faild", msg.message, "error");
                }
            }
    });
    }
</script>

@if(Auth::User()->mobile_verified == 0)
<div class="alert alert-danger" role="alert">
    <button aria-label="Close" class="close" data-dismiss="alert" type="button">
        <span aria-hidden="true">&times;</span>
    </button>
    <strong>Alert : </strong> Kindly verify your mobile number with otp and activate account,  also check your profile details or change your password
</div>
@endif

<div class="main-content-body">
    <div class="row row-sm">
        <!-- Col -->
        <div class="col-lg-5">

            <div class="card custom-card">
                <div class="card-body text-center">

                    <div class="user-lock text-center">
                        @if(Auth::User()->member->profile_photo)
                            <img alt="avatar" class="rounded-circle" src="{{Auth::User()->member->profile_photo}}">
                        @else
                            <img alt="avatar" class="rounded-circle" src="{{url('assets/img/profile-pic.jpg')}}">
                        @endif

                    </div>
                    <h5 class="mb-1 mt-3 card-title">{{ Auth::User()->name }} {{ Auth::User()->last_name }}</h5>
                    <p class="mb-2 mt-1 tx-inverse">{{ Auth::User()->role->role_title }}</p>
                    <p class="text-muted text-center mt-1">Company Name : {{ Auth::User()->company->company_name }}</p>


                    <div class="mt-2 user-info btn-list">
                        <a class="btn btn-outline-light btn-block" href="#"><i class="fe fe-mail mr-2"></i><span> {{ Auth::User()->email }}</span></a>
                        <a class="btn btn-outline-light btn-block" href="#"><i class="fe fe-phone mr-2"></i><span> {{ Auth::User()->mobile }}</span></a>
                        <a class="btn btn-outline-light  btn-block" href="#"><i class="far fa-clock"></i> <span> {{ Auth::User()->created_at }}</span></a>


                    </div>
                </div>
            </div>

            <div class="card mg-b-20">
                <div class="card-body">
                    <div class="mb-4 main-content-label">Change Password</div>
                    <hr>
                    <div class="form-horizontal">


                        <div class="form-group ">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Old Password</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="password" class="form-control"  placeholder="Old Password" id="old_password">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="old_password_errors"></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="form-group ">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">New Password</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="password" class="form-control"  placeholder="New Password" id="new_password">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="new_password_errors"></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="form-group ">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Confirm Password</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="password" class="form-control"  placeholder="Confirm Password" id="confirm_password">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="confirm_password_errors"></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-danger btn-block" onclick="change_password()">Change Password</button>


                    </div>
                </div>
            </div>
            <div class="card mg-b-20">
                <div class="card-body">
                    <div class="main-content-label tx-13 mg-b-25">
                        Kyc Details
                    </div>
                    <hr>

                    <div class="form-horizontal">

                        @if(Auth::User()->member->kyc_remark)
                            <p class="text-muted text-center mt-1">
                            <div class="alert alert-danger mg-b-0" role="alert">
                                <strong>Rmark! </strong> {{ Auth::User()->member->kyc_remark }}
                            </div>
                            </p>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if(Session::has('success'))
                            <p class="alert alert-success">{{ Session::get('success') }}</p>
                        @endif


                        @if(Session::has('failure'))
                            <p class="alert alert-danger">{{ Session::get('failure') }}</p>
                        @endif

                            @if(Auth::User()->member->kyc_status == 1)

                            @else
                        <form role="form" action="{{url('agent/update-profile-photo')}}" method="post" enctype="multipart/form-data">
                            {!! csrf_field() !!}
                            <div class="form-group ">
                                <div class="row">

                                    <div class="col-md-3">
                                        <label class="form-label">Profile Photo</label>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="input-group">
                                            <input class="form-control"  type="file" name="profile_photo">
                                            <span class="input-group-btn">
                                                <button class="btn btn-danger" type="submit">Upload</button></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <form role="form" action="{{url('agent/update-shop-photo')}}" method="post" enctype="multipart/form-data">
                            {!! csrf_field() !!}
                            <div class="form-group ">
                                <div class="row">

                                    <div class="col-md-3">
                                        <label class="form-label">Shop Photo</label>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="input-group">
                                            <input class="form-control"  type="file" name="shop_photo">
                                            <span class="input-group-btn">
                                                <button class="btn btn-danger" type="submit">Upload</button></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <form role="form" action="{{url('agent/update-gst-regisration-photo')}}" method="post" enctype="multipart/form-data">
                            {!! csrf_field() !!}
                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Gst Registration</label>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="input-group">
                                            <input class="form-control"  type="file" name="gst_regisration_photo">
                                            <span class="input-group-btn">
                                                <button class="btn btn-danger" type="submit">Upload</button></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>


                        <form role="form" action="{{url('agent/update-pancard-photo')}}" method="post" enctype="multipart/form-data">
                            {!! csrf_field() !!}
                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Pancard</label>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="input-group">
                                            <input class="form-control"  type="file" name="pancard_photo">
                                            <span class="input-group-btn">
                                                <button class="btn btn-danger" type="submit">Upload</button></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>


                        <form role="form" action="{{url('agent/cancel-cheque-photo')}}" method="post" enctype="multipart/form-data">
                            {!! csrf_field() !!}
                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Cancel Cheque</label>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="input-group">
                                            <input class="form-control"  type="file" name="cancel_cheque">
                                            <span class="input-group-btn">
                                                <button class="btn btn-danger" type="submit">Upload</button></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>


                        <form role="form" action="{{url('agent/address-proof-photo')}}" method="post" enctype="multipart/form-data">
                            {!! csrf_field() !!}
                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Address Proof</label>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="input-group">
                                            <input class="form-control"  type="file" name="address_proof">
                                            <span class="input-group-btn">
                                                <button class="btn btn-danger" type="submit">Upload</button></span>
                                        </div>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required">Address Proof Front And Back</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </form>
                        @endif




                    </div>
                </div>
            </div>
        </div>
        <!-- /Col -->

        <!-- Col -->
        <div class="col-lg-7">
            <div class="card">
                <div class="card-body">
                    <div class="mb-4 main-content-label">Personal Information</div>
                    <hr>
                    <form class="form-horizontal">
                        <div class="form-group ">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Member Type</label>
                                </div>
                                <div class="col-md-9">
                                    <select class="form-control select2" disabled>
                                        @foreach(App\Role::where('status_id', 1)->get() as $value)
                                            <option value="{{ $value->id }}" @if(Auth::User()->role_id == $value->id) selected="selected" @endif >{{ $value->role_title }}</option>
                                        @endforeach

                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-4 main-content-label">Name</div>
                        <hr>

                        <div class="form-group ">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">First Name</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" class="form-control"  placeholder="First Name" value="{{ Auth::User()->name }}" disabled>
                                </div>
                            </div>
                        </div>
                        <div class="form-group ">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">last Name</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" class="form-control"  placeholder="Last Name" value="{{ Auth::User()->last_name }}" disabled>
                                </div>
                            </div>
                        </div>
                        <div class="form-group ">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Email Address</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="email" class="form-control"  placeholder="Email Address" value="{{ Auth::User()->email }}" disabled>
                                </div>
                            </div>
                        </div>
                        <div class="form-group ">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Mobile Number</label>
                                </div>
                                <div class="col-md-9">
                                    <div class="input-group">
                                        <input type="number" class="form-control"  placeholder="Mobile Number" id="mobile_number" value="{{ Auth::User()->mobile }}" disabled>

                                        @if(Auth::User()->mobile_verified == 0)
                                        <span class="input-group-btn">
                                                <button class="btn btn-warning" type="button" onclick="verify_mobile()">Verify Mobile</button>
                                        </span>
                                            @endif

                                    </div>
                                    @if(Auth::User()->mobile_verified == 0)
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required">kindly verify mobile number</li>
                                    </ul>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="mb-4 main-content-label">permanent address </div>
                        <hr>

                        <div class="form-group ">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Address</label>
                                </div>
                                <div class="col-md-9">
                                    <textarea class="form-control" id="permanent_address"  rows="2"  placeholder="Office Address" >{{ Auth::User()->member->permanent_address }}</textarea>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="permanent_address_errors"></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                 <div class="form-group ">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">State</label>
                                </div>
                                <div class="col-md-9">
                                    <select class="form-control select2" id="permanent_state" onchange="get_permanent_distric(this)">
                                        @foreach(App\State::where('status_id', 1)->get() as $value)
                                            <option value="{{$value->id }}" @if(Auth::User()->member->permanent_state == $value->id) selected="selected" @endif>{{ $value->name }}</option>
                                        @endforeach
                                            <ul class="parsley-errors-list filled">
                                                <li class="parsley-required" id="permanent_state_errors"></li>
                                            </ul>

                                    </select>
                                </div>
                            </div>
                        </div>


                        <div class="form-group ">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">District</label>
                                </div>
                                <div class="col-md-9">
                                    <select class="form-control select2" id="permanent_district" >
                                        @foreach(App\District::where('status_id', 1)->get() as $value)
                                            <option value="{{$value->id }}" @if(Auth::User()->member->permanent_district == $value->id) selected="selected" @endif>{{ $value->district_name }}</option>
                                        @endforeach
                                            <ul class="parsley-errors-list filled">
                                                <li class="parsley-required" id="permanent_district_errors"></li>
                                            </ul>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group ">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">City</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" id="permanent_city"  placeholder="City" value="{{ Auth::User()->member->permanent_city }}">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="permanent_city_errors"></li>
                                    </ul>
                                </div>
                            </div>
                        </div>



                        <div class="form-group ">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Pin Code</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="number" class="form-control" id="permanent_pin_code"  placeholder="Pin Code" value="{{ Auth::User()->member->permanent_pin_code }}">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="permanent_pin_code_errors"></li>
                                    </ul>
                                </div>
                            </div>
                        </div>



                        <div class="mb-4 main-content-label">Present  address</div>
                        <hr>


                        <div class="form-group ">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Shop Name</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" id="shop_name"  placeholder="Shope Name" value="{{ Auth::User()->member->shop_name }}">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="shop_name_errors"></li>
                                    </ul>
                                </div>
                            </div>
                        </div>


                        <div class="form-group ">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Office Address</label>
                                </div>
                                <div class="col-md-9">
                                    <textarea class="form-control" id="office_address" rows="2"  placeholder="Office Address">{{ Auth::User()->member->office_address }}</textarea>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="office_address_errors"></li>
                                    </ul>
                                </div>
                            </div>
                        </div>


                    </form>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-danger waves-effect waves-light" onclick="update_profile()">Update Profile</button>
                </div>
            </div>
        </div>
        <!-- /Col -->
    </div>


</div>
<!-- /row -->
</div>
<!-- /container -->
</div>
<!-- /main-content -->


<div class="modal  show" id="profile_verify_otp_model"data-toggle="modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Activate Profile</h6><button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="form-body">



                    <div class="row">



                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="name">Mobile Number</label>
                                <input type="text" id="verify_mobile_number" class="form-control" disabled>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="verify_mobile_number_errors"></li>
                                </ul>

                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="name">OTP</label>
                                <input type="password" id="verify_otp" class="form-control" placeholder="Enter OTP">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="verify_otp_errors"></li>
                                </ul>

                            </div>
                        </div>

                    </div>
                </div>

                <div class="alert alert-outline-danger" role="alert">
                    <strong> OTP sent to your mobile number</strong>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn ripple btn-primary" type="button" id="activate_profile_btn" onclick="activate_profile()">Activate Now</button>
                <button class="btn btn-primary" type="button"  id="activate_profile_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

