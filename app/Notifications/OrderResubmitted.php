<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Order;

class OrderResubmitted extends Notification
{
    use Queueable;

    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Resubmission by ' . ($this->order->user->name ?? 'Customer'),
            'order_id' => $this->order->id,
            'status' => 'Pending',
            'redirect' => route('cashier.orderDetails', $this->order->id),
        ];
    }
}