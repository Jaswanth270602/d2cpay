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
                            <div class="table-responsive">
                                <table class="table text-md-nowrap" id="example1">
                                    <thead>
                                    <tr>
                                        <th class="wd-15p border-bottom-0">Date</th>
                                        <th class="wd-15p border-bottom-0">Ip Address</th>
                                        <th class="wd-15p border-bottom-0">Logs</th>

                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($apiresponses as $value)
                                        @php
                                            $exploadmessage = explode(' ', $value->message);
                                            $callback_url = $exploadmessage[1];
                                        @endphp

                                        <tr>
                                            <td>{{ $value->created_at }}</td>
                                            <td>{{ $value->ip_address }}</td>
                                            <td>{{ url('').''.$callback_url }}</td>

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
        <!-- Main Body Ends -->


@endsection