<?php

namespace App\Console\Commands;

use App\Models\Offer;
use Illuminate\Console\Command;

class ExpireStaleOffers extends Command
{
    protected $signature = 'offers:expire-stale';

    protected $description = 'Mark offers as expired when valid_until has passed and status is still draft or sent';

    public function handle(): int
    {
        $count = Offer::whereIn('status', ['draft', 'sent'])
            ->where('valid_until', '<', now()->startOfDay())
            ->update(['status' => 'expired']);

        $this->info("Expired {$count} stale offer(s).");

        return self::SUCCESS;
    }
}
