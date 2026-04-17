@extends('front.ecommerce.header')
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
                            alert(msg.message);
                            setTimeout(function () { location.reload(1); }, 2000);
                        }else{
                            alert(msg.message);
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

    </script>
    <section class="shopping_cart_page">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    <div class="checkout-step mb-40">
                        <ul>
                            <li class="active">
                                <a href="{{url('ecommerce/view-cart')}}">
                                    <div class="step">
                                        <div class="line"></div>
                                        <div class="circle">1</div>
                                    </div>
                                    <span>Cart</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{url('ecommerce/checkout')}}">
                                    <div class="step">
                                        <div class="line"></div>
                                        <div class="circle">2</div>
                                    </div>
                                    <span>Checkout</span>
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <div class="step">
                                        <div class="line"></div>
                                        <div class="circle">3</div>
                                    </div>
                                    <span>Order Complete</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-12 col-md-8 mx-auto">
                    <div class="widget">
                        <div class="section-header">
                            <h3 class="heading-design-h5">
                                Cart
                            </h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table cart_summary">
                                <thead>
                                <tr>
                                    <th class="cart_product">Product</th>
                                    <th>Description</th>
                                    <th>Unit price</th>
                                    <th>Qty</th>
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
                                    <td class="cart_product"><a href="#"><img class="img-fluid" src="{{ $value->product->product_image }}" alt="Product"></a></td>
                                    <td class="cart_description">
                                        <p class="product-name"><a href="#">{{ $value->product->subcategory->category_name }}</a></p>
                                        <small><a href="#">Name : {{ $value->product->product_name }}</a></small><br>
                                        <small><a href="#">Weight: {{ $value->product->product_weight }} Grams</a></small>
                                    </td>
                                    @php
                                        $product_price = $value->product->product_price;
                                        $service_Charge = ($value->product->product_price * $value->product->subcategory->commission) / 100;
                                        $unit_price = $product_price + $service_Charge;
                                    @endphp
                                    <td class="price"><span>₹ {{ $unit_price  }}</span></td>
                                    <td class="qty">
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
                                    <td class="price">
                                        @php
                                            $actutal_discount = ($value->product->product_price * $value->product->product_discount) / 100;


                                        @endphp
                                        ₹ {{ $actutal_discount * $value->quantity }}
                                    </td>
                                    <td class="price">
                                        @php
                                            $quantity = $value->quantity;
                                            $sum_total = $unit_price * $quantity;
                                        @endphp
                                        ₹ {{ $sum_total }}
                                    </td>
                                    <td class="action">
                                        <a  href="#" onclick="delete_product_cart({{ $value->id }})"><i class="fa fa-trash-o"></i></a>
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
                                <tfoot>
                                <tr>
                                    <td colspan="2"></td>
                                    <td colspan="3">Total Amount</td>
                                    <td colspan="2">₹ {{ number_format($total_amount, 2) }}</td>
                                </tr>

                                <tr>
                                    <td colspan="2"></td>
                                    <td colspan="3">Shipping Charge</td>
                                    <td colspan="2">₹ {{ number_format($shipping_charge, 2) }}</td>
                                </tr>

                                <tr>
                                    <td colspan="2"></td>
                                    <td colspan="3">Total Discount</td>
                                    <td colspan="2">₹ {{ number_format($total_discount, 2) }}</td>
                                </tr>

                                <tr>
                                    <td colspan="2"></td>
                                    <td colspan="3">Grand Total</td>
                                    <td colspan="2">₹ {{ number_format($grand_amount, 2) }}</td>
                                </tr>

                                </tfoot>
                            </table>
                        </div>
                        <a href="{{url('')}}" class="btn btn-danger btn-round">Continue Shopping</a>
                        <a href="{{url('ecommerce/checkout')}}" class="btn btn-success btn-round pull-right">NEXT</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
