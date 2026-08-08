<?php

/**
 * Seeds MizorPay payout API id 20 (payout-only gateway).
 *
 * Run (empty credentials):
 *   php scripts/setup-mizorpay-payout.php
 *
 * Run with Token-Id + Secret-Key (recommended on server — do not commit secrets):
 *   php scripts/setup-mizorpay-payout.php "YOUR_TOKEN_ID" "YOUR_SECRET_KEY"
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Api;
use App\Library\MizorPayLibrary;
use Illuminate\Support\Facades\DB;

$apiId = 20;

$tokenId = trim((string)($argv[1] ?? getenv('MIZORPAY_TOKEN_ID') ?: ''));
$secretKey = trim((string)($argv[2] ?? getenv('MIZORPAY_SECRET_KEY') ?: ''));

$existingApi = Api::find($apiId);
$existingCredentials = [];
if ($existingApi && !empty($existingApi->credentials)) {
    $decoded = json_decode($existingApi->credentials, true);
    if (is_array($decoded)) {
        $existingCredentials = $decoded;
    }
}

$credentials = array_merge([
    'base_url' => 'https://payout.mizorpay.in',
    'token_id' => '',
    'secret_key' => '',
], $existingCredentials);

if ($tokenId !== '') {
    $credentials['token_id'] = $tokenId;
}
if ($secretKey !== '') {
    $credentials['secret_key'] = $secretKey;
}

$apiData = [
    'api_name' => 'MizorPay',
    'base_url' => $credentials['base_url'],
    'method' => 1,
    'response_type' => 1,
    'status_id' => 1,
    'user_id' => 1,
    'support_number' => '1234567890',
    'speed_status' => 0,
    'speed_limit' => 100000,
    'credentials' => json_encode($credentials),
    'vender_id' => 0,
    'company_id' => 1,
    'updated_at' => now(),
];

if ($existingApi) {
    Api::where('id', $apiId)->update($apiData);
    echo "Updated API id {$apiId} (MizorPay)\n";
} else {
    $apiData['id'] = $apiId;
    $apiData['created_at'] = now();
    DB::table('apis')->insert($apiData);
    echo "Inserted API id {$apiId} (MizorPay)\n";
}

if ($credentials['token_id'] !== '' && $credentials['secret_key'] !== '') {
    echo "Credentials saved (token_id prefix: " . substr($credentials['token_id'], 0, 8) . "...)\n";
    $library = new MizorPayLibrary();
    $balance = $library->checkBalance();
    if ($balance['ok'] ?? false) {
        echo "Wallet balance: Rs " . number_format((float)$balance['balance'], 2) . "\n";
    } else {
        echo "Balance check: " . ($balance['message'] ?? 'failed') . "\n";
    }
} else {
    echo "Credentials not set — pass token_id and secret_key as CLI args.\n";
}

echo "\nMizorPay payout callback URL:\n";
echo "  https://d2cpay.co/api/call-back/mizorpay-payout\n\n";
echo "Whitelist IPs on MizorPay:\n";
echo "  Server (live): 132.148.176.36\n";
echo "  PC testing:    223.228.126.10\n\n";
echo "Next steps:\n";
echo "  1. Set company payout_route => {$apiId} or Bank Transfer Switching\n";
echo "  2. Fund MizorPay wallet before live payout test\n";
