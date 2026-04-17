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
                            <div class="card-body">
                                <form action="{{url('admin/update-content')}}" method="post">
                                    {!! csrf_field() !!}
                                <div class="form-body">
                                    <div class="row">
                                        <input type="hidden" name="navigation_id" value="{{ $navigation_id }}">
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
                                                <label for="name">Content</label>
                                                <textarea class="form-control"  name="nav_content" id="nav_content">{{ $content }}</textarea>
                                                <script>
                                                    CKEDITOR.replace('nav_content');
                                                </script>
                                            </div>
                                        </div>



                                    </div>

                                </div>
                                <hr>
                                <div class="widget-footer text-right">
                                    <button type="submit" class="btn btn-primary mr-2">Submit</button>
                                    <button type="reset" class="btn btn-outline-primary"> Cancel</button>
                                </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!-- Main Body Ends -->


@endsection