<?php

namespace App\Notifications;

use App\Models\ProductBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductExpiringNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private ProductBatch $batch) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Product expiring soon'))
            ->line(__('Product :product (lot :lot) expires on :date.', [
                'product' => $this->batch->product?->name ?? 'Unknown',
                'lot' => $this->batch->lot_number,
                'date' => $this->batch->expiration_date?->format('Y-m-d'),
            ]));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'product_expiring',
            'product_id' => $this->batch->product_id,
            'lot_number' => $this->batch->lot_number,
            'expiration_date' => $this->batch->expiration_date?->toDateString(),
            'message' => __('Product :product expires on :date.', [
                'product' => $this->batch->product?->name ?? 'Unknown',
                'date' => $this->batch->expiration_date?->format('Y-m-d'),
            ]),
        ];
    }
}
