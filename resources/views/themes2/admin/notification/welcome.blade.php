@extends('themes2.admin.layout.header')
@section('content')
    <script type="text/javascript">

        $(document).ready(function () {
            $("#notification_type").select2({
                placeholder: "Notification Type",
                allowClear: true
            });

            $("#role_id").select2({
                placeholder: "Member Type",
                allowClear: true
            });
        });
        function send_notification() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var notification_title = $("#notification_title").val();
            var notification_message = $("#notification_message").val();
            var notification_type = $("#notification_type").val();
            var role_id = $("#role_id").val();
            var dataString = 'notification_title=' + notification_title + '&notification_message=' + notification_message + '&notification_type=' + notification_type + '&role_id=' + role_id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/notification/send-notification')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#notification_title_errors").text(msg.errors.notification_title);
                        $("#notification_message_errors").text(msg.errors.notification_message);
                        $("#role_id_errors").text(msg.errors.role_id);
                        $("#notification_type_errors").text(msg.errors.notification_type);
                    }else{
                        swal("Faild", msg.message, "error");
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
            <div class="layout-top-spacing mb-2">
                <div class="col-md-12">
                    <div class="row">
                        <div class="container p-0">
                            <div class="row layout-top-spacing">
                                <div class="col-lg-7 layout-spacing">
                                    <div class="statbox widget box box-shadow mb-4">
                                            {!! csrf_field() !!}
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
                                                    <label>Notification Type</label>
                                                    <select class="form-control select2" id="notification_type" style="width: 100%;" multiple>
                                                        <option value="1">Sms</option>
                                                        <option value="2">Whatsapp</option>
                                                    </select>
                                                    <span class="invalid-feedback d-block" id="notification_type_errors"></span>
                                                </div>

                                                <div class="form-group">
                                                    <label>Member Type</label>
                                                    <select class="form-control select2" id="role_id" style="width: 100%;" multiple>
                                                        @foreach($roledetails as $value)
                                                            <option value="{{ $value->id }}">{{ $value->role_title }}</option>
                                                        @endforeach
                                                    </select>
                                                    <span class="invalid-feedback d-block" id="role_id_errors"></span>
                                                </div>

                                                <div class="form-group">
                                                    <label>Notification Title</label>
                                                    <input type="text" class="form-control"  placeholder="Notification Title" id="notification_title">
                                                    <span class="invalid-feedback d-block" id="notification_title_errors"></span>
                                                </div>

                                                <div class="form-group">
                                                    <label>Notification Message</label>
                                                    <textarea class="form-control"  placeholder="Notification Message" rows="4" id="notification_message"></textarea>
                                                    <span class="invalid-feedback d-block" id="notification_message_errors"></span>
                                                </div>


                                            </div>
                                            <div class="widget-footer text-right">
                                                <button type="button" class="btn btn-primary mr-2"  onclick="send_notification()">Send Notification</button>
                                                <button type="reset" class="btn btn-outline-primary"> Cancel</button>
                                            </div>
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