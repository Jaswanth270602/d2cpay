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
                            <h6 class="card-title mb-1">Create Order</h6>
                        </div>
                        <hr>

                        <p>Creates a Payin 9 UPI collection order. Send your API token as a <strong>Bearer</strong> token in the <code>Authorization</code> header. Request body may be sent as <code>application/json</code> or <code>application/x-www-form-urlencoded</code> (form fields).</p>

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
                                <td>Your API token (header: <code>Authorization: Bearer &lt;api_token&gt;</code>). Found in Developer &rarr; Settings.</td>
                                <td>Yes</td>
                            </tr>
                            <tr>
                                <td><code>amount</code></td>
                                <td>numeric</td>
                                <td>Order amount in INR. Must be between the configured minimum and maximum limits for your MID.</td>
                                <td>Yes</td>
                            </tr>
                            <tr>
                                <td><code>client_id</code></td>
                                <td>string</td>
                                <td>Your unique reference for this transaction. Must be unique per order. Echoed in callbacks and status enquiry.</td>
                                <td>Yes</td>
                            </tr>
                            <tr>
                                <td><code>callback_url</code></td>
                                <td>string (URL)</td>
                                <td>Your server URL that receives a <strong>GET</strong> callback when payment succeeds (see Callback section below).</td>
                                <td>Yes</td>
                            </tr>
                            <tr>
                                <td><code>customer_name</code></td>
                                <td>string</td>
                                <td>Customer full name (max 255 characters).</td>
                                <td>Yes</td>
                            </tr>
                            <tr>
                                <td><code>mobile_number</code></td>
                                <td>string</td>
                                <td>Customer mobile number (exactly 10 digits).</td>
                                <td>Yes</td>
                            </tr>
                            <tr>
                                <td><code>email</code></td>
                                <td>string</td>
                                <td>Customer email address (valid email, max 255 characters).</td>
                                <td>Yes</td>
                            </tr>
                        </table>

                        <hr>
                        <h6 class="card-title mb-1">Sample Request (cURL)</h6>
<pre>curl -X POST "{{ url('api/add-money/v9/createOrder') }}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -d "amount=101" \
  -d "client_id=PAYIN_PROD_20260709120000" \
  -d "callback_url=https://your-domain.com/qpc/callback" \
  -d "customer_name=Test Customer" \
  -d "mobile_number=9876543210" \
  -d "email=test@example.com"</pre>
                        <p class="text-muted mb-0"><small>Use a new <code>client_id</code> for every order. On Windows PowerShell, use <code>curl.exe</code> and backtick (<code>`</code>) for line breaks, or send all <code>-d</code> fields on one line.</small></p>
                    </div>
                    <div class="card-footer">
                        <pre>POST: {{ url('api/add-money/v9/createOrder') }}</pre>
                        <hr>
<pre style="color:#0ba360;">Success Response :
{
    "status": "success",
    "message": "success",
    "data": {
        "txnid": 1131,
        "order_token": "QPI1131114304",
        "transaction_id": "4872520927813632",
        "qrString": "upi://pay?pa=merchant@upi&amp;pn=Merchant&amp;am=101.99&amp;tr=eF2CB&amp;tn=eF2CB&amp;cu=INR",
        "qrCodeUrl": "",
        "status": "pending",
        "report_id": 813,
        "upi_link": "upi://pay?pa=merchant@upi&amp;pn=Merchant&amp;am=101.99&amp;tr=eF2CB&amp;tn=eF2CB&amp;cu=INR",
        "upi_intent": "upi://pay?pa=merchant@upi&amp;pn=Merchant&amp;am=101.99&amp;tr=eF2CB&amp;tn=eF2CB&amp;cu=INR",
        "upi_phonepe": "phonepe://native?data=...&amp;id=p2pContactChat",
        "upi_gpay": "gpay://upi/pay?pa=merchant@upi&amp;pn=Merchant&amp;am=101.99&amp;tr=eF2CB&amp;tn=eF2CB&amp;cu=INR",
        "upi_paytm": "paytmmp://cash_wallet?pa=merchant@upi&amp;pn=Merchant&amp;am=101.99&amp;tr=eF2CB&amp;tn=eF2CB&amp;cu=INR&amp;featuretype=money_transfer",
        "payment_status": "PENDING",
    }
}</pre>
                        <hr>
                        <table class="table main-table-reference mt-0 mb-0">
                            <tr>
                                <th>Response field</th>
                                <th>Description</th>
                            </tr>
                            <tr>
                                <td><code>txnid</code></td>
                                <td>Internal gateway order ID on d2cpay.</td>
                            </tr>
                            <tr>
                                <td><code>order_token</code></td>
                                <td>Merchant order reference sent to the payment gateway (e.g. <code>QPI1131114304</code>).</td>
                            </tr>
                            <tr>
                                <td><code>transaction_id</code></td>
                                <td>Provider platform order / transaction ID.</td>
                            </tr>
                            <tr>
                                <td><code>report_id</code></td>
                                <td>Transaction report ID in d2cpay (pending until payment is confirmed).</td>
                            </tr>
                            
                            <tr>
                                <td><code>upi_intent</code> / <code>upi_link</code></td>
                                <td>Generic UPI intent deep link. Open on a mobile device with a UPI app installed.</td>
                            </tr>
                            <tr>
                                <td><code>upi_phonepe</code></td>
                                <td>PhonePe-specific deep link.</td>
                            </tr>
                            <tr>
                                <td><code>upi_gpay</code></td>
                                <td>Google Pay-specific deep link.</td>
                            </tr>
                            <tr>
                                <td><code>upi_paytm</code></td>
                                <td>Paytm-specific deep link.</td>
                            </tr>
                            <tr>
                                <td><code>payment_status</code></td>
                                <td>Gateway status at creation time (usually <code>PENDING</code>).</td>
                            </tr>
                           
                        </table>
                        <p class="text-muted mt-2 mb-0"><small>The payable amount inside UPI links (e.g. <code>am=101.99</code>) may differ slightly from the requested <code>amount</code> due to gateway fee rounding.</small></p>
                        <hr>
<pre style="color:#dc3545;">Error Response :
{
    "status": "failure",
    "message": "The amount field is required."
}
or
{
    "status": "failure",
    "message": "Service not active!"
}
or
{
    "status": "failure",
    "message": " credentials not configured"
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

                        <p>Poll transaction status using the same <code>client_id</code> from create order. If the order is still pending, d2cpay will attempt to sync status from the gateway before responding.</p>

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
                                <td>Your API token (Bearer token in Authorization header).</td>
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
                        <pre>POST: {{ url('api/add-money/v9/status-enquiry') }}</pre>
                        <hr>
<pre style="color:#0ba360;">Success — Payment credited :
{
    "status": true,
    "message": "Transaction record found successfully!",
    "data": {
        "client_id": "PAYIN_PROD_20260709120000",
        "report_id": 813,
        "amount": 101,
        "utr": "010555695027",
        "status": "credit"
    }
}</pre>
                        <hr>
<pre style="color:#ffc107;">Pending :
{
    "status": true,
    "message": "Transaction is pending",
    "data": {
        "client_id": "PAYIN_PROD_20260709120000",
        "report_id": 813,
        "amount": 101,
        "utr": "",
        "status": "pending"
    }
}</pre>
                        <hr>
<pre style="color:#dc3545;">Failed :
{
    "status": true,
    "message": "Transaction failed",
    "data": {
        "client_id": "PAYIN_PROD_20260709120000",
        "report_id": 813,
        "amount": 101,
        "utr": "",
        "status": "failed"
    }
}</pre>
                        <hr>
<pre style="color:#dc3545;">Error Response :
{
    "status": false,
    "message": "No matching order found!"
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

                        <p>When payment is <strong>successfully credited</strong>, d2cpay sends a <strong>GET</strong> request to your <code>callback_url</code> with query parameters. Verify the <code>signature</code> before marking the order paid.</p>

                        <table class="table main-table-reference mt-0 mb-0">
                            <tr>
                                <th>Parameter</th>
                                <th>Type</th>
                                <th>Required</th>
                                <th>Description</th>
                            </tr>
                            <tr>
                                <td><code>status</code></td>
                                <td>string</td>
                                <td>Yes</td>
                                <td><code>credit</code> when payment is successful.</td>
                            </tr>
                            <tr>
                                <td><code>client_id</code></td>
                                <td>string</td>
                                <td>Yes</td>
                                <td>Your original <code>client_id</code> from create order.</td>
                            </tr>
                            <tr>
                                <td><code>amount</code></td>
                                <td>numeric</td>
                                <td>Yes</td>
                                <td>Credited transaction amount.</td>
                            </tr>
                            <tr>
                                <td><code>utr</code></td>
                                <td>string</td>
                                <td>Yes</td>
                                <td>Bank UTR / RRN of the transaction.</td>
                            </tr>
                            <tr>
                                <td><code>txnid</code></td>
                                <td>int</td>
                                <td>Yes</td>
                                <td>Internal gateway order ID (<code>data.txnid</code> from create order response).</td>
                            </tr>
                            <tr>
                                <td><code>signature</code></td>
                                <td>string</td>
                                <td>Yes</td>
                                <td>HMAC-SHA256 of the query string (without <code>signature</code>) using your API token as the secret.</td>
                            </tr>
                        </table>

                        <hr>
                        <h6 class="card-title mb-1">Signature verification</h6>
<pre>// Build query string from callback params (exclude signature)
$params = [
    'status'   => 'credit',
    'client_id'=> 'PAYIN_PROD_20260709120000',
    'amount'   => 101,
    'utr'      => '010555695027',
    'txnid'    => 1131,
];
$signatureString = http_build_query($params);
$expected = hash_hmac('sha256', $signatureString, YOUR_API_TOKEN);
// Compare $expected with $_GET['signature']</pre>
                    </div>
                    <div class="card-footer">
<pre>Sample callback URL (GET) :
https://your-domain.com/qpc/callback?status=credit&amp;client_id=PAYIN_PROD_20260709120000&amp;amount=101&amp;utr=010555695027&amp;txnid=1131&amp;signature=abc123...</pre>
                        <hr>
                        <p class="mb-0"><small><strong>Note:</strong> Use the <code>callback_url</code> you pass in create order. This is separate from the gateway webhook (<code>/api/call-back/qpc-payin</code>) which is handled internally by d2cpay.</small></p>
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection
