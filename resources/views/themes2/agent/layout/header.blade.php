<!DOCTYPE html>
<html lang="en">
<head>
    <title> {{ $company_name }} </title>
    <!-- initiate head with meta tags, css and script -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">


    <link rel="icon" type="image/x-icon" href="{{url('assets/img/favicon.ico')}}"/>

    <!-- fonts library -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700&display=swap"
          rel="stylesheet">

    <script src="{{ $cdnLink}}themes2/assets/js/app.js"></script>

    <link href="{{url('assets/css/icons.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="{{ $cdnLink}}themes2/assets/css/all.css">
    <link rel="stylesheet" href="{{ $cdnLink}}themes2/plugins/line-awesome-1.3.0/css/line-awesome.min.css">

    <!-- Stack array for including inline css or head elements -->
    {{--<link href="{{url('assets/plugins/select2/css/select2.min.css')}}" rel="stylesheet">--}}
    <link media="all" type="text/css" rel="stylesheet" href="{{ $cdnLink}}themes2/assets/css/loader.css">
    <link media="all" type="text/css" rel="stylesheet" href="{{ $cdnLink}}themes2/plugins/apex/apexcharts.css">
    <link media="all" type="text/css" rel="stylesheet" href="{{ $cdnLink}}themes2/assets/css/dashboard/dashboard_3.css">
    <link media="all" type="text/css" rel="stylesheet" href="{{ $cdnLink}}themes2/plugins/flatpickr/flatpickr.css">
    <link media="all" type="text/css" rel="stylesheet"
          href="{{ $cdnLink}}themes2/plugins/flatpickr/custom-flatpickr.css">
    <link media="all" type="text/css" rel="stylesheet" href="{{ $cdnLink}}themes2/assets/css/elements/tooltip.css">
    <link media="all" type="text/css" rel="stylesheet" href="{{ $cdnLink}}themes2/assets/css/ui-elements/alert.css">

    <link href="{{url('assets/plugins/datatable/css/dataTables.bootstrap4.min.css')}}" rel="stylesheet">
    <link href="{{url('assets/plugins/datatable/css/buttons.bootstrap4.min.css')}}" rel="stylesheet">
    <link href="{{url('assets/plugins/datatable/css/responsive.bootstrap4.min.css')}}" rel="stylesheet">
    <link href="{{url('assets/plugins/datatable/css/jquery.dataTables.min.css')}}" rel="stylesheet">
    <link href="{{url('assets/plugins/datatable/css/responsive.dataTables.min.css')}}" rel="stylesheet">

    {{--start sweetalert css--}}
    <link href="{{url('assets/plugins/sweet-alert/sweetalert.css')}}" rel="stylesheet">
    {{--start sweetalert js--}}
    <link media="all" type="text/css" rel="stylesheet" href="{{ $cdnLink}}themes2/plugins/select2/select2.min.css">


    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

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

</head>
<body class="mode" data-base-url="{{url('')}}">
<script type="text/javascript">
    $( document ).ready(function() {
        $.ajax({
            url: "{{url('admin/dashboard-data-api')}}",
            success: function(msg){
                if (msg.status == 'success'){
                    $("#dashboard_api_balance").text(msg.balance.api_balance);
                    $("#dashboard_today_sale").text(msg.sales.today_sale);
                    $("#dashboard_aeps_sale").text(msg.sales.aeps_sale);
                    $("#dashboard_today_profit").text(msg.sales.today_profit);
                }
            }});
    });
</script>


<div class="loader" style="display: none;"></div>


<!--  Navbar Starts  -->
<div class="header-container fixed-top">
    <header class="header navbar navbar-expand-sm">
        <ul class="navbar-item theme-brand flex-row  text-center">
            <li class="nav-item">
                <a href="javascript:void(0);" class="sidebarCollapse" data-placement="bottom">
                    <i class="las la-bars"></i>
                </a>
            </li>
        </ul>

        <ul class="navbar-item flex-row ml-md-auto">
            <!-- Using Switch option -->
            <!--<li class="nav-item dropdown fullscreen-dropdown">
                <div class="switch-container mb-0 pl-0">
                    <label class="switch">
                        <input id="theme-switch" type="checkbox">
                        <span class="slider round primary-switch"></span>
                    </label>
                    <p class="ml-3 text-dark">Dark</p>
                </div>
            </li>-->
            <li class="nav-item dropdown  fullscreen-dropdown">
                <a class="nav-link night-light-mode">
                    <button type="button" class="btn btn-sm btn-outline-primary">Normal Bal
                        : {{number_format(Auth::user()->balance->user_balance,2)}}</button>
                </a>
            </li>

            @if(Auth::User()->company->aeps == 1 && Auth::User()->profile->aeps == 1)
                <li class="nav-item dropdown  fullscreen-dropdown">
                    <a class="nav-link night-light-mode">
                        <button type="button" class="btn btn-sm btn-outline-primary">Aeps Bal
                            : {{number_format(Auth::user()->balance->aeps_balance,2)}}</button>
                    </a>
                </li>
            @endif


            <li class="nav-item dropdown notification-dropdown">
                <a href="javascript:void(0);" class="nav-link dropdown-toggle position-relative"
                   id="notificationDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="las la-bell">{{ Auth::User()->unreadNotifications->count() }}</i>
                </a>
                <div class="dropdown-menu position-absolute" aria-labelledby="notificationDropdown">
                    <div class="nav-drop is-notification-dropdown">
                        <div class="inner">
                            <div class="nav-drop-header">
                                <span class="text-black font-12 strong">{{ Auth::User()->unreadNotifications->count()
                                }} new Notifications</span>
                                <a class="text-muted font-12" href="{{url('admin/notification/mark-all-read')}}">
                                    Mark All Read
                                </a>
                            </div>
                            <div class="nav-drop-body account-items pb-0">

                                @foreach(Auth::User()->unreadNotifications as $value)
                                    <a class="account-item" href="{{url('admin/notification/view')}}/{{$value->id}}">
                                        <div class="media align-center">
                                            <div class="media-content ml-3">
                                                <h6 class="font-13 mb-0 strong">{{ Str::limit($value->data['letter']['title'], 25)
                                    }}</h6>
                                                <p class="m-0 mt-1 font-10 text-muted"> {{
                                    Carbon\Carbon::parse($value->created_at)->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach

                                <hr class="account-divider">
                                <div class="text-center">
                                    <a class="text-primary strong font-13" href="#"> View All</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
            <li class="nav-item dropdown user-profile-dropdown">
                <a href="javascript:void(0);" class="nav-link dropdown-toggle user" id="userProfileDropdown"
                   data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                    <img src="http://rechargeexchange.mobileapi.in/assets/img/profile-1.jpg" alt="avatar">
                </a>
                <div class="dropdown-menu position-absolute" aria-labelledby="userProfileDropdown">
                    <div class="nav-drop is-account-dropdown">
                        <div class="inner">
                            <div class="nav-drop-header">
                                <span class="text-primary font-15">Welcome Admin !</span>
                            </div>
                            <div class="nav-drop-body account-items pb-0">
                                <a id="profile-link" class="account-item"
                                   href="http://rechargeexchange.mobileapi.in/pages/profile">
                                    <div class="media align-center">
                                        <div class="media-left">
                                            <div class="image">
                                                <img class="rounded-circle avatar-xs"
                                                     src="http://rechargeexchange.mobileapi.in/assets/img/profile-1.jpg"
                                                     alt="">
                                            </div>
                                        </div>
                                        <div class="media-content ml-3">
                                            <h6 class="font-13 mb-0 strong">John Doe</h6>
                                            <small>john@neptune.com</small>
                                        </div>
                                        <div class="media-right">
                                            <i data-feather="check"></i>
                                        </div>
                                    </div>
                                </a>
                                <a class="account-item" href="http://rechargeexchange.mobileapi.in/pages/profile">
                                    <div class="media align-center">
                                        <div class="icon-wrap">
                                            <i class="las la-user font-20"></i>
                                        </div>
                                        <div class="media-content ml-3">
                                            <h6 class="font-13 mb-0 strong"> My Account</h6>
                                        </div>
                                    </div>
                                </a>
                                <a class="account-item" href="http://rechargeexchange.mobileapi.in/pages/timeline">
                                    <div class="media align-center">
                                        <div class="icon-wrap">
                                            <i class="las la-briefcase font-20"></i>
                                        </div>
                                        <div class="media-content ml-3">
                                            <h6 class="font-13 mb-0 strong">My Activity</h6>
                                        </div>
                                    </div>
                                </a>
                                <a class="account-item settings">
                                    <div class="media align-center">
                                        <div class="icon-wrap">
                                            <i class="las la-cog font-20"></i>
                                        </div>
                                        <div class="media-content ml-3">
                                            <h6 class="font-13 mb-0 strong">Settings</h6>
                                        </div>
                                    </div>
                                </a>
                                <a class="account-item"
                                   href="http://rechargeexchange.mobileapi.in/authentications/style3/locked">
                                    <div class="media align-center">
                                        <div class="icon-wrap">
                                            <i class="las la-lock font-20"></i>
                                        </div>
                                        <div class="media-content ml-3">
                                            <h6 class="font-13 mb-0 strong">Lock Screen</h6>
                                        </div>
                                    </div>
                                </a>
                                <hr class="account-divider">
                                <a class="account-item"
                                   href="http://rechargeexchange.mobileapi.in/authentications/style3/login">
                                    <div class="media align-center">
                                        <div class="icon-wrap">
                                            <i class="las la-sign-out-alt font-20"></i>
                                        </div>
                                        <div class="media-content ml-3">
                                            <h6 class="font-13 mb-0 strong ">Logout</h6>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
        <ul class="navbar-item flex-row">
            <li class="nav-item dropdown header-setting">
                <a href="javascript:void(0);" class="nav-link dropdown-toggle rightbarCollapse" data-placement="bottom">
                    <i class="las la-sliders-h"></i>
                </a>
            </li>
        </ul>
    </header>
</div>
<!--  Navbar Ends  -->

<!--  Main Container Starts  -->
<div class="main-container" id="container">
    <div class="overlay"></div>
    <div class="search-overlay"></div>
    <div class="rightbar-overlay"></div>

    <!--  Sidebar Starts  -->
    <div class="sidebar-wrapper sidebar-theme">
        <nav id="sidebar">
            <ul class="navbar-item theme-brand flex-row  text-center">


                <li class="nav-item theme-text">
                    <a href="{{url('admin/dashboard')}}"><img src="{{ $cdnLink}}{{ $company_logo }}" class="navbar-logo"
                                                              alt="logo" style="height: 50px;"></a>
                </li>
            </ul>
            <hr>
            <ul class="list-unstyled menu-categories" id="accordionExample">


                <li class="menu {{ (request()->is('agent/dashboard')) ? 'active' : '' }}">
                    <a href="{{url('agent/dashboard')}}" data-active="{{ (request()->is('agent/dashboard')) ? 'true' : '' }}" aria-expanded="false"
                       class="dropdown-toggle" >
                        <div class="">
                            <i class="las la-home"></i>
                            <span> Dashboard</span>
                        </div>
                    </a>
                </li>

                <li class="menu-title"> Common Menu</li>

                {{--Start Recharge & Bills--}}
                <li class="menu {{ (request()->is('agent/prepaid-mobile','agent/dth','agent/postpaid','agent/electricity','agent/landline','agent/water','agent/gas','agent/fastag-recharge','agent/insurance','agent/loan-payment','agent/broadband','agent/subscription','agent/housing-society','agent/cable-tv','agent/lpg-gas')) ? 'active' : '' }}">
                    <a href="#admin_master" data-active="false" data-toggle="collapse" aria-expanded="false"
                       class="dropdown-toggle">
                        <div class="">
                            <i class="las la-mobile-alt"></i>
                            <span>  Recharge & Bills </span>
                        </div>
                        <div>
                            <i class="las la-angle-right sidemenu-right-icon"></i>
                        </div>
                    </a>
                    <ul class="collapse submenu list-unstyled {{ (request()->is('agent/prepaid-mobile','agent/dth','agent/postpaid','agent/electricity','agent/landline','agent/water','agent/gas','agent/fastag-recharge','agent/insurance','agent/loan-payment','agent/broadband','agent/subscription','agent/housing-society','agent/cable-tv','agent/lpg-gas')) ? 'show' : '' }}" id="admin_master" data-parent="#accordionExample">
                        @foreach(App\Service::where('status_id', 1)->whereIn('id', [1,2,3,4,5,6,7,8,9,10,11,19,20,21,22])->get() as $value)
                           <li><a data-active="{{ (request()->is('agent'.'/'.$value->slug)) ? 'true' : '' }}" href="{{url('agent')}}/{{ $value->slug }}">{{ $value->service_name }}</a></li>
                        @endforeach
                    </ul>
                </li>
                {{--End Recharge & Bills--}}


            </ul>
        </nav>
    </div>
    <!--  Sidebar Ends  -->


@yield('content')


<!-- Main Body Ends -->

    <div class="responsive-msg-component">
        <p>
            <a class="close-msg-component"><i class="las la-times"></i></a>
            Please reload the page to view the responsive functionalities
        </p>
    </div>

    <!-- Copyright Footer Starts -->
    <!-- Copyright Footer Starts -->
    <div class="footer-wrapper">
        <div class="footer-section f-section-1">
            <p class=""> Copyright © 2022 {{ $company_name }}. All rights reserved. </p>

        </div>
    </div>
    <!-- Copyright Footer Ends -->
    <!-- Copyright Footer Ends -->

    <!-- Arrow Starts -->
    <div class="scroll-top-arrow" style="display: none;">
        <i class="las la-angle-up"></i>
    </div>
    <!-- Arrow Ends -->
</div>
<!--  Content Area Ends  -->

<!--  Rightbar Area Starts -->
<div class="right-bar">
    <div class="h-100">
        <div class="simplebar-wrapper" style="margin: 0px;">
            <div class="simplebar-mask">
                <div class="simplebar-offset" style="right: 0px; bottom: 0px;">
                    <div class="simplebar-content-wrapper" style="height: 100%;">
                        <div class="simplebar-content" style="padding: 0px;">
                            <!-- Nav tabs -->

                            <!-- Tab panes starts -->
                            <h6 class="font-weight-medium px-3 mb-0 mt-4 text-uppercase">Balance</h6>
                            <hr>
                            <div class="p-2">

                                <a href="javascript: void(0);" class="text-reset item-hovered d-block p-2">
                                    <p class="text-muted mb-0">Normal Balance<span
                                                class="float-right">{{number_format(Auth::user()->balance->user_balance,2)}}</span>
                                    </p>
                                </a>

                                <hr>

                                <a href="javascript: void(0);" class="text-reset item-hovered d-block p-2">
                                    <p class="text-muted mb-0">Sms Balance<span
                                                class="float-right">{{number_format(Auth::user()->balance->sms_balance,2)}}</span>
                                    </p>
                                </a>

                                <hr>

                                <a href="javascript: void(0);" class="text-reset item-hovered d-block p-2">
                                    <p class="text-muted mb-0">Aeps Balance<span
                                                class="float-right">{{number_format(Auth::user()->balance->aeps_balance,2)}}</span>
                                    </p>
                                </a>

                            </div>
                            <!-- Tab panes ends -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--  Rightbar Area Ends -->
</div>
<!-- Main Container Ends -->

<!-- Common Script Starts -->
<script src="{{ $cdnLink}}themes2/assets/js/all.js"></script>

<!-- Stack array for including inline js or scripts -->
<script src="{{ $cdnLink}}themes2/assets/js/loader.js"></script>
<script src="{{ $cdnLink}}themes2/plugins/apex/apexcharts.min.js"></script>
<script src="{{ $cdnLink}}themes2/plugins/flatpickr/flatpickr.js"></script>
<script src="{{ $cdnLink}}themes2/assets/js/dashboard/dashboard_3.js"></script>
<script src="{{ $cdnLink}}themes2/plugins/counter/jquery.countTo.js"></script>
<script src="{{ $cdnLink}}themes2/assets/js/components/custom-counter.js"></script>

<!-- Common Script Ends -->

<!--- Start Chart bundle min js --->
<script src="{{url('assets/plugins/chart.js/Chart.bundle.min.js')}}"></script>
<script src="{{url('assets/plugins/chart.js/excanvas.js')}}"></script>
<script src="{{url('assets/plugins/chart.js/utils.js')}}"></script>
<!--- End Chart bundle min js --->
<script src="{{url('assets/plugins/select2/js/select2.min.js')}}"></script>

<!--- Start datatable bundle min js --->
<script src="{{url('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
<script src="{{url('assets/plugins/datatable/js/dataTables.dataTables.min.js')}}"></script>
<script src="{{url('assets/plugins/datatable/js/dataTables.responsive.min.js')}}"></script>
<script src="{{url('assets/plugins/datatable/js/responsive.dataTables.min.js')}}"></script>
<script src="{{url('assets/plugins/datatable/js/jquery.dataTables.js')}}"></script>
<script src="{{url('assets/plugins/datatable/js/dataTables.bootstrap4.js')}}"></script>
<script src="{{url('assets/plugins/datatable/js/dataTables.buttons.min.js')}}"></script>
<!--- End datatable bundle min js --->

<script src="{{url('assets/plugins/datatable/js/dataTables.responsive.min.js')}}"></script>
<script src="{{url('assets/plugins/datatable/js/responsive.bootstrap4.min.js')}}"></script>
<script src="{{url('assets/js/table-data.js')}}"></script>

{{--start sweetalert--}}
<script src="{{url('assets/plugins/sweet-alert/sweetalert.min.js')}}"></script>
<script src="{{url('assets/plugins/sweet-alert/jquery.sweet-alert.js')}}"></script>
{{--End sweetalert--}}
@csrf


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

</body>
</html>
