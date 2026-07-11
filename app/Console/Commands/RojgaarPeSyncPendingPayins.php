<?php

namespace App\Console\Commands;

use App\Http\Controllers\Agent\RojgaarPeController;
use App\Models\Gatewayorder;
use Illuminate\Console\Command;

class RojgaarPeSyncPendingPayins extends Command
{
    protected $signature = 'rojgaarpe:sync-pending {--order= : Gateway order id to sync}';

    protected $description = 'Sync pending RojgaarPe payin orders from status API and credit wallet';

    public function handle(): int
    {
        $orderId = $this->option('order');
        $query = Gatewayorder::where('api_id', 17)->whereIn('status_id', [3, 9]);

        if ($orderId !== null && $orderId !== '') {
            $query->where('id', (int)$orderId);
        }

        $orders = $query->orderBy('id')->get();
        if ($orders->isEmpty()) {
            $this->info('No pending RojgaarPe payin orders found.');
            return self::SUCCESS;
        }

        $controller = new RojgaarPeController();
        $synced = 0;

        foreach ($orders as $order) {
            $before = (int)$order->status_id;
            $credited = $controller->syncPendingOrderFromProvider($order);
            $order->refresh();

            if ($credited || ((int)$order->status_id === 1 && $before !== 1)) {
                $synced++;
                $this->info("Order {$order->id} ({$order->order_token}) credited. report_id={$order->report_id}");
            } elseif ((int)$order->status_id === 2 && $before === 3) {
                $this->warn("Order {$order->id} marked failed at RojgaarPe.");
            } else {
                $this->line("Order {$order->id} still pending at RojgaarPe.");
            }
        }

        $this->info("Done. Credited {$synced} order(s).");
        return self::SUCCESS;
    }
}
