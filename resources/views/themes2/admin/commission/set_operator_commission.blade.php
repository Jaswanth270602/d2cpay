@extends('themes2.admin.layout.header')
@section('content')
    <script type="text/javascript">
        function save_commission() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var scheme_id = $("#scheme_id").val();
            var provider_id = $("#provider_id").val();
            var min_amount = $("#min_amount").val();
            var max_amount = $("#max_amount").val();
            var type = $("#type").val();
            var st = $("#st").val();
            var sd = $("#sd").val();
            var d = $("#d").val();
            var r = $("#r").val();
            var referral = $("#referral").val();
            var dataString = 'scheme_id=' + scheme_id + '&provider_id=' + provider_id + '&min_amount=' + min_amount + '&max_amount=' + max_amount + '&type=' + type + '&st=' + st + '&sd=' + sd + '&d=' + d + '&r=' + r + '&referral=' + referral + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/store-commission')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () {
                            location.reload(1);
                        }, 3000);
                    } else if (msg.status == 'validation_error') {
                        $("#min_amount_errors").text(msg.errors.min_amount);
                        $("#max_amount_errors").text(msg.errors.max_amount);
                    } else {
                        swal("Faild", msg.message, "error");
                    }
                }
            });
        }

        function delete_commission(id) {
            swal({
                    title: "Are you sure?",
                    text: 'you want to delete this Slab ',
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
                            url: "{{url('admin/delete-commission-slab')}}",
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

        function view_commission(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/view-operator-commission')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        $("#view_id").val(msg.commission.id);
                        $("#view_provider_name").val(msg.commission.provider_name);
                        $("#view_min_amount").val(msg.commission.min_amount);
                        $("#view_max_amount").val(msg.commission.max_amount);
                        $("#view_st").val(msg.commission.st);
                        $("#view_sd").val(msg.commission.sd);
                        $("#view_d").val(msg.commission.d);
                        $("#view_r").val(msg.commission.r);
                        $("#view_referral").val(msg.commission.referral);
                        $("#view_type").val(msg.commission.type);
                        $("#view_commission_model").modal('show');
                    } else {
                        swal("Faild", msg.message, "error");
                    }
                }
            });
        }

        function update_commission() {
            $("#update_btn").hide();
            $("#update_btn_loader").show();
            var token = $("input[name=_token]").val();
            var id = $("#view_id").val();
            var min_amount = $("#view_min_amount").val();
            var max_amount = $("#view_max_amount").val();
            var st = $("#view_st").val();
            var sd = $("#view_sd").val();
            var d = $("#view_d").val();
            var r = $("#view_r").val();
            var referral = $("#view_referral").val();
            var type = $("#view_type").val();
            var dataString = 'id=' + id + '&min_amount=' + min_amount + '&max_amount=' + max_amount + '&st=' + st + '&sd=' + sd + '&d=' + d + '&r=' + r + '&referral=' + referral + '&type=' + type + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/update-operator-commission')}}",
                data: dataString,
                success: function (msg) {
                    $("#update_btn").show();
                    $("#update_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () {
                            location.reload(1);
                        }, 3000);
                    } else {
                        swal("Faild", msg.message, "error");
                    }
                }
            });
        }
    </script>
    <!--  Content Area Starts  -->
    <div id="content" class="main-content">
        <input type="hidden" id="scheme_id" value="{{ $scheme_id }}">
        <input type="hidden" id="provider_id" value="{{ $provider_id }}">
        <!-- Main Body Starts -->
    @include('themes2.admin.layout.breadcrumb')
    <!-- Main Body Starts -->
        <div class="layout-px-spacing">
            <div class="row layout-top-spacing">
                <!-- REVENUE ENDS-->
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
                    <div class="widget dashboard-table">
                        <div class="widget-content">
                            <div class="card-body">

                                <div class="widget-content widget-content-area">
                                    <div class="form-group row">
                                        <div class="col-lg-12 col-md-12 col-sm-12">
                                            <div class="form-row">
                                                <div class="col-md-3 mb-4">
                                                    <label class="form-label">Min Amount: <span class="tx-danger">*</span></label>
                                                    <input class="form-control" type="text" id="min_amount" placeholder="Min Amount" autocomplete="off">
                                                    <span class="invalid-feedback d-block" id="min_amount_errors"></span>
                                                </div>

                                                <div class="col-md-3 mb-4">
                                                    <label class="form-label">Max Amount: <span class="tx-danger">*</span></label>
                                                    <input class="form-control" type="text" id="max_amount" placeholder="Max Amount" autocomplete="off">
                                                    <span class="invalid-feedback d-block" id="max_amount_errors"></span>
                                                </div>

                                                <div class="col-md-3 mb-4">
                                                    <label class="form-label">Type: <span class="tx-danger">*</span></label>
                                                    <select class="form-control" id="type">
                                                        <option value="0">%</option>
                                                        <option value="1">Rs</option>
                                                    </select>
                                                </div>

                                                <div class="col-md-3 mb-4">
                                                    <label class="form-label">Sales Team: <span class="tx-danger">*</span></label>
                                                    <input class="form-control" type="text" id="st" value="0" autocomplete="off">
                                                </div>

                                                <div class="col-md-3 mb-4">
                                                    <label class="form-label">Super Distributor: <span class="tx-danger">*</span></label>
                                                    <input class="form-control" type="text" id="sd" value="0" autocomplete="off">
                                                </div>

                                                <div class="col-md-3 mb-4">
                                                    <label class="form-label">Distributor: <span class="tx-danger">*</span></label>
                                                    <input class="form-control" type="text" id="d" value="0" autocomplete="off">
                                                </div>

                                                <div class="col-md-3 mb-4">
                                                    <label class="form-label">Retailer: <span class="tx-danger">*</span></label>
                                                    <input class="form-control" type="text" id="r" value="0" autocomplete="off">
                                                </div>

                                                <div class="col-md-3 mb-4">
                                                    <label class="form-label">Referral: <span class="tx-danger">*</span></label>
                                                    <input class="form-control" type="text" id="referral" value="0" autocomplete="off">
                                                </div>
                                            </div>
                                            <button class="btn btn-primary"  type="button" onclick="save_commission()"><i class="fas fa-plus-square"></i> Add Commission</button>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
                    <div class="widget dashboard-table">
                        <div class="widget-heading">
                            <h5 class=""> {{ $scheme_name }} : {{ $provider_name }} </h5>
                        </div>
                        <hr>
                        <div class="widget-content">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table text-md-nowrap" id="example1">
                                        <thead>
                                        <tr>
                                            <th class="wd-15p border-bottom-0">Provider Name</th>
                                            <th class="wd-15p border-bottom-0">Min Amount</th>
                                            <th class="wd-15p border-bottom-0">Max Amount</th>
                                            <th class="wd-15p border-bottom-0">Type</th>
                                            <th class="wd-15p border-bottom-0">Sales Team</th>
                                            <th class="wd-15p border-bottom-0">Super Disributor</th>
                                            <th class="wd-15p border-bottom-0">Disributor</th>
                                            <th class="wd-15p border-bottom-0">Retailer</th>
                                            <th class="wd-15p border-bottom-0">Referral</th>
                                            <th class="wd-15p border-bottom-0">Update</th>
                                            <th class="wd-15p border-bottom-0">Action</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($commission as $value)
                                            <tr>
                                                <td>{{ $value->provider->provider_name }}</td>
                                                <td>{{ $value->min_amount }}</td>
                                                <td>{{ $value->max_amount }}</td>
                                                <td>@if($value->type == 0) % @else Rs @endif</td>
                                                <td>{{ $value->st }}</td>
                                                <td>{{ $value->sd }}</td>
                                                <td>{{ $value->d }}</td>
                                                <td>{{ $value->r }}</td>
                                                <td>{{ $value->referral }}</td>
                                                <td><button class="btn btn-success btn-sm" onclick="view_commission({{ $value->id }})">Update</button></td>
                                                <td><button class="btn btn-danger btn-sm" onclick="delete_commission({{ $value->id }})">Delete</button></td>
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



    {{--Start Update Commission model--}}
        <div class="modal  show" id="view_commission_model"data-toggle="modal">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content modal-content-demo">
                    <div class="modal-header">
                        <h6 class="modal-title">Update Commission</h6>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-body">
                            <input type="hidden" id="view_id">
                            <div class="row">

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Provider Name</label>
                                        <input type="text" id="view_provider_name" class="form-control" placeholder="Provider Name" readonly>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Type</label>
                                        <select class="form-control" id="view_type">
                                            <option value="0">%</option>
                                            <option value="1">Rs</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Minimum Amount</label>
                                        <input type="text" id="view_min_amount" class="form-control" placeholder="Minimum Amount">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Maximum Amount</label>
                                        <input type="text" id="view_max_amount" class="form-control" placeholder="Maximum Amount">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Sales Team</label>
                                        <input type="text" id="view_st" class="form-control" placeholder="Sales Team">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Super Distributor</label>
                                        <input type="text" id="view_sd" class="form-control" placeholder="Super Distributor">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Distributor</label>
                                        <input type="text" id="view_d" class="form-control" placeholder="Distributor">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Retailer</label>
                                        <input type="text" id="view_r" class="form-control" placeholder="Retailer">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Referral</label>
                                        <input type="text" id="view_referral" class="form-control" placeholder="Referral">
                                    </div>
                                </div>


                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn ripple btn-primary" type="button" onclick="update_commission()" id="update_btn">Update Now</button>
                        <button class="btn btn-primary" type="button"  id="update_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                        <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                    </div>
                </div>
            </div>
        </div>
    {{--End Update Commission model--}}


@endsection