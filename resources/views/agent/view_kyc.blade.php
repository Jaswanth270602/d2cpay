@extends('agent.layout.header')
@section('content')

    <div class="main-content-body">
        <div class="row row-sm">


            <div class="col-lg-4 col-md-12">
                <div class="card custom-card">
                    <div class="card-body text-center">

                        <div class="user-lock text-center">
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
                                <a href="{{$cdnLink}}{{ $shop_photo }}" target="_blank" class="btn btn-success btn-block mb-0"><i class="fas fa-plus-circle"></i> Download</a>
                            </div>
                        </div>

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
                                <a href="{{$cdnLink}}{{ $gst_regisration_photo }}" target="_blank" class="btn btn-success btn-block mb-0"><i class="fas fa-plus-circle"></i> Download</a>
                            </div>
                        </div>

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
                                <a href="{{$cdnLink}}{{ $pancard_photo }}" target="_blank" class="btn btn-success btn-block mb-0"><i class="fas fa-plus-circle"></i> Download</a>
                            </div>
                        </div>

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
                                <a href="{{$cdnLink}}{{ $cancel_cheque }}" target="_blank" class="btn btn-success btn-block mb-0"><i class="fas fa-plus-circle"></i> Download</a>
                            </div>
                        </div>

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
                                <a href="{{$cdnLink}}{{ $address_proof }}" target="_blank" class="btn btn-success btn-block mb-0"><i class="fas fa-plus-circle"></i> Download</a>
                            </div>
                        </div>

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


@endsection