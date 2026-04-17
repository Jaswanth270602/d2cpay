@extends('admin.layout.header')
@section('content')
    <script type="text/javascript">
        function saveBulkCommission (){
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var scheme_id = $("#scheme_id").val();
            var service_id = $("#service_id").val();
            var min_amount = $("#min_amount").val();
            var max_amount = $("#max_amount").val();
            var type = $("#type").val();
            var st = $("#st").val();
            var sd = $("#sd").val();
            var d = $("#d").val();
            var r = $("#r").val();
            var referral = $("#referral").val();
            var dataString = 'scheme_id=' + scheme_id + '&service_id=' + service_id + '&min_amount=' + min_amount + '&max_amount=' + max_amount + '&type=' + type + '&st=' + st + '&sd=' + sd +  '&d=' + d + '&r=' + r + '&referral=' + referral + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/store-bulk-commission')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#service_id_errors").text(msg.errors.service_id);
                        $("#min_amount_errors").text(msg.errors.min_amount);
                        $("#max_amount_errors").text(msg.errors.max_amount);
                    }else{
                        swal("Faild", msg.message, "error");
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
                        <input type="hidden" id="scheme_id" value="{{ $scheme_id }}">
                        <div class="row">
                            <div class="col-lg-2 col-md-8 form-group mg-b-0">
                                <label class="form-label">Select Service: <span class="tx-danger">*</span></label>
                                <select class="form-control" id="service_id">
                                    <option value="">Select Service</option>
                                    @foreach($services as $value)
                                        <option value="{{ $value->id }}">{{ $value->service_name }} </option>
                                    @endforeach
                                </select>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="service_id_errors"></li>
                                </ul>
                            </div>

                            <div class="col-lg-2 col-md-8 form-group mg-b-0">
                                <label class="form-label">Min Amount: <span class="tx-danger">*</span></label>
                                <input class="form-control" type="text" id="min_amount" placeholder="Min Amount" autocomplete="off">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="min_amount_errors"></li>
                                </ul>
                            </div>

                            <div class="col-lg-2 col-md-8 form-group mg-b-0">
                                <label class="form-label">Max Amount: <span class="tx-danger">*</span></label>
                                <input class="form-control" type="text" id="max_amount" placeholder="Max Amount" autocomplete="off">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="max_amount_errors"></li>
                                </ul>
                            </div>


                            <div class="col-lg-2 col-md-8 form-group mg-b-0">
                                <label class="form-label">Type: <span class="tx-danger">*</span></label>
                                <select class="form-control" id="type">
                                    <option value="0">%</option>
                                    <option value="1">Rs</option>
                                </select>
                            </div>

                            <div class="col-lg-2 col-md-8 form-group mg-b-0">
                                <label class="form-label">Sales Team: <span class="tx-danger">*</span></label>
                                <input class="form-control" type="text" id="st" value="0" autocomplete="off">
                            </div>

                            <div class="col-lg-2 col-md-8 form-group mg-b-0">
                                <label class="form-label">Super Distributor: <span class="tx-danger">*</span></label>
                                <input class="form-control" type="text" id="sd" value="0" autocomplete="off">
                            </div>

                            <div class="col-lg-2 col-md-8 form-group mg-b-0">
                                <label class="form-label">Distributor: <span class="tx-danger">*</span></label>
                                <input class="form-control" type="text" id="d" value="0" autocomplete="off">
                            </div>

                            <div class="col-lg-2 col-md-8 form-group mg-b-0">
                                <label class="form-label">Retailer: <span class="tx-danger">*</span></label>
                                <input class="form-control" type="text" id="r" value="0" autocomplete="off">
                            </div>

                            <div class="col-lg-2 col-md-8 form-group mg-b-0">
                                <label class="form-label">Referral: <span class="tx-danger">*</span></label>
                                <input class="form-control" type="text" id="referral" value="0" autocomplete="off">
                            </div>


                            <div class="col-lg-2 col-md-4 mg-t-10 mg-sm-t-25">
                                <button class="btn btn-main-primary pd-x-20" type="button" onclick="saveBulkCommission()">Save Now</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row row-sm">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">

                            <h4 class="card-title mg-b-2 mt-2">Package : {{ $scheme_name }}</h4>
                            <a href="{{url()->previous()}}">Back</a>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table text-md-nowrap" id="example1">
                                <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0">Provider id</th>
                                    <th class="wd-25p border-bottom-0">Provider Name</th>
                                    <th class="wd-25p border-bottom-0">Service</th>
                                    <th class="wd-25p border-bottom-0">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($providers as $value)
                                        <tr>
                                            <td>{{ $value->id }}</td>
                                            <td>{{ $value->provider_name }}</td>
                                            <td>{{ $value->service->service_name }}</td>
                                            <td>
                                                <form method="POST" action="{{ url('admin/set-operator-commission') }}" class="pull-right">
                                                    @csrf
                                                    <input type="hidden" name="scheme_id" value="{{ $scheme_id }}">
                                                    <input type="hidden" name="provider_id" value="{{ $value->id }}">
                                                    <button type="submit" class="btn btn-danger btn-sm">Update Commission</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!--/div-->

        </div>

    </div>
    </div>
    </div>


@endsection