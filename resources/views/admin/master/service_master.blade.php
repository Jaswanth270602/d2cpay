@extends('admin.layout.header')
@section('content')

    <script type="text/javascript">
        function view_service(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/view-serivce-master')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        $("#view_id").val(msg.details.id);
                        $("#view_service_name").val(msg.details.service_name);
                        $("#view_wallet_id").val(msg.details.wallet_id);
                        $("#view_status_id").val(msg.details.status_id);
                        $("#view_service_model").modal('show');
                    } else {
                        swal("Faild", msg.message, "error");
                    }
                }
            });

        }

        function update_service() {
            $("#update_btn").hide();
            $("#update_btn_loader").show();
            var token = $("input[name=_token]").val();
            var id = $("#view_id").val();
            var service_name = $("#view_service_name").val();
            var wallet_id = $("#view_wallet_id").val();
            var status_id = $("#view_status_id").val();
            var dataString = 'id=' + id + '&service_name=' + service_name + '&wallet_id=' + wallet_id + '&status_id=' + status_id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/update-service-master')}}",
                data: dataString,
                success: function (msg) {
                    $("#update_btn").show();
                    $("#update_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () {
                            location.reload(1);
                        }, 3000);
                    } else if (msg.status == 'validation_error') {
                        $("#view_service_name_errors").text(msg.errors.service_name);
                    } else {
                        swal("Faild", msg.message, "error");
                    }
                }
            });
        }

        function viewServiceLogo(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/view-serivce-master')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        $("#logo_service_id").val(msg.details.id);
                        $("#logo_service_name").val(msg.details.service_name);
                        $("#view_logo_model").modal('show');
                    } else {
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
                            <h4 class="card-title mg-b-2 mt-2">Status Master</h4>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="@if(Auth::User()->company->table_format == 1)table text-md-nowrap @else display responsive nowrap @endif" id="example1">
                                <thead>
                                <tr>
                                    <th class="wd-25p border-bottom-0">Service Name</th>
                                    <th class="wd-25p border-bottom-0">Service Icon</th>
                                    <th class="wd-25p border-bottom-0">Wallet</th>
                                    <th class="wd-25p border-bottom-0">Status</th>
                                    <th class="wd-25p border-bottom-0">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($service as $value)
                                    <tr>
                                        <td>
                                            {{ $value->service_name }}
                                            <small id="slabHelp" class="form-text text-muted mt-0" style="font-size:80%"><u><a href="#" class="text-info" onclick="viewServiceLogo({{ $value->id }})">Add Logo</a></u></small>
                                        </td>
                                        <td><img src="{{$cdnLink}}{{$value->service_image}}" style="width: 30%;"></td>
                                        <td>{{ $value->wallet->wallet_name }}</td>
                                        <td>@if($value->status_id == 1) <span class="btn btn-success btn-sm">Enabled</span> @else <span class="btn btn-danger btn-sm">Disabled</span> @endif</td>
                                        <td><button class="btn btn-danger btn-sm" onclick="view_service({{ $value->id }})"><i class="typcn typcn-edit"></i> Edit</button></td>
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


    {{--update status modal--}}
    <div class="modal fade" id="view_service_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2" aria-hidden="true">
        <div class="modal-dialog modal-dialog-slideout" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Update Service</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">
                            <input type="hidden" id="view_id">

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Service Name</label>
                                    <input type="text" id="view_service_name" class="form-control" placeholder="Service Name">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_service_name_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Wallet Type</label>
                                   <select class="form-control" id="view_wallet_id">
                                       @foreach($wallets as $value)
                                           <option value="{{ $value->id }}">{{ $value->wallet_name }}</option>
                                       @endforeach
                                   </select>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_wallet_id_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Status</label>
                                    <select class="form-control" id="view_status_id">
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
                    <button type="button" class="btn btn-primary" id="update_btn" onclick="update_service()">Save changes</button>
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


    {{--add logo model --}}
    <div class="modal fade" id="view_logo_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-slideout" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Add Service Logo</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <form role="form" action="{{url('admin/upload-service-master-icon')}}" method="post"
                      enctype="multipart/form-data">
                    {!! csrf_field() !!}
                    <div class="modal-body">
                        <div class="form-body">
                            <div class="row">
                                <input type="hidden" id="logo_service_id" name="service_id">

                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="name">Service Name</label>
                                        <input type="text" id="logo_service_name" class="form-control" placeholder="Service Name" disabled>
                                    </div>
                                </div>

                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="name">Select Logo</label>
                                        <input type="file" class="form-control" name="service_logo">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{--add logo model cloe --}}
@endsection