<div class="col-lg-3 col-md-4 col-sm-5">
    <div class="user-account-sidebar">
        <aside class="user-info-wrapper">
            <div class="user-cover" style="background-image: url(https://askbootstrap.com/preview/osahan-fashion/images/user-cover-img.jpg);">
                <div class="info-label" data-toggle="tooltip" title="" data-original-title="Verified Account"><i class="icofont icofont-check-circled"></i></div>
            </div>
            <div class="user-info">
                <div class="user-avatar"><a class="edit-avatar" href="#"><i class="icofont icofont-edit"></i></a><img src="https://askbootstrap.com/preview/osahan-fashion/images/user-ava.jpg" alt="User"></div>
                <div class="user-data">
                    <h4>{{ Auth::User()->name }}  {{ Auth::User()->last_name }}</h4>
                    <span><i class="icofont icofont-ui-calendar"></i> Joined {{ Auth::User()->created_at->format('d-M-Y') }}</span>
                </div>
            </div>
        </aside>
        <nav class="list-group">
            <a class="list-group-item {{(Request::is('*my-account') ? 'active' : '')}}" href="{{url('ecommerce/my-account')}}"><i class="icofont icofont-ui-user fa-fw"></i> My Profile</a>
            <a class="list-group-item {{(Request::is('*my-addresses') ? 'active' : '')}}" href="{{url('ecommerce/my-addresses')}}"><i class="icofont icofont-location-pin fa-fw"></i> My Address</a>
            <a class="list-group-item {{(Request::is('*my-order') ? 'active' : '')}}" href="{{url('ecommerce/my-order')}}"><span><i class="icofont icofont-list fa-fw"></i> My Order</span> </a>
            <a class="list-group-item {{(Request::is('*my-wishlist') ? 'active' : '')}}" href="{{url('ecommerce/my-wishlist')}}"><span><i class="icofont icofont-heart-alt fa-fw"></i> Wish List</span></a>
            <a class="list-group-item" href="{{ route('logout') }}"
               onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();"> <i class="icofont icofont-logout fa-fw"></i>
                {{ __('Logout') }}
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </nav>
    </div>
</div>