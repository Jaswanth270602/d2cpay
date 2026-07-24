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

        $payin9 = DB::table('services')->where('id', 35)->first();
        $now = now();

        $serviceData = [
            'service_name' => 'Payin 10',
            'service_image' => $payin9->service_image ?? 'storage/provider-icon/qpc-payin.png',
            'slug' => 'add-money/v10/welcome',
            'sub_slug' => null,
            'report_slug' => 'payin-ten-history',
            'wallet_id' => $payin9->wallet_id ?? 1,
            'bbps' => 0,
            'servicegroup_id' => $payin9->servicegroup_id ?? 11,
            'report_is_static' => 0,
            'status_id' => 1,
            'updated_at' => $now,
        ];

        if (DB::table('services')->where('id', 36)->exists()) {
            DB::table('services')->where('id', 36)->update($serviceData);
        } else {
            DB::table('services')->insert(array_merge($serviceData, [
                'id' => 36,
                'created_at' => $now,
            ]));
        }

        $providerData = [
            'provider_name' => 'Payin 10',
            'service_id' => 36,
            'api_id' => 17,
            'min_amount' => 100,
            'max_amount' => 20000,
            'status_id' => 1,
            'updated_at' => $now,
        ];

        if (DB::table('providers')->where('id', 341)->exists()) {
            DB::table('providers')->where('id', 341)->update($providerData);
        } else {
            DB::table('providers')->insert(array_merge($providerData, [
                'id' => 341,
                'created_at' => $now,
            ]));
        }

        $existingApi = DB::table('apis')->where('id', 17)->first();
        $credentials = [
            'base_url' => 'https://rojgaarpe.com',
            'login_id' => '',
            'secret_key' => '',
            'payout_secret_key' => '',
        ];

        if ($existingApi && !empty($existingApi->credentials)) {
            $decoded = json_decode($existingApi->credentials, true);
            if (is_array($decoded)) {
                $credentials = array_merge($credentials, array_intersect_key($decoded, $credentials));
            }
        }

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
            'updated_at' => $now,
        ];

        if ($existingApi) {
            DB::table('apis')->where('id', 17)->update($apiData);
        } else {
            DB::table('apis')->insert(array_merge($apiData, [
                'id' => 17,
                'created_at' => $now,
            ]));
        }

        foreach (['commissions', 'apicommissions', 'apiproviders'] as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $rows = DB::table($table)->where('provider_id', 340)->get();
            foreach ($rows as $row) {
                $data = (array) $row;
                unset($data['id']);
                $data['provider_id'] = 341;
                if ($table === 'apiproviders' || $table === 'apicommissions') {
                    $data['api_id'] = 17;
                }

                $exists = DB::table($table)
                    ->where('provider_id', 341)
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

        DB::table('services')->where('id', 36)->delete();
        DB::table('providers')->where('id', 341)->delete();
        DB::table('apis')->where('id', 17)->delete();

        foreach (['commissions', 'apicommissions', 'apiproviders'] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->where('provider_id', 341)->delete();
            }
        }
    }
};
