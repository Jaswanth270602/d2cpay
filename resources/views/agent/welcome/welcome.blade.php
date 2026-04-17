@extends('agent.layout.header')
@section('content')


<div class="main-content-body">

    <div class="row">
        <div class="col-lg-4 col-md-12">
            <div class="card">
                <div class="card-body">
                    <div>
                        <h6 class="card-title mb-1">{{ $page_title }}</h6>
                        <hr>
                    </div>




                    <div class="mb-4">
                        <label>UTI Username</label>
                        <input type="text" class="form-control" placeholder="DTH Number" id="mobile_number">
                        <ul class="parsley-errors-list filled">
                            <li class="parsley-required" id="mobile_number_errors"></li>
                        </ul>
                    </div>







                </div>

                <div class="modal-footer">
                    <button class="btn ripple btn-primary" type="button" onclick="view_recharges()">Pay Now</button>
                    <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>





    </div>

</div>
</div>
</div>

@endsection