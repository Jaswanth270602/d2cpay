@extends('admin.layout.header')
@section('content')

    <script type="text/javascript">
        function delete_benner(id) {
            if (confirm("Are you sure? Delete this banner") == true) {
                $(".loader").show();
                var token = $("input[name=_token]").val();
                var dataString = 'id=' + id +  '&_token=' + token;
                $.ajax({
                    type: "POST",
                    url: "{{url('admin/ecommerce/delete-shopping-banners')}}",
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
    </script>



    <div class="main-content-body">

        <div class="row">
            <div class="col-lg-4 col-md-12">
                <form role="form" action="{{url('admin/ecommerce/store-shopping-banners')}}" method="post" enctype="multipart/form-data">
                    {!! csrf_field() !!}
                    <div class="card">
                        <div class="card-body">
                            <div>
                                <h6 class="card-title mb-1">Upload Ecommerce Banner</h6>
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

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif



                            <div class="mb-4">
                                <label>Select Banner</label>
                                <input type="file" class="form-control" placeholder="Select Logo" name="banners">
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
                                            <th class="wd-15p border-bottom-0">Banner</th>
                                            <th class="wd-15p border-bottom-0">Action</th>

                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php $i = 0 ?>
                                        @foreach($shoppingbanners as $value)
                                            <tr>
                                                <td>{{ ++$i }}</td>
                                                <td>{{ $value->created_at }}</td>
                                                <td><img src="{{ $value->banners }}" style="height: 50px;"></td>
                                                <td><button class="btn btn-danger btn-sm" onclick="delete_benner({{ $value->id }})"><i class="fas fa-trash-alt"></i> Delete</button></td>
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

@endsection