<?php

namespace App\Library;

use App\Http\Controllers\Agent\DirectTransferController;
use App\Models\Report;
use App\Models\User;
use App\Library\GetcommissionLibrary;

class LockHoldPayoutLibrary
{
    public const FLAG = 'waiting_for_bank_confirmation';
    public const FLAG_INSUFFICIENT = 'insufficient_wallet_balance';
    public const REASON_WAITING = 'Waiting for Bank Confirmation';
    public const REASON_INSUFFICIENT = 'Bank Downtime.Please try again after some time.';
    public const REASON_BANK_DOWNTIME = 'Bank Downtime.Please try again after some time.';

    /**
     * Hide provider/wallet technical failure text from merchants.
     */
    public static function merchantFacingFailureReason(?string $reason): string
    {
        $reason = trim((string)$reason);
        if ($reason === '') {
            return $reason;
        }

        $needles = [
            'insufficient',
            'consecutive requests',
            'break the pattern',
            'maximum number of',
            'try a different amount',
            'balance is low',
        ];
        foreach ($needles as $needle) {
            if (stripos($reason, $needle) !== false) {
                return self::REASON_BANK_DOWNTIME;
            }
        }

        return $reason;
    }

    public static function isLockHoldPending(Report $report): bool
    {
        $row = self::decodeRowData($report->row_data ?? null);
        return ($row['lock_hold_flag'] ?? '') === self::FLAG
            || trim((string)($report->reason ?? '')) === self::REASON_WAITING;
    }

    public static function decodeRowData($rowData): array
    {
        if (is_array($rowData)) {
            return $rowData;
        }
        if (!is_string($rowData) || $rowData === '') {
            return [];
        }
        $decoded = json_decode($rowData, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Wallet covers amount + fee (and lien), ignoring lock.
     */
    public static function hasSufficientWallet(float $balance, float $debitAmount, float $lienAmount): bool
    {
        return $balance >= ($debitAmount + $lienAmount);
    }

    /**
     * Wallet would drop below lock if payout is processed.
     */
    public static function isBlockedByLock(float $balance, float $debitAmount, float $lienAmount, float $lockAmount): bool
    {
        if (!self::hasSufficientWallet($balance, $debitAmount, $lienAmount)) {
            return false;
        }
        return $balance < ($debitAmount + $lienAmount + $lockAmount);
    }

    /**
     * After admin lock update: process held payouts that are now eligible.
     * Does not auto-retry reports already marked insufficient_wallet_balance.
     */
    public function processEligibleForUser(int $userId): array
    {
        $processed = 0;
        $keptInsufficient = 0;
        $skipped = 0;

        $reports = Report::where('user_id', $userId)
            ->where('status_id', 3)
            ->where('provider_id', 324)
            ->orderBy('id', 'asc')
            ->get();

        $controller = new DirectTransferController();

        foreach ($reports as $report) {
            $row = self::decodeRowData($report->row_data);
            if (($row['lock_hold_flag'] ?? '') !== self::FLAG) {
                $skipped++;
                continue;
            }

            $user = User::with('balance', 'company')->find($userId);
            if (!$user || !$user->balance) {
                $skipped++;
                continue;
            }

            $amount = (float)$report->amount;
            $schemeId = $user->scheme_id;
            $commissionLibrary = new GetcommissionLibrary();
            $commission = $commissionLibrary->get_commission($schemeId, 324, $amount);
            $retailer = ((float)($commission['retailer'] ?? 0) == 0) ? 10 : (float)$commission['retailer'];
            // Prefer fee stored at create time (profit is negative fee).
            $storedFee = abs((float)$report->profit);
            if ($storedFee > 0) {
                $retailer = $storedFee;
            }
            $debitAmount = $amount + $retailer;
            $balance = (float)$user->balance->user_balance;
            $lien = (float)($user->balance->lien_amount ?? 0);
            $lock = (float)($user->lock_amount ?? 0);

            if (!self::hasSufficientWallet($balance, $debitAmount, $lien)) {
                $row['lock_hold_flag'] = self::FLAG_INSUFFICIENT;
                Report::where('id', $report->id)->update([
                    'reason' => self::REASON_INSUFFICIENT,
                    'row_data' => json_encode($row),
                ]);
                $keptInsufficient++;
                continue;
            }

            if (self::isBlockedByLock($balance, $debitAmount, $lien, $lock)) {
                $skipped++;
                continue;
            }

            $result = $controller->processLockHeldPayout($report->id);
            if (($result['processed'] ?? false) === true) {
                $processed++;
            } elseif (($result['insufficient'] ?? false) === true) {
                $keptInsufficient++;
            } else {
                $skipped++;
            }
        }

        return compact('processed', 'keptInsufficient', 'skipped');
    }
}
