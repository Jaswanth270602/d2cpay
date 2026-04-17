@extends('front.ecommerce.header')
@section('content')


    <section class="shopping_cart_page">
        <div class="container">
            <div class="row">

                @include('front.ecommerce.profile_left')

                <div class="col-lg-9 col-md-8 col-sm-7">
                    <div class="widget">
                        <div class="section-header">
                            <h5 class="heading-design-h5">
                               My Order
                            </h5>
                        </div>

                        <div class="table-responsive">
                            <table class="table cart_summary">
                                <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Date</th>
                                    <th>Mobile</th>
                                    <th>Amount</th>
                                    <th>Discount</th>
                                    <th>Shipping</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($ecommerce_orders as $value)
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
                                            <td>{{ $value->id }}</td>
                                            <td>{{$value->created_at}}</td>
                                            <td>{{ $value->mobile_number }}</td>
                                            <td>{{number_format($value->total_amount + $value->total_commission, 2)}}</td>
                                            <td>{{ number_format($value->total_discount, 2)}}</td>
                                            <td>{{number_format($value->shipping_charges, 2)}}</td>
                                            <td>{{number_format($value->grand_total, 2)}}</td>
                                            <td>{!! $status !!}</td>
                                            <td><a href="{{url('ecommerce/order-details')}}/{{$value->id}}" class="btn btn-danger btn-sm">Details</a></td>
                                        </tr>
                                    @endforeach
                                </tbody>

                            </table>
                            {!! $ecommerce_orders->appends(Request::all())->links() !!}
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
                    <h6 class="modal-title">Product Details</h6>
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


                    <div class="card">
                        <div class="product-details table-responsive text-nowrap">
                            <table class="table table-bordered table-hover mb-0 text-nowrap">
                                    <tr>
                                        <th>Name</th>
                                        <td><span class="name"></span></td>
                                    </tr>

                                <tr>
                                    <th>City</th>
                                    <td><span class="city"></span></td>
                                </tr>

                                <tr>
                                    <th>State</th>
                                    <td><span class="state_name"></span></td>
                                </tr>

                                <tr>
                                    <th>District</th>
                                    <td><span class="district_name"></span></td>
                                </tr>

                                <tr>
                                    <th>Pin Code</th>
                                    <td><span class="pin_code"></span></td>
                                </tr>
                                <tr>
                                    <th>Mobile Number</th>
                                    <td><span class="mobile_number"></span></td>
                                </tr>

                                <tr>
                                    <th>Email Address</th>
                                    <td><span class="email"></span></td>
                                </tr>

                                <tr>
                                    <th>Full Address</th>
                                    <td><span class="address"></span></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="product-details table-responsive text-nowrap" style="margin-top: 2%">
                        <table class="table table-bordered table-hover mb-0 text-nowrap">
                            <thead>
                            <tr>
                                <th>Product Image</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Discount</th>
                                <th>Shipping</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Track</th>
                            </tr>
                            </thead>
                            <tbody class="productdetails_list">

                            </tbody>
                        </table>
                    </div>





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