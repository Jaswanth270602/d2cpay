@extends('themes2.admin.layout.header')
@section('content')

    <script type="text/javascript">

        /* $( document ).ready(function() {
             $.ajax({
                 url: "{{url('admin/get-api-balance')}}",
            success: function(msg){
                if (msg.status == 'success'){
                    var balance = msg.balance;
                    for (var key in balance) {
                        $("#checkbalance_"+ balance[key].id).text(balance[key].balance);
                    }
                }

            }});
    });*/

        function get_api_balance(api_id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'api_id=' + api_id +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/get-api-balance')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        $("#checkbalance_"+ msg.balance.api_id).text(msg.balance.balance);
                        document.getElementById("checkbalance_"+ msg.balance.api_id).style.color = 'green';
                        document.getElementById("checkbalance_"+ msg.balance.api_id).style.fontWeight = 'bold';
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function create_new_api() {
            $("#create_btn").hide();
            $("#create_btn_loader").show();
            var token = $("input[name=_token]").val();
            var api_name = $("#api_name").val();
            var method = $("#method").val();
            var response_type = $("#response_type").val();
            var base_url = $("#base_url").val();
            var dataString = 'api_name=' + api_name + '&method=' + method + '&response_type=' + response_type + '&base_url=' + encodeURIComponent(base_url) +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/create-new-api')}}",
                data: dataString,
                success: function (msg) {
                    $("#create_btn").show();
                    $("#create_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#api_name_errors").text(msg.errors.api_name);
                        $("#method_errors").text(msg.errors.method);
                        $("#response_type_errors").text(msg.errors.response_type);
                        $("#base_url_errors").text(msg.errors.base_url);
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function view_apis(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/view-api-details')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        $("#view_id").val(msg.details.id);
                        $("#view_api_name").val(msg.details.api_name);
                        $("#view_base_url").val(msg.details.base_url);
                        $("#view_method").val(msg.details.method);
                        $("#view_response_type").val(msg.details.response_type);
                        $("#view_support_number").val(msg.details.support_number);
                        $("#view_speed_status").val(msg.details.speed_status);
                        $("#view_speed_limit").val(msg.details.speed_limit);
                        $("#view_api_update_model").modal('show');
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function update_api() {
            $("#update_btn").hide();
            $("#update_btn_loader").show();
            var token = $("input[name=_token]").val();
            var id = $("#view_id").val();
            var api_name = $("#view_api_name").val();
            var base_url = $("#view_base_url").val();
            var method = $("#view_method").val();
            var response_type = $("#view_response_type").val();
            var support_number = $("#view_support_number").val();
            var speed_status = $("#view_speed_status").val();
            var speed_limit = $("#view_speed_limit").val();
            var dataString = 'api_name=' + api_name + '&method=' + method + '&response_type=' + response_type + '&base_url=' + encodeURIComponent(base_url) +  '&id=' + id + '&support_number=' + support_number + '&speed_status=' + speed_status + '&speed_limit=' + speed_limit + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/update-api-details')}}",
                data: dataString,
                success: function (msg) {
                    $("#update_btn").show();
                    $("#update_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#view_api_name_errors").text(msg.errors.api_name);
                        $("#view_method_errors").text(msg.errors.method);
                        $("#view_response_type_errors").text(msg.errors.response_type);
                        $("#view_base_url_errors").text(msg.errors.base_url);
                        $("#view_support_number_errors").text(msg.errors.support_number);
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function check_balance_view(api_id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'api_id=' + api_id +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/view-check-balance-api')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        $("#check_balance_id").val(msg.details.id);
                        $("#check_balance_base_url").val(msg.details.base_url);
                        $("#check_balance_method").val(msg.details.method);
                        $("#check_balance_response_type").val(msg.details.response_type);
                        $("#check_balance_status_type").val(msg.details.status_type);
                        $("#check_balance_status_parameter").val(msg.details.status_parameter);
                        $("#check_balance_status_value").val(msg.details.status_value);
                        $("#check_balance_balance_parameter").val(msg.details.balance_parameter);
                        $("#check_balance_status_id").val(msg.details.status_id);
                        $("#check_balance_under_value").val(msg.details.under_value);
                        $("#view_check_balance_api_model").modal('show');
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function update_check_balance_api() {
            $("#check_balance_update_btn").hide();
            $("#check_balance_update_btn_loader").show();
            var token = $("input[name=_token]").val();
            var id = $("#check_balance_id").val();
            var base_url = $("#check_balance_base_url").val();
            var method = $("#check_balance_method").val();
            var response_type = $("#check_balance_response_type").val();
            var status_parameter = $("#check_balance_status_parameter").val();
            var status_value = $("#check_balance_status_value").val();
            var balance_parameter = $("#check_balance_balance_parameter").val();
            var status_id = $("#check_balance_status_id").val();
            var status_type = $("#check_balance_status_type").val();
            var under_value = $("#check_balance_under_value").val();
            var dataString = 'id=' + id + '&base_url=' + encodeURIComponent(base_url) + '&method=' + method + '&response_type=' + response_type + '&status_parameter=' + status_parameter + '&status_value=' + status_value + '&balance_parameter=' + balance_parameter + '&status_id=' + status_id + '&status_type=' + status_type + '&under_value=' + under_value + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/update-check-balance-api')}}",
                data: dataString,
                success: function (msg) {
                    $("#check_balance_update_btn").show();
                    $("#check_balance_update_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#check_balance_id_errors").text(msg.errors.id);
                        $("#check_balance_base_url_errors").text(msg.errors.base_url);
                        $("#check_balance_method_errors").text(msg.errors.method);
                        $("#check_balance_response_type_errors").text(msg.errors.response_type);
                        $("#check_balance_status_parameter_errors").text(msg.errors.status_parameter);
                        $("#check_balance_status_value_errors").text(msg.errors.status_value);
                        $("#check_balance_balance_parameter_errors").text(msg.errors.balance_parameter);
                        $("#check_balance_status_id_errors").text(msg.errors.status_id);
                        $("#check_balance_status_type_errors").text(msg.errors.status_type);
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

    </script>




    <!--  Content Area Starts  -->
    <div id="content" class="main-content">

        <!-- Main Body Starts -->
    @include('themes2.admin.layout.breadcrumb')
    <!-- Main Body Starts -->
        <div class="layout-px-spacing">
            <div class="row layout-top-spacing">
                <!-- REVENUE ENDS-->



                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
                    <div class="widget dashboard-table">
                        <div class="widget-heading">
                            <h5 class=""> {{ $page_title }}</h5>
                            @if(Auth::User()->role_id == 2 && $permission_add_api == 1)
                                <button class="btn btn-danger btn-sm" data-target="#add_new_api_model" data-toggle="modal">Add New Api</button>
                            @elseif(Auth::User()->role_id == 1)
                                <button class="btn btn-danger btn-sm" data-target="#add_new_api_model" data-toggle="modal">Add New Api</button>
                            @endif
                        </div>
                        <hr>
                        <div class="widget-content">
                            <div class="table-responsive">
                                <table class="@if(Auth::User()->company->table_format == 1)table text-md-nowrap @else display responsive nowrap @endif" id="example1">
                                    <thead>
                                    <tr>
                                        <th class="wd-15p border-bottom-0">Api Id</th>
                                        <th class="wd-25p border-bottom-0">Api name</th>
                                        <th class="wd-25p border-bottom-0">Support</th>
                                        <th class="wd-25p border-bottom-0">Balance</th>
                                        <th class="wd-25p border-bottom-0">Setting</th>
                                        <th class="wd-25p border-bottom-0">Callback</th>
                                        <th class="wd-25p border-bottom-0">Response</th>
                                        <th class="wd-25p border-bottom-0">Action</th>
                                        <th class="wd-25p border-bottom-0">Check Balance</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($api as $value)
                                        <tr>
                                            <td>{{ $value->id }}</td>
                                            <td>{{ $value->api_name }}</td>
                                            <td>{{ $value->support_number }}</td>
                                            <td><a href="#" onclick="get_api_balance({{ $value->id }})"><span id="checkbalance_{{ $value->id }}" style="color: blue;">View Balance</span></a> </td>
                                            <td><a href="{{url('admin/view-api-provider')}}/{{ $value->id }}" style="color: blue;">Add Provider</a> </td>
                                            <td><a href="{{url('admin/webhook-setting')}}/{{ $value->id }}" style="color: blue;">Webhook Setting</a> </td>
                                            <td><a href="{{url('admin/response-setting')}}/{{ $value->id }}" style="color: blue;">Add Response</a> </td>

                                            <td>
                                                @if(Auth::User()->role_id == 2 && $permission_update_api == 1)
                                                    <button class="btn btn-danger btn-sm" onclick="view_apis({{ $value->id }})">Update</button>
                                                @elseif(Auth::User()->role_id == 1)
                                                    <button class="btn btn-danger btn-sm" onclick="view_apis({{ $value->id }})">Update</button>
                                                @endif

                                            </td>
                                            <td><button class="btn btn-primary btn-sm" onclick="check_balance_view({{ $value->id }})">Balance Setting</button></td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!-- Main Body Ends -->

    {{--Start Add Api Model--}}
        <div class="modal  show" id="add_new_api_model"data-toggle="modal">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content modal-content-demo">
                    <div class="modal-header">
                        <h6 class="modal-title"><i class="fas fa-plus-circle"></i> Add New Api</h6>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-body">
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Api Name</label>
                                        <input type="text" id="api_name" class="form-control" placeholder="Api Name">
                                        <span class="invalid-feedback d-block" id="api_name_errors"></span>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Api Method</label>
                                        <select class="form-control" id="method">
                                            <option value="1">Get</option>
                                        </select>
                                        <span class="invalid-feedback d-block" id="method_errors"></span>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Response Type</label>
                                        <select class="form-control" id="response_type">
                                            <option value="1">Json</option>
                                            <option value="2">XML</option>
                                        </select>
                                        <span class="invalid-feedback d-block" id="response_type_errors"></span>

                                    </div>
                                </div>



                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="name">Api URL</label>
                                        <textarea type="text" id="base_url" class="form-control" placeholder="Api URL" rows="4"></textarea>
                                        <span class="invalid-feedback d-block" id="base_url_errors"></span>
                                    </div>
                                </div>


                                <table class="table table-bordered mb-0">
                                    <thead>
                                    <tr>
                                        <th class="wd-40p">Attribute</th>
                                        <th class="wd-60p">Value</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>number</td>
                                        <td>[number]</td>
                                    </tr>

                                    <tr>
                                        <td>amount</td>
                                        <td>[amount]</td>
                                    </tr>

                                    <tr>
                                        <td>operator_code</td>
                                        <td>[opcode]</td>
                                    </tr>

                                    <tr>
                                        <td>uniq_id</td>
                                        <td>[txnid]</td>
                                    </tr>

                                    <tr>
                                        <td>optional1</td>
                                        <td>[optional1]</td>
                                    </tr>

                                    <tr>
                                        <td>optional2</td>
                                        <td>[optional2]</td>
                                    </tr>
                                    </tbody>
                                </table>

                            </div>



                        </div>
                    </div>

                    <pre><code>Api URL Exmple : {{url('')}}/api/telecom/v1/payment?api_token=xyz&number=[number]&amount=[amount]&provider_id=[opcode]&client_id=[txnid]&optional1=[optional1]&optional2=[optional2]</code></pre>

                    <div class="modal-footer">
                        <button class="btn ripple btn-primary" type="button" id="create_btn" onclick="create_new_api()">Create Now</button>
                        <button class="btn btn-primary" type="button"  id="create_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                        <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                    </div>
                </div>
            </div>
        </div>
    {{--End Add Api Model--}}

    {{--Start Update Api model--}}
        <div class="modal  show" id="view_api_update_model"data-toggle="modal">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content modal-content-demo">
                    <div class="modal-header">
                        <h6 class="modal-title"> Update Api</h6>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                    </div>

                    <input type="hidden" id="view_id">
                    <div class="modal-body">
                        <div class="form-body">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Api Name</label>
                                        <input type="text" id="view_api_name" class="form-control" placeholder="Api Name">
                                        <span class="invalid-feedback d-block" id="view_api_name_errors"></span>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Support Number</label>
                                        <input type="text" id="view_support_number" class="form-control" placeholder="Support Number">
                                        <span class="invalid-feedback d-block" id="view_support_number_errors"></span>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Api Method</label>
                                        <select class="form-control" id="view_method">
                                            <option value="1">Get</option>
                                        </select>
                                        <span class="invalid-feedback d-block" id="view_method_errors"></span>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Response Type</label>
                                        <select class="form-control" id="view_response_type">
                                            <option value="1">Json</option>
                                            <option value="2">XML</option>
                                        </select>
                                        <span class="invalid-feedback d-block" id="view_response_type_errors"></span>

                                    </div>
                                </div>



                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="name">Api URL</label>
                                        <textarea type="text" id="view_base_url" class="form-control" placeholder="Api URL" rows="4"></textarea>
                                        <span class="invalid-feedback d-block" id="view_base_url_errors"></span>
                                    </div>
                                </div>


                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Speed Status</label>
                                        <select class="form-control" id="view_speed_status">
                                            <option value="0">Disable</option>
                                            <option value="1">Enable</option>
                                        </select>
                                        <span class="invalid-feedback d-block" id="view_speed_status_errors"></span>
                                    </div>
                                </div>


                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Speed Limit</label>
                                        <input type="number" class="form-control" id="view_speed_limit" placeholder="Speed Limit">
                                        <span class="invalid-feedback d-block" id="view_speed_limit_errors"></span>
                                    </div>
                                </div>


                                <table class="table main-table-reference mt-0 mb-0">
                                    <thead>
                                    <tr>
                                        <th class="wd-40p">Attribute</th>
                                        <th class="wd-60p">Value</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>number</td>
                                        <td>[number]</td>
                                    </tr>

                                    <tr>
                                        <td>amount</td>
                                        <td>[amount]</td>
                                    </tr>

                                    <tr>
                                        <td>operator_code</td>
                                        <td>[opcode]</td>
                                    </tr>

                                    <tr>
                                        <td>uniq_id</td>
                                        <td>[txnid]</td>
                                    </tr>

                                    <tr>
                                        <td>optional1</td>
                                        <td>[optional1]</td>
                                    </tr>

                                    <tr>
                                        <td>optional2</td>
                                        <td>[optional2]</td>
                                    </tr>
                                    </tbody>
                                </table>

                            </div>



                        </div>
                    </div>

                    <pre><code>Api URL Exmple : {{url('')}}/api/telecom/v1/payment?api_token=xyz&number=[number]&amount=[amount]&provider_id=[opcode]&client_id=[txnid]&optional1=[optional1]&optional2=[optional2]</code></pre>

                    <div class="modal-footer">
                        <button class="btn ripple btn-primary" type="button" id="update_btn" onclick="update_api()">Update Now</button>
                        <button class="btn btn-primary" type="button"  id="update_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                        <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                    </div>
                </div>
            </div>
        </div>
    {{--End Update Api model--}}


    {{--Start Check Balance Api Settings--}}
        <div class="modal  show" id="view_check_balance_api_model" data-toggle="modal">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content modal-content-demo">
                    <div class="modal-header">
                        <h6 class="modal-title"> Check Balance Api Setting</h6>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                    </div>

                    <input type="hidden" id="check_balance_id">
                    <div class="modal-body">
                        <div class="form-body">
                            <div class="row">


                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Api Method</label>
                                        <select class="form-control" id="check_balance_method">
                                            <option value="1">Get</option>
                                        </select>
                                        <span class="invalid-feedback d-block" id="check_balance_method_errors"></span>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Response Type</label>
                                        <select class="form-control" id="check_balance_response_type">
                                            <option value="1">Json</option>
                                            <option value="2">XML</option>
                                        </select>
                                        <span class="invalid-feedback d-block" id="check_balance_response_type_errors"></span>

                                    </div>
                                </div>



                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="name">Api URL</label>
                                        <textarea type="text" id="check_balance_base_url" class="form-control" placeholder="Api URL" rows="4"></textarea>
                                        <span class="invalid-feedback d-block" id="check_balance_base_url_errors"></span>
                                    </div>
                                </div>



                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Status Available</label>
                                        <select class="form-control" id="check_balance_status_type">
                                            <option value="1">Yes</option>
                                            <option value="0">No</option>
                                        </select>
                                        <span class="invalid-feedback d-block" id="check_balance_status_type_errors"></span>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Status Parameter Name</label>
                                        <input type="text" class="form-control" id="check_balance_status_parameter" placeholder="Status Parameter">
                                        <span class="invalid-feedback d-block" id="check_balance_status_parameter_errors"></span>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Success Value</label>
                                        <input type="text" class="form-control" id="check_balance_status_value" placeholder="Status Value">
                                        <span class="invalid-feedback d-block" id="check_balance_status_value_errors"></span>
                                    </div>
                                </div>


                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Balance Parameter Name</label>
                                        <input type="text" class="form-control" id="check_balance_balance_parameter" placeholder="Status Parameter">
                                        <span class="invalid-feedback d-block" id="check_balance_balance_parameter_errors"></span>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Status</label>
                                        <select class="form-control" id="check_balance_status_id">
                                            <option value="1">Enable</option>
                                            <option value="0">Disable</option>
                                        </select>
                                        <span class="invalid-feedback d-block" id="check_balance_status_id_errors"></span>
                                    </div>
                                </div>

                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="name">Under value</label>
                                        <input type="text" class="form-control" id="check_balance_under_value" placeholder="Under value">
                                        <span class="invalid-feedback d-block" id="check_balance_under_value_errors"></span>
                                        <span class="invalid-feedback d-block">If the data is inside a parameter</span>
                                    </div>
                                </div>

                            </div>



                        </div>
                    </div>


                    <div class="modal-footer">
                        <button class="btn ripple btn-primary" type="button" id="check_balance_update_btn" onclick="update_check_balance_api()">Update Now</button>
                        <button class="btn btn-primary" type="button"  id="check_balance_update_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                        <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                    </div>
                </div>
            </div>
        </div>
    {{--End Check Balance Api Settings--}}


@endsection