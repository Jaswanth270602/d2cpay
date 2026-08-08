<?php

namespace App\Console\Commands;

use App\Library\MizorPayLibrary;
use App\Models\Report;
use Illuminate\Console\Command;

class MizorPaySyncPendingPayouts extends Command
{
    protected $signature = 'mizorpay:sync-pending
                            {--report= : Report id to sync}
                            {--limit=20 : Max pending payouts to process}';

    protected $description = 'Sync pending MizorPay payout reports from status API';

    public function handle(): int
    {
        $reportId = $this->option('report');
        $limit = max(1, (int)$this->option('limit'));
        $apiId = 20;

        $query = Report::where('api_id', $apiId)->where('status_id', 3);

        if ($reportId !== null && $reportId !== '') {
            $query->where('id', (int)$reportId);
        } else {
            $query->orderBy('id', 'DESC')->limit($limit);
        }

        $reports = $query->get();
        if ($reports->isEmpty()) {
            $this->info('No pending MizorPay payout reports found.');
            return self::SUCCESS;
        }

        $library = new MizorPayLibrary();
        $updated = 0;

        foreach ($reports as $report) {
            $result = $library->checkStatusByCron($report->id);
            if ($result !== null) {
                $updated++;
                $this->info("Report {$report->id} updated.");
            } else {
                $this->line("Report {$report->id} still pending.");
            }
        }

        $this->info("Done. Updated {$updated} report(s).");
        return self::SUCCESS;
    }
}
