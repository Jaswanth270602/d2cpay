<?php

/**
 * Seeds FrapPay (Payin 11) API id 18, service id 37, provider id 342.
 * Run: php scripts/setup-frappay-v11.php
 *
 * Credentials are stored in the database only — not committed to git.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Api;
use App\Models\Provider;
use Illuminate\Support\Facades\DB;

$apiId = 18;
$providerId = 342;
$serviceId = 37;

$credentials = [
    // Partner docs: UAT = /api/partner/uat , Production = /api/partner/v1 (needs IP whitelist)
    'base_url' => 'https://frappay.in/api/partner/uat',
    'api_key' => 'fpak_ba65a817069291ee0e32db0644c7bcb4096e',
    'secret_key' => 'fpsk_37b56c8ccf9ea078a231ff54b80d4d4ef9a4c252f9ffc6804926b963909170fd',
];

$apiData = [
    'api_name' => 'FrapPay',
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

$existingApi = Api::find($apiId);
if ($existingApi) {
    Api::where('id', $apiId)->update($apiData);
    echo "Updated API id {$apiId} (FrapPay)\n";
} else {
    $apiData['id'] = $apiId;
    $apiData['created_at'] = now();
    DB::table('apis')->insert($apiData);
    echo "Inserted API id {$apiId} (FrapPay)\n";
}

$payin10Service = DB::table('services')->where('id', 36)->first();
if (!$payin10Service) {
    $payin10Service = DB::table('services')->where('id', 35)->first();
}

$serviceData = [
    'service_name' => 'Payin 11',
    'service_image' => $payin10Service->service_image ?? 'storage/provider-icon/qpc-payin.png',
    'slug' => 'add-money/v11/welcome',
    'sub_slug' => null,
    'report_slug' => 'payin-eleven-history',
    'wallet_id' => $payin10Service->wallet_id ?? 1,
    'bbps' => 0,
    'servicegroup_id' => $payin10Service->servicegroup_id ?? 11,
    'report_is_static' => 0,
    'status_id' => 1,
    'updated_at' => now(),
];

$existingService = DB::table('services')->where('id', $serviceId)->first();
if ($existingService) {
    DB::table('services')->where('id', $serviceId)->update($serviceData);
    echo "Updated service id {$serviceId} (Payin 11)\n";
} else {
    $serviceData['id'] = $serviceId;
    $serviceData['created_at'] = now();
    DB::table('services')->insert($serviceData);
    echo "Inserted service id {$serviceId} (Payin 11)\n";
}

$providerData = [
    'provider_name' => 'Payin 11',
    'service_id' => $serviceId,
    'api_id' => $apiId,
    'min_amount' => 1,
    'max_amount' => 100000,
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

$sourceProviderId = 341;
if (!DB::table('commissions')->where('provider_id', $sourceProviderId)->exists()) {
    $sourceProviderId = 340;
}

$commissionTables = ['commissions', 'apicommissions', 'apiproviders'];
foreach ($commissionTables as $table) {
    if (!DB::getSchemaBuilder()->hasTable($table)) {
        continue;
    }
    $rows = DB::table($table)->where('provider_id', $sourceProviderId)->get();
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
    echo "Copied {$table} rows from provider {$sourceProviderId} to {$providerId}\n";
}

echo "Done. Callback URLs:\n";
echo "  Payin:  " . \App\Library\FrapPayLibrary::publicUrl('api/call-back/frappay-payin') . "\n";
echo "  Payout: " . \App\Library\FrapPayLibrary::publicUrl('api/call-back/frappay-payout') . "\n";
echo "  Web UI: " . url('agent/add-money/v11/welcome') . "\n";
echo "\nEnable Payin 11 separately: Admin -> Company Settings -> Active Service -> add 'Payin 11' (service id {$serviceId})\n";
echo "For production, set apis.id=18 credentials.base_url to https://frappay.in/api/partner/v1\n";
