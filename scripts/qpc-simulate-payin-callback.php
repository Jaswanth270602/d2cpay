<?php

/**
 * Simulate a QPC payin SUCCESS callback for a pending gateway order.
 * Usage: php scripts/qpc-simulate-payin-callback.php [merchantOrderNo|gatewayOrderId]
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Api;
use App\Models\Gatewayorder;
use App\library\QuickPayCashLibrary;

$ref = $argv[1] ?? '';
if ($ref === '') {
    $order = Gatewayorder::where('api_id', 16)->where('status_id', 3)->orderBy('id', 'DESC')->first();
} elseif (ctype_digit($ref)) {
    $order = Gatewayorder::where('id', (int)$ref)->where('api_id', 16)->first();
} else {
    $order = Gatewayorder::where('api_id', 16)
        ->where(function ($q) use ($ref) {
            $q->where('order_token', $ref)->orWhere('client_id', $ref);
        })->first();
}

if (!$order) {
    fwrite(STDERR, "No matching QPC gateway order found.\n");
    exit(1);
}

$credentials = json_decode(optional(Api::find(16))->credentials);
$merchantKey = $credentials->merchant_key ?? '';
$merchantNo = $credentials->merchant_id ?? '';
$merchantOrderNo = $order->order_token;
$platOrderNo = (string)($order->remark ?: '4707899999999000');
$orderStatus = 'SUCCESS';
$amount = (float)$order->amount;
$utr = 'TESTUTR' . time();
$sign = strtoupper(md5($platOrderNo . $merchantOrderNo . $orderStatus . $merchantKey));

// QPC production webhook format (sign + platOrderNo + orderStatus)
$payload = [
    'merchantNo' => $merchantNo,
    'merchantOrderNo' => $merchantOrderNo,
    'platOrderNo' => $platOrderNo,
    'orderStatus' => $orderStatus,
    'utr' => $utr,
    'amount' => $amount,
    'merchantFee' => round($amount * 0.01, 2),
    'sign' => $sign,
    'timestamp' => (string)time(),
];

$base = QuickPayCashLibrary::publicBaseUrl();
$url = rtrim($base, '/') . '/api/call-back/qpc-payin';

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT => 30,
]);
$response = curl_exec($ch);
$httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "Order: #{$order->id} {$merchantOrderNo}\n";
echo "POST {$url}\n";
echo "Payload: " . json_encode($payload) . "\n";
echo "HTTP {$httpCode}\n";
echo "Response: {$response}\n";
if ($error !== '') {
    echo "cURL error: {$error}\n";
    exit(1);
}

$order->refresh();
echo "Gateway status_id: {$order->status_id}, report_id: " . ($order->report_id ?: 'null') . "\n";
exit($httpCode >= 200 && $httpCode < 300 ? 0 : 1);
