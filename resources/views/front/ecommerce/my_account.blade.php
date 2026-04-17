@extends('front.ecommerce.header')
@section('content')
    <script type="text/javascript">

        function get_distric() {
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
        
        function update_profile() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var first_name = $("#first_name").val();
            var last_name = $("#last_name").val();
            var state_id = $("#state_id").val();
            var district_id = $("#district_id").val();
            var city = $("#city").val();
            var pin_code = $("#pin_code").val();
            var address = $("#address").val();
            var dataString = 'first_name=' + first_name + '&last_name=' + last_name + '&state_id=' + state_id + '&district_id=' + district_id + '&city=' + city + '&pin_code=' + pin_code + '&address=' + address + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('ecommerce/update-profile')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        alert(msg.message);
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#first_name_errors").text(msg.errors.first_name);
                        $("#last_name_errors").text(msg.errors.last_name);
                        $("#state_id_errors").text(msg.errors.state_id);
                        $("#district_id_errors").text(msg.errors.district_id);
                        $("#city_errors").text(msg.errors.city);
                        $("#pin_code_errors").text(msg.errors.pin_code);
                        $("#address_errors").text(msg.errors.address);
                    }else{
                        alert(msg.message);
                    }
                }
            });
        }
    </script>
    <section class="shopping_cart_page">
        <div class="container">
            <div class="row">

                @include('front.ecommerce.profile_left')

                <div class="col-lg-9 col-md-8 col-sm-7">
                    <div class="widget">
                        <div class="section-header">
                            <h5 class="heading-design-h5">
                                My Profile
                            </h5>
                        </div>
                        <form>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="control-label">First Name <span class="required">*</span></label>
                                        <input class="form-control" value="{{ Auth::User()->name }}" placeholder="First Name" type="text" id="first_name">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="first_name_errors" style="color: red;"></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="control-label">Last Name <span class="required">*</span></label>
                                        <input class="form-control" value="{{ Auth::User()->last_name }}" placeholder="Last Name" type="text" id="last_name">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="last_name_errors" style="color: red;"></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="control-label">Mobile Number <span class="required">*</span></label>
                                        <input class="form-control" value="{{ Auth::User()->mobile }}" placeholder="Mobile Number" type="number" disabled>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="control-label">Email Address <span class="required">*</span></label>
                                        <input class="form-control" value="{{ Auth::User()->email }}" placeholder="Email Address" type="email" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="control-label">State <span class="required">*</span></label>
                                        <select class="select2 form-control" id="state_id" onchange="get_distric(this)">
                                            <option value="">Select State</option>
                                            @foreach($states as $value)
                                                <option value="{{ $value->id }}" @if(Auth::User()->member->permanent_state == $value->id) selected @endif>{{ $value->name }}</option>
                                            @endforeach
                                        </select>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="state_id_errors" style="color: red;"></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="control-label">District <span class="required">*</span></label>
                                        <select class="select2 form-control" id="district_id">
                                            <option value="">Select District</option>
                                            @foreach($districts as $value)
                                                <option value="{{ $value->id }}" @if(Auth::User()->member->permanent_district == $value->id) selected @endif>{{ $value->district_name }}</option>
                                            @endforeach
                                        </select>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="district_id_errors" style="color: red;"></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="control-label">City <span class="required">*</span></label>
                                        <input class="form-control" value="{{ Auth::User()->member->permanent_city }}" placeholder="City" type="text" id="city">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="city_errors" style="color: red;"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="control-label">Pin Code <span class="required">*</span></label>
                                        <input class="form-control" value="{{ Auth::User()->member->permanent_pin_code }}" placeholder="Pin Code" type="number" id="pin_code">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="pin_code_errors" style="color: red;"></li>
                                        </ul>
                                    </div>
                                </div>

                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label class="control-label">Address <span class="required">*</span></label>
                                        <textarea class="form-control" id="address">{{ Auth::User()->member->office_address }}</textarea>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="address_errors" style="color: red;"></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 text-right">
                                    <button type="button" class="btn btn-outline-danger btn-lg"> Cencel </button>
                                    <button type="button" class="btn btn-outline-success btn-lg" onclick="update_profile()"> Save Changes </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>


@endsection