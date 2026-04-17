@extends('admin.layout.header')
@section('content')

    <script type="text/javascript">
        function viewDetails (id){
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/gateway-charges/view-charges-details')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        $("#view_id").val(msg.details.id);
                        $("#view_gateway_method").val(msg.details.gateway_method);
                        $("#view_method_code").val(msg.details.method_code);
                        $("#view_commission").val(msg.details.commission);
                        $("#view_type").val(msg.details.type);
                        $("#view_charges_model").modal('show');
                    }else{
                        swal("Faild", msg.message, "error");
                    }
                }
            });
        }

        function updateDetails (){
            $("#update_btn").hide();
            $("#update_btn_loader").show();
            var token = $("input[name=_token]").val();
            var id = $("#view_id").val();
            var method_code = $("#view_method_code").val();
            var commission = $("#view_commission").val();
            var type = $("#view_type").val();
            var dataString = 'id=' + id + '&method_code=' + method_code + '&commission=' + commission + '&type=' + type +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/gateway-charges/update-charges-details')}}",
                data: dataString,
                success: function (msg) {
                    $("#update_btn").show();
                    $("#update_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#view_id_errors").text(msg.errors.id);
                        $("#view_method_code_errors").text(msg.errors.method_code);
                        $("#view_commission_errors").text(msg.errors.commission);
                        $("#view_type_errors").text(msg.errors.type);
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
                            <table class="@if(Auth::User()->company->table_format == 1)table text-md-nowrap @else display responsive nowrap @endif" id="example1">
                                <thead>
                                <tr>
                                    <th class="wd-25p border-bottom-0">Method Name</th>
                                    <th class="wd-25p border-bottom-0">Method Code</th>
                                    <th class="wd-25p border-bottom-0">Charges</th>
                                    <th class="wd-25p border-bottom-0">Type</th>
                                    <th class="wd-25p border-bottom-0">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($gatewaycharges as $value)
                                    <tr>
                                        <td>{{ $value->gatewayslab->slab_name }}</td>
                                        <td>{{ $value->method_code }}</td>
                                        <td>{{ $value->commission }}</td>
                                        <td>@if($value->type == 0) % @else  Rs @endif</td>
                                        <td><button class="btn btn-danger btn-sm" onclick="viewDetails({{ $value->id }})">Update</button></td>
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



    {{--view Charges Model--}}
    <div class="modal fade" id="view_charges_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2" aria-hidden="true">
        <div class="modal-dialog modal-dialog-slideout" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Update Charges</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">
                            <input type="hidden" id="view_id">

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Method</label>
                                    <input type="text" id="view_gateway_method" class="form-control" placeholder="Method" readonly>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_gateway_method_errors"></li>
                                    </ul>
                                </div>
                            </div>


                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Method Code</label>
                                    <input type="text" id="view_method_code" class="form-control" placeholder="Method Code">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_method_code_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Type</label>
                                    <select class="form-control" id="view_type">
                                        <option value="0">%</option>
                                        <option value="1">Rs</option>
                                    </select>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_type_errors"></li>
                                    </ul>
                                </div>
                            </div>


                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Charges</label>
                                    <input type="text" id="view_commission" class="form-control" placeholder="Charges">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_commission_errors"></li>
                                    </ul>
                                </div>
                            </div>





                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="update_btn" onclick="updateDetails()">Update</button>
                    <button class="btn btn-primary" type="button"  id="update_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    {{--view Charges Model--}}


@endsection