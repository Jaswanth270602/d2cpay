<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="Description" content="">
    <meta name="Author" content="">
    <meta name="Keywords" content="" />

    <title> {{ $company_name }} </title>

    <link rel="icon" href="https://cdn.bceres.com/admin2020/assets/img/brand/favicon.png" type="image/x-icon" />

    <link href="{{url('assets/css/icons.css')}}" rel="stylesheet">
    <link href="{{url('assets/plugins/select2/css/select2.min.css')}}" rel="stylesheet">
    <link href="{{url('assets/plugins/sweet-alert/sweetalert.css')}}" rel="stylesheet">


    <link href="{{url('assets/plugins/datatable/css/dataTables.bootstrap4.min.css')}}" rel="stylesheet">
    <link href="{{url('assets/plugins/datatable/css/buttons.bootstrap4.min.css')}}" rel="stylesheet">
    <link href="{{url('assets/plugins/datatable/css/responsive.bootstrap4.min.css')}}" rel="stylesheet">
    <link href="{{url('assets/plugins/datatable/css/jquery.dataTables.min.css')}}" rel="stylesheet">
    <link href="{{url('assets/plugins/datatable/css/responsive.dataTables.min.css')}}" rel="stylesheet">


    <link href="{{url('assets/plugins/owl-carousel/owl.carousel.css')}}" rel="stylesheet"/>

    <link href="{{url('assets/plugins/sidebar/sidebar.css')}}" rel="stylesheet">

    <link href="{{url('assets/css/style.css')}}" rel="stylesheet">
    <link href="{{url('assets/css/skin-modes.css')}}" rel="stylesheet">

    <link href="{{url('assets/css/animate.css')}}" rel="stylesheet">

    <style>
        .custom-navbar {
            background-color: #2c3e50; /* A dark blue-gray color */
        }
    </style>

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
    {!! $chat_script !!}
    @include('agent.layout.style')
</head>

<body class="main-body app dashboard-agent">


<script type="text/javascript">
    $(document).ready(function () {
        $.ajax({
            url: "{{url('admin/dashboard-data-api')}}",
            success: function (msg) {
                if (msg.status == 'success') {
                    $("#dashboard_api_balance").text(msg.balance.api_balance);
                    $("#dashboard_today_sale").text(msg.sales.today_sale);
                    $("#dashboard_today_profit").text(msg.sales.today_profit);

                }

            }
        });
        getWalletBal();
    });

    function getWalletBal() {
        var token = $("input[name=_token]").val();
        var dataString = '_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/get-wallet-balance')}}",
            data: dataString,
            success: function (msg) {
                if (msg.status == 'success') {
                    $(".normal_balance").text(msg.data.normal_balance);
                    $(".aeps_balance").text(msg.data.aeps_balance);
                }
            }
        });
    }
</script>


<div class="loader" style="display: none;"></div>

{{--Header top--}}
    @include('agent.layout.header_top')
</div>
</div>
<div class="sticky">
    <div class="horizontal-main hor-menu clearfix side-header custom-navbar">
        <div class="horizontal-mainwrapper container clearfix">
            <nav class="horizontalMenu clearfix">
                <ul class="horizontalMenu-list d-flex">
                  <!--  <li aria-haspopup="true"><a href="{{url('agent/dashboard')}}" class=""><i class="fe fe-airplay menu-icon"></i> Dashboard</a></li>
                    <li aria-haspopup="true"><a href="{{url('agent/payout/v2/bulk-upload')}}" class=""><i class="fas fa-rupee-sign menu-icon"></i> Bulk Payout</a></li>
-->
 <li aria-haspopup="true">
    <a href="{{ url('agent/dashboard') }}" class="">
        <i class="fe fe-airplay menu-icon text-primary"></i> Dashboard
    </a>
</li>

<li aria-haspopup="true">
    <a href="{{ url('agent/payout/v2/bulk-upload') }}" class="">
        <i class="fas fa-rupee-sign menu-icon text-success"></i> Bulk Payout
    </a>
</li>



                    @php
                        $library = new \App\Library\BasicLibrary;
                        $companyActiveService = $library->getCompanyActiveService(Auth::id());
                        $userActiveService = $library->getUserActiveService(Auth::id());
                    @endphp
                    <li aria-haspopup="true"><a href="#" class="sub-icon"><i class="fas fa-table text-warning"></i> Report <i class="fe fe-chevron-down horizontal-icon"></i></a>
                        <ul class="sub-menu">
                            <li aria-haspopup="true"><a href="{{url('agent/report/v1/all-transaction-report')}}" class="slide-item"> All Transaction Report</a></li>
                            @foreach(App\Models\Servicegroup::where('status_id', 1)->select('id', 'group_name')->get() as $value)
                           <li aria-haspopup="true" class="sub-menu-sub"><a href="#">{{ $value->group_name }}</a>
                                <ul class="sub-menu">
                                    @foreach(App\Models\Service::where('servicegroup_id', $value->id)->whereIn('id', $companyActiveService)->whereIn('id', $userActiveService)->where('status_id', 1)->select('id', 'service_name', 'report_slug')->get() as $serv)
                                        <li aria-haspopup="true"><a href="{{url('agent/report/v1/welcome')}}/{{ $serv->report_slug }}" class="slide-item">{{ $serv->service_name }} History</a></li>
                                    @endforeach
                                </ul>
                            </li>
                            @endforeach

                            <li aria-haspopup="true"><a href="{{url('agent/report/v1/ledger-report')}}" class="slide-item"> Ledger Report</a></li>

                            <li aria-haspopup="true" class="sub-menu-sub"><a href="#">Sales & Income</a>
                                <ul class="sub-menu">
                                    <li aria-haspopup="true"><a href="{{url('agent/operator-report')}}" class="slide-item">Operator Wise Sales</a></li>
                                    <li aria-haspopup="true"><a href="{{url('agent/income-report')}}" class="slide-item">Income</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>

                    <li aria-haspopup="true"><a href="#" class="sub-icon"><i class="fas fa-rupee-sign text-info"></i> Payment <i class="fe fe-chevron-down horizontal-icon"></i></a>
                        <ul class="sub-menu">
                            <li aria-haspopup="true"><a href="{{url('agent/payment-request')}}" class="">Payment Request</a></li>
                            @if(Auth::User()->company->cashfree == 1 && Auth::User()->profile->cashfree == 1)
                                <li aria-haspopup="true"><a href="{{url('agent/add-money/v1/welcome')}}" class="slide-item"> Add Money</a></li>
                            @endif
                        </ul>
                    </li>

                   <!-- @if(Auth::User()->company->ecommerce == 1 && Auth::User()->profile->ecommerce == 1)
                        <li aria-haspopup="true"><a href="{{url('agent/ecommerce/welcome')}}" class=""><i class="fas fa-shopping-cart"></i> Shop </a></li>
                    @endif
                    
                    <li aria-haspopup="true" class="btn btn-success ml-auto" BLUR style="margin-right:10px;">
                        <a href="#" style="color: white;">
                            Payout Bal : <span class="normal_balance"></span>
                        </a>
                    </li>
                    
                    <li aria-haspopup="true" class="btn btn-warning">
                        <a href="#" style="color: white;">
                            Payin Bal : <span class="aeps_balance"></span>
                        </a>
                    </li>-->
                   @if(Auth::User()->company->ecommerce == 1 && Auth::User()->profile->ecommerce == 1)
    <li aria-haspopup="true">
        <a href="{{ url('agent/ecommerce/welcome') }}" class="btn btn-info" style="color: white; margin-right: 10px;">
            <i class="fas fa-shopping-cart"></i> Shop
        </a>
    </li>
@endif

<!-- Buttons aligned right -->
<div style="margin-left: auto; display: flex; gap: 10px;">
    <!-- Payout Button (Orange) -->
    <li aria-haspopup="true" class="btn" style="background-color: #ff9800;">
        <a href="#" style="color: white;">
            Payout Bal : <span class="normal_balance"></span>
        </a>
    </li>

    <!-- Payin Button (Tint / Teal) -->
    <li aria-haspopup="true" class="btn" style="background-color: #4dd0e1;">
        <a href="#" style="color: white;">
            Payin Bal : <span class="aeps_balance"></span>
        </a>
    </li>
</div>


                </ul>
            </nav>
            </div>
    </div>
</div>
<div class="main-content horizontal-content">

    <div class="container">

        <div class="breadcrumb-header justify-content-between">
            <div>
                <h4 class="content-title mb-2">Hi, {{ Auth::User()->name }} welcome back!</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{url('admin/dashboard')}}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $page_title }}</li>
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
    </div>
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
    </div>
<div class="main-footer ht-40">
    <div class="container-fluid pd-t-0-f ht-100p">
        <span>Copyright © 2020 <a href="#">{{ $company_name }}</a>. All rights reserved.</span>
    </div>
</div>
<a href="#top" id="back-to-top"><i class="las la-angle-double-up"></i></a>

<script src="{{url('assets/plugins/jquery/jquery.min.js')}}"></script>
<script src="{{url('assets/plugins/jquery-ui/ui/widgets/datepicker.js')}}"></script>
<script src="{{url('assets/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
<script src="{{url('assets/plugins/ionicons/ionicons.js')}}"></script>
<script src="{{url('assets/plugins/select2/js/select2.min.js')}}"></script>
<script src="{{url('assets/plugins/chart.js/Chart.bundle.min.js')}}"></script>
<script src="{{url('assets/plugins/chart.js/excanvas.js')}}"></script>
<script src="{{url('assets/plugins/chart.js/utils.js')}}"></script>
<script src="{{url('assets/js/index.js')}}"></script>
<script src="{{url('assets/plugins/jquery-sparkline/jquery.sparkline.min.js')}}"></script>
<script src="{{url('assets/js/chart.flot.sampledata.js')}}"></script>
<script src="{{url('assets/plugins/rating/jquery.rating-stars.js')}}"></script>
<script src="{{url('assets/plugins/rating/jquery.barrating.js')}}"></script>
<script src="{{url('assets/plugins/horizontal-menu/horizontal-menu.js')}}"></script>
<script src="{{url('assets/js/eva-icons.min.js')}}"></script>
<script src="{{url('assets/plugins/moment/moment.js')}}"></script>
<script src="{{url('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
<script src="{{url('assets/plugins/datatable/js/dataTables.dataTables.min.js')}}"></script>
<script src="{{url('assets/plugins/datatable/js/dataTables.responsive.min.js')}}"></script>
<script src="{{url('assets/plugins/datatable/js/responsive.dataTables.min.js')}}"></script>
<script src="{{url('assets/plugins/datatable/js/jquery.dataTables.js')}}"></script>
<script src="{{url('assets/plugins/datatable/js/dataTables.bootstrap4.js')}}"></script>
<script src="{{url('assets/plugins/datatable/js/dataTables.buttons.min.js')}}"></script>
<script src="{{url('assets/plugins/datatable/js/dataTables.responsive.min.js')}}"></script>
<script src="{{url('assets/plugins/datatable/js/responsive.bootstrap4.min.js')}}"></script>
<script src="{{url('assets/js/table-data.js')}}"></script>
<script src="{{url('assets/plugins/perfect-scrollbar/perfect-scrollbar.min.js')}}"></script>
<script src="{{url('assets/plugins/perfect-scrollbar/p-scroll.js')}}"></script>
<script src="{{url('assets/js/sticky.js')}}"></script>
<script src="{{url('assets/plugins/sidebar/sidebar.js')}}"></script>
<script src="{{url('assets/plugins/sidebar/sidebar-custom.js')}}"></script>
<script src="{{url('assets/js/script.js')}}"></script>
<script src="{{url('assets/js/custom.js')}}"></script>
<script src="{{url('assets/plugins/sweet-alert/sweetalert.min.js')}}"></script>
<script src="{{url('assets/plugins/sweet-alert/jquery.sweet-alert.js')}}"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<script>
    $(document).ready(function () {
        $("#search_text").autocomplete({
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