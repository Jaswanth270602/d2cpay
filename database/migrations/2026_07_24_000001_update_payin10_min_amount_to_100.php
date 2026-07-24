<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('providers')) {
            return;
        }

        DB::table('providers')
            ->where('id', 341)
            ->orWhere(function ($query) {
                $query->where('provider_name', 'Payin 10')
                    ->where('service_id', 36);
            })
            ->update([
                'min_amount' => 100,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('providers')) {
            return;
        }

        DB::table('providers')
            ->where('id', 341)
            ->orWhere(function ($query) {
                $query->where('provider_name', 'Payin 10')
                    ->where('service_id', 36);
            })
            ->update([
                'min_amount' => 300,
                'updated_at' => now(),
            ]);
    }
};
