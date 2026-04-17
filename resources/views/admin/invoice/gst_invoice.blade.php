@extends('admin.layout.header')
@section('content')


<script type="text/javascript">
        $(document).ready(function () {
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
        
        function create_invoice() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = '_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/invoice/create-invoice')}}",
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
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="card">
                <div class="card-body">

                    <div class="row">


                        <div class="col-lg-3 col-md-8 form-group mg-b-0">
                            <label class="form-label">From: <span class="tx-danger">*</span></label>
                            <input class="form-control fc-datepicker" value="{{ $fromdate }}" type="text" id="fromdate" name="fromdate" autocomplete="off">
                        </div>

                        <div class="col-lg-3 col-md-8 form-group mg-b-0">
                            <label class="form-label">To: <span class="tx-danger">*</span></label>
                            <input class="form-control fc-datepicker" value="{{ $todate }}" type="text" id="todate"  name="todate" autocomplete="off">
                        </div>

                        <div class="col-lg-2 col-md-4 mg-t-10 mg-sm-t-25">
                            <button class="btn btn-primary pd-x-20" type="button"> Search</button>
                        </div>
                        <div class="col-lg-4 col-md-4 mg-t-10 mg-sm-t-25">
                            <button class="btn btn-danger pd-x-20" type="button" onclick="create_invoice()">{{ $create_name }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-sm">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between">
                        <h4 class="card-title mg-b-2 mt-2">Invoice</h4>
                        <i class="mdi mdi-dots-horizontal text-gray"></i>
                    </div>
                    <hr>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table text-md-nowrap" id="example1">
                            <thead>
                            <tr>
                                <th class="wd-15p border-bottom-0">Id</th>
                                <th class="wd-25p border-bottom-0">Business Name</th>
                                <th class="wd-25p border-bottom-0">Month</th>
                                <th class="wd-25p border-bottom-0">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($invoices as $value)
                                <tr>
                                    <td>{{ $value->invoice_id}}</td>
                                    <td>{{ $value->user->member->shop_name}}</td>
                                    <td>{{ $value->invoice_month}}</td>
                                    <td><a href="{{ url('admin/invoice/generate-invoice')}}/{{ $value->id}}" class="btn btn-danger btn-sm" >Download</a></td>
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





@endsection