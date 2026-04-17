@extends('agent.layout.header')
@section('content')
    <script type="text/javascript">
        $(document).ready(function () {
            $("#quantity").select2();
        });

        function buy_coupons() {
            var token = $("input[name=_token]").val();
            var username = $("#username").val();
            var quantity = $("#quantity").val();
            var transaction_pin = $("#transaction_pin").val();
            var latitude = $("#inputLatitude").val();
            var longitude = $("#inputLongitude").val();
            if (latitude && longitude){
                $(".loader").show();
                var dataString = 'username=' + username + '&quantity=' + quantity + '&transaction_pin=' + transaction_pin + '&latitude=' + latitude + '&longitude=' + longitude + '&_token=' + token;
                $.ajax({
                    type: "POST",
                    url: "{{url('agent/pancard/buy-coupons')}}",
                    data: dataString,
                    success: function (msg) {
                        $(".loader").hide();
                        if (msg.status == 'success') {
                            swal("Success", msg.message, "success");
                            setTimeout(function () {
                                location.reload(1);
                            }, 3000);
                        } else if (msg.status == 'validation_error') {
                            $("#username_errors").text(msg.errors.username);
                            $("#quantity_errors").text(msg.errors.quantity);
                            $("#transaction_pin_errors").text(msg.errors.transaction_pin);
                        } else {
                            swal("Failed", msg.message, "error");
                        }
                    }
                });
            }else{
                getLocation();
                alert('Please allow this site to access your location');
            }
        }
    </script>


    <div class="main-content-body">

        <div class="row">
            <div class="col-lg-5 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">{{ $page_title }}</h6>
                            <hr>
                        </div>


                        <div class="mb-4">
                            <label>UTI Username</label>
                            <input type="text" class="form-control" placeholder="UTI Username" id="username"
                                   value="{{ Auth::User()->pan_username }}">
                            <ul class="parsley-errors-list filled">
                                <li class="parsley-required" id="username_errors"></li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <label>Coupon Quantity</label>
                            <select class="form-control select2" id="quantity">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="10">10</option>
                                <option value="15">15</option>
                                <option value="20">20</option>
                                <option value="25">25</option>
                                <option value="30">30</option>
                            </select>
                            <ul class="parsley-errors-list filled">
                                <li class="parsley-required" id="quantity_errors"></li>
                            </ul>
                        </div>

                        @if(Auth::User()->company->transaction_pin == 1)
                            <div class="mb-4">
                                <label>Transaction Pin</label>
                                <input type="password" class="form-control" placeholder="Transaction Pin" id="transaction_pin">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="transaction_pin_errors"></li>
                                </ul>
                            </div>
                        @endif

                    </div>

                    <div class="modal-footer">
                        <button class="btn ripple btn-primary" type="button" onclick="buy_coupons()">Buy Now</button>
                        <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                    </div>
                </div>
            </div>


            <div class="col-lg-7 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Introduction</h6>
                            <hr>
                        </div>

                        <p>The fee for processing PAN application is ₹107 inclusive of GST</p>
                        <p>PAN card application can be processed using eKYC or physical documents</p>
                        <p>PSA Login <a href="https://www.psaonline.utiitsl.com/psaonline/" target="_blank">click here
                                <img src="https://cdn.bceres.com/front/img/uti.png"></a></p>


                    </div>


                </div>
            </div>

        </div>

        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Full Instructions</h6>
                            <hr>
                        </div>

                        <p>You may download any one of the following app (from google store) in your Smart Phone at your
                            own discretion. Apps are suggested but not endorsed by {{ $company_name }}.</p>

                        <ol type="1">
                            <li>Cam Scanner</li>
                            <li>Smart Docs Scanner</li>
                            <li>Convert JPG to PDF & Scanner</li>
                        </ol>
                        <p>To merge Two or More .PDF pages use following links</p>
                        <ol type="1">
                            <li><a href="https://www.pdfmerge.com" target="_blank">https://www.pdfmerge.com</a></li>
                            <li><a href="http://pdfjoiner.com" target="_blank">http://pdfjoiner.com</a></li>
                        </ol>

                        <p>Click the photo of following customer documents, using above app and send it to your own
                            email in .PDF format</p>
                        <ol type="1">
                            <li>PAN Application form, signed by customer with his photograph</li>
                            <li>ID proof</li>
                            <li>Address Proof</li>
                            <li>Date of Birth document</li>
                        </ol>

                        <p>Click photo of following documents in .JPEG/.JPG format with defined specification and send
                            it to your email address</p>
                        <ol type="1">
                            <li>Photo Scanning 300 dpi,Colour,213 X 213 px (Size:less than 30 kb)</li>
                            <li>Signature scanning 600 dpi black and white (Size:less than 60 kb)</li>
                        </ol>

                        <p>To convert the scanned image as per above specification you may use following links or any
                            software like photoshop etc or link as per your wish</p>
                        <ol type="1">
                            <li><a href="https://online-converting.com/image" target="_blank">https://online-converting.com/image</a>
                            </li>
                            <li><a href="https://www.imgonline.com.ua/eng/resize-image.php" target="_blank">https://www.imgonline.com.ua/eng/resize-image.php</a>
                            </li>
                        </ol>
                        <p>Save all of the above documents in your computer and Upload on UTIISL website</p>

                    </div>


                </div>
            </div>

        </div>

    </div>
    </div>
    </div>

@endsection