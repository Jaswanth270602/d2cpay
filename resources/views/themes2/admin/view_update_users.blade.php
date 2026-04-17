@extends('themes2.admin.layout.header')
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
            $("#parent_id").select2();
            $("#gst_type").select2();
            $("#user_gst_type").select2();
            $("#day_book").select2();
            $("#monthly_statement").select2();

        });

        function get_permanent_distric() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var state_id = $("#state_id").val();
            var dataString = 'state_id=' + state_id + '&_token=' + token;
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

                    } else {
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
            var status_id = $("#status_id").val();

            var address = $("#address").val();
            var city = $("#city").val();
            var state_id = $("#state_id").val();
            var district_id = $("#district_id").val();
            var pin_code = $("#pin_code").val();


            var recharge = $("#recharge").val();
            var money = $("#money").val();
            var money_two = $("#money_two").val();
            var aeps = $("#aeps").val();
            var payout = $("#payout").val();
            var pancard = $("#pancard").val();
            var ecommerce = $("#ecommerce").val();
            var giftcard = $("#giftcard").val();
            var seller = $("#seller").val();
            var day_book = $("#day_book").val();
            var monthly_statement = $("#monthly_statement").val();

            var user_id = $("#user_id").val();
            var parent_id = $("#parent_id").val();
            var gst_type = $("#gst_type").val();
            var pan_number = $("#pan_number").val();
            var gst_number = $("#gst_number").val();
            var user_gst_type = $("#user_gst_type").val();
            var cashfree = $("#cashfree").val();
            var dataString = 'name=' + name + '&last_name=' + last_name + '&email=' + email + '&mobile=' + mobile + '&role_id=' + role_id + '&scheme_id=' + scheme_id + '&shop_name=' + shop_name + '&office_address=' + office_address + '&lock_amount=' + lock_amount + '&company_id=' + company_id + '&address=' + address + '&city=' + city + '&state_id=' + state_id + '&district_id=' + district_id + '&pin_code=' + pin_code + '&recharge=' + recharge + '&money=' + money + '&money_two=' + money_two + '&aeps=' + aeps + '&payout=' + payout + '&pancard=' + pancard + '&ecommerce=' + ecommerce + '&user_id=' + user_id + '&parent_id=' + parent_id + '&giftcard=' + giftcard + '&seller=' + seller + '&gst_type=' + gst_type + '&pan_number=' + pan_number + '&gst_number=' + gst_number + '&user_gst_type=' + user_gst_type + '&day_book=' + day_book + '&monthly_statement=' + monthly_statement + '&status_id=' + status_id + '&cashfree=' + cashfree + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/update-members')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () {
                            location.reload(1);
                        }, 3000);
                    } else if (msg.status == 'validation_error') {
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
                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function create_pancard_id() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var user_id = $("#user_id").val();
            var dataString = 'user_id=' + user_id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/create-pancard-id')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () {
                            location.reload(1);
                        }, 3000);
                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }
    </script>
    <!--  Content Area Starts  -->
    <div id="content" class="main-content">

        <!-- Main Body Starts -->
    @include('themes2.admin.layout.breadcrumb')
    <!-- Main Body Starts -->
        <div class="layout-px-spacing">
            <div class="row layout-top-spacing">
                <!-- REVENUE ENDS-->



                {{--Start Basic Details Label--}}
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
                    <div class="widget dashboard-table">
                        <div class="widget-heading">
                            <h5 class=""> Basic Details </h5>
                        </div>
                        <hr>
                        <div class="widget-content">
                            <div class="card-body">
                                <div class="form-body">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">First Name</label>
                                                <input type="text" id="name" value="{{ $name }}" class="form-control" placeholder="First Name">
                                                <span class="invalid-feedback d-block" id="name_errors"></span>
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">Last Name </label>
                                                <input type="text" id="last_name" value="{{ $last_name }}" class="form-control" placeholder="Last Name">
                                                <span class="invalid-feedback d-block" id="last_name_errors"></span>
                                            </div>
                                        </div>


                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">Email Address</label>
                                                <input type="text" id="email" value="{{ $email }}" class="form-control" placeholder="Email Address">
                                                <span class="invalid-feedback d-block" id="email_errors"></span>
                                            </div>
                                        </div>


                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">Mobile Number</label>
                                                <input type="text" id="mobile" value="{{ $mobile }}" class="form-control" placeholder="Mobile Number">
                                                <span class="invalid-feedback d-block" id="mobile_errors"></span>
                                            </div>
                                        </div>


                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">Member Type</label>
                                                <select class="form-control select2" id="role_id">
                                                    @foreach($roledetails as $value)
                                                        <option value="{{ $value->id }}" @if($role_id== $value->id) selected="selected"@endif>{{ $value->role_title }}</option>
                                                    @endforeach
                                                </select>
                                                <span class="invalid-feedback d-block" id="role_id_errors"></span>
                                            </div>
                                        </div>


                                        @if(Auth::User()->role_id == 1)
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label for="name">Package</label>
                                                    <select class="form-control select2" id="scheme_id">
                                                        <option value="">Select Package</option>
                                                        @foreach($schemes as $value)
                                                            <option value="{{ $value->id }}" @if($scheme_id== $value->id)selected="selected" @endif>{{ $value->scheme_name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <span class="invalid-feedback d-block" id="scheme_id_errors"></span>
                                                </div>
                                            </div>


                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label for="name">Parents</label>
                                                    <select class="form-control select2" id="parent_id">
                                                        @foreach($parents as $value)
                                                            <option value="{{ $value->id }}" @if($parent_id== $value->id)selected="selected" @endif>{{ $value->name }} {{ $value->last_name }}- {{$value->mobile }}</option>
                                                        @endforeach
                                                    </select>
                                                    <span class="invalid-feedback d-block" id="parent_id_errors"></span>
                                                </div>
                                            </div>

                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label for="name">Day Book Alert</label>
                                                    <select class="form-control select2" id="day_book" style="width: 100%;">
                                                        <option value="1" @if($day_book== 1) selected @endif>Enabled</option>
                                                        <option value="0" @if($day_book== 0) selected @endif>Disabled</option>
                                                    </select>
                                                    <span class="invalid-feedback d-block" id="day_book_errors"></span>
                                                </div>
                                            </div>

                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label for="name">Daily Statement Alert</label>
                                                    <select class="form-control select2" id="monthly_statement" style="width: 100%;">
                                                        <option value="1" @if($monthly_statement== 1) selected @endif>Enabled</option>
                                                        <option value="0" @if($monthly_statement== 0) selected @endif>Disabled</option>
                                                    </select>
                                                    <span class="invalid-feedback d-block" id="monthly_statement_errors"></span>
                                                </div>
                                            </div>
                                        @endif


                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">Shop Name</label>
                                                <input type="text" id="shop_name" value="{{ $shop_name }}" class="form-control" placeholder="Shop Name">
                                                <span class="invalid-feedback d-block" id="shop_name_errors"></span>
                                            </div>
                                        </div>


                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">Lock Amount</label>
                                                <input type="text" id="lock_amount" value="{{ $lock_amount }}" class="form-control" placeholder="Lock Amount">
                                                <span class="invalid-feedback d-block" id="lock_amount_errors"></span>
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">Pan Number</label>
                                                <input type="text" id="pan_number" value="{{ $pan_number }}" class="form-control" placeholder="Pan Number">
                                                <span class="invalid-feedback d-block" id="pan_number_errors"></span>
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">GST Number</label>
                                                <input type="text" id="gst_number" value="{{ $gst_number }}" class="form-control" placeholder="GST Number">
                                                <span class="invalid-feedback d-block" id="gst_number_errors"></span>
                                            </div>
                                        </div>

                                        @if(Auth::User()->role_id <= 2 && Auth::User()->company->invoice == 1)
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label for="name">GST Invoce</label>
                                                    <select class="form-control select2" id="gst_type" style="width: 100%;">
                                                        <option value="1" @if($gst_type== 1) selected @endif>Yes</option>
                                                        <option value="0" @if($gst_type== 0) selected @endif>No</option>
                                                    </select>
                                                    <span class="invalid-feedback d-block" id="gst_number_errors"></span>
                                                </div>
                                            </div>

                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label for="name">GST Type</label>
                                                    <select class="form-control select2" id="user_gst_type" style="width: 100%;">
                                                        <option value="">Select GST Type</option>
                                                        <option value="1" @if($user_gst_type== 1) selected @endif>I GST</option>
                                                        <option value="2" @if($user_gst_type== 2) selected @endif>CGST</option>
                                                    </select>
                                                </div>
                                            </div>
                                        @endif

                                        @if(Auth::User()->role_id == 1 && Auth::User()->company->pancard == 1)
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label for="name">Pan Username</label>
                                                    <input type="text" id="pan_username" value="{{ $pan_username }}" class="form-control" placeholder="Pan Username" readonly>
                                                </div>
                                            </div>

                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label for="name">Pan Password</label>
                                                    <input type="text" id="pan_password" value="{{ $pan_password }}" class="form-control" placeholder="Pan Password" readonly>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label for="name">Office Address</label>
                                                <textarea class="form-control" id="office_address" placeholder="Office Address">{{ $office_address }}</textarea>
                                                <span class="invalid-feedback d-block" id="office_address_errors"></span>
                                            </div>
                                        </div>


                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                {{--End Basic Details Label--}}

                {{--Start Permanent Details Label--}}
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
                    <div class="widget dashboard-table">
                        <div class="widget-heading">
                            <h5 class=""> Permanent Details </h5>
                        </div>
                        <hr>
                        <div class="widget-content">
                            <div class="card-body">
                                <div class="form-body">
                                    <div class="row">

                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">Address</label>
                                                <input class="form-control" id="address" placeholder="Address" value="{{ $address }}">
                                                <span class="invalid-feedback d-block" id="address_errors"></span>
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">City</label>
                                                <input type="text" id="city" value="{{ $city }}" class="form-control" placeholder="City">
                                                <span class="invalid-feedback d-block" id="city_errors"></span>
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">Pincode</label>
                                                <input type="text" id="pin_code" value="{{ $pin_code }}" class="form-control" placeholder="Pincode">
                                                <span class="invalid-feedback d-block" id="pin_code_errors"></span>
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">State</label>
                                                <select class="form-control select2" id="state_id" onchange="get_permanent_distric(this)">
                                                    <option value="">Select State</option>
                                                    @foreach($state as $value)
                                                        <option value="{{ $value->id }}"
                                                                @if($state_id== $value->id) selected="selected"
                                                                @endif>{{ $value->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <span class="invalid-feedback d-block" id="state_id_errors"></span>
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">District</label>
                                                <select class="form-control select2" id="district_id">
                                                    <option value="">Select District</option>
                                                    @foreach($permanentdistrict as $value)
                                                        <option value="{{ $value->id }}" @if($district_id== $value->id) selected="selected" @endif>{{ $value->district_name }}</option>
                                                    @endforeach
                                                </select>
                                                <span class="invalid-feedback d-block" id="district_id_errors"></span>
                                            </div>
                                        </div>


                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                {{--End Permanent Details Label--}}


                {{--Start Service Label--}}
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
                    <div class="widget dashboard-table">
                        <div class="widget-heading">
                            <h5 class=""> Service </h5>
                        </div>
                        <hr>
                        <div class="widget-content">
                            <div class="card-body">
                                <div class="form-body">
                                    <div class="row">

                                        @if(Auth::User()->company->recharge == 1 && Auth::User()->profile->recharge == 1)
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label for="name">Recharge Services</label>
                                                    <select class="form-control" id="recharge">
                                                        <option value="1" @if($recharge=='1') selected="selected" @endif>Active</option>
                                                        <option value="0" @if($recharge=='0') selected="selected" @endif>De Active</option>
                                                    </select>
                                                </div>
                                            </div>
                                        @endif


                                        @if(Auth::User()->company->money == 1 && Auth::User()->profile->money == 1)
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label for="name">Money Trasnfer</label>
                                                    <select class="form-control" id="money">
                                                        <option value="1" @if($money=='1') selected="selected" @endif>Active</option>
                                                        <option value="0" @if($money=='0') selected="selected" @endif>De Active</option>
                                                    </select>
                                                </div>
                                            </div>
                                        @endif


                                        @if(Auth::User()->company->money_two == 1 && Auth::User()->profile->money_two == 1)
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label for="name">Money Trasnfer 2</label>
                                                    <select class="form-control" id="money_two">
                                                        <option value="1" @if($money_two=='1') selected="selected" @endif>Active</option>
                                                        <option value="0" @if($money_two=='0') selected="selected" @endif>De Active</option>
                                                    </select>
                                                </div>
                                            </div>
                                        @endif

                                        @if(Auth::User()->company->aeps == 1 && Auth::User()->profile->aeps == 1)
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label for="name">Aeps Services</label>
                                                    <select class="form-control" id="aeps">
                                                        <option value="1" @if($aeps=='1') selected="selected" @endif>Active</option>
                                                        <option value="0" @if($aeps=='0') selected="selected" @endif>De Active</option>
                                                    </select>
                                                </div>
                                            </div>
                                        @endif

                                        @if(Auth::User()->company->payout == 1 && Auth::User()->profile->payout == 1)
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label for="name">Payout Services</label>
                                                    <select class="form-control" id="payout">
                                                        <option value="1" @if($payout=='1') selected="selected" @endif>Active</option>
                                                        <option value="0" @if($payout=='0') selected="selected" @endif>De Active</option>
                                                    </select>
                                                </div>
                                            </div>
                                        @endif

                                        @if(Auth::User()->company->pancard == 1 && Auth::User()->profile->pancard == 1)
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label for="name">Pancard Services</label>
                                                    <select class="form-control" id="pancard">
                                                        <option value="1" @if($pancard=='1') selected="selected" @endif>Active</option>
                                                        <option value="0" @if($pancard=='0') selected="selected" @endif>De Active</option>
                                                    </select>
                                                </div>
                                            </div>
                                        @endif

                                        @if(Auth::User()->company->ecommerce == 1 && Auth::User()->profile->ecommerce == 1)
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label for="name">Ecommerce Services</label>
                                                    <select class="form-control" id="ecommerce">
                                                        <option value="1" @if($ecommerce=='1') selected="selected" @endif>Active</option>
                                                        <option value="0" @if($ecommerce=='0') selected="selected" @endif>De Active</option>
                                                    </select>
                                                </div>
                                            </div>
                                        @endif

                                        @if(Auth::User()->company->ecommerce == 1 && Auth::User()->profile->seller == 1)
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label for="name">Ecommerce Seller</label>
                                                    <select class="form-control" id="seller">
                                                        <option value="1" @if($seller=='1') selected="selected" @endif>Active</option>
                                                        <option value="0" @if($seller=='0') selected="selected" @endif>De Active</option>
                                                    </select>
                                                </div>
                                            </div>
                                        @endif

                                        @if(Auth::User()->profile->giftcard == 1 && Auth::User()->company->giftcard == 1)
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label for="name">Gift Card</label>
                                                    <select class="form-control" id="giftcard">
                                                        <option value="1" @if($giftcard=='1') selected="selected" @endif>Active</option>
                                                        <option value="0" @if($giftcard== '0') selected="selected" @endif>De Active</option>
                                                    </select>
                                                </div>
                                            </div>
                                        @endif

                                        @if(Auth::User()->role_id == 1 && Auth::User()->company->cashfree == 1)
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label for="name">Cashfree Payment Gateway</label>
                                                    <select class="form-control" id="cashfree">
                                                        <option value="1" @if($cashfree=='1') selected="selected" @endif>Active</option>
                                                        <option value="0" @if($cashfree== '0') selected="selected" @endif>De Active</option>
                                                    </select>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">Member Status</label>
                                                <select class="form-control" id="status_id">
                                                    <option value="1" @if($status_id=='1') selected="selected" @endif>Active</option>
                                                    <option value="0" @if($status_id=='0') selected="selected" @endif>De Active</option>
                                                </select>
                                            </div>
                                        </div>


                                    </div>

                                </div>
                                <hr>
                                <div class="widget-footer text-right">
                                    <input type="hidden" value="{{ $user_id }}" id="user_id">
                                    <button type="reset" class="btn btn-primary mr-2" onclick="create_users()">Update</button>
                                    @if(Auth::User()->role_id == 1 && Auth::User()->company->pancard == 1)
                                        <button type="submit" class="btn btn-info waves-effect waves-light" onclick="create_pancard_id()">
                                            Create Pancard Id
                                        </button>
                                    @endif
                                    <a href="{{url()->previous()}}"  class="btn btn-outline-primary"> Back</a>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                {{--End Service Label--}}

            </div>
        </div>
        <!-- Main Body Ends -->


@endsection