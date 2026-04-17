@extends('admin.layout.header')
@section('content')

    <script type="text/javascript">
        $(document).ready(function ()
        {
            $("#provider_id").select2();
            $("#api_id").select2();
        });

        function add_back_api() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var provider_id = $("#provider_id").val();
            var api_id = $("#api_id").val();
            var dataString = 'provider_id=' + provider_id + '&api_id=' + api_id +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/save-backup-api')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#provider_id_errors").text(msg.errors.provider_id);
                        $("#api_id_errors").text(msg.errors.api_id);
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }
        
        function deleteapis(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var r = confirm("r u sure delete this id");
            if (r == true) {
                var dataString = 'id=' + id + '&_token=' + token;
                $.ajax({
                    type: "POST",
                    url: "{{url('admin/delete-backup-api')}}",
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
            }else{
                $(".loader").hide();
            }
        }
        
        function viewapis (id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/view-backup-api')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        $("#view_id").val(msg.details.id);
                        $("#view_provider_id").val(msg.details.provider_id);
                        $("#view_api_id").val(msg.details.api_id);
                        $("#view_api_type").val(msg.details.api_type);
                        $("#view_status_id").val(msg.details.status_id);
                        $("#view_backup_api_model").modal('show');
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }
        
        
        function update_backup_api() {
            $("#update_btn").hide();
            $("#update_btn_loader").show();
            var token = $("input[name=_token]").val();
            var id = $("#view_id").val();
            var provider_id = $("#view_provider_id").val();
            var api_id = $("#view_api_id").val();
            var api_type = $("#view_api_type").val();
            var status_id = $("#view_status_id").val();
            var dataString = 'id=' + id + '&provider_id=' + provider_id + '&api_id=' + api_id + '&api_type=' + api_type + '&status_id=' + status_id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/update-backup-api')}}",
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
                        $("#view_api_type_errors").text(msg.errors.api_type);
                        $("#view_status_id_errors").text(msg.errors.status_id);
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });

        }
    </script>


    <div class="main-content-body">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card">
                    <div class="card-body">

                            <div class="row">
                                <div class="col-lg-5 col-md-8 form-group mg-b-0">
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

                                <div class="col-lg-5 col-md-8 form-group mg-b-0">
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

                                <div class="col-lg-2 col-md-4 mg-t-10 mg-sm-t-25">
                                    <button class="btn btn-main-primary pd-x-20" type="button" onclick="add_back_api()"><i class="fas fa-plus-square"></i> Add Now</button>
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
                                    <th class="wd-25p border-bottom-0">Status</th>
                                   <th class="wd-25p border-bottom-0">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($backupapi as $value)
                                    <tr>
                                        <td>{{ $value->provider->provider_name }}</td>
                                        <td>{{ $value->api->api_name }} ( Backup No: {{ $value->api_type }} )</td>
                                        <td>@if($value->status_id == 1)<span class="badge badge-success">Enabled</span> @else <span class="badge badge-danger">Disabled</span>  @endif</td>
                                      <td>
                                            <button type="button" class="btn btn-success btn-sm" onclick="viewapis({{ $value->id }})"> <i class="fa fa-edit" aria-hidden="true"></i> Update</button> &nbsp;&nbsp;
                                            <button type="button" class="btn btn-danger btn-sm" onclick="deleteapis({{ $value->id }})"> <i class="fas fa-trash-alt"></i> Delete</button>
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




    {{--update package modal--}}
    <div class="modal fade" id="view_backup_api_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2" aria-hidden="true">
        <div class="modal-dialog modal-dialog-slideout" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Update Backup Api</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">
                            <input type="hidden" id="view_id">

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Provider Name</label>
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
                                    <label for="name">Api Name</label>
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
                                    <label for="name">Backup Number</label>
                                    <select class="form-control" id="view_api_type">
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                        <option value="6">6</option>
                                        <option value="7">7</option>
                                        <option value="8">8</option>
                                        <option value="9">9</option>
                                        <option value="10">10</option>
                                    </select>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_api_type_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Status</label>
                                    <select class="form-control" id="view_status_id">
                                        <option value="0">Disabled</option>
                                        <option value="1">Enabled</option>
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
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="update_btn" onclick="update_backup_api()">Save changes</button>
                    <button class="btn btn-primary" type="button"  id="update_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .modal-dialog-slideout {min-height: 100%; margin: 0 0 0 auto;background: #fff;}
        .modal.fade .modal-dialog.modal-dialog-slideout {-webkit-transform: translate(100%,0)scale(1);transform: translate(100%,0)scale(1);}
        .modal.fade.show .modal-dialog.modal-dialog-slideout {-webkit-transform: translate(0,0);transform: translate(0,0);display: flex;align-items: stretch;-webkit-box-align: stretch;height: 100%;}
        .modal.fade.show .modal-dialog.modal-dialog-slideout .modal-body{overflow-y: auto;overflow-x: hidden;}
        .modal-dialog-slideout .modal-content{border: 0;}
        .modal-dialog-slideout .modal-header, .modal-dialog-slideout .modal-footer {height: 69px; display: block;}
        .modal-dialog-slideout .modal-header h5 {float:left;}
    </style>

@endsection