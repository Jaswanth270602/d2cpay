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

                        <form action="{{url('agent/operator-report')}}" method="get">
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
                            <table class="table text-md-nowrap" id="example1">
                                <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0">Sr No</th>
                                    <th class="wd-15p border-bottom-0">Provider</th>
                                    <th class="wd-15p border-bottom-0">Amount</th>
                                    <th class="wd-15p border-bottom-0">profit</th>
                                    <th class="wd-15p border-bottom-0">total count</th>
                                </tr>
                                </thead>
                                <tbody>

                                <?php $i = 1 ?>
                                @foreach($reports as $value)
                                    <tr>
                                        <td>{{ $i++ }}</td>
                                        <td>{{ $value->provider->provider_name }}</td>
                                        {{--<td><img src="https://cdn.bceres.com/provider-icon/{{$value->provider_id}}.png" style="height: 50px;" title="{{ $value->provider->provider_name }}"></td>--}}
                                        <td>{{ number_format($value->total_amount, 2) }}</td>
                                        <td>{{ number_format($value->total_profit, 2) }}</td>
                                        <td>{{ $value->all_count }}</td>
                                      </tr>
                                @endforeach
                               <tr style="background: linear-gradient(45deg, #f33057, #3858f9); color: white;">
                                   <td style="color: #3b4863">{{ $row_count + 1 }}</td>
                                   <td>Total</td>
                                   <td>{{ $total_amount }}</td>
                                   <td>{{ $total_profit }}</td>
                                   <td>{{ $all_count }}</td>
                               </tr>
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