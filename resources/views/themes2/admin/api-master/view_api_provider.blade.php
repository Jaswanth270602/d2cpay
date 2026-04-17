@extends('themes2.admin.layout.header')
@section('content')
    <script type="text/javascript">

        function  update_commission(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var operator_code = $("#operatorcode_"+id).val();
            var type = $("#type_"+id).val();
            var commission = $("#commission_"+id).val();
            var dataString = 'id=' + id + '&operator_code=' + operator_code + '&api_commission=' + commission + '&type=' + type + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/update-api-provider')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                    }else{
                        swal("Failed", msg.message, "error");
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
                        <div class="widget-heading">
                            <h5 class=""> {{ $page_title }}</h5>

                        </div>
                        <hr>
                        <div class="widget-content">
                            <div class="table-responsive">

                                <table class="table text-md-nowrap" id="example1">
                                    <thead>
                                    <tr>
                                        <th class="wd-15p border-bottom-0">Sr No</th>
                                        <th class="wd-15p border-bottom-0">Provider Name</th>
                                        <th class="wd-15p border-bottom-0">Service Name</th>
                                        <th class="wd-15p border-bottom-0">Api Name</th>
                                        <th class="wd-15p border-bottom-0">Operator Code</th>
                                        <th class="wd-20p border-bottom-0">Type</th>
                                        <th class="wd-15p border-bottom-0">Commission</th>
                                        <th class="wd-15p border-bottom-0">Action</th>

                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php $i = 1 ?>
                                    @foreach($apiprovider as $value)
                                        <tr>
                                            <td>{{ $i++ }}</td>
                                            <td>{{ $value->provider->provider_name }}</td>
                                            <td>{{ $value->provider->service->service_name }}</td>
                                            <td>{{ $value->api->api_name }}</td>
                                            <td> <input type="text"  value="{{ $value->operator_code }}" id="operatorcode_{{$value->id}}" class="form-control" placeholder="Operator Code"></td>
                                            <td><select class="form-control" id="type_{{ $value->id }}" style="width: 90px;">
                                                    <option value="0" @if($value->type == 0) {{ "selected" }} @endif>%</option>
                                                    <option value="1" @if($value->type == 1) {{ "selected" }} @endif>Rs</option>
                                                </select>
                                            </td>
                                            <td> <input type="text"  value="{{ $value->api_commission }}" class="form-control" id="commission_{{$value->id}}" placeholder="Commission"></td>
                                            <td><button class="btn btn-danger btn-sm" onclick="update_commission({{ $value->id }})">Update</button></td>
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