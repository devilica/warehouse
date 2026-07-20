<?php

namespace App\Jobs;

use App\Models\ProductBatch;
use App\Models\User;
use App\Notifications\ProductExpiringNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;

class CheckExpiringProducts implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $expiringBatches = ProductBatch::query()
            ->with('product')
            ->whereNotNull('expiration_date')
            ->whereDate('expiration_date', '<=', now()->addDays(30))
            ->whereDate('expiration_date', '>=', now())
            ->get();

        if ($expiringBatches->isEmpty()) {
            return;
        }

        $recipients = User::role(['warehouse-manager', 'purchasing-manager', 'administrator'])->get();

        foreach ($expiringBatches as $batch) {
            Notification::send($recipients, new ProductExpiringNotification($batch));
        }
    }
}
