<?php

namespace App\Imports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Models\Payoutbulkupload;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithHeadingRow;


class BulkUpload implements ToCollection
{
    public function __construct($uniqueId)
    {
        $this->uniqueId = $uniqueId;
    }

    public function collection(Collection $collection)
    {
        $rows = $collection->toArray(); // Convert the collection to a regular array
        $headers = $rows[0]; // Extract headers
        unset($rows[0]); // Remove the header row from data rows

        $now = new \DateTime();
        $ctime = $now->format('Y-m-d H:i:s');
        foreach ($rows as $row) {
            // Combine headers with the data
            $row = array_combine($headers, $row);
            // Now you can use the row data with header keys
            Payoutbulkupload::insertGetId([
                'user_id' => Auth::id(),
                'mobile_number' => $row['mobile_number'],
                'email' => $row['email'],
                'beneficiary_name' => $row['beneficiary_name'],
                'ifsc_code' => $row['ifsc_code'],
                'account_number' => $row['account_number'],
                'amount' => floatval($row['amount']),
                'mode' => $row['mode'],
                'bulk_id' => $this->uniqueId,
                'status_id' => 3,
                'created_at' => now() // Use current timestamp
            ]);
        }
    }
}
