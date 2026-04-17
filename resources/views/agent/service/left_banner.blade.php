<div class="col-lg-8 col-md-12">
    <div class="card custom-card">
        <div class="card-body ht-100p">
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