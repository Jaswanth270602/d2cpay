@extends('agent.layout.header')
@section('content')



    <div class="main-content-body">


        <div class="row row-sm">
            <!-- Col -->
            <div class="col-lg-4 col-xl-3 col-md-12">
                <div class="card mg-b-20 compose-mail">
                    <div class="main-content-left main-content-left-mail card-body">
                        <div class="main-mail-menu">
                            <nav class="nav main-nav-column mg-b-20">
                                <a class="nav-link" href="#">   <i class="fe fe-bell"></i> {{ $notitication_title }} <span>{{ $time }}</span></a>
                                @foreach(Auth::User()->unreadNotifications as $value)
                                    <a class="nav-link" href="{{url('agent/notification/view')}}/{{$value->id}}">   <i class="fe fe-bell"></i> {{ Str::limit($value->data['letter']['title'], 25)  }} <span>{{ Carbon\Carbon::parse($value->created_at)->diffForHumans() }}</span></a>
                                @endforeach

                            </nav>
                        </div><!-- main-mail-menu -->
                    </div>
                </div>
            </div>
            <!-- /Col -->
            <div class="col-lg-8 col-xl-9 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="email-media">
                            <div class="mt-0 d-sm-flex">
                                <h6 class="modal-title"><i class="fe fe-bell"></i> View Notification</h6>
                                <div class="media-body">
                                    <div class="float-right d-none d-md-flex fs-15">
                                        <small class="mr-3">{{ $created_at }}</small>
                                    </div>

                            </div>
                        </div>
                        </div>
                            <hr>
                        <div class="eamil-body mt-4">
                            <p>{{ $notitication_body }}</p>
                            <br>


                        </div>
                    </div>

                </div>
            </div>
        </div>


    </div>
    </div>
    </div>





@endsection