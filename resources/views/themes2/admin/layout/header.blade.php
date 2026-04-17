

@if(Auth::User()->role_id == 2)
    @include('themes2.admin.layout.staff_header')
@else
    @include('themes2.admin.layout.main_header')
@endif