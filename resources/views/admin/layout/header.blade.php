@if(Auth::User()->role_id == 2)
    @include('admin.layout.staff_header')
@else
    @include('admin.layout.main_header')
@endif
