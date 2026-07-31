<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('services') || !Schema::hasTable('providers') || !Schema::hasTable('apis')) {
            return;
        }

        $template = DB::table('services')->where('id', 37)->first()
            ?: DB::table('services')->where('id', 36)->first();
        $now = now();

        $serviceData = [
            'service_name' => 'Payin 12',
            'service_image' => $template->service_image ?? 'storage/provider-icon/qpc-payin.png',
            'slug' => 'add-money/v12/welcome',
            'sub_slug' => null,
            'report_slug' => 'payin-twelve-history',
            'wallet_id' => $template->wallet_id ?? 1,
            'bbps' => 0,
            'servicegroup_id' => $template->servicegroup_id ?? 11,
            'report_is_static' => 0,
            'status_id' => 1,
            'updated_at' => $now,
        ];

        if (DB::table('services')->where('id', 38)->exists()) {
            DB::table('services')->where('id', 38)->update($serviceData);
        } else {
            DB::table('services')->insert(array_merge($serviceData, [
                'id' => 38,
                'created_at' => $now,
            ]));
        }

        $providerData = [
            'provider_name' => 'Payin 12',
            'service_id' => 38,
            'api_id' => 19,
            'min_amount' => 500,
            'max_amount' => 25000,
            'status_id' => 1,
            'updated_at' => $now,
        ];

        if (DB::table('providers')->where('id', 343)->exists()) {
            DB::table('providers')->where('id', 343)->update($providerData);
        } else {
            DB::table('providers')->insert(array_merge($providerData, [
                'id' => 343,
                'created_at' => $now,
            ]));
        }

        $existingApi = DB::table('apis')->where('id', 19)->first();
        $credentials = [
            'base_url' => 'https://aurexapay.com/api/v1.1',
            'client_key' => '',
            'client_secret' => '',
        ];

        if ($existingApi && !empty($existingApi->credentials)) {
            $decoded = json_decode($existingApi->credentials, true);
            if (is_array($decoded)) {
                $credentials = array_merge($credentials, array_intersect_key($decoded, $credentials));
            }
        }

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
            'updated_at' => $now,
        ];

        if ($existingApi) {
            DB::table('apis')->where('id', 19)->update($apiData);
        } else {
            DB::table('apis')->insert(array_merge($apiData, [
                'id' => 19,
                'created_at' => $now,
            ]));
        }

        $sourceProviderId = DB::table('commissions')->where('provider_id', 342)->exists() ? 342
            : (DB::table('commissions')->where('provider_id', 341)->exists() ? 341 : 340);

        foreach (['commissions', 'apicommissions', 'apiproviders'] as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $rows = DB::table($table)->where('provider_id', $sourceProviderId)->get();
            foreach ($rows as $row) {
                $data = (array) $row;
                unset($data['id']);
                $data['provider_id'] = 343;
                if ($table === 'apiproviders' || $table === 'apicommissions') {
                    $data['api_id'] = 19;
                }

                $exists = DB::table($table)
                    ->where('provider_id', 343)
                    ->where('scheme_id', $data['scheme_id'] ?? 0)
                    ->when(isset($data['min_amount']), fn ($q) => $q->where('min_amount', $data['min_amount']))
                    ->exists();

                if (!$exists) {
                    DB::table($table)->insert($data);
                }
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('services')) {
            return;
        }

        DB::table('services')->where('id', 38)->delete();
        DB::table('providers')->where('id', 343)->delete();
        DB::table('apis')->where('id', 19)->delete();

        foreach (['commissions', 'apicommissions', 'apiproviders'] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->where('provider_id', 343)->delete();
            }
        }
    }
};
