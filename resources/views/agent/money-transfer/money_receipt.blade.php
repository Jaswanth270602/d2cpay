

<div class="modal  show" id="transaction_confirmation_model"data-toggle="modal">
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
                            <span class="float-right ml-auto confirm_bank_name"></span>
                        </div>

                        <div class="d-flex tasks">
                            <div class="mb-0">
                                <div class="h6 fs-15 mb-0">Account Number: </div>
                            </div>
                            <span class="float-right ml-auto confirm_account_number"></span>
                        </div>


                        <div class="d-flex tasks">
                            <div class="mb-0">
                                <div class="h6 fs-15 mb-0">IFSC Code: </div>
                            </div>
                            <span class="float-right ml-auto confirm_ifsc_code"></span>
                        </div>

                        <div class="d-flex tasks">
                            <div class="mb-0">
                                <div class="h6 fs-15 mb-0">Holder Name: </div>
                            </div>
                            <span class="float-right ml-auto confirm_holder_name"></span>
                        </div>


                        <div class="d-flex tasks">
                            <div class="mb-0">
                                <div class="h6 fs-15 mb-0">Amount: </div>
                            </div>
                            <span class="float-right ml-auto confirm_amount"></span>
                        </div>

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

            <div class="modal-footer">
                <button class="btn ripple btn-primary" type="button" id="transfer_btn" onclick="transfer_now()">Transfer Now</button>
                <button class="btn ripple btn-primary" type="button" id="transfer_btn_loader" disabled style="display: none;"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</button>
                <button class="btn ripple btn-danger" aria-label="Close" class="close" data-dismiss="modal" type="button">Cancel</button>
            </div>
        </div>
    </div>
</div>




<div class="modal  show" id="get_charges_model"data-toggle="modal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Charges</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">

                <div class="card">
                    <div class="task-stat pb-0">
                        <div class="d-flex tasks">
                            <div class="mb-0">
                                <div class="h6 fs-15 mb-0">Amount</div>
                            </div>
                            <span class="float-right ml-auto">Charges</span>
                            <span class="float-right ml-auto">Total Amount</span>
                        </div>

                        <div class="charges_list"></div>




                    </div>
                </div>

            </div>
        </div>
    </div>
</div>



<div class="modal  show" id="money_receipt_model"data-toggle="modal">
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

<input type="hidden" id="money_millisecond">