<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>{{ $company_name }}</title>
    <!-- web fonts -->
    <link href="//fonts.googleapis.com/css?family=Karla:400,700&display=swap" rel="stylesheet">
    <!-- //web fonts -->
    <!-- Template CSS -->
    <link rel="stylesheet" href="{{url('front/css/style-freedom.css')}}">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
<script src='//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js'></script>
<script src="//m.servedby-buysellads.com/monetization.js" type="text/javascript"></script>

<script src="https://codefund.io/properties/441/funder.js" async="async"></script>

<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src='https://www.googletagmanager.com/gtag/js?id=UA-149859901-1'></script>

<meta name="robots" content="noindex">
<body>

@if (Auth::guest())

@else
    @if(Auth::user()->role_id <= 7)
        <script type="text/javascript">
            document.location.href = "admin/dashboard";
        </script>
    @else
        <script type="text/javascript">
            document.location.href = "agent/dashboard";
        </script>
    @endif
@endif
{!! $chat_script !!}

<link rel="stylesheet" href="{{url('front/css/demobar_w3_4thDec2019.css')}}">

<!-- //Top Menu 1 -->
<section class="bg-dark">
    <nav class="navbar navbar-expand-lg navbar-light py-lg-2 py-2">
        <div class="container">
            <a class="navbar-brand" href="{{url('')}}"><img src="{{$cdnLink}}{{ $company_logo }}" style="height: 60px;"></a>
            <!-- if logo is image enable this
            <a class="navbar-brand" href="#index.html">
                <img src="image-path" alt="Your logo" title="Your logo" style="height:35px;" />
            </a> -->
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                    aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon fa fa-bars"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link text-white fw-semibold" href="{{url('')}}">Home</a></li>
                    @foreach(App\Models\Navigation::where('status_id', 1)->where('company_id', $company_id)->where('type', 1)->get() as $value)
                    <li class="nav-item"><a class="nav-link text-white fw-semibold" href="{{url('pages')}}/{{$company_id}}/{{ $value->navigation_slug}}">{{ $value->navigation_name }}</a></li>
                    @endforeach

                    <li class="nav-item">
                        <a class="nav-link text-white fw-semibold" href="{{url('contact-us')}}">Contact</a>
                    </li>
                </ul>
                <form class="form-inline">
                    <a class="btn btn-secondary btn-theme" href="{{url('login')}}"> Login</a>
                </form>
                   &nbsp; &nbsp;
                @if($registration_status == 1)
                <form class="form-inline">
                    <a class="btn btn-primary btn-theme" href="{{url('sign-up')}}"> Register</a>
                </form>
                @endif
            </div>
        </div>
    </nav>
</section>

@yield('content')

<!-- grids block 5 -->
<section class="w3l-footer-29-main">
    <div class="footer-29">
        <div class="container">
            <div class="d-grid grid-col-4 footer-top-29">
                <div class="footer-list-29 footer-1">
                    <h6 class="footer-title-29"><a href="{{url('')}}"><img src="{{$cdnLink}}{{ $company_logo }}" style="height: 60px;"></a></h6>

                   {{-- <div class="main-social-footer-29">
                        <a href="#facebook" class="facebook"><span class="fa fa-facebook"></span></a>
                        <a href="#twitter" class="twitter"><span class="fa fa-twitter"></span></a>
                        <a href="#instagram" class="instagram"><span class="fa fa-instagram"></span></a>
                        <a href="#google-plus" class="google-plus"><span class="fa fa-google-plus"></span></a>
                        <a href="#linkedin" class="linkedin"><span class="fa fa-linkedin"></span></a>
                    </div>--}}
                </div>
                <div class="footer-list-29 footer-2">
                    <ul>
                        <h6 class="footer-title-29">Features</h6>

                    </ul>
                </div>

                <div class="footer-list-29 footer-3">

                    <h6 class="footer-title-29">Newsletter </h6>
                    <form action="#" class="subscribe" method="post">
                        <input type="email" name="email" placeholder="Email" required="">
                        <button><span class="fa fa-envelope-o"></span></button>
                    </form>
                    <p>Subscribe and get our weekly newsletter</p>
                    <p>We'll never share your email address</p>

                </div>
                <div class="footer-list-29 footer-4">
                    <ul>
                        <h6 class="footer-title-29">Quick Links</h6>
                        @foreach(App\Models\Navigation::where('status_id', 1)->where('company_id', $company_id)->where('type', 2)->get() as $value)
                        <li><a href="{{url('pages')}}/{{$company_id}}/{{ $value->navigation_slug}}">{{ $value->navigation_name }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="d-grid grid-col-2 bottom-copies">
                <p class="copy-footer-29">© 2020 {{ $company_name }}. All rights reserved </p>
                <ul class="list-btm-29">
                    @foreach(App\Models\Navigation::where('status_id', 1)->where('company_id', $company_id)->where('type',2)->get() as $value)
                    <li><a href="{{url('pages')}}/{{$company_id}}/{{ $value->navigation_slug}}">{{ $value->navigation_name }}</a></li>
                    @endforeach

                </ul>
            </div>
        </div>
    </div>
    <!-- move top -->
    <button onclick="topFunction()" id="movetop" title="Go to top">
        <span class="fa fa-angle-up"></span>
    </button>
    <style>
        body {
            padding-top:10px !important;
        }
    </style>
    <script>
        // When the user scrolls down 20px from the top of the document, show the button
        window.onscroll = function () {
            scrollFunction()
        };

        function scrollFunction() {
            if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
                document.getElementById("movetop").style.display = "block";
            } else {
                document.getElementById("movetop").style.display = "none";
            }
        }

        // When the user clicks on the button, scroll to the top of the document
        function topFunction() {
            document.body.scrollTop = 0;
            document.documentElement.scrollTop = 0;
        }
    </script>
    <!-- /move top -->
</section>
<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>

<!-- //footer-28 block -->
</section>
<script>
    $(function () {
        $('.navbar-toggler').click(function () {
            $('body').toggleClass('noscroll');
        })
    });
</script>
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"
        integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous">
</script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
        integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous">
</script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"
        integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous">
</script>

<!-- Template JavaScript -->
<script src="{{url('front/js/all.js')}}"></script>
<!-- Smooth scrolling -->
<!-- <script src="assets/js/smoothscroll.js"></script> -->


</body>

</html>
<!-- // grids block 5 -->

