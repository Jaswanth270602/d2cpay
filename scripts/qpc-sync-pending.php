<?php

/**
 * Sync pending QPC payin orders (credits wallet when QPC shows SUCCESS).
 * Run: php scripts/qpc-sync-pending.php
 *      php scripts/qpc-sync-pending.php 195
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$orderArg = $argv[1] ?? null;
$cmd = 'qpc:sync-pending';
if ($orderArg !== null && $orderArg !== '') {
    $cmd .= ' --order=' . escapeshellarg($orderArg);
}

exit($kernel->call($cmd));
