<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page_title ?? 'Complete Payment' }}</title>
    <style>
        body { font-family: Segoe UI, Arial, sans-serif; background: #f4f6fb; margin: 0; padding: 40px 16px; }
        .box { max-width: 420px; margin: 0 auto; background: #fff; border-radius: 12px; padding: 28px; box-shadow: 0 8px 24px rgba(0,0,0,.08); text-align: center; }
        h1 { font-size: 20px; margin: 0 0 8px; color: #1f2937; }
        p { color: #6b7280; margin: 0 0 18px; font-size: 14px; }
        .amt { font-size: 28px; font-weight: 700; color: #111827; margin-bottom: 18px; }
        button { background: linear-gradient(90deg,#2563eb,#7c3aed); color: #fff; border: 0; border-radius: 8px; padding: 12px 18px; font-size: 15px; cursor: pointer; width: 100%; }
        .hint { margin-top: 14px; font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
<div class="box">
    <h1>Complete UPI Payment</h1>
    <p>Order #{{ $order_id ?? $txnid ?? '' }} — redirecting to payment gateway…</p>
    <div class="amt">₹{{ number_format((float)$amount, 2) }}</div>
    <form id="easebuzzForm" method="POST" action="{{ $initiate_url ?? $action }}">
        <input type="hidden" name="access_key" value="{{ $access_key }}">
        <input type="hidden" name="payment_mode" value="UPI">
        <input type="hidden" name="upi_qr" value="true">
        <button type="submit">Continue to Pay</button>
    </form>
    <div class="hint">If you are not redirected automatically, click the button above.</div>
</div>
<script>
    setTimeout(function () {
        document.getElementById('easebuzzForm').submit();
    }, 400);
</script>
</body>
</html>
