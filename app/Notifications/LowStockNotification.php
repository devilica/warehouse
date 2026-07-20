<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Product $product,
        private int $warehouseId,
        private float $currentQuantity,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Low stock alert'))
            ->line(__('Product :product is running low. Current quantity: :qty (minimum: :min).', [
                'product' => $this->product->name,
                'qty' => $this->currentQuantity,
                'min' => $this->product->min_stock,
            ]));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'low_stock',
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouseId,
            'current_quantity' => $this->currentQuantity,
            'min_stock' => $this->product->min_stock,
            'message' => __('Product :product is running low.', ['product' => $this->product->name]),
        ];
    }
}
