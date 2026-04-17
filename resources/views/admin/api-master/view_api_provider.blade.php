@extends('admin.layout.header')
@section('content')
    <script type="text/javascript">

      function  update_commission(id) {
          $(".loader").show();
          var token = $("input[name=_token]").val();
          var operator_code = $("#operatorcode_"+id).val();
          var type = $("#type_"+id).val();
          var commission = $("#commission_"+id).val();
          var dataString = 'id=' + id + '&operator_code=' + operator_code + '&api_commission=' + commission + '&type=' + type + '&_token=' + token;
          $.ajax({
              type: "POST",
              url: "{{url('admin/update-api-provider')}}",
              data: dataString,
              success: function (msg) {
                  $(".loader").hide();
                  if (msg.status == 'success') {
                      swal("Success", msg.message, "success");
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
                            <h4 class="card-title mg-b-2 mt-2">Api Provider</h4>
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
                                    <th class="wd-15p border-bottom-0">Service Name</th>
                                    <th class="wd-15p border-bottom-0">Api Name</th>
                                    <th class="wd-15p border-bottom-0">Operator Code</th>
                                    <th class="wd-20p border-bottom-0">Type</th>
                                    <th class="wd-15p border-bottom-0">Commission</th>
                                    <th class="wd-15p border-bottom-0">Action</th>

                                </tr>
                                </thead>
                                <tbody>
                                <?php $i = 1 ?>
                                @foreach($apiprovider as $value)
                                    <tr>
                                        <td>{{ $i++ }}</td>
                                        <td>{{ $value->provider->provider_name }}</td>
                                        <td>{{ $value->provider->service->service_name }}</td>
                                        <td>{{ $value->api->api_name }}</td>
                                        <td> <input type="text"  value="{{ $value->operator_code }}" id="operatorcode_{{$value->id}}" class="form-control" placeholder="Operator Code"></td>
                                        <td><select class="form-control" id="type_{{ $value->id }}" style="width: 70px;">
                                                <option value="0" @if($value->type == 0) {{ "selected" }} @endif>%</option>
                                                <option value="1" @if($value->type == 1) {{ "selected" }} @endif>Rs</option>
                                            </select>
                                        </td>
                                        <td> <input type="text"  value="{{ $value->api_commission }}" class="form-control" id="commission_{{$value->id}}" placeholder="Commission"></td>
                                        <td><button class="btn btn-danger btn-sm" onclick="update_commission({{ $value->id }})">Update</button></td>
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