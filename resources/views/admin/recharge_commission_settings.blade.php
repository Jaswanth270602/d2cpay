@extends('admin.layout.header')
@section('content')
    <script type="text/javascript">
        function update_recharge_commission(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var type = $("#type_"+id).val();
            var commission = $("#commission_"+id).val();
            var dataString = 'id=' + id + '&type=' + type + '&commission=' + commission + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/update-recharge-commission')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                      //  setTimeout(function () { location.reload(1); }, 3000);
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
                                    <th class="wd-15p border-bottom-0">Sr No</th>
                                    <th class="wd-15p border-bottom-0">Provider Name</th>
                                    <th class="wd-15p border-bottom-0">Service</th>
                                    <th class="wd-20p border-bottom-0">Commission Type</th>
                                    <th class="wd-15p border-bottom-0">Commission</th>
                                    <th class="wd-10p border-bottom-0">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $i = 1 ?>
                                @foreach($commissions as $value)
                                    <tr>
                                        <td>{{ $i++ }}</td>
                                        <td>{{ $value->provider->provider_name }}</td>
                                        <td>{{ $value->provider->service->service_name }}</td>
                                        <td><select class="form-control" id="type_{{ $value->id }}" style="width: 70px;">
                                                <option value="0" @if($value->type == 0) {{ "selected" }} @endif>%</option>
                                                <option value="1" @if($value->type == 1) {{ "selected" }} @endif>Rs</option>
                                            </select>
                                        </td>
                                        <td><input type="text" id="commission_{{ $value->id }}" value="{{ $value->commission }}" class="form-control"></td>
                                        <td><button type="button" class="btn btn-success" onclick="update_recharge_commission({{$value->id}})">Update</button></td>
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