<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderPlacedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public bool $forSeller = false
    ) {}

    public function build(): self
    {
        $subject = $this->forSeller
            ? "Novo pedido {$this->order->public_id}"
            : "Pedido {$this->order->public_id} recebido";

        return $this
            ->subject($subject)
            ->view('emails.orders.placed');
    }
}
