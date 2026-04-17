@extends('agent.layout.header')
@section('content')



    <div class="main-content-body">
        <div class="row row-sm">

            @include('agent.developer.left_side')

            <div class="col-lg-8 col-xl-9">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">{{ $page_title }}</h4>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table text-md-nowrap" id="example1">
                                <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0">Provider Id</th>
                                    <th class="wd-25p border-bottom-0">Provider name</th>
                                    <th class="wd-25p border-bottom-0">Service</th>
                                    <th class="wd-25p border-bottom-0">Min</th>
                                    <th class="wd-25p border-bottom-0">Max</th>
                                    <th class="wd-25p border-bottom-0">Status</th>
                                </tr>
                                </thead>
                                <tbody>

                                @foreach($providers as $value)
                                    <tr>
                                        <td>{{ $value->id }}</td>
                                        <td>{{ $value->provider_name }}</td>
                                        <td>{{ $value->service->service_name }}</td>
                                        <td>{{ $value->min_length }}</td>
                                        <td>{{ $value->max_length }}</td>
                                        <td>@if($value->status_id == 1)<span class="badge badge-success">Enabled</span> @else <span class="badge badge-danger">Disabled</span>  @endif</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!--/div-->

        </div>

    </div>
    </div>
    </div>



@endsection