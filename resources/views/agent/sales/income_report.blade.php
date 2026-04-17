@extends('agent.layout.header')
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

            $("#wallet_type").select2();
        });



    </script>



    <div class="main-content-body">

        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card">
                    <div class="card-body">

                        <form action="{{url('agent/income-report')}}" method="get">
                            <div class="row">
                                <div class="col-lg-3 col-md-8 form-group mg-b-0">
                                    <label class="form-label">From: <span class="tx-danger">*</span></label>
                                    <input class="form-control fc-datepicker" value="{{ $fromdate }}" type="text" id="fromdate" name="fromdate" autocomplete="off">
                                </div>

                                <div class="col-lg-3 col-md-8 form-group mg-b-0">
                                    <label class="form-label">To: <span class="tx-danger">*</span></label>
                                    <input class="form-control fc-datepicker" value="{{ $todate }}" type="text" id="todate"  name="todate" autocomplete="off">
                                </div>

                                <div class="col-lg-3 col-md-8 form-group mg-b-0">
                                    <label class="form-label">Wallet Type: <span class="tx-danger">*</span></label>
                                    <select class="form-control select2" id="wallet_type" name="wallet_type">
                                        <option value="1" @if($wallet_type == 1) selected @endif>Normal Wallet</option>
                                        @if(Auth::User()->company->aeps == 1 && Auth::User()->profile->aeps == 1)
                                            <option value="2" @if($wallet_type == 2) selected @endif>Aeps Wallet</option>
                                        @endif
                                    </select>
                                </div>

                                <div class="col-lg-3 col-md-4 mg-t-10 mg-sm-t-25">
                                    <button class="btn btn-main-primary pd-x-20" type="submit"><i class="fas fa-search"></i> Search</button>
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
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="display responsive nowrap" id="my_table">
                                <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0">User Id</th>
                                    <th class="wd-15p border-bottom-0">Name</th>
                                    <th class="wd-15p border-bottom-0">Opening Balance</th>
                                    <th class="wd-15p border-bottom-0">Credit Amount</th>
                                    <th class="wd-15p border-bottom-0">Debit Amount</th>
                                    <th class="wd-15p border-bottom-0">Sales</th>
                                    <th class="wd-15p border-bottom-0">Profit</th>
                                    <th class="wd-15p border-bottom-0">Charges</th>
                                    <th class="wd-15p border-bottom-0">Pending</th>
                                    <th class="wd-15p border-bottom-0">Closing Bal</th>

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
                                            { data: 'name' },
                                            { data: 'opening_balance' },
                                            { data: 'credit_amount' },
                                            { data: 'debit_amount' },
                                            { data: 'sales' },
                                            { data: 'profit' },
                                            { data: 'charges' },
                                            { data: 'pending' },
                                            { data: 'closing_bal' },

                                        ]
                                    });

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




@endsection