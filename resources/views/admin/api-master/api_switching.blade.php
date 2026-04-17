@extends('admin.layout.header')
@section('content')

    <script type="text/javascript">
        function update_api(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var api_id = $("#apiid_"+id).val();
            var dataString = 'api_id=' + api_id + '&id=' + id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/update-api-switching')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                      //  setTimeout(function () { location.reload(1); }, 3000);
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }
    </script>

    <div class="main-content-body">
        <div class="row row-sm">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">Api Switching</h4>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
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
                                    <th class="wd-25p border-bottom-0">Api</th>

                                </tr>
                                </thead>
                                <tbody>

                                @foreach($providers as $value)
                                    <tr>
                                        <td>{{ $value->id }}</td>
                                        <td>{{ $value->provider_name }}</td>
                                        <td>{{ $value->service->service_name }}</td>
                                        <td>  <select class="form-control" id="apiid_{{ $value->id }}" onchange="update_api({{ $value->id }})">
                                                <option value="">Select Api</option>
                                                @foreach($apis as $api)
                                                    <option value="{{$api->id}}" @if($value->api_id == $api->id) selected="selected" @endif>{{$api->api_name}}</option>
                                                @endforeach
                                            </select></td>
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