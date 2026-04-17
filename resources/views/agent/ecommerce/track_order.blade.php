@extends('agent.layout.header_ecommerce')
@section('content')


    <div class="main-content-body">

        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card">
                    <div class="card-body">

                        <form action="{{url('agent/ecommerce/track-orders')}}" method="get">
                            <div class="row">


                                <div class="col-lg-3 col-md-8 form-group mg-b-0">
                                    <label class="form-label">Tracking Id: <span class="tx-danger">*</span></label>
                                    <input type="text" class="form-control" name="order_id" value="{{ $order_id }}" placeholder="Tracking Id">
                                    </select>
                                </div>

                                <div class="col-lg-3 col-md-4 mg-t-10 mg-sm-t-25">
                                    <button class="btn btn-primary pd-x-20" type="submit"><i class="fas fa-search"></i> Search</button>
                                </div>


                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>


        @foreach($ecommerce_productorders as $value)
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="product-details table-responsive text-nowrap">
                                <table class="table table-bordered table-hover mb-0 text-nowrap">
                                    <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Discount</th>
                                        <th>Shipping</th>
                                        <th>Service Charge</th>
                                        <th>Total Amount</th>

                                    </tr>
                                    </thead>
                                    <tbody>




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

                                        <td>{{ $value->quantity }}</td>
                                        <td>{{ number_format($value->product_price, 2) }}</td>
                                        <td>{{ number_format($value->product_discount, 2) }}</td>
                                        <td>{{ number_format($value->shipping_charge, 2) }}</td>
                                        <td>{{ number_format($value->commission, 2) }}</td>
                                        <td>{{ number_format($value->total_amount, 2) }}</td>




                                    </tbody>
                                </table>
                            </div>

                            @php
                                if ($value->status_id == 4){
                                      $orderdetails = '<ul id="progressbar">
                                                  <li class="step0 active " id="step1">Ordered</li>
                                                  <li class="step0  text-center" id="step2">Packed</li>
                                                  <li class="step0  text-right" id="step3">Shipped</li>
                                                  <li class="step0  text-right" id="step4">Delivered</li>
                                              </ul>';
                                  }elseif ($value->status_id == 3){
                                      $orderdetails = '<ul id="progressbar">
                                                  <li class="step0 active " id="step1">Ordered</li>
                                                  <li class="step0 active text-center" id="step2">Packed</li>
                                                  <li class="step0  text-right" id="step3">Shipped</li>
                                                  <li class="step0  text-right" id="step4">Delivered</li>
                                              </ul>';
                                  }elseif ($value->status_id == 2){
                                      $orderdetails = '<ul id="progressbar">
                                                  <li class="step0 active " id="step1">Ordered</li>
                                                  <li class="step0 active text-center" id="step2">Packed</li>
                                                  <li class="step0 active text-right" id="step3">Shipped</li>
                                                  <li class="step0  text-right" id="step4">Delivered</li>
                                              </ul>';
                                  }elseif ($value->status_id == 1){
                                      $orderdetails = '<ul id="progressbar">
                                                  <li class="step0 active " id="step1">Ordered</li>
                                                  <li class="step0 active text-center" id="step2">Packed</li>
                                                  <li class="step0 active text-right" id="step3">Shipped</li>
                                                  <li class="step0 active text-right" id="step4">Delivered</li>
                                              </ul>';
                                  }
                            @endphp

                            <div class="progress-track">
                                {!! $orderdetails !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach






    </div>
    </div>

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