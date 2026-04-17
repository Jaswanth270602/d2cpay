@extends('agent.layout.header')
@section('content')
    <style>


        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }

    </style>


    <div class="main-content-body">
        <div class="row row-sm">

            @include('agent.developer.left_side')

            <div class="col-lg-8 col-xl-9">
                <div class="card">
                    <div class="card-body">
                        <div class="mb-4 main-content-label">{{ $page_title }}</div>
                        <hr>
                        <div class="row">

                            <div class="card" id="basic-alert">
                                <div class="card-body">
                                    <div>
                                        <h6 class="card-title mb-1">Generate QR Code for Adding Money</h6>
                                    </div>
                                    <hr>

                                    <table class="table main-table-reference mt-0 mb-0">

                                        <tr>
                                            <th>Parameter</th>
                                            <th>Type</th>
                                            <th>Required</th>
                                            <th>Description</th>
                                        </tr>
                                        <tr>
                                            <td>api_token</td>
                                            <td>string</td>
                                            <td>Yes</td>
                                            <td>API token for authentication.</td>
                                        </tr>

                                        <tr>
                                            <td>amount</td>
                                            <td>numeric</td>
                                            <td>Yes</td>
                                            <td>Amount to be added (minimum 1)</td>
                                        </tr>

                                        <tr>
                                            <td>callback_url</td>
                                            <td>string (URL)</td>
                                            <td>Yes</td>
                                            <td>URL where payment status updates will be sent</td>
                                        </tr>

                                        <tr>
                                            <td>client_id</td>
                                            <td>string</td>
                                            <td>Yes</td>
                                            <td>Unique transaction identifier</td>
                                        </tr>

                                    </table>
                                </div>
                                <div class="card-footer">
                                    <pre>POST: {{url('api/add-money/v2/generate-qrcode')}}</pre>
                                    <hr>
                                    <pre>Response : {"status":"success","message":"Successful","data":{"qrString":"upi://pay?mc=7322&pa=VENKATAUDAYKUMARTONDAP-12439199.payu%40indus&am=10.00&tr=22596225826&pn=THE+MOTHER+S+TOUCH+EDUCATION+ACADEMY","txnid":38,"amount":"10"}}</pre>
                                </div>
                            </div>

                            <hr>

                            <div class="card" id="basic-alert">
                                <div class="card-body">
                                    <div>
                                        <h6 class="card-title mb-1">Status Enquiry</h6>
                                    </div>
                                    <hr>

                                    <table class="table main-table-reference mt-0 mb-0">

                                        <tr>
                                            <th>Parameter</th>
                                            <th>Type</th>
                                            <th>Description</th>
                                        </tr>

                                        <tbody>
                                        <tr>
                                            <td><code>api_token</code></td>
                                            <td>String</td>
                                            <td>Your API token for authentication.</td>
                                        </tr>
                                        <tr>
                                            <td><code>client_id</code></td>
                                            <td>String</td>
                                            <td><strong>client_id</strong> (string) - Required. The unique client ID for the transaction</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="card-footer">
                                    <pre>POST: {{url('api/add-money/v2/status-enquiry')}}</pre>
                                    <hr>
                                    <pre>Response : {"status":true,"message":"Record found!","data":{"client_id":"123456789","report_id":"987654321","amount":1000,"utr":"UTR123456789","status":"credit"}}</pre>
                                </div>
                            </div>

                            <hr>

                            <div class="card" id="basic-alert">
                                <div class="card-body">
                                    <div>
                                        <h6 class="card-title mb-1">Callback Request</h6>
                                    </div>
                                    <hr>

                                    <p>This Callback API is used to receive the transaction status update after a payment is processed. The merchant's system should be ready to accept and process the callback data.</p>
                                    <table class="table main-table-reference mt-0 mb-0">
                                        <tr>
                                            <th>Parameter</th>
                                            <th>Type</th>
                                            <th>Required</th>
                                            <th>Description</th>
                                        </tr>
                                        <tr>
                                            <td>status</td>
                                            <td>string</td>
                                            <td>Yes</td>
                                            <td>Transaction status (e.g., credit, failed)</td>
                                        </tr>

                                        <tr>
                                            <td>client_id</td>
                                            <td>string</td>
                                            <td>Yes</td>
                                            <td>Unique transaction identifier</td>
                                        </tr>

                                        <tr>
                                            <td>amount</td>
                                            <td>numeric</td>
                                            <td>Yes</td>
                                            <td>Transaction amount</td>
                                        </tr>

                                        <tr>
                                            <td>utr</td>
                                            <td>string</td>
                                            <td>Yes</td>
                                            <td>Unique Transaction Reference (UTR) number</td>
                                        </tr>

                                        <tr>
                                            <td>payerVPA</td>
                                            <td>string</td>
                                            <td>Yes</td>
                                            <td>UPI ID of the payer</td>
                                        </tr>


                                    </table>
                                </div>
                                <div class="card-footer">
                                    <pre>GET: https://merchant-website.com/callback-url</pre>
                                </div>
                            </div>

                            <hr>




                        </div>
                    </div>

                </div>
            </div>
            <!--/div-->

        </div>

    </div>
    </div>
    </div>



@endsection