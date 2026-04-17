
<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="Description" content="">
    <meta name="Author" content="">
    <meta name="Keywords" content=""/>

    <!-- Title -->
    <title> {{ $company_name }} </title>

    <!--- Favicon --->
    <link rel="icon" href="https://cdn.bceres.com/admin2020/assets/img/brand/favicon.png" type="image/x-icon"/>

    <!--- Icons css --->
    <link href="{{url('assets/css/icons.css')}}" rel="stylesheet">
    <link href="{{url('assets/plugins/select2/css/select2.min.css')}}" rel="stylesheet">
    <link href="{{url('assets/plugins/sweet-alert/sweetalert.css')}}" rel="stylesheet">


    <link href="{{url('assets/plugins/datatable/css/dataTables.bootstrap4.min.css')}}" rel="stylesheet">
    <link href="{{url('assets/plugins/datatable/css/buttons.bootstrap4.min.css')}}" rel="stylesheet">
    <link href="{{url('assets/plugins/datatable/css/responsive.bootstrap4.min.css')}}" rel="stylesheet">
    <link href="{{url('assets/plugins/datatable/css/jquery.dataTables.min.css')}}" rel="stylesheet">
    <link href="{{url('assets/plugins/datatable/css/responsive.dataTables.min.css')}}" rel="stylesheet">


    <!-- Owl-carousel css-->
    <link href="{{url('assets/plugins/owl-carousel/owl.carousel.css')}}" rel="stylesheet"/>

    <!--- Right-sidemenu css --->
    <link href="{{url('assets/plugins/sidebar/sidebar.css')}}" rel="stylesheet">

    <!--- Style css --->

    <link href="{{url('assets/css/style.css')}}" rel="stylesheet">
    <link href="{{url('assets/css/skin-modes.css')}}" rel="stylesheet">

    <!--- Animations css --->
    <link href="{{url('assets/css/animate.css')}}" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>


    <script type="text/javascript">
        var session_id = "{!! (Session::getId())?Session::getId():'' !!}";
        var user_id = "{!! (Auth::user())?Auth::user()->id:'' !!}";

        // Your web app's Firebase configuration
        var firebaseConfig = {
            apiKey: "FIREBASE_API_KEY",
            authDomain: "FIREBASE_AUTH_DOMAIN",
            databaseURL: "FIREBASE_DATABASE_URL",
            storageBucket: "FIREBASE_STORAGE_BUCKET",
        };
        // Initialize Firebase
        firebase.initializeApp(firebaseConfig);

        var database = firebase.database();



        firebase.database().ref('/users/' + user_id).on('value', function (snapshot2) {
            var v = snapshot2.val();

            if (v.session_id !== session_id) {

                console.log("Your account login from another device!!");

                setTimeout(function () {
                    window.location = '/login';
                }, 4000);
            }
        });
    </script>
    @include('agent.layout.style')
</head>

<body class="main-body  app">


<script type="text/javascript">
    $( document ).ready(function() {
        $.ajax({
            url: "{{url('admin/dashboard-data-api')}}",
            success: function(msg){
                if (msg.status == 'success'){
                    $("#dashboard_api_balance").text(msg.balance.api_balance);

                    $("#dashboard_today_sale").text(msg.sales.today_sale);
                    $("#dashboard_today_profit").text(msg.sales.today_profit);

                }

            }});
    });
</script>


<div class="loader" style="display: none;"></div>

{{--Header top--}}
@include('agent.layout.header_top')
</div>
</div>
<!-- main-header closed -->

<!--Horizontal-main -->
<div class="sticky">
    <div class="horizontal-main hor-menu clearfix side-header">
        <div class="horizontal-mainwrapper container clearfix">
            <!--Nav-->
            <nav class="horizontalMenu clearfix">
                <ul class="horizontalMenu-list">
                    <li aria-haspopup="true"><a href="{{url('agent/dashboard')}}" class=""><i class="fe fe-airplay  menu-icon"></i> Dashboard</a></li>

                    @foreach(App\Category::where('status_id', 1)->get() as $value)
                        <li aria-haspopup="true"><a href="#" class="sub-icon"> {!! $value->font_icon !!} {{ $value->category_name }} <i class="fe fe-chevron-down horizontal-icon"></i></a>
                            <ul class="sub-menu">
                                @foreach(App\Subcategory::where('status_id', 1)->where('category_id', $value->id)->get() as $sub)
                                    <li aria-haspopup="true"><a href="{{url('agent/ecommerce/page')}}/{{$sub->slug}}" class="slide-item">{{ $sub->category_name }}</a></li>
                                @endforeach
                             </ul>
                        </li>
                    @endforeach


                    <li aria-haspopup="true"><a href="#" class="sub-icon"><i class="fas fa-table  menu-icon"></i> Reports <i class="fe fe-chevron-down horizontal-icon"></i></a>
                        <ul class="sub-menu">
                            <li aria-haspopup="true"><a href="{{url('agent/ecommerce/my-orders')}}" class="slide-item">My Orders</a></li>
                            <li aria-haspopup="true"><a href="{{url('agent/ecommerce/track-orders')}}" class="slide-item">Track Orders</a></li>
                        </ul>
                    </li>

                </ul>
            </nav>
            <!--Nav-->
        </div>
    </div>
</div>
<!--Horizontal-main -->

<!-- main-content opened -->
<div class="main-content horizontal-content">

    <!-- container opened -->
    <div class="container">

        <!-- breadcrumb -->
        <div class="breadcrumb-header justify-content-between">
            <div>
                <h4 class="content-title mb-2">Hi, {{ Auth::User()->name }} welcome back!</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{url('admin/dashboard')}}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $page_title  }}</li>
                    </ol>
                </nav>

            </div>



            <div class="d-flex my-auto">
                <div class=" d-flex right-page">
                    <div class="d-flex justify-content-center mr-5">
                        <div class="">
									<span class="d-block">
										<span class="label">Today Sale</span>
									</span>
                            <span class="value" id="dashboard_today_sale"></span>
                        </div>
                        <div class="ml-3 mt-2">
                            <span class="sparkline_bar"></span>
                        </div>
                    </div>
                    <div class="d-flex justify-content-center">
                        <div class="">
									<span class="d-block">
										<span class="label">Today Profit</span>
									</span>
                            <span class="value" id="dashboard_today_profit"></span>
                        </div>
                        <div class="ml-3 mt-2">
                            <span class="sparkline_bar31"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /breadcrumb -->

        @if(Auth::User()->active == 0)
            <div class="alert alert-danger" role="alert">
                <strong>Alert </strong> {{Auth::User()->reason}}
            </div>
        @endif

        @if(Auth::User()->mobile_verified == 1 && Auth::User()->active != 0)
            @yield('content')
        @else
            @include('agent.layout.profile_verify')
        @endif



    <!--Sidebar-right-->
        <div class="sidebar sidebar-right sidebar-animate">
            <div class="panel panel-primary card mb-0">
                <div class="panel-body tabs-menu-body p-0 border-0">
                    <ul class="Date-time">
                        <li class="time">
                            <h1 class="animated ">21:00</h1>
                            <p class="animated ">Sat,October 1st 2029</p>
                        </li>
                    </ul>
                    <div class="card-body latest-tasks">

                        <div class="task-stat pb-0">
                            <div class="d-flex tasks">
                                <div class="mb-0">
                                    <div class="h6 fs-15 mb-0">Normal Balance</div>
                                </div>
                                <span class="float-right ml-auto">{{number_format(Auth::user()->balance->user_balance,2)}}</span>
                            </div>

                            <div class="d-flex tasks">
                                <div class="mb-0">
                                    <div class="h6 fs-15 mb-0">Lien Balance</div>
                                </div>
                                <span class="float-right ml-auto">{{number_format(Auth::user()->balance->lien_amount,2)}}</span>
                            </div>

                            <div class="d-flex tasks">
                                <div class="mb-0">
                                    <div class="h6 fs-15 mb-0">Sms Balance</div>
                                </div>
                                <span class="float-right ml-auto">{{number_format(Auth::user()->balance->sms_balance,2)}}</span>
                            </div>
                            <div class="d-flex tasks">
                                <div class="mb-0">
                                    <div class="h6 fs-15 mb-0">Aeps Balance</div>
                                </div>
                                <span class="float-right ml-auto">{{number_format(Auth::user()->balance->aeps_balance,2)}}</span>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
        <!--/Sidebar-right-->



        <!-- Footer opened -->
        <div class="main-footer ht-40">
            <div class="container-fluid pd-t-0-f ht-100p">
                <span>Copyright © 2020 <a href="#">{{ $company_name }}</a>.  All rights reserved.</span>
            </div>
        </div>
        <!-- Footer closed -->



        <!--- Back-to-top --->
        <a href="#top" id="back-to-top"><i class="las la-angle-double-up"></i></a>

        <!--- JQuery min js --->
        <script src="{{url('assets/plugins/jquery/jquery.min.js')}}"></script>

        <!--- Datepicker js --->
        <script src="{{url('assets/plugins/jquery-ui/ui/widgets/datepicker.js')}}"></script>

        <!--- Bootstrap Bundle js --->
        <script src="{{url('assets/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>

        <!--- Ionicons js --->
        <script src="{{url('assets/plugins/ionicons/ionicons.js')}}"></script>

        <script src="{{url('assets/plugins/select2/js/select2.min.js')}}"></script>


        <!--- Chart bundle min js --->
        <script src="{{url('assets/plugins/chart.js/Chart.bundle.min.js')}}"></script>
        <script src="{{url('assets/plugins/chart.js/excanvas.js')}}"></script>
        <script src="{{url('assets/plugins/chart.js/utils.js')}}"></script>



        <!--- Index js --->
        <script src="{{url('assets/js/index.js')}}"></script>

        <!--- JQuery sparkline js --->
        <script src="{{url('assets/plugins/jquery-sparkline/jquery.sparkline.min.js')}}"></script>

        <!--- Internal Sampledata js --->
        <script src="{{url('assets/js/chart.flot.sampledata.js')}}"></script>

        <!--- Rating js --->
        <script src="{{url('assets/plugins/rating/jquery.rating-stars.js')}}"></script>
        <script src="{{url('assets/plugins/rating/jquery.barrating.js')}}"></script>

        <!--- Horizontalmenu js --->
        <script src="{{url('assets/plugins/horizontal-menu/horizontal-menu.js')}}"></script>

        <!--- Eva-icons js --->
        <script src="{{url('assets/js/eva-icons.min.js')}}"></script>

        <!--- Moment js --->
        <script src="{{url('assets/plugins/moment/moment.js')}}"></script>


        <script src="{{url('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
        <script src="{{url('assets/plugins/datatable/js/dataTables.dataTables.min.js')}}"></script>
        <script src="{{url('assets/plugins/datatable/js/dataTables.responsive.min.js')}}"></script>
        <script src="{{url('assets/plugins/datatable/js/responsive.dataTables.min.js')}}"></script>
        <script src="{{url('assets/plugins/datatable/js/jquery.dataTables.js')}}"></script>
        <script src="{{url('assets/plugins/datatable/js/dataTables.bootstrap4.js')}}"></script>
        <script src="{{url('assets/plugins/datatable/js/dataTables.buttons.min.js')}}"></script>

        {{--  <script src="{{url('assets/plugins/datatable/js/jszip.min.js')}}"></script>
          <script src="{{url('assets/plugins/datatable/js/pdfmake.min.js')}}"></script>
          <script src="{{url('assets/plugins/datatable/js/vfs_fonts.js')}}"></script>
          <script src="{{url('assets/plugins/datatable/js/buttons.html5.min.js')}}"></script>
          <script src="{{url('assets/plugins/datatable/js/buttons.print.min.js')}}"></script>--}}



        <script src="{{url('assets/plugins/datatable/js/dataTables.responsive.min.js')}}"></script>
        <script src="{{url('assets/plugins/datatable/js/responsive.bootstrap4.min.js')}}"></script>
        <script src="{{url('assets/js/table-data.js')}}"></script>


        <!--- Perfect-scrollbar js --->
        <script src="{{url('assets/plugins/perfect-scrollbar/perfect-scrollbar.min.js')}}"></script>
        <script src="{{url('assets/plugins/perfect-scrollbar/p-scroll.js')}}"></script>

        <!--- Sticky js --->
        <script src="{{url('assets/js/sticky.js')}}"></script>

        <!--- Right-sidebar js --->
        <script src="{{url('assets/plugins/sidebar/sidebar.js')}}"></script>
        <script src="{{url('assets/plugins/sidebar/sidebar-custom.js')}}"></script>

        <!--- Scripts js --->
        <script src="{{url('assets/js/script.js')}}"></script>

        <!--- Custom js --->
        <script src="{{url('assets/js/custom.js')}}"></script>
        <script src="{{url('assets/plugins/sweet-alert/sweetalert.min.js')}}"></script>
        <script src="{{url('assets/plugins/sweet-alert/jquery.sweet-alert.js')}}"></script>

        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

        <script>
            $(document).ready(function () {
                $( "#search_text" ).autocomplete({
                    source: function (request, response) {
                        $.ajax({
                            type: "GET",
                            url: "{{url('agent/ecommerce/searchProductAjax')}}",
                            data: {
                                term:request.term
                            },
                            dataType: 'json',
                            success: function (data) {
                                response(data);
                            }
                        });
                    },
                    minLength: 1,
                });

                $(document).on('click', '.ui-menu-item', function () {
                    $('#search-form').submit();
                });
            });

        </script>


@csrf

</body>
</html>
