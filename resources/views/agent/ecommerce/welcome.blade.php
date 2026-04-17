@extends('agent.layout.header_ecommerce')
@section('content')
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script type="text/javascript">



    </script>

    <div class="main-content-body">

        <div class="row row-sm">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body p-2">
                        <form id="search-form" action="{{url('agent/ecommerce/search-product')}}" method="get">
                            {!! csrf_field() !!}
                        <div class="input-group">
                            <input type="text" class="form-control" name="search_product" id="search_text" placeholder="Search ...">
                            <span class="input-group-append">
                                <button class="btn btn-primary" type="button">Search</button>
                            </span>
                        </div>
                        </form>
                    </div>
                </div>
                <div class="row row-sm">

                @foreach($products as $value)
                    <div class="col-md-3">
                        <div class="product-card card">
                            <div class="card-body h-100">
                                <div class="d-flex">
                                    <span class="text-secondary small text-uppercase">{{ $value->subcategory->category_name }}</span>
                                    @php
                                        $wishlists = App\Wishlist::where('user_id', Auth::id())->where('product_id', $value->id)->first();
                                        if ($wishlists){
                                             $wishlists_heart = '<i class="fa fa-heart text-danger"></i>';
                                        }else{
                                             $wishlists_heart = "<i class='far fa-heart'></i>";
                                        }
                                    @endphp
                                    <span class="ml-auto">{!! $wishlists_heart !!}</span>
                                </div>
                                <h3 class="h6 mb-2 font-weight-bold text-uppercase">{{ Str::limit($value->product_name, 30) }}</h3>
                                <div class="d-flex">
                                    @php
                                        $product_discount = ($value->product_price * $value->product_discount) / 100;
                                        $discount_price = $value->product_price - $product_discount;
                                        $commission = ($value->product_price * $value->subcategory->commission) / 100;
                                        $product_price = $discount_price + $commission;
                                    @endphp

                                    <h4 class="h5 w-50 font-weight-bold text-danger">₹ {{ number_format($product_price, 2) }}</h4>
                                </div>
                                <a href="{{url('agent/ecommerce/product-details')}}/{{ $value->id }}">
                                 <img class="w-100 mt-2 mb-3" src="{{ $value->product_image }}" alt="product-image"/>
                                </a>
                                <a href="{{url('agent/ecommerce/product-details')}}/{{ $value->id }}" class="btn btn-primary btn-block mb-0">
                                    <i class="fe fe-shopping-cart mr-1"></i>
                                    View Product
                                </a>
                            </div>
                        </div>
                    </div>
            @endforeach


                </div>
                {!! $products->appends(Request::all())->links() !!}
            </div>
        </div>

    </div>
    </div>
    </div>


@endsection