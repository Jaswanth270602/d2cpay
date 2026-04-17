@extends('themes2.admin.layout.header')
@section('content')
    <!--  Content Area Starts  -->
    <div id="content" class="main-content">

        <!-- Main Body Starts -->
    @include('themes2.admin.layout.breadcrumb')
    <!-- Main Body Starts -->
        <div class="layout-px-spacing">
            <div class="layout-top-spacing mb-2">
                <div class="col-md-12">
                    <div class="row">
                        <div class="container p-0">
                            <div class="row layout-top-spacing">
                                <div class="col-lg-6 layout-spacing">
                                    <div class="statbox widget box box-shadow mb-4">
                                        <form role="form" action="{{url('admin/store-logo')}}" method="post" enctype="multipart/form-data">
                                            {!! csrf_field() !!}
                                        <div class="widget-header">
                                            <div class="row">
                                                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                                                    <h4>{{ $page_title }}</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="widget-content widget-content-area">

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

                                            <div class="form-group">
                                                <label>Select Logo</label>
                                                <input type="file" class="form-control" placeholder="Select Logo" name="photo">
                                                <span class="form-text text-muted">Width: 160 Height : 45</span>
                                            </div>
                                        </div>
                                        <div class="widget-footer text-right">
                                            <button type="submit" class="btn btn-primary mr-2">Upload Logo</button>
                                            <button type="reset" class="btn btn-outline-primary"> Cancel</button>
                                        </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="col-lg-6 layout-spacing">
                                    <div class="statbox widget box box-shadow mb-4">
                                        <div class="widget-header">
                                            <div class="row">
                                                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                                                    <h4>Current Logo</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="widget-content widget-content-area">
                                            <img class="w-100 mt-2 mb-3" src="{{$cdnLink}}{{$company_logo}}" alt="company-logo">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Main Body Ends -->


@endsection