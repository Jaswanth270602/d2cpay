@extends('admin.layout.header')
@section('content')

    <script type="text/javascript">
        function view_package(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/view-package-details')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        $("#scheme_id").val(msg.details.scheme_id);
                        $("#scheme_name").val(msg.details.scheme_name);
                        $("#created_at").val(msg.details.created_at);
                        $("#created_by").val(msg.details.created_by);
                        $("#view_package_model").modal('show');
                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function update_package() {
            //$(".loader").show();
            $("#update_btn").hide();
            $("#update_btn_loader").show();
            var token = $("input[name=_token]").val();
            var scheme_id = $("#scheme_id").val();
            var scheme_name = $("#scheme_name").val();
            var dataString = 'scheme_id=' + scheme_id + '&scheme_name=' + scheme_name + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/update-package')}}",
                data: dataString,
                success: function (msg) {
                    // $(".loader").hide();
                    $("#update_btn").show();
                    $("#update_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () {
                            location.reload(1);
                        }, 3000);
                    } else if (msg.status == 'validation_error') {
                        $("#scheme_name_errors").text(msg.errors.scheme_name);
                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function create_package() {
            // $(".loader").show();
            $("#create_btn").hide();
            $("#create_btn_loader").show();
            var token = $("input[name=_token]").val();
            var scheme_name = $("#new_scheme_name").val();
            var dataString = 'scheme_name=' + scheme_name + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/create-new-package')}}",
                data: dataString,
                success: function (msg) {
                    // $(".loader").hide();
                    $("#create_btn").show();
                    $("#create_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () {
                            location.reload(1);
                        }, 3000);
                    } else if (msg.status == 'validation_error') {
                        $("#new_scheme_name_errors").text(msg.errors.scheme_name);
                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function deletePackage(id) {
            swal({
                    title: "Are you sure?",
                    text: 'you want to delete this package ',
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Yes, delete it!",
                    cancelButtonText: "No, cancel",
                    closeOnConfirm: false,
                    closeOnCancel: false
                },
                function (isConfirm) {
                    if (isConfirm) {
                        $(".loader").show();
                        var token = $("input[name=_token]").val();
                        var dataString = 'id=' + id + '&_token=' + token;
                        $.ajax({
                            type: "POST",
                            url: "{{url('admin/delete-package')}}",
                            data: dataString,
                            success: function (msg) {
                                $(".loader").hide();
                                if (msg.status == 'success') {
                                    swal("Deleted!", msg.message, "success");
                                    setTimeout(function () {
                                        location.reload(1);
                                    }, 3000);
                                } else {
                                    swal("Faild", msg.message, "error");
                                }
                            }
                        });
                    }
                }
            );
        }

        function copyPackage(scheme_id) {
            $("#copy_scheme_id").val(scheme_id);
            $("#view_copy_package_model").modal('show');
        }

        function createCopyPackage (){
            $("#copy_create_btn").hide();
            $("#copy_create_btn_loader").show();
            var token = $("input[name=_token]").val();
            var scheme_id = $("#copy_scheme_id").val();
            var scheme_name = $("#copy_scheme_name").val();
            var dataString = 'scheme_id=' + scheme_id + '&scheme_name=' + scheme_name + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/copy-package')}}",
                data: dataString,
                success: function (msg) {
                    $("#copy_create_btn").show();
                    $("#copy_create_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () {location.reload(1);}, 3000);
                    } else if (msg.status == 'validation_error') {
                        $("#scheme_name_errors").text(msg.errors.scheme_name);
                    } else {
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
                            <h4 class="card-title mg-b-2 mt-2">Package Settings</h4>
                            <button class="btn btn-danger btn-sm" data-target="#create_package_model" data-toggle="modal">Create New Package</button>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="@if(Auth::User()->company->table_format == 1)table text-md-nowrap @else display responsive nowrap @endif" id="example1">
                                <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0">Sr No</th>
                                    <th class="wd-15p border-bottom-0">Package Name</th>
                                    <th class="wd-15p border-bottom-0">Commission</th>
                                    <th class="wd-25p border-bottom-0">Action</th>
                                    <th class="wd-25p border-bottom-0">Delete</th>

                                </tr>
                                </thead>
                                <tbody>
                                <?php $i = 1 ?>
                                @foreach($schemes as $value)
                                    @php
                                        $countUser = App\Models\User::where('scheme_id', $value->id)->count();
                                    @endphp
                                <tr>
                                    <td>{{ $i++ }}</td>
                                    <td>
                                        {{ $value->scheme_name }}
                                        <small id="slabHelp" class="form-text text-muted mt-0" style="font-size:70%">
                                            <u><a href="#" class="text-info" onclick="copyPackage({{$value->id}})">Copy Package</a></u>
                                            | <u><a href="{{url('admin/all-user-list')}}/{{Crypt::encrypt($value->id)}}" target="_blank" class="text-info">{{$countUser}} Users</a></u>
                                        </small>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ url('admin/commission-setup') }}" class="pull-right">
                                            @csrf
                                            <input type="hidden" name="scheme_id" value="{{ $value->id }}">
                                            <button type="submit" class="btn btn-success btn-sm">Commission Set Up</button>
                                        </form>
                                    </td>
                                    <td><button class="btn btn-danger btn-sm" onclick="view_package({{ $value->id }})">Edit</button></td>
                                    <td><button class="btn btn-danger btn-sm" onclick="deletePackage({{ $value->id }})">Delete</button></td>
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

    {{--create new package--}}
    <div class="modal fade" id="create_package_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2" aria-hidden="true">
        <div class="modal-dialog modal-dialog-slideout" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Create New Package</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">


                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Package Name</label>
                                    <input type="text" id="new_scheme_name" class="form-control" placeholder="Package Name">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="new_scheme_name_errors"></li>
                                    </ul>

                                </div>
                            </div>

                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="create_btn" onclick="create_package()">Create Package</button>
                    <button class="btn btn-primary" type="button"  id="create_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                </div>
            </div>
        </div>
    </div>


    {{--update package modal--}}
    <div class="modal fade" id="view_package_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2" aria-hidden="true">
        <div class="modal-dialog modal-dialog-slideout" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Edit Package</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">
                            <input type="hidden" id="scheme_id">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Created By</label>
                                    <input type="text" id="created_by" class="form-control" placeholder="Created By" disabled>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Created Date</label>
                                    <input type="text" id="created_at" class="form-control" placeholder="Created Date" disabled>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Package Name</label>
                                    <input type="text" id="scheme_name" class="form-control" placeholder="Package Name">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="scheme_name_errors"></li>
                                    </ul>

                                </div>
                            </div>

                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="update_btn" onclick="update_package()">Save changes</button>
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


    {{--Copy Package--}}
    <div class="modal fade" id="view_copy_package_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2" aria-hidden="true">
        <div class="modal-dialog modal-dialog-slideout" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Copy Package</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">

                            <input type="hidden" id="copy_scheme_id">


                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">New Package Name</label>
                                    <input type="text" id="copy_scheme_name" class="form-control" placeholder="Package Name">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="copy_scheme_name_errors"></li>
                                    </ul>

                                </div>
                            </div>

                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="copy_create_btn" onclick="createCopyPackage()">Create Package</button>
                    <button class="btn btn-primary" type="button"  id="copy_create_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                </div>
            </div>
        </div>
    </div>
    {{--Copy Package End--}}

@endsection