@extends('admin.layout.header')
@section('content')
    
    <script type="text/javascript">

        $(document).ready(function () {
            $('#view_child_id').select2({
                dropdownParent: $('#view_bank_model')
            });

            $('#child_id').select2({
                dropdownParent: $('#add_new_bank_model')
            });
        });
        function view_banks(id) {
            $(".loader").show();
            var token = $("input[name=_token]").val();
            var dataString = 'id=' + id +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/view-bank-details')}}",
                data: dataString,
                success: function (msg) {
                    $(".loader").hide();
                    if (msg.status == 'success') {
                        $("#view_bank_id").val(msg.details.bank_id);
                        $("#view_bank_name").val(msg.details.bank_name);
                        $("#view_bank_account_number").val(msg.details.bank_account_number);
                        $("#view_bank_ifsc").val(msg.details.bank_ifsc);
                        $("#view_bank_account_name").val(msg.details.bank_account_name);
                        $("#view_bank_branch").val(msg.details.bank_branch);
                        $("#view_status_id").val(msg.details.status_id);
                        if (msg.details.child_id && $("#view_child_id option[value='" + msg.details.child_id + "']").length) {
                            $("#view_child_id").val(msg.details.child_id).trigger('change');
                        } else {
                            $("#view_child_id").val(0).trigger('change'); // Default 'All User' ko select karega
                        }
                        $("#view_bank_model").modal('show');
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }

        function update_banks () {
           // $(".loader").show();
            $("#updatebank_btn").hide();
            $("#updatebank_btn_loader").show();
            var token = $("input[name=_token]").val();
            var bank_id = $("#view_bank_id").val();
            var bank_name = $("#view_bank_name").val();
            var bank_account_number = $("#view_bank_account_number").val();
            var bank_ifsc = $("#view_bank_ifsc").val();
            var bank_account_name = $("#view_bank_account_name").val();
            var bank_branch = $("#view_bank_branch").val();
            var status_id = $("#view_status_id").val();
            var child_id = $("#view_child_id").val();
            var dataString = 'bank_id=' + bank_id + '&bank_name=' + bank_name + '&bank_account_number=' + bank_account_number +  '&bank_ifsc=' + bank_ifsc + '&bank_account_name=' + bank_account_name + '&bank_branch=' + bank_branch + '&status_id=' + status_id + '&child_id=' + child_id + '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/update-bank')}}",
                data: dataString,
                success: function (msg) {
                   // $(".loader").hide();
                    $("#updatebank_btn").show();
                    $("#updatebank_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#view_bank_id_errors").text(msg.errors.bank_id);
                        $("#view_bank_name_errors").text(msg.errors.bank_name);
                        $("#view_bank_account_number_errors").text(msg.errors.bank_account_number);
                        $("#view_bank_ifsc_errors").text(msg.errors.bank_ifsc);
                        $("#view_bank_account_name_errors").text(msg.errors.bank_account_name);
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }
        
        
        function add_banks() {
            //$(".loader").show();
            $("#addbank_btn").hide();
            $("#addbank_btn_loader").show();
            var token = $("input[name=_token]").val();
            var bank_name = $("#bank_name").val();
            var bank_account_number = $("#bank_account_number").val();
            var bank_ifsc = $("#bank_ifsc").val();
            var bank_account_name = $("#bank_account_name").val();
            var bank_branch = $("#bank_branch").val();
            var child_id = $("#child_id").val();
            var dataString = 'bank_name=' + bank_name +  '&bank_account_number=' + bank_account_number +  '&bank_ifsc=' + bank_ifsc + '&bank_account_name=' + bank_account_name + '&bank_branch=' + bank_branch + '&child_id=' + child_id +  '&_token=' + token;
            $.ajax({
                type: "POST",
                url: "{{url('admin/add-bank')}}",
                data: dataString,
                success: function (msg) {
                    //$(".loader").hide();
                    $("#addbank_btn").show();
                    $("#addbank_btn_loader").hide();
                    if (msg.status == 'success') {
                        swal("Success", msg.message, "success");
                        setTimeout(function () { location.reload(1); }, 3000);
                    } else if(msg.status == 'validation_error'){
                        $("#bank_name_errors").text(msg.errors.bank_name);
                        $("#bank_account_number_errors").text(msg.errors.bank_account_number);
                        $("#bank_ifsc_errors").text(msg.errors.bank_ifsc);
                        $("#bank_account_name_errors").text(msg.errors.bank_account_name);
                    }else{
                        swal("Failed", msg.message, "error");
                    }
                }
            });
        }
    </script>




    <div class="main-content-body">
        <div class="row row-sm">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">Bank Settings</h4>
                            <button class="btn btn-danger btn-sm" data-target="#add_new_bank_model" data-toggle="modal">Add New Bank</button>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table text-md-nowrap" id="example1">
                                <thead>
                                <tr>

                                    <th class="wd-15p border-bottom-0">User</th>
                                    <th class="wd-15p border-bottom-0">Bank Name</th>
                                    <th class="wd-15p border-bottom-0">Account Number</th>
                                    <th class="wd-20p border-bottom-0">Holder Name</th>
                                    <th class="wd-15p border-bottom-0">ifsc Code</th>
                                    <th class="wd-10p border-bottom-0">Branch</th>
                                    <th class="wd-10p border-bottom-0">Status</th>
                                    <th class="wd-25p border-bottom-0">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($banks as $value)
                                <tr>
                                    @php
                                      $userDetails = App\Models\User::find($value->child_id);
                                      $child_name = (!empty($userDetails)) ? $userDetails->name.' '. $userDetails->lase_name : 'All Users'
                                    @endphp
                                    <td>{{ $child_name }}</td>
                                    <td>{{ $value->bank_name }}</td>
                                    <td>{{ $value->bank_account_number }}</td>
                                    <td>{{ $value->bank_account_name }}</td>
                                    <td>{{ $value->bank_ifsc }}</td>
                                    <td>{{ $value->bank_branch }}</td>
                                    <td> <span class="{{ $value->status?->class ?? 'text-muted' }}"> {{ $value->status?->status ?? 'No Status' }} </span> </td>

                                    <td><button class="btn btn-danger btn-sm" onclick="view_banks({{ $value->id }})"><i class="typcn typcn-edit"></i> Edit Bank</button></td>
                                </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!--/div-->

        </div>

    </div>
    </div>
    </div>



    {{--add new bank model--}}
    <div class="modal fade" id="add_new_bank_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2" aria-hidden="true">
        <div class="modal-dialog modal-dialog-slideout" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Add New Bank</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Bank Name</label>
                                    <input type="text" id="bank_name" class="form-control" placeholder="Bank Name">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="bank_name_errors"></li>
                                    </ul>
                                </div>
                            </div>


                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Account Number</label>
                                    <input type="text" id="bank_account_number" class="form-control" placeholder="Account Number">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="bank_account_number_errors"></li>
                                    </ul>
                                </div>
                            </div>


                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">IFSC Code</label>
                                    <input type="text" id="bank_ifsc" class="form-control" placeholder="IFSC Code">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="bank_ifsc_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Holder Name</label>
                                    <input type="text" id="bank_account_name" class="form-control" placeholder="Holder Name">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="bank_account_name_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Branch</label>
                                    <input type="text" id="bank_branch" class="form-control" placeholder="Holder Name">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="bank_branch_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">User </label>
                                    <select class="form-control select2" id="child_id" style="width: 100%;">
                                        <option value="0">All User</option>
                                        @foreach($users as $value)
                                            <option value="{{ $value->id }}">{{ $value->name }} {{ $value->last_name }} : {{ $value->mobile }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="addbank_btn" onclick="add_banks()">Save changes</button>
                    <button class="btn btn-primary" type="button"  id="addbank_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                </div>
            </div>
        </div>
    </div>


    {{--update bank modal--}}
    <div class="modal fade" id="view_bank_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2" aria-hidden="true">
        <div class="modal-dialog modal-dialog-slideout" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Bank Update</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">
                            <input type="hidden" id="view_bank_id">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Bank Name</label>
                                    <input type="text" id="view_bank_name" class="form-control" placeholder="Bank Name">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_bank_name_errors"></li>
                                    </ul>
                                </div>
                            </div>


                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Account Number</label>
                                    <input type="text" id="view_bank_account_number" class="form-control" placeholder="Account Number">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_bank_account_number_errors"></li>
                                    </ul>
                                </div>
                            </div>


                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">IFSC Code</label>
                                    <input type="text" id="view_bank_ifsc" class="form-control" placeholder="IFSC Code">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_bank_ifsc_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Holder Name</label>
                                    <input type="text" id="view_bank_account_name" class="form-control" placeholder="Holder Name">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_bank_account_name_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Branch</label>
                                    <input type="text" id="view_bank_branch" class="form-control" placeholder="Holder Name">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_bank_branch_errors"></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">User </label>
                                    <select class="form-control select2" id="view_child_id" style="width: 100%;">
                                        <option value="0">All User</option>
                                        @foreach($users as $value)
                                            <option value="{{ $value->id }}">{{ $value->name }} {{ $value->last_name }} : {{ $value->mobile }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Status</label>
                                    <select class="form-control" id="view_status_id">
                                        <option value="1">Active</option>
                                        <option value="0">De Active</option>
                                    </select>
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_status_id_errors"></li>
                                    </ul>
                                </div>
                            </div>


                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="updatebank_btn" onclick="update_banks()">Save changes</button>
                    <button class="btn btn-primary" type="button"  id="updatebank_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                </div>
            </div>
        </div>
    </div>


    <style>
        .modal-dialog-slideout {min-height: 100%; margin: 0 0 0 auto;background: #fff;}
        .modal.fade .modal-dialog.modal-dialog-slideout {-webkit-transform: translate(100%,0)scale(1);transform: translate(100%,0)scale(1);}
        .modal.fade.show .modal-dialog.modal-dialog-slideout {-webkit-transform: translate(0,0);transform: translate(0,0);display: flex;align-items: stretch;-webkit-box-align: stretch;height: 100%;}
        .modal.fade.show .modal-dialog.modal-dialog-slideout .modal-body{overflow-y: auto;overflow-x: hidden;}
        .modal-dialog-slideout .modal-content{border: 0;}
        .modal-dialog-slideout .modal-header, .modal-dialog-slideout .modal-footer {height: 69px; display: block;}
        .modal-dialog-slideout .modal-header h5 {float:left;}
    </style>


@endsection