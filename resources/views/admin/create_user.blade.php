@extends('admin.layout.header')
@section('content')
    
    
    <script type="text/javascript">
        $(document).ready(function () {
            $("#role_id").select2();
            $("#scheme_id").select2();
            $("#company_id").select2();
            $("#permanent_state").select2();
            $("#permanent_district").select2();
            $("#present_state").select2();
            $("#present_district").select2();
            $("#gst_type").select2();
            $("#user_gst_type").select2();
            $("#active_services").select2();
        });



        function get_permanent_distric() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var state_id = $("#state_id").val();
            var dataString = 'state_id=' + state_id +  '&_token=' + token;
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
                        $("#district_id").html(html);

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
                        $("#present_district").html(html);

                    }else{
                        alert(msg.message);
                    }
                }
            });

        }
        function create_users() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var name = $("#name").val();
            var last_name = $("#last_name").val();
            var email = $("#email").val();
            var mobile = $("#mobile").val();
            var role_id = $("#role_id").val();
            var scheme_id = $("#scheme_id").val();
            var shop_name = $("#shop_name").val();
            var office_address = $("#office_address").val();
            var lock_amount = $("#lock_amount").val();
            var company_id = $("#company_id").val();

            var address = $("#address").val();
            var city = $("#city").val();
            var pin_code = $("#pin_code").val();
            var state_id = $("#state_id").val();
            var district_id = $("#district_id").val();

            var gst_type = $("#gst_type").val();
            var pan_number = $("#pan_number").val();
            var gst_number = $("#gst_number").val();
            var user_gst_type = $("#user_gst_type").val();
            var active_services = $("#active_services").val();
            var dataString = 'name=' + name + '&last_name=' + last_name + '&email=' + email + '&mobile=' + mobile + '&role_id=' + role_id + '&scheme_id=' + scheme_id + '&shop_name=' + shop_name + '&office_address=' + office_address + '&lock_amount=' + lock_amount + '&company_id=' + company_id + '&address=' + address + '&city=' + city + '&state_id=' + state_id + '&district_id=' + district_id + '&pin_code=' + pin_code +  '&gst_type=' + gst_type + '&pan_number=' + pan_number + '&gst_number=' + gst_number + '&user_gst_type=' + user_gst_type + '&active_services=' + active_services + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/store-members')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#name_errors").text(msg.errors.name);
                        $("#last_name_errors").text(msg.errors.last_name);
                        $("#email_errors").text(msg.errors.email);
                        $("#mobile_errors").text(msg.errors.mobile);
                        $("#role_id_errors").text(msg.errors.role_id);
                        $("#scheme_id_errors").text(msg.errors.scheme_id);
                        $("#shop_name_errors").text(msg.errors.shop_name);
                        $("#office_address_errors").text(msg.errors.office_address);
                        $("#address_errors").text(msg.errors.address);
                        $("#city_errors").text(msg.errors.city);
                        $("#state_id_errors").text(msg.errors.state_id);
                        $("#district_id_errors").text(msg.errors.district_id);
                        $("#pin_code_errors").text(msg.errors.pin_code);
                        $("#pan_number_errors").text(msg.errors.pan_number);
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }


        function create_scheme(url) {
            popup = window.open(url, "{{url('')}}", "height=530,width=1000,top=100,left=100");
            form.setAttribute('target', "{{url('')}}");
        }

        function refresh_scheme() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var state_id = $("#present_state").val();
            var dataString = 'state_id=' + state_id +  '&_token=' + token;
            $.ajax({
                type: "post",
                url: "{{url('admin/refresh-scheme')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        var scheme = msg.scheme;
                        var html = "";
                        for (var key in scheme) {
                            html += '<option value="' + scheme[key].scheme_id + '">' + scheme[key].scheme_name + ' </option>';
                        }
                        $("#scheme_id").html(html);

                    }else{
                        alert(msg.message);
                    }
                }
            });
        }


    </script>

    <div class="main-content-body">
        {{--perssinal details--}}
        <div class="row row-sm">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">Basic details</h4>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">

                        <div class="form-body">
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">First Name</label>
                                        <input type="text" id="name" class="form-control" placeholder="First Name">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="name_errors"></li>
                                        </ul>

                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Last Name </label>
                                        <input type="text" id="last_name" class="form-control" placeholder="Last Name">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="last_name_errors"></li>
                                        </ul>
                                    </div>
                                </div>


                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Email Address</label>
                                        <input type="text" id="email" class="form-control" placeholder="Email Address">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="email_errors"></li>
                                        </ul>
                                    </div>
                                </div>


                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Mobile Number</label>
                                        <input type="text" id="mobile" class="form-control" placeholder="Mobile Number">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="mobile_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Member Type</label>
                                        <select class="form-control select2" id="role_id">
                                            @foreach($roledetails as $value)
                                                <option value="{{ $value->id }}">{{ $value->role_title }}</option>
                                                @endforeach
                                        </select>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="role_id_errors"></li>
                                        </ul>
                                    </div>
                                </div>


                                @if(Auth::User()->role_id == 1)
                                    <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Package</label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <button class="btn btn-success btn-sm" type="button" onclick="refresh_scheme()">
                                                    <span class="input-group-btn"><i class="fas fa-sync-alt"></i></span>
                                                </button>
                                            </div>

                                            <select class="form-control select2" id="scheme_id">
                                                <option value="">Select Package</option>
                                                @foreach($schemes as $value)
                                                    <option value="{{ $value->id }}">{{ $value->scheme_name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="input-group-append">
                                                <button class="btn btn-danger btn-sm" type="button" onclick="create_scheme('{{url('admin/package-settings')}}')">
                                                    <span class="input-group-btn"><i class="fas fa-plus-circle"></i> Create</span>
                                                </button>

                                            </div>
                                            <ul class="parsley-errors-list filled">
                                                <li class="parsley-required" id="scheme_id_errors"></li>
                                            </ul>
                                        </div><!-- input-group -->

                                    </div>
                                </div>
                                @endif



                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Shop Name</label>
                                        <input type="text" id="shop_name" class="form-control" placeholder="Shop Name">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="shop_name_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Office Address</label>
                                        <textarea class="form-control" id="office_address" placeholder="Office Address"></textarea>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="office_address_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Lock Amount</label>
                                        <input type="text" id="lock_amount" class="form-control" placeholder="Lock Amount">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="lock_amount_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Pan Number</label>
                                        <input type="text" id="pan_number"  class="form-control" placeholder="Pan Number">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="pan_number_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">GST Number</label>
                                        <input type="text" id="gst_number" class="form-control" placeholder="GST Number">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="gst_number_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                @if(Auth::User()->role_id <= 2 && Auth::User()->company->invoice == 1)
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="name">GST Invoce</label>
                                            <select class="form-control select2" id="gst_type" style="width: 100%;">
                                                <option value="1">Yes</option>
                                                <option value="0">No</option>
                                            </select>
                                            <ul class="parsley-errors-list filled">
                                                <li class="parsley-required" id="gst_number_errors"></li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="name">GST Type</label>
                                            <select class="form-control select2" id="user_gst_type" style="width: 100%;">
                                                <option value="1">I GST</option>
                                                <option value="2">CGST</option>
                                            </select>
                                        </div>
                                    </div>
                                @endif


                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <!--/div-->
        </div>
        {{--perssinal details clase--}}



        {{--Permanent details--}}

        <div class="row row-sm">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">Permanent details </h4>

                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">

                        <div class="form-body">
                            <div class="row">

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Address</label>
                                        <input class="form-control" id="address" placeholder="Address">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="address_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">City</label>
                                        <input type="text" id="city" class="form-control" placeholder="City">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="city_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Pincode</label>
                                        <input type="text" id="pin_code" class="form-control" placeholder="Pincode">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="pin_code_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">State</label>
                                        <select class="form-control select2" id="state_id" onchange="get_permanent_distric(this)">
                                            <option value="">Select State</option>
                                            @foreach($state as $value)
                                                <option value="{{ $value->id }}">{{ $value->name }}</option>
                                            @endforeach
                                        </select>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="state_id_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">District</label>
                                        <select class="form-control select2" id="district_id">
                                            <option value="">Select District</option>
                                            @foreach($district as $value)
                                                <option value="{{ $value->id }}">{{ $value->district_name }}</option>
                                            @endforeach
                                        </select>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="district_id_errors"></li>
                                        </ul>
                                    </div>
                                </div>



                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <!--/div-->
        </div>
        {{--Permanent details close--}}



        {{--service detail--}}
        <div class="row row-sm">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">Service</h4>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div class="form-body">
                            <div class="row">

                                @if(Auth::User()->role_id == 1)
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="name">Active Service</label>
                                        <select class="form-control select2" id="active_services" style="width: 100%" multiple>
                                            @foreach($services as $value)
                                                <option value="{{ $value->id }}">{{ $value->service_name }}</option>
                                            @endforeach
                                        </select>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="active_services_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                                @endif


                            </div>

                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-danger waves-effect waves-light" onclick="create_users()">Save Details</button>
                    </div>
                </div>
            </div>
            <!--/div-->

        </div>
        {{--service detail close--}}



    </div>
    </div>
    </div>




@endsection