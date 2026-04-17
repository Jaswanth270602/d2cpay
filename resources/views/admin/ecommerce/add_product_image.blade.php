@extends('admin.layout.header')
@section('content')

    <script type="text/javascript">
        function delete_benner(id) {
            if (confirm("Are you sure? Delete this image") == true) {
                $(".loader").show();
                var token = $("input[name=_token]").val();
                var dataString = 'id=' + id +  '&_token=' + token;
                $.ajax({
                    type: "POST",
                    url: "{{url('admin/ecommerce/delete-product-image')}}",
                    data: dataString,
                    success: function (msg) {
                        $(".loader").hide();
                        if (msg.status == 'success') {
                            swal("Success", msg.message, "success");
                            setTimeout(function () { location.reload(1); }, 3000);
                        }else{
                            swal("Faild", msg.message, "error");
                        }
                    }
                });
            }
        }

        function view_images(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/ecommerce/view-product-image')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        $("#view_id").val(msg.details.id);
                        $("#view_status_id").val(msg.details.status_id);
                        $("#view_image_model").modal('show');
                    }else{
                        swal("Faild", msg.message, "error");
                    }
                }
            });
        }
        
        function update_category() {
            $("#update_btn").hide();
            $("#update_btn_loader").show();
            var token = $("input[name=_token]").val();
            var id = $("#view_id").val();
            var status_id = $("#view_status_id").val();
            var dataString = 'id=' + id + '&status_id=' + status_id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/ecommerce/update-product-image')}}",
                data: dataString,
                success: function (msg) {
                    $("#update_btn").show();
                    $("#update_btn_loader").hide();
                    var token = $("input[name=_token]").val();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    }else{
                        swal("Faild", msg.message, "error");
                    }
                }
            });
        }
    </script>



    <div class="main-content-body">

        <div class="row">
            <div class="col-lg-4 col-md-12">
                <form role="form" action="{{url('admin/ecommerce/save-product-image')}}" method="post" enctype="multipart/form-data">
                    {!! csrf_field() !!}
                    <div class="card">
                        <div class="card-body">
                            <div>
                                <h6 class="card-title mb-1">Upload Sub Image</h6>
                                <hr>
                            </div>

                            @if(Session::has('success'))
                                <div class="alert alert-success">
                                    <a class="close" data-dismiss="alert">×</a>
                                    <strong>Alert </strong> {!!Session::get('success')!!}
                                </div>
                            @endif

                            @if(Session::has('failure'))
                                <div class="alert alert-danger">
                                    <a class="close" data-dismiss="alert">×</a>
                                    <strong>Alert </strong> {!!Session::get('failure')!!}
                                </div>
                            @endif


                            <input type="hidden" value="{{$encrypt_id}}" name="encrypt_id">

                            <div class="mb-4">
                                <label>Product Name</label>
                                <input type="text" class="form-control" placeholder="Product Name" value="{{ $page_title }}" disabled>
                            </div>

                            <div class="mb-4">
                                <label>Select Image</label>
                                <input type="file" class="form-control"  name="photo">
                                @if ($errors->has('photo'))
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required">{{ $errors->first('photo') }}</li>
                                    </ul>
                                @endif
                            </div>

                            <div class="alert alert-danger" role="alert">
                                Before uploading the product photo, in the photo shop adjust the photo's height 300 and width 400 so that your ecommerce design looks amazing Click to see our designs
                                <a href="{{url('assets/img/ecommerce-ui.png')}}" target="_blank">Click Here</a>
                            </div>

                        </div>

                        <div class="modal-footer">
                            <button class="btn ripple btn-primary" type="submit" >Save</button>
                            <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                        </div>
                    </div>
                </form>
            </div>



            <div class="col-lg-8 col-md-12">
                <div class="card custom-card">
                    <div class="card-body ht-100p">

                        <div class="product-card card">
                            <div class="card-body h-100">

                                <div class="table-responsive">
                                    <table class="table text-md-nowrap" id="example1">
                                        <thead>
                                        <tr>
                                            <th class="wd-15p border-bottom-0">Sr No</th>
                                            <th class="wd-15p border-bottom-0">Created Date</th>
                                            <th class="wd-15p border-bottom-0">Photo</th>
                                            <th class="wd-15p border-bottom-0">Status</th>
                                            <th class="wd-15p border-bottom-0">Action</th>
                                            <th class="wd-15p border-bottom-0">Delete</th>

                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php $i = 0 ?>
                                        @foreach($productimages as $value)
                                            <tr>
                                                <td>{{ ++$i }}</td>
                                                <td>{{ $value->created_at }}</td>
                                                <td><a href="{{ $value->photo }}" target="_blank"><img src="{{ $value->photo }}" style="width: 30%;"></a></td>
                                                <td>
                                                    @if($value->status_id == 1) <span class="badge badge-success">Approved</span>
                                                    @elseif($value->status_id == 2) <span class="badge badge-danger">Rejected</span>
                                                    @else <span class="badge badge-warning">Pending</span> @endif
                                                </td>
                                                <td><button type="button" class="btn btn-success btn-sm" onclick="view_images({{ $value->id }})">Update</button></td>
                                                <td><button type="button" class="btn btn-danger btn-sm" onclick="delete_benner({{ $value->id }})">Delete</button></td>
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

    </div>
    </div>
    </div>


    <div class="modal  show" id="view_image_model"data-toggle="modal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-content-demo">
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
                                    <label for="name">Status</label>
                                    <select class="form-control" id="view_status_id">
                                        <option value="1">Approved</option>
                                        <option value="2">Rejected</option>
                                        <option value="3">Pending</option>
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
                    <button class="btn ripple btn-primary" type="button" id="update_btn" onclick="update_category()">Update Now</button>
                    <button class="btn btn-primary" type="button"  id="update_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                    <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection