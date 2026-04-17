<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Apiresponse;
use App\Models\Report;
use App\Library\VtransactLibrary;

class VtransactPayoutStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:VtransactPayoutStatus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Apiresponse::insertGetId(['message' => 'VtransactPayoutStatus', 'created_at' => now(), 'request_message' => 'VtransactPayoutStatus',]);
        $api_id = 12;
        $reports = Report::where('api_id', $api_id)->where('status_id', 3)->paginate(20);
        foreach ($reports as $value){
            $insert_id = $value->id;
            $library = new VtransactLibrary();
            $library->checkStatusByCron($insert_id);
        }
        return ['status_id' => true, 'message' => 'Success'];
    }
}
