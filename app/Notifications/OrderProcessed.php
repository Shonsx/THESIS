<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Order; 
use App\Models\CustomNotification; 

class OrderProcessed extends Notification
{
    use Queueable;
    public $order;
    public $status;
    /**
     * Create a new notification instance.
     *
     * @param  \App\Models\Order  $order
     * @param  string  $status
     * @return void
     */

    public function __construct(Order $order, $status)
    {
        $this->order = $order;
        $this->status = $status;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toDatabase($notifiable)
    {
        CustomNotification::create([
            'notifiable_type' => get_class($this->order->user),
            'notifiable_id' => $this->order->user->id,
            'message' => 'Your order for ' . $this->order->product->name . ' is now being processed.',
            'order_id' => $this->order->id,
            'type' => 'App\Notifications\OrderProcessed',  // Add the type here
        ]);

        return [
            'message' => 'Your order for ' . $this->order->product->name . ' is now being processed.',
            'order_id' => $this->order->id,
            'status' => 'Processing',
            'product_name' => $this->order->product->name
        ];
    }


    public function getTable()
    {
        return 'create_notifications_table';  // specify your custom table name here
    }


    
    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => "Your order for {$this->order->product->name} is now {$this->status}.",
            'order_id' => $this->order->id,
            'status' => $this->status,
        ];
    }
}
