
<!-- main-header opened -->
<div class="main-header nav nav-item hor-header">
    <div class="container">
        <div class="main-header-left ">
            <a class="animated-arrow hor-toggle horizontal-navtoggle"><span></span></a><!-- sidebar-toggle-->
            <a class="header-brand" href="{{url('agent/dashboard')}}">
                <img src="{{$cdnLink}}{{$company_logo}}" class="logo-white ">
                <img src="{{$cdnLink}}{{$company_logo}}" class="logo-default">
                <img src="{{$cdnLink}}{{$company_logo}}" class="icon-white">
                <img src="{{$cdnLink}}{{$company_logo}}" class="icon-default">
            </a>

            <div class="main-header-center  ml-4">
                <ul class="header-megamenu-dropdown  nav">

                    <li class="nav-item">
                        <div class="dropdown-menu-rounded btn-group dropdown" >
                            <button aria-expanded="false" aria-haspopup="true" class="btn btn-link dropdown-toggle" data-toggle="dropdown" id="dropdownMenuButton3" type="button"><span><i class="nav-link-icon fas fa-comments"></i> Dispute </span></button>
                            <div class="dropdown-menu-lg dropdown-menu"  x-placement="bottom-left">
                                <a  class="dropdown-item  mt-2" href="{{url('agent/pending-dispute')}}"><i class="dropdown-icon"></i>Pending Dispute</a>
                                <a  class="dropdown-item  mt-2" href="{{url('agent/solve-dispute')}}"><i class="dropdown-icon"></i>Solve Dispute</a>

                            </div>
                        </div>
                    </li>

                    @if(Auth::User()->company->ecommerce == 1 && Auth::User()->profile->ecommerce == 1)
                        @php $total_cart = App\Cart::where('user_id', Auth::id())->count(); @endphp
                    <li class="nav-item float-right">
                        <div class="btn-group dropdown">
                            <button aria-expanded="false" aria-haspopup="true" class="btn btn-link dropdown-toggle" data-toggle="dropdown" id="dropdownMenuButton2" type="button"><span><i class="fas fa-cart-plus"></i> {{ $total_cart }} Cart </span></button>
                            <div  class="dropdown-menu" >
                                <div class="dropdown-menu-header header-img p-3">
                                    <div class="drop-menu-inner">
                                        <div class="header-content text-left d-flex">
                                            <div class="text-white">
                                                <h6 class="menu-header-subtitle mb-0">Shopping Cart</h6>
                                            </div>
                                            <div class="my-auto ml-auto">
                                                <span class="badge badge-pill badge-warning float-right">{{ $total_cart }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="setting-scroll">
                                    <div>
                                        <div class="setting-menu ">
                                            <a  class="dropdown-item" href="{{url('agent/ecommerce/view-cart')}}"><i class="fas fa-shopping-cart tx-16 mr-2 mt-1"></i>View Cart</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="{{url('agent/ecommerce/my-wishlist')}}"><i class="fas fa-heart tx-16 mr-2"></i>My Wishlist</a>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </li>
                    @endif

                    @if(Auth::User()->company->ecommerce == 1 && Auth::User()->profile->seller == 1)
                        <li class="nav-item float-right">
                            <div class="btn-group dropdown">
                                <button aria-expanded="false" aria-haspopup="true" class="btn btn-link dropdown-toggle" data-toggle="dropdown" id="dropdownMenuButton2" type="button"><span><i class="fas fa-shopping-cart"></i> Ecommerce Master </span></button>
                                <div  class="dropdown-menu" >
                                    <div class="dropdown-menu-header header-img p-3">
                                        <div class="drop-menu-inner">
                                            <div class="header-content text-left d-flex">
                                                <div class="text-white">
                                                    <h6 class="menu-header-subtitle mb-0">Ecommerce Master</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="setting-scroll">
                                        <div>
                                            <div class="setting-menu ">
                                                <a  class="dropdown-item" href="{{url('agent/ecommerce-seller/product-list')}}">Product List</a>
                                                <div class="dropdown-divider"></div>
                                                <a  class="dropdown-item" href="{{url('agent/ecommerce-seller/my-product')}}">My Product</a>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item" href="{{url('agent/ecommerce-seller/order-request')}}">Order Request</a>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </li>

                    @endif

                    <li class="nav-item"><marquee direction="left" style="color: white; font-weight: bold;">{{ $news }} </marquee></li>
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
                            <h6 class="menu-header-title text-white mb-0">{{ Auth::User()->unreadNotifications->count() }} new Notifications</h6>
                        </div>
                        <div class="my-auto ml-auto">
                            <a class="badge badge-pill badge-warning float-right" href="{{url('agent/notification/mark-all-read')}}">Mark All Read</a>
                        </div>
                    </div>
                    <div class="main-notification-list Notification-scroll">

                        @foreach(Auth::User()->unreadNotifications as $value)
                            <a class="d-flex p-3 border-bottom" href="{{url('agent/notification/view')}}/{{$value->id}}">
                                <div class="ml-3">
                                    <h5 class="notification-label mb-1">{{ Str::limit($value->data['letter']['title'], 25)  }}</h5>
                                    <div class="notification-subtext">{{ Carbon\Carbon::parse($value->created_at)->diffForHumans() }}</div>
                                </div>
                                <div class="ml-auto" >
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
                        <img src="{{$cdnLink}}{{Auth::User()->member->profile_photo}}" alt="user-img" class="rounded-circle mCS_img_loaded">
                    @else
                        <img src="{{url('assets/img/profile-pic.jpg')}}" alt="user-img" class="rounded-circle mCS_img_loaded">
                    @endif
                    <span></span></a>
                <div class="dropdown-menu">
                    <div class="main-header-profile header-img">
                        @if(Auth::User()->member->profile_photo)
                            <div class="main-img-user"><img alt="" src="{{$cdnLink}}{{Auth::User()->member->profile_photo}}"></div>
                        @else
                            <div class="main-img-user"><img alt="" src="{{url('assets/img/profile-pic.jpg')}}"></div>
                        @endif
                        <h6>{{ Auth::User()->name }}</h6>
                        <span>({{ Auth::User()->role->role_title }})</span>
                    </div>
                    <a class="dropdown-item" href="{{url('agent/my-profile')}}"><i class="far fa-user"></i> My Profile</a>
                    <a class="dropdown-item" href="{{url('agent/my-settings')}}"><i class="fas fa-user-cog"></i> Settings</a>
                    <a class="dropdown-item" href="{{url('agent/activity-logs')}}"><i class="far fa-clock"></i> Activity Logs</a>
                    <a class="dropdown-item" href="{{url('agent/my-recharge-commission')}}"><i class="fas fa-rupee-sign"></i> Commission Structure</a>
                    <a class="dropdown-item" href="{{url('agent/certificate')}}" target="_blank"><i class="fas fa-certificate"></i> Certificate</a>
                    @if(Auth::User()->company->transaction_pin == 1)
                        <a class="dropdown-item" href="{{url('agent/transaction-pin')}}"><i class="fas fa-lock"></i> Transaction Pin</a>
                    @endif
                    @if(Auth::User()->role_id == 10)
                        <a class="dropdown-item" href="{{url('agent/developer/settings')}}"><i class="fas fa-file-code"></i>  Developer Zone</a>
                    @endif
                    <a class="dropdown-item" href="{{ route('logout') }}"
                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();"> <i class="fas fa-sign-out-alt"></i>
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