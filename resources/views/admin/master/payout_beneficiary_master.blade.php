@extends('admin.layout.header')
@section('content')
    <script type="text/javascript">
        $(document).ready(function () {
            $("#status_id").select2();
        });
        
        function update_status(id) {
            var status_id = $("#statuss_"+id).val();
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id + '&status_id=' + status_id +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/update-payout-beneficiary')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
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

                        <form action="{{url('admin/payout-beneficiary-master')}}" method="get">
                            <div class="row">
                                <div class="col-lg-4 col-md-8 form-group mg-b-0">
                                    <label class="form-label">Status: <span class="tx-danger">*</span></label>
                                    <select class="form-control select2" id="status_id" name="status_id">
                                        <option value="1" @if($status_id == 1) selected @endif>Active</option>
                                        <option value="2" @if($status_id == 2) selected @endif>Reject</option>
                                        <option value="3" @if($status_id == 3) selected @endif>Pending</option>
                                    </select>
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
                            <h4 class="card-title mg-b-2 mt-2">Payout Beneficiary Master</h4>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="@if(Auth::User()->company->table_format == 1)table text-md-nowrap @else display responsive nowrap @endif" id="example1">
                                <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0">User Name</th>
                                    <th class="wd-15p border-bottom-0">Date</th>
                                    <th class="wd-15p border-bottom-0">Mobile Number</th>
                                    <th class="wd-15p border-bottom-0">Account Number</th>
                                    <th class="wd-15p border-bottom-0">Holder Name</th>
                                    <th class="wd-15p border-bottom-0">IFSC Code</th>
                                    <th class="wd-15p border-bottom-0">Status</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($payoutbeneficiary as $value)
                                    <tr>
                                        <td>{{ $value->user->name  }} {{ $value->user->last_name  }}</td>
                                        <td>{{ $value->created_at }}</td>
                                        <td>{{ $value->mobile_number }}</td>
                                        <td>{{ $value->account_number }}</td>
                                        <td>{{ $value->holder_name }}</td>
                                        <td>{{ $value->ifsc_code }}</td>
                                        <td>
                                            <select class="form-control" id="statuss_{{ $value->id }}" onchange="update_status({{ $value->id }})">
                                                <option value="1" @if($value->status_id == 1) selected @endif>Active</option>
                                                <option value="2" @if($value->status_id == 2) selected @endif>Reject</option>
                                                <option value="3" @if($value->status_id == 3) selected @endif>Pending</option>
                                            </select>
                                            <th></th>
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


@endsection