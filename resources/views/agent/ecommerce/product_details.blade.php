@extends('agent.layout.header_ecommerce')
@section('content')
    <script type="text/javascript">
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


        <div class="row row-sm">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body h-100">
                        <div class="row row-sm ">
                            <div class=" col-xl-5 col-lg-12 col-md-12">
                                <div class="preview-pic tab-content">
                                    <div class="tab-pane active" id="first-1"><img src="{{ $product_image }}" alt="image"/></div>

                                    @foreach($productimages as $value)
                                        <div class="tab-pane" id="pic-{{$value->id}}"><img src="{{ $value->photo }}" alt="image"/></div>
                                    @endforeach

                                </div>
                                <ul class="preview-thumbnail nav nav-tabs">
                                    <li class="active"><a data-target="#first-1" data-toggle="tab"><img src="{{ $product_image }}" alt="image"/></a></li>

                                    @foreach($productimages as $value)
                                         <li><a data-target="#pic-{{$value->id}}" data-toggle="tab"><img src="{{ $value->photo }}" alt="image"/></a></li>
                                    @endforeach
                                </ul>
                            </div>

                            <div class="details col-xl-7 col-lg-12 col-md-12 mt-4 mt-xl-0">
                                @if(Session::has('success_message'))
                                    <div class="alert alert-success">
                                        <a class="close" data-dismiss="alert">×</a>
                                       {!!Session::get('success_message')!!}
                                    </div>
                                @endif

                                @if(Session::has('error_message'))
                                    <div class="alert alert-danger">
                                        <a class="close" data-dismiss="alert">×</a>
                                        {!!Session::get('error_message')!!}
                                    </div>
                                @endif

                                <form  action="{{url('agent/ecommerce/add-to-cart')}}" method="post">
                                    {!! csrf_field() !!}
                                <h4 class="product-title">{{ $product_name }}</h4>
                                    <hr>


                                <p class="product-description">{!! $description !!}</p>
                                <h6 class="price">Price: <span class="h3 ml-2"> ₹ {{ number_format($product_price, 2) }}</span></h6>
                                <div class="d-flex  mt-2">
                                    <div class="mt-2 product-title">Quantity:</div>
                                    <div class="d-flex ml-2">
                                        <div class="form-group">
                                            <input type="hidden" class="form-control" name="product_id" value="{{ $product_id }}">
                                            <div class="form-group">
                                                <select name="quantity" id="quantity" class="form-control custom-select select2">
                                                    <option value="1">1</option>
                                                    <option value="2">2</option>
                                                    <option value="3">3</option>
                                                    <option value="4">4</option>
                                                    <option value="5">5</option>
                                                    <option value="6">6</option>
                                                    <option value="7">7</option>
                                                    <option value="8">8</option>
                                                    <option value="9">9</option>
                                                    <option value="10">10</option>
                                                </select>
                                            </div>
                                            @if ($errors->has('quantity'))
                                                <ul class="parsley-errors-list filled">
                                                    <li class="parsley-required">{{ $errors->first('quantity') }}</li>
                                                </ul>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="action">
                                    <button class="add-to-cart btn btn-danger" type="button" onclick="save_to_wishlist({{ $product_id }})">ADD TO WISHLIST</button>
                                    <button class="add-to-cart btn btn-success" type="submit">ADD TO CART</button>
                                </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>



        <div class="row">

        @foreach($relatedproduct as $value)
            <div class="col-lg-3">
                <div class="card item-card">
                    <div class="card-body pb-0 h-100">
                        <div class="text-center">
                            <img src="{{ $value->product_image }}" alt="img" class="img-fluid">
                        </div>
                        <div class="card-body cardbody relative">
                            <div class="cardtitle">
                                <span>Items</span>
                                <a>{{ Str::limit($value->product_name, 30) }}</a>
                            </div>
                            <div class="cardprice">
                                @php
                                    $product_discount = ($value->product_price * $value->product_discount) / 100;
                                    $product_price = $value->product_price - $product_discount;
                                @endphp
                                <span class="type--strikethrough">₹ {{ number_format($value->product_price, 2) }}</span>
                                <span>₹ {{ number_format($product_price, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-center border-top p-3">
                        <a href="{{url('agent/ecommerce/welcome')}}" class="btn btn-primary btn-sm"> View More</a>
                        <a href="{{url('agent/ecommerce/product-details')}}/{{ $value->id }}" class="btn btn-success btn-sm"><i class="fa fa-shopping-cart"></i> View Product</a>
                    </div>
                </div>
            </div>
        @endforeach



        </div>

    </div>
    </div>
    </div>


@endsection