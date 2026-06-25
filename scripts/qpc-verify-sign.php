<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$c = json_decode(optional(\App\Models\Api::find(16))->credentials);
$k = $c->merchant_key;
$p = '4707891292672000';
$m = 'QPI196224918';
$s = 'FAILED';
echo strtoupper(md5($p . $m . $s . $k)) . PHP_EOL;
