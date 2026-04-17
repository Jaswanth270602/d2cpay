@extends('admin.layout.header')
@section('content')
    <script type="text/javascript">
        function view_whitelabe_details(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/view-white-label-details')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        $("#view_company_name").val(msg.details.company_name);
                        $("#view_company_email").val(msg.details.company_email);
                        $("#view_company_address").val(msg.details.company_address);
                        $("#view_company_address_two").val(msg.details.company_address_two);
                        $("#view_support_number").val(msg.details.support_number);
                        $("#view_whatsapp_number").val(msg.details.whatsapp_number);
                        $("#view_company_website").val(msg.details.company_website);
                        $("#view_news").val(msg.details.news);
                        $("#view_update_one").val(msg.details.update_one);
                        $("#view_update_two").val(msg.details.update_two);
                        $("#view_update_three").val(msg.details.update_three);
                        $("#view_sender_id").val(msg.details.sender_id);
                        $("#view_recharge").val(msg.details.recharge);
                        $("#view_money").val(msg.details.money);
                        $("#view_aeps").val(msg.details.aeps);
                        $("#view_payout").val(msg.details.payout);
                        $("#view_view_plan").val(msg.details.view_plan);
                        $("#view_pancard").val(msg.details.pancard);
                        $("#view_ecommerce").val(msg.details.ecommerce);
                        $("#view_status_id").val(msg.details.status_id);
                        $("#view_whitelabel_model").modal('show');
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }
        


        function create_whitelabel() {
            //$(".loader").show();
            $("#create_btn").hide();
            $("#create_btn_loader").show();
            var token = $("input[name=_token]").val();
            var company_name = $("#company_name").val();
            var company_email = $("#company_email").val();
            var company_address = $("#company_address").val();
            var support_number = $("#support_number").val();
            var whatsapp_number = $("#whatsapp_number").val();
            var company_website = $("#company_website").val();
            var recharge = $("#recharge").val();
            var money = $("#money").val();
            var aeps = $("#aeps").val();
            var payout = $("#payout").val();
            var view_plan = $("#view_plan").val();
            var pancard = $("#pancard").val();
            var ecommerce = $("#ecommerce").val();
            var user_id = $("#user_id").val();
            var sender_id = $("#sender_id").val();
            var dataString = 'company_name=' + company_name + '&company_email=' + company_email + '&company_address=' + company_address + '&support_number=' + support_number + '&whatsapp_number=' + whatsapp_number + '&company_website=' + company_website + '&recharge=' + recharge + '&money=' + money + '&aeps=' + aeps + '&payout=' + payout + '&view_plan=' + view_plan + '&pancard=' + pancard + '&ecommerce=' + ecommerce + '&user_id=' + user_id + '&sender_id=' + sender_id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/create-white-label')}}",
                data: dataString,
                success: function (msg) {
                    //$(".loader").hide();
                    $("#create_btn").show();
                    $("#create_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#company_name_errors").text(msg.errors.company_name);
                        $("#company_email_errors").text(msg.errors.company_email);
                        $("#company_address_errors").text(msg.errors.company_address);
                        $("#support_number_errors").text(msg.errors.support_number);
                        $("#whatsapp_number_errors").text(msg.errors.whatsapp_number);
                        $("#company_website_errors").text(msg.errors.company_website);
                        $("#user_id_errors").text(msg.errors.user_id);
                        $("#sender_id_errors").text(msg.errors.sender_id);
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });

        }
        
        function update_view_details(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/view-white-label-details')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        $("#update_company_id").val(msg.details.company_id);
                        $("#update_company_name").val(msg.details.company_name);
                        $("#update_company_email").val(msg.details.company_email);
                        $("#update_company_address").val(msg.details.company_address);
                        $("#update_support_number").val(msg.details.support_number);
                        $("#update_whatsapp_number").val(msg.details.whatsapp_number);
                        $("#update_company_website").val(msg.details.company_website);
                        $("#update_sender_id").val(msg.details.sender_id);
                        $("#update_recharge").val(msg.details.recharge);
                        $("#update_money").val(msg.details.money);
                        $("#update_aeps").val(msg.details.aeps);
                        $("#update_payout").val(msg.details.payout);
                        $("#update_view_plan").val(msg.details.view_plan);
                        $("#update_pancard").val(msg.details.pancard);
                        $("#update_ecommerce").val(msg.details.ecommerce);
                        $("#update_user_id").val(msg.details.user_id);
                        $("#update_status_id").val(msg.details.status_id);
                        $("#view_update_whitelabel_model").modal('show');
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }
        
        
        function update_white_label() {
           // $(".loader").show();
            $("#update_btn").hide();
            $("#update_btn_loader").show();
            var token = $("input[name=_token]").val();
            var company_id = $("#update_company_id").val();
            var company_name = $("#update_company_name").val();
            var company_email = $("#update_company_email").val();
            var company_address = $("#update_company_address").val();
            var support_number = $("#update_support_number").val();
            var whatsapp_number = $("#update_whatsapp_number").val();
            var company_website = $("#update_company_website").val();
            var recharge = $("#update_recharge").val();
            var money = $("#update_money").val();
            var aeps = $("#update_aeps").val();
            var payout = $("#update_payout").val();
            var view_plan = $("#update_view_plan").val();
            var pancard = $("#update_pancard").val();
            var ecommerce = $("#update_ecommerce").val();
            var user_id = $("#update_user_id").val();
            var status_id = $("#update_status_id").val();
            var sender_id = $("#update_sender_id").val();
            var dataString = 'company_id=' + company_id + '&company_name=' + company_name + '&company_email=' + company_email + '&company_address=' + company_address + '&support_number=' + support_number + '&whatsapp_number=' + whatsapp_number + '&company_website=' + company_website + '&recharge=' + recharge + '&money=' + money + '&aeps=' + aeps + '&payout=' + payout + '&view_plan=' + view_plan + '&pancard=' + pancard + '&ecommerce=' + ecommerce + '&user_id=' + user_id + '&status_id=' + status_id + '&sender_id=' + sender_id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/update-white-label')}}",
                data: dataString,
                success: function (msg) {
                  //  $(".loader").hide();
                    $("#update_btn").show();
                    $("#update_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#update_company_name_errors").text(msg.errors.company_name);
                        $("#update_company_email_errors").text(msg.errors.company_email);
                        $("#update_company_address_errors").text(msg.errors.company_address);
                        $("#update_support_number_errors").text(msg.errors.support_number);
                        $("#update_whatsapp_number_errors").text(msg.errors.whatsapp_number);
                        $("#update_company_website_errors").text(msg.errors.company_website);
                        $("#update_user_id_errors").text(msg.errors.user_id);
                        $("#update_sender_id_errors").text(msg.errors.sender_id);
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
                            <h4 class="card-title mg-b-2 mt-2">White Label</h4>
                            <button class="btn btn-danger btn-sm" data-target="#create_whitelabel_model" data-toggle="modal"><i class="fas fa-plus-circle"></i> Create New Whiate Label</button>

                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table text-md-nowrap" id="example1">
                                <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0">Id</th>
                                    <th class="wd-15p border-bottom-0">Date</th>
                                    <th class="wd-15p border-bottom-0">User</th>
                                    <th class="wd-15p border-bottom-0">User Type</th>
                                    <th class="wd-10p border-bottom-0">Website</th>
                                    <th class="wd-15p border-bottom-0">Name</th>
                                    <th class="wd-20p border-bottom-0">Email</th>
                                    <th class="wd-15p border-bottom-0">Number</th>
                                    <th class="wd-15p border-bottom-0">Parent</th>
                                    <th class="wd-15p border-bottom-0">Status</th>
                                    <th class="wd-15p border-bottom-0">Action</th>

                                </tr>
                                </thead>
                                <tbody>
                                @foreach($company as $value)
                                    <tr>
                                        <td> <a href="#" onclick="view_whitelabe_details({{ $value->id }})"> <i class="fas fa-eye"></i> {{ $value->id }} </a></td>
                                        <td>{{ $value->created_at }}</td>
                                        <td>{{ $value->user->name }}</td>
                                        <td>{{ $value->user->role->role_title }}</td>
                                        <td>{{ $value->company_website }}</td>
                                        <td>{{ $value->company_name }}</td>
                                        <td>{{ $value->company_email}}</td>
                                        <td>{{ $value->support_number}}</td>
                                        <td>@if($value->parent_id == 0)No Parent @else {{ \App\User::find($value->parent_id)->name }}  @endif</td>
                                        <td>@if($value->status_id == 1) <span class="badge badge-success">Active</span>  @else <span class="badge badge-danger">De Active</span> @endif</td>
                                        <td><button class="btn btn-success btn-sm" onclick="update_view_details({{ $value->id }})"><i class="typcn typcn-edit"></i> Edit</button></td>
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

    <div class="modal  show" id="create_whitelabel_model"data-toggle="modal">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title"><i class="fas fa-plus-circle"></i> Create New White Label</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Company Name</label>
                                    <input type="text" id="company_name" class="form-control" placeholder="Company Name">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="company_name_errors"></li>
                                    </ul>

                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Company Email </label>
                                    <input type="text" id="company_email" class="form-control" placeholder="Company Email">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="company_email_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Company Address </label>
                                    <input type="text" id="company_address" class="form-control" placeholder="Company Address">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="company_address_errors"></li>
                                    </ul>
                                </div>
                            </div>


                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Support Number</label>
                                    <input type="text" id="support_number" class="form-control" placeholder="Support Number">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="support_number_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Whatsapp Number</label>
                                    <input type="text" id="whatsapp_number" class="form-control" placeholder="Whatsapp Number">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="whatsapp_number_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Company Website</label>
                                    <input type="text" id="company_website" class="form-control" placeholder="Company Website">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="company_website_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Sender Id</label>
                                    <input type="text" id="sender_id" class="form-control" placeholder="6 Digit Sender Id">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="sender_id_errors"></li>
                                    </ul>
                                </div>
                            </div>



                            @if(Auth::User()->company->recharge == 1)
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Recharge Service</label>
                                        <select class="form-control" id="recharge">
                                            <option value="1">Active</option>
                                            <option value="0">De Active</option>

                                        </select>
                                    </div>
                                </div>
                            @endif

                            @if(Auth::User()->company->money == 1)
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Money Service</label>
                                        <select class="form-control" id="money">
                                            <option value="1">Active</option>
                                            <option value="0">De Active</option>

                                        </select>
                                    </div>
                                </div>
                            @endif

                            @if(Auth::User()->company->aeps == 1)
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Aeps Service</label>
                                        <select class="form-control" id="aeps">
                                            <option value="1">Active</option>
                                            <option value="0">De Active</option>

                                        </select>
                                    </div>
                                </div>
                            @endif


                            @if(Auth::User()->company->payout == 1)
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Payout</label>
                                        <select class="form-control" id="payout">
                                            <option value="1">Active</option>
                                            <option value="0">De Active</option>

                                        </select>
                                    </div>
                                </div>
                            @endif

                            @if(Auth::User()->company->view_plan == 1)
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">View Plan</label>
                                        <select class="form-control" id="view_plan">
                                            <option value="1">Active</option>
                                            <option value="0">De Active</option>

                                        </select>
                                    </div>
                                </div>
                            @endif

                            @if(Auth::User()->company->pancard == 1)
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Pancard</label>
                                        <select class="form-control" id="pancard">
                                            <option value="1">Active</option>
                                            <option value="0">De Active</option>

                                        </select>
                                    </div>
                                </div>
                            @endif

                            @if(Auth::User()->company->ecommerce == 1)
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Ecommerce</label>
                                        <select class="form-control" id="ecommerce">
                                            <option value="1">Active</option>
                                            <option value="0">De Active</option>

                                        </select>
                                    </div>
                                </div>
                            @endif

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">User</label>
                                    <select class="form-control" id="user_id">
                                        <option value="">Select User</option>
                                        @foreach($users as $value)
                                        <option value="{{ $value->id }}">{{ $value->name }} ({{ $value->role->role_title }})</option>
                                            @endforeach
                                    </select>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="user_id_errors"></li>
                                    </ul>
                                </div>
                            </div>



                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn ripple btn-primary" type="button" id="create_btn" onclick="create_whitelabel()">Create Now</button>
                    <button class="btn btn-primary" type="button"  id="create_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                    <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
    </div>




    {{--view update whitelabel modal--}}
    <div class="modal  show" id="view_update_whitelabel_model"data-toggle="modal">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Update White Label</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">

                        <input type="hidden" id="update_company_id">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Company Name</label>
                                    <input type="text" id="update_company_name" class="form-control" placeholder="Company Name">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="update_company_name_errors"></li>
                                    </ul>

                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Company Email </label>
                                    <input type="text" id="update_company_email" class="form-control" placeholder="Company Email">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="update_company_email_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Company Address </label>
                                    <input type="text" id="update_company_address" class="form-control" placeholder="Company Address">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="update_company_address_errors"></li>
                                    </ul>
                                </div>
                            </div>


                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Support Number</label>
                                    <input type="text" id="update_support_number" class="form-control" placeholder="Support Number">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="update_support_number_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Whatsapp Number</label>
                                    <input type="text" id="update_whatsapp_number" class="form-control" placeholder="Whatsapp Number">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="update_whatsapp_number_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Company Website</label>
                                    <input type="text" id="update_company_website" class="form-control" placeholder="Company Website">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="update_company_website_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Sender Id</label>
                                    <input type="text" id="update_sender_id" class="form-control" placeholder="6 Digit Sender Id">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="update_sender_id_errors"></li>
                                    </ul>
                                </div>
                            </div>



                            @if(Auth::User()->company->recharge == 1)
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Recharge Service</label>
                                        <select class="form-control" id="update_recharge">
                                            <option value="1">Active</option>
                                            <option value="0">De Active</option>

                                        </select>
                                    </div>
                                </div>
                            @endif

                            @if(Auth::User()->company->money == 1)
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Money Service</label>
                                        <select class="form-control" id="update_money">
                                            <option value="1">Active</option>
                                            <option value="0">De Active</option>

                                        </select>
                                    </div>
                                </div>
                            @endif

                            @if(Auth::User()->company->aeps == 1)
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Aeps Service</label>
                                        <select class="form-control" id="update_aeps">
                                            <option value="1">Active</option>
                                            <option value="0">De Active</option>

                                        </select>
                                    </div>
                                </div>
                            @endif


                            @if(Auth::User()->company->payout == 1)
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Payout</label>
                                        <select class="form-control" id="update_payout">
                                            <option value="1">Active</option>
                                            <option value="0">De Active</option>

                                        </select>
                                    </div>
                                </div>
                            @endif

                            @if(Auth::User()->company->view_plan == 1)
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">View Plan</label>
                                        <select class="form-control" id="update_view_plan">
                                            <option value="1">Active</option>
                                            <option value="0">De Active</option>

                                        </select>
                                    </div>
                                </div>
                            @endif

                            @if(Auth::User()->company->pancard == 1)
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Pancard</label>
                                        <select class="form-control" id="update_pancard">
                                            <option value="1">Active</option>
                                            <option value="0">De Active</option>

                                        </select>
                                    </div>
                                </div>
                            @endif

                            @if(Auth::User()->company->ecommerce == 1)
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Ecommerce</label>
                                        <select class="form-control" id="update_ecommerce">
                                            <option value="1">Active</option>
                                            <option value="0">De Active</option>

                                        </select>
                                    </div>
                                </div>
                            @endif

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">User</label>
                                    <select class="form-control" id="update_user_id">
                                        <option value="">Select User</option>
                                        @foreach($users as $value)
                                            <option value="{{ $value->id }}">{{ $value->name }} ({{ $value->role->role_title }})</option>
                                        @endforeach
                                    </select>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="update_user_id_errors"></li>
                                    </ul>
                                </div>
                            </div>


                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Status</label>
                                    <select class="form-control" id="update_status_id">
                                        <option value="1">Active</option>
                                        <option value="0">De Active</option>

                                    </select>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn ripple btn-primary" type="button" onclick="update_white_label()" id="update_btn">Update Now</button>
                    <button class="btn btn-primary" type="button"  id="update_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                    <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
    </div>




    <div class="modal fade" id="view_whitelabel_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2" aria-hidden="true">
        <div class="modal-dialog modal-dialog-slideout" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Company Name</label>
                                    <input type="text" id="view_company_name" class="form-control" placeholder="Company Name" disabled>

                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Company Email </label>
                                    <input type="text" id="view_company_email" class="form-control" placeholder="Company Email" disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Company Address </label>
                                    <input type="text" id="view_company_address" class="form-control" placeholder="Company Address" disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Company Address Two</label>
                                    <input type="text" id="view_company_address_two" class="form-control" placeholder="Company Address Two" disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Support Number</label>
                                    <input type="text" id="view_support_number" class="form-control" placeholder="Support Number" disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Whatsapp Number</label>
                                    <input type="text" id="view_whatsapp_number" class="form-control" placeholder="Whatsapp Number" disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Company Website</label>
                                    <input type="text" id="view_company_website" class="form-control" placeholder="Company Website" disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">News</label>
                                    <input type="text" id="view_news" class="form-control" placeholder="News" disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Information 1</label>
                                    <input type="text" id="view_update_one" class="form-control" placeholder="Information 1" disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Information 2</label>
                                    <input type="text" id="view_update_two" class="form-control" placeholder="Information 2" disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Information 3</label>
                                    <input type="text" id="view_update_three" class="form-control" placeholder="Information 3" disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Sender ID</label>
                                    <input type="text" id="view_sender_id" class="form-control" placeholder="Sender ID" disabled>
                                </div>
                            </div>



                            @if(Auth::User()->company->recharge == 1)
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Recharge Service</label>
                                    <select class="form-control" id="view_recharge" disabled>
                                        <option value="1">Active</option>
                                        <option value="0">De Active</option>

                                    </select>
                                </div>
                            </div>
                            @endif

                            @if(Auth::User()->company->money == 1)
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Money Service</label>
                                    <select class="form-control" id="view_money" disabled>
                                        <option value="1">Active</option>
                                        <option value="0">De Active</option>

                                    </select>
                                </div>
                            </div>
                            @endif

                            @if(Auth::User()->company->aeps == 1)
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Aeps Service</label>
                                    <select class="form-control" id="view_aeps" disabled>
                                        <option value="1">Active</option>
                                        <option value="0">De Active</option>

                                    </select>
                                </div>
                            </div>
                            @endif


                            @if(Auth::User()->company->payout == 1)
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Payout</label>
                                    <select class="form-control" id="view_payout" disabled>
                                        <option value="1">Active</option>
                                        <option value="0">De Active</option>

                                    </select>
                                </div>
                            </div>
                            @endif

                            @if(Auth::User()->company->view_plan == 1)
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">View Plan</label>
                                    <select class="form-control" id="view_view_plan" disabled>
                                        <option value="1">Active</option>
                                        <option value="0">De Active</option>

                                    </select>
                                </div>
                            </div>
                            @endif

                            @if(Auth::User()->company->pancard == 1)
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Pancard</label>
                                    <select class="form-control" id="view_pancard" disabled>
                                        <option value="1">Active</option>
                                        <option value="0">De Active</option>

                                    </select>
                                </div>
                            </div>
                            @endif

                            @if(Auth::User()->company->ecommerce == 1)
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Ecommerce</label>
                                    <select class="form-control" id="view_ecommerce" disabled>
                                        <option value="1">Active</option>
                                        <option value="0">De Active</option>

                                    </select>
                                </div>
                            </div>
                            @endif

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Status</label>
                                    <select class="form-control" id="view_status_id" disabled>
                                        <option value="1">Active</option>
                                        <option value="0">De Active</option>

                                    </select>
                                </div>
                            </div>



                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" style="display: none;">Save changes</button>
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
@endsection