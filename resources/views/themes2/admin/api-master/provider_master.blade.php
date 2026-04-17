@extends('themes2.admin.layout.header')
@section('content')
    <script type="text/javascript">


        function view_provider(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/view-provider')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        $("#view_provider_id").val(msg.details.provider_id);
                        $("#view_provider_name").val(msg.details.provider_name);
                        $("#view_service_id").val(msg.details.service_id);
                        $("#view_gst_type").val(msg.details.gst_type);
                        $("#view_min_length").val(msg.details.min_length);
                        $("#view_max_length").val(msg.details.max_length);
                        $("#view_start_with").val(msg.details.start_with);
                        $("#view_status_id").val(msg.details.status_id);
                        $("#view_min_amount").val(msg.details.min_amount);
                        $("#view_max_amount").val(msg.details.max_amount);
                        $("#view_help_line").val(msg.details.help_line);
                        $("#view_block_amount").val(msg.details.block_amount);
                        $("#view_provider_model").modal('show');
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function update_provider() {
            $("#update_btn").hide();
            $("#update_btn_loader").show();
            var provider_id = $("#view_provider_id").val();
            var provider_name = $("#view_provider_name").val();
            var service_id = $("#view_service_id").val();
            var status_id = $("#view_status_id").val();
            var gst_type = $("#view_gst_type").val();
            var token = $("input[name=_token]").val();
            var min_length = $("#view_min_length").val();
            var max_length = $("#view_max_length").val();
            var start_with = $("#view_start_with").val();
            var min_amount = $("#view_min_amount").val();
            var max_amount = $("#view_max_amount").val();
            var help_line = $("#view_help_line").val();
            var block_amount = $("#view_block_amount").val();
            var dataString = 'provider_id=' + provider_id + '&provider_name=' + provider_name + '&service_id=' + service_id + '&status_id=' + status_id + '&gst_type=' + gst_type + '&min_length=' + min_length + '&max_length=' + max_length + '&start_with=' + start_with + '&min_amount=' + min_amount + '&max_amount=' + max_amount + '&help_line=' + help_line + '&block_amount=' + block_amount + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/update-provider')}}",
                data: dataString,
                success: function (msg) {
                    $("#update_btn").show();
                    $("#update_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#view_provider_name_errors").text(msg.errors.provider_name);
                        $("#view_service_id_errors").text(msg.errors.service_id);
                        $("#view_status_id_errors").text(msg.errors.status_id);
                        $("#view_min_length_errors").text(msg.errors.min_length);
                        $("#view_max_length_errors").text(msg.errors.max_length);
                        $("#view_min_amount_errors").text(msg.errors.min_amount);
                        $("#view_max_amount_errors").text(msg.errors.max_amount);
                        $("#view_help_line_errors").text(msg.errors.help_line);
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function add_provider() {
            $("#create_btn").hide();
            $("#create_btn_loader").show();
            var token = $("input[name=_token]").val();
            var service_id = $("#service_id").val();
            var provider_name = $("#provider_name").val();
            var dataString = 'service_id=' + service_id + '&provider_name=' + provider_name + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/add-provider')}}",
                data: dataString,
                success: function (msg) {
                    // $(".loader").hide();
                    $("#create_btn").show();
                    $("#create_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#service_id_errors").text(msg.errors.service_id);
                        $("#provider_name_errors").text(msg.errors.provider_name);
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function viewProviderForLogo(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/view-provider')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        $("#logo_provider_id").val(msg.details.provider_id);
                        $("#logo_provider_name").val(msg.details.provider_name);
                        $("#logo_provider_model").modal('show');
                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });
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
                            <button class="btn btn-danger btn-sm" data-target="#add_provider_model" data-toggle="modal">Add Provider</button>
                        </div>
                        <hr>
                        <div class="widget-content">
                            <div class="table-responsive">
                                <table class="@if(Auth::User()->company->table_format == 1)table text-md-nowrap @else display responsive nowrap @endif" id="my_table">
                                    <thead>
                                    <tr>
                                        <th class="wd-15p border-bottom-0">Provider Id</th>
                                        <th class="wd-25p border-bottom-0">Provider Name</th>
                                        <th class="wd-25p border-bottom-0">Service</th>
                                        <th class="wd-25p border-bottom-0">Logo</th>
                                        <th class="wd-25p border-bottom-0">Status</th>
                                        <th class="wd-25p border-bottom-0">Min Length</th>
                                        <th class="wd-25p border-bottom-0">Max Length</th>
                                        <th class="wd-25p border-bottom-0">Start With</th>
                                        <th class="wd-25p border-bottom-0">GST Type</th>
                                        <th class="wd-25p border-bottom-0">Min Amount</th>
                                        <th class="wd-25p border-bottom-0">Max Amount</th>
                                        <th class="wd-25p border-bottom-0">Help Line</th>
                                        <th class="wd-25p border-bottom-0">Action</th>
                                    </tr>
                                    </thead>
                                </table>

                                <script type="text/javascript">
                                    $(document).ready(function(){

                                        // DataTable
                                        var todate = $("#todate").val();
                                        $('#my_table').DataTable({
                                            processing: true,
                                            serverSide: true,
                                            ajax: "{{ $urls }}",
                                            columns: [
                                                { data: 'id' },
                                                { data: 'provider_name' },
                                                { data: 'service_name' },
                                                { data: 'provider_image' },
                                                { data: 'status' },
                                                { data: 'min_length' },
                                                { data: 'max_length' },
                                                { data: 'start_with' },
                                                { data: 'gst_type' },
                                                { data: 'min_amount' },
                                                { data: 'max_amount' },
                                                { data: 'help_line' },
                                                { data: 'action' },
                                            ]
                                        });

                                    });
                                </script>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!-- Main Body Ends -->

    {{--Start Add Provider Model--}}
        <div class="modal fade" id="add_provider_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2" aria-hidden="true">
            <div class="modal-dialog modal-dialog-slideout" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title">Add Provider</h6>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-body">
                            <div class="row">


                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="name">Service Name</label>
                                        <select class="form-control" id="service_id">
                                            @foreach($services as $value)
                                                <option value="{{ $value->id }}">{{ $value->service_name }}</option>
                                            @endforeach
                                        </select>
                                        <span class="invalid-feedback d-block" id="service_id_errors"></span>
                                    </div>
                                </div>


                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="name">Provider Name</label>
                                        <input type="text" id="provider_name" class="form-control" placeholder="Provider Name">
                                        <span class="invalid-feedback d-block" id="provider_name_errors"></span>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="create_btn" onclick="add_provider()">Add Provider</button>
                        <button class="btn btn-primary" type="button"  id="create_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    {{--End Add Provider Model--}}


    {{--Start Update Provider Model--}}
        <div class="modal fade" id="view_provider_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title">Edit Provider</h6>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-body">
                            <div class="row">
                                <input type="hidden" id="view_provider_id">

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Provider Name</label>
                                        <input type="text" id="view_provider_name" class="form-control" placeholder="Provider Name">
                                        <span class="invalid-feedback d-block" id="view_provider_name_errors"></span>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Service Name</label>
                                        <select class="form-control" id="view_service_id">
                                            @foreach($services as $value)
                                                <option value="{{ $value->id }}">{{ $value->service_name }}</option>
                                            @endforeach
                                        </select>
                                        <span class="invalid-feedback d-block" id="view_service_id_errors"></span>
                                    </div>
                                </div>





                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Minimum Length</label>
                                        <input type="text" id="view_min_length" class="form-control" placeholder="Minimum Length">
                                        <span class="invalid-feedback d-block" id="view_min_length_errors"></span>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Maximum Length</label>
                                        <input type="text" id="view_max_length" class="form-control" placeholder="Maximum Length">
                                        <span class="invalid-feedback d-block" id="view_max_length_errors"></span>
                                    </div>
                                </div>


                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Minimum Amount</label>
                                        <input type="text" id="view_min_amount" class="form-control" placeholder="Minimum Amount">
                                        <span class="invalid-feedback d-block" id="view_min_amount_errors"></span>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Maximum Amount</label>
                                        <input type="text" id="view_max_amount" class="form-control" placeholder="Maximum Amount">
                                        <span class="invalid-feedback d-block" id="view_max_amount_errors"></span>
                                    </div>
                                </div>



                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">GST Type</label>
                                        <select class="form-control" id="view_gst_type">
                                            <option value="0">No</option>
                                            <option value="1">Yes</option>
                                        </select>
                                        <span class="invalid-feedback d-block" id="view_gst_type_errors"></span>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Status</label>
                                        <select class="form-control" id="view_status_id">
                                            <option value="0">Disabled</option>
                                            <option value="1">Enabled</option>
                                        </select>
                                        <span class="invalid-feedback d-block" id="view_service_id_errors"></span>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Number Start With</label>
                                        <input type="text" id="view_start_with" class="form-control" placeholder="Start With : (1,2,3)">
                                        <span class="invalid-feedback d-block" id="view_start_with_errors"></span>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Help Line Number</label>
                                        <input type="text" id="view_help_line" class="form-control" placeholder="Customer Care No">
                                        <span class="invalid-feedback d-block" id="view_help_line_errors"></span>
                                    </div>
                                </div>

                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="name">Block Amount</label>
                                        <input type="text" id="view_block_amount" class="form-control" placeholder="Block Amount (10,20,30)">
                                        <span class="invalid-feedback d-block" id="view_block_amount_errors"></span>
                                    </div>
                                </div>


                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="update_btn" onclick="update_provider()">Save changes</button>
                        <button class="btn btn-primary" type="button"  id="update_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

    {{--End Update Provider Model--}}

    {{--Start Add logo model--}}
        <div class="modal fade" id="logo_provider_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2"
             aria-hidden="true">
            <div class="modal-dialog modal-dialog-slideout" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title">Add Provider Logo</h6>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                                    aria-hidden="true">×</span></button>
                    </div>
                    <form role="form" action="{{url('admin/store-provider-logo')}}" method="post"
                          enctype="multipart/form-data">
                        {!! csrf_field() !!}
                        <div class="modal-body">
                            <div class="form-body">
                                <div class="row">
                                    <input type="hidden" id="logo_provider_id" name="logo_provider_id">

                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="name">Provider Name</label>
                                            <input type="text" id="logo_provider_name" class="form-control"
                                                   placeholder="Provider Name" disabled>
                                        </div>
                                    </div>

                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="name">Select Logo</label>
                                            <input type="file" class="form-control" name="provider_logo">
                                        </div>
                                    </div>

                                </div>

                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Submit</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    {{--End Add logo model--}}




@endsection