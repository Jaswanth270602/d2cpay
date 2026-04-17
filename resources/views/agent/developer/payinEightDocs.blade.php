@extends('agent.layout.header')
@section('content')

    <div class="main-content-body">
        <div class="row row-sm">

            @include('agent.developer.left_side')

            <div class="col-lg-8 col-xl-9">

                {{-- Create Order --}}
                <div class="card" id="basic-alert">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Create Order </h6>
                        </div>
                        <hr>

                        <table class="table main-table-reference mt-0 mb-0">
                            <tr>
                                <th>Parameter</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Required</th>
                            </tr>
                            <tr>
                                <td><code>api_token</code></td>
                                <td>string</td>
                                <td>Your API token for authentication (send as Bearer token in the Authorization header).</td>
                                <td>Yes</td>
                            </tr>
                            <tr>
                                <td><code>amount</code></td>
                                <td>numeric</td>
                                <td>The order amount. Must be between the configured minimum and maximum limits for your ZigPay MID.</td>
                                <td>Yes</td>
                            </tr>
                            <tr>
                                <td><code>client_id</code></td>
                                <td>string</td>
                                <td>Your unique reference for this transaction. This will be echoed back in callbacks and status enquiry.</td>
                                <td>Yes</td>
                            </tr>
                            <tr>
                                <td><code>callback_url</code></td>
                                <td>string</td>
                                <td>URL on your server that will receive the transaction status callback when payment is completed.</td>
                                <td>Yes</td>
                            </tr>
                            <tr>
                                <td><code>customer_name</code></td>
                                <td>string</td>
                                <td>Full name of the customer initiating the transaction (max 255 characters).</td>
                                <td>Yes</td>
                            </tr>
                            <tr>
                                <td><code>mobile_number</code></td>
                                <td>string</td>
                                <td>Customer mobile number (10 digits).</td>
                                <td>Yes</td>
                            </tr>
                            <tr>
                                <td><code>email</code></td>
                                <td>string</td>
                                <td>Customer email address (valid email, max 255 characters).</td>
                                <td>Yes</td>
                            </tr>
                        </table>
                    </div>
                    <div class="card-footer">
                        <pre>POST: {{ url('api/add-money/v8/createOrder') }}</pre>
                        <hr>
<pre style="color:#0ba360;">Success Response :
{
    "status": "success",
    "message": "Order created successfully",
    "data": {
        "txnid": 12345,
        "order_token": "ZIG12345...",
        "transaction_id": "TPAY202604161253332117010",
        "qrString": "upi://pay?pa=merchant@upi&amp;pn=Merchant Name&amp;am=101.00&amp;cu=INR...",
        "upiLink": "upi://pay?pa=merchant@upi&amp;pn=Merchant Name&amp;am=101.00&amp;cu=INR...",
        "status": "pending"
    }
}</pre>
                        <hr>
<pre style="color:#dc3545;">Error Response :
{
    "status": "failure",
    "message": "Unable to generate ZigPay token"
}
or
{
    "status": "failure",
    "message": "ZigPay MID minimum is Rs. XXX. Please enter Rs. XXX or above."
}</pre>
                    </div>
                </div>

                {{-- Status Enquiry --}}
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
                                <th>Required</th>
                            </tr>
                            <tr>
                                <td><code>api_token</code></td>
                                <td>string</td>
                                <td>Your API token for authentication (Bearer token).</td>
                                <td>Yes</td>
                            </tr>
                            <tr>
                                <td><code>client_id</code></td>
                                <td>string</td>
                                <td>The same <code>client_id</code> you used when creating the order.</td>
                                <td>Yes</td>
                            </tr>
                        </table>
                    </div>
                    <div class="card-footer">
                        <pre>POST: {{ url('api/add-money/v8/status-enquiry') }}</pre>
                        <hr>
<pre style="color:#0ba360;">Success Response :
{
    "status": true,
    "message": "Transaction record found successfully!",
    "data": {
        "client_id": "your_client_id",
        "report_id": 67890,
        "amount": 101.00,
        "utr": "204162689734",
        "status": "credit"
    }
}</pre>
                        <hr>
<pre style="color:#dc3545;">Error Response :
{
    "status": false,
    "message": "No matching report found!"
}</pre>
                    </div>
                </div>

                {{-- Callback --}}
                <hr>
                <div class="card" id="basic-alert">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Callback Request</h6>
                        </div>
                        <hr>

                        <p>When a payment is completed, we will send a callback to your <code>callback_url</code> with the following fields:</p>

                        <table class="table main-table-reference mt-0 mb-0">
                            <tr>
                                <th>Parameter</th>
                                <th>Type</th>
                                <th>Required</th>
                                <th>Description</th>
                            </tr>
                            <tr>
                                <td><code>status_id</code></td>
                                <td>int</td>
                                <td>Yes</td>
                                <td>Transaction status code: 1 = Success, 2 = Pending, 3 = Failed.</td>
                            </tr>
                            <tr>
                                <td><code>amount</code></td>
                                <td>numeric</td>
                                <td>Yes</td>
                                <td>Paid amount.</td>
                            </tr>
                            <tr>
                                <td><code>utr</code></td>
                                <td>string</td>
                                <td>Yes</td>
                                <td>Bank UTR / RRN of the transaction.</td>
                            </tr>
                            <tr>
                                <td><code>client_id</code></td>
                                <td>string</td>
                                <td>Yes</td>
                                <td>Your original <code>client_id</code> or ZigPay reference that maps to the order.</td>
                            </tr>
                            <tr>
                                <td><code>message</code></td>
                                <td>string</td>
                                <td>Yes</td>
                                <td>Text message describing the transaction status.</td>
                            </tr>
                        </table>
                    </div>
                    <div class="card-footer">
<pre>Sample Callback (form-data or JSON) :
{
    "status_id": 1,
    "amount": "101.00",
    "utr": "204162689734",
    "client_id": "ZIG1771776357115",
    "message": "Transaction Successfully"
}</pre>
                        <hr>
<pre>Your callback endpoint:
https://your-domain.com/your-callback-url
</pre>
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection

