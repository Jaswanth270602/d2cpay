@extends('agent.layout.header')
@section('content')



    <div class="main-content-body">

        <div class="row">

            <div class="col-lg-4 col-md-12">

                <div class="card">
                    <form role="form" action="{{url('agent/payout/v2/bulk-upload')}}" method="post" enctype="multipart/form-data">
                        {!! csrf_field() !!}
                        <div class="card-body">
                            <div>
                                <h6 class="card-title mb-1">{{ $page_title }}</h6>
                                <hr>
                            </div>
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

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="row">


                                <input type="hidden" name="dupplicate_transaction" value="{{ $timestamp }}">


                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="name">Select Excel File</label>
                                        <input type="file" class="form-control" name="excel_file">
                                        <ul class="parsley-errors-list filled">
                                            <li class="parsley-required" id="ifsc_code_errors"></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn ripple btn-primary" type="submit">Upload & Transfer</button>
                        </div>
                    </form>


                </div>
            </div>


            <div class="col-lg-8 col-md-12">
                <div class="card">

                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Excel Upload Instructions</h6>
                            <hr>
                        </div>

                        <div class="row">
                            <h4>How to Upload Your Excel Data</h4>
                            <ol type="1">
                                <li><strong>Download the Template:</strong> Click the link below to download the Excel template. Fill in your data in the provided fields.</li>
                                <li><strong>Keep the Headers Unchanged:</strong> Do not modify the header names in the Excel file. Only update the data beneath them.</li>
                                <li><strong>Enter Mode:</strong> In the "mode" column, select either NEFT or IMPS for each record.</li>
                                <li><strong>Upload Limit:</strong> You can upload up to 100 records at once. If you try to upload more than 100 records, the upload will fail, and no data will be saved.</li>
                            </ol>
                            <p>Download the template here: <a href="{{url('bulk_payout_upload.xlsx')}}" target="_blank" download="">Download Excel Template</a></p>

                        </div>


                    </div>
                </div>





            </div>

        </div>
    </div>
    </div>
@endsection