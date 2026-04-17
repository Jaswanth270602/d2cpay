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
                                <td><code>redirect_url</code></td>
                                <td>string</td>
                                <td>The URL to redirect the user to after the order creation.</td>
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
                        <pre>POST: {{url('api/add-money/v3/createOrder')}}</pre>
                        <hr>
                        <pre style="color: #0ba360;">Success Response : {"status":"success","message":"Successfully created","payment_url":"","order_id":2473}</pre>
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
                        <pre>POST: {{url('api/add-money/v3/status-enquiry')}}</pre>
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








            </div>
            <!--/div-->

        </div>

    </div>
    </div>
    </div>



@endsection