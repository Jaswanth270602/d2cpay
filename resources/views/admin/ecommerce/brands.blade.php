@extends('admin.layout.header')
@section('content')
    
    <script type="text/javascript">
        function add_brand() {
            $("#add_brand_btn").hide();
            $("#add_brand_btn_loader").show();
            var token = $("input[name=_token]").val();
            var brand_name = $("#brand_name").val();
            var dataString = 'brand_name='  + encodeURIComponent(brand_name) +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/ecommerce/save-brands')}}",
                data: dataString,
                success: function (msg) {
                    $("#add_brand_btn").show();
                    $("#add_brand_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#brand_name_errors").text(msg.errors.brand_name);
                    }else{
                        swal("Faild", msg.message, "error");
                    }
                }
            });
        }

        function view_brand(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/ecommerce/view-brand')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        $("#view_id").val(msg.details.id);
                        $("#view_brand_name").val(msg.details.brand_name);
                        $("#view_status_id").val(msg.details.status_id);
                        $("#view_brand_model").modal('show');
                    }else{
                        swal("Faild", msg.message, "error");
                    }
                }
            });
        }
        
        function update_brand() {
            $("#update_brand_btn").hide();
            $("#update_brand_btn_loader").show();
            var token = $("input[name=_token]").val();
            var id = $("#view_id").val();
            var brand_name = $("#view_brand_name").val();
            var status_id = $("#view_status_id").val();
            var dataString = 'id=' + id + '&brand_name=' + brand_name + '&status_id=' + status_id +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/ecommerce/update-brands')}}",
                data: dataString,
                success: function (msg) {
                    $("#update_brand_btn").show();
                    $("#update_brand_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#view_brand_name_errors").text(msg.errors.brand_name);
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
                            <button class="btn btn-danger btn-sm" data-target="#add_brand_model" data-toggle="modal">Add New Brand</button>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table text-md-nowrap" id="example1">
                                <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0">Sr No</th>
                                    <th class="wd-15p border-bottom-0">Created Date</th>
                                    <th class="wd-15p border-bottom-0">Brand Name</th>
                                    <th class="wd-15p border-bottom-0">Status</th>
                                    <th class="wd-15p border-bottom-0">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $i = 0 ?>
                                @foreach($brands as $value)
                                    <tr>
                                        <td>{{ ++$i }}</td>
                                        <td>{{ $value->created_at }}</td>
                                        <td>{{ $value->brand_name }}</td>
                                        <td>@if($value->status_id == 1) <span class="badge badge-success">Enabled</span> @else <span class="badge badge-danger">Disabled</span> @endif</td>
                                        <td><button class="btn btn-danger btn-sm" onclick="view_brand({{ $value->id }})">Update</button></td>
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


    <div class="modal  show" id="add_brand_model"data-toggle="modal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Add New Brand</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Brand Name</label>
                                    <input type="text" id="brand_name" class="form-control" placeholder="Brand Name">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="brand_name_errors"></li>
                                    </ul>
                                </div>
                            </div>


                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn ripple btn-primary" type="button" id="add_brand_btn" onclick="add_brand()">Save Now</button>
                    <button class="btn btn-primary" type="button"  id="add_brand_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                    <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
    </div>



    <div class="modal  show" id="view_brand_model"data-toggle="modal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Update Brand</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">
                            <input type="hidden" id="view_id">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Brand Name</label>
                                    <input type="text" id="view_brand_name" class="form-control" placeholder="Brand Name">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_brand_name_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Status</label>
                                    <select class="form-control" id="view_status_id">
                                        <option value="1">Enabled</option>
                                        <option value="2">Disabled</option>
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
                    <button class="btn ripple btn-primary" type="button" id="update_brand_btn" onclick="update_brand()">Update Now</button>
                    <button class="btn btn-primary" type="button"  id="update_brand_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                    <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
    </div>





@endsection