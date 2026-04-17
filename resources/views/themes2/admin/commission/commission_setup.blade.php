@extends('themes2.admin.layout.header')
@section('content')
    <!--  Content Area Starts  -->
    <div id="content" class="main-content">
        <!-- Main Body Starts -->
    @include('themes2.admin.layout.breadcrumb')
    <!-- Main Body Starts -->
        <div class="layout-px-spacing">
            <div class="row layout-top-spacing">
                <!-- REVENUE ENDS-->
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
                    <div class="widget dashboard-table">
                        <div class="widget-heading">
                            <h5 class=""> {{ $page_title }}</h5>
                        </div>
                        <hr>
                        <div class="widget-content">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table text-md-nowrap" id="example1">
                                        <thead>
                                        <tr>
                                            <th class="wd-15p border-bottom-0">Provider id</th>
                                            <th class="wd-25p border-bottom-0">Provider Name</th>
                                            <th class="wd-25p border-bottom-0">Service</th>
                                            <th class="wd-25p border-bottom-0">Action</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($providers as $value)
                                            <tr>
                                                <td>{{ $value->id }}</td>
                                                <td>{{ $value->provider_name }}</td>
                                                <td>{{ $value->service->service_name }}</td>
                                                <td>{{ Form::open(array('url' => 'admin/set-operator-commission', 'class' => 'pull-right')) }}
                                                    {{ Form::hidden('scheme_id', $scheme_id) }}
                                                    {{ Form::hidden('provider_id', $value->id) }}
                                                    {{ Form::submit('Update Commission', array('class' => 'btn btn-danger btn-sm')) }}
                                                    {{ Form::close() }}
                                                </td>
                                            </tr>
                                        @endforeach

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!-- Main Body Ends -->


@endsection