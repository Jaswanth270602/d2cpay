@extends('admin.layout.header')
@section('content')
    <script type="text/javascript">
        $(document).ready(function ()
        {
            $("#user_id").select2();
        });

        function saveData() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var user_id = $("#user_id").val();
            var minimum_amount = $("#minimum_amount").val();
            var maximum_amount = $("#maximum_amount").val();
            var status_id = $("#status_id").val();
            var api_id = $("#api_id").val();
            var dataString = '&user_id=' + user_id + '&minimum_amount=' + minimum_amount + '&maximum_amount=' + maximum_amount + '&status_id=' + status_id + '&api_id=' + api_id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/bank-transfer-switching-store')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () {
                            location.reload(1);
                        }, 3000);
                    } else if (msg.status == 'validation_error') {
                        $("#minimum_amount_errors").text(msg.errors.minimum_amount);
                        $("#maximum_amount_errors").text(msg.errors.maximum_amount);
                        $("#status_id_errors").text(msg.errors.status_id);
                        $("#api_id_errors").text(msg.errors.api_id);
                    } else {
                        swal("Faild", msg.message, "error");
                    }
                }
            });
        }

        function deleteData (id){
            var r = confirm("Are you sure you want delete this?");
            if (r == true) {
                $(".loader").show();
                var token = $("input[name=_token]").val();
                var dataString = 'id=' + id + '&_token=' + token;
                $.ajax({
                    type: "POST",
                    url: "{{url('admin/bank-transfer-switching-delete')}}",
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
        }
    </script>

    <div class="main-content-body">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card">
                    <div class="card-body">

                        <div class="row">

                            <div class="col-lg-2 col-md-8 form-group mg-b-0">
                                <label class="form-label">Select User: <span class="tx-danger">*</span></label>
                                <select class="form-control select2" id="user_id" style="width: 100%;">
                                    <option value="0">All User</option>
                                    @foreach($users as $value)
                                        <option value="{{ $value->id }}">{{ $value->name }} {{ $value->last_name }}</option>
                                    @endforeach
                                </select>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="user_id_errors"></li>
                                </ul>
                            </div>

                            <div class="col-lg-2 col-md-8 form-group mg-b-0">
                                <label class="form-label">Minimum Amount: <span class="tx-danger">*</span></label>
                                <input type="text" class="form-control" id="minimum_amount"
                                       placeholder="Minimum Amount">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="minimum_amount_errors"></li>
                                </ul>
                            </div>

                            <div class="col-lg-2 col-md-8 form-group mg-b-0">
                                <label class="form-label">Maximum Amount: <span class="tx-danger">*</span></label>
                                <input type="text" class="form-control" id="maximum_amount"
                                       placeholder="Maximum Amount">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="maximum_amount_errors"></li>
                                </ul>
                            </div>


                            <div class="col-lg-2 col-md-8 form-group mg-b-0">
                                <label class="form-label">Api: <span class="tx-danger">*</span></label>
                                <select class="form-control" id="api_id">
                                    <option value="">Select Api</option>
                                    @foreach($apis as $value)
                                        <option value="{{ $value->id }}">{{ $value->api_name }}</option>
                                    @endforeach
                                </select>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="api_id_errors"></li>
                                </ul>
                            </div>

                            <div class="col-lg-2 col-md-8 form-group mg-b-0">
                                <label class="form-label">Status: <span class="tx-danger">*</span></label>
                                <select class="form-control" id="status_id">
                                    <option value="1">Enable</option>
                                    <option value="0">Disable</option>
                                </select>
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="status_id_errors"></li>
                                </ul>
                            </div>

                            <div class="col-lg-2 col-md-4 mg-t-10 mg-sm-t-25">
                                <button class="btn btn-main-primary pd-x-20" type="button" onclick="saveData()">Save
                                </button>
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
                            <h4 class="card-title mg-b-2 mt-2">Backup Api Master</h4>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table text-md-nowrap" id="example1">
                                <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0">User</th>
                                    <th class="wd-15p border-bottom-0">Api</th>
                                    <th class="wd-15p border-bottom-0">Min Amount</th>
                                    <th class="wd-15p border-bottom-0">Max Amount</th>
                                    <th class="wd-15p border-bottom-0">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($moneytransferswitchings as $value)

                                    @php
                                        $userDetails = App\Models\User::find($value->user_id);
                                        $name = $userDetails ? $userDetails->name . ' ' . $userDetails->last_name : 'All User';
                                    @endphp
                                    <tr>
                                        <td>{{ $name}}</td>
                                        <td>{{ $value->api->api_name }}</td>
                                        <td>{{ $value->minimum_amount }}</td>
                                        <td>{{ $value->maximum_amount }}</td>
                                        <td><button type="button" class="btn btn-danger btn-sm" onclick="deleteData({{ $value->id }})"> <i class="fas fa-trash-alt"></i> Delete</button></td>
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