@extends('agent.layout.header')
@section('content')

<script>
    let paymentStatusInterval = null;
    let statusCheckCount = 0;
    const maxStatusChecks = 112; // ~15 minutes at 8-second intervals

    function createSafepPayOrder() {
        $(".loader").show();
        var token = $("input[name=_token]").val();
        var amount = $("#amount").val();

        // Basic validation
        if (!amount || amount <= 0) {
            $(".loader").hide();
            showErrorMessage("Please enter a valid amount");
            return;
        }

        if (amount < 100 || amount > 50000) {
            $(".loader").hide();
            showErrorMessage("Amount must be between ₹100 and ₹50,000");
            return;
        }

        $.ajax({
            type: "POST",
            url: "{{ url('agent/add-money/v7/create-order') }}",
            data: {
                _token: token,
                amount: amount
            },
            success: function(response) {
                $(".loader").hide();
                if (response.status === 'success' && response.data) {
                    handlePaymentSuccess(response, amount);
                } else {
                    showErrorMessage(response.message || "Something went wrong. No payment details received.");
                }
            },
            error: function(xhr) {
                $(".loader").hide();
                handleAjaxError(xhr);
            }
        });
    }

    function handlePaymentSuccess(response, amount) {
        // Hide the form and show payment details
        $("#payment-form").hide();
        $("#payment-details").show();
        
        const data = response.data;
        
        // Display QR Code
        if (data.qrCode) {
            $("#qr-code-img").attr('src', data.qrCode);
            $("#qr-section").show();
        }
        
        // Display UPI Intent Links
        if (data.qrString) {
            $("#upi-default-link").attr('href', data.qrString);
            $("#upi-intent-text").text(data.qrString);
            $("#upi-section").show();
        }

        // Display specific UPI app intents if available
        if (data.upi_intents) {
            displayUpiAppIntents(data.upi_intents);
        }

        // Display Checkout URL if available
        if (data.checkout_url) {
            $("#checkout-url").attr('href', data.checkout_url);
            $("#checkout-section").show();
        }
        
        // Display transaction details
        $("#transaction-id").text(data.txnid || 'N/A');
        $("#safeppay-txn-id").text(data.transaction_id || 'N/A');
        $("#order-token").text(data.order_token || 'N/A');
        $("#transaction-amount").text(amount);
        
        // Start status checking with available IDs
        const transactionId = data.transaction_id || data.txnid;
        if (transactionId) {
            startStatusCheck(data.txnid, transactionId);
        } else {
            showErrorMessage('Payment initiated but unable to track status. Please contact support.');
        }
    }

    function displayUpiAppIntents(intents) {
        var intentHtml = '<div class="upi-apps-grid">';
        
        if (intents.phonepe) {
            intentHtml += '<a href="' + intents.phonepe + '" class="upi-app-card phonepe"><div class="upi-icon">📱</div><span>PhonePe</span></a>';
        }
        if (intents.gpay) {
            intentHtml += '<a href="' + intents.gpay + '" class="upi-app-card gpay"><div class="upi-icon">🔷</div><span>Google Pay</span></a>';
        }
        if (intents.paytm) {
            intentHtml += '<a href="' + intents.paytm + '" class="upi-app-card paytm"><div class="upi-icon">💳</div><span>Paytm</span></a>';
        }
        if (intents.bhim) {
            intentHtml += '<a href="' + intents.bhim + '" class="upi-app-card bhim"><div class="upi-icon">🏦</div><span>BHIM UPI</span></a>';
        }
        
        intentHtml += '</div>';
        $("#upi-apps-section").html(intentHtml).show();
        
        // Setup UPI app button loading states
        setupUpiAppButtons();
    }

    function startStatusCheck(txnId, transactionId) {
        cleanupPaymentProcess(); // Clear any existing intervals
        
        statusCheckCount = 0;
        paymentStatusInterval = setInterval(function() {
            statusCheckCount++;
            checkPaymentStatus(txnId, transactionId, statusCheckCount);
        }, 8000);
    }

    function checkPaymentStatus(txnId, transactionId, checkCount) {
        // Stop if exceeded max checks
        if (checkCount >= maxStatusChecks) {
            clearInterval(paymentStatusInterval);
            $("#payment-status").html('<i class="fa fa-clock"></i> Payment timeout. Please check your bank statement or contact support.');
            $("#payment-status").removeClass('status-pending').addClass('status-failed');
            return;
        }
        
        $.ajax({
            type: "POST",
            url: "{{ url('agent/add-money/v7/check-status') }}",
            data: {
                _token: $("input[name=_token]").val(),
                transaction_id: transactionId,
                txnid: txnId
            },
            success: function(response) {
                if (response.status === true && response.data) {
                    handlePaymentStatus(response.data);
                } else if (response.status === false) {
                    // Handle API-level failure
                    $("#payment-status").html('<i class="fa fa-exclamation-triangle"></i> Status check failed');
                    $("#payment-status").removeClass('status-pending').addClass('status-failed');
                }
            },
            error: function(xhr) {
                console.error('Status check error:', xhr);
                // Update status to show connection issues but don't stop
                if (checkCount % 5 === 0) { // Show message every 5 checks
                    $("#payment-status").html('<i class="fa fa-wifi"></i> Checking... (Connection issues)');
                }
            }
        });
    }

    function handlePaymentStatus(data) {
        const txnStatus = data.transaction_status?.toLowerCase();
        
        switch(txnStatus) {
            case 'success':
            case 'completed':
                handleSuccessfulPayment(data);
                break;
                
            case 'failed':
            case 'failure':
            case 'rejected':
                handleFailedPayment(data);
                break;
                
            case 'pending':
            case 'processing':
                $("#payment-status").html('<i class="fa fa-spinner fa-spin"></i> Payment Processing...');
                $("#payment-status").removeClass('status-success status-failed').addClass('status-pending');
                break;
                
            default:
                $("#payment-status").html('<i class="fa fa-spinner fa-spin"></i> Payment Initiated');
                $("#payment-status").removeClass('status-success status-failed').addClass('status-pending');
                break;
        }
    }

    function handleSuccessfulPayment(data) {
        clearInterval(paymentStatusInterval);
        $("#payment-status").html('<i class="fa fa-check-circle"></i> Payment Successful!');
        $("#payment-status").removeClass('status-pending status-failed').addClass('status-success');
        $("#success-details").show();
        
        // Update with actual response data
        $("#utr-number").text(data.utr || data.reference_id || 'N/A');
        $("#payment-mode").text(data.payment_mode || data.transfer_mode || 'UPI');
        
        // Hide all payment options
        $(".payment-option").hide();
        
        showSuccessMessage('Payment completed successfully! Redirecting...');
        
        // Redirect after delay
        setTimeout(function() {
            window.location.href = "{{ url('agent/dashboard') }}";
        }, 3000);
    }

    function handleFailedPayment(data) {
        clearInterval(paymentStatusInterval);
        $("#payment-status").html('<i class="fa fa-times-circle"></i> Payment Failed');
        $("#payment-status").removeClass('status-pending status-success').addClass('status-failed');
        
        const failReason = data.failure_reason || data.message || 'Payment failed. Please try again.';
        showErrorMessage(failReason);
    }

    function setupUpiAppButtons() {
        $(".upi-app-card").on('click', function(e) {
            const $btn = $(this);
            $btn.addClass('loading');
            
            setTimeout(() => {
                $btn.removeClass('loading');
            }, 3000);
        });
    }

    function handleAjaxError(xhr) {
        let errMsg = "Unknown error occurred";
        
        if (xhr.status === 0) {
            errMsg = "Network error. Please check your internet connection.";
        } else if (xhr.status === 422) {
            errMsg = Object.values(xhr.responseJSON.errors)[0][0];
        } else if (xhr.status === 500) {
            errMsg = "Server error. Please try again later.";
        } else if (xhr.responseJSON && xhr.responseJSON.message) {
            errMsg = xhr.responseJSON.message;
        } else if (xhr.statusText) {
            errMsg = xhr.statusText;
        }
        
        showErrorMessage(errMsg);
    }

    function copyUpiLink() {
        var upiLink = $("#upi-intent-text").text();
        if (!upiLink) {
            showErrorMessage('No UPI link available to copy');
            return;
        }

        navigator.clipboard.writeText(upiLink).then(function() {
            showSuccessMessage('UPI link copied to clipboard!');
        }).catch(function() {
            // Fallback for older browsers
            var textArea = document.createElement("textarea");
            textArea.value = upiLink;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            showSuccessMessage('UPI link copied to clipboard!');
        });
    }

    function showSuccessMessage(message) {
        var alertHtml = '<div class="modern-alert success"><i class="fa fa-check-circle"></i><span>' + message + '</span><button type="button" class="alert-close" onclick="$(this).parent().fadeOut()">&times;</button></div>';
        $("#alert-container").html(alertHtml);
        setTimeout(function() {
            $(".modern-alert").fadeOut(300, function() { $(this).remove(); });
        }, 5000);
    }

    function showErrorMessage(message) {
        var alertHtml = '<div class="modern-alert error"><i class="fa fa-exclamation-circle"></i><span>' + message + '</span><button type="button" class="alert-close" onclick="$(this).parent().fadeOut()">&times;</button></div>';
        $("#alert-container").html(alertHtml);
        setTimeout(function() {
            $(".modern-alert").fadeOut(300, function() { $(this).remove(); });
        }, 5000);
    }

    function cleanupPaymentProcess() {
        if (paymentStatusInterval) {
            clearInterval(paymentStatusInterval);
            paymentStatusInterval = null;
        }
        statusCheckCount = 0;
    }

    function resetPaymentForm() {
        cleanupPaymentProcess();
        
        $("#payment-details").hide();
        $("#payment-form").show();
        $("#amount").val('');
        $("#payment-status").html('<i class="fa fa-spinner fa-spin"></i> Checking...')
            .removeClass('status-success status-failed')
            .addClass('status-pending');
        
        $(".payment-option").hide();
        $("#success-details").hide();
        $("#alert-container").empty();
        
        // Reset UPI app buttons
        $(".upi-app-card").off('click').removeClass('loading');
    }

    function openCheckoutPage() {
        var checkoutUrl = $("#checkout-url").attr('href');
        if (checkoutUrl) {
            window.open(checkoutUrl, '_blank', 'noopener,noreferrer');
        } else {
            showErrorMessage('Checkout URL not available');
        }
    }

    // Handle page visibility changes
    $(document).on('visibilitychange', function() {
        if (document.hidden && paymentStatusInterval) {
            console.log('Page hidden, payment status checking continues...');
        }
    });

    // Cleanup on page unload
    $(window).on('beforeunload', function() {
        cleanupPaymentProcess();
    });

    // Handle QR code loading errors
    $(document).ready(function() {
        $("#qr-code-img").on('error', function() {
            $(this).attr('src', '{{ asset("images/fallback-qr.png") }}');
            showErrorMessage('QR code failed to load. Please use UPI links instead.');
        });
    });
</script>

<div class="main-content-body">
    <!-- Alert Container -->
    <div id="alert-container"></div>

    <div class="payment-container">
        <!-- Payment Form -->
        <div class="payment-card" id="payment-form">
            <div class="card-header-modern">
                <div class="header-icon">💳</div>
                <h2>{{ $page_title }}</h2>
                <p>Enter amount to add money to your wallet</p>
            </div>
            
            <div class="card-body-modern">
                <div class="input-group-modern">
                    <label>Amount</label>
                    <div class="amount-input-wrapper">
                        <span class="currency-symbol">₹</span>
                        <input type="number" id="amount" placeholder="Enter amount" min="100" max="50000" step="1">
                    </div>
                    <span class="input-hint">Min: ₹100 • Max: ₹50,000</span>
                </div>

                @csrf
                
                <div class="button-group-modern">
                    <button class="btn-modern primary" type="button" onclick="createSafepPayOrder()">
                        <span>Proceed to Payment</span>
                        <i class="fa fa-arrow-right"></i>
                    </button>
                    <button class="btn-modern secondary" type="button" onclick="window.history.back()">
                        Cancel
                    </button>
                </div>
            </div>
        </div>

        <!-- Payment Details -->
        <div class="payment-card wide" id="payment-details" style="display: none;">
            <div class="status-header">
                <div class="status-badge" id="payment-status">
                    <i class="fa fa-spinner fa-spin"></i> Checking...
                </div>
            </div>

            <div class="payment-grid">
                <!-- Left Column - Transaction Info -->
                <div class="transaction-info">
                    <h3>Transaction Details</h3>
                    
                    <div class="info-row">
                        <span class="label">Amount</span>
                        <span class="value amount">₹<span id="transaction-amount"></span></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="label">Order ID</span>
                        <span class="value" id="transaction-id">N/A</span>
                    </div>
                    
                    <div class="info-row">
                        <span class="label">Transaction ID</span>
                        <span class="value small" id="safeppay-txn-id">N/A</span>
                    </div>
                    
                    <div class="info-row">
                        <span class="label">Order Token</span>
                        <span class="value small mono" id="order-token">N/A</span>
                    </div>

                    <!-- Success Details -->
                    <div id="success-details" class="success-card" style="display: none;">
                        <div class="success-icon">✓</div>
                        <h4>Payment Successful!</h4>
                        <div class="success-info">
                            <div class="info-item">
                                <span>UTR Number</span>
                                <strong id="utr-number">N/A</strong>
                            </div>
                            <div class="info-item">
                                <span>Payment Mode</span>
                                <strong id="payment-mode">UPI</strong>
                            </div>
                        </div>
                        <p class="redirect-msg"><i class="fa fa-spinner fa-spin"></i> Redirecting to dashboard...</p>
                    </div>
                </div>

                <!-- Right Column - Payment Options -->
                <div class="payment-options">
                    <!-- QR Code Section -->
                    <div id="qr-section" class="payment-option" style="display: none;">
                        <h3>Scan QR Code</h3>
                        <div class="qr-wrapper">
                            <img id="qr-code-img" src="" alt="QR Code">
                        </div>
                        <p class="hint">Scan with any UPI app to pay</p>
                    </div>

                    <!-- Checkout URL Section -->
                    <div id="checkout-section" class="payment-option" style="display: none;">
                        <h3>Web Checkout</h3>
                        <p>Complete payment on our secure checkout page</p>
                        <button class="btn-modern primary" onclick="openCheckoutPage()">
                            <span>Open Checkout</span>
                            <i class="fa fa-external-link"></i>
                        </button>
                        <a id="checkout-url" href="#" target="_blank" style="display: none;"></a>
                    </div>

                    <!-- UPI Intent Link Section -->
                    <div id="upi-section" class="payment-option" style="display: none;">
                        <h3>UPI Payment Link</h3>
                        <div class="upi-link-actions">
                            <a id="upi-default-link" href="#" class="btn-modern primary">
                                <span>Open UPI App</span>
                                <i class="fa fa-external-link"></i>
                            </a>
                            <button class="btn-modern secondary" onclick="copyUpiLink()">
                                <i class="fa fa-copy"></i>
                            </button>
                        </div>
                        <div class="upi-link-text">
                            <span id="upi-intent-text"></span>
                        </div>
                    </div>

                    <!-- UPI Apps Section -->
                    <div id="upi-apps-section" class="payment-option" style="display: none;">
                        <h3>Choose Your UPI App</h3>
                        <!-- UPI app buttons will be dynamically inserted here -->
                    </div>
                </div>
            </div>

            <div class="action-footer">
                <button class="btn-modern secondary" onclick="resetPaymentForm()">
                    <i class="fa fa-arrow-left"></i> Back
                </button>
                <button class="btn-modern secondary" onclick="location.reload()">
                    <i class="fa fa-refresh"></i> Refresh
                </button>
            </div>
        </div>

        <!-- Instructions Sidebar -->
        <div class="instructions-sidebar">
            <div class="instruction-card">
                <div class="instruction-icon">ℹ️</div>
                <h3>How to Pay</h3>
                
                <div class="instruction-section">
                    <h4>Using QR Code</h4>
                    <ol>
                        <li>Open any UPI app</li>
                        <li>Scan the QR code</li>
                        <li>Verify and pay</li>
                    </ol>
                </div>
                
                <div class="instruction-section">
                    <h4>Using UPI Link</h4>
                    <ol>
                        <li>Click "Open UPI App"</li>
                        <li>Select your UPI app</li>
                        <li>Enter UPI PIN</li>
                    </ol>
                </div>
                
                <div class="instruction-section">
                    <h4>Using Web Checkout</h4>
                    <ol>
                        <li>Click "Open Checkout"</li>
                        <li>Choose payment method</li>
                        <li>Complete payment</li>
                    </ol>
                </div>
            </div>

            <div class="info-card warning">
                <div class="info-icon">⚠️</div>
                <div class="info-content">
                    <strong>Important Notes</strong>
                    <ul>
                        <li>Don't close this page during payment</li>
                        <li>Status updates every 8 seconds</li>
                        <li>Transaction expires in 15 minutes</li>
                    </ul>
                </div>
            </div>

            <div class="info-card help">
                <div class="info-icon">💬</div>
                <div class="info-content">
                    <strong>Need Help?</strong>
                    <ul>
                        <li>Check internet connection</li>
                        <li>Verify account balance</li>
                        <li>Try different UPI app</li>
                        <li>Contact support if needed</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loader -->
<div class="loader" style="display: none;">
    <div class="spinner"></div>
    <p>Processing...</p>
</div>

<style>
* { box-sizing: border-box; }

.main-content-body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    padding: 2rem 1rem;
}

.payment-container {
    max-width: 1400px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 1.5rem;
    align-items: start;
}

/* Modern Alert */
.modern-alert {
    background: white;
    border-radius: 12px;
    padding: 1rem 1.5rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    animation: slideDown 0.3s ease;
}

.modern-alert.success { border-left: 4px solid #10b981; }
.modern-alert.error { border-left: 4px solid #ef4444; }
.modern-alert i { font-size: 1.25rem; }
.modern-alert.success i { color: #10b981; }
.modern-alert.error i { color: #ef4444; }
.modern-alert span { flex: 1; font-weight: 500; }
.alert-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #6b7280;
    opacity: 0.6;
    transition: opacity 0.2s;
}
.alert-close:hover { opacity: 1; }

/* Payment Card */
.payment-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    overflow: hidden;
    animation: fadeInUp 0.4s ease;
}

.payment-card.wide {
    grid-column: 1 / 2;
}

.card-header-modern {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2.5rem;
    text-align: center;
}

.header-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.card-header-modern h2 {
    margin: 0 0 0.5rem 0;
    font-size: 1.75rem;
    font-weight: 700;
}

.card-header-modern p {
    margin: 0;
    opacity: 0.9;
    font-size: 0.95rem;
}

.card-body-modern {
    padding: 2.5rem;
}

/* Input Group */
.input-group-modern {
    margin-bottom: 2rem;
}

.input-group-modern label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #374151;
}

.amount-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.currency-symbol {
    position: absolute;
    left: 1.25rem;
    font-size: 1.5rem;
    font-weight: 700;
    color: #6b7280;
}

.amount-input-wrapper input {
    width: 100%;
    padding: 1rem 1rem 1rem 3rem;
    font-size: 1.5rem;
    font-weight: 600;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    transition: all 0.3s;
}

.amount-input-wrapper input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.input-hint {
    display: block;
    margin-top: 0.5rem;
    font-size: 0.875rem;
    color: #6b7280;
}

/* Buttons */
.button-group-modern {
    display: flex;
    gap: 1rem;
}

.btn-modern {
    flex: 1;
    padding: 1rem 1.5rem;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.3s;
}

.btn-modern.primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-modern.primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
}

.btn-modern.secondary {
    background: #f3f4f6;
    color: #374151;
}

.btn-modern.secondary:hover {
    background: #e5e7eb;
}

/* Payment Details */
.status-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 2rem;
    text-align: center;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1rem;
}

.status-badge.status-pending {
    background: #fef3c7;
    color: #92400e;
}

.status-badge.status-success {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.status-failed {
    background: #fee2e2;
    color: #991b1b;
}

.payment-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    padding: 2rem;
}

/* Transaction Info */
.transaction-info h3 {
    margin: 0 0 1.5rem 0;
    font-size: 1.25rem;
    color: #111827;
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 1rem 0;
    border-bottom: 1px solid #f3f4f6;
}

.info-row .label {
    color: #6b7280;
    font-size: 0.875rem;
}

.info-row .value {
    font-weight: 600;
    color: #111827;
    text-align: right;
}

.info-row .value.amount {
    font-size: 1.5rem;
    color: #10b981;
}

.info-row .value.small {
    font-size: 0.875rem;
}

.info-row .value.mono {
    font-family: monospace;
    font-size: 0.75rem;
}

/* Success Card */
.success-card {
    margin-top: 2rem;
    padding: 2rem;
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    border-radius: 16px;
    text-align: center;
}

.success-icon {
    width: 60px;
    height: 60px;
    background: #10b981;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    margin: 0 auto 1rem auto;
}

.success-card h4 {
    margin: 0 0 1.5rem 0;
    color: #065f46;
    font-size: 1.25rem;
}

.success-info {
    display: grid;
    gap: 1rem;
    margin-bottom: 1rem;
}

.info-item {
    background: white;
    padding: 0.75rem;
    border-radius: 8px;
    display: flex;
    justify-content: space-between;
}

.info-item span {
    color: #6b7280;
    font-size: 0.875rem;
}

.info-item strong {
    color: #111827;
}

.redirect-msg {
    color: #065f46;
    font-size: 0.875rem;
    margin: 1rem 0 0 0;
}

/* Payment Options */
.payment-options h3 {
    margin: 0 0 1.5rem 0;
    font-size: 1.25rem;
    color: #111827;
}

.payment-option {
    margin-bottom: 2rem;
}

.payment-option .hint {
    text-align: center;
    color: #6b7280;
    font-size: 0.875rem;
    margin-top: 1rem;
}

/* QR Code */
.qr-wrapper {
    background: white;
    padding: 1.5rem;
    border-radius: 16px;
    border: 2px dashed #e5e7eb;
    text-align: center;
}

.qr-wrapper img {
    max-width: 250px;
    width: 100%;
    height: auto;
    border-radius: 8px;
}

/* UPI Link Actions */
.upi-link-actions {
    display: flex;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.upi-link-actions .btn-modern {
    flex: 1;
}

.upi-link-text {
    background: #f9fafb;
    padding: 1rem;
    border-radius: 8px;
    font-family: monospace;
    font-size: 0.75rem;
    word-break: break-all;
    color: #6b7280;
    border: 1px solid #e5e7eb;
}

/* UPI Apps Grid */
.upi-apps-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.upi-app-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
    padding: 1.5rem;
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 16px;
    text-decoration: none;
    color: #374151;
    font-weight: 600;
    transition: all 0.3s;
    cursor: pointer;
}

.upi-app-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.upi-app-card.phonepe { border-color: #5f259f; }
.upi-app-card.phonepe:hover { background: #f5f3ff; border-color: #5f259f; }

.upi-app-card.gpay { border-color: #4285f4; }
.upi-app-card.gpay:hover { background: #eff6ff; border-color: #4285f4; }

.upi-app-card.paytm { border-color: #00baf2; }
.upi-app-card.paytm:hover { background: #ecfeff; border-color: #00baf2; }

.upi-app-card.bhim { border-color: #f26522; }
.upi-app-card.bhim:hover { background: #fff7ed; border-color: #f26522; }

.upi-icon {
    font-size: 2.5rem;
}

.upi-app-card.loading {
    pointer-events: none;
    opacity: 0.6;
}

/* Action Footer */
.action-footer {
    padding: 1.5rem 2rem;
    background: #f9fafb;
    border-top: 1px solid #e5e7eb;
    display: flex;
    gap: 1rem;
}

.action-footer .btn-modern {
    flex: 0 0 auto;
}

/* Instructions Sidebar */
.instructions-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.instruction-card,
.info-card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

.instruction-icon,
.info-icon {
    font-size: 2rem;
    margin-bottom: 1rem;
}

.instruction-card h3 {
    margin: 0 0 1.5rem 0;
    font-size: 1.125rem;
    color: #111827;
}

.instruction-section {
    margin-bottom: 1.5rem;
}

.instruction-section:last-child {
    margin-bottom: 0;
}

.instruction-section h4 {
    margin: 0 0 0.75rem 0;
    font-size: 0.95rem;
    color: #374151;
    font-weight: 600;
}

.instruction-section ol {
    margin: 0;
    padding-left: 1.25rem;
    color: #6b7280;
    font-size: 0.875rem;
    line-height: 1.6;
}

.instruction-section ol li {
    margin-bottom: 0.25rem;
}

/* Info Cards */
.info-card {
    display: flex;
    gap: 1rem;
}

.info-card.warning {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
}

.info-card.help {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
}

.info-content strong {
    display: block;
    margin-bottom: 0.5rem;
    color: #111827;
    font-size: 0.95rem;
}

.info-content ul {
    margin: 0;
    padding-left: 1.25rem;
    font-size: 0.875rem;
    line-height: 1.6;
    color: #374151;
}

.info-content ul li {
    margin-bottom: 0.25rem;
}

/* Loader */
.loader {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(4px);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.spinner {
    width: 50px;
    height: 50px;
    border: 4px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

.loader p {
    color: white;
    margin-top: 1rem;
    font-size: 1rem;
    font-weight: 500;
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Responsive Design */
@media (max-width: 1024px) {
    .payment-container {
        grid-template-columns: 1fr;
    }
    
    .payment-card.wide {
        grid-column: 1;
    }
    
    .instructions-sidebar {
        grid-row: 1;
    }
}

@media (max-width: 768px) {
    .main-content-body {
        padding: 1rem 0.5rem;
    }
    
    .payment-grid {
        grid-template-columns: 1fr;
        padding: 1.5rem;
        gap: 1.5rem;
    }
    
    .card-header-modern {
        padding: 2rem 1.5rem;
    }
    
    .card-body-modern {
        padding: 1.5rem;
    }
    
    .button-group-modern {
        flex-direction: column;
    }
    
    .upi-apps-grid {
        grid-template-columns: 1fr;
    }
    
    .qr-wrapper img {
        max-width: 200px;
    }
    
    .header-icon {
        font-size: 2.5rem;
    }
    
    .card-header-modern h2 {
        font-size: 1.5rem;
    }
    
    .amount-input-wrapper input {
        font-size: 1.25rem;
    }
    
    .action-footer {
        flex-direction: column;
    }
    
    .action-footer .btn-modern {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .payment-container {
        gap: 1rem;
    }
    
    .instruction-card,
    .info-card {
        padding: 1rem;
    }
    
    .status-header {
        padding: 1.5rem;
    }
    
    .info-row {
        flex-direction: column;
        gap: 0.25rem;
        padding: 0.75rem 0;
    }
    
    .info-row .value {
        text-align: left;
    }
}
</style>

@endsection