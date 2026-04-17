@php
    $userbroadcasts = App\Models\Userbroadcast::where('company_id', Auth::User()->company_id)->first();
    $broadcastStatus = (empty($userbroadcasts) ? 2 : $userbroadcasts->status_id);
    $broadcastHeading = (empty($userbroadcasts) ? '' : $userbroadcasts->heading);
    $broadcastMessage = (empty($userbroadcasts) ? '' : $userbroadcasts->message);
    $broadcastImage_url = (empty($userbroadcasts) ? '' : $userbroadcasts->image_url);
    $broadcastImg_status = (empty($userbroadcasts) ? '' : $userbroadcasts->img_status);
@endphp

@if($broadcastStatus == 1)
    <script type="text/javascript">
        $(document).ready(function () {
            $("#user-broadcasts-model").modal('show');
        });
    </script>
@endif

{{--userbroadcasts notifications--}}

<div class="modal  show" id="user-broadcasts-model" data-toggle="modal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">{{ $broadcastHeading }}</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                            aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <!-- PRODUCT CAROUSEL -->
                <div class="col-xl-12 col-lg-6 col-md-6 col-sm-6 col-12 layout-spacing product-carousel">
                    @if($broadcastImg_status == 1 && !empty($broadcastImage_url))
                        <div class="product-carousel-inner">
                            <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
                                <div class="carousel-inner">
                                    <div class="carousel-item active">
                                        <img class="d-block w-100" src="{{ $cdnLink}}{{ $broadcastImage_url }}"
                                             alt="First slide">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                    @endif


                    <div class="widget card-body" style="margin-top: 1%;">
                        <p>{!! $broadcastMessage !!}</p>
                    </div>
                </div>
                <!-- PRODUCT CAROUSEL ENDS-->

            </div>
        </div>
    </div>
</div>

{{--userbroadcasts notifications close--}}