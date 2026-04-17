@extends('admin.layout.header')
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
            var dataString = 'role_id=' + role_id +  '&_token=' + token;
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

                    }else{
                        alert(msg.message);
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

                        <form action="{{url('admin/user-operator-limit')}}" method="get">
                        <div class="row">
                            <div class="col-lg-5 col-md-8 form-group mg-b-0">
                                <label class="form-label">Member Type: <span class="tx-danger">*</span></label>
                                <select class="form-control select2" id="role_id" name="role_id" onchange="get_users(this)">
                                    <option value="0" @if($role_id == 0) selected @endif>All Type</option>
                                    @foreach($roles as $value)
                                        <option value="{{ $value->id }}" @if($role_id == $value->id) selected @endif>{{ $value->role_title }} </option>
                                    @endforeach
                                </select>

                            </div>

                            <div class="col-lg-5 col-md-8 form-group mg-b-0">
                                <label class="form-label">User: <span class="tx-danger">*</span></label>
                                <select class="form-control select2" id="user_id" name="user_id">
                                    <option value="0" @if($user_id == 0) selected @endif>All Users</option>
                                    @foreach($users as $value)
                                        <option value="{{ $value->id }}" @if($user_id == $value->id) selected @endif>{{ $value->name.' '. $value->last_name .' - '. $value->mobile}} ({{ $value->role->role_title }})</option>
                                    @endforeach
                                </select>
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
                            <h4 class="card-title mg-b-2 mt-2">{{ $page_title }} List</h4>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">


                            <table id='my_table' class="@if(Auth::User()->company->table_format == 1)table text-md-nowrap @else display responsive nowrap @endif">
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
            <!--/div-->

        </div>

    </div>
    </div>
    </div>



@endsection