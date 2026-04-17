@extends('agent.layout.header')
@section('content')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<style>
    body {
        background: linear-gradient(120deg, #a1c4fd, #c2e9fb);
    }

    .payin-card {
        background: linear-gradient(135deg, #fdfbfb, #ebedee);
        border-radius: 12px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.2);
        padding: 25px;
        width: 150%;
        max-width: 550px;
        margin: 120px auto;
        text-align: center;
        transition: transform 0.3s ease;
    }

    .payin-card.shift-left {
        transform: translateX(-30px);
    }

    .payin-card h3 {
        font-weight: bold;
        margin-bottom: 8px;
    }

    .payin-card p {
        color: #555;
        font-size: 14px;
        margin-bottom: 20px;
    }

    .payin-input {
        border-radius: 10px;
        border: 1px solid #ddd;
        padding: 10px 15px;
        width: 100%;
        margin-bottom: 5px;
        transition: all 0.3s ease-in-out;
    }

    .payin-input:focus {
        border-color: #7f00ff;
        box-shadow: 0 0 6px rgba(127, 0, 255, 0.4);
    }

    .error-text {
        font-size: 13px;
        color: red;
        margin-bottom: 10px;
        display: none;
    }

    .btn-gradient {
        background: linear-gradient(90deg, #00c6ff, #ff00d4);
        border: none;
        border-radius: 10px;
        padding: 12px 20px;
        color: #fff;
        font-weight: bold;
        width: 100%;
        cursor: not-allowed;
        opacity: 0.6;
        transition: transform 0.2s, opacity 0.3s;
    }

    .btn-gradient.enabled {
        cursor: pointer;
        opacity: 1;
    }

    .btn-gradient.enabled:hover {
        transform: scale(1.05);
    }

    .btn-close {
        margin-top: 12px;
        width: 100%;
        background: #fff;
        border: 1px solid #ccc;
        border-radius: 10px;
        padding: 12px 20px;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-close:hover {
        background: #f5f5f5;
    }

    /* Error Card */
    .error-card {
        position: fixed;
        top: 100px;
        right: -400px;
        width: 320px;
        padding: 15px;
        background: #ff4d4f;
        color: #fff;
        border-radius: 10px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        font-weight: 500;
        transition: right 0.5s ease;
        z-index: 2000;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .error-card.show {
        right: 20px;
    }

    .error-card button {
        background: transparent;
        border: none;
        color: #fff;
        font-size: 18px;
        cursor: pointer;
    }
</style>

<div class="main-content-body">
    <!-- Error Card -->
    <div id="error-card" class="error-card">
        <span id="error-msg"></span>
        <button onclick="hideErrorCard()">✖</button>
    </div>

    <!-- Payin Card -->
    <div class="payin-card" id="payinCard">
        <h3>{{ $page_title }}</h3>
        <p>Generate a QR instantly by entering the amount</p>

        <input type="number" class="payin-input" placeholder="Enter Amount" name="amount" id="amount" min="100" max="20000">
        <div class="error-text" id="amountError">Amount must be between 100 and 20000</div>

        <button id="addMoneyBtn" class="btn-gradient" type="button" onclick="createOrder()" disabled>⚡ Add Money</button>
        <button class="btn-close" type="button" onclick="window.history.back()">Close</button>
    </div>
</div>

<script>
    const amountInput = document.getElementById("amount");
    const addMoneyBtn = document.getElementById("addMoneyBtn");
    const errorText = document.getElementById("amountError");

    amountInput.addEventListener("input", function () {
        let value = parseInt(amountInput.value);

        if (!isNaN(value) && value >= 100 && value <= 20000) {
            addMoneyBtn.disabled = false;
            addMoneyBtn.classList.add("enabled");
            errorText.style.display = "none";
        } else {
            addMoneyBtn.disabled = true;
            addMoneyBtn.classList.remove("enabled");
            if (amountInput.value.trim() !== "") {
                errorText.style.display = "block";
            } else {
                errorText.style.display = "none";
            }
        }
    });

    function showErrorCard(message) {
        const card = document.getElementById('error-card');
        const msg = document.getElementById('error-msg');
        const payinCard = document.getElementById('payinCard');

        msg.textContent = message;
        card.classList.add('show');
        payinCard.classList.add('shift-left');

        setTimeout(() => {
            hideErrorCard();
        }, 5000);
    }

    function hideErrorCard() {
        const card = document.getElementById('error-card');
        const payinCard = document.getElementById('payinCard');

        card.classList.remove('show');
        payinCard.classList.remove('shift-left');
    }

    function createOrder() {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var amount = $("#amount").val();

        $.ajax({
            type: "POST",
            url: "{{ url('agent/add-money/v3/create-order') }}",
            data: {
                amount: amount,
                _token: token
            },
            success: function (msg) {
                $(".loader").hide();
                if (msg.status == 'success') {
                    $("#qrCodeUrl").attr('src', msg.data.qrCodeUrl);
                    $("#qrStringBtn").attr('href', msg.data.qrString);
                    $("#view-qrcode-model").modal('show');
                } else {
                    showErrorCard(msg.message || 'Failed to create order. Please try again.');
                }
            },
            error: function () {
                $(".loader").hide();
                showErrorCard('Network or server error. Please try again.');
            }
        });
    }
</script>

@endsection
