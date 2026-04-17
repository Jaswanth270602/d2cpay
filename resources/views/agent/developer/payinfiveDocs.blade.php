@extends('agent.layout.header')
@section('content')



    <div class="main-content-body">
        <div class="row row-sm">

            @include('agent.developer.left_side')

            <div class="col-lg-8 col-xl-9">









                <div class="card" id="basic-alert">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Create Order</h6>
                        </div>
                        <hr>



                        <table class="table main-table-reference mt-0 mb-0"> <tr>
                                <th>Parameter</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Required</th>
                            </tr>
                            <tr>
                                <td><code>api_token</code></td>
                                <td>string</td>
                                <td>The API token for authenticating the request.</td>
                                <td>Yes</td>
                            </tr>
                            <tr>
                                <td><code>amount</code></td>
                                <td>numeric</td>
                                <td>The order amount, which must fall between the minimum and maximum allowed amounts.</td>
                                <td>Yes</td>
                            </tr>
                            <tr>
                                <td><code>client_id</code></td>
                                <td>string</td>
                                <td>A unique identifier for the client making the request.</td>
                                <td>Yes</td>
                            </tr>

                            <tr>
                                <td><code>callback_url</code></td>
                                <td>string</td>
                                <td>The URL to call after order processing is complete, typically used for order status updates.</td>
                                <td>Yes</td>
                            </tr>

                            <tr>
                                <td><code>customer_name</code></td>
                                <td>string</td>
                                <td>The name of the customer initiating the transaction.</td>
                                <td>Yes</td>
                            </tr>

                            <tr>
                                <td><code>mobile_number</code></td>
                                <td>string</td>
                                <td>The mobile number of the customer.</td>
                                <td>Yes</td>
                            </tr>

                            <tr>
                                <td><code>email</code></td>
                                <td>string</td>
                                <td>The email address of the customer.</td>
                                <td>Yes</td>
                            </tr>
                        </table>

                    </div>
                    <div class="card-footer">
                        <pre>POST: {{url('api/add-money/v5/createOrder')}}</pre>
                        <hr>
                        <pre style="color: #0ba360;">Success Response : {"status":"success","message":"Order created success","data":{"imageUrl":"","txnid":4928}}</pre>
                        <hr>
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
                        <pre>POST: {{url('api/add-money/v5/status-enquiry')}}</pre>
                        <hr>
                        <pre>Response : {"status":true,"message":"Transaction record found successfully!","data":{"client_id":"your id","report_id":"our report id","amount":100,"utr":"Bank UTR number","status":"credit"}}</pre>
                    </div>
                </div>



                <div class="card" id="basic-alert">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Callback Request</h6>
                        </div>
                        <hr>

                        <p>This callback notifies your system when a transaction is successfully processed (status = credit). It includes key transaction data and a secure signature for verification.</p>
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
                                <td>txnid</td>
                                <td>string</td>
                                <td>Yes</td>
                                <td>Create order txnid</td>
                            </tr>

                            <tr>
                                <td>signature</td>
                                <td>string</td>
                                <td>Yes</td>
                                <td>Encrypted HMAC-SHA256 signature (for verification)</td>
                            </tr>


                        </table>

                        <br>
                        <h4>Signature Generation</h4>
                        <p>To verify the callback, compute the HMAC-SHA256 signature using your API Token as the secret key.</p>
                        <h5>Signature Creation Logic (PHP example):</h5>
                        <pre style="background:#1e1e1e; color:#d4d4d4; padding:1em; border-radius:8px; font-family:monospace; overflow:auto;">
<span style="color:#9cdcfe;">$queryParams</span> = [
    <span style="color:#ce9178;">'status'</span> => <span style="color:#ce9178;">'credit'</span>,
    <span style="color:#ce9178;">'client_id'</span> => <span style="color:#ce9178;">'your_client_id'</span>,
    <span style="color:#ce9178;">'amount'</span> => <span style="color:#ce9178;">'100.00'</span>,
    <span style="color:#ce9178;">'utr'</span> => <span style="color:#ce9178;">'1234567890'</span>,
    <span style="color:#ce9178;">'txnid'</span> => <span style="color:#ce9178;">'TXN123456'</span>,
];

<span style="color:#9cdcfe;">$signatureString</span> = <span style="color:#dcdcaa;">http_build_query</span>(<span style="color:#9cdcfe;">$queryParams</span>);

<span style="color:#6a9955;">// $signatureString value:</span>
<span style="color:#6a9955;">// status=credit&amp;client_id=your_client_id&amp;amount=100.00&amp;utr=1234567890&amp;txnid=TXN123456</span>

<span style="color:#9cdcfe;">$signature</span> = <span style="color:#dcdcaa;">hash_hmac</span>(
    <span style="color:#ce9178;">'sha256'</span>,
    <span style="color:#9cdcfe;">$signatureString</span>,
    <span style="color:#ce9178;">'your_api_token'</span>
);
</pre>

                    </div>


                    <div class="card-footer">
                        <pre>GET: https://merchant-website.com/callback-url</pre>
                    </div>


                </div>

            </div>
            <!--/div-->

        </div>

    </div>
    </div>
    </div>



@endsection