<?php

namespace App\Console\Commands;

use App\Http\Controllers\Agent\QuickPayCashController;
use App\Models\Gatewayorder;
use Illuminate\Console\Command;

class QpcSyncPendingPayins extends Command
{
    protected $signature = 'qpc:sync-pending {--order= : Gateway order id to sync}';

    protected $description = 'Sync pending QPC payin orders from QPC status API and credit wallet';

    public function handle(): int
    {
        $orderId = $this->option('order');
        $query = Gatewayorder::where('api_id', 16)->where('status_id', 3);

        if ($orderId !== null && $orderId !== '') {
            $query->where('id', (int)$orderId);
        }

        $orders = $query->orderBy('id')->get();
        if ($orders->isEmpty()) {
            $this->info('No pending QPC payin orders found.');
            return self::SUCCESS;
        }

        $controller = new QuickPayCashController();
        $synced = 0;

        foreach ($orders as $order) {
            $before = (int)$order->status_id;
            $controller->syncPendingOrderFromQpc($order);
            $order->refresh();

            if ((int)$order->status_id === 1 && $before === 3) {
                $synced++;
                $this->info("Order {$order->id} ({$order->order_token}) credited. report_id={$order->report_id}");
            } elseif ((int)$order->status_id === 2 && $before === 3) {
                $this->warn("Order {$order->id} marked failed at QPC.");
            } else {
                $this->line("Order {$order->id} still pending at QPC.");
            }
        }

        $this->info("Done. Credited {$synced} order(s).");
        return self::SUCCESS;
    }
}
