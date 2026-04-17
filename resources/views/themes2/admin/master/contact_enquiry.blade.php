@extends('themes2.admin.layout.header')
@section('content')
    <script type="text/javascript">
        function delete_enquiry(id) {
            var result = confirm("R you sure you want to delete this?");
            if (result) {
                $(".loader").show();
                var token = $("input[name=_token]").val();
                var dataString = 'id=' + id + '&_token=' + token;
                $.ajax({
                    type: "POST",
                    url: "{{url('admin/delete-contact-enquiry')}}",
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
                                <table class="@if(Auth::User()->company->table_format == 1)table text-md-nowrap @else display responsive nowrap @endif" id="example1">
                                    <thead>
                                    <tr>
                                        <th class="wd-15p border-bottom-0">Name</th>
                                        <th class="wd-15p border-bottom-0">Email</th>
                                        <th class="wd-15p border-bottom-0">Mobile</th>
                                        <th class="wd-15p border-bottom-0">Message</th>
                                        <th class="wd-15p border-bottom-0">Action</th>

                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($contactenquiries as $value)
                                        <tr>
                                            <td>{{ $value->name }}</td>
                                            <td>{{ $value->email }}</td>
                                            <td>{{ $value->mobile_number }}</td>
                                            <td>{{ $value->message }}</td>
                                            <td><button class="btn btn-danger btn-sm" onclick="delete_enquiry({{ $value->id }})">Delete</button></td>
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