@extends('front.template1.header')
@section('content')

<style>
    .about-inner {
        background-image: none !important;
        background-color: transparent !important; /* optional, just in case */
    }
</style>
    <!-- inner banner -->
    <section class="w3l-inner-banner-main">
        <div class="about-inner">
            <div class="container">
                <ul class="breadcrumbs-custom-path">
                    <li><a href="{{ url('') }}">Home <span class="fa fa-angle-double-right" aria-hidden="true"></span></a></li>
                    <li class="active">{{ $navigation_name  }}</li>
                </ul>
            </div>
        </div>
    </section>
    <!-- //covers -->
    <!---728x90--->

    <!-- features-4 block -->
    <section class="w3l-galleries-14">
        <div id="gallery14-block" class="py-5">
            <div class="container py-md-3">
                <div class="top-main-content">

                {!! $content !!}
                    <!---728x90--->



            </div>
        </div>
    </section>
    <!-- features-4 block -->
    <!---728x90--->


    <!---728x90--->


@endsection
