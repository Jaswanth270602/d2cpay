@extends('agent.layout.header')
@section('content')

    <script type="text/javascript">

        $(document).ready(function () {
            $("#day_book").select2();
            $("#daily_statement").select2();

        });

        function save_settings() {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var day_book = $("#day_book").val();
            var daily_statement = $("#daily_statement").val();
            var dataString = 'day_book=' + day_book + '&daily_statement=' + daily_statement +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('agent/save-settings')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 2000);
                    } else if(msg.status == 'validation_error'){
                        $("#day_book_errors").text(msg.errors.day_book);
                        $("#daily_statement_errors").text(msg.errors.daily_statement);
                    }else{
                        swal("Faild", msg.message, "error");
                    }
                }
            });
        }

    </script>


    <div class="main-content-body">
        <div class="row row-sm">
            <!-- Col -->
            <div class="col-lg-5">

                {{-- //--}}
                <div class="card custom-card">
                    <div class="card-body text-center">

                        <div class="user-lock text-center">
                            @if(Auth::User()->member->profile_photo)
                                <img alt="avatar" class="rounded-circle" src="{{Auth::User()->member->profile_photo}}">
                            @else
                                <img alt="avatar" class="rounded-circle" src="{{url('assets/img/profile-pic.jpg')}}">
                            @endif

                        </div>
                        <h5 class="mb-1 mt-3 card-title">{{ Auth::User()->name }} {{ Auth::User()->last_name }}</h5>
                        <p class="mb-2 mt-1 tx-inverse">{{ Auth::User()->role->role_title }}</p>
                        <p class="text-muted text-center mt-1">Company Name : {{ Auth::User()->company->company_name }}</p>


                        <div class="mt-2 user-info btn-list">
                            <a class="btn btn-outline-light btn-block" href="#"><i class="fe fe-mail mr-2"></i><span> {{ Auth::User()->email }}</span></a>
                            <a class="btn btn-outline-light btn-block" href="#"><i class="fe fe-phone mr-2"></i><span> {{ Auth::User()->mobile }}</span></a>
                            <a class="btn btn-outline-light  btn-block" href="#"><i class="far fa-clock"></i> <span> {{ Auth::User()->created_at }}</span></a>


                        </div>
                    </div>
                </div>



            </div>
            <!-- /Col -->

            <!-- Col -->
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-body">
                        <div class="mb-4 main-content-label">{{ $page_title }}</div>
                        <hr>
                        <form class="form-horizontal">


                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Day Book</label>
                                    </div>
                                    <div class="col-md-9">
                                       <select class="form-control select2" id="day_book">
                                           <option value="1" @if(Auth::User()->profile->day_book == 1) selected @endif>Enabled</option>
                                           <option value="0" @if(Auth::User()->profile->day_book == 0) selected @endif>Disabled</option>
                                       </select>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="day_book_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>


                            <div class="form-group ">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Daily Statement (Excel)</label>
                                    </div>
                                    <div class="col-md-9">
                                        <select class="form-control select2" id="daily_statement">
                                            <option value="1" @if(Auth::User()->profile->monthly_statement == 1) selected @endif>Enabled</option>
                                            <option value="0" @if(Auth::User()->profile->monthly_statement == 0) selected @endif>Disabled</option>
                                        </select>
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="daily_statement_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-danger waves-effect waves-light" onclick="save_settings()">Save</button>
                    </div>
                </div>
            </div>
            <!-- /Col -->
        </div>


    </div>
    <!-- /row -->
    </div>
    <!-- /container -->
    </div>
    <!-- /main-content -->

@endsection