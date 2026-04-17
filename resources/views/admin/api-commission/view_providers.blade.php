@extends('admin.layout.header')
@section('content')

    <script type="text/javascript">
        function saveCommission() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var api_id = $("#api_id").val();
            var provider_id = $("#provider_id").val();
            var min_amount = $("#min_amount").val();
            var max_amount = $("#max_amount").val();
            var type = $("#type").val();
            var commission_type = $("#commission_type").val();
            var commission = $("#commission").val();
            var dataString = 'api_id=' + api_id + '&provider_id=' + provider_id + '&min_amount=' + min_amount + '&max_amount=' + max_amount + '&type=' + type + '&commission_type=' + commission_type + '&commission=' + commission +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/api-commission/v1/save-commission')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#min_amount_errors").text(msg.errors.min_amount);
                        $("#max_amount_errors").text(msg.errors.max_amount);
                        $("#type_errors").text(msg.errors.type);
                        $("#commission_type_errors").text(msg.errors.commission_type);
                        $("#commission_errors").text(msg.errors.commission);
                    }else{
                        swal("Faild", msg.message, "error");
                    }
                }
            });
        }

        function deleteRecord(id) {
            swal({
                    title: "Are you sure?",
                    text: 'you want to delete this Slab ',
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Yes, delete it!",
                    cancelButtonText: "No, cancel",
                    closeOnConfirm: false,
                    closeOnCancel: false
                },
                function(isConfirm) {
                    if (isConfirm) {
                        $(".loader").show();
                        var token = $("input[name=_token]").val();
                        var dataString = 'id=' + id +  '&_token=' + token;
                        $.ajax({
                            type: "POST",
                            url: "{{url('admin/api-commission/v1/delete-record')}}",
                            data: dataString,
                            success: function (msg) {
                                $(".loader").hide();
                                if (msg.status == 'success') {
                                    swal("Deleted!", msg.message, "success");
                                    setTimeout(function () { location.reload(1); }, 3000);
                                }else{
                                    swal("Faild", msg.message, "error");
                                }
                            }
                        });
                    }
                }
            );
        }

        function viewCommission(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/api-commission/v1/view-provider-commission')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        $("#view_id").val(msg.commission.id);
                        $("#view_provider_name").val(msg.commission.provider_name);
                        $("#view_min_amount").val(msg.commission.min_amount);
                        $("#view_max_amount").val(msg.commission.max_amount);
                        $("#view_type").val(msg.commission.type);
                        $("#view_commission_type").val(msg.commission.commission_type);
                        $("#view_commission").val(msg.commission.commission);
                        $("#view_commission_model").modal('show');
                    }else{
                        swal("Faild", msg.message, "error");
                    }
                }
            });
        }

        function updateComission() {
            $("#update_btn").hide();
            $("#update_btn_loader").show();
            var token = $("input[name=_token]").val();
            var id = $("#view_id").val();
            var min_amount = $("#view_min_amount").val();
            var max_amount = $("#view_max_amount").val();
            var type = $("#view_type").val();
            var commission_type = $("#view_commission_type").val();
            var commission = $("#view_commission").val();
            var dataString = 'id=' + id + '&min_amount=' + min_amount + '&max_amount=' + max_amount + '&commission=' + commission + '&commission_type=' + commission_type +  '&type=' + type + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/api-commission/v1/update-commission')}}",
                data: dataString,
                success: function (msg) {
                    $("#update_btn").show();
                    $("#update_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    }else{
                        swal("Faild", msg.message, "error");
                    }
                }
            });
        }
    </script>

    <input type="hidden" id="api_id" value="{{ $api_id }}">
    <input type="hidden" id="provider_id" value="{{ $provider_id }}">
    <div class="main-content-body">
        <div class="row row-sm">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">

                            <strong>{{ $api_name }} : {{ $provider_name }} </strong>

                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div class="form-body">
                            <div class="row">
                                <div class="col-lg-4 col-md-8 form-group mg-b-0">
                                    <label class="form-label">Min Amount: <span class="tx-danger">*</span></label>
                                    <input class="form-control" type="text" id="min_amount" placeholder="Min Amount" autocomplete="off">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="min_amount_errors"></li>
                                    </ul>
                                </div>

                                <div class="col-lg-4 col-md-8 form-group mg-b-0">
                                    <label class="form-label">Max Amount: <span class="tx-danger">*</span></label>
                                    <input class="form-control" type="text" id="max_amount" placeholder="Max Amount" autocomplete="off">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="max_amount_errors"></li>
                                    </ul>
                                </div>

                                <div class="col-lg-4 col-md-8 form-group mg-b-0">
                                    <label class="form-label">Type: <span class="tx-danger">*</span></label>
                                    <select class="form-control" id="type">
                                        <option value="0">%</option>
                                        <option value="1">Rs</option>
                                    </select>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="type_errors"></li>
                                    </ul>
                                </div>



                            </div>
                            <br>
                            <div class="row">

                                <div class="col-lg-4 col-md-8 form-group mg-b-0">
                                    <label class="form-label">Commission Type: <span class="tx-danger">*</span></label>
                                    <select class="form-control" id="commission_type">
                                        <option value="">Select Commission Type</option>
                                        <option value="commission">Commission</option>
                                        <option value="charges">Charges</option>
                                    </select>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="commission_type_errors"></li>
                                    </ul>
                                </div>


                                <div class="col-lg-4 col-md-8 form-group mg-b-0">
                                    <label class="form-label">Commission / Charges: <span class="tx-danger">*</span></label>
                                    <input class="form-control" type="text" id="commission" value="0" autocomplete="off">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="commission_errors"></li>
                                    </ul>
                                </div>
                                <div class="col-lg-4 col-md-4 mg-t-10 mg-sm-t-25">
                                    <button class="btn btn-main-primary pd-x-20" type="button" onclick="saveCommission()">Save Commission</button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <!--/div-->

        </div>


        <div class="row row-sm">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">Commission Slab</h4>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table text-md-nowrap" id="example1">
                                <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0">Provider Name</th>
                                    <th class="wd-15p border-bottom-0">Min Amount</th>
                                    <th class="wd-15p border-bottom-0">Max Amount</th>
                                    <th class="wd-15p border-bottom-0">Type</th>
                                    <th class="wd-15p border-bottom-0">Commission Type</th>
                                    <th class="wd-15p border-bottom-0">Comm / Charges</th>
                                    <th class="wd-15p border-bottom-0">Update</th>
                                    <th class="wd-15p border-bottom-0">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($apicommissions as $value)
                                    <tr>
                                        <td>{{ $value->provider->provider_name }}</td>
                                        <td>{{ $value->min_amount }}</td>
                                        <td>{{ $value->max_amount }}</td>
                                        <td>@if($value->type == 0) % @else Rs @endif</td>
                                        <td>{{ $value->commission_type }}</td>
                                        <td>{{ $value->commission }}</td>
                                        <td><button class="btn btn-success btn-sm" onclick="viewCommission({{ $value->id }})">Update</button></td>
                                        <td><button class="btn btn-danger btn-sm" onclick="deleteRecord({{ $value->id }})">Delete</button></td>
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


    <div class="modal  show" id="view_commission_model"data-toggle="modal">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Update Commission</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">

                        <input type="hidden" id="view_id">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Provider Name</label>
                                    <input type="text" id="view_provider_name" class="form-control" placeholder="Provider Name" readonly>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Type</label>
                                    <select class="form-control" id="view_type">
                                        <option value="0">%</option>
                                        <option value="1">Rs</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Minimum Amount</label>
                                    <input type="text" id="view_min_amount" class="form-control" placeholder="Minimum Amount">
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Maximum Amount</label>
                                    <input type="text" id="view_max_amount" class="form-control" placeholder="Maximum Amount">
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="form-label">Commission Type: <span class="tx-danger">*</span></label>
                                    <select class="form-control" id="view_commission_type">
                                        <option value="">Select Commission Type</option>
                                        <option value="commission">Commission</option>
                                        <option value="charges">Charges</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Commission / Charges</label>
                                    <input type="text" id="view_commission" class="form-control" placeholder="Commission / Charges">
                                </div>
                            </div>


                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn ripple btn-primary" type="button" onclick="updateComission()" id="update_btn">Update Now</button>
                    <button class="btn btn-primary" type="button"  id="update_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                    <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
    </div>

@endsection