@extends('themes2.admin.layout.header')
@section('content')
    <script type="text/javascript">
        function create_new_response() {
            $("#create_btn").hide();
            $("#create_btn_loader").show();
            var token = $("input[name=_token]").val();
            var api_id = $("#api_id").val();
            var status_id = $("#status_id").val();
            var status_parameter = $("#status_parameter").val();
            var status_value = $("#status_value").val();
            var operator_ref_parameter = $("#operator_ref_parameter").val();
            var under_value = $("#under_value").val();
            var dataString = 'api_id=' + api_id + '&status_id=' + status_id + '&status_parameter=' + status_parameter + '&status_value=' + status_value + '&operator_ref_parameter=' + operator_ref_parameter + '&under_value='  + under_value +'&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/add-new-responses')}}",
                data: dataString,
                success: function (msg) {
                    $("#create_btn").show();
                    $("#create_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#status_id_errors").text(msg.errors.status_id);
                        $("#status_parameter_errors").text(msg.errors.status_parameter);
                        $("#status_value_errors").text(msg.errors.status_value);
                        $("#operator_ref_parameter_errors").text(msg.errors.operator_ref_parameter);
                        $("#under_value_errors").text(msg.errors.under_value);
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function view_responses(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/view-api-responses')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        $("#view_id").val(msg.details.id);
                        $("#view_api_id").val(msg.details.api_id);
                        $("#view_status_id").val(msg.details.status_id);
                        $("#view_status_parameter").val(msg.details.status_parameter);
                        $("#view_status_value").val(msg.details.status_value);
                        $("#view_operator_ref_parameter").val(msg.details.operator_ref_parameter);
                        $("#view_under_value").val(msg.details.under_value);
                        $("#view_api_response_model").modal('show');
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function update_new_response() {
            $("#update_btn").hide();
            $("#update_btn_loader").show();
            var token = $("input[name=_token]").val();
            var id = $("#view_id").val();
            var status_id = $("#view_status_id").val();
            var status_parameter = $("#view_status_parameter").val();
            var status_value = $("#view_status_value").val();
            var operator_ref_parameter = $("#view_operator_ref_parameter").val();
            var under_value = $("#view_under_value").val();
            var dataString = 'id=' + id + '&status_id=' + status_id + '&status_parameter=' + status_parameter + '&status_value=' + status_value + '&operator_ref_parameter=' + operator_ref_parameter + '&under_value=' + under_value + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/update-api-responses')}}",
                data: dataString,
                success: function (msg) {
                    $("#update_btn").show();
                    $("#update_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#view_status_id_errors").text(msg.errors.status_id);
                        $("#view_status_parameter_errors").text(msg.errors.status_parameter);
                        $("#view_status_value_errors").text(msg.errors.status_value);
                        $("#view_operator_ref_parameter_errors").text(msg.errors.operator_ref_parameter);
                        $("#view_under_value_errors").text(msg.errors.under_value);
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function delete_responses(id) {
            if (confirm("Are you sure you want to delete this response?") == true) {
                $(".loader").show();
                var token = $("input[name=_token]").val();
                var dataString = 'id=' + id +  '&_token=' + token;
                $.ajax({
                    type: "POST",
                    url: "{{url('admin/delete-api-responses')}}",
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



    <!--  Content Area Starts  -->
    <div id="content" class="main-content">

        <!-- Main Body Starts -->
    @include('themes2.admin.layout.breadcrumb')
    <!-- Main Body Starts -->
        <div class="layout-px-spacing">
            <div class="row layout-top-spacing">
                <!-- REVENUE ENDS-->



                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
                    <div class="widget dashboard-table">
                        <div class="widget-heading">
                            <h5 class=""> {{ $page_title }}</h5>
                            <button class="btn btn-danger btn-sm" data-target="#add_response_model" data-toggle="modal">Add Response</button>
                        </div>
                        <hr>
                        <div class="widget-content">
                            <div class="table-responsive">
                                <table class="table text-md-nowrap" id="example1">
                                    <thead>
                                    <tr>
                                        <th class="wd-15p border-bottom-0">Status Type</th>
                                        <th class="wd-15p border-bottom-0">Status Parameter</th>
                                        <th class="wd-15p border-bottom-0">Status Value</th>
                                        <th class="wd-15p border-bottom-0">Operator Ref</th>
                                        <th class="wd-15p border-bottom-0">Separate With</th>
                                        <th class="wd-25p border-bottom-0">Action</th>
                                        <th class="wd-25p border-bottom-0">Delete</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($responsesettings as $value)
                                        <tr>
                                            <td><span class="{{ $value->status->class }}">{{ $value->status->status }}</span></td>
                                            <td>{{ $value->status_parameter }}</td>
                                            <td>{{ $value->status_value }}</td>
                                            <td>{{ $value->operator_ref_parameter }}</td>
                                            <td>{{ $value->under_value }}</td>
                                            <td><button class="btn btn-primary btn-sm" onclick="view_responses({{ $value->id }})">Update</button></td>
                                            <td><button class="btn btn-danger btn-sm" onclick="delete_responses({{ $value->id }})">Delete</button></td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!-- Main Body Ends -->

        {{--start add response model--}}
        <div class="modal  show" id="add_response_model"data-toggle="modal">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content modal-content-demo">
                    <div class="modal-header">
                        <h6 class="modal-title"><i class="fas fa-plus-circle"></i> Add New Response</h6>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-body">
                            <div class="row">

                                <input type="hidden" id="api_id" value="{{ $api_id }}">

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Status Type</label>
                                        <select class="form-control" id="status_id">
                                            @foreach($statuses as $value)
                                                <option value="{{ $value->id }}">{{ $value->status }}</option>
                                            @endforeach
                                        </select>
                                        <span class="invalid-feedback d-block" id="status_id_errors"></span>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Status Parameter</label>
                                        <input type="text" id="status_parameter" class="form-control" placeholder="Status Parameter">
                                        <span class="invalid-feedback d-block" id="status_parameter_errors"></span>
                                    </div>
                                </div>


                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Status Value</label>
                                        <input type="text" id="status_value" class="form-control" placeholder="Status Value">
                                        <span class="invalid-feedback d-block" id="status_value_errors"></span>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Operator Ref Parameter</label>
                                        <input type="text" id="operator_ref_parameter" class="form-control" placeholder="Operator Ref Parameter">
                                        <span class="invalid-feedback d-block" id="operator_ref_parameter_errors"></span>
                                    </div>
                                </div>

                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="name">Response Separate With</label>
                                        <input type="text" id="under_value" class="form-control" placeholder="Response Separate With">
                                        <span class="invalid-feedback d-block" id="under_value_errors"></span>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>


                    <div class="modal-footer">
                        <button class="btn ripple btn-primary" type="button" id="create_btn" onclick="create_new_response()">Add Response</button>
                        <button class="btn btn-primary" type="button"  id="create_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                        <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                    </div>
                </div>
            </div>
        </div>
    {{--end add response model close--}}


        {{--view api response model--}}
        <div class="modal  show" id="view_api_response_model"data-toggle="modal">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content modal-content-demo">
                    <div class="modal-header">
                        <h6 class="modal-title">Update New Response</h6>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-body">
                            <div class="row">

                                <input type="hidden" id="view_id">

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Status Type</label>
                                        <select class="form-control" id="view_status_id">
                                            @foreach($statuses as $value)
                                                <option value="{{ $value->id }}">{{ $value->status }}</option>
                                            @endforeach
                                        </select>
                                        <span class="invalid-feedback d-block" id="view_status_id_errors"></span>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Status Parameter</label>
                                        <input type="text" id="view_status_parameter" class="form-control" placeholder="Status Parameter">
                                        <span class="invalid-feedback d-block" id="view_status_parameter_errors"></span>
                                    </div>
                                </div>


                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Status Value</label>
                                        <input type="text" id="view_status_value" class="form-control" placeholder="Status Value">
                                        <span class="invalid-feedback d-block" id="view_status_value_errors"></span>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Operator Ref Parameter</label>
                                        <input type="text" id="view_operator_ref_parameter" class="form-control" placeholder="Operator Ref Parameter">
                                        <span class="invalid-feedback d-block" id="view_operator_ref_parameter_errors"></span>
                                    </div>
                                </div>

                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="name">Response Separate With</label>
                                        <input type="text" id="view_under_value" class="form-control" placeholder="Response Separate With">
                                        <span class="invalid-feedback d-block" id="view_under_value_errors"></span>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>


                    <div class="modal-footer">
                        <button class="btn ripple btn-primary" type="button" id="update_btn" onclick="update_new_response()">Update Response</button>
                        <button class="btn btn-primary" type="button"  id="update_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                        <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                    </div>
                </div>
            </div>
        </div>
    {{--view api response model close--}}


@endsection