<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderAdminController extends Controller
{
    public function index()
    {
        $orders = Order::latest()->paginate(20);

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load('items.product');

        return view('admin.orders.show', compact('order'));
    }

    public function transition(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', 'in:start_processing,mark_ready,complete,cancel,reject_cancellation'],
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

            $nextStatus = match ($action) {
                'start_processing' => $this->requireCurrentStatus(
                    $lockedOrder,
                    Order::STATUS_NEW,
                    Order::STATUS_PROCESSING
                ),
                'mark_ready' => $this->requireCurrentStatus(
                    $lockedOrder,
                    Order::STATUS_PROCESSING,
                    Order::STATUS_READY_FOR_PICKUP
                ),
                'complete' => $this->requireCurrentStatus(
                    $lockedOrder,
                    Order::STATUS_READY_FOR_PICKUP,
                    Order::STATUS_COMPLETED
                ),
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
            if (in_array($action, ['cancel', 'reject_cancellation'], true)) {
                $lockedOrder->cancellation_requested_at = null;
            }
            $lockedOrder->save();
        });

        return back()->with('success', 'Этап заказа обновлён.');
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
