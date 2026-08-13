<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ReportNotification;
use App\Models\Store;
use App\Services\OrderStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $status = in_array((string) $request->query('status'), array_keys(Order::STATUSES), true)
            ? (string) $request->query('status')
            : '';
        $storeId = Store::query()->whereKey($request->integer('store_id'))->exists()
            ? $request->integer('store_id')
            : null;
        $search = Str::limit(trim((string) $request->query('q')), 100, '');

        $orders = Order::query()
            ->with(['user:id,name,email', 'store:id,name,slug,user_id'])
            ->withCount('items')
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($storeId, fn ($query) => $query->where('store_id', $storeId))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery
                        ->where('public_id', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%")
                        ->orWhere('store_name', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->latest('placed_at')
            ->paginate(20)
            ->withQueryString();

        $metrics = [
            'total' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'in_progress' => Order::whereIn('status', ['confirmed', 'preparing', 'ready'])->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];
        $stores = Store::query()
            ->whereHas('orders')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.orders.index', compact(
            'orders',
            'metrics',
            'stores',
            'status',
            'storeId',
            'search'
        ));
    }

    public function show(Order $order, OrderStatusService $statusService)
    {
        $order->load(['user', 'store.user', 'items']);

        return view('admin.orders.show', [
            'order' => $order,
            'nextStatuses' => $statusService->allowedTransitions($order),
        ]);
    }

    public function updateStatus(
        Request $request,
        Order $order,
        OrderStatusService $statusService
    ) {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(Order::STATUSES))],
        ]);

        $statusService->transition($order, $validated['status']);
        $order->loadMissing('store');

        if ($order->store && $order->store->user_id !== $request->user()->id) {
            ReportNotification::sendTo($order->store->user_id, [
                'kind' => 'order_admin_status',
                'message' => "A administração atualizou o pedido {$order->public_id} para: {$order->status_label}.",
                'action_url' => route('seller.orders.show', [$order->store, $order], false),
            ]);
        }

        return back()->with('success', 'Status do pedido atualizado pela administração.');
    }
}
