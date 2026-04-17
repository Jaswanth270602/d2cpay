@extends('admin.layout.header')
@section('content')



    <div class="main-content-body">
        <div class="row row-sm">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">State Wise Api</h4>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
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
            <!--/div-->

        </div>

    </div>
    </div>
    </div>


@endsection