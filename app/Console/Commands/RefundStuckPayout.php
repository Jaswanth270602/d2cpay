<?php

namespace App\Console\Commands;

use App\Library\RefundLibrary;
use App\Models\Report;
use Illuminate\Console\Command;

class RefundStuckPayout extends Command
{
    protected $signature = 'payout:refund-stuck
                            {report_id : Report id to refund (e.g. 982)}
                            {--force : Refund even if status is not Failed (2)}';

    protected $description = 'Refund a stuck failed payout that was marked failed without wallet credit';

    public function handle(): int
    {
        $reportId = (int)$this->argument('report_id');
        $report = Report::find($reportId);

        if (!$report) {
            $this->error("Report {$reportId} not found.");
            return self::FAILURE;
        }

        if ((int)$report->wallet_type !== 1) {
            $this->error("Report {$reportId} is not a payout (wallet_type={$report->wallet_type}).");
            return self::FAILURE;
        }

        $alreadyRefunded = Report::where('txnid', 'Refund Id ' . $reportId)->where('status_id', 4)->exists();
        if ($alreadyRefunded) {
            $this->warn("Refund Id {$reportId} already exists. Nothing to do.");
            return self::SUCCESS;
        }

        $statusId = (int)$report->status_id;
        if ($statusId !== 2 && !$this->option('force')) {
            $this->error("Report {$reportId} status_id={$statusId} (expected 2 Failed). Use --force to override.");
            return self::FAILURE;
        }

        $this->info("Refunding report {$reportId}: amount={$report->amount}, profit={$report->profit}, user_id={$report->user_id}, status={$statusId}");

        $library = new RefundLibrary();
        $response = $library->update_transaction(2, (string)($report->txnid ?: 'Payment failed'), $reportId, 'Stuck payout refund');

        $payload = method_exists($response, 'getData') ? (array)$response->getData(true) : [];
        $this->line(json_encode($payload ?: ['raw' => 'done']));

        $report->refresh();
        $this->info("After: status_id={$report->status_id}, balance check via Refund Id row.");

        $refundRow = Report::where('txnid', 'Refund Id ' . $reportId)->where('status_id', 4)->first();
        if ($refundRow) {
            $this->info("Refund credited. Refund report id={$refundRow->id}, closing_balance={$refundRow->total_balance}");
            return self::SUCCESS;
        }

        $this->error('Refund row was not created. Check RefundLibrary response above.');
        return self::FAILURE;
    }
}
