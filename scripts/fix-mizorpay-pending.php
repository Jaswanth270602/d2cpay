<?php

/**
 * Set MizorPay payout route and fail/refund pending reports that never reached MizorPay.
 * Run: php scripts/fix-mizorpay-pending.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Company;
use App\Models\Report;
use App\Models\Apiresponse;
use App\Library\RefundLibrary;

$updatedCompanies = Company::query()->update(['payout_route' => 20]);
echo "Updated {$updatedCompanies} company row(s) payout_route => 20 (MizorPay)\n";

$pending = Report::where('api_id', 20)->where('status_id', 3)->orderBy('id')->get();
echo 'Pending MizorPay reports: ' . $pending->count() . "\n";

$refund = new RefundLibrary();
foreach ($pending as $report) {
    $log = Apiresponse::where('report_id', $report->id)
        ->where('api_type', 20)
        ->where('response_type', 'payout_create')
        ->orderByDesc('id')
        ->first();

    $msg = (string)($log->message ?? '');
    $shouldFail = $msg === ''
        || stripos($msg, 'Invalid authentication') !== false
        || stripos($msg, 'credentials missing') !== false;

    if (!$shouldFail && $log) {
        $decoded = json_decode($msg, true);
        if (is_array($decoded) && !empty($decoded['error'])) {
            $shouldFail = true;
        }
    }

    if ($shouldFail) {
        $refund->update_transaction(2, 'Invalid authentication details (refunded)', $report->id, 'Manual cleanup');
        echo "Failed + refunded report id {$report->id} amount {$report->amount}\n";
    } else {
        echo "Left pending report id {$report->id}\n";
    }
}

echo "Done.\n";
