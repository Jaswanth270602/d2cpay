<?php

namespace App\Console\Commands;

use App\Http\Controllers\Agent\FrapPayController;
use App\Models\Gatewayorder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class FrapPaySyncPendingPayins extends Command
{
    protected $signature = 'frappay:sync-pending
                            {--order= : Gateway order id to sync}
                            {--limit=40 : Max pending orders to process (newest first)}
                            {--oldest : Process oldest first instead of newest}';

    protected $description = 'Sync pending FrapPay payin orders from status API and credit wallet';

    public function handle(): int
    {
        $orderId = $this->option('order');
        $limit = max(1, (int)$this->option('limit'));
        $oldestFirst = (bool)$this->option('oldest');

        $query = Gatewayorder::where('api_id', 18)->whereIn('status_id', [3, 9]);

        if ($orderId !== null && $orderId !== '') {
            $query->where('id', (int)$orderId);
        } else {
            $query->orderBy('id', $oldestFirst ? 'ASC' : 'DESC')->limit($limit);
        }

        $orders = $query->get();
        if ($orders->isEmpty()) {
            $this->info('No pending FrapPay payin orders found.');
            // Still attempt wallet reconcile (covers races / empty filtered set).
            try {
                $controller = new FrapPayController();
                $walletCredited = $controller->reconcilePendingFromPartnerWallet();
                if ($walletCredited !== []) {
                    $this->info('Wallet reconcile credited order(s): ' . implode(',', $walletCredited));
                }
            } catch (Throwable $e) {
                $this->error('Wallet reconcile error: ' . $e->getMessage());
            }
            return self::SUCCESS;
        }

        $this->info('Processing ' . $orders->count() . ' pending order(s)...');

        $controller = new FrapPayController();
        $synced = 0;
        $failed = 0;
        $errors = 0;
        $walletCreditedIds = [];

        // Prefer wallet reconcile first — FrapPay status API often lags behind dashboard.
        try {
            $walletCreditedIds = $controller->reconcilePendingFromPartnerWallet();
            if ($walletCreditedIds !== []) {
                $synced += count($walletCreditedIds);
                $this->info('Wallet reconcile credited order(s): ' . implode(',', $walletCreditedIds));
            }
        } catch (Throwable $e) {
            $errors++;
            $this->error('Wallet reconcile error: ' . $e->getMessage());
        }

        foreach ($orders as $order) {
            if (in_array((int)$order->id, $walletCreditedIds, true)) {
                continue;
            }

            try {
                DB::reconnect();
            } catch (Throwable $e) {
                $this->warn("DB reconnect warning before order {$order->id}: " . $e->getMessage());
            }

            $order->refresh();
            if ((int)$order->status_id === 1) {
                $synced++;
                $this->info("Order {$order->id} already credited. report_id={$order->report_id}");
                continue;
            }

            $before = (int)$order->status_id;

            try {
                $credited = $controller->syncPendingOrder($order);
                $order->refresh();

                if ($credited || ((int)$order->status_id === 1 && $before !== 1)) {
                    $synced++;
                    $this->info("Order {$order->id} ({$order->order_token}) credited. report_id={$order->report_id}");
                } elseif ((int)$order->status_id === 2 && $before === 3) {
                    $failed++;
                    $this->warn("Order {$order->id} marked failed at FrapPay.");
                } else {
                    $this->line("Order {$order->id} still pending at FrapPay.");
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
