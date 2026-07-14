<?php

namespace App\Console\Commands;

use App\Http\Controllers\Agent\RojgaarPeController;
use App\Models\Gatewayorder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class RojgaarPeSyncPendingPayins extends Command
{
    protected $signature = 'rojgaarpe:sync-pending
                            {--order= : Gateway order id to sync}
                            {--limit=40 : Max pending orders to process (newest first)}
                            {--oldest : Process oldest first instead of newest}';

    protected $description = 'Sync pending RojgaarPe payin orders from webhook logs / status API and credit wallet';

    public function handle(): int
    {
        $orderId = $this->option('order');
        $limit = max(1, (int)$this->option('limit'));
        $oldestFirst = (bool)$this->option('oldest');

        $query = Gatewayorder::where('api_id', 17)->whereIn('status_id', [3, 9]);

        if ($orderId !== null && $orderId !== '') {
            $query->where('id', (int)$orderId);
        } else {
            $query->orderBy('id', $oldestFirst ? 'ASC' : 'DESC')->limit($limit);
        }

        $orders = $query->get();
        if ($orders->isEmpty()) {
            $this->info('No pending RojgaarPe payin orders found.');
            return self::SUCCESS;
        }

        $this->info('Processing ' . $orders->count() . ' pending order(s)...');

        $controller = new RojgaarPeController();
        $synced = 0;
        $failed = 0;
        $errors = 0;

        foreach ($orders as $order) {
            try {
                DB::reconnect();
            } catch (Throwable $e) {
                $this->warn("DB reconnect warning before order {$order->id}: " . $e->getMessage());
            }

            $before = (int)$order->status_id;

            try {
                $credited = $controller->syncPendingOrderFromProvider($order);
                $order->refresh();

                if ($credited || ((int)$order->status_id === 1 && $before !== 1)) {
                    $synced++;
                    $this->info("Order {$order->id} ({$order->order_token}) credited. report_id={$order->report_id}");
                } elseif ((int)$order->status_id === 2 && $before === 3) {
                    $failed++;
                    $this->warn("Order {$order->id} marked failed at RojgaarPe.");
                } else {
                    $this->line("Order {$order->id} still pending at RojgaarPe.");
                }
            } catch (Throwable $e) {
                $errors++;
                $this->error("Order {$order->id} error: " . $e->getMessage());
                try {
                    DB::reconnect();
                    Gatewayorder::where('id', $order->id)->where('status_id', 9)->update(['status_id' => 3]);
                } catch (Throwable $inner) {
                    // ignore
                }
            }
        }

        $this->info("Done. Credited {$synced}, failed {$failed}, errors {$errors}.");
        return self::SUCCESS;
    }
}
