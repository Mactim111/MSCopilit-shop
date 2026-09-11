<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    // доработали метод - теперь именно тут происходит «магия» списания или возврата вариантов товаров на склад, управление их остатками
    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => 'required|in:new,paid,shipped,cancelled'
        ]);

        $oldStatus = $order->status;
        $newStatus = $data['status'];

        DB::transaction(function () use ($order, $oldStatus, $newStatus) {
            $order->update(['status' => $newStatus]);

            // Логика обработки резервов и склада
            foreach ($order->items as $item) {
                $variant = $item->variant; // Убедись, что связь в OrderItem есть

                // Если заказ отменили — возвращаем из резерва в доступность
                if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
                    $variant->decrement('reserved', $item->quantity);
                }

                // Если заказ завершен/отправлен — списываем физически
                if ($newStatus === 'shipped' || $newStatus === 'paid') {
                    // Если мы перешли в этот статус из "new", значит вычитаем из резерва
                    if ($oldStatus === 'new') {
                        $variant->decrement('reserved', $item->quantity);
                    }
                    
                    // Физическое списание со склада
                    $variant->decrement('stock', $item->quantity);
                }
            }
        });

        return back()->with('success', 'Статус заказа и складские остатки обновлены');
    }
}
