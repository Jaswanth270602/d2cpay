@extends('agent.layout.header')
@section('content')
    <script type="text/javascript">
        function generate_token_otp() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = '_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/developer/generate-token-otp')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if(msg.status){
                        $("#token_generate_otp_model").modal('show');
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }
        
        
        function token_generate_save() {
            $("#token_generate_btn").hide();
            $("#token_generate_btn_loader").show();
            var token = $("input[name=_token]").val();
            var otp = $("#token_generate_otp").val();
            var password = $("#token_generate_password").val();
            var dataString = 'otp=' + otp + '&password=' + password +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/developer/generate-token-save')}}",
                data: dataString,
                success: function (msg) {
                    $("#token_generate_btn").show();
                    $("#token_generate_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 2000);
                    } else if(msg.status == 'validation_error'){
                        $("#token_generate_otp_errors").text(msg.errors.otp);
                        $("#token_generate_password_errors").text(msg.errors.password);
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }
        
        function add_ipaddress_otp() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var ip_address = $("#ip_address").val();
            var dataString = 'ip_address=' + ip_address + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/developer/add-ipaddress-otp')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        $("#ip_address_otp_model").modal('show');
                    } else if(msg.status == 'validation_error'){
                        $("#ip_address_errors").text(msg.errors.ip_address);
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }
        
        function add_ipaddress_save() {
            $("#add_ipaddress_btn").hide();
            $("#add_ipaddress_btn_loader").show();
            var ip_address = $("#ip_address").val();
            var otp = $("#ip_address_otp").val();
            var password = $("#ip_address_password").val();
            var token = $("input[name=_token]").val();
            var dataString = 'ip_address=' + ip_address + '&otp=' + otp + '&password=' + password +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/developer/ip-address-save')}}",
                data: dataString,
                success: function (msg) {
                    $("#add_ipaddress_btn").show();
                    $("#add_ipaddress_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 2000);
                    } else if(msg.status == 'validation_error'){
                        $("#ip_address_errors").text(msg.errors.ip_address);
                        $("#ip_address_otp_errors").text(msg.errors.otp);
                        $("#ip_address_password_errors").text(msg.errors.password);
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }
        
        
        function update_call_back() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var call_back_url = $("#call_back_url").val();
            var dataString = 'call_back_url=' + encodeURIComponent(call_back_url) +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/developer/update-call-back-url')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 2000);
                    } else if(msg.status == 'validation_error'){
                        $("#call_back_url_errors").text(msg.errors.call_back_url);
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function removeIpAddressOtp (){
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = '_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/developer/remove-ip-address-otp')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if(msg.status){
                        $("#remove_ip_address_otp_model").modal('show');
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function removeIpAddressSave (){
            $("#removeIpAddressBtn").hide();
            $("#removeIpAddressBtn_loader").show();
            var otp = $("#removeIpAddressOTP").val();
            var password = $("#removeIpAddressPassword").val();
            var token = $("input[name=_token]").val();
            var dataString = 'otp=' + otp + '&password=' + password +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/developer/remove-ip-address-save')}}",
                data: dataString,
                success: function (msg) {
                    $("#removeIpAddressBtn").show();
                    $("#removeIpAddressBtn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 2000);
                    } else if(msg.status == 'validation_error'){
                        $("#removeIpAddressOTP_errors").text(msg.errors.otp);
                        $("#removeIpAddressPassword_errors").text(msg.errors.password);
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
         @include('agent.developer.left_side')
            <div class="col-lg-8 col-xl-9">
                <div class="card">
                    <div class="card-body">
                        <div class="mb-4 main-content-label">{{ $page_title }}</div>
                        <hr>
                        <div class="row">



                            <div class="col-12">
                                <div class="text-bold-600 font-medium-2 ">API Token</div>
                                <div class="input-group">

                                    <input type="text" class="form-control" id="api_token" name="api_token" value="{{Auth::User()->api_token }}" placeholder="Api Token" aria-describedby="button-addon2">
                                    <div class="input-group-append" id="button-addon2">
                                        <button class="btn btn-danger waves-effect waves-light gentok" type="button" onclick="generate_token_otp()">Generate Token </button>
                                    </div>

                                </div>
                                <p><small class="text-muted">Don't Share token with Anybody !</small></p>
                            </div>

                            
                            <div class="col-6">
                                <div class="form-group">
                                    <div class="text-bold-600 font-medium-2 ">IP Address</div> <p><small class="text-muted">Request accepted only from Following IP's !</small></p>
                                    <div>
                                        <table class="table" id="Ipaddress" style="width: 50%">

                                            <tbody>
                                            @if(Auth::User()->member->ip_address)
                                                <tr>
                                                    <td>{{ Auth::User()->member->ip_address }}</td>
                                                    <td><i class="fa fa-check tx-success mg-r-8 right"></i></td>
                                                    <td><button class="btn btn-danger btn-sm waves-effect waves-light gentok" type="button" onclick="removeIpAddressOtp()">Remove</button></td>
                                                </tr>
                                            @endif
                                            </tbody>
                                        </table>

                                    </div>
                                </div>
                            </div>
                            <div class="col-6"><div class="text-bold-600 font-medium-2 ">Add Ip</div>
                                <div class="input-group">
                                    <input type="text" id="ip_address" name="ip_address" class="form-control" aria-describedby="button-add" placeholder="Enter New IP Addresses">
                                    <div class="input-group-append" id="button-add">
                                        <button class="btn btn-danger waves-effect waves-light add_ip" type="button" onclick="add_ipaddress_otp()">Add IP Address</button>
                                    </div>
                                </div>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="ip_address_errors"></li>
                                </ul>
                                <p><small class="text-muted"> ie : 123.45.6.789 or dynamic </small></p>
                            </div>
                            <div class="col-12">
                                <div class="text-bold-600 font-medium-2 ">Callback Url
                                    <i class="step-icon feather icon-alert-circle" style="float: center;font-size: 18px;color: blue;" data-toggle="popover" data-html="true" data-placement="top" data-container="body" data-original-title="Supported Parameters" data-content="<b>[number]</b> - Recharge Number <span class='rr'></span> <br> <b>[amount] </b>- Recharge Amount <span class='rr'></span><br> <b>[status]</b> - Transaction Status <span class='rr'></span><br> <b>[opid]</b> - Operator TxnID <br> <b>[txnid]</b> - Transaction ID<br> <b>[client_id]</b> - Your TxnID <br> <b>[response_code]</b> - Response Code <br> <b>[response_msg]</b> - Response Message" aria-describedby="popover427601"></i>
                                </div><div class="input-group">
                                    <input type="text" class="form-control" id="call_back_url" name="call_back_url" value="{{Auth::User()->member->call_back_url }}" placeholder="Callback Url" aria-describedby="button-url">
                                    <div class="input-group-append" id="button-url">
                                        <button class="btn btn-danger waves-effect waves-light saveurl" type="button" onclick="update_call_back()">Save URL</button>
                                    </div>
                                </div>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="call_back_url_errors"></li>
                                </ul>

                                <p><small class="text-muted">Leave Empty to Cancel Callback ! ,  Url must start with http and should get HTTP 200 Response.</small></p>

                                <p><small class="text-muted">Sample Response : <br>https://www.yourdomain.com/callback?payid=1234&amp;client_id=2121&amp;operator_ref=1234567&amp;status=success/failure</small></p>
                            </div>

                        </div>
                    </div>

                </div>
            </div>



        </div>
        <!-- /row -->

        <!-- row -->



    </div>
    <!-- /row -->
    </div>
    <!-- /container -->
    </div>

@endsection