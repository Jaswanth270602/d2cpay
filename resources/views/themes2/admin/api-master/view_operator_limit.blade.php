@extends('themes2.admin.layout.header')
@section('content')
    <script type="text/javascript">
        function update_limit(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var amountlimit = $("#amountlimit_" + id).val();
            var providerstatus = $("#providerstatus_" + id).val();
            var status = $("#status_" + id).val();
            var limittiming = $("#limittiming_" + id).val();
            var dailylimit = $("#dailylimit_" + id).val();
            var dataString = 'id=' + id + '&amount_limit=' + amountlimit + '&provider_status=' + providerstatus + '&status_id=' + status + '&limit_timing=' + limittiming + '&daily_limit=' + dailylimit + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/update-operator-limit')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        // setTimeout(function () { location.reload(1); }, 3000);
                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function change_daily_value(id) {
            var limit_timing = $("#limittiming_" + id).val();
            if (limit_timing == 1) {
                $("#dailylimit_" + id).prop('disabled', false)
            } else {
                $("#dailylimit_" + id).prop('disabled', true)
            }
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
                        <div class="widget-heading">
                            <a href="{{url()->previous()}}" class="btn btn-danger"><< Back</a>
                        </div>
                        <hr>
                        <div class="widget-content">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table text-md-nowrap" id="example1">
                                        <thead>
                                        <tr>
                                            <th class="wd-15p border-bottom-0">User Name</th>
                                            <th class="wd-15p border-bottom-0">Provider</th>
                                            <th class="wd-15p border-bottom-0">Service</th>
                                            <th class="wd-15p border-bottom-0">Limit Type</th>
                                            <th class="wd-15p border-bottom-0">Daily Value</th>
                                            <th class="wd-15p border-bottom-0">Limit</th>
                                            <th class="wd-15p border-bottom-0">Provider Status</th>
                                            <th class="wd-15p border-bottom-0">limit Status</th>
                                            <th class="wd-15p border-bottom-0">action</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($providerlimit as $value)
                                            <tr>
                                                <td>{{ $value->user->name.' '. $value->user->last_name }}</td>
                                                <td>{{ $value->provider->provider_name }}</td>
                                                <td>{{ $value->provider->service->service_name }}</td>

                                                <td>
                                                    <select class="form-control" id="limittiming_{{ $value->id }}" onchange="change_daily_value({{ $value->id }})" style="width: 100px;">
                                                        <option value="0"  @if($value->limit_timing == 0) selected @endif>Once</option>
                                                        <option value="1" @if($value->limit_timing == 1) selected @endif>Daily Value</option>
                                                    </select>
                                                </td>

                                                <td><input type="number" class="form-control" id="dailylimit_{{ $value->id }}" value="{{ $value->daily_limit }}" @if($value->limit_timing == 0) disabled  @endif style="width: 100px;"></td>


                                                <td><input type="number" class="form-control" id="amountlimit_{{ $value->id }}" value="{{ $value->amount_limit }}" style="width: 100px;"></td>
                                                <td>
                                                    <select class="form-control" id="providerstatus_{{ $value->id }}" style="width: 120px;">
                                                        <option value="1" @if($value->provider_status == 1) selected @endif>Enabled</option>
                                                        <option value="0"  @if($value->provider_status == 0) selected @endif>Disabled</option>
                                                    </select>
                                                </td>

                                                <td>
                                                    <select class="form-control" id="status_{{$value->id}}" style="width: 130px;">
                                                        <option value="1" @if($value->status_id == 1) selected @endif>Enabled</option>
                                                        <option value="0"  @if($value->status_id == 0) selected @endif>Disabled</option>
                                                    </select>
                                                </td>
                                                <td><button type="button" class="btn btn-success" onclick="update_limit({{$value->id}})">Update</button></td>
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
        <!-- Main Body Ends -->


@endsection