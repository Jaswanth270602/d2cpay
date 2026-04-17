<link href="{{url('assets/plugins/sidebar/sidebar.css')}}" rel="stylesheet">


<!-- Col -->

<div class="col-lg-4 col-xl-3">
    <div class="card mg-b-20 compose-mail">
        <div class="main-content-left main-content-left-mail card-body">
            <div class="main-mail-menu" style="max-height:50%; overflow-y:auto;">
                <nav class="nav main-nav-column mg-b-20">
                    @php
                        $library = new \App\Library\BasicLibrary;
                        $companyActiveService = $library->getCompanyActiveService(Auth::id());
                        $userActiveService = $library->getUserActiveService(Auth::id());
                    @endphp
                    @foreach(App\Models\Service::where('status_id', 1)
                                    ->whereIn('id', $companyActiveService)
                                    ->whereIn('id', $userActiveService)
                                    ->whereNotIn('id', [23])
                                    ->get() as $value)
                    <a class="nav-link" href="{{url('agent/service-wise-commission')}}/{{ $value->id }}">{{ $value->service_name }}</a>
                    @endforeach
                </nav>

            </div><!-- main-mail-menu -->
        </div>
    </div>
</div>
<!-- /Col -->
