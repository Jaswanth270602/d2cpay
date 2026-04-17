@extends('themes2.admin.layout.header')
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
                            <button class="btn btn-danger btn-sm" data-target="#add_number_series_model" data-toggle="modal">Add Number Series Master</button>
                        </div>
                        <hr>
                        <div class="widget-content">

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
                </div>

            </div>
        </div>
        <!-- Main Body Ends -->

        {{-- start add add number series model--}}
        <div class="modal fade" id="add_number_series_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
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
                                        <span class="invalid-feedback d-block" id="number_errors"></span>
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
                                        <span class="invalid-feedback d-block" id="state_id_errors"></span>
                                    </div>
                                </div>


                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="create_btn" onclick="add_number_series()">Add Number Series</button>
                        <button class="btn btn-primary" type="button"  id="create_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    {{-- End add add number series model--}}

        {{--start update number seares modal--}}
        <div class="modal fade" id="view_number_series_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
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
                                        <textarea class="form-control" id="view_number" rows="8" placeholder="Enter 4 digit number like (1111,2222,3333)"></textarea>
                                        <span class="invalid-feedback d-block" id="view_number_errors"></span>
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
                                        <span class="invalid-feedback d-block" id="view_state_id_errors"></span>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="update_btn" onclick="update_number()">Save changes</button>
                        <button class="btn btn-primary" type="button"  id="update_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    {{--End update number seares modal--}}


@endsection