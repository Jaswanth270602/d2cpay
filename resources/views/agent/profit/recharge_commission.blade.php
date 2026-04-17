@extends('agent.layout.header')
@section('content')
    
    <script type="text/javascript">
        function view_commission_slab(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/view-my-comm-slab')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        var commission = msg.commission;
                        var html = "";
                        for (var key in commission) {
                            html += "<tr>";
                            html += '<td>' + commission[key].min_amount + '</td>';
                            html += '<td>' + commission[key].max_amount + '</td>';
                            html += '<td>' + commission[key].type + '</td>';
                            html += '<td>' + commission[key].commission + '</td>';
                            html += "</tr>";
                        }

                        $(".commission_list").html(html);
                        $(".provider_name").text(msg.provider_name);
                        $("#view_commission_slab").modal('show');
                    }else{
                        swal("Faild", msg.message, "error");
                    }
                }
            });
        }
        
    </script>


    <!-- main-content-body -->
    <div class="main-content-body">

        <!-- row -->
        <div class="row row-sm">
        @include('agent.profit.left_side')

            <!-- Col -->
            <div class="col-lg-8 col-xl-9">
                {{--prepaid commission--}}
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
                            <table class="table text-md-nowrap" id="example1">
                                <thead>
                                <tr>
                                    <th class="wd-15p border-bottom-0">Provider Id</th>
                                    <th class="wd-25p border-bottom-0">Provider name</th>
                                    <th class="wd-25p border-bottom-0">Service</th>
                                    <th class="wd-25p border-bottom-0">Action</th>

                                </tr>
                                </thead>
                                <tbody>

                                @foreach($providers as $value)
                                   <tr>
                                       <td>{{ $value->id }}</td>
                                       <td>{{ $value->provider_name }}</td>
                                       <td>{{ $value->service->service_name }}</td>
                                       <td><button class="btn btn-danger btn-sm" onclick="view_commission_slab({{ $value->id }})">View Slab</button></td>
                                   </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                {{--clase prepaid commission--}}








            </div>
            <!-- /Col -->


        </div>
        <!-- /row -->

        <!-- row -->


    </div>
    <!-- /row -->
    </div>
    <!-- /container -->
    </div>
    <!-- /main-content -->



    <div class="modal  show" id="view_commission_slab"data-toggle="modal">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title provider_name"></h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">

                    <div class="table-responsive mb-0">
                        <table class="table table-striped mg-b-0 text-md-nowrap">
                            <thead>
                            <tr>
                                <th>Min Amount</th>
                                <th>Max Amount</th>
                                <th>Type</th>
                                <th>Commission And Charges</th>

                            </tr>
                            </thead>
                            <tbody class="commission_list"></tbody>
                        </table>
                    </div>

                </div>

            </div>
        </div>
    </div>

@endsection