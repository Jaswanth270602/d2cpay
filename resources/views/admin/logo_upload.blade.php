@extends('admin.layout.header')
@section('content')



    <div class="main-content-body">

        <div class="row">
            <div class="col-lg-5 col-md-12">
                <form role="form" action="{{url('admin/store-logo')}}" method="post" enctype="multipart/form-data">
                    {!! csrf_field() !!}
                <div class="card">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">{{ $page_title }}</h6>
                            <hr>
                        </div>

                        @if(Session::has('msg'))
                            <div class="alert alert-info">
                                <a class="close" data-dismiss="alert">×</a>
                                <strong>Alert </strong> {!!Session::get('msg')!!}
                            </div>
                        @endif

                        @if(Session::has('failure'))
                            <div class="alert alert-danger">
                                <a class="close" data-dismiss="alert">×</a>
                                <strong>Alert </strong> {!!Session::get('failure')!!}
                            </div>
                        @endif


                        <div class="mb-4">
                            <label>Select Logo</label>
                            <input type="file" class="form-control" placeholder="Select Logo" name="photo">
                        </div>


                        <p class="br-section-text">Width: 160 Height : 45</p>
                        <hr>

                    </div>

                    <div class="modal-footer">
                        <button class="btn ripple btn-primary" type="submit" >Upload Logo</button>
                        <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                    </div>
                </div>
                </form>
            </div>



            <div class="col-lg-7 col-md-12">
                <div class="card custom-card">
                    <div class="card-body ht-100p">
                        <div>
                            <h6 class="card-title mb-1">Current Logo</h6>
                            <hr>
                        </div>
                        <div class="product-card card">
                            <div class="card-body h-100">

                                <img class="w-100 mt-2 mb-3" src="{{$cdnLink}}{{$company_logo}}" alt="company-logo">
                                <button class="btn btn-success btn-block mb-0">Current Logo</button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>

    </div>
    </div>
    </div>


    @include('agent.service.recharge_confirm')
    @include('agent.service.dth_customer_info_model')
@endsection