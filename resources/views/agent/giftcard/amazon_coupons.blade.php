@extends('agent.layout.header')
@section('content')
    <script type="text/javascript">
        $(document).ready(function () {
            $("#provider_id").select2();
            $("#denomination").select2();
        });


        function purchase_now() {
            var qty = $("#qty").val();
            var r = confirm('Are you sure? you want to purchase '+ qty +' gift voucher');
            if (r == true) {
                $(".loader").show();
                var token = $("input[name=_token]").val();
                var mobile_number = $("#mobile_number").val();
                var email = $("#email").val();
                var denomination = $("#denomination").val();
                var provider_id = $("#provider_id").val();
                var dataString = 'mobile_number=' + mobile_number + '&email=' + email + '&denomination=' + denomination + '&provider_id=' + provider_id + '&qty=' + qty +  '&_token=' + token;
                $.ajax({
                    type: "POST",
                    url: "{{url('agent/giftcard/purchase-amazon-coupons')}}",
                    data: dataString,
                    success: function (msg) {
                        $(".loader").hide();
                        if (msg.status == 'success') {
                            var html = "";
                            var re = msg.coupon_code;
                            for (var key in re) {
                                html += '<div class="d-flex tasks"><div class="mb-0"><div class="h6 fs-15 mb-0">' + re[key].id + '</div></div><span class="float-right ml-auto">' + re[key].coupon_code + '</span><span class="float-right ml-auto">' + re[key].amount + '</span></div>';
                            }
                            $('#coupondetails').html(html);
                            $("#view-coupon-details-models").modal('show');
                        }else if(msg.status == 'pending'){
                            swal("Success", msg.message, "success");
                            setTimeout(function () { location.reload(1); }, 2000);
                        } else if(msg.status == 'validation_error'){
                            $("#mobile_number_errors").text(msg.errors.mobile_number);
                            $("#email_errors").text(msg.errors.email);
                            $("#denomination_errors").text(msg.errors.denomination);
                            $("#provider_id_errors").text(msg.errors.provider_id);
                            $("#qty_errors").text(msg.errors.qty);
                        }else{
                            swal("Failed", msg.message, "error");
                        }
                    }
                });
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

                        <ul class="parsley-errors-list filled">
                            <li class="parsley-required" id="mobile_number_errors"></li>
                            <li class="parsley-required" id="email_errors"></li>
                            <li class="parsley-required" id="denomination_errors"></li>
                            <li class="parsley-required" id="provider_id_errors"></li>
                            <li class="parsley-required" id="qty_errors"></li>
                        </ul>
                        @foreach($schema as $value)
                            <div class="form-group">
                                <div class="row row-sm">
                                    <div class="col-sm">
                                        <label>{{ $value->placeholder }}</label>
                                        @if($value->fieldType == 'TextInput')
                                            <input type="text" id="{{ $value->name }}" class="form-control" placeholder="{{ $value->placeholder }}">
                                        @elseif($value->fieldType == 'SelectList')
                                            @if($value->name == 'denomination')
                                                <select id="{{ $value->name }}" class="form-control select2">
                                                    @foreach($value->options as $myop)
                                                        <option value="{{ $myop->amount }}">{{ $myop->amount }}</option>
                                                    @endforeach
                                                </select>
                                            @elseif($value->name == 'provider_id')
                                                <select id="{{ $value->name }}" class="form-control select2">
                                                    @foreach($value->options as $myop)
                                                        <option value="{{ $myop->id }}">{{ $myop->provider_name }}</option>
                                                    @endforeach
                                                </select>
                                            @endif


                                        @endif
                                    </div><!-- col -->
                                </div><!-- row -->
                            </div><!-- form-group -->
                        @endforeach


                    </div>

                    <div class="modal-footer">
                        <button class="btn ripple btn-primary" type="button" onclick="purchase_now()">Purchase Now</button>
                        <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                    </div>
                </div>
            </div>





        </div>

    </div>
    </div>
    </div>



    <div class="modal  show" id="view-coupon-details-models"data-toggle="modal">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Coupon Details</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">

                    <div class="card">
                        <div class="task-stat pb-0">
                            <div class="d-flex tasks">
                                <div class="mb-0">
                                    <div class="h6 fs-15 mb-0">Id</div>
                                </div>
                                <span class="float-right ml-auto">Coupon Code</span>
                                <span class="float-right ml-auto">Amount</span>
                            </div>

                            <div id="coupondetails"></div>

                        </div>
                    </div>

                    <div class="alert alert-danger mg-b-0" role="alert">
                        <strong>Alert!</strong> make sure to copy all the coupon code before close the tab coz once you close the tab then you won't be able to see the coupon code
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection