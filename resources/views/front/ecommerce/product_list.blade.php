<div class="row products_page_list">
    <div class="clearfix"></div>


    @foreach($products as $value)
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
                @if(Request::segment(2)  == 'my-wishlist')
                    <span class="like-icon"><a href="#"> <i class="icofont icofont-close-circled"></i></a></span>
                @endif
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



{!! $products->appends(Request::all())->links() !!}