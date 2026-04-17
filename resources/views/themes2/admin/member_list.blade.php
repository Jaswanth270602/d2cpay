@extends('themes2.admin.layout.header')
@section('content')
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

                    </div>
                    <hr>
                    <div class="widget-content">
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
            </div>

        </div>
    </div>
    <!-- Main Body Ends -->

    @include('common.adminMemberViewModel')
    @endsection