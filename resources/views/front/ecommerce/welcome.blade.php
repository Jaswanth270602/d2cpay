@extends('front.ecommerce.header')
@section('content')


<section class="products_page">
    <div class="container">
        <div class="row">

            {{--left side--}}
            @include('front.ecommerce.left_bar')

            <div class="col-lg-9 col-md-8">
                <div class="osahan-inner-slider">
                    <div class="owl-carousel owl-carousel-slider">
                        @foreach(App\Shoppingbanner::where('status_id', 1)->inRandomOrder()->get() as $key => $value)
                        <div class="item">
                            <a href="#"><img class="d-block img-fluid" src="{{ $value->banners }}" alt="First slide"></a>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{--product list--}}
                @include('front.ecommerce.product_list')


            </div>
        </div>
    </div>
    </div>
</section>
<section class="top-brands">
    <div class="container">
        <div class="section-header">
            <h5 class="heading-design-h5">Top Brands <span class="badge badge-primary">200 Brands</span></h5>
        </div>
        <div class="row text-center">
            <div class="col-lg-2 col-md-2 col-sm-2">
                <a href="#"><img class="img-responsive" src="https://askbootstrap.com/preview/osahan-fashion/images/brands/1.jpg" alt=""></a>
            </div>
            <div class="col-lg-2 col-md-2 col-sm-2">
                <a href="#"><img class="img-responsive" src="https://askbootstrap.com/preview/osahan-fashion/images/brands/2.jpg" alt=""></a>
            </div>
            <div class="col-lg-2 col-md-2 col-sm-2">
                <a href="#"><img class="img-responsive" src="https://askbootstrap.com/preview/osahan-fashion/images/brands/3.jpg" alt=""></a>
            </div>
            <div class="col-lg-2 col-md-2 col-sm-2">
                <a href="#"><img class="img-responsive" src="https://askbootstrap.com/preview/osahan-fashion/images/brands/4.jpg" alt=""></a>
            </div>
            <div class="col-lg-2 col-md-2 col-sm-2">
                <a href="#"><img class="img-responsive" src="https://askbootstrap.com/preview/osahan-fashion/images/brands/5.jpg" alt=""></a>
            </div>
            <div class="col-lg-2 col-md-2 col-sm-2">
                <a href="#"><img class="img-responsive" src="https://askbootstrap.com/preview/osahan-fashion/images/brands/6.jpg" alt=""></a>
            </div>

        </div>
    </div>
</section>

@endsection
