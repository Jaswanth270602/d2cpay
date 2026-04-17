@extends('agent.layout.header_ecommerce')
@section('content')
    
    <script type="text/javascript">

        $(document).ready(function () {
            $("#payment_methods").select2();
        });
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
                        swal("Success", msg.message, "success");
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
                        swal("Failed", msg.message, "error");
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
                        swal("Faild", msg.message, "error");
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
                        swal("Success", msg.message, "success");
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
                        swal("Failed", msg.message, "error");
                    }
                }
            });


        }
    </script>



    <div class="main-content-body">

        @if(Session::has('success_message'))
            <div class="alert alert-success">
                <a class="close" data-dismiss="alert">×</a>
                <strong>Alert </strong> {!!Session::get('success_message')!!}
            </div>
        @endif

        @if(Session::has('error_message'))
            <div class="alert alert-danger">
                <a class="close" data-dismiss="alert">×</a>
                <strong>Alert </strong> {!!Session::get('error_message')!!}
            </div>
        @endif

        <form action="{{url('agent/ecommerce/place-order')}}" method="post">
            {!! csrf_field() !!}
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">Delivery Addresses</h4>
                            <button type="button" class="btn btn-danger btn-sm" data-target="#add_delivery_addresses_model" data-toggle="modal">Add Addresses</button>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>

                    <div class="card-body">
                        <div class="product-details table-responsive text-nowrap">
                            @if ($errors->has('deliveryaddress'))
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required">{{ $errors->first('deliveryaddress') }}</li>
                                </ul>
                            @endif
                        <table class="table table-bordered table-hover mb-0 text-nowrap">
                            <thead>
                            <tr>
                                <th>Select</th>
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
                                <td><input type="radio" id="address_{{$value->id}}" name="deliveryaddress" value="{{$value->id}}"></td>
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


        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="product-details table-responsive text-nowrap">
                            <table class="table table-bordered table-hover mb-0 text-nowrap">
                                <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Unit Price</th>
                                    <th>Quantity</th>
                                    <th>Discount</th>
                                    <th>Sub Total</th>
                                </tr>
                                </thead>
                                <tbody>
                                @php
                                    $total_amount = 0;
                                    $total_discount = 0;
                                    $commission = 0;
                                    $shipping_charge = 0;
                                    $grand_amount = 0;
                                @endphp
                                @foreach($cart as $value)


                                    <tr>
                                        <td>
                                            <div class="media">
                                                <div class="card-aside-img">
                                                    <img src="{{ $value->product->product_image }}" alt="img" class="h-60 w-60">
                                                </div>
                                                <div class="media-body">
                                                    <div class="card-item-desc mt-0">
                                                        <h6 class="font-weight-semibold mt-0 text-uppercase">{{ $value->product->subcategory->category_name }}</h6>
                                                        <dl class="card-item-desc-1">
                                                            <dt>Name: </dt>
                                                            <dd>{{ $value->product->product_name }}</dd>
                                                        </dl>
                                                        <dl class="card-item-desc-1">
                                                            <dt>Weight: </dt>
                                                            <dd>{{ $value->product->product_weight }}</dd>
                                                        </dl>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        @php
                                            $product_price = $value->product->product_price;
                                            $service_Charge = ($value->product->product_price * $value->product->subcategory->commission) / 100;
                                            $unit_price = $product_price + $service_Charge;
                                        @endphp
                                        <td>₹ {{ $unit_price  }}</td>

                                        <td>{{ $value->quantity }}</td>


                                        <td>
                                            @php
                                                $actutal_discount = ($value->product->product_price * $value->product->product_discount) / 100;


                                            @endphp
                                            ₹ {{ $actutal_discount * $value->quantity }}
                                        </td>



                                        <td>
                                            @php
                                                $quantity = $value->quantity;
                                                $sum_total = $unit_price * $quantity;
                                            @endphp
                                            ₹ {{ $sum_total }}
                                        </td>

                                    </tr>


                                    @php
                                        $quantity = $value->quantity;
                                         $actutal_product_price = $value->product->product_price * $quantity;
                                         $actual_commission = ($actutal_product_price * $value->product->subcategory->commission) / 100;
                                         $shipping_charge += $value->product->shipping_charge;
                                         $actutal_discount = ($value->product->product_price * $value->product->product_discount) / 100;
                                         $total_discount += $actutal_discount * $quantity;

                                        // $commission += $actual_commission;
                                         $total_amount += $actutal_product_price + $actual_commission;

                                         $grand_amount = $total_amount +  $shipping_charge - $total_discount;
                                    @endphp

                                @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12 col-lg-6">
                <div class="card">
                    <div class="card-header pb-0">
                        <h3 class="card-title mb-0">Payment Methods</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-4">
                            <label class="form-label fs-15">Select Method</label>
                            <div class=" gutters-xs">
                                <select name="payment_method" id="payment_methods" class="form-control custom-select select2">
                                    @foreach($deliverymethods as $value)
                                        <option value="{{ $value->id }}">{{ $value->name }}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('payment_method'))
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required">{{ $errors->first('payment_method') }}</li>
                                    </ul>
                                @endif
                            </div>

                        </div>

                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="card-title mb-0">Order Summery</div>

                    </div>
                    <div class="card-body">
                        {{-- <div class="row mb-4">
                             <div class="col-6"><input class="productcart form-control" type="text" placeholder="Coupon Code"></div>
                             <div class="col-6"><a href="#" class="btn btn-primary btn-md">Apply</a></div>
                         </div>--}}
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tbody>
                                <tr>
                                    <td><i class="fas fa-plus-circle"></i> Total Amount</td>
                                    <td class="text-right"> ₹ {{ number_format($total_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-plus-circle"></i> Shipping Charge</td>
                                    <td class="text-right"> ₹ {{ number_format($shipping_charge, 2) }}</td>
                                </tr>


                                <tr>
                                    <td><span><i class="fas fa-minus-circle"></i> Total Discount</span></td>
                                    <td class="text-right text-muted"><span> ₹ {{ number_format($total_discount, 2) }}</span></td>
                                </tr>

                                <tr>
                                    <td><span><i class="fas fa-calculator"></i> Grand Total</span></td>
                                    <td><h2 class="price text-right mb-0"> ₹ {{ number_format($grand_amount, 2) }}</h2></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center">
                            <a href="{{url('agent/ecommerce/view-cart')}}" class="btn btn-danger float-left mt-2 m-b-20">Back to Cart</a>
                            <button  class="btn btn-success mt-2 float-right" type="submit">Place Order</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </form>



    </div>
    </div>
    </div>




    {{--add delivery addresses--}}

    <div class="modal fade" id="add_delivery_addresses_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2" aria-hidden="true">
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
                                        <li class="parsley-required" id="name_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label for="name">Mobile Number</label>
                                    <input type="text" id="mobile_number" class="form-control" placeholder="Mobile Number">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="mobile_number_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label for="name">Email Address</label>
                                    <input type="email" id="email" class="form-control" placeholder="Email Address">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="email_errors"></li>
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
                                        <li class="parsley-required" id="state_id_errors"></li>
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
                                        <li class="parsley-required" id="district_id_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">City</label>
                                    <input type="text" id="city" class="form-control" placeholder="City">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="city_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Pin Code</label>
                                    <input type="text" id="pin_code" class="form-control" placeholder="Pin Code">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="pin_code_errors"></li>
                                    </ul>
                                </div>
                            </div>


                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Full Address</label>
                                    <textarea class="form-control" placeholder="Enter Full Address.." id="address"></textarea>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="address_errors"></li>
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
                                        <li class="parsley-required" id="view_name_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label for="name">Mobile Number</label>
                                    <input type="text" id="view_mobile_number" class="form-control" placeholder="Mobile Number">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_mobile_number_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label for="name">Email Address</label>
                                    <input type="email" id="view_email" class="form-control" placeholder="Email Address">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_email_errors"></li>
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
                                        <li class="parsley-required" id="view_state_id_errors"></li>
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
                                        <li class="parsley-required" id="view_district_id_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">City</label>
                                    <input type="text" id="view_city" class="form-control" placeholder="City">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_city_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Pin Code</label>
                                    <input type="text" id="view_pin_code" class="form-control" placeholder="Pin Code">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_pin_code_errors"></li>
                                    </ul>
                                </div>
                            </div>


                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Full Address</label>
                                    <textarea class="form-control" placeholder="Enter Full Address.." id="view_address"></textarea>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_address_errors"></li>
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