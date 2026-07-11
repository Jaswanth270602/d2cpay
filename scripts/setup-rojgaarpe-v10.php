<?php

/**
 * Seeds RojgaarPe (Payin 10) API id 17 and provider id 341.
 * Run: php scripts/setup-rojgaarpe-v10.php
 *
 * Credentials are stored in the database only — not committed to git.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Api;
use App\Models\Provider;
use Illuminate\Support\Facades\DB;

$apiId = 17;
$providerId = 341;

$credentials = [
    'base_url' => 'https://rojgaarpe.com',
    'login_id' => 'ROJ121002',
    'secret_key' => '783EC60A0320452492EC9DFD7B1C3191',
    'payout_secret_key' => '92CC227F747B4D5B8B3B8A5984B5E25E',
];

$apiData = [
    'api_name' => 'RojgaarPe',
    'base_url' => 'https://rojgaarpe.com',
    'method' => 1,
    'response_type' => 1,
    'status_id' => 1,
    'user_id' => 1,
    'support_number' => '1234567890',
    'speed_status' => 0,
    'speed_limit' => 30000,
    'credentials' => json_encode($credentials),
    'vender_id' => 0,
    'company_id' => 1,
    'updated_at' => now(),
];

$existingApi = Api::find($apiId);
if ($existingApi) {
    Api::where('id', $apiId)->update($apiData);
    echo "Updated API id {$apiId} (RojgaarPe)\n";
} else {
    $apiData['id'] = $apiId;
    $apiData['created_at'] = now();
    DB::table('apis')->insert($apiData);
    echo "Inserted API id {$apiId} (RojgaarPe)\n";
}

$qpcProvider = Provider::find(340);
$payin9Service = DB::table('services')->where('id', 35)->first();
$serviceId = 36;

$serviceData = [
    'service_name' => 'Payin 10',
    'service_image' => $payin9Service->service_image ?? 'storage/provider-icon/qpc-payin.png',
    'slug' => 'add-money/v10/welcome',
    'sub_slug' => null,
    'report_slug' => 'payin-ten-history',
    'wallet_id' => $payin9Service->wallet_id ?? 1,
    'bbps' => 0,
    'servicegroup_id' => $payin9Service->servicegroup_id ?? 11,
    'report_is_static' => 0,
    'status_id' => 1,
    'updated_at' => now(),
];

$existingService = DB::table('services')->where('id', $serviceId)->first();
if ($existingService) {
    DB::table('services')->where('id', $serviceId)->update($serviceData);
    echo "Updated service id {$serviceId} (Payin 10)\n";
} else {
    $serviceData['id'] = $serviceId;
    $serviceData['created_at'] = now();
    DB::table('services')->insert($serviceData);
    echo "Inserted service id {$serviceId} (Payin 10)\n";
}

$providerData = [
    'provider_name' => 'Payin 10',
    'service_id' => $serviceId,
    'api_id' => $apiId,
    'min_amount' => 300,
    'max_amount' => 20000,
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
    $rows = DB::table($table)->where('provider_id', 340)->get();
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
    echo "Copied {$table} rows from provider 340 to {$providerId}\n";
}

echo "Done. Callback URLs:\n";
echo "  Payin:  " . \App\library\RojgaarPeLibrary::publicUrl('api/call-back/rojgaarpe-payin') . "\n";
echo "  Payout: " . \App\library\RojgaarPeLibrary::publicUrl('api/call-back/rojgaarpe-payout') . "\n";
echo "  Web UI: " . url('agent/add-money/v10/welcome') . "\n";
echo "\nEnable Payin 10 separately: Admin -> Company Settings -> Active Service -> add 'Payin 10' (service id {$serviceId})\n";
