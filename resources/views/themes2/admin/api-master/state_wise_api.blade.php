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
                                            <th class="wd-15p border-bottom-0">Id</th>
                                            <th class="wd-25p border-bottom-0">State Name</th>
                                            <th class="wd-25p border-bottom-0">Provider Setting</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($state as $value)
                                            <tr>
                                                <td>{{ $value->id }}</td>
                                                <td>{{ $value->name }}</td>
                                                <td><a href="{{url('admin/state-provider-setting')}}/{{$value->id}}" target="_blank" class="btn btn-success btn-sm"><i class="fa fa-plus-square" aria-hidden="true"></i> Provider Setting</a> </td>
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