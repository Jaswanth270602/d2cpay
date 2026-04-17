@extends('agent.layout.header')
@section('content')



    <div class="main-content-body">
        <div class="row row-sm">

            @include('agent.developer.left_side')

            <div class="col-lg-8 col-xl-9">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">{{ $page_title }}</h4>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="display responsive nowrap" id="my_table">
                                <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0">ID</th>
                                    <th class="wd-15p border-bottom-0">Date Time</th>
                                    <th class="wd-15p border-bottom-0">User Name</th>
                                    <th class="wd-15p border-bottom-0">First Name</th>
                                    <th class="wd-15p border-bottom-0">Last Name</th>
                                    <th class="wd-15p border-bottom-0">Mobile Number</th>
                                    <th class="wd-15p border-bottom-0">Email</th>
                                    <th class="wd-15p border-bottom-0">Aadhar Number</th>
                                    <th class="wd-15p border-bottom-0">Pan Number</th>
                                    <th class="wd-15p border-bottom-0">Shop Name</th>
                                    <th class="wd-15p border-bottom-0">Pin Code</th>
                                    <th class="wd-15p border-bottom-0">Address</th>
                                    <th class="wd-15p border-bottom-0">Account Number</th>
                                    <th class="wd-15p border-bottom-0">IFSC Code</th>
                                    <th class="wd-15p border-bottom-0">State Name</th>
                                    <th class="wd-15p border-bottom-0">District Name</th>
                                    <th class="wd-15p border-bottom-0">City</th>
                                    <th class="wd-15p border-bottom-0">Status</th>

                                </tr>
                                </thead>
                            </table>

                            <script type="text/javascript">
                                $(document).ready(function(){
                                    $('#my_table').DataTable({
                                        "order": [[ 1, "desc" ]],
                                        processing: true,
                                        serverSide: true,
                                        ajax: "{{ $urls }}",
                                        columns: [
                                            { data: 'id' },
                                            { data: 'created_at' },
                                            { data: 'user' },
                                            { data: 'first_name' },
                                            { data: 'last_name' },
                                            { data: 'mobile_number' },
                                            { data: 'email' },
                                            { data: 'aadhar_number' },
                                            { data: 'pan_number' },
                                            { data: 'company' },
                                            { data: 'pin_code' },
                                            { data: 'address' },
                                            { data: 'bank_account_number' },
                                            { data: 'ifsc' },
                                            { data: 'state_name' },
                                            { data: 'district_name' },
                                            { data: 'city' },
                                            { data: 'status' },
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