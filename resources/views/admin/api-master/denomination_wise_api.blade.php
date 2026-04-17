@extends('admin.layout.header')
@section('content')

    <script type="text/javascript">
        $(document).ready(function ()
        {
            $("#provider_id").select2();
            $("#api_id").select2();
        });


        function save_now() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var provider_id = $("#provider_id").val();
            var api_id = $("#api_id").val();
            var amount = $("#amount").val();
            var dataString = 'provider_id=' + provider_id + '&api_id=' + api_id + '&amount=' + amount + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/save-denomination-wise-api')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#provider_id_errors").text(msg.errors.provider_id);
                        $("#api_id_errors").text(msg.errors.api_id);
                        $("#amount_errors").text(msg.errors.amount);
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }
        
        function view_denomination(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/view-denomination-wise-api')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        $("#view_id").val(msg.details.id);
                        $("#view_provider_id").val(msg.details.provider_id);
                        $("#view_api_id").val(msg.details.api_id);
                        $("#view_amount").val(msg.details.amount);
                        $("#view_status_id").val(msg.details.status_id);
                        $("#view_denomination_model").modal('show');
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }
        
        function update_now() {
            $("#update_btn").hide();
            $("#update_btn_loader").show();
            var token = $("input[name=_token]").val();
            var id = $("#view_id").val();
            var provider_id = $("#view_provider_id").val();
            var api_id = $("#view_api_id").val();
            var amount = $("#view_amount").val();
            var status_id = $("#view_status_id").val();
            var dataString = 'id=' + id + '&provider_id=' + provider_id + '&api_id=' + api_id + '&amount=' + amount + '&status_id=' + status_id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/update-denomination-wise-api')}}",
                data: dataString,
                success: function (msg) {
                    $("#update_btn").show();
                    $("#update_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#view_provider_id_errors").text(msg.errors.provider_id);
                        $("#view_api_id_errors").text(msg.errors.api_id);
                        $("#view_amount_errors").text(msg.errors.amount);
                        $("#view_status_id_errors").text(msg.errors.status_id);
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }
        
        function delete_denomination(id) {
            if (confirm("Are you sure you want to delete this denomination?") == true) {
                $(".loader").show();
                var token = $("input[name=_token]").val();
                var dataString = 'id=' + id + '&_token=' + token;
                $.ajax({
                    type: "POST",
                    url: "{{url('admin/delete-denomination-wise-api')}}",
                    data: dataString,
                    success: function (msg) {
                        $(".loader").hide();
                        if (msg.status == 'success') {
                            swal("Success", msg.message, "success");
                            setTimeout(function () { location.reload(1); }, 3000);
                        }else{
                            swal("Failed", msg.message, "error");
                        }
                    }
                });
            }
        }
    </script>


    <div class="main-content-body">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card">
                    <div class="card-body">

                        <div class="row">
                            <div class="col-lg-3 col-md-8 form-group mg-b-0">
                                <label class="form-label">Providers: <span class="tx-danger">*</span></label>
                                <select class="form-control select2" id="provider_id" style="width: 100%;">
                                    <option value="">Select Provider</option>
                                    @foreach($providers as $value)
                                        <option value="{{ $value->id }}">{{ $value->provider_name }} </option>
                                    @endforeach
                                </select>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="provider_id_errors"></li>
                                </ul>
                            </div>

                            <div class="col-lg-3 col-md-8 form-group mg-b-0">
                                <label class="form-label">Api: <span class="tx-danger">*</span></label>
                                <select class="form-control select2" id="api_id" style="width: 100%;">
                                    <option value="">Select Api</option>
                                    @foreach($apis as $value)
                                        <option value="{{ $value->id }}">{{ $value->api_name }} </option>
                                    @endforeach
                                </select>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="api_id_errors"></li>
                                </ul>
                            </div>

                            <div class="col-lg-3 col-md-8 form-group mg-b-0">
                                <label class="form-label">Amount: <span class="tx-danger">*</span></label>
                                 <input type="text" id="amount" class="form-control" placeholder="Amount">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="amount_errors"></li>
                                </ul>
                            </div>

                            <div class="col-lg-3 col-md-4 mg-t-10 mg-sm-t-25">
                                <button class="btn btn-main-primary pd-x-20" type="button" onclick="save_now()"><i class="fas fa-plus-square"></i> Add Now</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row row-sm">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">Backup Api Master</h4>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="@if(Auth::User()->company->table_format == 1)table text-md-nowrap @else display responsive nowrap @endif" id="example1">
                                <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0">Provider</th>
                                    <th class="wd-25p border-bottom-0">Api Name</th>
                                    <th class="wd-25p border-bottom-0">Amount</th>
                                    <th class="wd-25p border-bottom-0">Status</th>
                                    <th class="wd-25p border-bottom-0">Action</th>
                                    <th class="wd-25p border-bottom-0">Delete</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($denominations as $value)
                                    <tr>
                                        <td>{{ $value->provider->provider_name }}</td>
                                        <td>{{ $value->api->api_name }}</td>
                                        <td>{{ $value->amount }}</td>
                                        <td>@if($value->status_id == 1)<span class="badge badge-success">Enabled</span> @else <span class="badge badge-danger">Disabled</span>  @endif</td>
                                        <td><button type="button" class="btn btn-success btn-sm" onclick="view_denomination({{ $value->id }})">Update</button> &nbsp;&nbsp;</td>
                                        <td><button type="button" class="btn btn-danger btn-sm" onclick="delete_denomination({{ $value->id }})">Delete</button></td>
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



    <div class="modal  show" id="view_denomination_model"data-toggle="modal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Update Denomination Api</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">

                        <input type="hidden" id="view_id">

                        <div class="row">

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Provider</label>
                                    <select class="form-control" id="view_provider_id">
                                        <option value="">Select Provider</option>
                                        @foreach($providers as $value)
                                            <option value="{{ $value->id }}">{{ $value->provider_name }} </option>
                                        @endforeach
                                    </select>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_provider_id_errors"></li>
                                    </ul>

                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Api</label>
                                    <select class="form-control" id="view_api_id" >
                                        <option value="">Select Api</option>
                                        @foreach($apis as $value)
                                            <option value="{{ $value->id }}">{{ $value->api_name }} </option>
                                        @endforeach
                                    </select>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_api_id_errors"></li>
                                    </ul>

                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Amount</label>
                                    <input type="text" id="view_amount" class="form-control" placeholder="Amount">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_amount_errors"></li>
                                    </ul>

                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Status</label>
                                    <select class="form-control" id="view_status_id">
                                        <option value="1">Enabled</option>
                                        <option value="0">Disabled</option>
                                    </select>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_status_id_errors"></li>
                                    </ul>

                                </div>
                            </div>




                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn ripple btn-primary" type="button" id="update_btn" onclick="update_now()">Update Now</button>
                    <button class="btn btn-primary" type="button"  id="update_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                    <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
    </div>

@endsection