@extends('admin.layout.header')
@section('content')

    <script type="text/javascript">
        $(document).ready(function () {
            $("#other_id").select2();
        });



    </script>


    <div class="main-content-body">

        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card">
                    <div class="card-body">

                        <form action="{{url('admin/ecommerce/product-list')}}" method="get">
                            <div class="row">


                                <div class="col-lg-3 col-md-8 form-group mg-b-0">
                                    <label class="form-label">Status: <span class="tx-danger">*</span></label>
                                    <select class="form-control select2" id="other_id" name="status_id" style="width: 100%;">
                                        <option value="1" @if($status_id == 1) selected @endif>Approved</option>
                                        <option value="2" @if($status_id == 2) selected @endif>Rejected</option>
                                        <option value="3" @if($status_id == 3) selected @endif>Pending</option>
                                    </select>
                                </div>

                                <div class="col-lg-3 col-md-4 mg-t-10 mg-sm-t-25">
                                    <button class="btn btn-primary pd-x-20" type="submit"><i class="fas fa-search"></i> Search</button>
                                </div>


                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row row-sm">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">{{ $page_title }}</h4>
                            <a href="{{url('admin/ecommerce/add-products')}}" class="btn btn-danger btn-sm">Add New Product</a>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table text-md-nowrap" id="my_table">
                                <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0">Sr No</th>
                                    <th class="wd-15p border-bottom-0">Date</th>
                                    <th class="wd-15p border-bottom-0">user</th>
                                    <th class="wd-15p border-bottom-0">Category</th>
                                    <th class="wd-15p border-bottom-0">Photo</th>
                                    <th class="wd-15p border-bottom-0">Product Name</th>
                                    <th class="wd-15p border-bottom-0">Price</th>
                                    <th class="wd-15p border-bottom-0">Shipping</th>
                                    <th class="wd-15p border-bottom-0">Status</th>
                                    <th class="wd-15p border-bottom-0">add Image</th>
                                    <th class="wd-15p border-bottom-0">Action</th>
                                </tr>
                                </thead>
                            </table>

                            <script type="text/javascript">
                                $(document).ready(function(){

                                    // DataTable
                                    var todate = $("#todate").val();
                                    $('#my_table').DataTable({
                                        "order": [[ 1, "desc" ]],
                                        processing: true,
                                        serverSide: true,
                                        ajax: "{{ $urls }}",
                                        columns: [
                                            { data: 'sr_no' },
                                            { data: 'created_at' },
                                            { data: 'user' },
                                            { data: 'category_name' },
                                            { data: 'product_image' },
                                            { data: 'product_name' },
                                            { data: 'product_price' },
                                            { data: 'shipping_charge' },
                                            { data: 'status' },
                                            { data: 'add_image' },
                                            { data: 'action' },

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