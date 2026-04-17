@extends('themes2.admin.layout.header')
@section('content')
    <script type="text/javascript">
        function view_template(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/sms-template/view-template')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        $("#view_id").val(msg.details.id);
                        $("#view_template_name").val(msg.details.template_name);
                        $("#view_template_message").val(msg.details.template_message);
                        $("#view_whatsapp").val(msg.details.whatsapp);
                        $("#view_sms").val(msg.details.sms);
                        $("#view_send_mail").val(msg.details.send_mail);
                        $("#view_template_id").val(msg.details.template_id);
                        $("#view_template_model").modal('show');
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function update_template() {
            $("#update_btn").hide();
            $("#update_btn_loader").show();
            var token = $("input[name=_token]").val();
            var id = $("#view_id").val();
            var whatsapp = $("#view_whatsapp").val();
            var sms = $("#view_sms").val();
            var template_id = $("#view_template_id").val();
            var send_mail = $("#view_send_mail").val();
            var dataString = 'id=' + id + '&whatsapp=' + whatsapp + '&sms=' + sms + '&template_id=' + template_id + '&send_mail=' + send_mail + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/sms-template/update-template')}}",
                data: dataString,
                success: function (msg) {
                    $("#update_btn").show();
                    $("#update_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
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
                        </div>
                        <hr>
                        <div class="widget-content">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table text-md-nowrap" id="example1">
                                        <thead>
                                        <tr>
                                            <th class="wd-15p border-bottom-0">Id</th>
                                            <th class="wd-15p border-bottom-0">Template Id</th>
                                            <th class="wd-15p border-bottom-0">Template Name</th>
                                            <th class="wd-15p border-bottom-0">Template Message</th>
                                            <th class="wd-15p border-bottom-0">Whatsapp</th>
                                            <th class="wd-15p border-bottom-0">Sms</th>
                                            <th class="wd-15p border-bottom-0">Mail</th>
                                            <th class="wd-15p border-bottom-0">Action</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php $i = 0 ?>
                                        @foreach($smstemplates as $value)
                                            <tr>
                                                <td>{{ ++$i }}</td>
                                                <td>{{ $value->template_id }}</td>
                                                <td>{{ $value->template_name }}</td>
                                                <td>{{ $value->template_message }} {{ $brand_name }}</td>
                                                <td>@if($value->whatsapp == 1)<span class="badge badge-success">Enabled</span> @else <span class="badge badge-danger">Disabled</span>  @endif</td>
                                                <td>@if($value->sms == 1)<span class="badge badge-success">Enabled</span> @else <span class="badge badge-danger">Disabled</span>  @endif</td>
                                                <td>@if($value->send_mail == 1)<span class="badge badge-success">Enabled</span> @else <span class="badge badge-danger">Disabled</span>  @endif</td>
                                                <td><button class="btn btn-danger btn-sm" onclick="view_template({{ $value->id }})">Update</button></td>
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
        </div>
        <!-- Main Body Ends -->

        {{--Start update template modal--}}
        <div class="modal fade" id="view_template_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2" aria-hidden="true">
            <div class="modal-dialog modal-dialog-slideout" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title">Template Details</h6>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                    </div>

                    <input type="hidden" id="view_id">
                    <div class="modal-body">
                        <div class="form-body">
                            <div class="row">

                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="name">Template Name</label>
                                        <input type="text" id="view_template_name" class="form-control" readonly>
                                    </div>
                                </div>


                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="name">Template Message</label>
                                        <textarea type="text" id="view_template_message" class="form-control" readonly></textarea>
                                    </div>
                                </div>

                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="name">Template Id</label>
                                        <input type="text" id="view_template_id" class="form-control" placeholder="Template Id">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Whatsapp</label>
                                        <select class="form-control" id="view_whatsapp">
                                            <option value="1">Enabled</option>
                                            <option value="0">Disabled</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Sms</label>
                                        <select class="form-control" id="view_sms">
                                            <option value="1">Enabled</option>
                                            <option value="0">Disabled</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="name">Mail</label>
                                        <select class="form-control" id="view_send_mail">
                                            <option value="1">Enabled</option>
                                            <option value="0">Disabled</option>
                                        </select>
                                    </div>
                                </div>






                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="update_btn" onclick="update_template()">Update</button>
                        <button class="btn btn-primary" type="button"  id="update_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    {{--close update template modal--}}


@endsection