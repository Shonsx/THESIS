<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Order;

class OrderRejected extends Notification
{
    use Queueable;

    protected $order;
    protected $reason;

    public function __construct(Order $order, string $reason)
    {
        $this->order = $order;
        $this->reason = $reason;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Your order for ' . ($this->order->product ? $this->order->product->name : 'Unknown Product') . ' was rejected.',
            'order_id' => $this->order->id,
            'status' => 'Rejected',
            'reason' => $this->reason,
            'redirect' => route('orders.rejection', $this->order->id),
        ];
    }
}