@extends('themes2.agent.layout.header')
@section('content')

    <script type="text/javascript">
        function view_recharges() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var mobile_number = $("#mobile_number").val();
            var provider_id = $("#provider_id").val();
            var amount = $("#amount").val();
            var dataString = 'mobile_number=' + mobile_number + '&provider_id=' + provider_id + '&amount=' + amount + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/view-recharge-details')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        generate_millisecond();
                        $("#confirm_provider_name").val(msg.confirm_provider_name);
                        $("#confirm_mobile_number").val(msg.confirm_mobile_number);
                        $("#confirm_amount").val(msg.confirm_amount);
                        $("#confirm_provider_id").val(msg.confirm_provider_id);
                        $("#confirm_recharge_model").modal('show');
                    } else if(msg.status == 'validation_error'){
                        $("#mobile_number_errors").text(msg.errors.mobile_number);
                        $("#provider_id_errors").text(msg.errors.provider_id);
                        $("#amount_errors").text(msg.errors.amount);
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
        <!-- Main Body Starts -->
        <div class="layout-px-spacing">
            <div class="layout-top-spacing mb-2">
                <div class="col-md-12">
                    <div class="row">
                        <div class="container p-0">
                            <div class="row layout-top-spacing">
                                <div class="col-lg-5 layout-spacing">
                                    <div class="statbox widget box box-shadow mb-4">
                                        <div class="widget-header">
                                            <div class="row">
                                                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                                                    <h4>{{ $page_title }}</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="widget-content widget-content-area">
                                            <div class="form-group">
                                                <label>Provider</label>
                                                <select class="form-control select2" id="provider_id" style="width: 100%;">
                                                    @foreach($providers as $value)
                                                        <option value="{{ $value->id }}">{{ $value->provider_name }}</option>
                                                    @endforeach
                                                </select>
                                                <span class="invalid-feedback d-block" id="provider_id_errors"></span>
                                            </div>

                                            <div class="form-group">
                                                <label>DTH Number</label>
                                                <input type="text" class="form-control" placeholder="DTH Number" id="mobile_number">
                                                <span class="invalid-feedback d-block" id="mobile_number_errors"></span>
                                            </div>


                                            <div class="form-group">
                                                <label>Amount</label>
                                                <input type="text" class="form-control" placeholder="Amount" id="amount">
                                                @if(Auth::User()->company->view_plan == 1)
                                                    <span style="position: relative;top: -30px;right: 10px;float: right;"><a style="cursor: pointer;" onclick="dth_customer_info()" id="vbil">Customer Info</a></span>
                                                @endif
                                                <span class="invalid-feedback d-block" id="amount_errors"></span>
                                            </div>

                                        </div>
                                        <div class="widget-footer text-right">
                                            <button class="btn btn-primary mr-2" type="button" onclick="view_recharges()">Submit</button>
                                        </div>

                                        <div class="widget-footer text-right">
                                            @if(Auth::User()->company->view_plan == 1)
                                            <button class="btn ripple btn-danger" type="button" onclick="dth_plans()">Plans</button>
                                            <button class="btn ripple btn-info" type="button" onclick="dth_refresh()">Refresh</button>
                                            <button class="btn ripple btn-warning" type="button" onclick="dth_roffer()">Roffer</button>
                                            @endif
                                        </div>
                                    </div>

                                </div>

                                {{--Start Left Side Banner--}}
                                @include('themes2.agent.service.left_banner')
                                {{--End Left Side Banner--}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Main Body Ends -->

    @include('common.recharge_confirmation_model')
    @include('common.dth_view_plan_model')


@endsection