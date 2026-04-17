@extends('themes2.admin.layout.header')
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


    <!--  Content Area Starts  -->
    <div id="content" class="main-content">

        <!-- Main Body Starts -->
    @include('themes2.admin.layout.breadcrumb')
    <!-- Main Body Starts -->
        <div class="layout-px-spacing">
            <div class="row layout-top-spacing">
                <!-- REVENUE ENDS-->
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
                    <div class="widget dashboard-table">
                        <div class="widget-content">
                            <form action="{{url('admin/payout-beneficiary-master')}}" method="get">
                                <div class="form-group">
                                    <label>Search By Status</label>
                                    <div class="input-group">
                                        <select class="form-control select2" id="status_id" name="status_id">
                                            <option value="1" @if($status_id == 1) selected @endif>Active</option>
                                            <option value="2" @if($status_id == 2) selected @endif>Reject</option>
                                            <option value="3" @if($status_id == 3) selected @endif>Pending</option>
                                        </select>
                                        <div class="input-group-append">
                                            <button type="submit" class="input-group-text btn btn-primary">Search</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>




                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
                    <div class="widget dashboard-table">
                        <div class="widget-heading">
                            <h5 class=""> {{ $page_title }}</h5>
                        </div>
                        <hr>
                        <div class="widget-content">
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
                                            </td>
                                            <td></td>
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
        <!-- Main Body Ends -->


@endsection