<?php

namespace App\Services;

use App\Mail\OrderPlacedMail;
use App\Mail\OrderStatusMail;
use App\Models\Order;
use App\Models\ReportNotification;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderCommunicationService
{
    public function orderPlaced(Order $order): void
    {
        $order->loadMissing(['store.user', 'user', 'items']);
        if ($order->store) {
            ReportNotification::sendTo($order->store->user_id, [
                'kind' => 'order_received',
                'message' => "Novo pedido {$order->public_id} recebido.",
                'action_url' => route('seller.orders.show', [$order->store, $order]),
            ]);

            $this->sendMail(
                $order->store->user?->email,
                new OrderPlacedMail($order, true)
            );
        }

        $this->sendMail(
            $order->customer_email ?: $order->user?->email,
            new OrderPlacedMail($order)
        );
    }

    public function orderCancelled(Order $order): void
    {
        $order->loadMissing('store.user');
        if (! $order->store) {
            return;
        }

        ReportNotification::sendTo($order->store->user_id, [
            'kind' => 'order_cancelled',
            'message' => "O cliente cancelou o pedido {$order->public_id}.",
            'action_url' => route('seller.orders.show', [$order->store, $order]),
        ]);
        $this->sendMail($order->store->user?->email, new OrderStatusMail($order, true));
    }

    public function statusChanged(Order $order): void
    {
        $order->loadMissing('user');
        ReportNotification::sendTo($order->user_id, [
            'kind' => 'order_status',
            'message' => "O pedido {$order->public_id} agora está: {$order->status_label}.",
            'action_url' => route('orders.show', $order),
        ]);
        $this->sendMail(
            $order->customer_email ?: $order->user?->email,
            new OrderStatusMail($order)
        );
    }

    private function sendMail(?string $recipient, Mailable $mail): void
    {
        if (! $recipient) {
            return;
        }

        try {
            Mail::to($recipient)->send($mail);
        } catch (\Throwable $error) {
            Log::warning('Falha ao enviar comunicação de pedido.', [
                'recipient' => $recipient,
                'error' => $error->getMessage(),
            ]);
        }
    }
}
