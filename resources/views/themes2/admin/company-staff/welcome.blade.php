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
                        </div>
                        <hr>
                        <div class="widget-content">
                            <div class="table-responsive">
                                <table class="@if(Auth::User()->company->table_format == 1)table text-md-nowrap @else display responsive nowrap @endif" id="my_table">
                                    <thead>
                                    <tr>
                                        <td>User Id</td>
                                        <td>Joining Date</td>
                                        <td>Name</td>
                                        <td>Mobile</td>
                                        <td>Member Type</td>
                                        <td>Normal Balance</td>
                                        <td>Status</td>
                                        <td>Permission</td>
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
                                                { data: 'status' },
                                                { data: 'permission' },
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