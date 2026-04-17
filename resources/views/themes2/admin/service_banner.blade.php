@extends('themes2.admin.layout.header')
@section('content')
    <script type="text/javascript">
        function delete_benner(id) {
            if (confirm("Are you sure? Delete this banner") == true) {
                $(".loader").show();
                var token = $("input[name=_token]").val();
                var dataString = 'id=' + id +  '&_token=' + token;
                $.ajax({
                    type: "POST",
                    url: "{{url('admin/delete-service-banner')}}",
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
            <div class="layout-top-spacing mb-2">
                <div class="col-md-12">
                    <div class="row">
                        <div class="container p-0">
                            <div class="row layout-top-spacing">
                                <div class="col-lg-4 layout-spacing">
                                    <div class="statbox widget box box-shadow mb-4">
                                        <form role="form" action="{{url('admin/store-service-banner')}}" method="post" enctype="multipart/form-data">
                                            {!! csrf_field() !!}
                                            <div class="widget-header">
                                                <div class="row">
                                                    <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                                                        <h4>{{ $page_title }}</h4>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="widget-content widget-content-area">

                                                @if(Session::has('msg'))
                                                    <div class="alert alert-info">
                                                        <a class="close" data-dismiss="alert">×</a>
                                                        <strong>Alert </strong> {!!Session::get('msg')!!}
                                                    </div>
                                                @endif

                                                @if(Session::has('failure'))
                                                    <div class="alert alert-danger">
                                                        <a class="close" data-dismiss="alert">×</a>
                                                        <strong>Alert </strong> {!!Session::get('failure')!!}
                                                    </div>
                                                @endif

                                                    @if ($errors->any())
                                                        <div class="alert alert-danger">
                                                            <ul>
                                                                @foreach ($errors->all() as $error)
                                                                    <li>{{ $error }}</li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif

                                                <div class="form-group">
                                                    <label>Service</label>
                                                    <select class="form-control" name="service_id">
                                                        @foreach($service as $value)
                                                            <option value="{{ $value->id }}">{{ $value->service_name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                    <div class="form-group">
                                                        <label>Select Logo</label>
                                                        <input type="file" class="form-control" placeholder="Select Logo" name="service_banner">
                                                    </div>
                                            </div>
                                            <div class="widget-footer text-right">
                                                <button type="submit" class="btn btn-primary mr-2">Upload Banner</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="col-lg-8 layout-spacing">
                                    <div class="statbox widget box box-shadow mb-4">
                                        <div class="widget-header">
                                            <div class="row">
                                                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                                                    <h4>Banners</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="widget-content widget-content-area">
                                            <div class="table-responsive">
                                                <table class="table text-md-nowrap" id="example1">
                                                    <thead>
                                                    <tr>
                                                        <th class="wd-15p border-bottom-0">Service</th>
                                                        <th class="wd-15p border-bottom-0">Banner</th>
                                                        <th class="wd-15p border-bottom-0">Action</th>

                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($servicebanner as $value)
                                                        <tr>
                                                            <td>{{ $value->service->service_name }}</td>
                                                            <td><img src="{{$cdnLink}}{{ $value->service_banner }}" style="height: 50px;"></td>
                                                            <td><button class="btn btn-danger btn-sm" onclick="delete_benner({{ $value->id }})"><i class="fas fa-trash-alt"></i> Delete</button></td>
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
                </div>
            </div>
        </div>
        <!-- Main Body Ends -->


@endsection