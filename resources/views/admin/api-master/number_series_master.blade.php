@extends('admin.layout.header')
@section('content')

    <script type="text/javascript">
        function view_api(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/view-number-series')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        $("#view_id").val(msg.details.id);
                        $("#view_number").val(msg.details.number);
                        $("#view_state_id").val(msg.details.state_id);
                        $("#view_number_series_model").modal('show');
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }
        
        function update_number() {
            $("#update_btn").hide();
            $("#update_btn_loader").show();
            var token = $("input[name=_token]").val();
            var id = $("#view_id").val();
            var number = $("#view_number").val();
            var state_id = $("#view_state_id").val();
            var dataString = 'id=' + id + '&number=' + number + '&state_id=' + state_id +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/update-number-series')}}",
                data: dataString,
                success: function (msg) {
                    $("#update_btn").show();
                    $("#update_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#view_number_errors").text(msg.errors.number);
                        $("#view_state_id_errors").text(msg.errors.state_id);
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }
        
        function add_number_series() {
            $("#create_btn").hide();
            $("#create_btn_loader").show();
            var token = $("input[name=_token]").val();
            var number = $("#number").val();
            var state_id  = $("#state_id").val();
            var dataString = 'number=' + number + '&state_id=' + state_id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/add-number-series')}}",
                data: dataString,
                success: function (msg) {
                    // $(".loader").hide();
                    $("#create_btn").show();
                    $("#create_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#number_errors").text(msg.errors.number);
                        $("#state_id_errors").text(msg.errors.state_id);
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
                            <h4 class="card-title mg-b-2 mt-2">Number Series Master</h4>
                            <button class="btn btn-danger btn-sm" data-target="#add_number_series_model" data-toggle="modal">Add Number Series Master</button>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table text-md-nowrap" id="example1">
                                <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0">Id</th>
                                    <th class="wd-25p border-bottom-0">Created at</th>
                                    <th class="wd-25p border-bottom-0">Number</th>
                                    <th class="wd-25p border-bottom-0">Total</th>
                                    <th class="wd-25p border-bottom-0">State Name</th>
                                    <th class="wd-25p border-bottom-0">Status</th>
                                    <th class="wd-25p border-bottom-0">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($numberdata as $value)

                                    @php
                                        $exportnumber = explode(',', $value->number);
                                        $count_number =   count($exportnumber) - 1;
                                    @endphp
                                    <tr>
                                        <td>{{ $value->id }}</td>
                                        <td>{{ $value->created_at }}</td>
                                        <td>{{substr($value->number, 0, 25)}}</td>
                                        <td>{{ $count_number }}</td>
                                        <td>{{ $value->state->name }}</td>
                                        <td>@if($value->status_id == 1)<span class="badge badge-success">Enabled</span> @else <span class="badge badge-danger">Disabled</span>  @endif</td>
                                        <td><button class="btn btn-danger btn-sm" onclick="view_api({{ $value->id }})"><i class="typcn typcn-edit"></i> Edit</button></td>
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


    {{--add add number series model--}}
    <div class="modal fade" id="add_number_series_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2" aria-hidden="true">
        <div class="modal-dialog modal-dialog-slideout" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Add Number Series</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">



                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Numbers</label>
                                    <textarea class="form-control" id="number" rows="4" placeholder="Enter 4 digit number like (1111,2222,3333)"></textarea>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="number_errors"></li>
                                    </ul>
                                </div>
                            </div>


                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">State</label>
                                    <select class="form-control" id="state_id">
                                        <option value="">Select State</option>
                                        @foreach($state as $value)
                                            <option value="{{ $value->id }}">{{ $value->name }}</option>
                                        @endforeach
                                    </select>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="state_id_errors"></li>
                                    </ul>
                                </div>
                            </div>


                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="create_btn" onclick="add_number_series()">Add Number Series</button>
                    <button class="btn btn-primary" type="button"  id="create_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                </div>
            </div>
        </div>
    </div>

    {{--update number seares modal--}}
    <div class="modal fade" id="view_number_series_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2" aria-hidden="true">
        <div class="modal-dialog modal-dialog-slideout" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Edit Number Series</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">
                            <input type="hidden" id="view_id">

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Numbers</label>
                                    <textarea class="form-control" id="view_number" rows="15" placeholder="Enter 4 digit number like (1111,2222,3333)"></textarea>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_number_errors"></li>
                                    </ul>
                                </div>
                            </div>


                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">State</label>
                                    <select class="form-control" id="view_state_id">
                                        @foreach($state as $value)
                                            <option value="{{ $value->id }}">{{ $value->name }}</option>
                                            @endforeach
                                    </select>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_state_id_errors"></li>
                                    </ul>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="update_btn" onclick="update_number()">Save changes</button>
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