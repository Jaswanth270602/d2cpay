@extends('admin.layout.header')
@section('content')
    <script src="https://cdn.ckeditor.com/4.14.1/full/ckeditor.js"></script>
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


    <div class="main-content-body">

        <div class="row">
            <div class="col-lg-6 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">{{ $page_title }}</h6>
                            <hr>
                        </div>


                        <div class="mb-4">
                            <label>Select Company</label>
                            <select class="form-control select2" id="company_id" style="width: 100%" multiple>
                                @foreach($companies as $value)
                                    <option value="{{ $value->id }}">{{ $value->company_name }}</option>
                                @endforeach
                            </select>
                            <ul class="parsley-errors-list filled">
                                <li class="parsley-required" id="company_id_errors"></li>
                            </ul>
                        </div>


                        <div class="mb-4">
                            <label>Member Type</label>
                            <select class="form-control select2" id="role_id" style="width: 100%" multiple>
                                @foreach($roledetails as $value)
                                    <option value="{{ $value->id }}">{{ $value->role_title }}</option>
                                @endforeach
                            </select>
                            <ul class="parsley-errors-list filled">
                                <li class="parsley-required" id="role_id_errors"></li>
                            </ul>
                        </div>


                        <div class="mb-4">
                            <label>Message</label>
                            <textarea class="form-control" placeholder="Enter Message Here....." rows="4" id="message" name="message"></textarea>
                            <ul class="parsley-errors-list filled">
                                <li class="parsley-required" id="message_errors"></li>
                            </ul>
                        </div>








                    </div>

                    <div class="modal-footer">
                        <button class="btn ripple btn-primary" type="button" onclick="send_now()">Send Now</button>
                        <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                    </div>
                </div>
            </div>


            {{--with Image--}}
            <div class="col-lg-6 col-md-12">
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
                <form role="form" action="{{url('admin/whatsapp/role-wise-image')}}" method="post" enctype="multipart/form-data">
                    {!! csrf_field() !!}
                    <div class="card">
                        <div class="card-body">
                            <div>
                                <h6 class="card-title mb-1">{{ $page_title }} With Image</h6>
                                <hr>
                            </div>

                            <div class="mb-4">
                                <label>Select Company</label>
                                <select class="form-control select2" id="company_ids"  name="company_id[]" style="width: 100%" multiple>
                                    @foreach($companies as $value)
                                        <option value="{{ $value->id }}">{{ $value->company_name }}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('company_id'))
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required">{{ $errors->first('company_id') }}</li>
                                    </ul>
                                @endif
                            </div>

                            <div class="mb-4">
                                <label>Member Type</label>
                                <select class="form-control select2" id="role_ids" name="role_id[]" style="width: 100%" multiple>
                                    @foreach($roledetails as $value)
                                        <option value="{{ $value->id }}">{{ $value->role_title }}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('role_id'))
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required">{{ $errors->first('role_id') }}</li>
                                    </ul>
                                @endif

                            </div>


                            <div class="mb-4">
                                <label>Image</label>
                                <input type="file" class="form-control" name="photo">
                                @if ($errors->has('photo'))
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required">{{ $errors->first('photo') }}</li>
                                    </ul>
                                @endif
                            </div>

                            <div class="mb-4">
                                <label>Image Caption</label>
                                <input type="text" class="form-control" name="image_caption" placeholder="Image Caption">
                                @if ($errors->has('image_caption'))
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required">{{ $errors->first('image_caption') }}</li>
                                    </ul>
                                @endif
                            </div>



                        </div>

                        <div class="modal-footer">
                            <button class="btn ripple btn-primary" type="submit">Send Now</button>
                            <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                        </div>
                    </div>
                </form>
            </div>


        </div>

    </div>
    </div>
    </div>


@endsection