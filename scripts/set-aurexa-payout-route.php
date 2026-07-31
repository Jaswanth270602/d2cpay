<?php

/**
 * Set company payout_route to AurexaPay (api id 19).
 * Run: php scripts/set-aurexa-payout-route.php [company_id]
 * If company_id omitted, updates all companies currently on 17 or 18 (optional prompt via arg --all).
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$companyId = isset($argv[1]) && ctype_digit($argv[1]) ? (int)$argv[1] : null;
$all = in_array('--all', $argv, true);

echo "Current companies payout_route:\n";
$rows = DB::table('companies')->select('id', 'company_name', 'payout_route')->orderBy('id')->get();
foreach ($rows as $r) {
    echo "  id={$r->id} | {$r->company_name} | payout_route={$r->payout_route}\n";
}

if ($companyId) {
    $updated = DB::table('companies')->where('id', $companyId)->update([
        'payout_route' => 19,
        'updated_at' => now(),
    ]);
    echo $updated
        ? "\nUpdated company id {$companyId} payout_route => 19 (AurexaPay)\n"
        : "\nNo company found with id {$companyId}\n";
} elseif ($all) {
    $updated = DB::table('companies')->update([
        'payout_route' => 19,
        'updated_at' => now(),
    ]);
    echo "\nUpdated {$updated} company row(s) payout_route => 19 (AurexaPay)\n";
} else {
    echo "\nNo update. Pass company id, e.g.:\n";
    echo "  php scripts/set-aurexa-payout-route.php 1\n";
    echo "Or update all:\n";
    echo "  php scripts/set-aurexa-payout-route.php --all\n";
}

echo "\nBank transfer switching rows (can override company route):\n";
if (!DB::getSchemaBuilder()->hasTable('banktransferswitchings')) {
    echo "  (table banktransferswitchings not found)\n";
    exit(0);
}
$switches = DB::table('banktransferswitchings')->orderBy('id')->get();
if ($switches->isEmpty()) {
    echo "  (none)\n";
} else {
    foreach ($switches as $s) {
        echo "  id={$s->id} user_id=" . ($s->user_id ?? 'null')
            . " api_id={$s->api_id} min={$s->minimum_amount} max={$s->maximum_amount}\n";
    }
}
