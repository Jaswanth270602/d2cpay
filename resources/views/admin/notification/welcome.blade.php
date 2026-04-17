    @extends('admin.layout.header')
@section('content')
<script type="text/javascript">

    $(document).ready(function () {
        $("#notification_type").select2({
            placeholder: "Select Notification Type",
            allowClear: true
        });

        $("#role_id").select2({
            placeholder: "Select Member Type",
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


    <div class="main-content-body">
        <!-- row -->
        <div class="row row-sm">

            <!-- Col -->
            <div class="col-lg-8 col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <form class="form-horizontal">
                            <div class="mb-4 main-content-label"><i class="fas fa-bell"></i> Send Notifications</div>
                            <hr>

                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Notification Type</label>
                                    </div>
                                    <div class="col-md-9">
                                        <select class="form-control select2" id="notification_type" style="width: 100%;" multiple>
                                            <option value="1">Sms</option>
                                            <option value="2">Whatsapp</option>
                                        </select>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="notification_type_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Member Type</label>
                                    </div>
                                    <div class="col-md-9">
                                        <select class="form-control select2" id="role_id" style="width: 100%;" multiple>
                                            @foreach($roledetails as $value)
                                             <option value="{{ $value->id }}">{{ $value->role_title }}</option>
                                            @endforeach
                                        </select>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="role_id_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>


                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Notification Title</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control"  placeholder="Notification Title" id="notification_title">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="notification_title_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>




                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Notification Message</label>
                                    </div>
                                    <div class="col-md-9">
                                        <textarea class="form-control"  placeholder="Notification Message" rows="4" id="notification_message"></textarea>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="notification_message_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>
                    <div class="card-footer bg-white">
                        <div class="float-right"><a href="#" class="btn btn-danger" onclick="send_notification()"><i class="fas fa-bell"></i> Send Notification</a></div>
                    </div>
                </div>
            </div>
            <!-- /Col -->

        </div>

    </div>
    </div>
    </div>




@endsection