@extends('admin.layout.header')
@section('content')

    <script type="text/javascript">
        function view_kyc() {
            $("#view_kyc_model").modal('show');
        }

        function update_kyc_now() {
            $("#kyc_btn").hide();
            $("#kyc_btn_loader").show();
            var token = $("input[name=_token]").val();
            var user_id = $("#user_id").val();
            var kyc_remark = $("#kyc_remark").val();
            var kyc_status = $("#kyc_status").val();
            var dataString = 'user_id=' + user_id + '&kyc_remark=' + kyc_remark +  '&kyc_status=' + kyc_status + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/update-kyc')}}",
                data: dataString,
                success: function (msg) {
                    //$(".loader").hide();
                    $("#kyc_btn").show();
                    $("#kyc_btn_loader").hide();
                    if (msg.status == 'success') {
                        $("#view_kyc_model").modal('hide');
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#user_id_errors").text(msg.errors.user_id);
                        $("#kyc_remark_errors").text(msg.errors.kyc_remark);
                        $("#kyc_status_errors").text(msg.errors.kyc_status);
                    }else{
                        $("#view_kyc_model").modal('hide');
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }
    </script>


    <div class="main-content-body">
        <div class="row row-sm">


            <div class="col-lg-4 col-md-12">
                <div class="card custom-card">
                    <div class="card-body text-center">

                        <div class="user-lock text-center">
                            @if(Auth::User()->role_id <= 2)
                                <div class="dropdown text-right">
                                    <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                        <i class="fe fe-more-vertical text-dark fs-16"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right shadow">
                                        @if(Auth::User()->role_id == 2 && $permission_update_kyc == 1)
                                            <a class="dropdown-item" href="#" onclick="view_kyc()"><i class="fe fe-eye mr-2"></i> Action</a>
                                        @elseif(Auth::user()->role_id != 2)
                                            <a class="dropdown-item" href="#" onclick="view_kyc()"><i class="fe fe-eye mr-2"></i> Action</a>
                                        @endif

                                    </div>
                                </div>
                            @endif
                            <img alt="avatar" class="rounded-circle" src="{{$cdnLink}}{{ $profile_photo }}">
                        </div>
                        <h5 class="mb-1 mt-3 card-title">{{ $name }}</h5>
                        <p class="mb-2 mt-1 tx-inverse">{{ $role_type }}</p>
                        <p class="text-muted text-center mt-1">Company Name : {{ $website_name }}</p>
                        <p class="text-muted text-center mt-1">Kyc Status :
                            @if($kyc_status == 1) <span class="badge badge-success">Approved</span>
                            @elseif($kyc_status == 2) <span class="badge badge-danger">Rejected</span>
                            @else <span class="badge badge-warning">Pending</span>
                            @endif
                        </p>
                        @if($kyc_remark)
                            <p class="text-muted text-center mt-1">
                            <div class="alert alert-danger mg-b-0" role="alert">
                                <strong>Rmark! </strong> {{ $kyc_remark }}
                            </div>
                            </p>
                        @endif
                        <div class="mt-2 user-info btn-list">
                            <a class="btn btn-outline-light btn-block" href="#"><i class="fe fe-mail mr-2"></i><span> {{ $email }}</span></a>
                            <a class="btn btn-outline-light btn-block" href="#"><i class="fe fe-phone mr-2"></i><span> {{ $mobile }}</span></a>
                            <a class="btn btn-outline-light  btn-block" href="#"><i class="far fa-clock"></i> <span> {{ $joining_date }}</span></a>


                        </div>
                    </div>
                </div>
            </div>


            <div class="col-lg-4 col-md-12">
                <div class="card custom-card">
                    <div class="card-body ht-100p">
                        <div>
                            <h6 class="card-title mb-1">Shop Photo</h6>
                            <hr>
                        </div>
                        <div class="product-card card">
                            <div class="card-body h-100">
                                <img class="w-100 mt-2 mb-3" src="{{$cdnLink}}{{ $shop_photo }}" alt="product-image" style="height: 200px;">
                                <a href="{{$cdnLink}}{{ $shop_photo }}" target="_blank" class="btn btn-danger btn-block mb-0"><i class="fas fa-plus-circle"></i> Download</a>
                            </div>
                        </div>

                        @if(Auth::User()->role_id == 1)
                        <form role="form" action="{{url('admin/update-shop-photo')}}" method="post" enctype="multipart/form-data">
                            {!! csrf_field() !!}
                            <input type="hidden" name="user_id" value="{{ $user_id }}">
                            <div class="form-body">
                                <div class="form-group">
                                    <label for="name">Shop Photo : </label>
                                    <input class="form-control"  type="file" name="shop_photo">
                                </div>
                            </div>
                            <button class="btn btn-success btn-block mb-0" type="submit">Upload</button>
                        </form>
                            @endif
                    </div>
                </div>
            </div>



            <div class="col-lg-4 col-md-12">
                <div class="card custom-card">
                    <div class="card-body ht-100p">
                        <div>
                            <h6 class="card-title mb-1">Gst Registration</h6>
                            <hr>
                        </div>
                        <div class="product-card card">
                            <div class="card-body h-100">

                                <img class="w-100 mt-2 mb-3" src="{{$cdnLink}}{{ $gst_regisration_photo }}" alt="product-image" style="height: 200px;">
                                <a href="{{$cdnLink}}{{ $gst_regisration_photo }}" target="_blank" class="btn btn-danger btn-block mb-0"><i class="fas fa-plus-circle"></i> Download</a>
                            </div>
                        </div>
                        @if(Auth::User()->role_id == 1)
                        <form role="form" action="{{url('admin/update-gst-regisration-photo')}}" method="post" enctype="multipart/form-data">
                            {!! csrf_field() !!}
                            <input type="hidden" name="user_id" value="{{ $user_id }}">
                            <div class="form-body">
                                <div class="form-group">
                                    <label for="name">Gst Registration : </label>
                                    <input class="form-control"  type="file" name="gst_regisration_photo">
                                </div>
                            </div>
                            <button class="btn btn-success btn-block mb-0" type="submit">Upload</button>
                        </form>
                            @endif

                    </div>
                </div>
            </div>


            <div class="col-lg-4 col-md-12">
                <div class="card custom-card">
                    <div class="card-body ht-100p">
                        <div>
                            <h6 class="card-title mb-1">Pancard</h6>
                            <hr>
                        </div>
                        <div class="product-card card">
                            <div class="card-body h-100">

                                <img class="w-100 mt-2 mb-3" src="{{$cdnLink}}{{ $pancard_photo }}" alt="product-image" style="height: 200px;">
                                <a href="{{$cdnLink}}{{ $pancard_photo }}" target="_blank" class="btn btn-danger btn-block mb-0"><i class="fas fa-plus-circle"></i> Download</a>
                            </div>
                        </div>

                        @if(Auth::User()->role_id == 1)
                        <form role="form" action="{{url('admin/update-pancard-photo')}}" method="post" enctype="multipart/form-data">
                            {!! csrf_field() !!}
                            <input type="hidden" name="user_id" value="{{ $user_id }}">
                            <div class="form-body">
                                <div class="form-group">
                                    <label for="name">Pancard : </label>
                                    <input class="form-control"  type="file" name="pancard_photo">
                                </div>
                            </div>
                            <button class="btn btn-success btn-block mb-0" type="submit">Upload</button>
                        </form>
                            @endif


                    </div>
                </div>
            </div>



            <div class="col-lg-4 col-md-12">
                <div class="card custom-card">
                    <div class="card-body ht-100p">
                        <div>
                            <h6 class="card-title mb-1">cancel cheque</h6>
                            <hr>
                        </div>
                        <div class="product-card card">
                            <div class="card-body h-100">

                                <img class="w-100 mt-2 mb-3" src="{{$cdnLink}}{{ $cancel_cheque }}" alt="product-image" style="height: 200px;">
                                <a href="{{$cdnLink}}{{ $cancel_cheque }}" target="_blank" class="btn btn-danger btn-block mb-0"><i class="fas fa-plus-circle"></i> Download</a>
                            </div>
                        </div>

                        @if(Auth::User()->role_id == 1)
                        <form role="form" action="{{url('admin/cancel-cheque-photo')}}" method="post" enctype="multipart/form-data">
                            {!! csrf_field() !!}
                            <input type="hidden" name="user_id" value="{{ $user_id }}">
                            <div class="form-body">
                                <div class="form-group">
                                    <label for="name">Cancel Cheque : </label>
                                    <input class="form-control"  type="file" name="cancel_cheque">
                                </div>
                            </div>
                            <button class="btn btn-success btn-block mb-0" type="submit">Upload</button>
                        </form>

                            @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-12">
                <div class="card custom-card">
                    <div class="card-body ht-100p">
                        <div>
                            <h6 class="card-title mb-1">address proof</h6>
                            <hr>
                        </div>
                        <div class="product-card card">
                            <div class="card-body h-100">

                                <img class="w-100 mt-2 mb-3" src="{{$cdnLink}}{{ $address_proof }}" alt="product-image" style="height: 200px;">
                                <a href="{{$cdnLink}}{{ $address_proof }}" target="_blank" class="btn btn-danger btn-block mb-0"><i class="fas fa-plus-circle"></i> Download</a>
                            </div>
                        </div>

                        @if(Auth::User()->role_id == 1)
                        <form role="form" action="{{url('admin/address-proof-photo')}}" method="post" enctype="multipart/form-data">
                            {!! csrf_field() !!}
                            <input type="hidden" name="user_id" value="{{ $user_id }}">
                            <div class="form-body">
                                <div class="form-group">
                                    <label for="name">Address Proof : </label>
                                    <input class="form-control"  type="file" name="address_proof">
                                </div>
                            </div>
                            <button class="btn btn-success btn-block mb-0" type="submit">Upload</button>
                        </form>
                            @endif
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- /row -->
    </div>
    <!-- /container -->
    </div>
    <!-- /main-content -->


    <div class="modal  show" id="view_kyc_model"data-toggle="modal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Update Kyc</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">

                        <input type="hidden" id="user_id" value="{{$user_id}}">
                        <div class="row">

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">User Name</label>
                                    <input type="text" id="name" class="form-control" value="{{$name}}" disabled>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Mobile Number</label>
                                    <input type="text" id="name" class="form-control" value="{{$mobile}}" disabled>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Kyc Status</label>
                                    <select class="form-control" id="kyc_status">
                                        <option value="1"  @if($kyc_status == '1') selected="selected" @endif>Approved</option>
                                        <option value="2" @if($kyc_status == '2') selected="selected" @endif>Rejected</option>
                                        <option value="0" @if($kyc_status == '0') selected="selected" @endif>Pending</option>
                                    </select>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="kyc_status_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Remark</label>
                                    <textarea class="form-control" placeholder="Enter Remark" id="kyc_remark">{{ $kyc_remark }}</textarea>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="kyc_remark_errors"></li>
                                    </ul>
                                </div>
                            </div>


                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn ripple btn-primary" type="button" id="kyc_btn" onclick="update_kyc_now()">Update Kyc</button>
                    <button class="btn btn-primary" type="button"  id="kyc_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                    <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
    </div>

@endsection