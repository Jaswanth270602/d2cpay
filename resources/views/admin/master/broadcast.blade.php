@extends('admin.layout.header')
@section('content')
    <script src="https://cdn.ckeditor.com/4.14.1/full/ckeditor.js"></script>
    <div class="main-content-body">

        {{--service detail--}}
        <div class="row row-sm">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">{{ $page_title }}</h4>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
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
                                                <ul class="parsley-errors-list filled">
                                                    <li class="parsley-required">{{ $errors->first('heading') }}</li>
                                                </ul>
                                            @endif
                                        </div>
                                    </div>


                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="name">Image</label>
                                            <input type="file" class="form-control" name="photo">
                                            @if ($errors->has('image'))
                                                <ul class="parsley-errors-list filled">
                                                    <li class="parsley-required">{{ $errors->first('image') }}</li>
                                                </ul>
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
                                                <ul class="parsley-errors-list filled">
                                                    <li class="parsley-required">{{ $errors->first('img_status') }}</li>
                                                </ul>
                                            @endif
                                        </div>
                                    </div>



                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="name">Content</label>
                                            <textarea class="form-control" name="message"
                                                      id="content">{{ $message }}</textarea>
                                            @if ($errors->has('message'))
                                                <ul class="parsley-errors-list filled">
                                                    <li class="parsley-required">{{ $errors->first('message') }}</li>
                                                </ul>
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
                                                <ul class="parsley-errors-list filled">
                                                    <li class="parsley-required">{{ $errors->first('status_id') }}</li>
                                                </ul>
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
            <!--/div-->

        </div>
        {{--service detail close--}}


    </div>
    </div>
    </div>




@endsection