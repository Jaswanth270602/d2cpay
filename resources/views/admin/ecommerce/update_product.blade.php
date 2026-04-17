@extends('admin.layout.header')
@section('content')
    <script src="//cdn.ckeditor.com/4.13.1/full/ckeditor.js"></script>

    <script type="text/javascript">
        $(document).ready(function () {
            $("#category_id").select2();
            $("#subcategory_id").select2();
            $("#brand_id").select2();
            $("#status_id").select2();
            $("#home_page").select2();
        });

        function get_subcategory() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var category_id = $("#category_id").val();
            var dataString = 'category_id=' + category_id +  '&_token=' + token;
            $.ajax({
                type: "post",
                url: "{{url('admin/ecommerce/get-sub-category')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        var list = msg.list;
                        var html = "";
                        for (var key in list) {
                            html += '<option value="' + list[key].id + '">' + list[key].category_name + ' </option>';
                        }
                        $("#subcategory_id").html(html);

                    }else{
                        swal("Faild", msg.message, "error");
                    }
                }
            });


        }
    </script>

    <div class="main-content-body">

        @if(Session::has('success'))
            <div class="alert alert-success">
                <a class="close" data-dismiss="alert">×</a>
                <strong>Alert </strong> {!!Session::get('success')!!}
            </div>
        @endif

        @if(Session::has('failure'))
            <div class="alert alert-danger">
                <a class="close" data-dismiss="alert">×</a>
                <strong>Alert </strong> {!!Session::get('failure')!!}
            </div>
        @endif

        <form role="form" action="{{url('admin/ecommerce/products-update-now')}}" method="post" enctype="multipart/form-data">
            {!! csrf_field() !!}
            <input type="hidden" value="{{ $encrypt_id }}" name="encrypt_id">
            {{--service detail--}}
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
                            <div class="form-body">
                                <div class="row">

                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label for="name">Select Category</label>
                                            <select class="form-control select2" id="category_id" name="category_id" style="width: 100%;" onchange="get_subcategory(this)">
                                                <option value="">Select Category</option>
                                                @foreach($categories as $value)
                                                    <option value="{{ $value->id }}" @if($category_id == $value->id) selected @endif>{{ $value->category_name }}</option>
                                                @endforeach
                                            </select>
                                            @if ($errors->has('category_id'))
                                                <ul class="parsley-errors-list filled">
                                                    <li class="parsley-required">{{ $errors->first('category_id') }}</li>
                                                </ul>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label for="name">Select Sub Category</label>
                                            <select class="form-control select2" id="subcategory_id" name="subcategory_id" style="width: 100%;">
                                                @foreach($subcategories as $value)
                                                    <option value="{{ $value->id }}" @if($subcategory_id == $value->id) selected @endif>{{ $value->category_name }}</option>
                                                @endforeach
                                            </select>
                                            @if ($errors->has('subcategory_id'))
                                                <ul class="parsley-errors-list filled">
                                                    <li class="parsley-required">{{ $errors->first('subcategory_id') }}</li>
                                                </ul>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label for="name">Select Brand</label>
                                            <select class="form-control select2" id="brand_id" name="brand_id" style="width: 100%;">
                                                <option value="">Select Brand</option>
                                                @foreach($brands as $value)
                                                    <option value="{{ $value->id }}" @if($brand_id == $value->id) selected @endif >{{ $value->brand_name }}</option>
                                                @endforeach
                                            </select>
                                            @if ($errors->has('brand_id'))
                                                <ul class="parsley-errors-list filled">
                                                    <li class="parsley-required">{{ $errors->first('brand_id') }}</li>
                                                </ul>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label for="name">Shipping Charge</label>
                                            <input type="text" class="form-control" name="shipping_charge" placeholder="Shipping Charge"  value="{{ $shipping_charge }}">
                                            @if ($errors->has('shipping_charge'))
                                                <ul class="parsley-errors-list filled">
                                                    <li class="parsley-required">{{ $errors->first('shipping_charge') }}</li>
                                                </ul>
                                            @endif
                                        </div>
                                    </div>


                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label for="name">Product Name</label>
                                            <input type="text" class="form-control" name="product_name" placeholder="Product Name"  value="{{ $product_name }}">
                                            @if ($errors->has('product_name'))
                                                <ul class="parsley-errors-list filled">
                                                    <li class="parsley-required">{{ $errors->first('product_name') }}</li>
                                                </ul>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label for="name">Product Price</label>
                                            <input type="text" class="form-control" name="product_price" placeholder="Product Price"  value="{{ $product_price }}">
                                            @if ($errors->has('product_price'))
                                                <ul class="parsley-errors-list filled">
                                                    <li class="parsley-required">{{ $errors->first('product_price') }}</li>
                                                </ul>
                                            @endif
                                        </div>
                                    </div>


                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label for="name">Product Discount in  Percentage</label>
                                            <input type="text" class="form-control" name="product_discount" placeholder="Product Discount in %"  value="{{ $product_discount }}">
                                            @if ($errors->has('product_discount'))
                                                <ul class="parsley-errors-list filled">
                                                    <li class="parsley-required">{{ $errors->first('product_discount') }}</li>
                                                </ul>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label for="name">Product Weight</label>
                                            <input type="number" class="form-control" name="product_weight" placeholder="Product Weight"  value="{{ $product_weight }}">
                                            @if ($errors->has('product_weight'))
                                                <ul class="parsley-errors-list filled">
                                                    <li class="parsley-required">{{ $errors->first('product_weight') }}</li>
                                                </ul>
                                            @endif
                                        </div>
                                    </div>


                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="name">Product Image
                                                <span style="color: red; font: bold;">
                                               Before uploading the product photo, in the photo shop adjust the photo's height 300 and width 300 so that your ecommerce design looks amazing Click to see our designs
                                                <a href="{{url('assets/img/ecommerce-ui.png')}}" target="_blank">Click Here</a>
                                            </span>
                                            </label>
                                            <input type="file" class="form-control" name="product_image" placeholder="Product Sale Price">
                                            @if ($errors->has('product_image'))
                                                <ul class="parsley-errors-list filled">
                                                    <li class="parsley-required">{{ $errors->first('product_image') }}</li>
                                                </ul>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="name">Sttaus</label>
                                            <select class="form-control select2" name="status_id" id="status_id" style="width: 100%;">
                                                <option value="1" @if($status_id == 1) selected @endif>Approved</option>
                                                <option value="2" @if($status_id == 2) selected @endif>Rejected</option>
                                                <option value="3" @if($status_id == 3) selected @endif>Pending</option>
                                            </select>
                                            @if ($errors->has('status_id'))
                                                <ul class="parsley-errors-list filled">
                                                    <li class="parsley-required">{{ $errors->first('status_id') }}</li>
                                                </ul>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="name">Home Page</label>
                                            <select class="form-control select2" name="home_page" id="home_page" style="width: 100%;">
                                                <option value="1" @if($home_page == 1) selected @endif>Yes</option>
                                                <option value="0" @if($home_page == 0) selected @endif>No</option>
                                            </select>
                                            @if ($errors->has('home_page'))
                                                <ul class="parsley-errors-list filled">
                                                    <li class="parsley-required">{{ $errors->first('home_page') }}</li>
                                                </ul>
                                            @endif
                                        </div>
                                    </div>




                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="name">Description</label>
                                            <textarea class="form-control" placeholder="Description" name="description">{{ $description }}</textarea>
                                            @if ($errors->has('description'))
                                                <ul class="parsley-errors-list filled">
                                                    <li class="parsley-required">{{ $errors->first('description') }}</li>
                                                </ul>
                                            @endif
                                            <script>CKEDITOR.replace( 'description' );</script>
                                        </div>
                                    </div>



                                </div>

                            </div>
                        </div>

                    </div>
                </div>
                <!--/div-->

            </div>


            <div class="row row-sm">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header pb-0">
                            <div class="d-flex justify-content-between">
                                <h4 class="card-title mg-b-2 mt-2">SEO Tools</h4>

                                <i class="mdi mdi-dots-horizontal text-gray"></i>
                            </div>
                            <hr>
                        </div>
                        <div class="card-body">

                            <div class="form-body">
                                <div class="row">

                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="name">Meta Title</label>
                                            <input type="text" class="form-control" name="meta_title" placeholder="Meta Title"  value="{{ $meta_title }}">
                                            @if ($errors->has('meta_title'))
                                                <ul class="parsley-errors-list filled">
                                                    <li class="parsley-required">{{ $errors->first('meta_title') }}</li>
                                                </ul>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="name">Meta Keywords</label>
                                            <input type="text" class="form-control" name="meta_keywords" placeholder="Meta Keywords"  value="{{ $meta_keywords }}">
                                            @if ($errors->has('meta_keywords'))
                                                <ul class="parsley-errors-list filled">
                                                    <li class="parsley-required">{{ $errors->first('meta_keywords') }}</li>
                                                </ul>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="name">Meta Description</label>
                                            <textarea class="form-control" name="meta_description" placeholder="Meta Description">{{ $meta_description }}</textarea>
                                            @if ($errors->has('meta_description'))
                                                <ul class="parsley-errors-list filled">
                                                    <li class="parsley-required">{{ $errors->first('meta_description') }}</li>
                                                </ul>
                                            @endif
                                        </div>
                                    </div>






                                </div>

                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-danger waves-effect waves-light">Save Now</button>
                        </div>
                    </div>
                </div>
                <!--/div-->
            </div>

        </form>
        {{--service detail close--}}



    </div>
    </div>
    </div>




@endsection