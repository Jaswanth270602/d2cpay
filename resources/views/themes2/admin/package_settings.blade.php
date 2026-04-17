@extends('themes2.admin.layout.header')
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
                            <button class="btn btn-danger btn-sm" data-target="#create_package_model" data-toggle="modal">Create New Package</button>
                        </div>
                        <hr>
                        <div class="widget-content">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table text-md-nowrap" id="example1">
                                        <thead>
                                        <tr>
                                            <th class="wd-15p border-bottom-0">Sr No</th>
                                            <th class="wd-15p border-bottom-0">Package Name</th>
                                            <th class="wd-15p border-bottom-0">Commission</th>
                                            <th class="wd-25p border-bottom-0">Action</th>

                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php $i = 1 ?>
                                        @foreach($schemes as $value)
                                            <tr>
                                                <td>{{ $i++ }}</td>
                                                <td>{{ $value->scheme_name }}</td>
                                                <td>{{ Form::open(array('url' => 'admin/commission-setup', 'class' => 'pull-right')) }}
                                                    {{ Form::hidden('scheme_id', $value->id) }}
                                                    {{ Form::submit('Commission Set Up', array('class' => 'btn btn-success btn-sm')) }}
                                                    {{ Form::close() }}
                                                </td>


                                                <td><button class="btn btn-danger btn-sm" onclick="view_package({{ $value->id }})"><i class="typcn typcn-edit"></i>Edit</button></td>
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
        </div>
        <!-- Main Body Ends -->

    {{--Start Create Package--}}
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
                                        <span class="invalid-feedback d-block" id="new_scheme_name_errors"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="create_btn" onclick="create_package()">Create Package</button>
                        <button class="btn btn-primary" type="button"  id="create_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    {{--End Create Package--}}

    {{--Start Update Package--}}
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
                                        <span class="invalid-feedback d-block" id="scheme_name_errors"></span>

                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="update_btn" onclick="update_package()">Save changes</button>
                        <button class="btn btn-primary" type="button"  id="update_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

    {{--End Update Package--}}


@endsection