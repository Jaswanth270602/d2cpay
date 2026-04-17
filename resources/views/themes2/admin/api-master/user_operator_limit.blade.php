@extends('themes2.admin.layout.header')
@section('content')
    <script type="text/javascript">
        $(document).ready(function () {
            $("#role_id").select2();
            $("#user_id").select2();
        });

        function get_users() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var role_id = $("#role_id").val();
            var dataString = 'role_id=' + role_id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/get-user-by-role')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        var users = msg.users;
                        var html = "";
                        html += '<option value="0">All Users</option>';
                        for (var key in users) {
                            html += '<option value="' + users[key].id + '">' + users[key].name + ' </option>';
                        }
                        $("#user_id").html(html);

                    } else {
                        alert(msg.message);
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
                            <div class="card-body">

                                <div class="widget-content widget-content-area">
                                    <div class="form-group row">
                                        <div class="col-lg-12 col-md-12 col-sm-12">
                                            <form action="{{url('admin/user-operator-limit')}}" method="get">
                                                <div class="form-row">
                                                    <div class="col-md-6 mb-4">
                                                        <label class="form-label">Member Type: <span class="tx-danger">*</span></label>
                                                        <select class="form-control select2" id="role_id" name="role_id" onchange="get_users(this)">
                                                            <option value="0" @if($role_id == 0) selected @endif>All Type</option>
                                                            @foreach($roles as $value)
                                                                <option value="{{ $value->id }}" @if($role_id == $value->id) selected @endif>{{ $value->role_title }} </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="col-md-6 mb-4">
                                                        <label class="form-label">User: <span class="tx-danger">*</span></label>
                                                        <select class="form-control select2" id="user_id" name="user_id">
                                                            <option value="0" @if($user_id == 0) selected @endif>All Users</option>
                                                            @foreach($users as $value)
                                                                <option value="{{ $value->id }}"
                                                                        @if($user_id == $value->id) selected @endif>{{ $value->name.' '. $value->last_name .' - '. $value->mobile}}({{ $value->role->role_title }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Search</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            </div>
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
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id='my_table' class="table text-md-nowrap">
                                        <thead>
                                        <tr>
                                            <td>User Id</td>
                                            <td>Name</td>
                                            <td>Email</td>
                                            <td>Mobile</td>
                                            <td>Role</td>
                                            <td>Action</td>

                                        </tr>
                                        </thead>
                                    </table>

                                    <!-- Script -->
                                    <script type="text/javascript">
                                        $(document).ready(function(){
                                            // DataTable
                                            $('#my_table').DataTable({
                                                processing: true,
                                                serverSide: true,
                                                ajax: "{{ $urls }}",
                                                columns: [
                                                    { data: 'id' },
                                                    { data: 'name' },
                                                    { data: 'email' },
                                                    { data: 'mobile' },
                                                    { data: 'role_type' },
                                                    { data: 'action' },
                                                ],
                                            });
                                            $("input[type='search']").wrap("<form>");
                                            $("input[type='search']").closest("form").attr("autocomplete","off");
                                        });
                                    </script>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!-- Main Body Ends -->


@endsection