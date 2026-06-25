<?php

/**
 * Seeds Quick Pay Cash (QPC) API id 16 and provider id 340.
 * Run: php scripts/setup-qpc-v9.php
 *
 * Credentials are stored in the database only — not committed to git.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Api;
use App\Models\Provider;
use Illuminate\Support\Facades\DB;

$apiId = 16;
$providerId = 340;

$credentials = [
    'base_url' => env('QPC_BASE_URL', 'https://portalquickpaycash.com'),
    'merchant_id' => env('QPC_MERCHANT_ID', ''),
    'merchant_key' => env('QPC_MERCHANT_KEY', ''),
    'username' => env('QPC_USERNAME', ''),
    'password' => env('QPC_PASSWORD', ''),
];

if ($credentials['merchant_id'] === '' || $credentials['merchant_key'] === '') {
    fwrite(STDERR, "Set QPC_MERCHANT_ID and QPC_MERCHANT_KEY in .env before running this script.\n");
    exit(1);
}

$apiData = [
    'api_name' => 'Quick Pay Cash',
    'base_url' => 'https://portalquickpaycash.com',
    'method' => 1,
    'response_type' => 1,
    'status_id' => 1,
    'user_id' => 1,
    'support_number' => '1234567890',
    'speed_status' => 0,
    'speed_limit' => 25000,
    'credentials' => json_encode($credentials),
    'vender_id' => 0,
    'company_id' => 1,
    'updated_at' => now(),
];

$existingApi = Api::find($apiId);
if ($existingApi) {
    Api::where('id', $apiId)->update($apiData);
    echo "Updated API id {$apiId} (Quick Pay Cash)\n";
} else {
    $apiData['id'] = $apiId;
    $apiData['created_at'] = now();
    DB::table('apis')->insert($apiData);
    echo "Inserted API id {$apiId} (Quick Pay Cash)\n";
}

$zigProvider = Provider::find(339);
$payin8Service = DB::table('services')->where('id', 34)->first();
$serviceId = 35;

$serviceData = [
    'service_name' => 'Payin 9',
    'service_image' => $payin8Service->service_image ?? 'storage/provider-icon/qpc-payin.png',
    'slug' => 'add-money/v9/welcome',
    'sub_slug' => null,
    'report_slug' => 'payin-nine-history',
    'wallet_id' => $payin8Service->wallet_id ?? 1,
    'bbps' => 0,
    'servicegroup_id' => $payin8Service->servicegroup_id ?? 11,
    'report_is_static' => 0,
    'status_id' => 1,
    'updated_at' => now(),
];

$existingService = DB::table('services')->where('id', $serviceId)->first();
if ($existingService) {
    DB::table('services')->where('id', $serviceId)->update($serviceData);
    echo "Updated service id {$serviceId} (Payin 9)\n";
} else {
    $serviceData['id'] = $serviceId;
    $serviceData['created_at'] = now();
    DB::table('services')->insert($serviceData);
    echo "Inserted service id {$serviceId} (Payin 9)\n";
}

$providerData = [
    'provider_name' => 'Payin 9',
    'service_id' => $serviceId,
    'api_id' => $apiId,
    'min_amount' => 100,
    'max_amount' => 10000,
    'status_id' => 1,
    'updated_at' => now(),
];

$existingProvider = Provider::find($providerId);
if ($existingProvider) {
    Provider::where('id', $providerId)->update($providerData);
    echo "Updated provider id {$providerId}\n";
} else {
    $providerData['id'] = $providerId;
    $providerData['created_at'] = now();
    DB::table('providers')->insert($providerData);
    echo "Inserted provider id {$providerId}\n";
}

$commissionTables = ['commissions', 'apicommissions', 'apiproviders'];
foreach ($commissionTables as $table) {
    if (!DB::getSchemaBuilder()->hasTable($table)) {
        continue;
    }
    $rows = DB::table($table)->where('provider_id', 339)->get();
    foreach ($rows as $row) {
        $data = (array)$row;
        unset($data['id']);
        $data['provider_id'] = $providerId;
        if ($table === 'apiproviders' || $table === 'apicommissions') {
            $data['api_id'] = $apiId;
        }
        $exists = DB::table($table)
            ->where('provider_id', $providerId)
            ->where('scheme_id', $data['scheme_id'] ?? 0)
            ->when(isset($data['min_amount']), fn ($q) => $q->where('min_amount', $data['min_amount']))
            ->exists();
        if (!$exists) {
            DB::table($table)->insert($data);
        }
    }
    echo "Copied {$table} rows from provider 339 to {$providerId}\n";
}

echo "Done. Callback URLs:\n";
echo "  Payin:  " . \App\library\QuickPayCashLibrary::publicUrl('api/call-back/qpc-payin') . "\n";
echo "  Payout: " . \App\library\QuickPayCashLibrary::publicUrl('api/call-back/qpc-payout') . "\n";
echo "  Web UI: " . url('agent/add-money/v9/welcome') . "\n";
echo "\nEnable Payin 9 separately: Admin -> Company Settings -> Active Service -> add 'Payin 9' (service id {$serviceId})\n";
