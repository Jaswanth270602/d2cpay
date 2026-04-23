<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addPayoutCallbackColumn('members');
        $this->addPayoutCallbackColumn('users');
        $this->addPayoutCallbackColumn('gatewayorders');
    }

    public function down(): void
    {
        foreach (['members', 'users', 'gatewayorders'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'payoutcallbackurl')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropColumn('payoutcallbackurl');
                });
            }
        }
    }

    private function addPayoutCallbackColumn(string $table): void
    {
        if (!Schema::hasTable($table) || Schema::hasColumn($table, 'payoutcallbackurl')) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($table) {
            if (Schema::hasColumn($table, 'callbackurl')) {
                $blueprint->string('payoutcallbackurl')->nullable()->after('callbackurl');
            } elseif (Schema::hasColumn($table, 'callback_url')) {
                $blueprint->string('payoutcallbackurl')->nullable()->after('callback_url');
            } elseif (Schema::hasColumn($table, 'call_back_url')) {
                $blueprint->string('payoutcallbackurl')->nullable()->after('call_back_url');
            } else {
                $blueprint->string('payoutcallbackurl')->nullable();
            }
        });
    }
};
