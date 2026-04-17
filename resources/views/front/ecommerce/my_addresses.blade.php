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

        function get_view_distric() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var state_id = $("#view_state_id").val();
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
                        $("#view_district_id").html(html);
                    }else{
                        alert(msg.message);
                    }
                }
            });
        }

        function save_now() {
            $("#save_btn").hide();
            $("#save_btn_loader").show();
            var token = $("input[name=_token]").val();
            var name = $("#name").val();
            var mobile_number = $("#mobile_number").val();
            var email = $("#email").val();
            var state_id = $("#state_id").val();
            var district_id = $("#district_id").val();
            var city = $("#city").val();
            var pin_code = $("#pin_code").val();
            var address = $("#address").val();
            var dataString = 'name=' + name + '&mobile_number=' + mobile_number + '&email=' + email + '&state_id=' + state_id + '&district_id=' + district_id + '&city=' + city + '&pin_code=' + pin_code + '&address=' + address + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/ecommerce/save-delivery-addresses')}}",
                data: dataString,
                success: function (msg) {
                    $("#save_btn").show();
                    $("#save_btn_loader").hide();
                    if (msg.status == 'success') {
                        alert(msg.message);
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#name_errors").text(msg.errors.name);
                        $("#mobile_number_errors").text(msg.errors.mobile_number);
                        $("#email_errors").text(msg.errors.email);
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

        function view_delivery_address(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/ecommerce/view-delivery-addresses')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        $("#view_id").val(msg.details.id);
                        $("#view_name").val(msg.details.name);
                        $("#view_address").val(msg.details.address);
                        $("#view_city").val(msg.details.city);
                        $("#view_state_id").val(msg.details.state_id);
                        $("#view_district_id").val(msg.details.district_id);
                        $("#view_pin_code").val(msg.details.pin_code);
                        $("#view_mobile_number").val(msg.details.mobile_number);
                        $("#view_email").val(msg.details.email);
                        $("#view_delivery_addresses_model").modal('show');
                    }else{
                        alert(msg.message);
                    }
                }
            });
        }

        function update_now() {
            $("#update_btn").hide();
            $("#update_btn_loader").show();
            var token = $("input[name=_token]").val();
            var id = $("#view_id").val();
            var name = $("#view_name").val();
            var address = $("#view_address").val();
            var city = $("#view_city").val();
            var state_id = $("#view_state_id").val();
            var district_id = $("#view_district_id").val();
            var pin_code = $("#view_pin_code").val();
            var mobile_number = $("#view_mobile_number").val();
            var email = $("#view_email").val();
            var dataString = 'id=' + id + '&name=' + name + '&address=' + address + '&city=' + city + '&state_id=' + state_id + '&district_id=' + district_id + '&pin_code=' + pin_code + '&mobile_number=' + mobile_number + '&email=' + email + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/ecommerce/update-delivery-addresses')}}",
                data: dataString,
                success: function (msg) {
                    $("#update_btn").show();
                    $("#update_btn_loader").hide();
                    if (msg.status == 'success') {
                        alert(msg.message);
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#name_errors").text(msg.errors.name);
                        $("#mobile_number_errors").text(msg.errors.mobile_number);
                        $("#email_errors").text(msg.errors.email);
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
                            <a href="#" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#add_delivery_addresses_model">Add Addresses</a>
                            <hr>
                        </div>

                        <div class="table-responsive">
                            <table class="table cart_summary">
                                <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Mobile</th>
                                    <th>Email</th>
                                    <th>State</th>
                                    <th>City</th>
                                    <th>Pincode</th>
                                    <th>Address</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($deliveryaddresses as $value)
                                    <tr>
                                        <td>{{ $value->name }}</td>
                                        <td>{{ $value->mobile_number }}</td>
                                        <td>{{ $value->email }}</td>
                                        <td>{{ $value->state->name }}</td>
                                        <td>{{ $value->city }}</td>
                                        <td>{{ $value->pin_code }}</td>
                                        <td>{{ $value->address }}</td>
                                        <td><button class="btn btn-success btn-sm" type="button" onclick="view_delivery_address({{ $value->id }})">Update</button></td>
                                    </tr>
                                @endforeach
                                </tbody>

                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{--add delivery addresses--}}

    <div class="modal fade" id="add_delivery_addresses_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Add Delivery Addresses</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">





                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label for="name">Full Name</label>
                                    <input type="text" id="name" class="form-control" placeholder="Full Name">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="name_errors" style="color: red;"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label for="name">Mobile Number</label>
                                    <input type="text" id="mobile_number" class="form-control" placeholder="Mobile Number">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="mobile_number_errors" style="color: red;"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label for="name">Email Address</label>
                                    <input type="email" id="email" class="form-control" placeholder="Email Address">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="email_errors" style="color: red;"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">State</label>
                                    <select class="form-control" id="state_id" onchange="get_distric(this)">
                                        <option value="">Select State</option>
                                        @foreach($states as $value)
                                            <option value="{{ $value->id }}">{{ $value->name }}</option>
                                        @endforeach
                                    </select>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="state_id_errors" style="color: red;"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">District</label>
                                    <select class="form-control" id="district_id">
                                        <option value="">Select District</option>
                                    </select>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="district_id_errors" style="color: red;"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">City</label>
                                    <input type="text" id="city" class="form-control" placeholder="City">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="city_errors" style="color: red;"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Pin Code</label>
                                    <input type="text" id="pin_code" class="form-control" placeholder="Pin Code">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="pin_code_errors" style="color: red;"></li>
                                    </ul>
                                </div>
                            </div>


                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Full Address</label>
                                    <textarea class="form-control" placeholder="Enter Full Address.." id="address"></textarea>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="address_errors" style="color: red;"></li>
                                    </ul>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
                <div class="modal-footer">

                    <button type="button" class="btn btn-primary" id="save_btn" onclick="save_now()">Save Now</button>
                    <button class="btn btn-primary" type="button"  id="save_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{--close add delivery addresses--}}



    {{--update delivery addresses--}}

    <div class="modal fade" id="view_delivery_addresses_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Update Delivery Addresses</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">

                            <input type="hidden" id="view_id">

                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label for="name">Full Name</label>
                                    <input type="text" id="view_name" class="form-control" placeholder="Full Name">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_name_errors" style="color: red;"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label for="name">Mobile Number</label>
                                    <input type="text" id="view_mobile_number" class="form-control" placeholder="Mobile Number">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_mobile_number_errors" style="color: red;"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label for="name">Email Address</label>
                                    <input type="email" id="view_email" class="form-control" placeholder="Email Address">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_email_errors" style="color: red;"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">State</label>
                                    <select class="form-control" id="view_state_id" onchange="get_view_distric(this)">
                                        <option value="">Select State</option>
                                        @foreach($states as $value)
                                            <option value="{{ $value->id }}">{{ $value->name }}</option>
                                        @endforeach
                                    </select>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_state_id_errors" style="color: red;"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">District</label>
                                    <select class="form-control" id="view_district_id">
                                        @foreach($districts as $value)
                                            <option value="{{ $value->id }}">{{ $value->district_name }}</option>
                                        @endforeach
                                    </select>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_district_id_errors" style="color: red;"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">City</label>
                                    <input type="text" id="view_city" class="form-control" placeholder="City">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_city_errors" style="color: red;"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Pin Code</label>
                                    <input type="text" id="view_pin_code" class="form-control" placeholder="Pin Code">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_pin_code_errors" style="color: red;"></li>
                                    </ul>
                                </div>
                            </div>


                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Full Address</label>
                                    <textarea class="form-control" placeholder="Enter Full Address.." id="view_address"></textarea>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_address_errors" style="color: red;"></li>
                                    </ul>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
                <div class="modal-footer">

                    <button type="button" class="btn btn-primary" id="update_btn" onclick="update_now()">Update Now</button>
                    <button class="btn btn-primary" type="button"  id="update_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{--close update delivery addresses--}}
@endsection