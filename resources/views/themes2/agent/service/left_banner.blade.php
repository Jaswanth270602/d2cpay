<div class="col-lg-7 layout-spacing">
    <div class="statbox widget box box-shadow mb-4">
        <div class="widget-header">
            <div class="row">
                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                    <h4>Service Banners</h4>
                </div>
            </div>
        </div>
        <hr>
        <div class="widget-content widget-content-area">
            <div>
                <div class="carousel slide" data-ride="carousel" id="carouselExample2">
                    <div class="carousel-inner">
                        @foreach($servicebanner as $key => $valye)
                            <div class="carousel-item {{ $key == 0 ? ' active' : '' }}">
                                <img alt="img" class="d-block w-100" src="{{$cdnLink}}{{ $valye->service_banner }}">
                            </div>
                        @endforeach
                    </div>
                    <a class="carousel-control-prev" href="#carouselExample2" role="button" data-slide="prev">
                        <i class="fa fa-angle-left fs-30" aria-hidden="true"></i>
                    </a>
                    <a class="carousel-control-next" href="#carouselExample2" role="button" data-slide="next">
                        <i class="fa fa-angle-right fs-30" aria-hidden="true"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>