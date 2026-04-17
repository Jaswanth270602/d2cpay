@extends('front.ecommerce.header')
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
                       alert(msg.message);
                    }else{
                        alert(msg.message);
                    }
                }
            });
        }
        </script>
    <section class="products_page">
        <div class="container">
            <div class="row">
                <div class="col-lg-5 col-md-5">
                    <div class="shop-detail-left">
                        <div class="item-img-grid">
                            <div class="favourite-icon">
                                <a class="fav-btn" title="" data-placement="bottom" data-toggle="tooltip" href="#" data-original-title="Save Ad">{{ $product_discount }} OFF</a>
                            </div>
                            <div id="sync1" class="owl-carousel">
                                <div class="item"><img alt="" src="{{ $product_image }}" class="img-responsive img-center"></div>
                                @foreach($productimages as $value)
                                     <div class="item"><img alt="" src="{{ $value->photo }}" class="img-responsive img-center"></div>
                                @endforeach
                            </div>
                            <div id="sync2" class="owl-carousel">
                                <div class="item"><img alt="" src="{{ $product_image }}" class="img-responsive img-center"></div>
                                @foreach($productimages as $value)
                                 <div class="item"><img alt="" src="{{ $value->photo }}" class="img-responsive img-center"></div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7 col-md-7">
                    <div class="shop-detail-right">
                        <div class="widget">
                            <div class="product-name">
                                <p class="text-danger text-uppercase">
                                    {{ $category_name }}
                                </p>
                                <h1>{{ $product_name }}</h1>
                            </div>
                            <div class="price-box">
                                <h5>
                                    <span class="product-desc-price">Price : {{number_format($product_show_price, 2)}}</span>
                                    <span class="product-price text-danger">Special Price {{ number_format($product_price, 2) }}</span>
                                    <span class="badge badge-default">50% Off</span>
                                </h5>
                            </div>

                            <div class="short-description">
                                <h4>Description</h4>
                                <hr>
                                <p>{!! $description !!}</p>
                            </div>

                            <div class="product-variation">
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
                                    <div class="form-group">
                                        <label class="control-label">Quantity<span class="required">*</span></label>
                                        <input type="hidden" class="form-control" name="product_id" value="{{ $product_id }}">
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
                                    <button type="button" class="btn btn-outline-success btn-lg" onclick="save_to_wishlist({{ $product_id }})"><i class="icofont icofont-heart"></i> Add To Wishlist</button>
                                    <button type="submit" class="btn btn-theme-round btn-lg"> <i class="icofont icofont-shopping-cart"></i> Add To Cart</button>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="deals-of-the-day">
        <div class="container">
            <div class="section-header">
                <h5 class="heading-design-h5">
                    Related Products
                </h5>
            </div>
            <div class="row">
                @foreach($relatedproduct as $value)
                    @php
                        $product_discount = ($value->product_price * $value->product_discount) / 100;
                        $discount_price = $value->product_price - $product_discount;
                        $commission = ($value->product_price * $value->subcategory->commission) / 100;
                        $product_price = $discount_price + $commission;
                        $product_show_price = $value->product_price + $commission;
                    @endphp

                    <div class="col-lg-3 col-md-6">
                        <div class="item">
                            <div class="h-100">
                                <div class="product-item">
                                    <span class="badge badge-danger offer-badge">{{$value->product_discount}}% OFF</span>
                                    <div class="product-item-image">
                                        <a href="{{url('ecommerce/product-details')}}/{{$value->id}}"><img class="card-img-top img-fluid" src="{{ $value->product_image }}" alt=""></a>
                                    </div>
                                    <div class="product-item-body">
                                        <h4 class="card-title"><a href="{{url('ecommerce/product-details')}}/{{$value->id}}">{{ Str::limit($value->product_name, 30) }}</a></h4>
                                        <h5>
                                            <span class="product-desc-price">₹{{number_format($product_show_price, 2)}}</span>
                                            <span class="product-price">₹{{number_format($product_price, 2)}}</span>
                                        </h5>
                                        <p>
                                            <a class="btn btn-success" href="{{url('ecommerce/product-details')}}/{{$value->id}}"><i class="icofont icofont-eye-alt"></i> View Product</a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach



            </div>
        </div>
    </section>
@endsection