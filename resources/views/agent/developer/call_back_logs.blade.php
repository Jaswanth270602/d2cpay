@extends('agent.layout.header')
@section('content')
    <script type="text/javascript">
        function view_response(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id +   '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/developer/view-callback-logs')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if(msg.status == 'success'){
                        $("#callback_id").val(msg.id);
                        $("#request_url").text(msg.request_url);
                        $("#response_message").text(msg.response_message);
                        $("#view_logs_model").modal('show');
                    }else{
                        swal("Faild", msg.message, "error");
                    }
                }
            });
        }
        
        function resend_call_back() {
            $("#resend_btn").hide();
            $("#resend_btn_loader").show();
            var token = $("input[name=_token]").val();
            var callback_id = $("#callback_id").val();
            var dataString = 'callback_id=' + callback_id +   '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/developer/resend-callback-url')}}",
                data: dataString,
                success: function (msg) {
                    $("#resend_btn").show();
                    $("#resend_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 2000);
                    }else{
                        swal("Faild", msg.message, "error");
                    }
                }
            });
        }
    </script>



    <div class="main-content-body">
        <div class="row row-sm">

            @include('agent.developer.left_side')

            <div class="col-lg-8 col-xl-9">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">{{ $page_title }}</h4>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table text-md-nowrap" id="example1">
                                <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0">Sr No</th>
                                    <th class="wd-15p border-bottom-0">Number</th>
                                    <th class="wd-25p border-bottom-0">URL</th>
                                    <th class="wd-25p border-bottom-0">Action</th>
                                </tr>
                                </thead>
                                <tbody>

                                <?php $i = 1 ?>
                                @foreach($reports as $value)
                                    <tr>
                                        <td>{{ $i++ }}</td>
                                        <td>{{ $value->number }}</td>
                                        <td>{{ $value->url }}</td>
                                        <td><button class="btn btn-danger btn-sm" onclick="view_response({{ $value->id }})">View</button></td>
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



    <div class="modal  show" id="view_logs_model"data-toggle="modal">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title"><i class="fas fa-history"></i> View Callback Logs</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div aria-multiselectable="true" class="accordion accordion-gray" id="accordion" role="tablist">
                        <input type="hidden" id="callback_id">
                        <div class="card">
                            <div class="card-header" id="headingOne" role="tab">
                                <a aria-controls="collapseOne" aria-expanded="true" data-toggle="collapse" href="#collapseOne"><span id="request_url"></span></a>
                            </div>
                            <div aria-labelledby="headingOne" class="collapse show" data-parent="#accordion" id="collapseOne" role="tabpanel">
                                <div class="card-body">
                                   <p id="response_message"></p>
                                </div>
                            </div>
                        </div>

                    </div><!-- accordion -->
                </div>
                <div class="modal-footer">
                    <button class="btn ripple btn-primary" type="button" id="resend_btn" onclick="resend_call_back()">Ressnd Callback</button>
                    <button class="btn btn-primary" type="button"  id="resend_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                    <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
    </div>

@endsection