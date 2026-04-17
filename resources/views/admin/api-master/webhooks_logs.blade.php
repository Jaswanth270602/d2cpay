@extends('admin.layout.header')
@section('content')



    <div class="main-content-body">
        <div class="row row-sm">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">{{ $page_title }}</h4>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
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
            <!--/div-->

        </div>

    </div>
    </div>
    </div>





@endsection