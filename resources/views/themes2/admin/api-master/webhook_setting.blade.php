@extends('themes2.admin.layout.header')
@section('content')

    <script type="text/javascript">

        function update_webhook_url() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var api_id = $("#api_id").val();
            var status_parameter = $("#status_parameter").val();
            var success_value = $("#success_value").val();
            var failure_value = $("#failure_value").val();
            var operator_ref = $("#operator_ref").val();
            var uniq_id = $("#uniq_id").val();
            var ip_address = $("#ip_address").val();
            var failure_value_two = $("#failure_value_two").val();
            var failure_value_three = $("#failure_value_three").val();
            var dataString = 'api_id=' + api_id + '&status_parameter=' + status_parameter + '&success_value=' + success_value + '&failure_value=' + failure_value + '&operator_ref=' + operator_ref + '&uniq_id=' + uniq_id + '&ip_address=' + ip_address + '&failure_value_two=' + failure_value_two + '&failure_value_three=' + failure_value_three + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/update-webhook-url')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#api_id_errors").text(msg.errors.api_id);
                        $("#status_parameter_errors").text(msg.errors.status_parameter);
                        $("#success_value_errors").text(msg.errors.success_value);
                        $("#failure_value_errors").text(msg.errors.failure_value);
                        $("#operator_ref_errors").text(msg.errors.operator_ref);
                        $("#uniq_id_errors").text(msg.errors.uniq_id);
                        $("#ip_address_errors").text(msg.errors.ip_address);
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
                            <a href="{{url('admin/webhooks-logs')}}/{{$api_id}}" class="btn btn-danger btn-sm" target="_blank">Webhook Logs</a>
                        </div>
                        <hr>
                        <div class="widget-content">

                            <div class="card-body">

                                <input type="hidden" id="api_id" value="{{ $api_id }}">
                                <div class="form-body">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label for="name">Webhook URL</label>
                                                <input type="text" id="webhook_url" class="form-control"  value="{{ $webhook_url }}" placeholder="Webhook URL" readonly>

                                            </div>
                                        </div>

                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="name">Status Parameter Name</label>
                                                <input type="text" id="status_parameter" class="form-control" placeholder="Status Parameter" value="{{ $status_parameter }}">
                                                <span class="invalid-feedback d-block" id="status_parameter_errors"></span>
                                            </div>
                                        </div>


                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="name">Success Value</label>
                                                <input type="text" id="success_value" class="form-control" placeholder="Success Value" value="{{ $success_value }}">
                                                <span class="invalid-feedback d-block" id="success_value_errors"></span>
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">Failure Value Case 1</label>
                                                <input type="text" id="failure_value" class="form-control" placeholder="Failure Value" value="{{ $failure_value }}">
                                                <span class="invalid-feedback d-block" id="failure_value_errors"></span>
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">Failure Value Case 2</label>
                                                <input type="text" id="failure_value_two" class="form-control" placeholder="Failure Value" value="{{ $failure_value_two }}">
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="name">Failure Value Case 3</label>
                                                <input type="text" id="failure_value_three" class="form-control" placeholder="Failure Value" value="{{ $failure_value_three }}">
                                            </div>
                                        </div>

                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="name">Operator Ref Number Parameter Name</label>
                                                <input type="text" id="operator_ref" class="form-control" placeholder="Operator Ref Number Paramter Name" value="{{ $operator_ref }}">
                                                <span class="invalid-feedback d-block" id="operator_ref_errors"></span>
                                            </div>
                                        </div>

                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="name">Our Uniq Id Parameter Name</label>
                                                <input type="text" id="uniq_id" class="form-control" placeholder="Our Uniq Id Parameter Name" value="{{ $uniq_id }}">
                                                <span class="invalid-feedback d-block" id="uniq_id_errors"></span>
                                            </div>
                                        </div>

                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label for="name">{{ $api_name }} Server IP</label>
                                                <input type="text" id="ip_address" class="form-control" placeholder="{{ $api_name }} Server IP" value="{{ $ip_address }}">
                                                <span class="invalid-feedback d-block" id="ip_address_errors"></span>
                                            </div>
                                        </div>


                                        <button type="submit" class="btn btn-danger waves-effect waves-light" onclick="update_webhook_url()">Save Details</button>

                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!-- Main Body Ends -->


@endsection