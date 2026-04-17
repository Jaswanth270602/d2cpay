@extends('admin.layout.header')
@section('content')
    <script type="text/javascript">

        $( document ).ready(function() {
            setInterval(function () {
                get_chat();
            }, 2000);
        });



        function view_conversation(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/view-dispute-conversation')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        $(".report_id").text(msg.recharge.report_id);
                        $(".provider").text(msg.recharge.provider);
                        $(".amount").text(msg.recharge.amount);
                        $(".txnid").text(msg.recharge.txnid);
                        $(".transaction_date").text(msg.recharge.transaction_date);
                        $(".transaction_status").text(msg.recharge.transaction_status);
                        $(".number").text(msg.recharge.number);
                        $(".complaint_status").text(msg.recharge.complaint_status);
                        $(".complaint_reason").text(msg.recharge.complaint_reason);
                        $("#dispute_id").val(msg.recharge.dispute_id);
                        $("#refund_anchor").attr('href', msg.recharge.refund_anchor);

                        $(".chat_with_user").html(msg.users.name);
                        $(".is_online").text(msg.users.is_online);
                        $("#view_conversation_model").modal('show');
                    }else{
                        swal("Faild", msg.message, "error");
                    }
                }
            });
        }


        function get_chat () {
            var dispute_id  = $("#dispute_id").val();
            if (dispute_id){
                var token = $("input[name=_token]").val();
                var dataString = 'dispute_id=' + dispute_id +  '&_token=' + token;
                $.ajax({
                    type: "POST",
                    url: "{{url('admin/get-dispute-chat')}}",
                    data: dataString,
                    success: function (msg) {
                        $(".chat_result").html(msg);
                    }
                });
            }
        }

        $(document).ready(function(){
            $('#chat_message').keypress(function(e){
                if(e.keyCode==13)
                // alert('ok');
                    $('#send_btn').click();
            });
        });

        function send_chat() {
            $("#send_btn").hide();
            $("#send_btn_loader").show();
            var token = $("input[name=_token]").val();
            var dispute_id  = $("#dispute_id").val();
            var chat_message = $("#chat_message").val();
            var dataString = 'chat_message=' + chat_message + '&dispute_id=' + dispute_id +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/send-chat-message')}}",
                data: dataString,
                success: function (msg) {
                    $("#send_btn").show();
                    $("#send_btn_loader").hide();
                    $("#chat_message").val('');
                }
            });
        }
        
        function update_compalint() {
            $("#view_conversation_model").modal('hide');
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dispute_id = $("#dispute_id").val();
            var complaint_status = $("#complaint_status").val();
            var dataString = 'dispute_id=' + dispute_id + '&complaint_status=' + complaint_status +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/update-complaint-status')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    }else{
                        swal("Faild", msg.message, "error");
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
                            <table class="@if(Auth::User()->company->table_format == 1)table text-md-nowrap @else display responsive nowrap @endif" id="example1" data-order='[[ 0, "desc" ]]'>
                                <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0">Ticket no</th>
                                    <th class="wd-15p border-bottom-0">txn Date</th>
                                    <th class="wd-15p border-bottom-0">dispute Date</th>
                                    <th class="wd-15p border-bottom-0">User</th>
                                    <th class="wd-15p border-bottom-0">Provider</th>
                                    <th class="wd-15p border-bottom-0">number</th>
                                    <th class="wd-15p border-bottom-0">amount</th>
                                    <th class="wd-15p border-bottom-0">reason</th>
                                    <th class="wd-15p border-bottom-0">Message</th>
                                    <th class="wd-15p border-bottom-0">Status</th>
                                    <th class="wd-15p border-bottom-0">Recharge Status</th>
                                    @if(Auth::User()->role_id == 1)
                                        <th class="wd-15p border-bottom-0">Api</th>
                                        @endif
                                    <th class="wd-15p border-bottom-0">Action</th>

                                </tr>
                                </thead>
                                <tbody>
                                @foreach($dispute as $value)
                                    <tr>
                                        <td>{{ $value->id }}</td>
                                        <td>{{ $value->report->created_at }}</td>
                                        <td>{{ $value->created_at }}</td>
                                        <td><a href="{{url('admin/user-ledger-report')}}/{{Crypt::encrypt($value->user_id)}}">{{ $value->user->name }} {{ $value->user->last_name }}</a></td>
                                        <td>{{ $value->report->provider->provider_name }}</td>
                                        <td>{{ $value->report->number }}</td>
                                        <td>{{ number_format($value->report->amount, 2) }}</td>
                                        <td>{{ $value->disputereason->reason }}</td>
                                        <td>{{ $value->message }}</td>
                                        <td><span class="{{ $value->status->class }}">{{ $value->status->status }}</span></td>
                                        <td><span class="{{ $value->report->status->class }}">{{ $value->report->status->status }}</span></td>
                                        @if(Auth::User()->role_id == 1)
                                            <td>@if($value->api_id == 0)  @else {{ $value->report->api->api_name }} @endif</td>
                                        @endif
                                        <td>
                                            @if(Auth::User()->role_id == 2 && $permission_dispute_chat == 1)
                                                <button class="btn btn-danger btn-sm" onclick="view_conversation({{ $value->id }})">Chat({{App\Models\Disputechat::where('dispute_id',  $value->id)->whereNotIn('user_id', [Auth::id()])->where('is_read', 0)->count()}})</button>
                                            @elseif(Auth::user()->role_id != 2)
                                                <button class="btn btn-danger btn-sm" onclick="view_conversation({{ $value->id }})">Chat({{App\Models\Disputechat::where('dispute_id',  $value->id)->whereNotIn('user_id', [Auth::id()])->where('is_read', 0)->count()}})</button>
                                            @endif

                                        </td>

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



    {{--update bank modal--}}
    <div class="modal fade" id="view_conversation_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2" aria-hidden="true">
        <div class="modal-dialog modal-dialog-slideout" role="document">

            <div class="modal-content">
                <div class="main-chat-header">
                    <div class="img_cont_msg">
                        <img src="https://static.turbosquid.com/Preview/001292/481/WV/_D.jpg" class="rounded-circle user_img_msg">
                    </div>

                    <div class="main-chat-msg-name">
                        <h6 class="chat_with_user"></h6>
                        <small class="is_online"></small>
                    </div>


                </div><!-- main-chat-header -->

                <div class="modal-body">
                    <div class="form-group row">
                        <div class="col-md-6">
                            <h5 style="padding: 2px;font-size: 12px">Report Id - <span class="report_id"></span> </h5>
                        </div>

                        <div class="col-md-6">
                            <h5 style="padding: 2px;font-size: 12px">Provider - <span class="provider" ></span></h5>
                        </div>

                        <div class="col-md-6">
                            <h5 style="padding: 2px;font-size: 12px">Amount - <span class="amount"></span></h5>
                        </div>

                        <div class="col-md-6">
                            <h5 style="padding: 2px;font-size: 12px">Txnid - <span class="txnid"></span></h5>
                        </div>

                        <div class="col-md-6">
                            <h5 style="padding: 2px;font-size: 12px">Transaction Date - <span class="transaction_date"></span></h5>
                        </div>
                        <div class="col-md-6">
                            <h5 style="padding: 2px;font-size: 12px">Transaction Status - <span class="transaction_status"></span></h5>
                        </div>
                        <div class="col-md-6">
                            <h5 style="padding: 2px;font-size: 12px">Number - <span class="number"></span></h5>
                        </div>
                        <div class="col-md-6">
                            <h5 style="padding: 2px;font-size: 12px">Complaint Status - <span class="complaint_status">complaint_status</span></h5>
                        </div>

                        <div class="col-md-12">
                            <h5 style="padding: 2px;font-size: 12px">Complaint Reason - <span class="complaint_reason"></span></h5>
                        </div>


                        <div class="col-md-12">
                            <hr>
                        </div>
                        <input type="hidden" id="dispute_id">

                    </div>
                </div>

                <div class="modal-body">
                    <div class="card grey lighten-3 chat-room">
                        <div class="card-body">
                            <div class="chat_result"></div>
                            @if(Auth::User()->role_id == 1)
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <a id="refund_anchor" target="_blank">Update Transaction</a>
                                </div>
                                <div class="col-md-6">
                                  <select class="form-control" id="complaint_status" onchange="update_compalint(this)">
                                      <option value="0">Update Complaint Status</option>
                                      <option value="1">Solve</option>
                                  </select>
                                </div>
                            </div>
                                @endif
                        </div>
                    </div>
                </div>
                <div class="main-chat-footer">
                    <button aria-label="Close" class="close " data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                    <input class="form-control" placeholder="Type your message here..." type="text" id="chat_message"> <a class="main-msg-send" href="#" onclick="send_chat()" id="send_btn"><i class="far fa-paper-plane"></i></a>
                    <button class="btn btn-primary" type="button"  id="send_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...</button>
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

        .chat{
            margin-top: auto;
            margin-bottom: auto;
        }


        .contacts_body{
            padding:  0.75rem 0 !important;
            overflow-y: auto;
            white-space: nowrap;
        }
        .msg_card_body{
            overflow-y: auto;
        }
        .card-header{
            border-radius: 15px 15px 0 0 !important;
            border-bottom: 0 !important;
        }
        .card-footer{
            border-radius: 0 0 15px 15px !important;
            border-top: 0 !important;
        }
        .container{
            align-content: center;
        }

        .type_msg{
            background-color: rgba(0,0,0,0.3) !important;
            border:0 !important;
            color:white !important;
            height: 60px !important;
            overflow-y: auto;
        }
        .type_msg:focus{
            box-shadow:none !important;
            outline:0px !important;
        }
        .attach_btn{
            border-radius: 15px 0 0 15px !important;
            background-color: rgba(0,0,0,0.3) !important;
            border:0 !important;
            color: white !important;
            cursor: pointer;
        }
        .send_btn{
            border-radius: 0 15px 15px 0 !important;
            background-color: rgba(0,0,0,0.3) !important;
            border:0 !important;
            color: white !important;
            cursor: pointer;
        }
        .search_btn{
            border-radius: 0 15px 15px 0 !important;
            background-color: rgba(0,0,0,0.3) !important;
            border:0 !important;
            color: white !important;
            cursor: pointer;
        }
        .contacts{
            list-style: none;
            padding: 0;
        }
        .contacts li{
            width: 100% !important;
            padding: 5px 10px;
            margin-bottom: 15px !important;
        }
      /*  .active{
            background-color: rgba(0,0,0,0.3);
        }*/
        .user_img{
            height: 70px;
            width: 70px;
            border:1.5px solid #f5f6fa;

        }
        .user_img_msg{
            height: 40px;
            width: 40px;
            border:1.5px solid #f5f6fa;

        }
        .img_cont{
            position: relative;
            height: 70px;
            width: 70px;
        }
        .img_cont_msg{
            height: 40px;
            width: 40px;
        }
        .online_icon{
            position: absolute;
            height: 15px;
            width:15px;
            background-color: #4cd137;
            border-radius: 50%;
            bottom: 0.2em;
            right: 0.4em;
            border:1.5px solid white;
        }
        .offline{
            background-color: #c23616 !important;
        }
        .user_info{
            margin-top: auto;
            margin-bottom: auto;
            margin-left: 15px;
        }
        .user_info span{
            font-size: 20px;
            color: white;
        }
        .user_info p{
            font-size: 10px;
            color: rgba(255,255,255,0.6);
        }
        .video_cam{
            margin-left: 50px;
            margin-top: 5px;
        }
        .video_cam span{
            color: white;
            font-size: 20px;
            cursor: pointer;
            margin-right: 20px;
        }
        .msg_cotainer{
            margin-top: auto;
            margin-bottom: auto;
            margin-left: 10px;
            border-radius: 25px;
            background-color: #82ccdd;
            padding: 10px;
            position: relative;
        }
        .msg_cotainer_send{
            margin-top: auto;
            margin-bottom: auto;
            margin-right: 10px;
            border-radius: 25px;
            background-color: #78e08f;
            padding: 10px;
            position: relative;
        }
        .msg_time{
            position: absolute;
            left: 0;
            bottom: -15px;
            color: black;
            font-size: 10px;
        }
        .msg_time_send{
            position: absolute;
            right:0;
            bottom: -15px;
            color: black;
            font-size: 10px;
        }
        .msg_head{
            position: relative;
        }

        @media(max-width: 576px) {
            .contacts_card {
                margin-bottom: 15px !important;
            }
        }
    </style>


@endsection