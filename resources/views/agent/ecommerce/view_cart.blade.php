@extends('agent.layout.header_ecommerce')
@section('content')
    
    <script type="text/javascript">
        function delete_product_cart(id) {
            if (confirm("Are you sure? Delete this product") == true) {
                $(".loader").show();
                var token = $("input[name=_token]").val();
                var dataString = 'id=' + id +  '&_token=' + token;
                $.ajax({
                    type: "POST",
                    url: "{{url('agent/ecommerce/delete-product-from-cart')}}",
                    data: dataString,
                    success: function (msg) {
                        $(".loader").hide();
                        if (msg.status == 'success') {
                            swal("Success", msg.message, "success");
                            setTimeout(function () { location.reload(1); }, 2000);
                        }else{
                            swal("Faild", msg.message, "error");
                        }
                    }
                });
            }
        }
        
        function update_quantity(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var quantity = $("#quantity_"+id).val();
            var dataString = 'id=' + id +  '&quantity=' + quantity + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/ecommerce/update-quantity-in-cart')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    setTimeout(function () { location.reload(1); }, 1000);
                }
            });
        }
        
        function save_to_wishlist(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'product_id=' + id +   '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/ecommerce/save-to-wishlist')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                    }else{
                        swal("Faild", msg.message, "error");
                    }
                }
            });
        }
    </script>

    <div class="main-content-body">

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
                                    <th>Action</th>
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

                                    <td>
                                        <div class="form-group">
                                            <select name="quantity_{{$value->id}}" id="quantity_{{$value->id}}" class="form-control custom-select select2" onchange="update_quantity({{ $value->id }})">
                                                <option value="1" @if($value->quantity == 1) selected @endif>1</option>
                                                <option value="2" @if($value->quantity == 2) selected @endif>2</option>
                                                <option value="3" @if($value->quantity == 3) selected @endif>3</option>
                                                <option value="4" @if($value->quantity == 4) selected @endif>4</option>
                                                <option value="5" @if($value->quantity == 5) selected @endif>5</option>
                                                <option value="6" @if($value->quantity == 6) selected @endif>6</option>
                                                <option value="7" @if($value->quantity == 7) selected @endif>7</option>
                                                <option value="8" @if($value->quantity == 8) selected @endif>8</option>
                                                <option value="9" @if($value->quantity == 9) selected @endif>9</option>
                                                <option value="10" @if($value->quantity == 10) selected @endif>10</option>
                                            </select>
                                        </div>
                                    </td>


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
                                    <td>
                                        <a class="btn btn-danger btn-sm text-white" data-toggle="tooltip" data-original-title="Delete" onclick="delete_product_cart({{ $value->id }})"><i class="fe fe-trash"></i></a>
                                        <a class="btn btn-info btn-sm text-white" data-toggle="tooltip" data-original-title="Save to Wishlist" onclick="save_to_wishlist({{ $value->product_id }})"><i class="fe fe-heart"></i></a>
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

            <div class="col-lg-12">
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
                        <form class="text-center">
                            <a href="{{url('agent/ecommerce/welcome')}}" class="btn btn-danger float-left mt-2 m-b-20">Continue Shopping</a>
                            <a href="{{url('agent/ecommerce/checkout')}}" class="btn btn-success mt-2 float-right">Next</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>




    </div>
    </div>
    </div>


@endsection