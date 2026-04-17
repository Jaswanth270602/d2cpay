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
</style>

<div class="main-content-body d-flex justify-content-center align-items-center" style="min-height:100vh;">
    <div class="col-md-5 unique-form-wrapper" id="form-wrapper">
        <div class="unique-card">
            <h3 class="unique-title">Payin 8</h3>
            <p class="unique-sub">Generate a UPI request by entering the amount</p>

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
                <h6 class="modal-title">Scan & Pay</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <center>
                    <h4>Open any UPI app and scan this QR</h4>
                    <br>
                    <img src="" class="qr_code" id="qrCodeUrl" style="width: 200px; display:none;">
                    <p id="qrFallbackMsg" style="display:none; color:#5c5c5c; margin-bottom:12px;">
                        QR image is unavailable for this order. Use the button below to complete payment.
                    </p>
                    <hr>
                    Post successful payment, balance will reflect in your wallet shortly.
                </center>
                <a class="btn btn-primary btn-lg btn-block" href="" role="button" id="qrStringBtn">
                    Pay <span id="amountString"></span> Using App
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    const MIN_AMOUNT = {{ $min_amount ?? 100 }};
    const MAX_AMOUNT = {{ $max_amount ?? 50000 }};

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

    function createOrder(){
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var amount = $("#amount").val();

        $.ajax({
            type: "POST",
            url: "{{url('agent/add-money/v8/create-order')}}",
            data: {
                amount: amount,
                _token: token
            },
            success: function(msg){
                $(".loader").hide();
                if (msg.status == 'success') {
                    if (msg.data.qrCodeUrl) {
                        $("#qrCodeUrl").attr('src', msg.data.qrCodeUrl);
                        $("#qrCodeUrl").show();
                        $("#qrFallbackMsg").hide();
                    } else {
                        $("#qrCodeUrl").attr('src', '').hide();
                        $("#qrFallbackMsg").show();
                    }
                    if (msg.data.qrString) {
                        $("#qrStringBtn").attr('href', msg.data.qrString);
                    } else {
                        $("#qrStringBtn").attr('href', '#');
                    }
                    $("#amountString").text(amount);
                    $("#view-qrcode-model").modal('show');
                } else {
                    showErrorCard(msg.message || 'Failed to create order. Please try again.');
                }
            },
            error: function(){
                $(".loader").hide();
                showErrorCard('Network or server error. Please try again.');
            }
        });
    }
</script>
@endsection
