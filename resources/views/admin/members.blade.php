@extends('admin.layout.header')
@section('content')



    <div class="main-content-body">
        <div class="row row-sm">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">All Member</h4>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                       <hr>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">


                            <table id='my_table' class="table text-md-nowrap">
                                <thead>
                                <tr>
                                    <td>S.no</td>
                                    <td>Username</td>
                                    <td>Name</td>
                                    <td>Email</td>
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
                                        ajax: "{{url('admin/get-all-members')}}/{{$page_title}}",
                                        columns: [
                                            { data: 'id' },
                                            { data: 'username' },
                                            { data: 'name' },
                                            { data: 'email' },
                                        ]
                                    });

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