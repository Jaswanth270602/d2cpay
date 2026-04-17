@extends('agent.layout.header')
@section('content')

    <script type="text/javascript">
        $(document).ready(function () {
            $("#state_id").select2();
            $("#district_id").select2();
        });
        
        function get_distric() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var state_id = $("#state_id").val();
            var dataString = 'state_id=' + state_id +  '&_token=' + token;
            $.ajax({
                type: "post",
                url: "{{url('agent/get-distric-by-state')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        var districts = msg.districts;
                        var html = "";
                        for (var key in districts) {
                            html += '<option value="' + districts[key].district_id + '">' + districts[key].district_name + ' </option>';
                        }
                        $("#district_id").html(html);

                    }else{
                        alert(msg.message);
                    }
                }
            });
        }
        
        function store_agent () {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var first_name = $("#first_name").val();
            var last_name = $("#last_name").val();
            var mobile_number = $("#mobile_number").val();
            var email = $("#email").val();
            var aadhar_number = $("#aadhar_number").val();
            var pan_number = $("#pan_number").val();
            var company = $("#company").val();
            var pin_code = $("#pin_code").val();
            var address = $("#address").val();
            var bank_account_number = $("#bank_account_number").val();
            var ifsc = $("#ifsc").val();
            var city = $("#city").val();
            var state_id = $("#state_id").val();
            var district_id = $("#district_id").val();
            var dataString = 'first_name=' + first_name +  '&last_name=' + last_name + '&mobile_number=' + mobile_number + '&email=' + email + '&aadhar_number=' + aadhar_number + '&pan_number=' + pan_number + '&company=' + company + '&pin_code=' + pin_code + '&address=' + address + '&bank_account_number=' + bank_account_number + '&ifsc=' + ifsc + '&city=' + city + '&state_id=' + state_id + '&district_id=' + district_id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/save-agent-onboarding')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#first_name_errors").text(msg.errors.first_name);
                        $("#last_name_errors").text(msg.errors.last_name);
                        $("#mobile_number_errors").text(msg.errors.mobile_number);
                        $("#email_errors").text(msg.errors.email);
                        $("#aadhar_number_errors").text(msg.errors.aadhar_number);
                        $("#pan_number_errors").text(msg.errors.pan_number);
                        $("#company_errors").text(msg.errors.company);
                        $("#pin_code_errors").text(msg.errors.pin_code);
                        $("#address_errors").text(msg.errors.address);
                        $("#bank_account_number_errors").text(msg.errors.bank_account_number);
                        $("#ifsc_errors").text(msg.errors.ifsc);
                        $("#city_errors").text(msg.errors.city);
                        $("#state_id_errors").text(msg.errors.state_id);
                        $("#district_id_errors").text(msg.errors.district_id);
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }
    </script>

    <!-- main-content-body -->
    <div class="main-content-body">

        <!-- row -->
        <div class="row row-sm">
        @include('agent.aeps.left_side')

        <!-- Col -->
            <div class="col-lg-8 col-xl-9">
                {{--prepaid commission--}}
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">{{ $page_title }}</h4>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div class="form-body">
                            <div class="row">

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">First Name</label>
                                        <input type="text" id="first_name" class="form-control" placeholder="First Name">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="first_name_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Last Name</label>
                                        <input type="text" id="last_name" class="form-control" placeholder="Last Name">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="last_name_errors"></li>
                                        </ul>
                                    </div>
                                </div>


                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Mobile Number</label>
                                        <input type="text" id="mobile_number" class="form-control" placeholder="Mobile Number" value="{{ Auth::User()->mobile }}" readonly>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="mobile_number_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Email Address</label>
                                        <input type="text" id="email" class="form-control" placeholder="Email Address">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="email_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Aadhar Number</label>
                                        <input type="text" id="aadhar_number" class="form-control" placeholder="Aadhar Number">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="aadhar_number_errors"></li>
                                        </ul>
                                    </div>
                                </div>


                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Pan Number</label>
                                        <input type="text" id="pan_number" class="form-control" placeholder="Pan Number">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="pan_number_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Shop Name</label>
                                        <input type="text" id="company" class="form-control" placeholder="Shop Name">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="company_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Pin Code</label>
                                        <input type="text" id="pin_code" class="form-control" placeholder="Pin Code">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="pin_code_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Address</label>
                                        <input type="text" id="address" class="form-control" placeholder="Address">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="address_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Account Number</label>
                                        <input type="text" id="bank_account_number" class="form-control" placeholder="Account Number">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="bank_account_number_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">IFSC Code</label>
                                        <input type="text" id="ifsc" class="form-control" placeholder="IFSC Code">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="ifsc_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">City</label>
                                        <input type="text" id="city" class="form-control" placeholder="City">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="city_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">State</label>
                                        <select class="form-control select2" id="state_id" onchange="get_distric(this)">
                                            <option value="">Select State</option>
                                            @foreach($states as $value)
                                                <option value="{{$value->id}}">{{ $value->name }}</option>
                                                @endforeach
                                        </select>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="state_id_errors"></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">District</label>
                                        <select class="form-control select2" id="district_id">
                                            <option value="">Select District</option>
                                        </select>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="district_id_errors"></li>
                                        </ul>
                                    </div>
                                </div>




                            </div>

                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-danger waves-effect waves-light" onclick="store_agent()">Save Details</button>
                    </div>
                </div>
                {{--clase prepaid commission--}}








            </div>
            <!-- /Col -->


        </div>
        <!-- /row -->

        <!-- row -->


    </div>
    <!-- /row -->
    </div>
    <!-- /container -->
    </div>
    <!-- /main-content -->





@endsection