@extends('admin.layout.header')
@section('content')

    <script type="text/javascript">
        function add_category() {
            $("#add_category_btn").hide();
            $("#add_category_btn_loader").show();
            var token = $("input[name=_token]").val();
            var category_id = $("#category_id").val();
            var category_name = $("#category_name").val();
            var slug = $("#slug").val();
            var meta_title = $("#meta_title").val();
            var meta_keywords = $("#meta_keywords").val();
            var meta_description = $("#meta_description").val();
            var commission = $("#commission").val();
            var dataString = 'category_id='  + category_id + '&category_name=' + encodeURIComponent(category_name) +  '&slug=' + encodeURIComponent(slug) + '&meta_title=' + encodeURIComponent(meta_title) + '&meta_keywords=' + encodeURIComponent(meta_keywords) + '&meta_description=' + encodeURIComponent(meta_description) + '&commission=' + commission + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/ecommerce/save-sub-category')}}",
                data: dataString,
                success: function (msg) {
                    $("#add_category_btn").show();
                    $("#add_category_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#category_id_errors").text(msg.errors.category_id);
                        $("#category_name_errors").text(msg.errors.category_name);
                        $("#slug_errors").text(msg.errors.slug);
                        $("#meta_title_errors").text(msg.errors.meta_title);
                        $("#meta_keywords_errors").text(msg.errors.meta_keywords);
                        $("#meta_description_errors").text(msg.errors.meta_description);
                        $("#commission_errors").text(msg.errors.commission);
                    }else{
                        swal("Faild", msg.message, "error");
                    }
                }
            });
        }
        
        function view_category(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/ecommerce/view-sub-category')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        $("#view_id").val(msg.details.id);
                        $("#view_category_id").val(msg.details.category_id);
                        $("#view_category_name").val(msg.details.category_name);
                        $("#view_slug").val(msg.details.slug);
                        $("#view_meta_title").val(msg.details.meta_title);
                        $("#view_meta_keywords").val(msg.details.meta_keywords);
                        $("#view_meta_description").val(msg.details.meta_description);
                        $("#view_status_id").val(msg.details.status_id);
                        $("#view_commission").val(msg.details.commission);
                        $("#view_category_model").modal('show');
                    }else{
                        swal("Faild", msg.message, "error");
                    }
                }
            });
        }
        
        function update_category() {
            $("#update_category_btn").hide();
            $("#update_category_btn_loader").show();
            var token = $("input[name=_token]").val();
            var id = $("#view_id").val();
            var category_id = $("#view_category_id").val();
            var category_name = $("#view_category_name").val();
            var slug = $("#view_slug").val();
            var status_id = $("#view_status_id").val();
            var meta_title = $("#view_meta_title").val();
            var meta_keywords = $("#view_meta_keywords").val();
            var meta_description = $("#view_meta_description").val();
            var commission = $("#view_commission").val();
            var dataString = 'category_id='  + category_id + '&category_name=' + encodeURIComponent(category_name) +  '&slug=' + encodeURIComponent(slug) + '&id=' + id + '&status_id=' + status_id + '&meta_title=' + encodeURIComponent(meta_title) + '&meta_keywords=' + encodeURIComponent(meta_keywords) +  '&meta_description=' + encodeURIComponent(meta_description) + '&commission=' + commission + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/ecommerce/update-sub-category')}}",
                data: dataString,
                success: function (msg) {
                    $("#update_category_btn").show();
                    $("#update_category_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#view_category_id_errors").text(msg.errors.category_id);
                        $("#view_category_name_errors").text(msg.errors.category_name);
                        $("#view_slug_errors").text(msg.errors.slug);
                        $("#view_status_id_errors").text(msg.errors.status_id);
                        $("#view_meta_title_errors").text(msg.errors.meta_title);
                        $("#view_meta_keywords_errors").text(msg.errors.meta_keywords);
                        $("#view_meta_description_errors").text(msg.errors.meta_description);
                        $("#view_commission_errors").text(msg.errors.commission);
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
                            @if(Auth::User()->role_id == 1)
                                <button class="btn btn-danger btn-sm" data-target="#add_sub_category_model" data-toggle="modal">Add Sub Category</button>
                            @endif
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
                                    <th class="wd-15p border-bottom-0">Category Name</th>
                                    <th class="wd-15p border-bottom-0">Sub Category Name</th>
                                    <th class="wd-15p border-bottom-0">Commission</th>
                                    <th class="wd-15p border-bottom-0">Status</th>
                                    <th class="wd-15p border-bottom-0">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $i = 0 ?>
                                @foreach($subcategories as $value)
                                    <tr>
                                        <td>{{ ++$i }}</td>
                                        <td>{{ $value->created_at }}</td>
                                        <td>{{ $value->category->category_name }}</td>
                                        <td>{{ $value->category_name }}</td>
                                        <td>{{ $value->commission }} %</td>
                                        <td>@if($value->status_id == 1) <span class="badge badge-success">Enabled</span> @else <span class="badge badge-danger">Disabled</span> @endif</td>
                                        <td><button class="btn btn-danger btn-sm" onclick="view_category({{ $value->id }})">Update</button></td>
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




    <div class="modal  show" id="add_sub_category_model"data-toggle="modal">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Add Sub Category</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">


                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Main Category</label>
                                     <select class="form-control" id="category_id">
                                         <option value="">Select Category</option>
                                         @foreach($categories as $value)
                                             <option value="{{ $value->id }}">{{ $value->category_name }}</option>
                                         @endforeach
                                     </select>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="category_id_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Sub Category Name</label>
                                    <input type="text" id="category_name" class="form-control" placeholder="Sub Category Name">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="category_name_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Slug (URL)</label>
                                    <input type="text" id="slug" class="form-control" placeholder="Slug (URL)">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="slug_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Commission (%)</label>
                                    <input type="text" id="commission" class="form-control" placeholder="Commission (%)">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="commission_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Meta Title</label>
                                    <input type="text" id="meta_title" class="form-control" placeholder="Meta Title">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="meta_title_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Meta Keywords</label>
                                    <input type="text" id="meta_keywords" class="form-control" placeholder="Meta Keywords">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="meta_keywords_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Meta Description</label>
                                    <textarea type="text" id="meta_description" class="form-control" placeholder="Meta Description" rows="4"></textarea>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="meta_description_errors"></li>
                                    </ul>
                                </div>
                            </div>


                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn ripple btn-primary" type="button" id="add_category_btn" onclick="add_category()">Add Sub Category</button>
                    <button class="btn btn-primary" type="button"  id="add_category_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                    <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
    </div>



    <div class="modal  show" id="view_category_model"data-toggle="modal">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Update Sub Category</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">
                            <input type="hidden" id="view_id">


                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Main Category</label>
                                    <select class="form-control" id="view_category_id">
                                        <option value="">Select Category</option>
                                        @foreach($categories as $value)
                                            <option value="{{ $value->id }}">{{ $value->category_name }}</option>
                                        @endforeach
                                    </select>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_category_id_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Sub Category Name</label>
                                    <input type="text" id="view_category_name" class="form-control" placeholder="Sub Category Name">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_category_name_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Meta Title</label>
                                    <input type="text" id="view_meta_title" class="form-control" placeholder="Meta Title">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_meta_title_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Meta Keywords</label>
                                    <input type="text" id="view_meta_keywords" class="form-control" placeholder="Meta Keywords">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_meta_keywords_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Meta Description</label>
                                    <textarea type="text" id="view_meta_description" class="form-control" placeholder="Meta Description" rows="4"></textarea>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_meta_description_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Slug (URL)</label>
                                    <input type="text" id="view_slug" class="form-control" placeholder="Slug (URL)">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_slug_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
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

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Commission (%)</label>
                                    <input type="text" id="view_commission" class="form-control" placeholder="Commission (%)">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_commission_errors"></li>
                                    </ul>
                                </div>
                            </div>


                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn ripple btn-primary" type="button" id="update_category_btn" onclick="update_category()">Update Sub Category</button>
                    <button class="btn btn-primary" type="button"  id="update_category_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                    <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
    </div>



@endsection