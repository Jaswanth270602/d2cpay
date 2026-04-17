@extends('agent.layout.header')
@section('content')



    <!-- main-content-body -->
    <div class="main-content-body">

        <!-- row -->
        <div class="row row-sm">
        @include('agent.aeps.left_side')

        <!-- Col -->
            <div class="col-lg-8 col-xl-9">
                {{--prepaid commission--}}
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">{{ $page_title }}</h4>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <iframe src="{{url('agent/aeps/v1/route-2-landing')}}" frameborder="0" width="100%" height="600"></iframe>
                    </div>


                </div>
                {{--clase prepaid commission--}}








            </div>
            <!-- /Col -->


        </div>
        <!-- /row -->

        <!-- row -->


    </div>
    <!-- /row -->
    </div>
    <!-- /container -->
    </div>
    <!-- /main-content -->





@endsection