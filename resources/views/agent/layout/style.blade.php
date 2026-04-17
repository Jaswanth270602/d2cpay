<style>

    .horizontalMenucontainer .main-header.hor-header {
        position: fixed;
        background: linear-gradient(45deg, {{ $color_start}}, {{ $color_end}}) !important; }

    .main-content:after {
        content: "";
        height: 220px;
        background: linear-gradient(45deg, {{ $color_start}}, {{ $color_end}});
        position: absolute;
        z-index: -1;
        width: 100%;
        top: 0;
        left: 0; }

    .main-sidebar-body .nav-item:hover .nav-link {
        border-radius: 0 100px 100px 0;
        box-shadow: 0 6px 14px 2px rgba(0, 0, 0, 0.2);
        margin-right: 14px;
        color: #fff;
        background: linear-gradient(45deg, {{ $color_start}}, {{ $color_end}});
        box-shadow: 0 6px 14px 2px rgba(0, 0, 0, 0.2); }

    .main-sidebar-body .nav-item.active .nav-link {
        color: #fff;
        font-weight: 500;
        border-top: 0;
        background: linear-gradient(45deg, {{ $color_start}}, {{ $color_end}});
        border-radius: 0 6px 6px 0;
        box-shadow: 0 6px 14px 2px rgba(0, 0, 0, 0.2); }

    .main-sidebar-body .nav-item.active .nav-link {
        color: #fff;
        font-weight: 500;
        border-top: 0;
        background: linear-gradient(45deg, {{ $color_start}}, {{ $color_end}});
        border-radius: 0 6px 6px 0;
        box-shadow: 0 6px 14px 2px rgba(0, 0, 0, 0.2); }

    .sticky-pin .horizontalMenucontainer .main-header.hor-header {
        background: linear-gradient(45deg, {{ $color_start}}, {{ $color_end}}); }

</style>

<script type="text/javascript">
    $(document).ready(function () {
        getLocation();
    });

    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(showPosition);
        } else {
            x.innerHTML = "Geolocation is not supported by this browser.";
        }
    }

    function showPosition(position) {
        $("#inputLatitude").val(position.coords.latitude);
        $("#inputLongitude").val(position.coords.longitude);
    }

    function sendAadharOtp (){
        $("#aadharBtn").hide();
        $("#aadharBtn_loader").show();
        var token = $("input[name=_token]").val();
        var aadhaar_number = $("#aadhaar_number").val();
        var dataString = 'aadhaar_number=' + aadhaar_number + '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/aadhaar-verification/v1/send-otp')}}",
            data: dataString,
            success: function (msg) {
                $("#aadharBtn").show();
                $("#aadharBtn_loader").hide();
                if (msg.status == 'success') {
                    $("#aadhar_client_id").val(msg.client_id);
                    $("#aadhar_reference_id").val(msg.reference_id);
                    $(".aadhar-otp-label").show();
                    $("#aadharBtn").attr('onclick', 'confirmAadharOtp()');
                    $("#aadharBtn").text('Confirm OTP');
                }else{
                    swal("Faild", msg.message, "error");
                }
            }
        });
    }

    function confirmAadharOtp (){
        $("#aadharBtn").hide();
        $("#aadharBtn_loader").show();
        var token = $("input[name=_token]").val();
        var aadhaar_number = $("#aadhaar_number").val();
        var client_id = $("#aadhar_client_id").val();
        var reference_id = $("#aadhar_reference_id").val();
        var otp = $("#aadhaar_otp").val();
        var dataString = 'aadhar_aumber=' + aadhaar_number + '&client_id=' + client_id + '&reference_id=' + reference_id + '&otp=' + otp + '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/aadhaar-verification/v1/confirm-otp')}}",
            data: dataString,
            success: function (msg) {
                $("#aadharBtn").show();
                $("#aadharBtn_loader").hide();
                if (msg.status == 'success') {
                    swal("Success", msg.message, "success");
                    setTimeout(function () { location.reload(1); }, 3000);
                }else{
                    swal("Faild", msg.message, "error");
                }
            }
        });
    }

    function panVerify (){
        $("#panVerifyBtn").hide();
        $("#panVerifyBtn_loader").show();
        var token = $("input[name=_token]").val();
        var pan_number = $("#pancard_number").val();
        var dataString = 'pan_number=' + pan_number + '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/pan-verify/v1/verify-now')}}",
            data: dataString,
            success: function (msg) {
                $("#panVerifyBtn").show();
                $("#panVerifyBtn_loader").hide();
                if (msg.status == 'success') {
                    $("#panVerify_first_name").text(msg.data.name);
                    $(".panVerify_father_name").text(msg.data.father_name);
                    $(".panVerify_type").text(msg.data.type);
                    $(".panVerify_address").text(msg.data.address);
                    $(".pan-details-list").show();
                    $("#panVerifyBtn").hide();
                    $("#panVerifyBtn_loader").hide();
                }else{
                    swal("Faild", msg.message, "error");
                }
            }
        });
    }

</script>
<input type="hidden"  name="latitude" id="inputLatitude">
<input type="hidden"  name="longitude" id="inputLongitude">

<div class="modal  show" id="view-aadhar-kyc-model" data-toggle="modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Aadhaar Verification</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="form-body">

                    <input type="hidden" id="aadhar_client_id">
                    <input type="hidden" id="aadhar_reference_id">

                    <div class="row">

                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="name">Aadhaar Number</label>
                                <input type="text" id="aadhaar_number" class="form-control" placeholder="Aadhaar Number">
                            </div>
                        </div>


                        <div class="col-sm-12 aadhar-otp-label" style="display: none;">
                            <div class="form-group">
                                <label for="name">OTP</label>
                                <input type="password" id="aadhaar_otp" class="form-control" placeholder="Enter OTP">
                            </div>
                        </div>

                    </div>

                </div>


            </div>

            <div class="modal-footer">
                <button class="btn ripple btn-primary" type="button" id="aadharBtn" onclick="sendAadharOtp()">Submit</button>
                <button class="btn btn-primary" type="button"  id="aadharBtn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>


{{--pan number verification--}}


<div class="modal  show" id="view-pan-verify-model" data-toggle="modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Pan Number Verify</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="form-body">

                    <div class="row">

                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="name">Pan Number</label>
                                <input type="text" id="pancard_number" class="form-control" placeholder="Pan Number" value="{{ Auth::User()->member->pan_number }}">
                            </div>
                        </div>

                        <table class="table table-bordered pan-details-list" style="display: none;">
                            <tr>
                                <th>Name</th>
                                <td><span id="panVerify_first_name"></span></td>
                            </tr>
                            <tr>
                                <th>Father Name</th>
                                <td><span class="panVerify_father_name"></span></td>
                            </tr>

                            <tr>
                                <th>type</th>
                                <td><span class="panVerify_type"></span></td>
                            </tr>

                            <tr>
                                <th>Address</th>
                                <td><span class="panVerify_address"></span></td>
                            </tr>
                        </table>



                    </div>

                </div>


            </div>

            <div class="modal-footer">
                <button class="btn ripple btn-primary" type="button" id="panVerifyBtn" onclick="panVerify()">Submit</button>
                <button class="btn btn-primary" type="button"  id="panVerifyBtn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>