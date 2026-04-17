<script type="text/javascript">
    function view_recharges(id) {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var dataString = 'id=' + id +  '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/report/v1/view-transaction-details')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    $("#view_id").val(msg.details.id);
                    $("#view_company").val(msg.details.company);
                    $("#view_created_at").val(msg.details.created_at);
                    $("#view_user").val(msg.details.user);
                    $("#view_provider").val(msg.details.provider);
                    $("#view_number").val(msg.details.number);
                    $("#view_txnid").val(msg.details.txnid);
                    $("#view_opening_balance").val(msg.details.opening_balance);
                    $("#view_amount").val(msg.details.amount);
                    $("#view_profit").val(msg.details.profit);
                    $("#view_total_balance").val(msg.details.total_balance);
                    $("#view_mode").val(msg.details.mode);
                    $("#view_api_id").val(msg.details.api_id);
                    $("#view_client_id").val(msg.details.client_id);
                    $("#view_ip_address").val(msg.details.ip_address);
                    $("#view_status_id").val(msg.details.status_id);

                    // money details
                    $("#view_account_number").val(msg.details.moneydetails.account_number);
                    $("#view_ifsc").val(msg.details.moneydetails.ifsc);
                    $("#view_bank_name").val(msg.details.moneydetails.bank_name);
                    $("#view_name").val(msg.details.moneydetails.name);
                    $("#view_remiter_number").val(msg.details.moneydetails.remiter_number);
                    $("#view_remiter_name").val(msg.details.moneydetails.remiter_name);

                    $("#receipt_anchor").attr('href', msg.details.receipt_anchor);
                    $("#mobile_receipt").attr('href', msg.details.mobile_receipt);
                    $("#dispute_anchor").attr('onclick', msg.details.dispute_anchor);
                    $("#view_recharge_model").modal('show');
                }else{
                    swal("Faild", msg.message, "error");
                }
            }
        });
    }
    
    function dispute_transaction(id) {
        $(".loader").show();
        $("#view_recharge_model").modal('hide');
        var token = $("input[name=_token]").val();
        var dataString = 'id=' + id +  '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/report/v1/view-transaction-details')}}",
            data: dataString,
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    $("#dispute_id").val(msg.details.id);
                    $("#dispute_provider").val(msg.details.provider);
                    $("#dispute_number").val(msg.details.number);
                    $("#dispute_model").modal('show');
                }else{
                    swal("Faild", msg.message, "error");
                }
            }
        });

    }
    
    function dispute_now() {
        $("#dispute_btn").hide();
        $("#dispute_btn_loader").show();
        var token = $("input[name=_token]").val();
        var report_id = $("#dispute_id").val();
        var reason = $("#dispute_reason").val();
        var message = $("#dispute_message").val();
        var dataString = 'report_id=' + report_id + '&reason=' + reason + '&message=' + message + '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/dispute-transaction')}}",
            data: dataString,
            success: function (msg) {
                $("#dispute_btn").show();
                $("#dispute_btn_loader").hide();
                if (msg.status == 'success') {
                    swal("Success", msg.message, "success");
                    setTimeout(function () { location.reload(1); }, 3000);
                } else if(msg.status == 'validation_error'){
                    $("#dispute_id_errors").text(msg.errors.report_id);
                    $("#dispute_reason_errors").text(msg.errors.reason);
                    $("#dispute_message_errors").text(msg.errors.message);
                }else{
                    swal("Faild", msg.message, "error");
                }
            }
        });
    }

    function download_report() {
        $("#download_btn").hide();
        $("#download_btn_loader").show();
        var token = $("input[name=_token]").val();
        var download_menu_name = $("#download_menu_name").val();
        var download_password = $("#download_password").val();
        var fromdate = $("#fromdate").val();
        var todate = $("#todate").val();
        var optional1 = $("#download_optional1").val();
        var dataString = 'menu_name=' + download_menu_name + '&password=' + download_password + '&fromdate=' + fromdate + '&todate=' + todate + '&optional1=' + optional1 + '&_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/download/v1/file-download')}}",
            data: dataString,
            success: function (msg) {
                $("#download_btn").show();
                $("#download_btn_loader").hide();
                if (msg.status == 'success') {
                    $("#download-label").show();
                    $("#download_link").attr('href', msg.download_link);
                } else if(msg.status == 'validation_error'){
                    $("#download_menu_name_errors").text(msg.errors.menu_name);
                    $("#download_password_errors").text(msg.errors.password);
                }else{
                    swal("Faild", msg.message, "error");
                }
            }
        });
    }
</script>

<div class="modal  show" id="dispute_model"data-toggle="modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Dispute Transaction</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="form-body">

                    <div class="row">

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Report Id</label>
                                <input type="text" id="dispute_id" class="form-control" placeholder="Report Id" disabled>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="dispute_id_errors"></li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Provider</label>
                                <input type="text" id="dispute_provider" class="form-control" placeholder="Provider" disabled>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="dispute_provider_errors"></li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Number</label>
                                <input type="text" id="dispute_number" class="form-control" placeholder="Number" disabled>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="dispute_number_errors"></li>
                                </ul>
                            </div>
                        </div>


                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Reason</label>
                                <select class="form-control" id="dispute_reason">
                                    @foreach(App\Models\Disputereason::where('company_id', Auth::User()->company_id)->get() as $value)
                                        <option value="{{$value->id }}">{{ $value->reason }}</option>
                                    @endforeach
                                </select>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="dispute_reason_errors"></li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="name">Message</label>
                                <textarea class="form-control" id="dispute_message" placeholder="Enter Message Here.."></textarea>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="dispute_message_errors"></li>
                                </ul>
                            </div>
                        </div>


                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn ripple btn-primary" type="button" id="dispute_btn" onclick="dispute_now()">Dispute</button>
                <button class="btn btn-primary" type="button"  id="dispute_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="view_recharge_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2" aria-hidden="true">
    <div class="modal-dialog modal-dialog-slideout" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="form-body">
                    <div class="row">

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Report Id</label>
                                <input type="text" id="view_id" class="form-control" placeholder="Report ID" disabled>
                            </div>
                        </div>



                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Transaction Date</label>
                                <input type="text" id="view_created_at" class="form-control" placeholder="Date" disabled>
                            </div>
                        </div>



                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Provider</label>
                                <input type="text" id="view_provider" class="form-control" placeholder="Provider" disabled>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Number</label>
                                <input type="text" id="view_number" class="form-control" placeholder="Number" disabled>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Txnid</label>
                                <input type="text" id="view_txnid" class="form-control" placeholder="TXNID" disabled>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Opening Balance</label>
                                <input type="text" id="view_opening_balance" class="form-control" placeholder="Opening Balance" disabled>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Amount</label>
                                <input type="text" id="view_amount" class="form-control" placeholder="Amount" disabled>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Profit</label>
                                <input type="text" id="view_profit" class="form-control" placeholder="Profit" disabled>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Total Balance</label>
                                <input type="text" id="view_total_balance" class="form-control" placeholder="Total Balance" disabled>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Mode</label>
                                <input type="text" id="view_mode" class="form-control" placeholder="Mode" disabled>
                            </div>
                        </div>


                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Client Id</label>
                                <input type="text" id="view_client_id" class="form-control" placeholder="Client Id" disabled>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Ip Address</label>
                                <input type="text" id="view_ip_address" class="form-control" placeholder="Ip Address" disabled>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Status</label>
                                <input type="text" id="view_status_id" class="form-control" placeholder="Status" disabled>
                            </div>
                        </div>

                        @if(Request::segment(2) == 'money-transfer-report')
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Account Number</label>
                                    <input type="text" id="view_account_number" class="form-control" placeholder="Account Number" disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">IFSC Code</label>
                                    <input type="text" id="view_ifsc" class="form-control" placeholder="IFSC Code" disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Bank Name</label>
                                    <input type="text" id="view_bank_name" class="form-control" placeholder="Bank Name" disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Beneficiary Name</label>
                                    <input type="text" id="view_name" class="form-control" placeholder="Beneficiary Name" disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Remiter Number</label>
                                    <input type="text" id="view_remiter_number" class="form-control" placeholder="Remiter Number" disabled>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Remiter Name</label>
                                    <input type="text" id="view_remiter_name" class="form-control" placeholder="Remiter Name" disabled>
                                </div>
                            </div>
                        @endif


                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <a class="btn btn-info" id="receipt_anchor" target="_blank"> Receipt</a>
                <a class="btn btn-info" id="mobile_receipt" target="_blank">Mobile Receipt</a>
                <a class="btn btn-danger" id="dispute_anchor" target="_blank"><i class="fas fa-comments"></i> Dispute</a>


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



<div class="modal  show" id="transaction_download_model"data-toggle="modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Download Data</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="form-body">

                    <div class="row">

                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="name">Menu Name</label>
                                <input type="text" id="download_menu_name" class="form-control" value="{{ $report_slug }}" readonly>

                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="name">Your Login Password</label>
                                <input type="password" id="download_password" class="form-control" placeholder="Login Password">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="download_password_errors"></li>
                                </ul>

                            </div>
                        </div>


                    </div>

                </div>

                <div class="alert alert-outline-danger" role="alert" id="download-label" style="display: none;">
                    <strong> Download File :  <a href="" target="_blank" id="download_link">Click Here</a> </strong>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn ripple btn-primary" type="button" id="download_btn" onclick="download_report()">Verify And Download</button>
                <button class="btn btn-primary" type="button"  id="download_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>