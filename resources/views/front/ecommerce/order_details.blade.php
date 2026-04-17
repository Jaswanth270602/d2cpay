@extends('front.ecommerce.header')
@section('content')

    <script type="text/javascript">
        function track_order(order_id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'order_id=' + order_id +  '&_token=' + token;
            $.ajax({
                type: "post",
                url: "{{url('agent/ecommerce/view-track-order')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        $("#track-order-label").show();
                        $("#progressbar-label").html(msg.orderdetails);
                        $("#view_product_details_model").modal('show');
                    }else{
                        swal("Failed", msg.message, "error");
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
                                Order Status
                            </h5>
                        </div>
                        <div class="status-main">
                            <div class="row">
                                <div class="col-lg-12 col-md-12">
                                    <h4 class="block-title-border"> Your Order Status </h4>
                                </div>
                               {{-- <div class="col-lg-12 col-md-12">
                                    <div class="statustop">
                                        <p><strong>Status:</strong> OnHold</p>
                                        <p><strong>Order Date:</strong> Saturday, April 09,2015</p>
                                        <p><strong>Order Number:</strong> #6469 </p>
                                        <br>
                                    </div>
                                </div>--}}
                            </div>
                            <div class="row">
                                <div class="col-lg-12 col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            Shipping Address
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table cart_summary">
                                                    <tr>
                                                        <th>Name</th>
                                                        <td>{{ $name }}</td>
                                                    </tr>

                                                    <tr>
                                                        <th>City</th>
                                                        <td>{{ $city }}</td>
                                                    </tr>

                                                    <tr>
                                                        <th>State</th>
                                                        <td>{{ $state_name }}</td>
                                                    </tr>

                                                    <tr>
                                                        <th>District</th>
                                                        <td>{{ $district_name }}</td>
                                                    </tr>

                                                    <tr>
                                                        <th>Pin Code</th>
                                                        <td>{{ $pin_code }}</td>
                                                    </tr>

                                                    <tr>
                                                        <th>Mobile Number</th>
                                                        <td>{{ $mobile_number }}</td>
                                                    </tr>

                                                    <tr>
                                                        <th>Email Address</th>
                                                        <td>{{ $email }}</td>
                                                    </tr>

                                                    <tr>
                                                        <th>Full Address</th>
                                                        <td>{{ $address }}</td>
                                                    </tr>

                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12 col-md-12">
                                    <div class="card">
                                        <div class="card-header">
                                            Order Items
                                        </div>
                                        <div class="card-block padding-none">
                                            <table class="table cart_summary table-responsive">
                                                <thead>
                                                <tr>
                                                    <th class="cart_product">Product</th>
                                                    <th>Description</th>
                                                    <th>Quantity</th>
                                                    <th>Price</th>
                                                    <th>Discount</th>
                                                    <th>Shipping</th>
                                                    <th>Total</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @php $total_amount = 0; @endphp
                                                @foreach($ecommerce_productorders as $value)
                                                    @php
                                                        if ($value->status_id == 1){
                                                             $status = '<span class="badge badge-success">Delivered</span>';
                                                         }elseif ($value->status_id == 2){
                                                             $status = '<span class="badge badge-primary">Shipped</span>';
                                                         }elseif ($value->status_id == 3){
                                                             $status = '<span class="badge badge-warning">Packed</span>';
                                                         }else{
                                                             $status = '<span class="badge badge-warning">Ordered</span>';
                                                         }
                                                    @endphp

                                                <tr>
                                                    <td class="cart_product"><a href="#"><img class="img-fluid" src="{{$value->product->product_image}}" alt="Product"></a></td>
                                                    <td class="cart_description">
                                                        <p class="product-name"><a href="#">{{$value->subcategory->category_name}}</a></p>
                                                        <small><a href="#">Name : {{$value->product->product_name}}</a></small><br>
                                                        <small><a href="#">Weight : {{$value->product->product_weight}} Grams</a></small>
                                                    </td>
                                                    <td>{{$value->quantity}}</td>
                                                    <td class="price"><span>{{number_format($value->product_price + $value->commission, 2)}}</span></td>
                                                    <td class="price"><span>{{number_format($value->product_discount, 2)}}</span></td>
                                                    <td class="price"><span>{{number_format($value->shipping_charge, 2)}}</span></td>
                                                    <td class="price"><span>{{number_format($value->total_amount, 2)}}</span></td>
                                                    <td>{!! $status !!}</td>
                                                    <td><span class="badge badge-danger" onclick="track_order({{$value->id}})">Track Order</span></td>
                                                </tr>
                                                @php $total_amount += $value->total_amount; @endphp
                                                @endforeach


                                                </tbody>
                                                <tfoot>
                                                <tr>
                                                    <td colspan="8"><strong>Total Amount</strong></td>
                                                    <td colspan="1"><strong>{{number_format($total_amount, 2)}}</strong></td>
                                                </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    {{--view product model--}}
    <div class="modal fade" id="view_product_details_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Tracking Details</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">


                    {{--Tracking Order--}}
                    <div style="display: none;" id="track-order-label">
                        <div class="tracking">
                            <div class="title">Tracking Order</div>
                        </div>
                        <div class="progress-track" id="progressbar-label"></div>
                        <hr>
                    </div>
                    {{--End Tracking Order--}}




                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    {{--close view product model--}}
    <style>
        .title {
            color: rgb(252, 103, 49);
            font-weight: 600;
            margin-bottom: 2vh;
            padding: 0 8%;
            font-size: initial
        }
        #progressbar {
            margin-bottom: 3vh;
            overflow: hidden;
            color: rgb(252, 103, 49);
            padding-left: 0px;
            margin-top: 3vh
        }

        #progressbar li {
            list-style-type: none;
            font-size: x-small;
            width: 25%;
            float: left;
            position: relative;
            font-weight: 400;
            color: rgb(160, 159, 159)
        }

        #progressbar #step1:before {
            content: "";
            color: rgb(252, 103, 49);
            width: 5px;
            height: 5px;
            margin-left: 0px !important
        }

        #progressbar #step2:before {
            content: "";
            color: #fff;
            width: 5px;
            height: 5px;
            margin-left: 32%
        }

        #progressbar #step3:before {
            content: "";
            color: #fff;
            width: 5px;
            height: 5px;
            margin-right: 32%
        }

        #progressbar #step4:before {
            content: "";
            color: #fff;
            width: 5px;
            height: 5px;
            margin-right: 0px !important
        }

        #progressbar li:before {
            line-height: 29px;
            display: block;
            font-size: 12px;
            background: #ddd;
            border-radius: 50%;
            margin: auto;
            z-index: -1;
            margin-bottom: 1vh
        }

        #progressbar li:after {
            content: '';
            height: 2px;
            background: #ddd;
            position: absolute;
            left: 0%;
            right: 0%;
            margin-bottom: 2vh;
            top: 1px;
            z-index: 1
        }

        .progress-track {
            padding: 0 8%
        }

        #progressbar li:nth-child(2):after {
            margin-right: auto
        }

        #progressbar li:nth-child(1):after {
            margin: auto
        }

        #progressbar li:nth-child(3):after {
            float: left;
            width: 68%
        }

        #progressbar li:nth-child(4):after {
            margin-left: auto;
            width: 132%
        }

        #progressbar li.active {
            color: black
        }

        #progressbar li.active:before,
        #progressbar li.active:after {
            background: rgb(15 191 53)
        }
    </style>

@endsection
