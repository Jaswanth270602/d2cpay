@extends('admin.layout.header')
@section('content')




    <div class="main-content-body">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card">
                    <div class="card-body">


                        <form action="{{url('admin/report/v1/search-refund-manager')}}">
                        <div class="row">
                            <div class="col-lg-5 col-md-8 form-group mg-b-0">
                                <label class="form-label">Search Type: <span class="tx-danger">*</span></label>
                                <select class="form-control" name="search_type">
                                    <option value="1" @if($search_type == 1) selected @endif>Search By Number</option>
                                    <option value="2" @if($search_type == 2) selected @endif>Search By Report Id</option>
                                    <option value="3" @if($search_type == 3) selected @endif>Txnid</option>
                                </select>

                            </div>

                            <div class="col-lg-5 col-md-8 form-group mg-b-0">
                                <label class="form-label">Value: <span class="tx-danger">*</span></label>
                                <input type="text" class="form-control" placeholder="Enter Value" name="number" value="{{ $number }}">
                            </div>

                            <div class="col-lg-2 col-md-4 mg-t-10 mg-sm-t-25">
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
                            <table class="@if(Auth::User()->company->table_format == 1)table text-md-nowrap @else display responsive nowrap @endif" id="example1" data-order='[[ 0, "desc" ]]'>
                                <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0">Report ID</th>
                                    <th class="wd-15p border-bottom-0">Date</th>
                                    <th class="wd-15p border-bottom-0">User NAme</th>
                                    <th class="wd-15p border-bottom-0">Provider NAme</th>
                                    <th class="wd-15p border-bottom-0">Number</th>
                                    <th class="wd-15p border-bottom-0">Txnid</th>
                                    <th class="wd-15p border-bottom-0">Opening Blance</th>
                                    <th class="wd-15p border-bottom-0">Amount</th>
                                    <th class="wd-15p border-bottom-0">Profit</th>
                                    <th class="wd-15p border-bottom-0">Closing Balance</th>
                                    <th class="wd-15p border-bottom-0">Status</th>
                                    <th class="wd-15p border-bottom-0">Action</th>
                                </tr>
                                </thead>

                                @if($search == 'yes')
                                <tbody>
                                @foreach($report as $value)
                                    <tr>
                                        <td>{{ $value->id }}</td>
                                        <td>{{ $value->created_at }}</td>
                                        <td><a href="{{url('admin/user-ledger-report')}}/{{Crypt::encrypt($value->user_id)}}">{{ $value->user->name }} {{ $value->user->last_name }}</a></td>
                                        <td>{{ $value->provider->provider_name }}</td>
                                        <td>{{ $value->number }}</td>
                                        <td>{{ $value->txnid }}</td>
                                        <td>{{ number_format($value->opening_balance, 2) }}</td>
                                        <td>{{ number_format($value->amount, 2) }}</td>
                                        <td>{{ number_format($value->profit, 2) }}</td>
                                        <td>{{ number_format($value->total_balance	, 2) }}</td>
                                        <td><span class="{{ $value->status->class }}">{{ $value->status->status }}</span></td>
                                        <td><button class="btn btn-danger btn-sm" onclick="view_recharges({{ $value->id }})"><i class="fas fa-eye"></i> View</button></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                    @endif
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

    @include('admin.report.transaction_refund_model')

@endsection