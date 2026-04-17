@extends('agent.layout.header')
@section('content')

<script>
    function createElectraOrder() {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var amount = $("#amount").val();

        $.ajax({
            type: "POST",
            url: "{{ url('agent/add-money/v6/create-order') }}",
            data: {
                _token: token,
                amount: amount
            },
            success: function (response) {
                $(".loader").hide();
                if (response.status === 'success') {
                    window.location.href = response.data.paymentUrl;
                } else {
                    showErrorCard(response.message || "Something went wrong");
                }
            },
            error: function (xhr) {
                $(".loader").hide();
                showErrorCard("Validation failed: " + xhr.responseJSON.message);
            }
        });
    }

    // Custom error card popup (right side)
    function showErrorCard(message) {
        const card = document.createElement("div");
        card.className = "error-card show";
        card.innerHTML = `
            <div class="error-content">
                <strong>⚠ Failed to Create Order</strong>
                <p>${message}</p>
            </div>
        `;
        document.body.appendChild(card);

        setTimeout(() => {
            card.classList.add("hide");
            setTimeout(() => card.remove(), 500);
        }, 4000);
    }

    // Enable/Disable button only if amount is valid
    document.addEventListener("DOMContentLoaded", function () {
        const amountInput = document.getElementById("amount");
        const proceedButton = document.getElementById("proceedButton");
        const errorText = document.createElement("div");
        errorText.id = "amountError";
        errorText.style.color = "red";
        errorText.style.fontSize = "13px";
        errorText.style.marginTop = "5px";
        errorText.style.display = "none";
        amountInput.parentNode.appendChild(errorText);

        proceedButton.disabled = true;

        amountInput.addEventListener("input", function () {
            const number = parseFloat(amountInput.value.trim());

            if (!isNaN(number) && number >= 100 && number <= 20000) {
                proceedButton.disabled = false;
                errorText.style.display = "none";
            } else {
                proceedButton.disabled = true;
                if (amountInput.value.trim() !== "") {
                    errorText.textContent = "Enter amount between 100 and 20000";
                    errorText.style.display = "block";
                } else {
                    errorText.style.display = "none";
                }
            }
        });
    });
</script>

<style>
    body {
        background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        font-family: 'Poppins', sans-serif;
    }
    .custom-card {
        background: #ffffff;
        border-radius: 20px;
        padding: 30px;
        text-align: center;
        box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        transition: transform .2s;
    }
    .custom-card:hover {
        transform: translateY(-4px);
    }
    .custom-card h6 {
        font-size: 22px;
        font-weight: 600;
        color: #6a11cb;
    }
    .form-control {
        border-radius: 10px;
        padding: 12px;
        border: 1px solid #ddd;
        font-size: 16px;
        margin-bottom: 5px;
    }
    .btn-primary {
        background: linear-gradient(90deg, #6a11cb, #2575fc);
        border: none;
        padding: 12px;
        font-size: 16px;
        border-radius: 10px;
        width: 100%;
        font-weight: 600;
        transition: 0.3s;
        opacity: 0.6;
        cursor: not-allowed;
    }
    .btn-primary:enabled {
        opacity: 1;
        cursor: pointer;
    }
    .btn-primary:enabled:hover {
        transform: scale(1.05);
    }
    .btn-secondary {
        background: #ffffff;
        color: #444;
        border: 2px solid #ddd;
        padding: 12px;
        font-size: 16px;
        border-radius: 10px;
        width: 100%;
        font-weight: 600;
        margin-top: 10px;
        transition: 0.3s;
    }
    .btn-secondary:hover {
        background: #f2f2f2;
    }
    /* Error card popup */
    .error-card {
        position: fixed;
        top: 20px;
        right: -400px;
        background: #ff5c5c;
        color: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        transition: all 0.5s ease-in-out;
        z-index: 9999;
        width: 300px;
    }
    .error-card.show {
        right: 20px;
    }
    .error-card.hide {
        right: -400px;
        opacity: 0;
    }
    .error-content strong {
        font-size: 18px;
    }
    .error-content p {
        margin: 8px 0 0;
        font-size: 14px;
    }
</style>

<div class="main-content-body">
    <div class="row">
        <div class="col-lg-6 col-md-12" style="margin-left:25%;margin-top:100px;">
            <div class="custom-card">
                <h6 class="card-title mb-1">Payin 6</h6>
                <p>Generate a QR instantly by entering the amount</p>
                <hr>
                <div class="mb-4">
                    <input type="text" class="form-control" id="amount" placeholder="Enter Amount">
                </div>
                <div>
                    @csrf
                    <button id="proceedButton" class="btn btn-primary" type="button" onclick="createElectraOrder()">
                        ⚡ Proceed to Payment
                    </button>
                    <button class="btn btn-secondary" data-dismiss="modal" type="button">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
