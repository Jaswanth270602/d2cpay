<!-- Col -->
<div class="col-lg-4 col-xl-3">
    <div class="card mg-b-20 compose-mail">
        <div class="main-content-left main-content-left-mail card-body">
            <div class="main-mail-menu" style="max-height:50%; overflow-y:auto;">
                <nav class="nav main-nav-column mg-b-20">
                    @foreach(App\Models\Service::where('status_id', 1)->get() as $value)
                    <a class="nav-link" href="{{url('admin/service-wise-commission')}}/{{ $value->id }}">{{ $value->service_name }}</a>
                    @endforeach
                </nav>

            </div><!-- main-mail-menu -->
        </div>
    </div>
</div>
<!-- /Col -->
