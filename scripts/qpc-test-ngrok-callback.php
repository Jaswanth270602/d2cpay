<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Api;
use App\Models\Gatewayorder;
use App\library\QuickPayCashLibrary;

$order = Gatewayorder::find(197);
$credentials = json_decode(optional(Api::find(16))->credentials);
$k = $credentials->merchant_key;
$m = $order->order_token;
$p = (string)$order->remark;
$sign = strtoupper(md5($p . $m . 'SUCCESS' . $k));

$payload = json_encode([
    'merchantNo' => $credentials->merchant_id,
    'merchantOrderNo' => $m,
    'platOrderNo' => $p,
    'orderStatus' => 'SUCCESS',
    'utr' => 'NGROK' . time(),
    'amount' => (float)$order->amount,
    'sign' => $sign,
]);

$base = QuickPayCashLibrary::publicBaseUrl();
$url = rtrim($base, '/') . '/api/call-back/qpc-payin';
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'ngrok-skip-browser-warning: 1',
    ],
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
]);
$response = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "POST $url\nHTTP $code\n$response\n";
