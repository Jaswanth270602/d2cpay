@extends('agent.layout.header')
@section('content')



    <div class="main-content-body">
        <div class="row row-sm">

            @include('agent.developer.left_side')

            <div class="col-lg-8 col-xl-9">









                <div class="card" id="basic-alert">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Trasnfer Payment</h6>
                        </div>
                        <hr>



                        <table class="table main-table-reference mt-0 mb-0">
                            <thead>
                            <tr>
                                <th>Parameter</th>
                                <th>Type</th>
                                <th>Validation Rules</th>
                                <th>Description</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>api_token</td>
                                <td>String</td>
                                <td>required</td>
                                <td>The token used to authenticate the API request.</td>
                            </tr>
                            <tr>
                                <td>mobile_number</td>
                                <td>String</td>
                                <td>required, digits:10</td>
                                <td>The mobile number of the beneficiary (10 digits).</td>
                            </tr>
                            <tr>
                                <td>email</td>
                                <td>String</td>
                                <td>required, email</td>
                                <td>The email address of the user, must be a valid email format.</td>
                            </tr>
                            <tr>
                                <td>beneficiary_name</td>
                                <td>String</td>
                                <td>required</td>
                                <td>The name of the beneficiary.</td>
                            </tr>
                            <tr>
                                <td>ifsc_code</td>
                                <td>String</td>
                                <td>required, min:11, max:11</td>
                                <td>The IFSC code of the beneficiary's bank, must be exactly 11 characters.</td>
                            </tr>
                            <tr>
                                <td>account_number</td>
                                <td>String</td>
                                <td>required</td>
                                <td>The bank account number of the beneficiary.</td>
                            </tr>
                            <tr>
                                <td>amount</td>
                                <td>Number</td>
                                <td>required, numeric, between:min_amount,max_amount</td>
                                <td>The transaction amount, must be a numeric value between the provider's minimum and maximum allowed amounts.</td>
                            </tr>
                            <tr>
                                <td>channel_id</td>
                                <td>String</td>
                                <td>required</td>
                                <td>The ID of the transaction channel. Use <strong>1</strong> for NEFT and <strong>2</strong> for IMPS.</td>
                            </tr>
                            <tr>
                                <td>client_id</td>
                                <td>String</td>
                                <td>required</td>
                                <td>Your unique ID for the client. This ID is used to identify the client making the request.</td>
                            </tr>
                            </tbody>
                        </table>

                    </div>
                    <div class="card-footer">
                        <pre>POST: {{url('api/payout/v1/transfer-now')}}</pre>
                        <hr>
                        <pre style="color: #0ba360;">Success Response : {"status":"success","message":"Your payout was successful! Thank you for using our service.","utr":"value_of_utr","payid":"12345"}</pre>
                        <pre style="color: #f53c5b;">Failure Response : {"status":"failure","message":"error_message","utr":"","payid":"12345"}</pre>
                        <pre style="color: #ffc107;">Pending Response : {"status":"pending","message":"Your payout transaction is in process. Please wait for confirmation.","utr":"","payid":"12345"}</pre>
                        <hr>
                        <div class="alert alert-danger mg-b-0" role="alert">
                            If the status is <strong>pending</strong>, the actual status and UTR will be sent in the callback. Ensure you update the callback settings accordingly.                        </div>
                    </div>
                </div>




                <div class="card" id="basic-alert">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Check Balance</h6>
                        </div>
                        <hr>

                        <table class="table main-table-reference mt-0 mb-0">
                            <thead>
                            <tr>
                                <th class="wd-40p">ATTRIBUTE</th>
                                <th class="wd-60p">DESCRIPTIONS</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>api_token</td>
                                <td>Api token provider by {{ $company_website }} OR <a href="{{url('agent/developer/settings')}}">Click Here</a> </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <pre>GET: {{url('api/telecom/v1/check-balance')}}?api_token=[api_token]</pre>
                        <hr>
                        <pre>Response : {"status":"success","balance":{"normal_balance":"2,999.1"}}</pre>
                    </div>
                </div>



                <div class="card" id="basic-alert">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Check Status</h6>
                        </div>
                        <hr>

                        <table class="table main-table-reference mt-0 mb-0">
                            <thead>
                            <tr>
                                <th class="wd-40p">ATTRIBUTE</th>
                                <th class="wd-60p">DESCRIPTIONS</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>api_token</td>
                                <td>Api token provider by {{ $company_website }} OR <a href="{{url('agent/developer/settings')}}">Click Here</a> </td>
                            </tr>

                            <tr>
                                <td>client_id</td>
                                <td>your uniq id</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <pre>GET: {{url('api/telecom/v1/check-status')}}?api_token=[api_token]&client_id=[client_id]</pre>
                        <hr>
                        <pre style="color: #0ba360;">Success Response : {"status":true,"message":"Success: Data found successfully.","data":{"payid":673,"provider":"Payout","date":"2024-10-19 14:15:37","number":"1234567890","amount":"10.00","profit":"0.00","txnid":null,"client_id":"A1","ip_address":"","status":"Success"}}</pre>
                        <pre style="color: #f53c5b;">Failure Response : {"status":true,"message":"Success: Data found successfully.","data":{"payid":673,"provider":"Payout","date":"2024-10-19 14:15:37","number":"1234567890","amount":"10.00","profit":"0.00","txnid":null,"client_id":"A1","ip_address":"","status":"Failed"}}</pre>
                        <pre style="color: #ffc107;">Pending Response : {"status":true,"message":"Success: Data found successfully.","data":{"payid":673,"provider":"Payout","date":"2024-10-19 14:15:37","number":"1234567890","amount":"10.00","profit":"0.00","txnid":null,"client_id":"A1","ip_address":"","status":"Pending"}}</pre>

                    </div>
                </div>







            </div>
            <!--/div-->

        </div>

    </div>
    </div>
    </div>



@endsection