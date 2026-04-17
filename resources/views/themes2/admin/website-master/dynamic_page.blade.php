@extends('themes2.admin.layout.header')
@section('content')
    <script type="text/javascript">
        function delete_navigation(id) {
            if (confirm("Are you sure? Delete this navigation") == true) {
                $(".loader").show();
                var token = $("input[name=_token]").val();
                var dataString = 'id=' + id +  '&_token=' + token;
                $.ajax({
                    type: "POST",
                    url: "{{url('admin/delete-navigation')}}",
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
                            <a href="{{url('admin/create-navigation')}}" class="btn btn-danger btn-sm">Add Navigation</a>
                        </div>
                        <hr>
                        <div class="widget-content">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table text-md-nowrap" id="example1">
                                        <thead>
                                        <tr>
                                            <th class="wd-15p border-bottom-0">navigation name</th>
                                            <th class="wd-15p border-bottom-0">navigation slug</th>
                                            <th class="wd-15p border-bottom-0">Type</th>
                                            <th class="wd-15p border-bottom-0">Content</th>
                                            <th class="wd-15p border-bottom-0">Action</th>

                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($navigation as $value)
                                            <tr>
                                                <td>{{ $value->navigation_name }}</td>
                                                <td>{{ $value->navigation_slug }}</td>
                                                <td> @if($value->type == 1)Header @else  Footer @endif</td>
                                                <td><a href="{{url('admin/add-content')}}/{{ $value->id }}" class="btn btn-success btn-sm"><i class="fas fa-plus-square"></i> Content</a> </td>
                                                <td>
                                                    <a href="{{url('admin/edit-navigation')}}/{{$value->id}}" class="btn btn-info btn-sm"><i class="fas fa-pen-square"></i> Edit</a>
                                                    <button class="btn btn-danger btn-sm" onclick="delete_navigation({{ $value->id }})"><i class="far fa-trash-alt"></i> Delete</button>
                                                </td>
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
        </div>
        <!-- Main Body Ends -->


@endsection