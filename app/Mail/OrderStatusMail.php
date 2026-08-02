<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public bool $forSeller = false
    ) {}

    public function build(): self
    {
        return $this
            ->subject("Atualização do pedido {$this->order->public_id}")
            ->view('emails.orders.status');
    }
}
