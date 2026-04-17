@extends('admin.layout.header')
@section('content')





<div class="main-content-body">
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


                        <table id='my_table' class="display responsive nowrap">
                            <thead>
                            <tr>
                                <td>User Id</td>
                                <td>Name</td>
                                <td>Mobile</td>
                                <td>Balance</td>
                                <td>Member Type</td>
                                <td>Last Transaction</td>
                                <td>Statement</td>
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
                                    ajax: "{{url('admin/not-working-users-api')}}",
                                    columns: [
                                        { data: 'id' },
                                        { data: 'username' },
                                        { data: 'mobile' },
                                        { data: 'balance' },
                                        { data: 'member_type' },
                                        { data: 'last_date' },
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


@include('admin.member_view_model')

@endsection