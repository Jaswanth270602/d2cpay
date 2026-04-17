@extends('agent.layout.header')
@section('content')

    <div class="main-content-body">

        <div class="row">


            @include('agent.service.bbps_validation')

            @include('agent.service.left_banner')

        </div>

    </div>
    </div>
    </div>


    @include('agent.service.recharge_confirm')
@endsection