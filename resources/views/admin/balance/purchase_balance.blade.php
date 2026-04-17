@extends('admin.layout.header')
@section('content')

    <script type="text/javascript">
        $(document).ready(function () {
            $('#api_id').select2({
                dropdownParent: $('#purchase_balance_model')
            });

            $('#masterbank_id').select2({
                dropdownParent: $('#purchase_balance_model')
            });

            $("#fromdate").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: ('yy-mm-dd'),
            });
            $("#todate").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: ('yy-mm-dd'),
            });
        });

        function purchase_balance() {
            $("#purchase_btn").hide();
            $("#purchase_btn_loader").show();
            var token = $("input[name=_token]").val();
            var api_id = $("#api_id").val();
            var masterbank_id = $("#masterbank_id").val();
            var utr = $("#utr").val();
            var amount = $("#amount").val();
            var password = $("#password").val();
            var dataString = 'api_id=' + api_id + '&masterbank_id=' + masterbank_id +  '&utr=' + utr + '&amount=' + amount + '&password=' + password + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/purchase-balance-now')}}",
                data: dataString,
                success: function (msg) {
                    $("#purchase_btn").show();
                    $("#purchase_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#api_id_errors").text(msg.errors.api_id);
                        $("#masterbank_id_errors").text(msg.errors.masterbank_id);
                        $("#utr_errors").text(msg.errors.utr);
                        $("#amount_errors").text(msg.errors.amount);
                        $("#password_errors").text(msg.errors.password);
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }
    </script>



    <div class="main-content-body">

        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card">
                    <div class="card-body">

                        <form action="{{url('admin/purchase-balance')}}" method="get">
                            <div class="row">
                                <div class="col-lg-4 col-md-8 form-group mg-b-0">
                                    <label class="form-label">From: <span class="tx-danger">*</span></label>
                                    <input class="form-control fc-datepicker" value="{{ $fromdate }}" type="text" id="fromdate" name="fromdate" autocomplete="off">
                                </div>

                                <div class="col-lg-4 col-md-8 form-group mg-b-0">
                                    <label class="form-label">To: <span class="tx-danger">*</span></label>
                                    <input class="form-control fc-datepicker" value="{{ $todate }}" type="text" id="todate"  name="todate" autocomplete="off">
                                </div>


                                <div class="col-lg-4 col-md-4 mg-t-10 mg-sm-t-25">
                                    <button class="btn btn-main-primary pd-x-20" type="submit"><i class="fas fa-search"></i> Search</button>
                                    <button class="btn btn-danger pd-x-20" type="button"  data-toggle="modal" data-target="#transaction_download_model"><i class="fas fa-download"></i> Download</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>


        <div class="row row-sm">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">{{ $page_title }}</h4>
                            <button class="btn btn-danger btn-sm" data-target="#purchase_balance_model" data-toggle="modal">Purchase Balance</button>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="@if(Auth::User()->company->table_format == 1)table text-md-nowrap @else display responsive nowrap @endif" id="my_table">
                                <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0">ID</th>
                                    <th class="wd-15p border-bottom-0">Date</th>
                                    <th class="wd-15p border-bottom-0">User Name</th>
                                    <th class="wd-15p border-bottom-0">Api Name</th>
                                    <th class="wd-15p border-bottom-0">Bank Name</th>
                                    <th class="wd-15p border-bottom-0">Amount</th>
                                    <th class="wd-15p border-bottom-0">UTR</th>
                                    <th class="wd-15p border-bottom-0">Purchase Type</th>
                                    <th class="wd-15p border-bottom-0">Status</th>

                                </tr>
                                </thead>
                            </table>

                            <script type="text/javascript">
                                $(document).ready(function(){
                                    $('#my_table').DataTable({
                                        "order": [[ 1, "desc" ]],
                                        processing: true,
                                        serverSide: true,
                                        ajax: "{{ $urls }}",
                                        columns: [
                                            { data: 'id' },
                                            { data: 'created_at' },
                                            { data: 'user' },
                                            { data: 'api_name' },
                                            { data: 'bank_name' },
                                            { data: 'amount' },
                                            { data: 'utr' },
                                            { data: 'purchase_type' },
                                            { data: 'status' },
                                        ]
                                    });
                                    $("input[type='search']").wrap("<form>");
                                    $("input[type='search']").closest("form").attr("autocomplete","off");
                                });
                            </script>
                        </div>
                    </div>
                </div>
            </div>
            <!--/div-->

        </div>

    </div>
    </div>
    </div>


    {{--add provider model--}}
    <div class="modal fade" id="purchase_balance_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2" aria-hidden="true">
        <div class="modal-dialog modal-dialog-slideout" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Purchase Balance</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">




                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Api</label>
                                    <select class="form-control select2" id="api_id" style="width: 100%">
                                        @foreach($apis as $value)
                                            <option value="{{ $value->id }}">{{ $value->api_name }}</option>
                                        @endforeach
                                    </select>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="api_id_errors"></li>
                                    </ul>
                                </div>
                            </div>


                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name">Banks</label>
                                    <select class="form-control select2" id="masterbank_id" style="width: 100%">
                                        @foreach($banks as $value)
                                            <option value="{{ $value->id }}">{{ $value->bank_name }}</option>
                                        @endforeach
                                    </select>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="masterbank_id_errors"></li>
                                    </ul>
                                </div>
                            </div>


                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">UTR</label>
                                    <input type="text" class="form-control" id="utr" placeholder="Bank UTR Number">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="utr_errors"></li>
                                    </ul>
                                </div>
                            </div>


                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Amount</label>
                                    <input type="text" class="form-control" id="amount" placeholder="Amount">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="amount_errors"></li>
                                    </ul>
                                </div>
                            </div>


                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Login Password</label>
                                    <input type="password" class="form-control" id="password" placeholder="Login Password">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="password_errors"></li>
                                    </ul>
                                </div>
                            </div>



                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="purchase_btn" onclick="purchase_balance()">Purchase Balance</button>
                    <button class="btn btn-primary" type="button"  id="purchase_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                </div>
            </div>
        </div>
    </div>

    @include('admin.report.transaction_refund_model')


@endsection