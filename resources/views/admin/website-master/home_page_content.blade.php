@extends('admin.layout.header')
@section('content')
    <script src="//cdn.ckeditor.com/4.13.1/full/ckeditor.js"></script>


    <div class="main-content-body">
        {{--perssinal details--}}
        <div class="row row-sm">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">Add home page content</h4>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">

                        <div class="form-body">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="name">Content : </label>
                                        <textarea class="form-control" rows="8" placeholder="Enter Notification Message" name="content" id="content"></textarea>
                                    </div>
                                    <script>
                                        CKEDITOR.replace( 'content' );
                                    </script>
                                </div>
















                            </div>

                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-danger waves-effect waves-light">Save Details</button>
                    </div>
                </div>
            </div>
            <!--/div-->
        </div>
        {{--perssinal details clase--}}








    </div>
    </div>
    </div>




@endsection