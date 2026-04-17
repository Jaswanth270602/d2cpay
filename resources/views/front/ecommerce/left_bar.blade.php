<div class="col-lg-3 col-md-4">
    <div class="widget">
        <div class="category_sidebar">
            <aside class="sidebar_widget">
                <div class="widget_title">
                    <h5 class="heading-design-h5"><i class="icofont icofont-filter"></i> Category</h5>
                </div>
                <div id="accordion" role="tablist" aria-multiselectable="true">

                    @foreach(App\Category::where('status_id', 1)->inRandomOrder()->paginate(3) as $key => $value)
                    <div class="card">
                        <div class="card-header" role="tab" id="headingOne">
                            <h5 class="mb-0">
                                <a data-toggle="collapse" data-parent="#accordion" href="#collapse_{{$value->id}}" aria-expanded="true" aria-controls="collapse_{{$value->id}}">
                                    {{ $value->category_name }}
                                    <span><i class="fa fa-plus-square-o"></i></span>
                                </a>
                            </h5>
                        </div>
                        <div id="collapse_{{$value->id}}" class="collapse {{ $key == 0 ? 'show' : '' }}" role="tabpanel" aria-labelledby="heading_{{$value->id}}">
                            <div class="card-block">
                                <ul class="trends">
                                    @foreach(App\Subcategory::where('status_id', 1)->where('category_id', $value->id)->get() as $sub)
                                    <li> <a href="">{{ $sub->category_name }}</a> </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    @endforeach

                </div>
            </aside>
            <hr>
            <aside class="sidebar_widget">
                <div class="widget_title">
                    <h5 class="heading-design-h5">Brand</h5>
                </div>
                <div class="card">
                    <div class="collapse show">
                        <div class="card-block">
                            <ul class="trends">
                                <li><a href="#">Apple</a> </li>


                            </ul>
                        </div>
                    </div>
                </div>
            </aside>
            <hr>




        </div>
    </div>
    <hr>
    <a href="shop-grid-left-sidebar.html">
        <img class="rounded" src="https://askbootstrap.com/preview/osahan-fashion/images/women-top.png" alt="Bannar 1">
    </a>
</div>