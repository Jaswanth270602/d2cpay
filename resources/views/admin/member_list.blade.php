@extends('admin.layout.header')
@section('content')
    <script type="text/javascript">
        function adminUpdatePackage(user_id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var scheme_id = $("#packageId_" + user_id).val();
            var dataString = 'scheme_id=' + scheme_id + '&user_id=' + user_id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/update-dropdown-package')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () {
                            location.reload(1);
                        }, 1000);
                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function adminUpdateParent(user_id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var parent_id = $("#parentId_" + user_id).val();
            var dataString = 'parent_id=' + parent_id + '&user_id=' + user_id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/update-dropdown-parent')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () {
                            location.reload(1);
                        }, 1000);
                    } else {
                        swal("Failed", msg.message, "error");
                    }
                }
            });

        }
    </script>


    <div class="main-content-body">
        <div class="row row-sm">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">{{ $page_title }} List</h4>

                            @if(Auth::User()->role_id == 1)
                                <button class="btn btn-danger btn-sm" type="button"  data-toggle="modal" onclick="logoutAllUsers()"> Logout All Users</button>
                            @endif

                            @if(Auth::User()->role_id == 2 && $permission_download_member == 1)
                                <button class="btn btn-primary btn-sm" type="button"  data-toggle="modal" data-target="#member_download_model"> Download {{ $page_title }}</button>
                            @elseif(Auth::user()->role_id != 2)
                                <button class="btn btn-primary btn-sm" type="button"  data-toggle="modal" data-target="#member_download_model"> Download {{ $page_title }}</button>
                            @endif



                            @if(Auth::User()->role_id == 2 && $permission_create_member == 1)
                                <a href="{{url('admin/create-user')}}/{{ $role_slug }}" class="btn btn-danger btn-sm"> Create {{ $page_title }}</a>
                            @elseif(Auth::user()->role_id != 2)
                                <a href="{{url('admin/create-user')}}/{{ $role_slug }}" class="btn btn-danger btn-sm"> Create {{ $page_title }}</a>
                            @endif



                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">


                            <table id='my_table' class="display responsive nowrap" data-order='[[ 0, "desc" ]]'>
                                <thead>
                                <tr>
                                    <td>User Id</td>
                                    <td>Joining Date</td>
                                    <td>Name</td>
                                    <td>Mobile</td>
                                    <td>Member Type</td>
                                    <td>Normal Balance</td>
                                    <td>Parent</td>
                                    <td>Package</td>
                                    <td>Status</td>
                                    <td>Aadhar Verify</td>
                                    <td>Pan Verify</td>
                                    <td>Care Of</td>
                                    <td>DOB</td>
                                    <td>Gender</td>
                                    <td>User Activity</td>
                                    <td>Statement</td>
                                </tr>
                                </thead>
                            </table>

                            <!-- Script -->
                            <script type="text/javascript">
                                $(document).ready(function(){
                                    $('#my_table').DataTable({
                                        processing: true,
                                        serverSide: true,
                                        ajax: "{{ $url }}",
                                        columns: [
                                            { data: 'id' },
                                            { data: 'joining_date' },
                                            { data: 'name' },
                                            { data: 'mobile_number' },
                                            { data: 'member_type' },
                                            { data: 'user_balance' },
                                            { data: 'parent_name' },
                                            { data: 'package_name' },
                                            { data: 'status' },
                                            { data: 'isAadharVerify' },
                                            { data: 'isPanVerify' },
                                            { data: 'care_of' },
                                            { data: 'dob' },
                                            { data: 'gender' },
                                            { data: 'is_online' },
                                            { data: 'statement' },
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

    @include('common.adminMemberViewModel')
@endsection