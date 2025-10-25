<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Order; 

class OrderProcessed extends Notification
{
    use Queueable;
    public $order;
    public $status;

    public function __construct(Order $order, $status)
    {
        $this->order = $order;
        $this->status = $status;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => 'Your order for ' . $this->order->product->name . ' is now being processed.',
            'order_id' => $this->order->id,
            'status' => 'Processing',
            'product_name' => $this->order->product->name
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => "Your order for {$this->order->product->name} is now {$this->status}.",
            'order_id' => $this->order->id,
            'status' => $this->status,
        ];
    }
}
