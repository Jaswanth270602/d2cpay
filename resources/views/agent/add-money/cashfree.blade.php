@extends('agent.layout.header')
@section('content')


    <script type="text/javascript">
        function order_gateway() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var amount = $("#amount").val();
            var dataString = 'amount=' + amount + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/add-money/v1/create-order')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        window.location.href = msg.payment_link;
                    } else if (msg.status == 'validation_error') {
                        $("#amount_errors").text(msg.errors.amount);
                    } else {
                        swal("Faild", msg.message, "error");
                    }
                }
            });
        }
    </script>

    <div class="main-content-body">

        <div class="row">
            <div class="col-lg-4 col-md-12">
                @if(Session::has('success'))
                    <div class="alert alert-success">
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

                <div class="card">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">{{ $page_title }}</h6>
                            <hr>
                        </div>


                        <div class="mb-4">
                            <label>Amount</label>
                            <input type="text" class="form-control" placeholder="Amount" name="amount" id="amount">
                            <ul class="parsley-errors-list filled">
                                <li class="parsley-required" id="amount_errors"></li>
                            </ul>
                        </div>


                    </div>

                    <div class="modal-footer">
                        <button class="btn ripple btn-primary" type="button" onclick="order_gateway()">Add Money
                        </button>
                        <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                    </div>

                </div>
            </div>


            <div class="col-lg-8 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Transaction</h6>
                            <hr>
                        </div>
                        <div class="payment_page">
                            <div class="table-responsive">
                                <table class="@if(Auth::User()->company->table_format == 1)table text-md-nowrap @else display responsive nowrap @endif"
                                       id="example1" data-order='[[ 0, "desc" ]]'>
                                    <thead>
                                    <tr>
                                        <th class="wd-15p border-bottom-0">ID</th>
                                        <th class="wd-15p border-bottom-0">Date</th>
                                        <th class="wd-15p border-bottom-0">Amount</th>
                                        <th class="wd-15p border-bottom-0">Mode</th>
                                        <th class="wd-15p border-bottom-0">Status</th>
                                        <th class="wd-15p border-bottom-0">Remark</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($gatewayorders as $value)
                                        <tr>
                                            <td>{{ $value->id }}</td>
                                            <td>{{ $value->created_at }}</td>
                                            <td>{{ number_format($value->amount, 2) }}</td>
                                            <td>{{ $value->mode }}</td>
                                            <td>
                                                <span class="{{ $value->status->class }}">{{ $value->status->status }}</span>
                                            </td>
                                            <td>{{ $value->remark }}</td>
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
    </div>
    </div>



@endsection