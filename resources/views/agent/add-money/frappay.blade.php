@extends('agent.layout.header')
@section('content')

<style>
    body {
        background: linear-gradient(135deg, #8EC5FC 0%, #E0C3FC 100%);
        min-height: 100vh;
    }

    .unique-form-wrapper {
        position: relative;
        z-index: 2;
        transition: transform 0.4s ease;
    }
    .unique-form-wrapper.shift-left {
        transform: translateX(-120px);
    }

    .unique-card {
        background: rgba(255, 255, 255, 0.78);
        backdrop-filter: blur(8px);
        border-radius: 18px;
        padding: 32px;
        color: #1d1d1f;
        border: 1px solid rgba(255,255,255,0.6);
        box-shadow: 0 12px 40px rgba(0,0,0,0.12);
        transition: 0.25s ease-in-out;
    }
    .unique-card:hover { transform: translateY(-4px); }

    .unique-title {
        font-size: 26px;
        font-weight: 800;
        text-align: center;
        margin-bottom: 8px;
        background: linear-gradient(90deg, #00dbde, #fc00ff);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .unique-sub { text-align:center; color:#3a3a3a; margin-bottom: 18px; }

    .form-group { position: relative; margin-bottom: 22px; }
    .form-control {
        background: rgba(255,255,255,0.9);
        border: 2px solid rgba(0,0,0,0.08);
        border-radius: 12px;
        color: #111;
        padding: 14px 14px;
        font-size: 16px;
        width: 100%;
        transition: all 0.25s ease-in-out;
    }
    .form-control:focus {
        border-color: #7b61ff;
        box-shadow: 0 0 0 4px rgba(123,97,255,0.15);
        outline: none;
    }
    .form-label {
        position: absolute;
        left: 14px;
        top: 12px;
        color: #555;
        pointer-events: none;
        transition: 0.25s ease;
    }
    .form-control:focus + .form-label,
    .form-control:not(:placeholder-shown) + .form-label {
        top: -10px;
        left: 10px;
        font-size: 12px;
        color: #7b61ff;
        background: #fff;
        padding: 0 6px;
        border-radius: 6px;
        border: 1px solid rgba(0,0,0,0.06);
    }

    .btn-neon {
        display: inline-block;
        border-radius: 12px;
        padding: 12px 18px;
        font-weight: 700;
        text-align: center;
        border: none;
        background: linear-gradient(90deg, #00dbde, #fc00ff);
        color: #fff;
        transition: all 0.25s;
        width: 100%;
    }
    .btn-neon:hover { transform: translateY(-1px) scale(1.01); box-shadow: 0 12px 25px rgba(124, 97, 255, 0.35); }
    .btn-neon:disabled { opacity: 0.5; cursor: not-allowed; }

    .btn-ghost {
        width: 100%;
        margin-top: 10px;
        border-radius: 12px;
        padding: 12px 18px;
        font-weight: 700;
        background: #ffffff;
        color: #333;
        border: 2px solid rgba(0,0,0,0.08);
        transition: 0.25s;
    }
    .btn-ghost:hover { background:#f6f6f6; }

    #error-card {
        position: fixed;
        top: 80px;
        right: -380px;
        width: 320px;
        background: #ffeaea;
        border-left: 6px solid #ff4d4f;
        border-radius: 14px 0 0 14px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        padding: 18px 20px;
        transition: right 0.4s ease;
        z-index: 9999;
    }
    #error-card.show { right: 30px; }
    #error-card h6 { margin: 0; font-size: 16px; font-weight: 800; color: #d00000; }
    #error-card p { margin: 6px 0 0; font-size: 14px; color: #333; }
    #error-card button {
        margin-top: 10px;
        padding: 6px 14px;
        border: none;
        border-radius: 8px;
        background: #ff4d4f;
        color: #fff;
        font-weight: 600;
        cursor: pointer;
    }

    #zigpay-result-success, #zigpay-result-failure, #zigpay-result-timeout {
        padding: 16px;
        border-radius: 12px;
        margin-top: 8px;
        text-align: center;
    }
    #zigpay-result-success {
        background: rgba(16, 185, 129, 0.12);
        border: 1px solid rgba(16, 185, 129, 0.35);
        color: #065f46;
    }
    #zigpay-result-failure, #zigpay-result-timeout {
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.35);
        color: #991b1b;
    }
    #zigpay-status-pending {
        font-size: 14px;
        color: #5c5c5c;
        margin-top: 10px;
    }
</style>

<div class="main-content-body d-flex justify-content-center align-items-center" style="min-height:100vh;">
    <div class="col-md-5 unique-form-wrapper" id="form-wrapper">
        <div class="unique-card">
            <h3 class="unique-title">Payin 11</h3>
            <p class="unique-sub">FrapPay collection — enter amount to generate UPI QR / payment link</p>

            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" id="mobile_number" value="{{ Auth::User()->mobile }}">

            <div class="form-group">
                <input type="text" id="amount" class="form-control" placeholder=" " oninput="toggleBtn()">
                <label class="form-label" for="amount">Enter Amount ({{ $min_amount ?? 100 }} to {{ $max_amount ?? 50000 }})</label>
                <ul class="parsley-errors-list filled" style="margin:6px 0 0 2px;">
                    <li class="parsley-required" id="amount_errors" style="color:#cc1f1a;"></li>
                </ul>
            </div>

            <button class="btn-neon mt-2" id="generateBtn" onclick="createOrder()" disabled>Generate Link</button>
            <button class="btn-ghost" type="button" onclick="window.history.back()">Close</button>
        </div>
    </div>
</div>

<div id="error-card">
    <h6>Error</h6>
    <p id="error-msg">Something went wrong</p>
    <button onclick="hideErrorCard()">Close</button>
</div>

<div class="modal show" id="view-qrcode-model" data-toggle="modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title" id="zigpay-modal-title">Scan & Pay</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div id="zigpay-qr-section">
                    <center>
                        <h4 id="qrHeadline">Scan QR or open payment page</h4>
                        <br>
                        <img src="" class="qr_code" id="qrCodeUrl" style="width: 200px; display:none;">
                        <p id="qrFallbackMsg" style="display:none; color:#5c5c5c; margin-bottom:12px;">
                            QR image is unavailable for this order. Use Open Payment Page.
                        </p>
                        <p id="zigpay-status-pending"><i class="fa fa-spinner fa-spin"></i> Waiting for payment…</p>
                        <hr>
                        Post successful payment, balance will reflect in your wallet shortly.
                    </center>
                    <a class="btn btn-primary btn-lg btn-block mt-2" href="" role="button" id="qrStringBtn" target="_blank" rel="noopener" style="display:none;">
                        Pay <span id="amountString"></span> Using App
                    </a>
                    <a class="btn btn-primary btn-lg btn-block mt-2" href="" role="button" id="paymentPageBtn" target="_blank" rel="noopener" style="display:none;">
                        Open Payment Page
                    </a>
                </div>
                <div id="zigpay-result-success" style="display:none;">
                    <strong id="zigpay-success-title">Payment successful</strong>
                    <p class="mb-0 mt-2" id="zigpay-success-msg">Your wallet will update shortly.</p>
                    <p class="mb-0 mt-1 small" id="zigpay-success-utr"></p>
                </div>
                <div id="zigpay-result-failure" style="display:none;">
                    <strong>Payment failed</strong>
                    <p class="mb-0 mt-2" id="zigpay-failure-msg">Payment could not be completed.</p>
                </div>
                <div id="zigpay-result-timeout" style="display:none;">
                    <strong>Status unclear</strong>
                    <p class="mb-0 mt-2">No confirmation received in time. If you paid, check your wallet or statement.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const MIN_AMOUNT = {{ $min_amount ?? 100 }};
    const MAX_AMOUNT = {{ $max_amount ?? 50000 }};

    let zigpayPollInterval = null;
    let zigpayPollCount = 0;
    let zigpayCurrentTxnid = null;
    const ZIGPAY_POLL_MS = 8000;
    const ZIGPAY_MAX_POLLS = 112;

    function stopZigpayPolling() {
        if (zigpayPollInterval) {
            clearInterval(zigpayPollInterval);
            zigpayPollInterval = null;
        }
        zigpayPollCount = 0;
    }

    function resetZigpayModalPaymentUi() {
        $('#zigpay-modal-title').text('Scan & Pay');
        $('#zigpay-qr-section').show();
        $('#zigpay-result-success').hide();
        $('#zigpay-result-failure').hide();
        $('#zigpay-result-timeout').hide();
        $('#zigpay-status-pending').html('<i class="fa fa-spinner fa-spin"></i> Waiting for payment…');
        $('#qrCodeUrl').attr('src', '').hide();
        $('#qrStringBtn').attr('href', '#').hide();
        $('#paymentPageBtn').attr('href', '#').hide();
        $('#qrFallbackMsg').show();
    }

    function showZigpaySuccess(data) {
        stopZigpayPolling();
        var uatSimulated = !!(data && (data.uat_simulated === true || data.uat_simulated === 1 || data.uat_simulated === 'true'));
        $('#zigpay-modal-title').text(uatSimulated ? 'UAT sandbox credited' : 'Payment successful');
        $('#zigpay-success-title').text(uatSimulated ? 'UAT sandbox: payin simulated' : 'Payment successful');
        $('#zigpay-success-msg').text(
            uatSimulated
                ? 'FrapPay UAT does not return a QR/payment link. It auto-marks collection as success so you can test wallet credit. Use production base URL (+ whitelisted IP) for a real payment link.'
                : 'Your wallet will update shortly.'
        );
        $('#zigpay-qr-section').hide();
        $('#zigpay-result-failure').hide();
        $('#zigpay-result-timeout').hide();
        var utr = (data && (data.utr || data.transaction_id)) ? (data.utr || data.transaction_id) : '';
        $('#zigpay-success-utr').text(utr ? ('UTR / ref: ' + utr) : '');
        $('#zigpay-result-success').show();
    }

    function showZigpayFailure(message) {
        stopZigpayPolling();
        $('#zigpay-modal-title').text('Payment failed');
        $('#zigpay-qr-section').hide();
        $('#zigpay-result-success').hide();
        $('#zigpay-result-timeout').hide();
        $('#zigpay-failure-msg').text(message || 'Payment could not be completed.');
        $('#zigpay-result-failure').show();
    }

    function showZigpayTimeout() {
        stopZigpayPolling();
        $('#zigpay-modal-title').text('Payment');
        $('#zigpay-qr-section').hide();
        $('#zigpay-result-success').hide();
        $('#zigpay-result-failure').hide();
        $('#zigpay-result-timeout').show();
    }

    function pollZigpayOrderStatus() {
        if (!zigpayCurrentTxnid) {
            return;
        }
        $.ajax({
            type: 'POST',
            url: "{{ url('agent/add-money/v11/order-status') }}",
            data: {
                _token: $("input[name=_token]").val(),
                txnid: zigpayCurrentTxnid
            },
            success: function (res) {
                if (!res || res.ok === false) {
                    return;
                }
                if (res.payment_status === 'success') {
                    showZigpaySuccess(res.data || {});
                } else if (res.payment_status === 'failed') {
                    showZigpayFailure(res.message);
                }
            },
            error: function () { /* keep polling */ }
        });
    }

    function startZigpayPolling(txnid) {
        stopZigpayPolling();
        zigpayCurrentTxnid = txnid;
        zigpayPollCount = 0;
        zigpayPollInterval = setInterval(function () {
            zigpayPollCount++;
            if (zigpayPollCount >= ZIGPAY_MAX_POLLS) {
                showZigpayTimeout();
                return;
            }
            pollZigpayOrderStatus();
        }, ZIGPAY_POLL_MS);
        pollZigpayOrderStatus();
    }

    $('#view-qrcode-model').on('hidden.bs.modal', function () {
        stopZigpayPolling();
        zigpayCurrentTxnid = null;
        resetZigpayModalPaymentUi();
    });

    function toggleBtn() {
        let amount = parseFloat(document.getElementById("amount").value.trim());
        let btn = document.getElementById("generateBtn");
        let errorField = document.getElementById("amount_errors");

        if (!isNaN(amount) && amount >= MIN_AMOUNT && amount <= MAX_AMOUNT) {
            btn.disabled = false;
            errorField.textContent = "";
        } else {
            btn.disabled = true;
            if (amount) {
                errorField.textContent = "Enter amount between " + MIN_AMOUNT + " and " + MAX_AMOUNT;
            } else {
                errorField.textContent = "";
            }
        }
    }

    function showErrorCard(message) {
        document.getElementById('error-msg').textContent = message;
        const card = document.getElementById('error-card');
        const form = document.getElementById('form-wrapper');
        card.classList.add('show');
        form.classList.add('shift-left');
        setTimeout(() => hideErrorCard(), 5000);
    }

    function hideErrorCard() {
        const card = document.getElementById('error-card');
        const form = document.getElementById('form-wrapper');
        card.classList.remove('show');
        form.classList.remove('shift-left');
    }

    function applyQrUi(data) {
        var isCheckoutQr = !!(data.qr_is_checkout_url);
        $('#qrHeadline').text(isCheckoutQr
            ? 'Scan to open payment page (then pay via UPI)'
            : 'Open any UPI app and scan this QR');

        var qrSrc = data.qrCodeUrl || '';
        if (!qrSrc && data.payment_page_url) {
            qrSrc = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' + encodeURIComponent(data.payment_page_url);
        }

        if (qrSrc) {
            $("#qrCodeUrl").attr('src', qrSrc);
            $("#qrCodeUrl").show();
            $("#qrFallbackMsg").hide();
        } else {
            $("#qrCodeUrl").attr('src', '').hide();
            $("#qrFallbackMsg").text(
                data.payment_page_url
                    ? 'Use Open Payment Page to complete UPI payment on Easebuzz.'
                    : 'Waiting for payment confirmation…'
            ).show();
        }

        if (data.upi_link || (data.qrString && String(data.qrString).indexOf('upi://') === 0)) {
            $("#qrStringBtn").attr('href', data.upi_link || data.qrString).show();
        } else {
            $("#qrStringBtn").attr('href', '#').hide();
        }

        if (data.payment_page_url) {
            $("#paymentPageBtn").attr('href', data.payment_page_url).show();
        } else {
            $("#paymentPageBtn").attr('href', '#').hide();
        }
    }

    function fetchFrapPayQr(txnid) {
        $.ajax({
            type: 'POST',
            url: "{{ url('agent/add-money/v11/fetch-qr') }}",
            data: {
                _token: $("input[name=_token]").val(),
                txnid: txnid
            },
            timeout: 30000,
            success: function (res) {
                if (!res || !res.data) {
                    return;
                }
                var data = res.data || {};
                if ((data.payment_status || '').toString().toLowerCase() === 'success') {
                    showZigpaySuccess(data);
                    return;
                }
                applyQrUi(data);
            },
            error: function () {
                // Keep Open Payment Page from create-order response.
            }
        });
    }

    function createOrder(){
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var amount = $("#amount").val();

        $.ajax({
            type: "POST",
            url: "{{url('agent/add-money/v11/create-order')}}",
            data: {
                amount: amount,
                _token: token
            },
            timeout: 90000,
            success: function(msg){
                $(".loader").hide();
                if (msg.status == 'success') {
                    resetZigpayModalPaymentUi();
                    $("#amountString").text(amount);
                    $("#view-qrcode-model").modal('show');

                    var data = msg.data || {};
                    var paymentStatus = (data.payment_status || data.status || '').toString().toLowerCase();
                    var uatSimulated = !!(data.uat_simulated === true || data.uat_simulated === 1 || data.uat_simulated === 'true');

                    if (paymentStatus === 'success' || uatSimulated) {
                        showZigpaySuccess(data);
                        return;
                    }

                    applyQrUi(data);
                    if (data.txnid) {
                        startZigpayPolling(data.txnid);
                    }
                    // Auto-open hosted payment page (Easebuzz shows the real UPI QR there).
                    if (data.auto_open_payment && data.payment_page_url) {
                        window.open(data.payment_page_url, '_blank');
                    }
                } else {
                    showErrorCard(msg.message || 'Failed to create order. Please try again.');
                }
            },
            error: function(xhr){
                $(".loader").hide();
                var msg = 'Network or server error. Please try again.';
                if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                showErrorCard(msg);
            }
        });
    }
</script>
@endsection
