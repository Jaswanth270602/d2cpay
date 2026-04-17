@extends('themes2.admin.layout.header')
@section('content')
    <script type="text/javascript">
        $(document).ready(function () {
            $("#role_id").select2({
                placeholder: "Select a Roles"
            });

            $("#company_id").select2({
                placeholder: "Select Company"
            });

            $("#role_ids").select2({
                placeholder: "Select a Roles"
            });

            $("#company_ids").select2({
                placeholder: "Select Company"
            });
        });

        function send_now() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var role_id = $("#role_id").val();
            var message = $("#message").val();
            var company_id = $("#company_id").val();
            var dataString = 'role_id=' + role_id + '&message=' + message + '&company_id=' + company_id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/whatsapp/role-wise')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#role_id_errors").text(msg.errors.role_id);
                        $("#message_errors").text(msg.errors.message);
                        $("#company_id_errors").text(msg.errors.company_id);
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
                                <div class="col-lg-6 layout-spacing">
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
                                                    <label>Select Company</label>
                                                    <select class="form-control select2" id="company_id" style="width: 100%" multiple>
                                                        @foreach($companies as $value)
                                                            <option value="{{ $value->id }}">{{ $value->company_name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <span class="invalid-feedback d-block" id="company_id_errors"></span>
                                                </div>

                                                <div class="form-group">
                                                    <label>Member Type</label>
                                                    <select class="form-control select2" id="role_id" style="width: 100%" multiple>
                                                        @foreach($roledetails as $value)
                                                            <option value="{{ $value->id }}">{{ $value->role_title }}</option>
                                                        @endforeach
                                                    </select>
                                                    <span class="invalid-feedback d-block" id="role_id_errors"></span>
                                                </div>

                                                <div class="form-group">
                                                    <label>Message</label>
                                                    <textarea class="form-control" placeholder="Enter Message Here....." rows="4" id="message" name="message"></textarea>
                                                    <span class="invalid-feedback d-block" id="message_errors"></span>
                                                </div>
                                            </div>
                                            <div class="widget-footer text-right">
                                                <button type="submit" class="btn btn-primary mr-2" onclick="send_now()">Send Now</button>
                                            </div>
                                    </div>
                                </div>

                                <div class="col-lg-6 layout-spacing">
                                    <div class="statbox widget box box-shadow mb-4">
                                        <div class="widget-header">
                                            <div class="row">
                                                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                                                    <h4>{{ $page_title }} With Image</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <form role="form" action="{{url('admin/whatsapp/role-wise-image')}}" method="post" enctype="multipart/form-data">
                                            {!! csrf_field() !!}
                                        <div class="widget-content widget-content-area">
                                            @if(Session::has('success'))
                                                <div class="alert alert-info">
                                                    <a class="close" data-dismiss="alert">×</a>
                                                    <strong>Alert </strong> {!!Session::get('success')!!}
                                                </div>
                                            @endif

                                            @if(Session::has('failure'))
                                                <div class="alert alert-danger">
                                                    <a class="close" data-dismiss="alert">×</a>
                                                    <strong>Alert </strong> {!!Session::get('failure')!!}
                                                </div>
                                            @endif

                                            <div class="form-group">
                                                <label>Select Company</label>
                                                <select class="form-control select2" id="company_ids"  name="company_id[]" style="width: 100%" multiple>
                                                    @foreach($companies as $value)
                                                        <option value="{{ $value->id }}">{{ $value->company_name }}</option>
                                                    @endforeach
                                                </select>
                                                @if ($errors->has('company_id'))
                                                    <span class="invalid-feedback d-block">{{ $errors->first('company_id') }}</span>
                                                @endif
                                            </div>

                                            <div class="form-group">
                                                <label>Member Type</label>
                                                <select class="form-control select2" id="role_ids" name="role_id[]" style="width: 100%" multiple>
                                                    @foreach($roledetails as $value)
                                                        <option value="{{ $value->id }}">{{ $value->role_title }}</option>
                                                    @endforeach
                                                </select>
                                                @if ($errors->has('role_id'))
                                                    <span class="invalid-feedback d-block">{{ $errors->first('role_id') }}</span>
                                                @endif
                                            </div>

                                            <div class="form-group">
                                                <label>Image</label>
                                                <input type="file" class="form-control" name="photo">
                                                @if ($errors->has('photo'))
                                                    <span class="invalid-feedback d-block">{{ $errors->first('photo') }}</span>
                                                @endif
                                            </div>

                                            <div class="form-group">
                                                <label>Image Caption</label>
                                                <input type="text" class="form-control" name="image_caption" placeholder="Image Caption">
                                                @if ($errors->has('image_caption'))
                                                    <span class="invalid-feedback d-block">{{ $errors->first('image_caption') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="widget-footer text-right">
                                            <button type="submit" class="btn btn-primary mr-2">Send Now</button>
                                        </div>
                                        </form>
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