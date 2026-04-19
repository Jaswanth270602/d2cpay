<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BulkPayoutTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'mobile_number',
            'email',
            'beneficiary_name',
            'ifsc_code',
            'account_number',
            'amount',
            'mode',
        ];
    }

    public function array(): array
    {
        return [
            ['9876543210', 'user@example.com', 'John Doe', 'HDFC0001234', '123456789012', '1000', 'IMPS'],
            ['9876543211', 'user2@example.com', 'Jane Doe', 'SBIN0005678', '123456789013', '1500', 'NEFT'],
        ];
    }
}
