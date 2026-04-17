@extends('admin.layout.header')
@section('content')

    <script type="text/javascript">
        function view_status(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/view-status-master')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        $("#view_id").val(msg.details.id);
                        $("#view_status_title").val(msg.details.status);
                        $("#view_status_model").modal('show');
                    }else{
                        swal("Faild", msg.message, "error");
                    }
                }
            });

        }

        function update_status() {
            $("#update_btn").hide();
            $("#update_btn_loader").show();
            var token = $("input[name=_token]").val();
            var id = $("#view_id").val();
            var status = $("#view_status_title").val();
            var dataString = 'id=' + id +  '&status=' + status + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/update-status-master')}}",
                data: dataString,
                success: function (msg) {
                    $("#update_btn").show();
                    $("#update_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#view_status_title_errors").text(msg.errors.status);
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
                                    <th class="wd-25p border-bottom-0">status Name</th>
                                    <th class="wd-25p border-bottom-0">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($status as $value)
                                    <tr>

                                        <td>{{ $value->status }}</td>
                                        <td><button class="btn btn-danger btn-sm" onclick="view_status({{ $value->id }})"><i class="typcn typcn-edit"></i> Edit</button></td>
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
    <div class="modal fade" id="view_status_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2" aria-hidden="true">
        <div class="modal-dialog modal-dialog-slideout" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Update Status</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">
                            <input type="hidden" id="view_id">

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">status Title</label>
                                    <input type="text" id="view_status_title" class="form-control" placeholder="Status Title">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_status_title_errors"></li>
                                    </ul>
                                </div>
                            </div>


                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="update_btn" onclick="update_status()">Save changes</button>
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