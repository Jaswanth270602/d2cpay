<script type="text/javascript">
    function view_members(id) {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var dataString = 'id=' + id +  '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('admin/view-members-details')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    $("#name").val(msg.details.name);
                    $("#last_name").val(msg.details.last_name);
                    $("#mobile").val(msg.details.mobile);
                    $("#email").val(msg.details.email);
                    $("#lock_amount").val(msg.details.lock_amount);
                    $("#shop_name").val(msg.details.shop_name);
                    $("#address").val(msg.details.address);
                    $("#city").val(msg.details.city);
                    $("#state_name").val(msg.details.state_name);
                    $("#district_name").val(msg.details.district_name);
                    $("#pin_code").val(msg.details.pin_code);
                    $("#office_address").val(msg.details.office_address);
                    $("#pan_number").val(msg.details.pan_number);
                    $("#gst_number").val(msg.details.gst_number);
                    $("#recharge").val(msg.details.recharge);
                    $("#money").val(msg.details.money);
                    $("#aeps").val(msg.details.aeps);
                    $("#payout").val(msg.details.payout);
                    $("#pancard").val(msg.details.pancard);
                    $("#ecommerce").val(msg.details.ecommerce);
                    $("#user_balance").val(msg.details.user_balance);
                    $("#aeps_balance").val(msg.details.aeps_balance);
                    $("#reason").val(msg.details.reason);
                    $("#login_otp").val(msg.details.login_otp);
                    $("#pan_username").val(msg.details.pan_username);
                    $("#pan_password").val(msg.details.pan_password);
                    $("#update_anchor_url").attr('href', msg.details.update_anchor_url);
                    $("#kyc_anchor_url").attr('href', msg.details.kyc_anchor_url);
                    $("#reset_password_anchor").attr('onclick', msg.details.reset_password_anchor);
                    $("#view_member_model").modal('show');
                }else{
                    swal("Failed", msg.message, "error");
                }
            }
        });
    }

    function reset_password(id) {
        $(".loader").show();
        $("#view_member_model").modal('hide');
        var token = $("input[name=_token]").val();
        var dataString = 'id=' + id +  '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('admin/view-members-details')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    $("#reset_name").val(msg.details.name + ' ' + msg.details.last_name);
                    $("#reset_user_id").val(msg.details.user_id);
                    $("#reset_password_model").modal('show');
                }else{
                    swal("Failed", msg.message, "error");
                }
            }
        });
    }

    function password_resent_now() {
        $("#pass_reset_btn").hide();
        $("#pass_reset_btn_loader").show();
        var token = $("input[name=_token]").val();
        var user_id = $("#reset_user_id").val();
        var admin_password = $("#reset_admin_password").val();
        var password = $("#reset_password").val();
        var dataString = 'user_id=' + user_id + '&admin_password=' + admin_password +  '&password=' + password + '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('admin/reset-password')}}",
            data: dataString,
            success: function (msg) {
                //$(".loader").hide();
                $("#pass_reset_btn").show();
                $("#pass_reset_btn_loader").hide();
                if (msg.status == 'success') {
                    swal("Success", msg.message, "success");
                    setTimeout(function () { location.reload(1); }, 3000);
                } else if(msg.status == 'validation_error'){
                    $("#reset_admin_password_errors").text(msg.errors.admin_password);
                    $("#reset_password_errors").text(msg.errors.password);
                }else{
                    swal("Failed", msg.message, "error");
                }
            }
        });
    }


    function download_member() {
        $("#download_btn").hide();
        $("#download_btn_loader").show();
        var token = $("input[name=_token]").val();
        var download_menu_name = $("#download_menu_name").val();
        var download_password = $("#download_password").val();
        var dataString = 'menu_name=' + download_menu_name + '&password=' + download_password +  '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('admin/download/v1/member-download')}}",
            data: dataString,
            success: function (msg) {
                $("#download_btn").show();
                $("#download_btn_loader").hide();
                if (msg.status == 'success') {
                    $("#download-label").show();
                    $("#download_link").attr('href', msg.download_link);
                } else if(msg.status == 'validation_error'){
                    $("#download_menu_name_errors").text(msg.errors.menu_name);
                    $("#download_password_errors").text(msg.errors.password);
                }else{
                    swal("Failed", msg.message, "error");
                }
            }
        });
    }
</script>

<div class="modal fade" id="view_member_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2" aria-hidden="true">
    <div class="modal-dialog modal-dialog-slideout" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="form-body">
                    <div class="row">

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" id="name" class="form-control" placeholder="Name" disabled>

                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Last Name</label>
                                <input type="text" id="last_name" class="form-control" placeholder="Last Name" disabled>

                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Mobile Number</label>
                                <input type="text" id="mobile" class="form-control" placeholder="Mobile Number" disabled>

                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Email Address</label>
                                <input type="text" id="email" class="form-control" placeholder="Email Address" disabled>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Lock Amount</label>
                                <input type="text" id="lock_amount" class="form-control" placeholder="Lock Amount" disabled>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Shop Name</label>
                                <input type="text" id="shop_name" class="form-control" placeholder="Shop Name" disabled>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Address</label>
                                <input type="text" id="address" class="form-control" placeholder="Address" disabled>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">City</label>
                                <input type="text" id="city" class="form-control" placeholder="City" disabled>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">State Name</label>
                                <input type="text" id="state_name" class="form-control" placeholder="State Name" disabled>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">District Name</label>
                                <input type="text" id="district_name" class="form-control" placeholder="District Name" disabled>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Pincode</label>
                                <input type="text" id="pin_code" class="form-control" placeholder="Pin Code" disabled>
                            </div>
                        </div>



                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Office Address</label>
                                <input type="text" id="office_address" class="form-control" placeholder="Office Address" disabled>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Pan Number</label>
                                <input type="text" id="pan_number" class="form-control" placeholder="Pan Number" disabled>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">GST Number</label>
                                <input type="text" id="gst_number" class="form-control" placeholder="GST Number" disabled>
                            </div>
                        </div>

                        @if(Auth::User()->company->recharge == 1 && Auth::User()->profile->recharge == 1)
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Recharge And Bill Payment</label>
                                    <select class="form-control" id="recharge" disabled>
                                        <option value="1">Active</option>
                                        <option value="0">De Active</option>
                                    </select>
                                </div>
                            </div>
                        @endif

                        @if(Auth::User()->company->money == 1 && Auth::User()->profile->money == 1)
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Money Transfer</label>
                                    <select class="form-control" id="money" disabled>
                                        <option value="1">Active</option>
                                        <option value="0">De Active</option>
                                    </select>
                                </div>
                            </div>
                        @endif


                        @if(Auth::User()->company->aeps == 1 && Auth::User()->profile->aeps == 1)
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Aeps</label>
                                    <select class="form-control" id="aeps" disabled>
                                        <option value="1">Active</option>
                                        <option value="0">De Active</option>
                                    </select>
                                </div>
                            </div>
                        @endif

                        @if(Auth::User()->company->payout == 1 && Auth::User()->profile->payout == 1)
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Payout</label>
                                    <select class="form-control" id="payout" disabled>
                                        <option value="1">Active</option>
                                        <option value="0">De Active</option>
                                    </select>
                                </div>
                            </div>
                        @endif

                        @if(Auth::User()->company->pancard == 1 && Auth::User()->profile->pancard == 1)
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Pancard</label>
                                    <select class="form-control" id="pancard" disabled>
                                        <option value="1">Active</option>
                                        <option value="0">De Active</option>
                                    </select>
                                </div>
                            </div>
                        @endif

                        @if(Auth::User()->company->ecommerce == 1 && Auth::User()->profile->ecommerce == 1)
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Ecommerce</label>
                                    <select class="form-control" id="ecommerce" disabled>
                                        <option value="1">Active</option>
                                        <option value="0">De Active</option>
                                    </select>
                                </div>
                            </div>
                        @endif

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Normal Balance</label>
                                <input type="text" id="user_balance" class="form-control" placeholder="Normal Balance" disabled>
                            </div>
                        </div>

                        @if(Auth::User()->company->aeps == 1 && Auth::User()->profile->aeps == 1)
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Aeps Balance</label>
                                <input type="text" id="aeps_balance" class="form-control" placeholder="Aeps Balance" disabled>
                            </div>
                        </div>
                        @endif

                        @if(Auth::User()->role_id == 1)
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Login OTP</label>
                                    <input type="text" id="login_otp" class="form-control" placeholder="Login OTP" disabled>
                                </div>
                            </div>
                            @endif

                        @if(Auth::User()->role_id == 1 && Auth::User()->company->pancard == 1)
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Pancard Username</label>
                                    <input type="text" id="pan_username" class="form-control" placeholder="Pancard Username" disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Pancard Password</label>
                                    <input type="text" id="pan_password" class="form-control" placeholder="Pancard Password" disabled>
                                </div>
                            </div>
                        @endif

                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="name">Remark</label>
                                <textarea class="form-control" id="reason" rows="4" readonly></textarea>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>


                @if(Auth::User()->role_id == 2 && $permission_reset_password == 1)
                    <button type="button" class="btn btn-info" id="reset_password_anchor"><i class="fas fa-unlock-alt"></i> Reset Password</button>
                @elseif(Auth::user()->role_id == 1)
                    <button type="button" class="btn btn-info" id="reset_password_anchor"><i class="fas fa-unlock-alt"></i> Reset Password</button>
                @endif


                @if(Auth::User()->role_id == 2 && $permission_update_member == 1)
                    <a href="" class="btn btn-success" id="update_anchor_url"><i class="fas fa-edit"></i> Edit</a>
                @elseif(Auth::user()->role_id != 2)
                    <a href="" class="btn btn-success" id="update_anchor_url"><i class="fas fa-edit"></i> Edit</a>
                @endif

                @if(Auth::User()->role_id == 2 && $permission_viewUser_kyc == 1)
                    <a href="" class="btn btn-danger" id="kyc_anchor_url"><i class="far fa-file"></i> Kyc</a>
                @elseif(Auth::user()->role_id != 2)
                    <a href="" class="btn btn-danger" id="kyc_anchor_url"><i class="far fa-file"></i> Kyc</a>
                @endif

            </div>
        </div>
    </div>
</div>


<div class="modal  show" id="reset_password_model"data-toggle="modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Reset Password</h6><button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="form-body">

                    <input type="hidden" id="reset_user_id">

                    <div class="row">

                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="name">User Name</label>
                                <input type="text" id="reset_name" class="form-control" placeholder="Your Name" disabled>

                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="name">Your Login Password</label>
                                <input type="password" id="reset_admin_password" class="form-control" placeholder="Login Password">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="reset_admin_password_errors"></li>
                                </ul>

                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="name">Password</label>
                                <input type="password" id="reset_password" class="form-control" placeholder="Password">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="reset_password_errors"></li>
                                </ul>

                            </div>
                        </div>

                    </div>

                </div>

                <div class="alert alert-outline-danger" role="alert">
                    <strong> Password will be send to customer register mobile number</strong>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn ripple btn-primary" type="button" id="pass_reset_btn" onclick="password_resent_now()">Reset Now</button>
                <button class="btn btn-primary" type="button"  id="pass_reset_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>



<style>
    .modal-dialog-slideout {min-height: 100%; margin: 0 0 0 auto;background: #fff;}
    .modal.fade .modal-dialog.modal-dialog-slideout {-webkit-transform: translate(100%,0)scale(1);transform: translate(100%,0)scale(1);}
    .modal.fade.show .modal-dialog.modal-dialog-slideout {-webkit-transform: translate(0,0);transform: translate(0,0);display: flex;align-items: stretch;-webkit-box-align: stretch;height: 100%;}
    .modal.fade.show .modal-dialog.modal-dialog-slideout .modal-body{overflow-y: auto;overflow-x: hidden;}
    .modal-dialog-slideout .modal-content{border: 0;}
    .modal-dialog-slideout .modal-header, .modal-dialog-slideout .modal-footer {height: 69px; display: block;}
    .modal-dialog-slideout .modal-header h5 {float:left;}
</style>




<div class="modal  show" id="member_download_model"data-toggle="modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Download Data</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="form-body">

                    <div class="row">

                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="name">Member Type</label>
                                <input type="text" id="download_menu_name" class="form-control" value="{{ $page_title }}" readonly>

                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="name">Your Login Password</label>
                                <input type="password" id="download_password" class="form-control" placeholder="Login Password">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="download_password_errors"></li>
                                </ul>

                            </div>
                        </div>


                    </div>

                </div>

                <div class="alert alert-outline-danger" role="alert" id="download-label" style="display: none;">
                    <strong> Download File :  <a href="" target="_blank" id="download_link">Click Here</a> </strong>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn ripple btn-primary" type="button" id="download_btn" onclick="download_member()">Verify And Download</button>
                <button class="btn btn-primary" type="button"  id="download_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>
