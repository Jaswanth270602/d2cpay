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
</head>
<body class="mode" data-base-url="{{url('')}}">

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
                    @if(Auth::User()->member->profile_photo)
                        <img src="{{Auth::User()->member->profile_photo}}" alt="avatar">
                    @else
                        <img src="{{url('assets/img/profile-pic.jpg')}}" alt="avatar">
                    @endif
                </a>
                <div class="dropdown-menu position-absolute" aria-labelledby="userProfileDropdown">
                    <div class="nav-drop is-account-dropdown">
                        <div class="inner">
                            <div class="nav-drop-header">
                                <span class="text-primary font-15">Welcome {{ Auth::User()->role->role_title }}</span>
                            </div>
                            <div class="nav-drop-body account-items pb-0">
                                <a id="profile-link" class="account-item"
                                   href="http://rechargeexchange.mobileapi.in/pages/profile">
                                    <div class="media align-center">
                                        <div class="media-left">
                                            <div class="image">
                                                @if(Auth::User()->member->profile_photo)
                                                  <img class="rounded-circle avatar-xs" src="{{Auth::User()->member->profile_photo}}" alt="">
                                                @else
                                                    <img class="rounded-circle avatar-xs" src="{{url('assets/img/profile-pic.jpg')}}" alt="">
                                                @endif
                                            </div>
                                        </div>
                                        <div class="media-content ml-3">
                                            <h6 class="font-13 mb-0 strong">{{ Auth::User()->name }}  {{ Auth::User()->last_name }}</h6>
                                            <small>{{ Auth::User()->mobile }}</small>
                                        </div>
                                        <div class="media-right">
                                            <i data-feather="check"></i>
                                        </div>
                                    </div>
                                </a>
                                <a class="account-item" href="{{url('admin/my-profile')}}">
                                    <div class="media align-center">
                                        <div class="icon-wrap">
                                            <i class="far fa-user font-20"></i>
                                        </div>
                                        <div class="media-content ml-3">
                                            <h6 class="font-13 mb-0 strong"> My Profile</h6>
                                        </div>
                                    </div>
                                </a>
                                @if(Auth::user()->role_id != 1)
                                <a class="account-item" href="{{url('admin/my-recharge-commission')}}">
                                    <div class="media align-center">
                                        <div class="icon-wrap">
                                            <i class="fas fa-rupee-sign font-20"></i>
                                        </div>
                                        <div class="media-content ml-3">
                                            <h6 class="font-13 mb-0 strong">Commission Structure</h6>
                                        </div>
                                    </div>
                                </a>
                                @endif

                                <a class="account-item" href="{{url('admin/activity-logs')}}">
                                    <div class="media align-center">
                                        <div class="icon-wrap">
                                            <i class="far fa-clock font-20"></i>
                                        </div>
                                        <div class="media-content ml-3">
                                            <h6 class="font-13 mb-0 strong">Activity Logs</h6>
                                        </div>
                                    </div>
                                </a>

                                @if(Auth::User()->role_id == 1)
                                    <a class="account-item" href="{{url('admin/transaction-pin')}}">
                                        <div class="media align-center">
                                            <div class="icon-wrap">
                                                <i class="fas fa-lock font-20"></i>
                                            </div>
                                            <div class="media-content ml-3">
                                                <h6 class="font-13 mb-0 strong"> Transaction Pin</h6>
                                            </div>
                                        </div>
                                    </a>
                                @endif


                                <hr class="account-divider">
                                <a class="account-item"
                                   href="{{ route('logout') }}"
                                   onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                    <div class="media align-center">
                                        <div class="icon-wrap">
                                            <i class="las la-sign-out-alt font-20"></i>
                                        </div>
                                        <div class="media-content ml-3">
                                            <h6 class="font-13 mb-0 strong ">Logout</h6>
                                        </div>
                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                          @csrf
                                        </form>
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


                <li class="menu {{ (request()->is('admin/dashboard')) ? 'active' : '' }}">
                    <a href="{{url('admin/dashboard')}}" data-active="{{ (request()->is('admin/dashboard')) ? 'true' : '' }}" aria-expanded="false"
                       class="dropdown-toggle" >
                        <div class="">
                            <i class="las la-home"></i>
                            <span> Dashboard</span>
                        </div>
                    </a>
                </li>

                @if(Auth::User()->role_id == 1)
                    <li class="menu-title">Admin Menu</li>
                    {{--Start Master Menu--}}
                    <li class="menu {{ (request()->is('admin/bank-master','admin/role-master', 'admin/status-master','admin/service-master','admin/payment-method','admin/payout-beneficiary-master','admin/agent-onboarding-list','admin/contact-enquiry','admin/company-staff/welcome','admin/company-staff/permission/*','admin/broadcast')) ? 'active' : '' }}">
                        <a href="#admin_master" data-active="false" data-toggle="collapse" aria-expanded="false"
                           class="dropdown-toggle">
                            <div class="">
                                <i class="las la-book-open"></i>
                                <span> Master </span>
                            </div>
                            <div>
                                <i class="las la-angle-right sidemenu-right-icon"></i>
                            </div>
                        </a>
                        <ul class="collapse submenu list-unstyled {{ (request()->is('admin/bank-master','admin/role-master', 'admin/status-master','admin/service-master','admin/payment-method','admin/payout-beneficiary-master','admin/agent-onboarding-list','admin/contact-enquiry','admin/company-staff/welcome','admin/company-staff/permission/*','admin/broadcast')) ? 'show' : '' }}" id="admin_master" data-parent="#accordionExample">
                            <li><a data-active="{{ (request()->is('admin/bank-master')) ? 'true' : '' }}" href="{{url('admin/bank-master')}}">Bank Master </a></li>
                            <li><a data-active="{{ (request()->is('admin/role-master')) ? 'true' : '' }}" href="{{url('admin/role-master')}}">Role Master </a></li>
                            <li><a data-active="{{ (request()->is('admin/status-master')) ? 'true' : '' }}" href="{{url('admin/status-master')}}">Status Master </a></li>
                            <li><a data-active="{{ (request()->is('admin/service-master')) ? 'true' : '' }}" href="{{url('admin/service-master')}}">Service Master </a></li>
                            <li><a data-active="{{ (request()->is('admin/payment-method')) ? 'true' : '' }}" href="{{url('admin/payment-method')}}">Payment Method</a></li>
                            @if(Auth::User()->company->payout == 1 && Auth::User()->profile->payout == 1)
                                <li><a data-active="{{ (request()->is('admin/payout-beneficiary-master')) ? 'true' : '' }}" href="{{url('admin/payout-beneficiary-master')}}">Payout Beneficiary </a></li>
                            @endif
                            @if(Auth::User()->company->aeps == 1 && Auth::User()->role_id == 1)
                                <li><a data-active="{{ (request()->is('admin/agent-onboarding-list')) ? 'true' : '' }}" href="{{url('admin/agent-onboarding-list')}}">Agent Onboarding List </a></li>
                            @endif

                            <li><a data-active="{{ (request()->is('admin/contact-enquiry')) ? 'true' : '' }}" href="{{url('admin/contact-enquiry')}}">Contact Enquiry</a></li>
                            <li><a data-active="{{ (request()->is('admin/company-staff/welcome')) ? 'true' : '' }}" href="{{url('admin/company-staff/welcome')}}">Staff Permission</a></li>
                            @if(Auth::User()->role_id == 1 && Auth::User()->company->cashfree == 1)
                                <li><a data-active="false" href="{{url('admin/cashfree-gateway-master')}}">Cashfree Master</a></li>
                            @endif
                            <li><a data-active="{{ (request()->is('admin/broadcast')) ? 'true' : '' }}" href="{{url('admin/broadcast')}}">Broadcast</a></li>
                        </ul>
                    </li>
                    {{--End Master Menu--}}

                    {{--Start Api Master Menu--}}
                    <li class="menu {{ (request()->is('admin/provider-master','admin/api-master','admin/view-api-provider/*','admin/webhook-setting/*','admin/webhooks-logs/*','admin/response-setting/*','admin/denomination-wise-api','admin/number-series-master','admin/state-wise-api','admin/state-provider-setting/*','admin/backup-api-master','admin/api-switching','admin/user-operator-limit','admin/view-operator-limit/*','admin/vendor-payment/welcome')) ? 'active' : '' }}">
                        <a href="#admin_api_master" data-active="false" data-toggle="collapse" aria-expanded="false"
                           class="dropdown-toggle">
                            <div class="">
                                <i class="las la-briefcase"></i>
                                <span> Api Master </span>
                            </div>
                            <div>
                                <i class="las la-angle-right sidemenu-right-icon"></i>
                            </div>
                        </a>
                        <ul class="collapse submenu list-unstyled {{ (request()->is('admin/provider-master','admin/api-master','admin/view-api-provider/*','admin/webhook-setting/*','admin/webhooks-logs/*','admin/response-setting/*','admin/denomination-wise-api','admin/number-series-master','admin/state-wise-api','admin/state-provider-setting/*','admin/backup-api-master','admin/api-switching','admin/user-operator-limit','admin/view-operator-limit/*','admin/vendor-payment/welcome')) ? 'show' : '' }}" id="admin_api_master"
                            data-parent="#accordionExample">
                            <li><a data-active="{{ (request()->is('admin/provider-master')) ? 'true' : '' }}" href="{{url('admin/provider-master')}}">Provider Master </a></li>
                            <li><a data-active="{{ (request()->is('admin/api-master')) ? 'true' : '' }}" href="{{url('admin/api-master')}}">Api Master </a></li>
                            <li><a data-active="{{ (request()->is('admin/denomination-wise-api')) ? 'true' : '' }}" href="{{url('admin/denomination-wise-api')}}">Denomination Wise Api </a></li>
                            <li><a data-active="{{ (request()->is('admin/number-series-master')) ? 'true' : '' }}" href="{{url('admin/number-series-master')}}">Number Series Master </a></li>
                            <li><a data-active="{{ (request()->is('admin/state-wise-api')) ? 'true' : '' }}" href="{{url('admin/state-wise-api')}}">State Wise Api </a></li>
                            <li><a data-active="{{ (request()->is('admin/backup-api-master')) ? 'true' : '' }}" href="{{url('admin/backup-api-master')}}">Backup Api Master </a></li>
                            <li><a data-active="{{ (request()->is('admin/api-switching')) ? 'true' : '' }}" href="{{url('admin/api-switching')}}">Api Switching </a></li>
                            <li><a data-active="{{ (request()->is('admin/user-operator-limit')) ? 'true' : '' }}" href="{{url('admin/user-operator-limit')}}">User Operator Limit </a></li>
                            @if(Auth::User()->company->vendor_payment == 1)
                                <li><a data-active="{{ (request()->is('admin/vendor-payment/welcome')) ? 'true' : '' }}" href="{{url('admin/vendor-payment/welcome')}}">Api Vendor Payment </a></li>
                            @endif
                        </ul>
                    </li>
                    {{--End Api Master Menu--}}

                    {{--Start Settings Menu--}}
                    <li class="menu {{ (request()->is('admin/company-settings','admin/site-setting/welcome','admin/sms-template/welcome','admin/package-settings','admin/commission-setup','admin/set-operator-commission','admin/bank-settings','admin/logo-upload','admin/service-banner','admin/notification/welcome')) ? 'active' : '' }}">
                        <a href="#admin_settings" data-active="false" data-toggle="collapse" aria-expanded="false"
                           class="dropdown-toggle">
                            <div class="">
                                <i class="las la-cog"></i>
                                <span> Settings </span>
                            </div>
                            <div>
                                <i class="las la-angle-right sidemenu-right-icon"></i>
                            </div>
                        </a>
                        <ul class="collapse submenu list-unstyled {{ (request()->is('admin/company-settings','admin/site-setting/welcome','admin/sms-template/welcome','admin/package-settings','admin/commission-setup','admin/set-operator-commission','admin/bank-settings','admin/logo-upload','admin/service-banner','admin/notification/welcome')) ? 'show' : '' }}" id="admin_settings" data-parent="#accordionExample">
                            <li><a data-active="{{ (request()->is('admin/company-settings')) ? 'true' : '' }}" href="{{url('admin/company-settings')}}">Company Settings </a></li>
                            <li><a data-active="{{ (request()->is('admin/site-setting/welcome')) ? 'true' : '' }}" href="{{url('admin/site-setting/welcome')}}">Site Settings </a></li>
                            <li><a data-active="{{ (request()->is('admin/sms-template/welcome')) ? 'true' : '' }}" href="{{url('admin/sms-template/welcome')}}">Sms Template </a></li>
                            <li><a data-active="{{ (request()->is('admin/package-settings')) ? 'true' : '' }}" href="{{url('admin/package-settings')}}">Package Settings </a></li>
                            <li><a data-active="{{ (request()->is('admin/bank-settings')) ? 'true' : '' }}" href="{{url('admin/bank-settings')}}">Bank Settings </a></li>
                            <li><a data-active="{{ (request()->is('admin/logo-upload')) ? 'true' : '' }}" href="{{url('admin/logo-upload')}}">Logo Upload </a></li>
                            <li><a data-active="{{ (request()->is('admin/service-banner')) ? 'true' : '' }}" href="{{url('admin/service-banner')}}">Service Banner </a></li>
                            <li><a data-active="{{ (request()->is('admin/notification/welcome')) ? 'true' : '' }}" href="{{url('admin/notification/welcome')}}">Notification Settings </a></li>
                        </ul>
                    </li>
                    {{--End Settings Menu--}}


                    {{--Start Website Master Menu--}}
                    <li class="menu {{ (request()->is('admin/dynamic-page','admin/create-navigation','admin/edit-navigation/*','admin/add-content/*','admin/front-banners')) ? 'active' : '' }}">
                        <a href="#admin_website_master" data-active="false" data-toggle="collapse" aria-expanded="false"
                           class="dropdown-toggle">
                            <div class="">
                                <i class="las la-globe"></i>
                                <span> Website Master </span>
                            </div>
                            <div>
                                <i class="las la-angle-right sidemenu-right-icon"></i>
                            </div>
                        </a>
                        <ul class="collapse submenu list-unstyled {{ (request()->is('admin/dynamic-page','admin/create-navigation','admin/edit-navigation/*','admin/add-content/*','admin/front-banners')) ? 'show' : '' }}" id="admin_website_master"
                            data-parent="#accordionExample">
                            <li><a data-active="{{ (request()->is('admin/dynamic-page')) ? 'true' : '' }}" href="{{url('admin/dynamic-page')}}">Dynamic Page </a></li>
                            <li><a data-active="{{ (request()->is('admin/front-banners')) ? 'true' : '' }}" href="{{url('admin/front-banners')}}">Front Banners </a></li>
                        </ul>
                    </li>
                    {{--End Website Master Menu--}}

                    {{--Start Whatsapp Master Menu--}}
                    @php
                        $sitesettings = App\Sitesetting::where('company_id', Auth::User()->company_id)->first();
                    @endphp
                    @if($sitesettings->whatsapp == 1)
                        <li class="menu {{ (request()->is('admin/whatsapp/role-wise')) ? 'active' : '' }}">
                            <a href="#admin_whatsapp_master" data-active="false" data-toggle="collapse"
                               aria-expanded="false"
                               class="dropdown-toggle">
                                <div class="">
                                    <i class="lab la-whatsapp"></i>
                                    <span> Whatsapp </span>
                                </div>
                                <div>
                                    <i class="las la-angle-right sidemenu-right-icon"></i>
                                </div>
                            </a>
                            <ul class="collapse submenu list-unstyled {{ (request()->is('admin/whatsapp/role-wise')) ? 'show' : '' }} " id="admin_whatsapp_master"
                                data-parent="#accordionExample">
                                <li><a data-active="{{ (request()->is('admin/whatsapp/role-wise')) ? 'true' : '' }}" href="{{url('admin/whatsapp/role-wise')}}">Send Role Wise </a></li>
                            </ul>
                        </li>
                    @endif
                    {{--End Whatsapp Master Menu--}}



                @endif
                <li class="menu-title"> Common Menu</li>

                {{--Start Member Menu--}}
                <li class="menu {{ (request()->is('admin/member-list/*','admin/create-user/*','admin/view-update-users/*','admin/view-user-kyc/*','admin/parent-down-users/*/*','admin/suspended-users','admin/not-working-users','admin/user-ledger-report/*')) ? 'active' : '' }}">
                    <a href="#admin_members" data-active="false" data-toggle="collapse" aria-expanded="false"
                       class="dropdown-toggle">
                        <div class="">
                            <i class="las la-users"></i>
                            <span> Members </span>
                        </div>
                        <div>
                            <i class="las la-angle-right sidemenu-right-icon"></i>
                        </div>
                    </a>
                    <ul class="collapse submenu list-unstyled {{ (request()->is('admin/member-list/*','admin/create-user/*','admin/view-update-users/*','admin/view-user-kyc/*','admin/parent-down-users/*/*','admin/suspended-users','admin/not-working-users','admin/user-ledger-report/*')) ? 'show' : '' }}" id="admin_members" data-parent="#accordionExample">
                        @if(Auth::User()->role_id == 1)
                            @foreach(App\Role::where('id', '>', Auth::user()->role_id)->where('status_id', 1)->get() as $value)
                                @php
                                    $library = new App\Library\MemberLibrary();
                                    $my_down_member = $library->my_down_member(Auth::User()->role_id, Auth::User()->company_id, Auth::id());
                                    $totalMembers = App\User::whereIn('id', $my_down_member)->where('role_id', $value->id)->count();
                                @endphp
                                <li aria-haspopup="{{ (request()->is('admin/member-list'.'/'.$value->role_slug)) ? 'true' : '' }}"><a href="{{url('admin/member-list')}}/{{ $value->role_slug }}" class="slide-item"> {{ $value->role_title }} ({{ $totalMembers}})</a></li>
                            @endforeach
                        @else
                            @foreach(App\Role::where('id', '>', Auth::user()->role_id)->where('status_id', 1)->whereNotIn('id', [9,10])->get() as $value)
                                @php
                                    $library = new App\Library\MemberLibrary();
                                    $my_down_member = $library->my_down_member(Auth::User()->role_id, Auth::User()->company_id, Auth::id());
                                    $totalMembers = App\User::whereIn('id', $my_down_member)->where('role_id', $value->id)->count();
                                @endphp
                                <li aria-haspopup="{{ (request()->is('admin/member-list'.'/'.$value->role_slug)) ? 'true' : '' }}"><a href="{{url('admin/member-list')}}/{{ $value->role_slug }}" class="slide-item"> {{ $value->role_title }} ({{ $totalMembers}})</a></li>
                            @endforeach
                        @endif
                        <li aria-haspopup="{{ (request()->is('admin/suspended-users')) ? 'true' : '' }}"><a href="{{url('admin/suspended-users')}}" class="slide-item">Suspended User</a></li>
                        <li aria-haspopup="{{ (request()->is('admin/not-working-users')) ? 'true' : '' }}"><a href="{{url('admin/not-working-users')}}" class="slide-item">Not Working Users</a></li>
                    </ul>
                </li>
                {{--End Member Menu--}}

                {{--Start Report Menu--}}
                <li class="menu {{ (request()->is('admin/all-transaction-report','admin/recharge-report','admin/pancard-report','admin/auto-payment-report','admin/pending-transaction','admin/profit-distribution')) ? 'active' : '' }}">
                    <a href="#admin_reports" data-active="false" data-toggle="collapse" aria-expanded="false"
                       class="dropdown-toggle">
                        <div class="">
                            <i class="las la-globe"></i>
                            <span> Report </span>
                        </div>
                        <div>
                            <i class="las la-angle-right sidemenu-right-icon"></i>
                        </div>
                    </a>
                    <ul class="collapse submenu list-unstyled {{ (request()->is('admin/all-transaction-report','admin/recharge-report','admin/pancard-report','admin/auto-payment-report','admin/pending-transaction','admin/profit-distribution')) ? 'show' : '' }}" id="admin_reports"
                        data-parent="#accordionExample">
                        <li><a data-active="{{ (request()->is('admin/all-transaction-report')) ? 'true' : '' }}" href="{{url('admin/all-transaction-report')}}" class="slide-item"> All Transaction Report</a></li>
                        @if(Auth::User()->company->recharge == 1 && Auth::User()->profile->recharge == 1)
                            <li><a data-active="{{ (request()->is('admin/recharge-report')) ? 'true' : '' }}" href="{{url('admin/recharge-report')}}" class="slide-item">Recharge Report</a></li>
                        @endif

                        @if(Auth::User()->company->pancard == 1 && Auth::User()->profile->pancard == 1)
                            <li><a data-active="{{ (request()->is('admin/pancard-report')) ? 'true' : '' }}" href="{{url('admin/pancard-report')}}" class="slide-item">Pancard Report</a></li>
                        @endif

                        @if(Auth::User()->company->collection == 1)
                            <li><a data-active="{{ (request()->is('admin/auto-payment-report')) ? 'true' : '' }}" href="{{url('admin/auto-payment-report')}}" class="slide-item">Auto Payment Report</a></li>
                        @endif

                        <li><a data-active="{{ (request()->is('admin/pending-transaction')) ? 'true' : '' }}" href="{{url('admin/pending-transaction')}}" class="slide-item">Pending Transaction</a></li>
                        @if(Auth::User()->role_id == 1)
                            <li><a data-active="{{ (request()->is('admin/profit-distribution')) ? 'true' : '' }}" href="{{url('admin/profit-distribution')}}" class="slide-item">Profit Distribution</a></li>
                            <li><a data-active="{{ (request()->is('admin/refund-manager')) ? 'true' : '' }}" href="{{url('admin/refund-manager')}}" class="slide-item">Refund Manager</a></li>
                            <li><a data-active="{{ (request()->is('admin/income/api-summary-report')) ? 'true' : '' }}" href="{{url('admin/income/api-summary-report')}}" class="slide-item">Api Summary</a></li>
                        @endif
                        <li><a data-active="{{ (request()->is('admin/income/operator-wise-sale')) ? 'true' : '' }}" href="{{url('admin/income/operator-wise-sale')}}" class="slide-item">Operator Wise Sale</a></li>
                        <li><a data-active="{{ (request()->is('admin/ledger-report')) ? 'true' : '' }}" href="{{url('admin/ledger-report')}}" class="slide-item"> Ledger Report</a></li>
                    </ul>
                </li>
                {{--End Website Master Menu--}}

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

                                @if(Auth::User()->role_id == 1)
                                    <hr>
                                    <a href="javascript: void(0);" class="text-reset item-hovered d-block p-2">
                                        <p class="text-muted mb-0">Api Balance<span class="float-right"
                                                                                    id="dashboard_api_balance"></span>
                                        </p>
                                    </a>

                                    <hr>
                                    <a href="javascript: void(0);" class="text-reset item-hovered d-block p-2">
                                        <p class="text-muted mb-0">Aeps Api Balance<span class="float-right"
                                                                                         id="dashboard_aeps_api_balance"></span>
                                        </p>
                                    </a>
                                @endif

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

<!--- Datepicker js --->
<script src="{{ $cdnLink}}themes2/plugins/flatpickr/flatpickr.js"></script>
<script src="{{ $cdnLink}}themes2/plugins/flatpickr/custom-flatpickr.js"></script>
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
