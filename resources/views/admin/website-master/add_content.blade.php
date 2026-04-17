@extends('admin.layout.header')
@section('content')
   {{-- <script src="//cdn.ckeditor.com/4.13.1/full/ckeditor.js"></script>--}}
   <script src="https://cdn.ckeditor.com/4.14.1/full/ckeditor.js"></script>


    <div class="main-content-body">
        {{--perssinal details--}}
        <div class="row row-sm">
            <div class="col-xl-12">
               <form action="{{url('admin/update-content')}}" method="post">
                   {!! csrf_field() !!}


                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">{{ $page_title }}</h4>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <input type="hidden" name="navigation_id" value="{{ $navigation_id }}">
                        <div class="form-body">
                            <div class="row">

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Navigation Name</label>
                                       <input type="text" value="{{ $navigation_name }}" class="form-control" disabled>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Navigation Slug (URL)</label>
                                        <input type="text" value="{{ $navigation_slug }}" class="form-control" disabled>
                                    </div>

                                </div>


                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="name">Content : </label>
                                        <textarea class="form-control"  name="nav_content" id="nav_content">{{ $content }}</textarea>
                                    </div>
                                    <script>
                                        CKEDITOR.replace( 'nav_content' );
                                    </script>
                                </div>



                            </div>

                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-danger waves-effect waves-light">Update Content</button>
                    </div>
                </div>
               </form>
            </div>
            <!--/div-->
        </div>
        {{--perssinal details clase--}}








    </div>
    </div>
    </div>




@endsection