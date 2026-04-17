@extends('themes2.admin.layout.header')
@section('content')
    <script src="https://cdn.ckeditor.com/4.14.1/full/ckeditor.js"></script>


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
                        </div>
                        <hr>
                        <div class="widget-content">
                            <form action="{{url('admin/save-broadcast')}}" method="post" enctype="multipart/form-data">
                                {!! csrf_field() !!}

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

                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">

                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label for="name">Broadcast Heading</label>
                                                    <input type="text" class="form-control" name="heading"
                                                           placeholder="Broadcast Heading" value="{{ $heading }}">
                                                    @if ($errors->has('heading'))
                                                        <span class="invalid-feedback d-block">{{ $errors->first('heading') }}</span>
                                                    @endif
                                                </div>
                                            </div>


                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="name">Image</label>
                                                    <input type="file" class="form-control" name="photo">
                                                    @if ($errors->has('image'))
                                                        <span class="invalid-feedback d-block">{{ $errors->first('image') }}</span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="name">Image Status</label>
                                                    <select class="form-control" name="img_status">
                                                        <option value="1" @if($img_status == 1) selected @endif>Enabled</option>
                                                        <option value="2" @if($status_id == 2) selected @endif>Disabled</option>
                                                    </select>
                                                    @if ($errors->has('img_status'))
                                                        <span class="invalid-feedback d-block">{{ $errors->first('img_status') }}</span>
                                                    @endif
                                                </div>
                                            </div>



                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label for="name">Content</label>
                                                    <textarea class="form-control" name="message"
                                                              id="content">{{ $message }}</textarea>
                                                    @if ($errors->has('message'))
                                                        <span class="invalid-feedback d-block">{{ $errors->first('message') }}</span>
                                                    @endif
                                                    <script>
                                                        CKEDITOR.replace('message');
                                                    </script>
                                                </div>
                                            </div>

                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label for="name">Status</label>
                                                    <select class="form-control" name="status_id">
                                                        <option value="1" @if($status_id == 1) selected @endif>Enabled</option>
                                                        <option value="2" @if($status_id == 2) selected @endif>Disabled</option>
                                                    </select>
                                                    @if ($errors->has('status_id'))
                                                        <span class="invalid-feedback d-block">{{ $errors->first('status_id') }}</span>
                                                    @endif
                                                </div>
                                            </div>


                                        </div>

                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-danger waves-effect waves-light">Update Details
                                    </button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!-- Main Body Ends -->




@endsection