@extends('themes2.admin.layout.header')
@section('content')
    <script type="text/javascript">
        function create_navigation() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var title = $("#title").val();
            var keyword = $("#keyword").val();
            var description = $("#description").val();
            var navigation_name = $("#navigation_name").val();
            var navigation_slug = $("#navigation_slug").val();
            var type = $("#type").val();
            var dataString = 'title=' + encodeURIComponent(title) + '&keyword=' + encodeURIComponent(keyword) + '&description=' + encodeURIComponent(description) + '&navigation_name=' + navigation_name + '&navigation_slug=' + navigation_slug + '&type=' + type + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/store-navigation')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () {
                            location.reload(1);
                        }, 3000);
                    } else if (msg.status == 'validation_error') {
                        $("#title_errors").text(msg.errors.title);
                        $("#keyword_errors").text(msg.errors.keyword);
                        $("#description_errors").text(msg.errors.description);
                        $("#navigation_name_errors").text(msg.errors.navigation_name);
                        $("#navigation_slug_errors").text(msg.errors.navigation_slug);
                        $("#type_errors").text(msg.errors.type);
                    } else {
                        swal("Faild", msg.message, "error");
                    }
                }
            });
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
                            <div class="card-body">
                                <div class="form-body">
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="name">Title</label>
                                                <textarea type="text" id="title" class="form-control" placeholder="Enter Title"></textarea>
                                                <span class="invalid-feedback d-block" id="title_errors"></span>
                                            </div>
                                        </div>

                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="name">Keyword</label>
                                                <textarea type="text" id="keyword" class="form-control" placeholder="Enter Keyword"></textarea>
                                                <span class="invalid-feedback d-block" id="keyword_errors"></span>
                                            </div>
                                        </div>

                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label for="name">Description</label>
                                                <textarea type="text" id="description" class="form-control" placeholder="Enter Description"></textarea>
                                                <span class="invalid-feedback d-block" id="description_errors"></span>
                                            </div>
                                        </div>

                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="name">Navigation Name</label>
                                                <input type="text" id="navigation_name" class="form-control" placeholder="Navigation Name">
                                                <span class="invalid-feedback d-block" id="navigation_name_errors"></span>
                                            </div>
                                        </div>

                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="name">Slug (Navigation URL)</label>
                                                <input type="text" id="navigation_slug" class="form-control" placeholder="Slug (Navigation URL)">
                                                <span class="invalid-feedback d-block" id="navigation_slug_errors"></span>
                                            </div>
                                        </div>

                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label for="name">Navigation Type</label>
                                                <select class="form-control" id="type">
                                                    <option value="1">Header</option>
                                                    <option value="2">Footer</option>
                                                </select>
                                                <span class="invalid-feedback d-block" id="type_errors"></span>
                                            </div>
                                        </div>


                                    </div>

                                </div>
                                <hr>
                                <div class="widget-footer text-right">
                                    <button type="button" class="btn btn-primary mr-2" onclick="create_navigation()">Submit</button>
                                    <button type="reset" class="btn btn-outline-primary"> Cancel</button>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!-- Main Body Ends -->


@endsection