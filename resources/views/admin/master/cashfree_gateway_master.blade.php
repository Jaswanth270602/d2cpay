@extends('admin.layout.header')
@section('content')
    <script type="text/javascript">
        function view_details(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/view-cashfree-gateway-master')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        $("#view_id").val(msg.details.id);
                        $("#view_app_id").val(msg.details.app_id);
                        $("#view_secret_key").val(msg.details.secret_key);
                        $("#view_base_url").val(msg.details.base_url);
                        $("#view_min_amount").val(msg.details.min_amount);
                        $("#view_max_amount").val(msg.details.max_amount);
                        $("#view_status_id").val(msg.details.status_id);
                        $("#view_cashfree_model").modal('show');
                    }else{
                        swal("Faild", msg.message, "error");
                    }
                }
            });
        }
        
        function update_details() {
            $("#update_btn").hide();
            $("#update_btn_loader").show();
            var token = $("input[name=_token]").val();
            var id = $("#view_id").val();
            var app_id = $("#view_app_id").val();
            var secret_key = $("#view_secret_key").val();
            var base_url = $("#view_base_url").val();
            var min_amount = $("#view_min_amount").val();
            var max_amount = $("#view_max_amount").val();
            var status_id = $("#view_status_id").val();
            var dataString = 'id=' + id + '&app_id=' + app_id + '&secret_key=' + secret_key + '&base_url=' + base_url + '&min_amount=' + min_amount + '&max_amount=' + max_amount + '&status_id=' + status_id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/update-cashfree-gateway-master')}}",
                data: dataString,
                success: function (msg) {
                    $("#update_btn").show();
                    $("#update_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#view_app_id_errors").text(msg.errors.app_id);
                        $("#view_secret_key_errors").text(msg.errors.secret_key);
                        $("#view_base_url_errors").text(msg.errors.base_url);
                        $("#view_min_amount_errors").text(msg.errors.min_amount);
                        $("#view_max_amount_errors").text(msg.errors.max_amount);
                        $("#view_status_id_errors").text(msg.errors.status_id);
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
                            <a href="{{url('admin/gateway-charges/welcome')}}" class="btn btn-danger btn-sm">Charges Slab</a>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="@if(Auth::User()->company->table_format == 1)table text-md-nowrap @else display responsive nowrap @endif" id="example1">
                                <thead>
                                <tr>
                                    <th class="wd-25p border-bottom-0">Id</th>
                                    <th class="wd-25p border-bottom-0">App Id</th>
                                    <th class="wd-25p border-bottom-0">Secret Key</th>
                                    <th class="wd-25p border-bottom-0">Base URL</th>
                                    <th class="wd-25p border-bottom-0">Status</th>
                                    <th class="wd-25p border-bottom-0">Min Amount</th>
                                    <th class="wd-25p border-bottom-0">Max Amount</th>
                                    <th class="wd-25p border-bottom-0">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($cashfreegateways as $value)
                                    <tr>
                                        <td>{{ $value->id }}</td>
                                        <td>{{ $value->app_id }}</td>
                                        <td>{{ $value->secret_key }}</td>
                                        <td>{{ $value->base_url }}</td>
                                        <td>@if($value->status_id == 1)<span class="badge badge-success">Enabled</span>@else<span class="badge badge-danger	">Disabled</span>@endif</td>
                                        <td>{{ $value->min_amount }}</td>
                                        <td>{{ $value->max_amount }}</td>
                                        <td><button class="btn btn-danger btn-sm" onclick="view_details({{ $value->id }})"><i class="typcn typcn-edit"></i> Edit</button></td>
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


    {{--update role modal--}}
    <div class="modal fade" id="view_cashfree_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2" aria-hidden="true">
        <div class="modal-dialog modal-dialog-slideout" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Update Cashfree Details</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">
                            <input type="hidden" id="view_id">

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">App Id</label>
                                    <input type="text" id="view_app_id" class="form-control" placeholder="App Id">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_app_id_errors"></li>
                                    </ul>
                                </div>
                            </div>


                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Secret Key</label>
                                    <input type="text" id="view_secret_key" class="form-control" placeholder="Secret Key">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_secret_key_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Base URL</label>
                                    <input type="text" id="view_base_url" class="form-control" placeholder="Base URL">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_base_url_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Min Amount</label>
                                    <input type="text" id="view_min_amount" class="form-control" placeholder="Min Amount">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_min_amount_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Max Amount</label>
                                    <input type="text" id="view_max_amount" class="form-control" placeholder="Max Amount">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_max_amount_errors"></li>
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
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="update_btn" onclick="update_details()">Save changes</button>
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