<?php

namespace App\Imports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Models\Payoutbulkupload;
use Illuminate\Support\Facades\Auth;


class BulkUpload implements ToCollection
{
    public function __construct($uniqueId)
    {
        $this->uniqueId = $uniqueId;
    }

    public function collection(Collection $collection)
    {
        $rows = $collection->toArray(); // Convert the collection to a regular array
        if (empty($rows)) {
            return;
        }

        $headers = $rows[0]; // Extract headers
        unset($rows[0]); // Remove the header row from data rows
        $headers = array_map(function ($header) {
            return strtolower(trim((string)$header));
        }, $headers);

        $now = new \DateTime();
        $ctime = $now->format('Y-m-d H:i:s');
        foreach ($rows as $row) {
            // Combine headers with the data
            $row = array_combine($headers, $row);
            if (!$row) {
                continue;
            }

            $mobileNumber = trim((string)($row['mobile_number'] ?? ''));
            $email = trim((string)($row['email'] ?? ''));
            $beneficiaryName = trim((string)($row['beneficiary_name'] ?? ''));
            $ifscCode = strtoupper(trim((string)($row['ifsc_code'] ?? '')));
            $accountNumber = trim((string)($row['account_number'] ?? ''));
            $amount = (float)($row['amount'] ?? 0);
            $mode = strtoupper(trim((string)($row['mode'] ?? '')));

            // Ignore fully empty rows.
            if ($mobileNumber === '' && $email === '' && $beneficiaryName === '' && $ifscCode === '' && $accountNumber === '' && $amount <= 0) {
                continue;
            }

            // Now you can use the row data with header keys
            Payoutbulkupload::insertGetId([
                'user_id' => Auth::id(),
                'mobile_number' => $mobileNumber,
                'email' => $email,
                'beneficiary_name' => $beneficiaryName,
                'ifsc_code' => $ifscCode,
                'account_number' => $accountNumber,
                'amount' => $amount,
                'mode' => $mode,
                'bulk_id' => $this->uniqueId,
                'status_id' => 3,
                'created_at' => now() // Use current timestamp
            ]);
        }
    }
}
