<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Stock;
use Carbon\Carbon;

class UpdateInStockStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stocks:update-in-stock-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update stock status to in_stock when in_stock_date matches current date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today()->toDateString();
        
        $updatedCount = Stock::where('in_stock_date', $today)
            ->where('status', 'pending')
            ->update(['status' => 'in_stock']);

        $this->info("Updated {$updatedCount} stocks to in_stock status for date: {$today}");
        
        return 0;
    }
}
