<input type="hidden" id="sender_name">
<input type="hidden" id="ad1">
<input type="hidden" id="ad2">
<input type="hidden" id="transactionMiliseconds">

<script type="text/javascript">
    function closeInputs (){
        // for add beneficiary
        $("#bank_id").val('').trigger('change');
        $("#ifsc_code").val('');
        $("#account_number").val('');
        $("#beneficiary_name").val('');
        $("#beneficiary_otp").val('');
        // for sender registeration
        $("#first_name").val('');
        $("#last_name").val('');
        $("#pincode").val('');
        $("#state").val('');
        $("#address").val('');
        $("#sender_otp").val('');
        $("#delete_beneficiary_otp").val('');
        $(".transactionChargesListDiv").hide();
    }
</script>


{{--sender registration model--}}
<div class="modal  show" id="add-sender-model" data-toggle="modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Add Sender</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="form-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">First Name</label>
                                <input type="text" id="first_name" class="form-control" placeholder="First Name">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="first_name_errors"></li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Last Name</label>
                                <input type="text" id="last_name" class="form-control" placeholder="Last Name">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="last_name_errors"></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">Pincode</label>
                                <input type="text" id="pincode" class="form-control" placeholder="Pincode">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="pincode_errors"></li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name">State</label>
                                <input type="text" id="state" class="form-control" placeholder="State">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="state_errors"></li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="name">Address</label>
                                <input type="text" id="address" class="form-control" placeholder="Address">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="address_errors"></li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-sm-12 add-sender-otp-label" style="display: none;">
                            <div class="form-group">
                                <label for="name">OTP</label>
                                <input type="password" id="sender_otp" class="form-control" placeholder="OTP">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="sender_otp_errors"></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn ripple btn-primary" type="button" id="addSederBtn" onclick="addSender()">Add Sender</button>
                <button class="btn btn-primary" type="button"  id="addSederBtn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                <button class="btn ripple btn-danger"  type="button" id="resendOtpBtn" onclick="resendOtp()">Resend OTP</button>
                <button class="btn btn-danger" type="button"  id="resendOtpBtn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
            </div>
        </div>
    </div>
</div>
{{--sender registration model close--}}


{{--beneficiary confirm model--}}
<div class="modal  show" id="beneficiary-confirm-model" data-toggle="modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Verify Beneficiary</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="form-body">


                    <div class="row">

                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="name">Beneficiary OTP</label>
                                <input type="text" id="beneficiary_otp" class="form-control" placeholder="Enter Beneficiary OTP">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="beneficiary_otp_errors"></li>
                                </ul>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

            <div class="modal-footer">
                <button class="btn ripple btn-primary" type="button" id="beneficiaryVerifyBtn" onclick="verifyBeneficiary()">Verify Beneficiary</button>
                <button class="btn btn-primary" type="button"  id="beneficiaryVerifyBtn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
            </div>
        </div>
    </div>
</div>

{{--beneficiary confirm model Close--}}


{{--delete beneficiary model--}}
<div class="modal  show" id="beneficiary-delete-otp-model" data-toggle="modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Confimr Delete Beneficiary</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="form-body">


                    <div class="row">

                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="name">Delete Beneficiary OTP</label>
                                <input type="text" id="delete_beneficiary_otp" class="form-control" placeholder="Enter OTP">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="delete_beneficiary_otp_errors"></li>
                                </ul>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

            <div class="modal-footer">
                <button class="btn ripple btn-primary" type="button" id="deleteBeneficiaryVerifyBtn" onclick="confirmDeleteBeneficiary()">Verify OTP</button>
                <button class="btn btn-primary" type="button"  id="deleteBeneficiaryVerifyBtn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
            </div>
        </div>
    </div>
</div>
{{--delete beneficiary model close--}}


{{--View Transaction confirmation model--}}

<div class="modal  show" id="view-transaction-confirm-model" data-toggle="modal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Transaction Confirmation</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="task-stat pb-0">

                        <div class="d-flex tasks">
                            <div class="mb-0">
                                <div class="h6 fs-15 mb-0">Bank Name: </div>
                            </div>
                            <span class="float-right ml-auto bank_name"></span>
                        </div>

                        <div class="d-flex tasks">
                            <div class="mb-0">
                                <div class="h6 fs-15 mb-0">Account Number: </div>
                            </div>
                            <span class="float-right ml-auto account_number"></span>
                        </div>


                        <div class="d-flex tasks">
                            <div class="mb-0">
                                <div class="h6 fs-15 mb-0">IFSC Code: </div>
                            </div>
                            <span class="float-right ml-auto ifsc_code"></span>
                        </div>

                        <div class="d-flex tasks">
                            <div class="mb-0">
                                <div class="h6 fs-15 mb-0">Holder Name: </div>
                            </div>
                            <span class="float-right ml-auto beneficiary_name"></span>
                        </div>


                    </div>
                </div>

                <div class="row">
                    <input type="hidden" id="transfer_account_number">
                    <input type="hidden" id="transfer_ifsc_code">
                    <input type="hidden" id="transfer_beneficiary_id">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="name">Mode</label>
                        <select class="form-control" id="channel_id">
                            <option value="2">IMPS</option>
                            <option value="1">NEFT</option>
                        </select>
                    </div>
                </div>


                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="name">Amount</label>
                            <input type="text" id="amount" class="form-control" placeholder="Enter Amount" autocomplete="off">
                        </div>
                    </div>

                @if(Auth::User()->company->transaction_pin == 1)
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label for="name">Transaction Pin</label>
                            <input type="password" id="transaction_pin" class="form-control" placeholder="Transaction Pin">
                        </div>
                    </div>
                @endif

                </div>
            </div>

            <div class="modal-body transactionChargesListDiv" style="display: none;">
                <div class="card">
                    <div class="task-stat pb-0">
                        <div class="d-flex tasks">
                            <div class="mb-0">
                                <div class="h6 fs-15 mb-0">Amount</div>
                            </div>
                            <span class="float-right ml-auto">Charges</span>
                            <span class="float-right ml-auto">Total Amount</span>
                        </div>
                        <div class="transactionChargesList"></div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn ripple btn-primary" type="button" id="transferBtn" onclick="transferNow()">Transfer Now</button>
                <button class="btn ripple btn-primary" type="button" id="transferBtn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                <button class="btn ripple btn-danger" type="button" id="getChargesBtn" onclick="getCharges()">Get Charge</button>
                <button class="btn ripple btn-danger" type="button" id="getChargesBtn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
            </div>
        </div>
    </div>
</div>
{{--View Transaction confirmation model End--}}

{{--transaction-receipt-model--}}
<div class="modal  show" id="transaction-receipt-model" data-toggle="modal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title"><img src="{{$cdnLink}}{{ $company_logo }}" style="height: 40px;"></h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="task-stat pb-0">
                        <div class="d-flex tasks">
                            <div class="mb-0">
                                <div class="h6 fs-15 mb-0">Beneficiary Name : <span class="receipt_beneficiary_name"></span></div>
                            </div>
                            <span class="float-right ml-auto">Account Number : <span class="receipt_account_number"></span></span>
                        </div>

                        <div class="d-flex tasks">
                            <div class="mb-0">
                                <div class="h6 fs-15 mb-0">Bank Name : <span class="receipt_bank_name"></span></div>
                            </div>
                            <span class="float-right ml-auto">IFSC Code : <span class="receipt_ifsc"></span></span>
                        </div>

                        <div class="d-flex tasks">
                            <div class="mb-0">
                                <div class="h6 fs-15 mb-0">Remiter Name : <span class="receipt_remiter_name"></span></div>
                            </div>
                            <span class="float-right ml-auto">Remiter Number : <span class="receipt_remiter_number"></span></span>
                        </div>

                        <div class="d-flex tasks">
                            <div class="mb-0">
                                <div class="h6 fs-15 mb-0">Payment Mode : <span class="receipt_payment_mode"></span></div>
                            </div>
                            <span class="float-right ml-auto">Full Amount : <span class="receipt_full_amount"></span></span>
                        </div>



                    </div>
                </div>

                <div class="table-responsive mb-0">
                    <table class="table table-hover table-bordered mb-0 text-md-nowrap text-lg-nowrap text-xl-nowrap table-striped ">
                        <thead>
                        <tr>
                            <th>Order Id</th>
                            <th>UTR</th>
                            <th>Amount</th>
                            <th>Charges</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody id="receipt_html">
                        </tbody>
                    </table>
                </div>

            </div>
            <div class="modal-footer">
                <a href="" class="btn ripple btn-primary" target="_blank" id="print_url">Print</a>
                <a href="" class="btn ripple btn-success" target="_blank" id="thermal_print">Thermal Print</a>
                <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

{{--transaction-receipt-model End--}}



<div class="modal  show" id="search-by-account-beneficiaries"data-toggle="modal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Beneficiary List</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0 text-md-nowrap text-lg-nowrap text-xl-nowrap table-striped ">
                    <thead>
                    <tr>
                        <th>Mobile Number</th>
                        <th>Account Number</th>
                        <th>IFSC Code</th>
                        <th>Bank Name</th>
                        <th>Name</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody class="searchByAccountBeneficiaries">
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>
