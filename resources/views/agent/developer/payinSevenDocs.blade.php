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
                                <td>The API token for authenticating the request (sent in Authorization header as Bearer token).</td>
                                <td>Yes</td>
                            </tr>
                            <tr>
                                <td><code>amount</code></td>
                                <td>numeric</td>
                                <td>The order amount, which must fall between the minimum and maximum allowed amounts (Min: ₹100, Max: ₹50,000).</td>
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
                                <td>The name of the customer initiating the transaction (max 255 characters).</td>
                                <td>Yes</td>
                            </tr>
                            <tr>
                                <td><code>mobile_number</code></td>
                                <td>string</td>
                                <td>The mobile number of the customer (exactly 10 digits).</td>
                                <td>Yes</td>
                            </tr>
                            <tr>
                                <td><code>email</code></td>
                                <td>string</td>
                                <td>The email address of the customer (max 255 characters).</td>
                                <td>Yes</td>
                            </tr>
                        </table>

                    </div>
                    <div class="card-footer">
                        <pre>POST: https://d2cpay.co/api/agent/add-money/v7/createOrder</pre>
                        <hr>
                        <pre style="color: #0ba360;">Success Response : {
    "status": "success",
    "message": "Payment initiated successfully",
    "data": {
        "qrString": "upi://pay?pa=merchant@ybl&pn=MerchantName&am=1500.00&tid=202407191719329711&tr=REQ1234562789",
        "qrCode": "data:image/svg+xml;base64,PD94bWwg......o=",
        "checkout_url": "https://safeppay.com/payment/process/page/202407191719329711",
        "txnid": "12345",
        "order_token": "ORD12345678901234",
        "transaction_id": "202407191719329711",
        "upi_intents": {
            "default": "upi://pay?pa=merchant@ybl...",
            "paytm": "paytmmp://upi/...",
            "phonepe": "phonepe://pay?...",
            "gpay": "tez://upi/pay?...",
            "bhim": "bhim://upi/pay?..."
        }
    }
}</pre>
                        <hr>
                        <pre style="color: #dc3545;">Error Response : {
    "status": "failure",
    "message": "Amount should be between 10 and 50000"
}</pre>
 <hr>
<pre>
If you found the UPI payment links returned by the API as URL-encoded, 
kindly decode them from your side before using or displaying them.

For example, the API may return:

Encoded UPI Link:
gpay://upi/pay?pa=vid.fibefashionltd@fino\u0026pn=FIBE%20PRIVATE%20LIMITED\u0026am=120\u0026cu=INR

After decoding, it becomes:
gpay://upi/pay?pa=vid.fibefashionltd@fino&pn=FIBE PRIVATE LIMITED&am=120&cu=INR

You can decode it easily using the below examples:

🟢 PHP Example:
$decodedUrl = urldecode($encodedUrl);

🟢 JavaScript Example:
const decodedUrl = decodeURIComponent(encodedUrl);

This decoding ensures that special characters like "&", "=", and spaces are properly displayed 
and the UPI link opens correctly in apps like GPay, PhonePe, Paytm, etc.
</pre>

                       
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
                                <td>The unique client ID for the transaction.</td>
                                <td>Yes</td>
                            </tr>
                        </table>
                    </div>
                    <div class="card-footer">
                        <pre>POST: https://d2cpay.co/api/agent/add-money/v7/status-enquiry</pre>
                        <hr>
                        <pre style="color: #0ba360;">Success Response : {
    "status": true,
    "message": "Transaction record found successfully!",
    "data": {
        "client_id": "your_client_id",
        "report_id": "67890",
        "amount": 1500,
        "utr": "520613452706",
        "status": "credit"
    }
}</pre>
                        <hr>
                        <pre style="color: #dc3545;">Error Response : {
    "status": false,
    "message": "No matching report found!"
}</pre>
                    </div>
                </div>

                <div class="card" id="basic-alert">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Callback Request</h6>
                        </div>
                        <hr>

                        <p>This callback notifies your system when a transaction is successfully processed. It includes key transaction data and a secure signature for verification.</p>
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
                                <td>Transaction status ("credit" for successful transactions)</td>
                            </tr>
                            <tr>
                                <td>client_id</td>
                                <td>string</td>
                                <td>Yes</td>
                                <td>Your unique transaction identifier</td>
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
                                <td>Bank UTR (Unique Transaction Reference) number</td>
                            </tr>
                            <tr>
                                <td>txnid</td>
                                <td>string</td>
                                <td>Yes</td>
                                <td>Our internal transaction ID</td>
                            </tr>
                            <tr>
                                <td>order_token</td>
                                <td>string</td>
                                <td>Yes</td>
                                <td>Order reference token</td>
                            </tr>
                            <tr>
                                <td>transaction_id</td>
                                <td>string</td>
                                <td>Yes</td>
                                <td>Gateway transaction ID</td>
                            </tr>
                            <tr>
                                <td>signature</td>
                                <td>string</td>
                                <td>Yes</td>
                                <td>HMAC-SHA256 signature for security verification</td>
                            </tr>
                        </table>

                        <br>
                        <h4>Signature Verification</h4>
                        <p>To verify the callback authenticity, compute the HMAC-SHA256 signature using your API Token as the secret key.</p>
                        <h5>Signature Verification Logic (PHP example):</h5>
                        <pre style="background:#1e1e1e; color:#d4d4d4; padding:1em; border-radius:8px; font-family:monospace; overflow:auto;">
<span style="color:#9cdcfe;">$receivedParams</span> = [
    <span style="color:#ce9178;">'status'</span> => <span style="color:#ce9178;">'credit'</span>,
    <span style="color:#ce9178;">'client_id'</span> => <span style="color:#ce9178;">'your_client_id'</span>,
    <span style="color:#ce9178;">'amount'</span> => <span style="color:#ce9178;">'1500'</span>,
    <span style="color:#ce9178;">'utr'</span> => <span style="color:#ce9178;">'520613452706'</span>,
    <span style="color:#ce9178;">'txnid'</span> => <span style="color:#ce9178;">'12345'</span>,
    <span style="color:#ce9178;">'order_token'</span> => <span style="color:#ce9178;">'ORD12345678901234'</span>,
    <span style="color:#ce9178;">'transaction_id'</span> => <span style="color:#ce9178;">'202407191719329711'</span>,
];

<span style="color:#9cdcfe;">$receivedSignature</span> = <span style="color:#9cdcfe;">$_GET</span>[<span style="color:#ce9178;">'signature'</span>];

<span style="color:#9cdcfe;">$signatureString</span> = <span style="color:#dcdcaa;">http_build_query</span>(<span style="color:#9cdcfe;">$receivedParams</span>);
<span style="color:#9cdcfe;">$calculatedSignature</span> = <span style="color:#dcdcaa;">hash_hmac</span>(<span style="color:#ce9178;">'sha256'</span>, <span style="color:#9cdcfe;">$signatureString</span>, <span style="color:#ce9178;">'your_api_token'</span>);

<span style="color:#c586c0;">if</span> (<span style="color:#dcdcaa;">hash_equals</span>(<span style="color:#9cdcfe;">$calculatedSignature</span>, <span style="color:#9cdcfe;">$receivedSignature</span>)) {
    <span style="color:#6a9955;">// Signature is valid - process the callback</span>
    <span style="color:#dcdcaa;">echo</span> <span style="color:#ce9178;">"SUCCESS"</span>;
} <span style="color:#c586c0;">else</span> {
    <span style="color:#6a9955;">// Invalid signature - reject the callback</span>
    <span style="color:#dcdcaa;">echo</span> <span style="color:#ce9178;">"INVALID SIGNATURE"</span>;
}
</pre>

                    </div>

                    <div class="card-footer">
                        <pre>GET: https://your-website.com/callback-url?status=credit&client_id=xyz&amount=1500&utr=520613452706&txnid=12345&order_token=ORD12345&transaction_id=202407191719329711&signature=abcd</pre>
                    </div>
                </div>

                <hr>
                <div class="card" id="basic-alert">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Authentication</h6>
                        </div>
                        <hr>
                        <p>All API requests must include your API token in the Authorization header:</p>
                        <pre style="background:#f8f9fa; padding:1em; border-radius:5px;">Authorization: Bearer YOUR_API_TOKEN</pre>
                        <p>Alternatively, you can send the token as a POST parameter:</p>
                        <pre style="background:#f8f9fa; padding:1em; border-radius:5px;">api_token: YOUR_API_TOKEN</pre>
                    </div>
                </div>

                <hr>
                <div class="card" id="basic-alert">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Features & Integration Benefits</h6>
                        </div>
                        <hr>

                        <p><strong>Payin 7</strong> provides advanced payment gateway integration with comprehensive UPI support and multiple payment channels:</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6><strong>Payment Methods:</strong></h6>
                                <ul>
                                    <li>💳 <strong>Checkout URL</strong> - Web-based payment page</li>
                                    <li>📱 <strong>UPI Intent</strong> - Direct UPI app integration (Paytm, PhonePe, GPay, BHIM)</li>
                                    <li>🔗 <strong>Deep Linking</strong> - Seamless app-to-app payments</li>
                                    <li>📊 <strong>QR Code</strong> - Scan and pay functionality</li>
                                    <li>💰 <strong>Card Payments</strong> - Credit/Debit card support</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6><strong>Key Features:</strong></h6>
                                <ul>
                                    <li>✅ Real-time transaction processing</li>
                                    <li>✅ Secure HMAC-SHA256 signatures</li>
                                    <li>✅ Instant webhook callbacks</li>
                                    <li>✅ Comprehensive status tracking</li>
                                    <li>✅ Multi-format UPI support (default, app-specific)</li>
                                    <li>✅ Automatic token generation</li>
                                    <li>✅ Robust error handling</li>
                                </ul>
                            </div>
                        </div>

                        <div class="alert alert-info mt-3">
                            <strong>💡 Integration Tip:</strong> Use the UPI intent deeplinks for mobile applications to provide seamless payment experience. The response includes app-specific intents for Paytm, PhonePe, GPay, and BHIM, allowing users to pay directly through their preferred UPI app without manual entry.
                        </div>

                        <div class="alert alert-warning mt-3">
                            <strong>⚠️ Important:</strong> Always verify callback signatures to ensure transaction authenticity. Store your API token securely and never expose it in client-side code. The payment session expires after a certain time, so ensure users complete the payment promptly.
                        </div>

                        <div class="alert alert-success mt-3">
                            <strong>🎯 Use Case Examples:</strong>
                            <ul class="mb-0">
                                <li><strong>E-commerce:</strong> Use checkout_url for web payments</li>
                                <li><strong>Mobile Apps:</strong> Use UPI intents with app-specific deeplinks</li>
                                <li><strong>POS Systems:</strong> Display QR code for quick scanning</li>
                                <li><strong>Hybrid:</strong> Offer multiple payment options simultaneously</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <hr>
                <div class="card" id="basic-alert">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title mb-1">Transaction Flow</h6>
                        </div>
                        <hr>
                        
                        <div class="row">
                            <div class="col-12">
                                <h6><strong>Standard Payment Flow:</strong></h6>
                                <ol>
                                    <li><strong>Initiate Payment:</strong> Call <code>createOrder</code> API with customer details</li>
                                    <li><strong>Receive Payment Options:</strong> Get checkout URL, UPI intents, QR code, and deeplinks</li>
                                    <li><strong>Customer Payment:</strong> Customer completes payment via chosen method</li>
                                    <li><strong>Webhook Notification:</strong> Receive instant callback at your specified URL</li>
                                    <li><strong>Verify Signature:</strong> Validate the callback using HMAC-SHA256</li>
                                    <li><strong>Status Check (Optional):</strong> Use <code>status-enquiry</code> for manual verification</li>
                                    <li><strong>Complete Order:</strong> Process the order in your system</li>
                                </ol>
                            </div>
                        </div>

                        <div class="alert alert-primary mt-3">
                            <strong>📌 Best Practices:</strong>
                            <ul class="mb-0">
                                <li>Store <code>order_token</code> and <code>transaction_id</code> for reference</li>
                                <li>Implement idempotency to handle duplicate callbacks</li>
                                <li>Use status enquiry API for reconciliation</li>
                                <li>Handle all payment statuses (success, pending, failed)</li>
                                <li>Implement timeout handling for expired sessions</li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
            <!--/div-->

        </div>
    </div>

@endsection