@extends('admin.layout.header')
@section('content')
<script type="text/javascript">

    $(document).ready(function () {
        $("#other_id").select2();
        $("#fromdate").datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: ('yy-mm-dd'),
        });
        $("#todate").datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: ('yy-mm-dd'),
        });
    });


    function view_product(id) {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var dataString = 'id=' + id +  '&_token=' + token;
        $.ajax({
            type: "post",
            url: "{{url('admin/ecommerce/view-order-product')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    $(".name").text(msg.addressdetails.name);
                    $(".address").text(msg.addressdetails.address);
                    $(".city").text(msg.addressdetails.city);
                    $(".state_name").text(msg.addressdetails.state_name);
                    $(".district_name").text(msg.addressdetails.district_name);
                    $(".pin_code").text(msg.addressdetails.pin_code);
                    $(".mobile_number").text(msg.addressdetails.mobile_number);
                    $(".email").text(msg.addressdetails.email);

                    var productdetails = msg.productdetails;
                    var html = "";
                    for (var key in productdetails) {
                        html += "<tr>";
                        html += '<td> <div class="media"><div class="card-aside-img"><img src="' + productdetails[key].image + '" alt="img" class="h-60 w-60"></div><div class="media-body"> <div class="card-item-desc mt-0"> <h6 class="font-weight-semibold mt-0 text-uppercase">' + productdetails[key].category_name + '</h6> <dl class="card-item-desc-1"> <dt>Name: </dt> <dd>' + productdetails[key].product_name + '</dd> </dl> <dl class="card-item-desc-1"> <dt>Weight: </dt> <dd>' + productdetails[key].product_weight + '</dd> </dl> </div> </div> </div></td>';
                        html += '<td>' + productdetails[key].quantity + '</td>';
                        html += '<td>' + productdetails[key].product_price + '</td>';
                        html += '<td>' + productdetails[key].product_discount + '</td>';
                        html += '<td>' + productdetails[key].shipping_charge + '</td>';
                        html += '<td>' + productdetails[key].commission + '</td>';
                        html += '<td>' + productdetails[key].total_amount + '</td>';
                        html += '<td>' + productdetails[key].status + '</td>';
                        html += '<td><span class="badge badge-danger" onclick="track_order(' + productdetails[key].order_id + ')">Track Order</span></td>';
                        html += "</tr>";
                    }
                    $(".productdetails_list").html(html);
                    $("#view_product_details_model").modal('show');
                }else{
                    swal("Failed", msg.message, "error");
                }
            }
        });
    }


    function track_order(order_id) {
        var token = $("input[name=_token]").val();
        var dataString = 'order_id=' + order_id +  '&_token=' + token;
        $.ajax({
            type: "post",
            url: "{{url('admin/ecommerce/view-track-order')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    $("#track-order-label").show();
                    $("#progressbar-label").html(msg.orderdetails);
                }else{
                    swal("Failed", msg.message, "error");
                }
            }
        });
    }


</script>


<div class="main-content-body">

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="card">
                <div class="card-body">

                    <form action="{{url('admin/ecommerce/order-report')}}" method="get">
                        <div class="row">

                            <div class="col-lg-3 col-md-8 form-group mg-b-0">
                                <label class="form-label">From: <span class="tx-danger">*</span></label>
                                <input class="form-control fc-datepicker" value="{{ $fromdate }}" type="text" id="fromdate" name="fromdate" autocomplete="off">
                            </div>

                            <div class="col-lg-3 col-md-8 form-group mg-b-0">
                                <label class="form-label">To: <span class="tx-danger">*</span></label>
                                <input class="form-control fc-datepicker" value="{{ $todate }}" type="text" id="todate"  name="todate" autocomplete="off">
                            </div>

                            <div class="col-lg-3 col-md-8 form-group mg-b-0">
                                <label class="form-label">Status: <span class="tx-danger">*</span></label>
                                <select class="form-control select2" id="other_id" name="status_id" style="width: 100%;">
                                    <option value="0" @if($status_id == 0) selected @endif>All Order</option>
                                    @foreach($delivery_statuses as $value)
                                    <option value="{{ $value->id }}" @if($status_id == $value->id) selected @endif>{{ $value->name }}</option>
                                    @endforeach

                                </select>
                            </div>

                            <div class="col-lg-3 col-md-4 mg-t-10 mg-sm-t-25">
                                <button class="btn btn-primary pd-x-20" type="submit"><i class="fas fa-search"></i> Search</button>
                            </div>


                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-sm">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between">
                        <h4 class="card-title mg-b-2 mt-2">{{ $page_title }}</h4>
                        <i class="mdi mdi-dots-horizontal text-gray"></i>
                    </div>
                    <hr>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table text-md-nowrap" id="my_table">
                            <thead>
                            <tr>
                                <th class="wd-15p border-bottom-0">Tracking</th>
                                <th class="wd-15p border-bottom-0">Date</th>
                                <th class="wd-15p border-bottom-0">User</th>
                                <th class="wd-15p border-bottom-0">Mobile</th>
                                <th class="wd-15p border-bottom-0">Product</th>
                                <th class="wd-15p border-bottom-0">Amount</th>
                                <th class="wd-15p border-bottom-0">Discount</th>
                                <th class="wd-15p border-bottom-0">Shipping</th>
                                <th class="wd-15p border-bottom-0">Charges</th>
                                <th class="wd-15p border-bottom-0">Total</th>
                                <th class="wd-15p border-bottom-0">Status</th>
                                <th class="wd-15p border-bottom-0">Details</th>

                            </tr>
                            </thead>
                        </table>

                        <script type="text/javascript">
                            $(document).ready(function(){

                                // DataTable
                                var todate = $("#todate").val();
                                $('#my_table').DataTable({
                                    "order": [[ 1, "desc" ]],
                                    processing: true,
                                    serverSide: true,
                                    ajax: "{{ $urls }}",
                                    columns: [
                                        { data: 'sr_no' },
                                        { data: 'created_at' },
                                        { data: 'username' },
                                        { data: 'mobile_number' },
                                        { data: 'product' },
                                        { data: 'amount' },
                                        { data: 'total_discount' },
                                        { data: 'shipping_charges' },
                                        { data: 'total_commission' },
                                        { data: 'grand_total' },
                                        { data: 'status' },
                                        { data: 'action' },

                                    ]
                                });

                            });
                        </script>
                    </div>
                </div>
            </div>
        </div>
        <!--/div-->

    </div>

</div>
</div>
</div>


{{--view product model--}}
<div class="modal fade" id="view_product_details_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Product Details</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">


                {{--Tracking Order--}}
                <div style="display: none;" id="track-order-label">
                    <div class="tracking">
                        <div class="title">Tracking Order</div>
                    </div>
                    <div class="progress-track" id="progressbar-label"></div>
                    <hr>
                </div>
                {{--End Tracking Order--}}


                <div class="card">
                    <div class="task-stat pb-0">
                        <div class="d-flex tasks">
                            <div class="mb-0">
                                <div class="h6 fs-15 mb-0">Full Name: </div>
                            </div>
                            <span class="float-right ml-auto name"></span>
                        </div>

                        <div class="d-flex tasks">
                            <div class="mb-0">
                                <div class="h6 fs-15 mb-0">Mobile Number: </div>
                            </div>
                            <span class="float-right ml-auto mobile_number"></span>
                        </div>

                        <div class="d-flex tasks">
                            <div class="mb-0">
                                <div class="h6 fs-15 mb-0">Email Address: </div>
                            </div>
                            <span class="float-right ml-auto email"></span>
                        </div>

                        <div class="d-flex tasks">
                            <div class="mb-0">
                                <div class="h6 fs-15 mb-0">State: </div>
                            </div>
                            <span class="float-right ml-auto state_name"></span>
                        </div>

                        <div class="d-flex tasks">
                            <div class="mb-0">
                                <div class="h6 fs-15 mb-0">District: </div>
                            </div>
                            <span class="float-right ml-auto district_name"></span>
                        </div>

                        <div class="d-flex tasks">
                            <div class="mb-0">
                                <div class="h6 fs-15 mb-0">City: </div>
                            </div>
                            <span class="float-right ml-auto city"></span>
                        </div>

                        <div class="d-flex tasks">
                            <div class="mb-0">
                                <div class="h6 fs-15 mb-0">Pin Code: </div>
                            </div>
                            <span class="float-right ml-auto pin_code"></span>
                        </div>

                        <div class="d-flex tasks">
                            <div class="mb-0">
                                <div class="h6 fs-15 mb-0">Address: </div>
                            </div>
                            <span class="float-right ml-auto address"></span>
                        </div>




                    </div>
                </div>

                <div class="product-details table-responsive text-nowrap">
                    <table class="table table-bordered table-hover mb-0 text-nowrap">
                        <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Discount</th>
                            <th>Shipping</th>
                            <th>Service Charge</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Track</th>
                        </tr>
                        </thead>
                        <tbody class="productdetails_list">

                        </tbody>
                    </table>
                </div>





            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
{{--close view product model--}}

<style>
    .title {
        color: rgb(252, 103, 49);
        font-weight: 600;
        margin-bottom: 2vh;
        padding: 0 8%;
        font-size: initial
    }
    #progressbar {
        margin-bottom: 3vh;
        overflow: hidden;
        color: rgb(252, 103, 49);
        padding-left: 0px;
        margin-top: 3vh
    }

    #progressbar li {
        list-style-type: none;
        font-size: x-small;
        width: 25%;
        float: left;
        position: relative;
        font-weight: 400;
        color: rgb(160, 159, 159)
    }

    #progressbar #step1:before {
        content: "";
        color: rgb(252, 103, 49);
        width: 5px;
        height: 5px;
        margin-left: 0px !important
    }

    #progressbar #step2:before {
        content: "";
        color: #fff;
        width: 5px;
        height: 5px;
        margin-left: 32%
    }

    #progressbar #step3:before {
        content: "";
        color: #fff;
        width: 5px;
        height: 5px;
        margin-right: 32%
    }

    #progressbar #step4:before {
        content: "";
        color: #fff;
        width: 5px;
        height: 5px;
        margin-right: 0px !important
    }

    #progressbar li:before {
        line-height: 29px;
        display: block;
        font-size: 12px;
        background: #ddd;
        border-radius: 50%;
        margin: auto;
        z-index: -1;
        margin-bottom: 1vh
    }

    #progressbar li:after {
        content: '';
        height: 2px;
        background: #ddd;
        position: absolute;
        left: 0%;
        right: 0%;
        margin-bottom: 2vh;
        top: 1px;
        z-index: 1
    }

    .progress-track {
        padding: 0 8%
    }

    #progressbar li:nth-child(2):after {
        margin-right: auto
    }

    #progressbar li:nth-child(1):after {
        margin: auto
    }

    #progressbar li:nth-child(3):after {
        float: left;
        width: 68%
    }

    #progressbar li:nth-child(4):after {
        margin-left: auto;
        width: 132%
    }

    #progressbar li.active {
        color: black
    }

    #progressbar li.active:before,
    #progressbar li.active:after {
        background: rgb(15 191 53)
    }
</style>

@endsection