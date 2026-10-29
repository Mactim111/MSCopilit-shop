<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DemoOrdersSeeder extends Seeder
{
    private const CUSTOMER_EMAILS = [
        'ivan@mail.ru',
        'peter@mail.ru',
        'sidor@mail.ru',
        'gleb@mail.ru',
        'igor@mail.ru',
        'panas@mail.ru',
        'maria@mail.ru',
    ];

    public function run(): void
    {
        $customers = User::query()
            ->whereIn('email', self::CUSTOMER_EMAILS)
            ->with('addresses')
            ->get()
            ->keyBy('email');

        if ($customers->count() !== count(self::CUSTOMER_EMAILS)) {
            throw new RuntimeException('Demo orders require the seven seeded customer accounts.');
        }

        $quantityPerVariant = $customers->count();

        DB::transaction(function () use ($customers, $quantityPerVariant): void {
            $variants = ProductVariant::query()
                ->whereIn('id', [1, 2, 3])
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($variants->count() !== 3) {
                throw new RuntimeException('Demo orders require product variants with IDs 1, 2, and 3.');
            }

            $completedVariants = $variants->only([1, 2]);
            $cancelledVariant = $variants->get(3);

            foreach ($completedVariants as $variant) {
                if ($variant->stock - $variant->reserved < $quantityPerVariant) {
                    throw new RuntimeException(
                        "Variant {$variant->id} does not have enough available stock for demo orders."
                    );
                }
            }

            foreach ($customers as $customer) {
                foreach ($completedVariants as $variant) {
                    $address = $customer->addresses->first();

                    $order = Order::create([
                        'user_id' => $customer->id,
                        'address_id' => $address?->id,
                        'name' => $customer->name,
                        'last_name' => $customer->last_name,
                        'email' => $customer->email,
                        'phone' => $customer->phone,
                        'address' => $address?->fullAddress(),
                        'total' => $variant->price,
                        'status' => Order::STATUS_COMPLETED,
                        'payment_status' => Order::PAYMENT_PAID,
                    ]);

                    $order->items()->create([
                        'product_variant_id' => $variant->id,
                        'title' => $variant->title,
                        'price' => $variant->price,
                        'quantity' => 1,
                        'subtotal' => $variant->price,
                    ]);
                }

                $address = $customer->addresses->first();
                $cancelledOrder = Order::create([
                    'user_id' => $customer->id,
                    'address_id' => $address?->id,
                    'name' => $customer->name,
                    'last_name' => $customer->last_name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'address' => $address?->fullAddress(),
                    'total' => $cancelledVariant->price,
                    'status' => Order::STATUS_CANCELLED,
                    'payment_status' => Order::PAYMENT_UNRECORDED,
                ]);

                $cancelledOrder->items()->create([
                    'product_variant_id' => $cancelledVariant->id,
                    'title' => $cancelledVariant->title,
                    'price' => $cancelledVariant->price,
                    'quantity' => 1,
                    'subtotal' => $cancelledVariant->price,
                ]);
            }

            foreach ($completedVariants as $variant) {
                $variant->decrement('stock', $quantityPerVariant);
            }
        });

        $this->command?->info('Created 14 completed and 7 cancelled demo orders; deducted 7 units from variants 1 and 2.');
    }
}
