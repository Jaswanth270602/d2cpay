@extends('admin.layout.header')
@section('content')

<script type="text/javascript">
    $(document).ready(function () {
        $('#user_id').select2({
            dropdownParent: $('#add_new_agent_model')
        });
    });

    function userDetails() {
        $("#add_btn").hide();
        $("#add_btn_loader").show();
        var token = $("input[name=_token]").val();
        var user_id = $("#user_id").val();
        var dataString = 'user_id=' + user_id + '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('admin/agent-onboarding-user-details')}}",
            data: dataString,
            success: function (msg) {
                $("#add_btn").show();
                $("#add_btn_loader").hide();
                if (msg.status == 'success') {
                    $("#first_name").val(msg.details.first_name);
                    $("#last_name").val(msg.details.last_name);
                    $("#mobile_number").val(msg.details.mobile_number);
                    $("#email").val(msg.details.email);
                    $("#aadhar_number").val(msg.details.aadhar_number);
                    $("#pan_number").val(msg.details.pan_number);
                    $("#company").val(msg.details.company);
                    $("#pin_code").val(msg.details.pin_code);
                    $("#address").val(msg.details.address);
                    $("#bank_account_number").val(msg.details.bank_account_number);
                    $("#ifsc").val(msg.details.ifsc);
                    $("#state_id").val(msg.details.state_id);
                    $("#district_id").val(msg.details.district_id);
                    $("#city").val(msg.details.city);
                }else{
                    swal("Failed", msg.message, "error");
                }
            }
        });
    }

        function save_agent_onboarding() {
            $("#add_btn").hide();
            $("#add_btn_loader").show();
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
            var state_id = $("#state_id").val();
            var district_id = $("#district_id").val();
            var city = $("#city").val();
            var user_id = $("#user_id").val();
            var dataString = 'first_name=' + first_name + '&last_name=' + last_name + '&mobile_number=' + mobile_number + '&email=' + email + '&aadhar_number=' + aadhar_number + '&pan_number=' + pan_number + '&company=' + company + '&pin_code=' + pin_code + '&address=' + address + '&bank_account_number=' + bank_account_number + '&ifsc=' + ifsc + '&state_id=' + state_id + '&district_id=' + district_id + '&city=' + city + '&user_id=' + user_id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/save-agent-onboarding')}}",
                data: dataString,
                success: function (msg) {
                    $("#add_btn").show();
                    $("#add_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#user_id_errors").text(msg.errors.user_id);
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
                        $("#state_id_errors").text(msg.errors.state_id);
                        $("#district_id_errors").text(msg.errors.district_id);
                        $("#city_errors").text(msg.errors.city);
                    }else{
                        swal("Faild", msg.message, "error");
                    }
                }
            });
        }


        function view_details(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/view-agent-onboarding')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        $("#view_id").val(msg.details.id);
                        $("#view_first_name").val(msg.details.first_name);
                        $("#view_last_name").val(msg.details.last_name);
                        $("#view_mobile_number").val(msg.details.mobile_number);
                        $("#view_email").val(msg.details.email);
                        $("#view_aadhar_number").val(msg.details.aadhar_number);
                        $("#view_pan_number").val(msg.details.pan_number);
                        $("#view_company").val(msg.details.company);
                        $("#view_pin_code").val(msg.details.pin_code);
                        $("#view_address").val(msg.details.address);
                        $("#view_bank_account_number").val(msg.details.bank_account_number);
                        $("#view_ifsc").val(msg.details.ifsc);
                        $("#view_state_id").val(msg.details.state_id);
                        $("#view_district_id").val(msg.details.district_id);
                        $("#view_city").val(msg.details.city);
                        $("#view_user_id").val(msg.details.user_id);
                        $("#view_company_id").val(msg.details.company_id);
                        $("#view_status_id").val(msg.details.status_id);
                        $("#view_onboarding_model").modal('show');
                    }else{
                        swal("Faild", msg.message, "error");
                    }
                }
            });
        }

        function update_agent_onboarding() {
            $("#update_btn").hide();
            $("#update_btn_loader").show();
            var token = $("input[name=_token]").val();
            var id = $("#view_id").val();
            var first_name = $("#view_first_name").val();
            var last_name = $("#view_last_name").val();
            var mobile_number = $("#view_mobile_number").val();
            var email = $("#view_email").val();
            var aadhar_number = $("#view_aadhar_number").val();
            var pan_number = $("#view_pan_number").val();
            var company = $("#view_company").val();
            var pin_code = $("#view_pin_code").val();
            var address = $("#view_address").val();
            var bank_account_number = $("#view_bank_account_number").val();
            var ifsc = $("#view_ifsc").val();
            var state_id = $("#view_state_id").val();
            var district_id = $("#view_district_id").val();
            var city = $("#view_city").val();
            var user_id = $("#view_user_id").val();
            var dataString = 'id=' + id + '&first_name=' + first_name + '&last_name=' + last_name + '&mobile_number=' + mobile_number + '&email=' + email + '&aadhar_number=' + aadhar_number + '&pan_number=' + pan_number + '&company=' + company + '&pin_code=' + pin_code + '&address=' + address + '&bank_account_number=' + bank_account_number + '&ifsc=' + ifsc + '&state_id=' + state_id + '&district_id=' + district_id + '&city=' + city + '&user_id=' + user_id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/update-agent-onboarding')}}",
                data: dataString,
                success: function (msg) {
                    $("#update_btn").show();
                    $("#update_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#view_user_id_errors").text(msg.errors.user_id);
                        $("#view_first_name_errors").text(msg.errors.first_name);
                        $("#view_last_name_errors").text(msg.errors.last_name);
                        $("#view_mobile_number_errors").text(msg.errors.mobile_number);
                        $("#view_email_errors").text(msg.errors.email);
                        $("#view_aadhar_number_errors").text(msg.errors.aadhar_number);
                        $("#view_pan_number_errors").text(msg.errors.pan_number);
                        $("#view_company_errors").text(msg.errors.company);
                        $("#view_pin_code_errors").text(msg.errors.pin_code);
                        $("#view_address_errors").text(msg.errors.address);
                        $("#view_bank_account_number_errors").text(msg.errors.bank_account_number);
                        $("#view_ifsc_errors").text(msg.errors.ifsc);
                        $("#view_state_id_errors").text(msg.errors.state_id);
                        $("#view_district_id_errors").text(msg.errors.district_id);
                        $("#view_city_errors").text(msg.errors.city);
                    }else{
                        swal("Faild", msg.message, "error");
                    }
                }
            });
        }

    function download_agent() {
        $("#download_btn").hide();
        $("#download_btn_loader").show();
        var token = $("input[name=_token]").val();
        var download_password = $("#download_password").val();
        var dataString = 'password=' + download_password +  '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('admin/download/agent-onboarding-download')}}",
            data: dataString,
            success: function (msg) {
                $("#download_btn").show();
                $("#download_btn_loader").hide();
                if (msg.status == 'success') {
                    $("#download-label").show();
                    $("#download_link").attr('href', msg.download_link);
                } else if(msg.status == 'validation_error'){
                    $("#download_password_errors").text(msg.errors.password);
                }else{
                    swal("Failed", msg.message, "error");
                }
            }
        });
    }

</script>



<div class="main-content-body">




    <div class="row row-sm">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between">
                        <h4 class="card-title mg-b-2 mt-2">{{ $page_title }}</h4>
                        <button class="btn btn-primary btn-sm" type="button"  data-toggle="modal" data-target="#agent_download_model"> Download Agent List</button>
                        <button class="btn btn-danger btn-sm" data-target="#add_new_agent_model" data-toggle="modal">Add New</button>
                        <i class="mdi mdi-dots-horizontal text-gray"></i>
                    </div>
                    <hr>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="@if(Auth::User()->company->table_format == 1)table text-md-nowrap @else display responsive nowrap @endif" id="my_table">
                            <thead>
                            <tr>
                                <th class="wd-15p border-bottom-0">ID</th>
                                <th class="wd-15p border-bottom-0">Date Time</th>
                                <th class="wd-15p border-bottom-0">User Name</th>
                                <th class="wd-15p border-bottom-0">First Name</th>
                                <th class="wd-15p border-bottom-0">Last Name</th>
                                <th class="wd-15p border-bottom-0">Mobile Number</th>
                                <th class="wd-15p border-bottom-0">Email</th>
                                <th class="wd-15p border-bottom-0">Aadhar Number</th>
                                <th class="wd-15p border-bottom-0">Pan Number</th>
                                <th class="wd-15p border-bottom-0">Shop Name</th>
                                <th class="wd-15p border-bottom-0">Pin Code</th>
                                <th class="wd-15p border-bottom-0">Address</th>
                                <th class="wd-15p border-bottom-0">Account Number</th>
                                <th class="wd-15p border-bottom-0">IFSC Code</th>
                                <th class="wd-15p border-bottom-0">State Name</th>
                                <th class="wd-15p border-bottom-0">District Name</th>
                                <th class="wd-15p border-bottom-0">City</th>
                                <th class="wd-15p border-bottom-0">Status</th>
                                <th class="wd-15p border-bottom-0">Action</th>

                            </tr>
                            </thead>
                        </table>

                        <script type="text/javascript">
                            $(document).ready(function(){
                                $('#my_table').DataTable({
                                    "order": [[ 1, "desc" ]],
                                    processing: true,
                                    serverSide: true,
                                    ajax: "{{ $urls }}",
                                    columns: [
                                        { data: 'id' },
                                        { data: 'created_at' },
                                        { data: 'user' },
                                        { data: 'first_name' },
                                        { data: 'last_name' },
                                        { data: 'mobile_number' },
                                        { data: 'email' },
                                        { data: 'aadhar_number' },
                                        { data: 'pan_number' },
                                        { data: 'company' },
                                        { data: 'pin_code' },
                                        { data: 'address' },
                                        { data: 'bank_account_number' },
                                        { data: 'ifsc' },
                                        { data: 'state_name' },
                                        { data: 'district_name' },
                                        { data: 'city' },
                                        { data: 'status' },
                                        { data: 'view' },
                                    ]
                                });
                                $("input[type='search']").wrap("<form>");
                                $("input[type='search']").closest("form").attr("autocomplete","off");
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


<div class="modal  show" id="add_new_agent_model"data-toggle="modal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title"><i class="fas fa-plus-circle"></i> Add New Agent</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="form-body">
                    <div class="row">

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="name">Select User</label>
                                <select class="form-control select2" id="user_id" style="width: 100%" onchange="userDetails(this)">
                                    <option value="">Select User</option>
                                    @foreach($users as $value)
                                        <option value="{{ $value->id }}">{{ $value->name }}  {{ $value->last_name }}</option>
                                    @endforeach
                                </select>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="user_id_errors"></li>
                                </ul>

                            </div>
                        </div>



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
                                <input type="text" id="mobile_number" class="form-control" placeholder="Mobile Number">
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
                                <label for="name">Pincode</label>
                                <input type="text" id="pin_code" class="form-control" placeholder="Pincode">
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
                                <label for="name">State</label>
                                 <select class="form-control" id="state_id">
                                    @foreach($states as $value)
                                        <option value="{{ $value->id }}">{{ $value->name }}</option>
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
                                <select class="form-control" id="district_id">
                                    @foreach($districts as $value)
                                        <option value="{{ $value->id }}">{{ $value->district_name }}</option>
                                    @endforeach
                                </select>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="district_id_errors"></li>
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




                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button class="btn ripple btn-primary" type="button" id="add_btn" onclick="save_agent_onboarding()">Add Now</button>
                <button class="btn btn-primary" type="button"  id="add_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

    {{--view onboard model--}}
<div class="modal  show" id="view_onboarding_model"data-toggle="modal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title"><i class="fas fa-plus-circle"></i> Update Agent</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="form-body">
                    <div class="row">

                        <input type="hidden" id="view_id">

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="name">Select User</label>
                                <select class="form-control" id="view_user_id">
                                    <option value="">Select User</option>
                                    @foreach($users as $value)
                                        <option value="{{ $value->id }}">{{ $value->name }}  {{ $value->last_name }}</option>
                                    @endforeach
                                </select>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="view_user_id_errors"></li>
                                </ul>

                            </div>
                        </div>



                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="name">First Name</label>
                                <input type="text" id="view_first_name" class="form-control" placeholder="First Name">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="view_first_name_errors"></li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="name">Last Name</label>
                                <input type="text" id="view_last_name" class="form-control" placeholder="Last Name">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="view_last_name_errors"></li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="name">Mobile Number</label>
                                <input type="text" id="view_mobile_number" class="form-control" placeholder="Mobile Number">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="view_mobile_number_errors"></li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="name">Email Address</label>
                                <input type="text" id="view_email" class="form-control" placeholder="Email Address">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="view_email_errors"></li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="name">Aadhar Number</label>
                                <input type="text" id="view_aadhar_number" class="form-control" placeholder="Aadhar Number">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="view_aadhar_number_errors"></li>
                                </ul>
                            </div>
                        </div>


                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="name">Pan Number</label>
                                <input type="text" id="view_pan_number" class="form-control" placeholder="Pan Number">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="view_pan_number_errors"></li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="name">Shop Name</label>
                                <input type="text" id="view_company" class="form-control" placeholder="Shop Name">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="view_company_errors"></li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="name">Pincode</label>
                                <input type="text" id="view_pin_code" class="form-control" placeholder="Pincode">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="view_pin_code_errors"></li>
                                </ul>
                            </div>
                        </div>


                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="name">Address</label>
                                <input type="text" id="view_address" class="form-control" placeholder="Address">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="view_address_errors"></li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="name">Account Number</label>
                                <input type="text" id="view_bank_account_number" class="form-control" placeholder="Account Number">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="view_bank_account_number_errors"></li>
                                </ul>
                            </div>
                        </div>


                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="name">IFSC Code</label>
                                <input type="text" id="view_ifsc" class="form-control" placeholder="IFSC Code">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="view_ifsc_errors"></li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="name">State</label>
                                <select class="form-control" id="view_state_id">
                                    @foreach($states as $value)
                                        <option value="{{ $value->id }}">{{ $value->name }}</option>
                                    @endforeach
                                </select>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="view_state_id_errors"></li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="name">District</label>
                                <select class="form-control" id="view_district_id">
                                    @foreach($districts as $value)
                                        <option value="{{ $value->id }}">{{ $value->district_name }}</option>
                                    @endforeach
                                </select>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="view_district_id_errors"></li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="name">City</label>
                                <input type="text" id="view_city" class="form-control" placeholder="City">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="view_city_errors"></li>
                                </ul>
                            </div>
                        </div>




                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button class="btn ripple btn-primary" type="button" id="update_btn" onclick="update_agent_onboarding()">Update Now</button>
                <button class="btn btn-primary" type="button"  id="update_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

    {{--download --}}
<div class="modal  show" id="agent_download_model"data-toggle="modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Download Agent List</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="form-body">

                    <div class="row">


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
                <button class="btn ripple btn-primary" type="button" id="download_btn" onclick="download_agent()">Verify And Download</button>
                <button class="btn btn-primary" type="button"  id="download_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection