@extends('admin.layout.header')
@section('content')
    <script type="text/javascript">
        function view_request(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/view-return-request')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        generate_millisecond();
                        $("#id").val(msg.details.id);
                        $("#parent_name").val(msg.details.parent_name);
                        $("#amount").val(msg.details.amount);
                        $("#remark").val(msg.details.remark);
                        $("#status_id").val(msg.details.status_id);
                        $("#view_return_model").modal('show');
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function generate_millisecond() {
            var id = 1;
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/generate-millisecond')}}",
                data: dataString,
                success: function (msg) {
                    $("#trasnfer_millisecond").val(msg.miliseconds);
                }
            });
        }
        
        
        function return_trasnfer() {
            $("#transfer_btn").hide();
            $("#transfer_btn_loader").show();
            var token = $("input[name=_token]").val();
            var id = $("#id").val();
            var password = $("#password").val();
            var status_id = $("#status_id").val();
            var millisecond = $("#trasnfer_millisecond").val();
            var dataString = 'id=' + id + '&password=' + password + '&status_id=' + status_id + '&dupplicate_transaction=' + millisecond +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/approve-payment-return-request')}}",
                data: dataString,
                success: function (msg) {
                    $("#transfer_btn").show();
                    $("#transfer_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#password_errors").text(msg.errors.password);
                        $("#status_id_errors").text(msg.errors.status_id);
                        $("#dupplicate_transaction_errors").text(msg.errors.dupplicate_transaction);
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
                            <h4 class="card-title mg-b-2 mt-2">{{ $page_title }}</h4>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table text-md-nowrap" id="example1">
                                <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0">id</th>
                                    <th class="wd-15p border-bottom-0">date</th>
                                    <th class="wd-15p border-bottom-0">request by</th>
                                    <th class="wd-15p border-bottom-0">Remark</th>
                                    <th class="wd-15p border-bottom-0">Amount</th>
                                    <th class="wd-15p border-bottom-0">Status</th>
                                    <th class="wd-15p border-bottom-0">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($returnrequest as $value)
                                    <tr>
                                        <td>{{ $value->id }}</td>
                                        <td>{{ $value->created_at }}</td>
                                        <td>{{ App\User::find($value->parent_id )->name }}</td>
                                        <td>{{ $value->remark }}</td>
                                        <td>{{ number_format($value->amount,2) }}</td>
                                        <td><span class="{{ $value->status->class }}">{{ $value->status->status }}</span></td>
                                        <td><button class="btn btn-danger btn-sm" onclick="view_request({{ $value->id }})"> Approve</button></td>
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




    <div class="modal  show" id="view_return_model"data-toggle="modal">
        <div class="modal-dialog" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Approve Return Request</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">

                        <input type="hidden" id="id">
                        <input type="hidden" id="trasnfer_millisecond">

                        <div class="row">

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Request By</label>
                                    <input type="text" id="parent_name" class="form-control" placeholder="Request By" disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Amount</label>
                                    <input type="text" id="amount" class="form-control" placeholder="Amount" disabled>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="dupplicate_transaction_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Remark</label>
                                    <input type="text" id="remark" class="form-control" placeholder="Remark" disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Login Password</label>
                                    <input type="text" id="password" class="form-control" placeholder="Password">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="password_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Status</label>
                                   <select class="form-control" id="status_id">
                                       <option value="1">Approve</option>
                                       <option value="2">Reject</option>
                                       <option value="3">Pending</option>
                                   </select>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="status_id_errors"></li>
                                    </ul>
                                </div>
                            </div>





                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn ripple btn-primary" type="button" id="transfer_btn" onclick="return_trasnfer()">Return Now</button>
                    <button class="btn btn-primary" type="button"  id="transfer_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                    <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection