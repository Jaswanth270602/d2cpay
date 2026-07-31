<?php

/**
 * Seeds AurexaPay (Payin 12) API id 19, service id 38, provider id 343.
 * Run: php scripts/setup-aurexapay-v12.php
 *
 * Credentials are stored in the database only — not committed to git.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Api;
use App\Models\Provider;
use Illuminate\Support\Facades\DB;

$apiId = 19;
$providerId = 343;
$serviceId = 38;

$credentials = [
    'base_url' => 'https://aurexapay.com/api/v1.1',
    'client_key' => 'FOUd3qir77',
    'client_secret' => '2d1xhcytvmguy6ak',
];

$apiData = [
    'api_name' => 'AurexaPay',
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
    echo "Updated API id {$apiId} (AurexaPay)\n";
} else {
    $apiData['id'] = $apiId;
    $apiData['created_at'] = now();
    DB::table('apis')->insert($apiData);
    echo "Inserted API id {$apiId} (AurexaPay)\n";
}

$payin11Service = DB::table('services')->where('id', 37)->first();
if (!$payin11Service) {
    $payin11Service = DB::table('services')->where('id', 36)->first();
}

$serviceData = [
    'service_name' => 'Payin 12',
    'service_image' => $payin11Service->service_image ?? 'storage/provider-icon/qpc-payin.png',
    'slug' => 'add-money/v12/welcome',
    'sub_slug' => null,
    'report_slug' => 'payin-twelve-history',
    'wallet_id' => $payin11Service->wallet_id ?? 1,
    'bbps' => 0,
    'servicegroup_id' => $payin11Service->servicegroup_id ?? 11,
    'report_is_static' => 0,
    'status_id' => 1,
    'updated_at' => now(),
];

$existingService = DB::table('services')->where('id', $serviceId)->first();
if ($existingService) {
    DB::table('services')->where('id', $serviceId)->update($serviceData);
    echo "Updated service id {$serviceId} (Payin 12)\n";
} else {
    $serviceData['id'] = $serviceId;
    $serviceData['created_at'] = now();
    DB::table('services')->insert($serviceData);
    echo "Inserted service id {$serviceId} (Payin 12)\n";
}

$providerData = [
    'provider_name' => 'Payin 12',
    'service_id' => $serviceId,
    'api_id' => $apiId,
    'min_amount' => 500,
    'max_amount' => 25000,
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

$sourceProviderId = 342;
if (!DB::table('commissions')->where('provider_id', $sourceProviderId)->exists()) {
    $sourceProviderId = DB::table('commissions')->where('provider_id', 341)->exists() ? 341 : 340;
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
echo "  Payin:  " . \App\Library\AurexaPayLibrary::publicUrl('api/call-back/aurexapay-payin') . "\n";
echo "  Payout: " . \App\Library\AurexaPayLibrary::publicUrl('api/call-back/aurexapay-payout') . "\n";
echo "  Web UI: " . url('agent/add-money/v12/welcome') . "\n";
echo "\nEnable Payin 12 separately: Admin -> Company Settings -> Active Service -> add 'Payin 12' (service id {$serviceId})\n";
