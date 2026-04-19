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
    @include('agent.layout.style')
</head>

<body class="main-body  app">

@if (Auth::guest())

@else
    @if(Auth::user()->role_id == 8 || Auth::user()->role_id == 9 || Auth::user()->role_id == 10)
        <script type="text/javascript">
            document.location.href = "{{url('agent/dashboard')}}";
        </script>
    @endif
@endif

<script type="text/javascript">
    $(document).ready(function () {
        $.ajax({
            url: "{{url('admin/dashboard-data-api')}}",
            success: function (msg) {
                if (msg.status == 'success') {
                    $("#dashboard_api_balance").text(msg.balance.api_balance);
                    $("#dashboard_aeps_api_balance").text(msg.balance.aeps_api_balance);
                    $("#dashboard_today_sale").text(msg.sales.today_sale);
                    $("#dashboard_aeps_sale").text(msg.sales.aeps_sale);
                    $("#dashboard_today_profit").text(msg.sales.today_profit);

                }

            }
        });
    });
</script>


<div class="loader" style="display: none;"></div>


<!-- main-header opened -->
<div class="main-header nav nav-item hor-header">
    <div class="container">
        <div class="main-header-left ">
            <a class="animated-arrow hor-toggle horizontal-navtoggle"><span></span></a><!-- sidebar-toggle-->
            <a class="header-brand" href="{{url('admin/dashboard')}}">
                <img src="{{ $cdnLink}}{{ $company_logo }}" class="logo-white ">
                <img src="{{ $cdnLink}}{{ $company_logo }}" class="logo-default">
                <img src="{{ $cdnLink}}{{ $company_logo }}" class="icon-white">
                <img src="{{ $cdnLink}}{{ $company_logo }}" class="icon-default">
            </a>
            <div class="main-header-center  ml-4">
                <ul class="header-megamenu-dropdown  nav">

                    @if(Auth::User()->role_id == 1)
                        <li class="nav-item">
                            <div class="dropdown-menu-rounded btn-group dropdown">
                                <button aria-expanded="false" aria-haspopup="true" class="btn btn-link dropdown-toggle"
                                        data-toggle="dropdown" id="dropdownMenuButton3" type="button"><span><i
                                                class="nav-link-icon fas fa-book-open"></i> Master </span></button>
                                <div class="dropdown-menu-lg dropdown-menu" x-placement="bottom-left">
                                    <a class="dropdown-item  mt-2" href="{{url('admin/bank-master')}}"><i class="dropdown-icon"></i>Bank Master</a>
                                    <a class="dropdown-item  mt-2" href="{{url('admin/role-master')}}"><i class="dropdown-icon"></i>Role Master</a>
                                    <a class="dropdown-item  mt-2" href="{{url('admin/status-master')}}"><i class="dropdown-icon"></i>Status Master</a>
                                    <a class="dropdown-item  mt-2" href="{{url('admin/service-master')}}"><i class="dropdown-icon"></i>Service Master</a>
                                    <a class="dropdown-item  mt-2" href="{{url('admin/payment-method')}}"><i class="dropdown-icon"></i>Payment Method Master</a>
                                    @if(Auth::User()->company->payout == 1 && Auth::User()->profile->payout == 1)
                                        <a class="dropdown-item  mt-2" href="{{url('admin/payout-beneficiary-master')}}"><i class="dropdown-icon"></i>Payout Beneficiary Master</a>
                                    @endif

                                    @if(Auth::User()->company->aeps == 1 && Auth::User()->role_id == 1)
                                        <a class="dropdown-item  mt-2" href="{{url('admin/agent-onboarding-list')}}"><i class="dropdown-icon"></i>Agent Onboarding List</a>
                                    @endif
                                    <a class="dropdown-item  mt-2" href="{{url('admin/contact-enquiry')}}"><i class="dropdown-icon"></i>Contact Enquiry</a>
                                    @if(Auth::User()->role_id == 1)
                                        <a class="dropdown-item  mt-2" href="{{url('admin/company-staff/welcome')}}"><i class="dropdown-icon"></i>Company Staff Permission</a>
                                    @endif

                                    @if(Auth::User()->role_id == 1 && Auth::User()->company->cashfree == 1)
                                        <a class="dropdown-item  mt-2" href="{{url('admin/cashfree-gateway-master')}}"><i class="dropdown-icon"></i>Cashfree Gateway Master</a>
                                    @endif
                                    <a class="dropdown-item  mt-2" href="{{url('admin/broadcast')}}"><i class="dropdown-icon"></i>Broadcast</a>

                                </div>
                            </div>
                        </li>

                        <li class="nav-item">
                            <div class="dropdown-menu-rounded btn-group dropdown">
                                <button aria-expanded="false" aria-haspopup="true" class="btn btn-link dropdown-toggle"
                                        data-toggle="dropdown" id="dropdownMenuButton3" type="button"><span><i
                                                class="nav-link-icon fe fe-briefcase"></i> Api Master </span></button>
                                <div class="dropdown-menu-lg dropdown-menu" x-placement="bottom-left">
                                    <a class="dropdown-item  mt-2" href="{{url('admin/provider-master')}}"><i class="dropdown-icon"></i>Provider Master</a>
                                    <a class="dropdown-item" href="{{url('admin/api-master')}}"><i class="dropdown-icon"></i>Api Master</a>
                                    <a class="dropdown-item" href="{{url('admin/denomination-wise-api')}}"><i class="dropdown-icon"></i>Denomination Wise Api</a>
                                    <a class="dropdown-item" href="{{url('admin/number-series-master')}}"><i class="dropdown-icon"></i>Number Series Master</a>
                                    <a class="dropdown-item" href="{{url('admin/state-wise-api')}}"><i class="dropdown-icon"></i>State Wise Api</a>
                                    <a class="dropdown-item" href="{{url('admin/backup-api-master')}}"><i class="dropdown-icon"></i>Backup Api Master</a>
                                    <a class="dropdown-item" href="{{url('admin/api-switching')}}"><i class="dropdown-icon"></i>Api Switching</a>
                                    <a class="dropdown-item" href="{{url('admin/user-operator-limit')}}"><i class="dropdown-icon"></i>User Operator Limit</a>
                                    <a class="dropdown-item" href="{{url('admin/bank-transfer-switching')}}"><i class="dropdown-icon"></i>User Payout  Switching</a>
                                </div>
                            </div>
                        </li>
                    @endif

                    @if(Auth::User()->role_id <= 2)
                        <li class="nav-item">
                            <div class="dropdown-menu-rounded btn-group dropdown">
                                <button aria-expanded="false" aria-haspopup="true" class="btn btn-link dropdown-toggle"
                                        data-toggle="dropdown" id="dropdownMenuButton3" type="button"><span><i
                                                class="nav-link-icon fe fe-settings"></i> Settings </span></button>
                                <div class="dropdown-menu-lg dropdown-menu" x-placement="bottom-left">
                                    <a class="dropdown-item  mt-2" href="{{url('admin/company-settings')}}"><i class="dropdown-icon"></i>Company Settings</a>
                                    <a class="dropdown-item  mt-2" href="{{url('admin/site-setting/welcome')}}"><i class="dropdown-icon"></i>Site Settings</a>
                                    <a class="dropdown-item  mt-2" href="{{url('admin/sms-template/welcome')}}"><i class="dropdown-icon"></i>Sms Template</a>
                                    <a class="dropdown-item" href="{{url('admin/package-settings')}}"><i class="dropdown-icon"></i>Package Settings</a>
                                    <a class="dropdown-item" href="{{url('admin/bank-settings')}}"><i class="dropdown-icon"></i>Bank Settings</a>
                                    <a class="dropdown-item" href="{{url('admin/logo-upload')}}"><i class="dropdown-icon"></i>Logo Upload</a>
                                    <a class="dropdown-item" href="{{url('admin/service-banner')}}"><i class="dropdown-icon"></i>Service Banner</a>
                                    <a class="dropdown-item" href="{{url('admin/notification/welcome')}}"><i class="dropdown-icon"></i>Notification Settings</a>

                                </div>
                            </div>
                        </li>

                        <li class="nav-item">
                            <div class="dropdown-menu-rounded btn-group dropdown">
                                <button aria-expanded="false" aria-haspopup="true" class="btn btn-link dropdown-toggle"
                                        data-toggle="dropdown" id="dropdownMenuButton3" type="button"><span><i
                                                class="fa fa-globe" aria-hidden="true"></i> Website Master </span>
                                </button>
                                <div class="dropdown-menu-lg dropdown-menu" x-placement="bottom-left">
                                <!--<a class="dropdown-item" href="{{url('admin/home-page-content')}}"><i class="dropdown-icon"></i>Home Page Content</a>-->
                                    <a class="dropdown-item" href="{{url('admin/dynamic-page')}}"><i class="dropdown-icon"></i>Dynamic Page</a>
                                    <a class="dropdown-item" href="{{url('admin/front-banners')}}"><i class="dropdown-icon"></i>Front Banners</a>

                                </div>
                            </div>
                        </li>

                        @php $sitesettings = App\Models\Sitesetting::where('company_id', Auth::User()->company_id)->first();
                        @endphp
                        @if($sitesettings->whatsapp == 1)
                            <li class="nav-item">
                                <div class="dropdown-menu-rounded btn-group dropdown">
                                    <button aria-expanded="false" aria-haspopup="true"
                                            class="btn btn-link dropdown-toggle"
                                            data-toggle="dropdown" id="dropdownMenuButton3" type="button"><span><i
                                                    class="nav-link-icon fe fe-mail"></i> Whatsapp </span></button>
                                    <div class="dropdown-menu-lg dropdown-menu" x-placement="bottom-left">
                                        <a class="dropdown-item" href="{{url('admin/whatsapp/role-wise')}}"><i class="dropdown-icon"></i>Send Role Wise</a>
                                    </div>
                                </div>
                            </li>
                        @endif

                    @endif


                </ul>
            </div>
        </div><!-- search -->
        <div class="main-header-right">


            <span class="badge badge-danger">{{ Auth::User()->unreadNotifications->count() }}</span>
            <div class="dropdown nav-item main-header-notification">
                <a class="new nav-link" href="#"> <i class="fe fe-bell"></i><span class="pulse"></span></a>
                <div class="dropdown-menu">
                    <div class="menu-header-content bg-primary-gradient text-left d-flex">
                        <div class="">
                            <h6 class="menu-header-title text-white mb-0">{{ Auth::User()->unreadNotifications->count()
                                }} new Notifications</h6>
                        </div>
                        <div class="my-auto ml-auto">
                            <a class="badge badge-pill badge-warning float-right"
                               href="{{url('admin/notification/mark-all-read')}}">Mark All Read</a>
                        </div>
                    </div>
                    <div class="main-notification-list Notification-scroll">

                        @foreach(Auth::User()->unreadNotifications as $value)
                            <a class="d-flex p-3 border-bottom"
                               href="{{url('admin/notification/view')}}/{{$value->id}}">
                                <div class="ml-3">
                                    <h5 class="notification-label mb-1">{{ Str::limit($value->data['letter']['title'], 25)
                                    }}</h5>
                                    <div class="notification-subtext">{{
                                    Carbon\Carbon::parse($value->created_at)->diffForHumans() }}
                                    </div>
                                </div>
                                <div class="ml-auto">
                                    <i class="las la-angle-right text-right text-muted"></i>
                                </div>
                            </a>
                        @endforeach

                    </div>
                    <div class="dropdown-footer">
                        <a href="#">VIEW ALL</a>
                    </div>
                </div>
            </div>
            <div class="dropdown main-profile-menu nav nav-item nav-link">
                <a class="profile-user d-flex" href="">
                    @if(Auth::User()->member->profile_photo)
                        <img src="{{Auth::User()->member->profile_photo}}" alt="user-img"
                             class="rounded-circle mCS_img_loaded">
                    @else
                        <span class="developer-profile-avatar" title="Profile"><i class="fas fa-user" aria-hidden="true"></i></span>
                    @endif
                    <span></span></a>
                <div class="dropdown-menu">
                    <div class="main-header-profile header-img">
                        @if(Auth::User()->member->profile_photo)
                            <div class="main-img-user"><img alt="" src="{{Auth::User()->member->profile_photo}}"></div>
                        @else
                            <div class="main-img-user"><span class="developer-profile-avatar developer-profile-avatar-lg"><i class="fas fa-user" aria-hidden="true"></i></span></div>
                        @endif
                        <h6>{{ Auth::User()->name }}</h6>
                        <span>({{ Auth::User()->role->role_title }})</span>
                    </div>
                    <a class="dropdown-item" href="{{url('admin/my-profile')}}"><i class="far fa-user"></i> My Profile</a>
                    @if(Auth::user()->role_id != 1)
                        <a class="dropdown-item" href="{{url('admin/my-recharge-commission')}}"><i class="fas fa-rupee-sign"></i> Commission Structure</a>
                    @endif
                    <a class="dropdown-item" href="{{url('admin/activity-logs')}}"><i class="far fa-clock"></i> Activity Logs</a>
                    @if(Auth::User()->company->transaction_pin == 1)
                        <a class="dropdown-item" href="{{url('admin/transaction-pin')}}"><i class="fas fa-lock"></i>Transaction Pin</a>
                    @endif
                    <a class="dropdown-item" href="{{ route('logout') }}"
                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();"> <i
                                class="fas fa-sign-out-alt"></i>
                        {{ __('Logout') }}
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </div>
            <div class="dropdown main-header-message right-toggle">
                <a class="nav-link pr-0" data-toggle="sidebar-right" data-target=".sidebar-right">
                    <i class="ion ion-md-menu tx-20 bg-transparent"></i>
                </a>
            </div>
        </div>
    </div>
</div>
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
                    <li aria-haspopup="true"><a href="{{url('admin/dashboard')}}" class=""><i
                                    class="fe fe-airplay  menu-icon"></i> Dashboard</a></li>


                    <li aria-haspopup="true"><a href="#" class="sub-icon"><i class="fe fe-users "></i> Members <i
                                    class="fe fe-chevron-down horizontal-icon"></i></a>
                        <ul class="sub-menu">

                            @if(Auth::User()->role_id == 1)
                                @foreach(App\Models\Role::where('id', '>', Auth::user()->role_id)->where('status_id', 1)->select('id', 'role_slug', 'role_title')->get() as $value)
                                    @php
                                        $library = new App\Library\MemberLibrary();
                                        $my_down_member = $library->my_down_member(Auth::User()->role_id, Auth::User()->company_id,
                                        Auth::id());
                                        $totalMembers = App\Models\User::whereIn('id', $my_down_member)->where('role_id',
                                        $value->id)->count();
                                    @endphp
                                    <li aria-haspopup="true"><a href="{{url('admin/member-list')}}/{{ $value->role_slug }}"
                                                class="slide-item"> {{ $value->role_title }} ({{ $totalMembers
                                    }})</a></li>
                                @endforeach
                            @else
                                @foreach(App\Models\Role::where('id', '>', Auth::user()->role_id)->where('status_id',
                                1)->whereNotIn('id', [9,10])->select('id', 'role_slug', 'role_title')->get() as $value)
                                    @php
                                        $library = new App\Library\MemberLibrary();
                                        $my_down_member = $library->my_down_member(Auth::User()->role_id, Auth::User()->company_id,
                                        Auth::id());
                                        $totalMembers = App\Models\User::whereIn('id', $my_down_member)->where('role_id',
                                        $value->id)->count();
                                    @endphp
                                    <li aria-haspopup="true"><a
                                                href="{{url('admin/member-list')}}/{{ $value->role_slug }}"
                                                class="slide-item"> {{ $value->role_title }} ({{ $totalMembers
                                    }})</a></li>
                                @endforeach
                            @endif
                            <li aria-haspopup="true"><a href="{{url('admin/suspended-users')}}" class="slide-item">Suspended User</a></li>
                            <li aria-haspopup="true"><a href="{{url('admin/not-working-users')}}" class="slide-item">Not Working Users</a></li>
                        </ul>
                    </li>

                    @php
                        $library = new \App\Library\BasicLibrary;
                        $companyActiveService = $library->getCompanyActiveService(Auth::id());
                        $userActiveService = $library->getUserActiveService(Auth::id());
                    @endphp

                    <li aria-haspopup="true"><a href="#" class="sub-icon"><i class="fas fa-table"></i> Report <i class="fe fe-chevron-down horizontal-icon"></i></a>
                        <ul class="sub-menu">
                            <li aria-haspopup="true"><a href="{{url('admin/report/v1/all-transaction-report')}}" class="slide-item"> All Transaction Report</a></li>
                            @foreach(App\Models\Servicegroup::where('status_id', 1)->select('id', 'group_name')->get() as $value)
                              <!--  <li aria-haspopup="true" class="sub-menu-sub"><a href="#">{{ $value->group_name }}</a>
                                    <ul class="sub-menu">
                                        @foreach(App\Models\Service::where('servicegroup_id', $value->id)->whereIn('id', $companyActiveService)->whereIn('id', $userActiveService)->where('status_id', 1)->select('id', 'service_name', 'report_slug')->get() as $serv)
                                            <li aria-haspopup="true"><a href="{{url('admin/report/v1/welcome')}}/{{ $serv->report_slug }}" class="slide-item">{{ $serv->service_name }} History</a></li>
                                        @endforeach
                                    </ul>
                                </li>-->
                            @endforeach
                            <li aria-haspopup="true"><a href="{{url('admin/report/v1/pending-transaction')}}" class="slide-item">Pending Transaction</a></li>
                            @if(Auth::User()->role_id == 1)
                                <li aria-haspopup="true"><a href="{{url('admin/report/v1/api-profit-loss-report')}}" class="slide-item">Admin Profit Report</a></li>
                                <li aria-haspopup="true"><a href="{{url('admin/report/v1/refund-manager')}}" class="slide-item">Refund Manager</a></li>
                                <li aria-haspopup="true"><a href="{{url('admin/income/api-summary-report')}}" class="slide-item">Api Summary</a></li>
                            @endif
                            <li aria-haspopup="true"><a href="{{url('admin/income/operator-wise-sale')}}" class="slide-item">Operator Wise Sale</a></li>
                            <li aria-haspopup="true"><a href="{{url('admin/report/v1/ledger-report')}}" class="slide-item"> Ledger Report</a></li>
                            <li aria-haspopup="true" class="sub-menu-sub"><a href="#">Payment Report</a>
                                <ul class="sub-menu">
                                    <li aria-haspopup="true"><a href="{{url('admin/report/v1/debit-report')}}" class="slide-item">Debit Report</a></li>
                                    <li aria-haspopup="true"><a href="{{url('admin/report/v1/credit-report')}}" class="slide-item">Credit Report</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>


                    <li aria-haspopup="true"><a href="#" class="sub-icon"><i class="fas fa-rupee-sign"></i> Payment <i
                                    class="fe fe-chevron-down horizontal-icon"></i></a>
                        <ul class="sub-menu">
                            <li aria-haspopup="true"><a href="{{url('admin/balance-transfer')}}" class="slide-item">Balance Transfer</a></li>
                            @if(Auth::User()->role_id == 1)
                                <li aria-haspopup="true"><a href="{{url('admin/balance-return')}}" class="slide-item">Balance Return</a></li>
                                <li aria-haspopup="true"><a href="{{url('admin/payin-to-payout')}}" class="slide-item">Payin To Payout</a></li>
                            @endif
                            <li aria-haspopup="true"><a href="{{url('admin/balance-return-request')}}" class="slide-item"> Balance Return Request</a></li>
                            <li aria-haspopup="true"><a href="{{url('admin/payment-request-view')}}" class="slide-item">Payment Request View</a></li>

                            @if(Auth::user()->role_id != 1)
                                <li aria-haspopup="true"><a href="{{url('admin/payment-request')}}" class="slide-item">Payment Request</a></li>
                            @endif
                            @if(Auth::User()->role_id == 1)
                                <li aria-haspopup="true"><a href="{{url('admin/purchase-balance')}}" class="slide-item">Purchase Balance</a></li>
                            @endif

                        </ul>
                    </li>

                    <li aria-haspopup="true"><a href="#" class="sub-icon"><i class="fas fa-comments"></i> Dispute <i
                                    class="fe fe-chevron-down horizontal-icon"></i></a>
                        <ul class="sub-menu">
                            <li aria-haspopup="true"><a href="{{url('admin/pending-dispute')}}" class="">Pending
                                    Dispute</a></li>
                            <li aria-haspopup="true"><a href="{{url('admin/solve-dispute')}}" class="">Solve Dispute</a>
                            </li>

                        </ul>
                    </li>
                    <li aria-haspopup="true">
                        <a href="#" class="sub-icon"><i class="far fa-money-bill-alt"></i>User Income <i class="fe fe-chevron-down horizontal-icon"></i></a>
                        <ul class="sub-menu">
                            @if(Auth::User()->role_id == 1)
                                @foreach(App\Models\Role::where('id', '>', Auth::user()->role_id)->where('status_id', 1)->get() as $value)
                                    <li aria-haspopup="true">
                                        <a href="{{url('admin/income/user-income')}}/{{ $value->role_slug }}" class="slide-item"> {{ $value->role_title }} Income</a>
                                    </li>
                                @endforeach
                            @else
                                @foreach(App\Models\Role::where('id', '>', Auth::user()->role_id)->where('status_id', 1)->whereNotIn('id', [9,10])->get() as $value)
                                    <li aria-haspopup="true">
                                        <a href="{{url('admin/income/user-income')}}/{{ $value->role_slug }}" class="slide-item"> {{ $value->role_title }} Income</a>
                                    </li>
                                @endforeach
                            @endif

                            @if(Auth::user()->role_id != 1)
                                <li aria-haspopup="true">
                                    <a href="{{url('admin/income/my-income')}}" class="slide-item">My Income</a>
                                </li>
                            @endif
                        </ul>
                    </li>

                    @if(Auth::User()->role_id == 1 && Auth::User()->company->invoice == 1)
                        <li aria-haspopup="true"><a href="{{url('admin/invoice/gst-invoice')}}" class=""><i
                                        class="fas fa-receipt"></i> Invoice Master</a></li>
                    @endif

                    @if(Auth::User()->company->ecommerce == 1 && Auth::User()->profile->ecommerce == 1)
                        <li aria-haspopup="true"><a href="#" class="sub-icon"><i class="fas fa-shopping-cart"></i>
                                Ecommerce
                                <i class="fe fe-chevron-down horizontal-icon"></i></a>
                            <ul class="sub-menu">


                                @if(Auth::User()->role_id == 1)
                                    <li aria-haspopup="true" class="sub-menu-sub"><a href="#">Categories</a>
                                        <ul class="sub-menu">
                                            <li aria-haspopup="true"><a href="{{url('admin/ecommerce/main-category')}}"
                                                                        class="slide-item">Main Category</a></li>
                                            <li aria-haspopup="true"><a href="{{url('admin/ecommerce/sub-category')}}"
                                                                        class="slide-item">Sub Category</a></li>
                                        </ul>
                                    </li>

                                    <li aria-haspopup="true" class="sub-menu-sub"><a href="#">Product</a>
                                        <ul class="sub-menu">
                                            <li aria-haspopup="true"><a
                                                        href="{{url('admin/ecommerce/shopping-banners')}}"
                                                        class="slide-item">Ecommerce Banners</a></li>
                                            <li aria-haspopup="true"><a href="{{url('admin/ecommerce/brands')}}"
                                                                        class="slide-item">Brands</a></li>
                                            <li aria-haspopup="true"><a href="{{url('admin/ecommerce/product-list')}}"
                                                                        class="slide-item">Product List</a></li>
                                        </ul>
                                    </li>

                                    <li aria-haspopup="true" class="sub-menu-sub"><a href="#">Report</a>
                                        <ul class="sub-menu">
                                            <li aria-haspopup="true"><a href="{{url('admin/ecommerce/order-report')}}"
                                                                        class="slide-item">Order Report</a></li>
                                            <li aria-haspopup="true"><a href="{{url('admin/ecommerce/product-report')}}"
                                                                        class="slide-item">Product Report</a></li>
                                            <li aria-haspopup="true"><a href="{{url('admin/ecommerce/track-order')}}"
                                                                        class="slide-item">Track Order</a></li>
                                        </ul>
                                    </li>
                                @endif


                            </ul>
                        </li>
                    @endif

                    {{--admin for mobile --}}
                    @if(Auth::User()->role_id == 1)
                        <li aria-haspopup="true" class="d-lg-none"><a href="#" class="sub-icon"><i
                                        class="fas fa-user-lock"></i> Admin <i
                                        class="fe fe-chevron-down horizontal-icon"></i></a>
                            <ul class="sub-menu">
                                <li aria-haspopup="true" class="sub-menu-sub"><a href="#">Master</a>
                                    <ul class="sub-menu">
                                        <li aria-haspopup="true"><a href="{{url('admin/bank-master')}}"
                                                                    class="slide-item">Bank
                                                Master</a></li>
                                        <li aria-haspopup="true"><a href="{{url('admin/role-master')}}"
                                                                    class="slide-item">Role
                                                Master</a></li>
                                        <li aria-haspopup="true"><a href="{{url('admin/status-master')}}"
                                                                    class="slide-item">Status Master</a></li>
                                        <li aria-haspopup="true"><a href="{{url('admin/service-master')}}"
                                                                    class="slide-item">Service Master</a></li>
                                        <li aria-haspopup="true"><a href="{{url('admin/payment-method')}}"
                                                                    class="slide-item">Payment Method Master</a></li>
                                        @if(Auth::User()->company->payout == 1 && Auth::User()->profile->payout == 1)
                                            <li aria-haspopup="true"><a
                                                        href="{{url('admin/payout-beneficiary-master')}}"
                                                        class="slide-item">Payout Beneficiary Master</a></li>
                                        @endif

                                        @if(Auth::User()->company->aeps == 1 && Auth::User()->role_id == 1)
                                            <li aria-haspopup="true"><a href="{{url('admin/agent-onboarding-list')}}"
                                                                        class="slide-item">Agent Onboarding List</a>
                                            </li>
                                        @endif
                                        <li aria-haspopup="true"><a href="{{url('admin/contact-enquiry')}}"
                                                                    class="slide-item">Contact Enquiry</a></li>
                                        @if(Auth::User()->role_id == 1)
                                            <li aria-haspopup="true"><a href="{{url('admin/company-staff/welcome')}}"
                                                                        class="slide-item">Company Staff Permission</a>
                                            </li>
                                        @endif

                                        @if(Auth::User()->role_id == 1 && Auth::User()->company->cashfree == 1)
                                            <li aria-haspopup="true"><a href="{{url('admin/cashfree-gateway-master')}}"
                                                                        class="slide-item">Cashfree Gateway Master</a>
                                            </li>
                                        @endif

                                    </ul>
                                </li>


                                <li aria-haspopup="true" class="sub-menu-sub"><a href="#">Api Master</a>
                                    <ul class="sub-menu">
                                        <li aria-haspopup="true"><a href="{{url('admin/provider-master')}}"
                                                                    class="slide-item">Provider Master</a></li>
                                        <li aria-haspopup="true"><a href="{{url('admin/api-master')}}"
                                                                    class="slide-item">Api
                                                Master</a></li>
                                        <li aria-haspopup="true"><a href="{{url('admin/denomination-wise-api')}}"
                                                                    class="slide-item">Denomination Wise Api</a></li>
                                        <li aria-haspopup="true"><a href="{{url('admin/number-series-master')}}"
                                                                    class="slide-item">Number Series Master</a></li>
                                        <li aria-haspopup="true"><a href="{{url('admin/state-wise-api')}}"
                                                                    class="slide-item">State Wise Api</a></li>
                                        <li aria-haspopup="true"><a href="{{url('admin/backup-api-master')}}"
                                                                    class="slide-item">Backup Api Master</a></li>
                                        <li aria-haspopup="true"><a href="{{url('admin/api-switching')}}"
                                                                    class="slide-item">Api Switching</a></li>
                                        <li aria-haspopup="true"><a href="{{url('admin/user-operator-limit')}}"
                                                                    class="slide-item">User Operator Limit</a></li>
                                        @if(Auth::User()->company->vendor_payment == 1)
                                            <li aria-haspopup="true"><a href="{{url('admin/vendor-payment/welcome')}}"
                                                                        class="slide-item">Api Vendor Payment</a></li>
                                        @endif

                                    </ul>
                                </li>


                                <li aria-haspopup="true" class="sub-menu-sub"><a href="#">Settings</a>
                                    <ul class="sub-menu">
                                        <li aria-haspopup="true"><a href="{{url('admin/company-settings')}}"
                                                                    class="slide-item">Company Settings</a></li>
                                        <li aria-haspopup="true"><a href="{{url('admin/site-setting/welcome')}}"
                                                                    class="slide-item">Site Settings</a></li>
                                        <li aria-haspopup="true"><a href="{{url('admin/sms-template/welcome')}}"
                                                                    class="slide-item">Sms Template</a></li>
                                        <li aria-haspopup="true"><a href="{{url('admin/package-settings')}}"
                                                                    class="slide-item">Package Settings</a></li>
                                        <li aria-haspopup="true"><a href="{{url('admin/bank-settings')}}"
                                                                    class="slide-item">Bank Settings</a></li>
                                        <li aria-haspopup="true"><a href="{{url('admin/logo-upload')}}"
                                                                    class="slide-item">Logo
                                                Upload</a></li>
                                        <li aria-haspopup="true"><a href="{{url('admin/service-banner')}}"
                                                                    class="slide-item">Service Banner</a></li>
                                        <li aria-haspopup="true"><a href="{{url('admin/notification/welcome')}}"
                                                                    class="slide-item">Notification Settings</a></li>
                                    </ul>
                                </li>

                            </ul>
                        </li>
                    @endif
                    {{--Admin for mobile End--}}
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


                    <div class="d-flex justify-content-center mr-5">
                        <div class="">
									<span class="d-block">
										<span class="label">Today Aeps Sale</span>
									</span>
                            <span class="value" id="dashboard_aeps_sale"></span>
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

                          {{--  <div class="d-flex tasks">
                                <div class="mb-0">
                                    <div class="h6 fs-15 mb-0">Sms Balance</div>
                                </div>
                                <span class="float-right ml-auto">{{number_format(Auth::user()->balance->sms_balance,2)}}</span>
                            </div>--}}

                            @if(Auth::User()->company->aeps == 1 && Auth::User()->profile->aeps == 1)
                            <div class="d-flex tasks">
                                <div class="mb-0">
                                    <div class="h6 fs-15 mb-0">Aeps Balance</div>
                                </div>
                                <span class="float-right ml-auto">{{number_format(Auth::user()->balance->aeps_balance,2)}}</span>
                            </div>
                            @endif

                            @if(Auth::User()->role_id == 1)
                                <div class="d-flex tasks">
                                    <div class="mb-0">
                                        <div class="h6 fs-15 mb-0">Api Balance</div>
                                    </div>
                                    <span class="float-right ml-auto" id="dashboard_api_balance"></span>
                                </div>

                                @if(Auth::User()->company->aeps == 1)
                                <div class="d-flex tasks">
                                    <div class="mb-0">
                                        <div class="h6 fs-15 mb-0">Aeps Api Balance</div>
                                    </div>
                                    <span class="float-right ml-auto" id="dashboard_aeps_api_balance"></span>
                                </div>
                                @endif
                            @endif
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



        @csrf

</body>
</html>
