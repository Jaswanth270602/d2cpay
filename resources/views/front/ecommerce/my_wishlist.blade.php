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
                                My Wishlist
                            </h5>
                        </div>


                        @include('front.ecommerce.product_list')

                    </div>
                </div>
            </div>
        </div>
    </section>




@endsection