<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderStatusService
{
    private const TRANSITIONS = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['preparing', 'cancelled'],
        'preparing' => ['ready', 'cancelled'],
        'ready' => ['completed'],
        'completed' => [],
        'cancelled' => [],
    ];

    public function __construct(
        private readonly StockService $stock,
        private readonly OrderCommunicationService $communication,
    ) {
    }

    public function allowedTransitions(Order $order): array
    {
        return collect(self::TRANSITIONS[$order->status] ?? [])
            ->mapWithKeys(fn (string $status) => [$status => Order::STATUSES[$status]])
            ->all();
    }

    public function transition(Order $order, string $nextStatus): Order
    {
        DB::transaction(function () use ($order, $nextStatus) {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $allowed = self::TRANSITIONS[$lockedOrder->status] ?? [];

            if (! in_array($nextStatus, $allowed, true)) {
                throw ValidationException::withMessages([
                    'status' => 'Esta alteração de status não é permitida.',
                ]);
            }

            if ($nextStatus === 'confirmed') {
                $this->stock->deductForOrder($lockedOrder);
            }

            if ($nextStatus === 'cancelled') {
                $this->stock->restoreForOrder($lockedOrder);
            }

            $lockedOrder->update(['status' => $nextStatus]);
        });

        $order->refresh();
        $this->communication->statusChanged($order);

        return $order;
    }
}
