@extends('agent.layout.header')
@section('content')

    <script type="text/javascript">
        $(document).ready(function () {
            $("#payment_date").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: ('yy-mm-dd'),
            });

            $("#bankdetail_id").select2();
            $("#paymentmethod_id").select2();
        });

        function amountToWords() {
            var a = ['', 'one ', 'two ', 'three ', 'four ', 'five ', 'six ', 'seven ', 'eight ', 'nine ', 'ten ', 'eleven ', 'twelve ', 'thirteen ', 'fourteen ', 'fifteen ', 'sixteen ', 'seventeen ', 'eighteen ', 'nineteen '];
            var b = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];

            var num = $("#amount").val();
            if ((num = num.toString()).length > 9) return 'overflow';
            n = ('000000000' + num).substr(-9).match(/^(\d{2})(\d{2})(\d{2})(\d{1})(\d{2})$/);
            if (!n) return;
            var str = '';
            str += (n[1] != 0) ? (a[Number(n[1])] || b[n[1][0]] + ' ' + a[n[1][1]]) + 'crore ' : '';
            str += (n[2] != 0) ? (a[Number(n[2])] || b[n[2][0]] + ' ' + a[n[2][1]]) + 'lakh ' : '';
            str += (n[3] != 0) ? (a[Number(n[3])] || b[n[3][0]] + ' ' + a[n[3][1]]) + 'thousand ' : '';
            str += (n[4] != 0) ? (a[Number(n[4])] || b[n[4][0]] + ' ' + a[n[4][1]]) + 'hundred ' : '';
            str += (n[5] != 0) ? ((str != '') ? 'and ' : '') + (a[Number(n[5])] || b[n[5][0]] + ' ' + a[n[5][1]]) + 'only ' : '';
            $("#amountToWordsText").text(str);
        }

        function payment_request() {
            var token = $("input[name=_token]").val();
            var bankdetail_id = $("#bankdetail_id").val();
            var paymentmethod_id = $("#paymentmethod_id").val();
            var payment_date = $("#payment_date").val();
            var amount = $("#amount").val();
            var bankref = $("#bankref").val();
            var latitude = $("#inputLatitude").val();
            var longitude = $("#inputLongitude").val();
            if (latitude && longitude) {
                $(".loader").show();
                var dataString = 'bankdetail_id=' + bankdetail_id + '&paymentmethod_id=' + paymentmethod_id + '&payment_date=' + payment_date + '&amount=' + amount + '&bankref=' + bankref + '&latitude=' + latitude + '&longitude=' + longitude + '&_token=' + token;
                $.ajax({
                    type: "POST",
                    url: "{{url('admin/save-payment-request')}}",
                    data: dataString,
                    success: function (msg) {
                        $(".loader").hide();
                        if (msg.status == 'success') {
                            swal("Success", msg.message, "success");
                            setTimeout(function () {
                                location.reload(1);
                            }, 3000);
                        } else if (msg.status == 'validation_error') {
                            $("#bankdetail_id_errors").text(msg.errors.bankdetail_id);
                            $("#paymentmethod_id_errors").text(msg.errors.paymentmethod_id);
                            $("#payment_date_errors").text(msg.errors.payment_date);
                            $("#amount_errors").text(msg.errors.amount);
                            $("#bankref_errors").text(msg.errors.bankref);
                        } else {
                            swal("Faild", msg.message, "error");
                        }
                    }
                });
            } else {
                getLocation();
                alert('Please allow this site to access your location');
            }
        }

    </script>

    <div class="main-content-body">

        <div class="row">
            <div class="col-lg-5 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">{{ $page_title }}</h6>
                            <hr>
                        </div>
                        <div class="mb-4">
                            <label>Bank Name</label>
                            <select class="form-control select2" id="bankdetail_id">
                                @foreach($bankdetails as $value)
                                    <option value="{{ $value->id }}">{{ $value->bank_name }}</option>
                                @endforeach
                            </select>
                            <ul class="parsley-errors-list filled">
                                <li class="parsley-required" id="bankdetail_id_errors"></li>
                            </ul>
                        </div>


                        <div class="mb-4">
                            <label>Payment Method</label>
                            <select class="form-control select2" id="paymentmethod_id">
                                @foreach($methods as $value)
                                    <option value="{{ $value->id }}">{{ $value->payment_type }}</option>
                                @endforeach
                            </select>
                            <ul class="parsley-errors-list filled">
                                <li class="parsley-required" id="paymentmethod_id_errors"></li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <label>Payment Date</label>
                            <input type="text" class="form-control" placeholder="Payment Date" id="payment_date" autocomplete="off" value="{{Carbon\Carbon::today()->format('Y-m-d')}}">
                            <ul class="parsley-errors-list filled">
                                <li class="parsley-required" id="payment_date_errors"></li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <label>Amount</label>
                            <input type="text" class="form-control" placeholder="Amount" id="amount" onkeyup="amountToWords();">
                            <ul class="parsley-errors-list filled">
                                <li class="parsley-required" id="amount_errors"></li>
                            </ul>
                            <strong style="color: red;" id="amountToWordsText"></strong>
                        </div>

                        <div class="mb-4">
                            <label>Bank Ref Number</label>
                            <input type="text" class="form-control" placeholder="Bank Ref Number" id="bankref">
                            <ul class="parsley-errors-list filled">
                                <li class="parsley-required" id="bankref_errors"></li>
                            </ul>
                        </div>


                    </div>

                    <div class="modal-footer">
                        <button class="btn ripple btn-primary" type="button" onclick="payment_request()">Save Now</button>
                        <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                    </div>
                </div>
            </div>




            <div class="col-lg-7 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Bank Details</h6>
                            <hr>
                        </div>
                        <div class="table-responsive">
                            <table class="table text-md-nowrap" id="example1">
                                <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0">Bank Name</th>
                                    <th class="wd-25p border-bottom-0">Account Name</th>
                                    <th class="wd-25p border-bottom-0">Account Number</th>
                                    <th class="wd-25p border-bottom-0">IFsc code</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(Auth::User()->company->collection == 1)
                                    <tr>
                                        <td>Auto Payment (ICICI BANK)</td>
                                        <td>{{ Auth::User()->name }} {{ Auth::User()->last_name }}</td>
                                        <td>{{Auth::User()->company->icici_code}}{{Auth::User()->mobile}}</td>
                                        <td>ICIC0000106</td>
                                    </tr>
                                    @endif

                                @foreach($bankdetails as $value)
                                    <tr>
                                        <td>{{ $value->bank_name }}</td>
                                        <td>{{ $value->bank_account_name }}</td>
                                        <td>{{ $value->bank_account_number }}</td>
                                        <td>{{ $value->bank_ifsc }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>


                    </div>
                </div>
            </div>



            <div class="col-lg-12 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Payment Request</h6>
                            <hr>
                        </div>
                        <div class="table-responsive">
                            <table class="table text-md-nowrap" id="example2" data-order='[[ 0, "desc" ]]'>
                                <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0">Id</th>
                                    <th class="wd-15p border-bottom-0">Status</th>
                                    <th class="wd-15p border-bottom-0">Request Date</th>
                                    <th class="wd-15p border-bottom-0">Payment Date</th>
                                    <th class="wd-15p border-bottom-0">Bank</th>
                                    <th class="wd-15p border-bottom-0">Method</th>
                                    <th class="wd-15p border-bottom-0">Amount</th>
                                    <th class="wd-15p border-bottom-0">UTR</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($loadcash as $value)
                                    <tr>
                                        <td>{{ $value->id }}</td>
                                        <td><span class="{{ $value->status->class }}">{{ $value->status->status }}</span></td>
                                        <td>{{ $value->created_at }}</td>
                                        <td>{{ $value->payment_date }}</td>
                                        <td>{{ $value->bankdetail->bank_name }}</td>
                                        <td>{{ $value->paymentmethod->payment_type }}</td>
                                        <td>{{ number_format($value->amount,2)}}</td>
                                        <td>{{ $value->bankref }}</td>
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


    @include('agent.service.recharge_confirm')

@endsection