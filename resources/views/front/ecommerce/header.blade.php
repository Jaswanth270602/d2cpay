


<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Osahan Fashion - Bootstrap 4 E-Commerce Theme">
    <meta name="keywords" content="Osahan, fashion, Bootstrap4, shop, e-commerce, modern, flat style, responsive, online store, business, mobile, blog, bootstrap 4, html5, css3, jquery, js, gallery, slider, touch, creative, clean">
    <meta name="author" content="Askbootstrap">
    <title>{{ $company_name }}</title>

    <link rel="apple-touch-icon" sizes="76x76" href="images/apple-icon.png">
    <link rel="icon" type="image/png" href="images/favicon.png">

    <link href="{{url('ecommerce/css/bootstrap.min.css')}}" rel="stylesheet">

    <link href="{{url('ecommerce/css/style.css')}}" rel="stylesheet">
    <link href="{{url('ecommerce/css/animate.css')}}" rel="stylesheet">
    <link href="{{url('ecommerce/css/animate.css')}}" rel="stylesheet">
    <link href="{{url('ecommerce/css/mobile.css')}}" rel="stylesheet">

    <link href="{{url('ecommerce/css/font-awesome.min.css')}}" rel="stylesheet" type="text/css">
    <link href="{{url('ecommerce/css/icofont.css')}}" rel="stylesheet" type="text/css">

    <link rel="stylesheet" href="{{url('ecommerce/css/owl.carousel.css')}}">
    <link rel="stylesheet" href="{{url('ecommerce/css/owl.theme.css')}}">
</head>
<body>


@if (Auth::guest())

@else
    @if(Auth::user()->role_id <= 7)
        <script type="text/javascript">
            document.location.href = "admin/dashboard";
        </script>
    @endif
@endif

<style>
    .loader {
        position: fixed !important;
        left: 0px !important;
        top: 0px !important;
        width: 100% !important;
        height: 100% !important;
        z-index: 9999 !important;
        background: url(https://media.giphy.com/media/y1ZBcOGOOtlpC/giphy.gif) 50% 50% no-repeat rgb(249,249,249) !important;
        opacity: .8 !important;
    }
</style>
<div class="loader" style="display: none;"></div>
<div class="modal fade login-modal-main" id="bd-example-modal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="login-modal">
                    <div class="row">
                        <div class="col-lg-6 pad-right-0">
                            <div class="login-modal-left">
                            </div>
                        </div>
                        <div class="col-lg-6 pad-left-0">
                            <button type="button" class="close close-top-right" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                                <span class="sr-only">Close</span>
                            </button>
                            <form>
                                <div class="login-modal-right">

                                    <div class="tab-content">
                                        <div class="tab-pane active" id="login" role="tabpanel">
                                            <h5 class="heading-design-h5">Login to your account</h5>
                                            <fieldset class="form-group">
                                                <label for="formGroupExampleInput">Enter Email/Mobile number</label>
                                                <input type="text" class="form-control" id="formGroupExampleInput" placeholder="+91 123 456 7890">
                                            </fieldset>
                                            <fieldset class="form-group">
                                                <label for="formGroupExampleInput2">Enter Password</label>
                                                <input type="password" class="form-control" id="formGroupExampleInput2" placeholder="********">
                                            </fieldset>
                                            <fieldset class="form-group">
                                                <button type="submit" class="btn btn-lg btn-theme-round btn-block">Enter to your account</button>
                                            </fieldset>
                                            <div class="login-with-sites text-center">
                                                <p>or Login with your social profile:</p>
                                                <button class="btn-facebook login-icons btn-lg"><i class="fa fa-facebook"></i> Facebook</button>
                                                <button class="btn-google login-icons btn-lg"><i class="fa fa-google"></i> Google</button>
                                                <button class="btn-twitter login-icons btn-lg"><i class="fa fa-twitter"></i> Twitter</button>
                                            </div>
                                            <p><label class="custom-control custom-checkbox mb-2 mr-sm-2 mb-sm-0">
                                                    <input type="checkbox" class="custom-control-input">
                                                    <span class="custom-control-indicator"></span>
                                                    <span class="custom-control-description">Remember me </span>
                                                </label>
                                            </p>
                                        </div>
                                        <div class="tab-pane" id="register" role="tabpanel">
                                            <h5 class="heading-design-h5">Register Now!</h5>
                                            <fieldset class="form-group">
                                                <label for="formGroupExampleInput">Enter Email/Mobile number</label>
                                                <input type="text" class="form-control" id="formGroupExampleInput" placeholder="+91 123 456 7890">
                                            </fieldset>
                                            <fieldset class="form-group">
                                                <label for="formGroupExampleInput2">Enter Password</label>
                                                <input type="password" class="form-control" id="formGroupExampleInput2" placeholder="********">
                                            </fieldset>
                                            <fieldset class="form-group">
                                                <label for="formGroupExampleInput3">Enter Confirm Password </label>
                                                <input type="password" class="form-control" id="formGroupExampleInput3" placeholder="********">
                                            </fieldset>
                                            <fieldset class="form-group">
                                                <button type="submit" class="btn btn-lg btn-theme-round btn-block">Create Your Account</button>
                                            </fieldset>
                                            <p>
                                                <label class="custom-control custom-checkbox mb-2 mr-sm-2 mb-sm-0">
                                                    <input type="checkbox" class="custom-control-input">
                                                    <span class="custom-control-indicator"></span>
                                                    <span class="custom-control-description">I Agree with Term and Conditions </span>
                                                </label>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                    <div class="text-center login-footer-tab">
                                        <ul class="nav nav-tabs" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link active" data-toggle="tab" href="#login" role="tab"><i class="icofont icofont-lock"></i> LOGIN</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-toggle="tab" href="#register" role="tab"><i class="icofont icofont-pencil-alt-5"></i> REGISTER</a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="navbar-top">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-sm-6 col-xs-6 col-md-6 text-right">
                <ul class="list-inline">
                    <li class="list-inline-item">
                        <a href="#"><i class="icofont icofont-headphone-alt"></i> Contact Us</a>
                    </li>

                    @if (Auth::check())


                        <li class="list-inline-item">
                            <a href="{{url('agent/dashboard')}}">B2B Dashboard</a>
                        </li>


                        <li class="list-inline-item">
                            <a href="#">Bal : {{ number_format(Auth::User()->balance->user_balance, 2) }}</a>
                        </li>



                        <li class="list-inline-item">
                        <div class="btn-group">
                            <a href="#" type="button"  class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Welcome, {{ Auth::User()->name }} {{ Auth::User()->last_name }}
                            </a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{url('ecommerce/my-account')}}">My Account</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                   onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                    {{ __('Logout') }}
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                            </div>
                        </div>
                        </li>
                        @else
                        <li class="list-inline-item">
                            <a href="{{url('login')}}">Login</a>
                        </li>
                        <li class="list-inline-item">
                            <a href="#">Register</a>
                        </li>
                        @endif

                </ul>
            </div>
        </div>
    </div>
</div>
<nav class="navbar navbar-expand-lg navbar-light bg-faded osahan-menu">
    <div class="container">
        <a class="navbar-brand" href="{{url('')}}"> <img src="{{ $company_logo }}" alt="logo" style="height: 60px;"> </a>
        <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav mr-auto mt-2 mt-lg-0 margin-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{url('')}}">Home</a>
                </li>




                @foreach(App\Category::where('status_id', 1)->get() as $value)
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        {{ $value->category_name }}
                    </a>
                    <div class="dropdown-menu">
                        @foreach(App\Subcategory::where('status_id', 1)->where('category_id', $value->id)->get() as $sub)
                          <a class="dropdown-item" href="blog-right.html">{{ $sub->category_name }} </a>
                        @endforeach
                    </div>
                </li>
                @endforeach
            </ul>


            @if (Auth::check())
                <div class="my-2 my-lg-0">
                <ul class="list-inline main-nav-right">
                    <li class="list-inline-item dropdown osahan-top-dropdown">
                        <a class="btn btn-theme-round dropdown-toggle" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icofont icofont-shopping-cart"></i> Cart
                            @php $total_cart = App\Cart::where('user_id', Auth::id())->count(); @endphp


                            <small class="cart-value">{{ $total_cart }}</small>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right cart-dropdown">
                            @foreach(App\Cart::where('user_id', Auth::id())->get() as $value)
                            <div class="dropdown-item">
                                <a href="#">
                                    <img class="img-fluid" src="{{ $value->product->product_image }}" alt="Product">
                                    <strong>{{ Str::limit($value->product->product_name, 30) }}</strong>
                                    <small>Weight : {{ $value->product->product_weight }} Grams</small>
                                </a>
                            </div>
                            @endforeach



                            <div class="dropdown-divider"></div>
                            <div class="dropdown-cart-footer text-center">
                                <a class="btn btn-sm btn-danger" href="{{url('ecommerce/view-cart')}}"> <i class="icofont icofont-shopping-cart"></i> VIEW
                                    CART </a> <a href="{{url('ecommerce/checkout')}}" class="btn btn-sm btn-primary"> CHECKOUT </a>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
            @else
                <div class="my-2 my-lg-0">
                    <ul class="list-inline main-nav-right">
                        <li class="list-inline-item dropdown osahan-top-dropdown">
                            <a class="btn btn-theme-round dropdown-toggle" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="icofont icofont-shopping-cart"></i> Cart


                                <small class="cart-value">0</small>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right cart-dropdown">

                            </div>
                        </li>
                    </ul>
                </div>
            @endif
        </div>
    </div>
</nav>

<div class="osahan-breadcrumb">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('')}}"><i class="icofont icofont-ui-home"></i> Home</a></li>
                    <li class="breadcrumb-item active">{{$page_title}}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

@yield('content')

<footer>
    <section class="footer-Content">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-3 col-sm-3">
                    <div class="footer-widget">
                        <h3 class="block-title">About</h3>
                        <div class="textwidget">
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque lobortis tincidunt est, et euismod purus suscipit quis. Etiam euismod ornare elementum. Sed ex est, Sed ex est, consectetur eget consectetur, Lorem ipsum dolor sit amet...</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-3">
                    <div class="footer-widget">
                        <h3 class="block-title">Categories</h3>
                        <ul class="menu">
                            <li><a href="#"><span>562</span> Footwear </a></li>
                            <li><a href="#"><span>451</span> Luggage </a></li>
                            <li><a href="#"><span>352</span> Clothing </a></li>
                            <li><a href="#"><span>312</span> Eyewear </a></li>
                            <li><a href="#"><span>262</span> Watches</a></li>
                            <li><a href="#"><span>152</span> Jewellery </a></li>
                            <li><a href="#"><span>352</span> Clothing </a></li>
                            <li><a href="#"><span>312</span> Eyewear </a></li>
                            <li><a href="#"><span>262</span> Watches</a></li>
                            <li><a href="#"><span>152</span> Jewellery </a></li>
                            <li><a href="#"><span>562</span> Footwear </a></li>
                            <li><a href="#"><span>451</span> Bags</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-3">
                    <div class="footer-widget">
                        <h3 class="block-title">Latest Post</h3>
                        <ul class="blog-footer">
                            <li>
                                <a href="#">Lorem ipsum dolor sit amet, quem...</a>
                                <span class="post-date"><i class="fa fa-calendar" aria-hidden="true"></i> March 12, 2017</span>
                            </li>
                            <li>
                                <a href="#">Full Width Media Post Lorem ipsum..</a>
                                <span class="post-date"><i class="fa fa-calendar" aria-hidden="true"></i> September 25, 2017</span>
                            </li>
                            <li>
                                <a href="#">Perfect Video Post Lorem ipsum..</a>
                                <span class="post-date"><i class="fa fa-calendar" aria-hidden="true"></i> November 19, 2017</span>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-3">
                    <div class="footer-widget">
                        <h3 class="block-title">Quick Links</h3>
                        <ul class="menu">
                            <li><a href="#">Home</a></li>
                            <li><a href="#">About</a></li>
                            <li><a href="#">FAQ</a></li>
                            <li><a href="#">Careers</a></li>
                            <li><a href="#">Discount</a></li>
                            <li><a href="#">Categories</a></li>
                            <li><a href="#">Retunrs</a></li>
                            <li><a href="#">Team</a></li>
                            <li><a href="#">Contact</a></li>
                            <li><a href="#">Blog</a></li>
                            <li><a href="#">Help</a></li>
                            <li><a href="#">Advertise With Us</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
</footer>
<section class="footer">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-md-8 col-sm-8">
                <div class="footer-logo pull-left hidden-xs">
                    <img alt="" src="https://askbootstrap.com/preview/osahan-fashion/images/footer-logo.png" class="img-responsive">
                </div>
                <div class="footer-links">
                    <ul>
                        <li><a href="#">Home</a></li>
                        <li><a href="#">New Collection</a></li>
                        <li><a href="#">Mens Collection</a></li>
                        <li><a href="#">Women Dresses</a></li>
                        <li><a href="#">Kids Collection</a></li>
                    </ul>
                </div>
                <div class="copyright">
                    <p>© Copyright 2021 {{$company_name}}.&nbsp; | &nbsp;Made with <i class="fa fa-heart"></i>
                        by
                        <a target="_blank" href="{{url('')}}">
                            <strong>{{$company_name}}</strong>
                        </a>
                    </p>
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-4 text-right">
                <div class="social-icon">
                    <a href="#"><i class="fa fa-facebook"></i></a>
                    <a href="#"><i class="fa fa-twitter"></i></a>
                    <a href="#"><i class="fa fa-linkedin"></i></a>
                    <a href="#"><i class="fa fa-instagram"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>


<script src="{{url('ecommerce/js/jquery.min.js')}}" type="9e2118a0bd50bb7abc3ba2aa-text/javascript"></script>
<script src="{{url('ecommerce/js/popper.min.js')}}" type="9e2118a0bd50bb7abc3ba2aa-text/javascript"></script>
<script src="{{url('ecommerce/js/tether.min.js')}}" type="9e2118a0bd50bb7abc3ba2aa-text/javascript"></script>
<script src="{{url('ecommerce/js/bootstrap.min.js')}}" type="9e2118a0bd50bb7abc3ba2aa-text/javascript"></script>

<script src="{{url('ecommerce/js/custom.js')}}" type="9e2118a0bd50bb7abc3ba2aa-text/javascript"></script>

<link href="{{url('ecommerce/css/select2.min.css')}}" rel="stylesheet" />
<script src="{{url('ecommerce/js/select2.min.js')}}" type="9e2118a0bd50bb7abc3ba2aa-text/javascript"></script>

<script src="{{url('ecommerce/js/owl.carousel.js')}}" type="9e2118a0bd50bb7abc3ba2aa-text/javascript"></script>
<script src="https://ajax.cloudflare.com/cdn-cgi/scripts/7d0fa10a/cloudflare-static/rocket-loader.min.js" data-cf-settings="9e2118a0bd50bb7abc3ba2aa-|49" defer=""></script>
<script defer src="https://static.cloudflareinsights.com/beacon.min.js" data-cf-beacon='{"rayId":"663e4c5b7f562e34","version":"2021.5.2","r":1,"token":"dd471ab1978346bbb991feaa79e6ce5c","si":10}'></script>
</body>
</html>



