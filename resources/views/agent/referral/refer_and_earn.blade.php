@extends('agent.layout.header')
@section('content')
    <style>
        .tooltip {
            position: relative;
            display: inline-block;
        }

        .tooltip .tooltiptext {
            visibility: hidden;
            width: 140px;
            background-color: #555;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 5px;
            position: absolute;
            z-index: 1;
            bottom: 150%;
            left: 50%;
            margin-left: -75px;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .tooltip .tooltiptext::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: #555 transparent transparent transparent;
        }

        .tooltip:hover .tooltiptext {
            visibility: visible;
            opacity: 1;
        }
    </style>


    <div class="main-content-body">
        <div class="row row-sm">
            <!-- Col -->
            <div class="col-lg-7">
                <div class="card mg-b-20">
                    <div class="card-body">
                        <div class="main-content-label tx-13 mg-b-25">
                            Refer & Earn
                        </div>
                        <hr>
                        <div class="form-horizontal">

                                <div class="form-group ">
                                    <div class="row">

                                        <div class="col-md-3">
                                            <label class="form-label">Referral Link</label>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="input-group">
                                                <input class="form-control"  id="myInput" type="text" value="{{url('sign-up')}}/{{ base64_encode(Auth::User()->mobile) }}" readonly>
                                                <span class="input-group-btn">
                                                <button class="btn btn-danger" onclick="myFunction()" onmouseout="outFunc()">Copy</button></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                        <hr>
                        <h3>How do you refer & earn?</h3>
                        <p>Refer your friends and once they start using {{ $company_name }}, you will get some commission of their transaction in {{ $company_name }} wallet. Also, your friend will earn profits. You can refer as many friends as you wish.</p>
                    </div>
                </div>
            </div>
            <!-- /Col -->

            <!-- Col -->
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header pt-4 pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-10 ">STATISTICS</h4>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="pl-4 pr-4 pt-4 pb-3">
                        <div class="">
                            @php
                                $totalRefers = App\User::where('parent_id', Auth::id())->count();
                                $totalEarnings = App\Report::where('user_id', Auth::id())->where('status_id', 1)->sum('referral_comm');
                            @endphp
                            <div class="row">
                                <div class="col-md-6 col-6 text-center">
                                    <div class="task-box danger mb-0">
                                        <p class="mb-0 tx-12">Total Refers</p>
                                        <h3 class="mb-0">{{ $totalRefers }}</h3>
                                    </div>
                                </div>
                                <div class="col-md-6 col-6 text-center">
                                    <div class="task-box primary  mb-0">
                                        <p class="mb-0 tx-12">Total Earings</p>
                                        <h3 class="mb-0">{{ number_format($totalEarnings, 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-header pt-4 pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-10 ">newly joined member</h4>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="task-stat pb-0">
                        @foreach(App\User::where('parent_id', Auth::id())->orderBy('id', 'DESC')->paginate(5) as $value)

                            <div class="d-flex tasks">
                                <div class="mb-0">
                                    <div class="h6 fs-15 mb-0"><i
                                                class="far fa-user text-primary mr-2"></i> {{ $value->name }} {{ $value->last_name }}
                                    </div>
                                    <span class="text-muted tx-11 ml-4">{{ date('d-m-Y', strtotime($value->created_at))  }}</span>
                                </div>
                                @php
                                    $phone = $value->mobile;
                                    $result = substr($phone, 0, 3);
                                    $result .= "*****";
                                    $result .= substr($phone, 3, 2);
                                @endphp
                                <span class="float-right ml-auto">{{ $result }}</span>
                            </div>
                        @endforeach

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
    <script>
        function myFunction() {
            var copyText = document.getElementById("myInput");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(copyText.value);

            var tooltip = document.getElementById("myTooltip");
            tooltip.innerHTML = "Copied: " + copyText.value;
        }

        function outFunc() {
            var tooltip = document.getElementById("myTooltip");
            tooltip.innerHTML = "Copy to clipboard";
        }
    </script>
@endsection