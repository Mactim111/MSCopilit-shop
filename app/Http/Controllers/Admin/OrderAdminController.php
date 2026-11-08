<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderAdminController extends Controller
{
    private const FILTERS = [
        'all',
        'created',
        'processing',
        'ready_for_pickup',
        'in_transit',
        'cancellation',
        'cancelled',
        'completed',
    ];

    private const SORTS = [
        'id',
        'customer',
        'email',
        'total',
        'created_at',
        'completed_at',
    ];

    public function index(Request $request)
    {
        $data = $request->validate([
            'filter' => ['sometimes', Rule::in(self::FILTERS)],
            'sort' => ['sometimes', Rule::in(self::SORTS)],
            'direction' => ['sometimes', Rule::in(['asc', 'desc'])],
        ]);
        $filter = $data['filter'] ?? 'all';
        $sort = $data['sort'] ?? 'id';
        $defaultDirection = in_array($sort, ['customer', 'email'], true) ? 'asc' : 'desc';
        $direction = $data['direction'] ?? $defaultDirection;
        $baseQuery = Order::query();
        $orderCounts = [];

        foreach (self::FILTERS as $filterKey) {
            $orderCounts[$filterKey] = $this->applyFilter(clone $baseQuery, $filterKey)->count();
        }

        if ($filter !== 'all' && $orderCounts[$filter] === 0) {
            return redirect()->route('admin.orders.index', [
                'filter' => 'all',
                'sort' => $sort,
                'direction' => $direction,
            ]);
        }

        $ordersQuery = $this->applyFilter($baseQuery, $filter);
        if ($sort === 'customer') {
            $ordersQuery->orderBy('name', $direction)->orderBy('last_name', $direction);
        } else {
            $ordersQuery->orderBy($sort, $direction);
        }

        if ($sort !== 'id') {
            $ordersQuery->orderByDesc('id');
        }

        $orders = $ordersQuery->paginate(20)->withQueryString();

        return view('admin.orders.index', compact('orders', 'orderCounts', 'filter', 'sort', 'direction'));
    }

    public function show(Order $order)
    {
        $order->load('items.variant');

        return view('admin.orders.show', compact('order'));
    }

    public function transition(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', 'in:start_processing,mark_ready,mark_in_transit,complete,cancel,reject_cancellation'],
        ]);

        DB::transaction(function () use ($order, $data): void {
            $lockedOrder = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->isFinalized()) {
                throw ValidationException::withMessages([
                    'status' => 'Завершённый или отменённый заказ нельзя изменить.',
                ]);
            }

            $action = $data['action'];
            if (
                $lockedOrder->cancellation_requested_at !== null
                && ! in_array($action, ['cancel', 'reject_cancellation'], true)
            ) {
                throw ValidationException::withMessages([
                    'status' => 'Сначала подтвердите или отклоните запрос покупателя на отмену.',
                ]);
            }

            if ($action === 'cancel' && $lockedOrder->cancellation_requested_at === null) {
                throw ValidationException::withMessages([
                    'status' => 'Нельзя отменить заказ без ожидающего запроса покупателя.',
                ]);
            }

            $nextStatus = match ($action) {
                'start_processing' => $this->requireCurrentStatus(
                    $lockedOrder,
                    Order::STATUS_NEW,
                    Order::STATUS_PROCESSING
                ),
                'mark_ready' => $this->requireCurrentStatus(
                    $this->requireDeliveryMethod($lockedOrder, Order::DELIVERY_PICKUP),
                    Order::STATUS_PROCESSING,
                    Order::STATUS_READY_FOR_PICKUP
                ),
                'mark_in_transit' => $this->requireCurrentStatus(
                    $this->requireDeliveryMethod($lockedOrder, Order::DELIVERY_COURIER),
                    Order::STATUS_PROCESSING,
                    Order::STATUS_IN_TRANSIT
                ),
                'complete' => $this->completeOrder($lockedOrder),
                'cancel' => Order::STATUS_CANCELLED,
                'reject_cancellation' => $this->rejectCancellationRequest($lockedOrder),
            };

            if (in_array($action, ['complete', 'cancel'], true)) {
                $items = $lockedOrder->items()
                    ->orderBy('product_variant_id')
                    ->get();

                if ($items->isEmpty()) {
                    throw ValidationException::withMessages([
                        'status' => 'Нельзя финализировать заказ без товарных позиций.',
                    ]);
                }

                $variants = ProductVariant::withTrashed()
                    ->whereIn('id', $items->pluck('product_variant_id')->unique())
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($items as $item) {
                    $variant = $variants->get($item->product_variant_id);

                    if (! $variant) {
                        throw ValidationException::withMessages([
                            'status' => "Не найден вариант товара для позиции «{$item->title}».",
                        ]);
                    }

                    if ($variant->reserved < $item->quantity) {
                        throw ValidationException::withMessages([
                            'status' => "Недостаточно зарезервированного количества товара «{$item->title}».",
                        ]);
                    }

                    if ($action === 'complete' && $variant->stock < $item->quantity) {
                        throw ValidationException::withMessages([
                            'status' => "Недостаточно складского остатка товара «{$item->title}» для завершения заказа.",
                        ]);
                    }

                    $variant->reserved -= $item->quantity;
                    if ($action === 'complete') {
                        $variant->stock -= $item->quantity;
                    }
                    $variant->save();
                }
            }

            $lockedOrder->status = $nextStatus;
            if ($action === 'complete') {
                $lockedOrder->completed_at = now();
            }
            if (in_array($action, ['cancel', 'reject_cancellation'], true)) {
                $lockedOrder->cancellation_requested_at = null;
            }
            $lockedOrder->save();
        });

        return back()->with('success', 'Этап заказа обновлён.');
    }

    private function applyFilter(Builder $query, string $filter): Builder
    {
        return match ($filter) {
            'created' => $query->where('status', Order::STATUS_NEW)
                ->whereNull('cancellation_requested_at'),
            'processing' => $query->where('status', Order::STATUS_PROCESSING)
                ->whereNull('cancellation_requested_at'),
            'ready_for_pickup' => $query->where('status', Order::STATUS_READY_FOR_PICKUP)
                ->where('delivery_method', Order::DELIVERY_PICKUP)
                ->whereNull('cancellation_requested_at'),
            'in_transit' => $query->where(function (Builder $query): void {
                $query->where('status', Order::STATUS_IN_TRANSIT)
                    ->orWhere(function (Builder $legacyQuery): void {
                        $legacyQuery->where('status', Order::STATUS_READY_FOR_PICKUP)
                            ->where('delivery_method', Order::DELIVERY_COURIER);
                    });
            })->whereNull('cancellation_requested_at'),
            'cancellation' => $query->whereIn('status', [
                Order::STATUS_NEW,
                Order::STATUS_PROCESSING,
                Order::STATUS_READY_FOR_PICKUP,
                Order::STATUS_IN_TRANSIT,
            ])->whereNotNull('cancellation_requested_at'),
            'cancelled' => $query->where('status', Order::STATUS_CANCELLED),
            'completed' => $query->where('status', Order::STATUS_COMPLETED),
            default => $query,
        };
    }

    private function requireCurrentStatus(Order $order, string $expected, string $next): string
    {
        if ($order->status !== $expected) {
            throw ValidationException::withMessages([
                'status' => 'Этот переход недоступен для текущего этапа заказа.',
            ]);
        }

        return $next;
    }

    private function requireDeliveryMethod(Order $order, string $expectedMethod): Order
    {
        if ($order->delivery_method !== $expectedMethod) {
            throw ValidationException::withMessages([
                'status' => 'Этот этап недоступен для выбранного способа доставки.',
            ]);
        }

        return $order;
    }

    private function completeOrder(Order $order): string
    {
        if ($order->delivery_method === Order::DELIVERY_PICKUP) {
            return $this->requireCurrentStatus(
                $order,
                Order::STATUS_READY_FOR_PICKUP,
                Order::STATUS_COMPLETED
            );
        }

        if ($order->delivery_method === Order::DELIVERY_COURIER) {
            if ($order->status === Order::STATUS_READY_FOR_PICKUP) {
                return Order::STATUS_COMPLETED;
            }

            return $this->requireCurrentStatus(
                $order,
                Order::STATUS_IN_TRANSIT,
                Order::STATUS_COMPLETED
            );
        }

        throw ValidationException::withMessages([
            'status' => 'Невозможно завершить заказ с неизвестным способом доставки.',
        ]);
    }

    private function rejectCancellationRequest(Order $order): string
    {
        if ($order->cancellation_requested_at === null) {
            throw ValidationException::withMessages([
                'status' => 'У заказа нет ожидающего запроса на отмену.',
            ]);
        }

        return $order->status;
    }
}
