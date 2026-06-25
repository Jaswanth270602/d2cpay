<?php
/**
 * Create a pending QPC test order and fire a real-format SUCCESS callback.
 */
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Api;
use App\Models\Gatewayorder;
use App\Models\User;
use App\library\QuickPayCashLibrary;

$user = User::orderBy('id')->first();
if (!$user) {
    fwrite(STDERR, "No user found.\n");
    exit(1);
}

$ctime = now()->format('Y-m-d H:i:s');
$gatewayOrderId = Gatewayorder::insertGetId([
    'user_id' => $user->id,
    'purpose' => 'Add Money',
    'amount' => 50,
    'email' => $user->email ?? '',
    'api_id' => 16,
    'status_id' => 3,
    'mode' => 'WEB',
    'ip_address' => '127.0.0.1',
    'created_at' => $ctime,
    'order_token' => 'QPCTMP' . time(),
]);

$merchantOrderNo = (new QuickPayCashLibrary())->buildPayinOrderNo($gatewayOrderId);
Gatewayorder::where('id', $gatewayOrderId)->update([
    'order_token' => $merchantOrderNo,
    'client_id' => $merchantOrderNo,
    'remark' => '4707899999999000',
]);

$credentials = json_decode(optional(Api::find(16))->credentials);
$merchantKey = $credentials->merchant_key ?? '';
$merchantNo = $credentials->merchant_id ?? '';
$platOrderNo = '4707899999999000';
$orderStatus = 'SUCCESS';
$amount = 50.0;
$utr = 'QPCUTR' . time();
$sign = strtoupper(md5($platOrderNo . $merchantOrderNo . $orderStatus . $merchantKey));

$payload = [
    'merchantNo' => $merchantNo,
    'merchantOrderNo' => $merchantOrderNo,
    'platOrderNo' => $platOrderNo,
    'orderStatus' => $orderStatus,
    'utr' => $utr,
    'amount' => $amount,
    'merchantFee' => 0.5,
    'sign' => $sign,
    'timestamp' => (string)time(),
];

$url = 'http://127.0.0.1:8000/api/call-back/qpc-payin';
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_RETURNTRANSFER => true,
]);
$response = curl_exec($ch);
$httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$order = Gatewayorder::find($gatewayOrderId);
echo "Created order #{$gatewayOrderId} {$merchantOrderNo}\n";
echo "HTTP {$httpCode}\n{$response}\n";
echo "status_id={$order->status_id}, report_id=" . ($order->report_id ?: 'null') . "\n";

exit($httpCode === 200 && (int)$order->status_id === 1 ? 0 : 1);
