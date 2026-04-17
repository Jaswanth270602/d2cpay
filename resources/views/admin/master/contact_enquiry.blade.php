@extends('admin.layout.header')
@section('content')
    <script type="text/javascript">
        function delete_enquiry(id) {
            var result = confirm("R u sure u want to delete?");
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

    <div class="main-content-body">
        <div class="row row-sm">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">{{ $page_title }}</h4>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="@if(Auth::User()->company->table_format == 1)table text-md-nowrap @else display responsive nowrap @endif" id="example1" data-order='[[ 0, "desc" ]]'>
                                <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0">Date</th>
                                    <th class="wd-15p border-bottom-0">Name</th>
                                    <th class="wd-15p border-bottom-0">Email</th>
                                    <th class="wd-15p border-bottom-0">Mobile</th>
                                    <th class="wd-15p border-bottom-0">Message</th>
                                    <th class="wd-15p border-bottom-0">action</th>

                                </tr>
                                </thead>
                                <tbody>
                                @foreach($contactenquiries as $value)
                                    <tr>
                                        <td>{{ $value->created_at }}</td>
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
            <!--/div-->

        </div>

    </div>
    </div>
    </div>



@endsection