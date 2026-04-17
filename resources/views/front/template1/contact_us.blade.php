@extends('front.template1.header')
@section('content')

<style>
    .about-inner {
        background-image: none !important;
        background-color: transparent !important;
    }
</style>

    <!-- inner banner -->
    
    
    <section class="w3l-inner-banner-main" style="background-color: navy; color: white;">
    <div class="about-inner">
        <div class="container">
            <ul class="breadcrumbs-custom-path">
                <li><a href="/" style="color: white;">Home <span class="fa fa-angle-double-right" aria-hidden="true"></span></a></li>
                <li class="active">Contact</li>
            </ul>
        </div>
    </div>
</section>

   <!-- <section class="w3l-inner-banner-main">
        <div class="about-inner">
            <div class="container">
                <ul class="breadcrumbs-custom-path">
                    <li><a href="/">Home <span class="fa fa-angle-double-right" aria-hidden="true"></span></a></li>
                    <li class="active">Contact</li>
                </ul>
            </div>
        </div>
    </section>-->
    <!-- //covers -->
    <!---728x90--->

    <!-- contact -->
    <section class="w3l-contacts-12" id="contact">
        <div class="contact-top pt-5">
            <div class="container py-md-3">
                <div class="heading text-center mx-auto">
                    <h3 class="head">Have you a question?</h3>
                    <p class="my-3 head"> </p>
                </div>
                <!---728x90--->

                <div class="row cont-main-top mt-5 pt-3">

                    <!-- contact address -->

                    <!-- //contact address -->
                    <!-- contact form -->
                    <div class="contacts12-main col-lg-7 pr-lg-5 pr-3">
                        @if(Session::has('success'))
                            <div class="alert alert-info">
                                <a class="close" data-dismiss="alert">×</a>
                                <strong>Alert </strong> {!!Session::get('success')!!}
                            </div>
                        @endif
                        <form role="form" action="{{url('save-contact-enquiry')}}" method="post" class="main-input">
                            {!! csrf_field() !!}
                            <div class="top-inputs">
                                <input type="text" name="name" class="form-input" value="{{ old('name') }}" placeholder="Your Name"/>
                                @if ($errors->has('name'))
                                    <span style="color: red; font-size: 12px; margin-top: 5%;">{{ $errors->first('name') }}</span>
                                @endif
                            </div>

                            <div class="top-inputs">
                                <input type="email" name="email" class="form-input" value="{{ old('email') }}" placeholder="Email" />
                                @if ($errors->has('email'))
                                    <span style="color: red; font-size: 12px; margin-top: 5%;">{{ $errors->first('email') }}</span>
                                @endif
                            </div>

                            <div class="top-inputs">
                                <input type="tel" name="mobile_number" class="form-input" value="{{ old('mobile_number') }}" placeholder="Phone / Mobile No." />
                                @if ($errors->has('mobile_number'))
                                    <span style="color: red; font-size: 12px; margin-top: 5%;">{{ $errors->first('mobile_number') }}</span>
                                @endif
                            </div>

                            <div class="top-inputs">
                                <input type="text" name="message" class="form-input" value="{{ old('message') }}" placeholder="Message" />
                                @if ($errors->has('message'))
                                    <span style="color: red; font-size: 12px; margin-top: 5%;">{{ $errors->first('message') }}</span>
                                @endif
                            </div>

                            <div class="text-right">
                                <button type="submit" class="btn btn-theme2" style="background: linear-gradient(to right, #a445b2, #fa4299);">Submit Now</button>
                            </div>
                        </form>
                    </div>
                    <!-- //contact form -->
                    <div class="contact col-lg-5 mt-lg-0 mt-5">
                        <div class="cont-subs">
                            <h5>Contact Info</h5>
                            <p class="mt-3">Have any Queries? Let us know. We will clear it for you at the best.</p>
                            <div class="cont-add mt-4">
                                <div class="cont-add-lft">
                                    <span class="fa fa-map-marker" aria-hidden="true"></span>
                                </div>
                                <div class="cont-add-rgt">
                                    <h4>Office</h4>
                                    <p class="contact-text-sub">{{ $company_address }}</p>
                                </div>
                            </div>


                           <!-- <div class="cont-add mt-4">
                                <div class="cont-add-lft">
                                    <span class="fa fa-map-marker" aria-hidden="true"></span>
                                </div>
                                <div class="cont-add-rgt">
                                    <h4>Office 2</h4>
                                    <p class="contact-text-sub">{{ $company_address_two }}</p>
                                </div>
                            </div>-->

                            <div class="cont-add my-4">
                                <div class="cont-add-lft">
                                    <span class="fa fa-envelope" aria-hidden="true"></span>
                                </div>
                                <div class="cont-add-rgt">
                                    <h4>Email</h4>
                                    <a href="mailto:{{ $company_email }}">
                                        <p class="contact-text-sub">{{ $company_email }}</p>
                                    </a>
                                </div>
                            </div>
                            <div class="cont-add my-4">
                                <div class="cont-add-lft">
                                    <span class="fa fa-phone" aria-hidden="true"></span>
                                </div>
                                <div class="cont-add-rgt">
                                    <h4>Support Number</h4>
                                    <a href="tel:{{ $support_number }}">
                                        <p class="contact-text-sub">{{ $support_number }}</p>
                                    </a>
                                </div>
                            </div>

                            <div class="cont-add">
                                <div class="cont-add-lft">
                                    <span class="fa fa-phone" aria-hidden="true"></span>
                                </div>
                                <div class="cont-add-rgt">
                                    <h4>Whatsapp Number</h4>
                                    <a href="tel:{{ $whatsapp_number }}">
                                        <p class="contact-text-sub">{{ $whatsapp_number }}</p>
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <!---728x90--->

        </div>
    </section>
    <!-- //contact -->




@endsection
