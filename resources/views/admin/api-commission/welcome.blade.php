@extends('admin.layout.header')
@section('content')
    <script type="text/javascript">
        function saveBulkCommission (){
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var api_id = $("#api_id").val();
            var service_id = $("#service_id").val();
            var min_amount = $("#min_amount").val();
            var max_amount = $("#max_amount").val();
            var type = $("#type").val();
            var commission = $("#commission").val();
            var gst = $("#gst").val();
            var tds = $("#tds").val();
            var dataString = 'api_id=' + api_id + '&service_id=' + service_id + '&min_amount=' + min_amount + '&max_amount=' + max_amount + '&type=' + type + '&commission=' + commission + '&gst=' + gst +  '&tds=' + tds +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/store-api-wise-bulk-commission')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#service_id_errors").text(msg.errors.service_id);
                        $("#min_amount_errors").text(msg.errors.min_amount);
                        $("#max_amount_errors").text(msg.errors.max_amount);
                    }else{
                        swal("Faild", msg.message, "error");
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

                            <h4 class="card-title mg-b-2 mt-2">Api Name : {{ $api_name }}</h4>
                            <a href="{{url()->previous()}}">Back</a>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
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
                                        <td>
                                            <form action="{{ url('admin/api-commission/v1/view-providers') }}" method="POST" class="pull-right">
                                                @csrf
                                                @method('POST')
                                                <input type="hidden" name="api_id" value="{{ $api_id }}">
                                                <input type="hidden" name="provider_id" value="{{ $value->id }}">
                                                <button type="submit" class="btn btn-danger btn-sm">Update Commission</button>
                                            </form>
                                        </td>
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