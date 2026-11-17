<?php

namespace Tests\Feature;

use App\Exceptions\CartOrderAvailabilityException;
use App\Services\OrderService;
use Mockery\MockInterface;
use Tests\TestCase;

class OrderUnavailableRedirectTest extends TestCase
{
    public function test_unavailable_quantity_during_checkout_redirects_to_cart_without_a_cart_link(): void
    {
        $this->mock(OrderService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('createFromCart')
                ->once()
                ->andThrow(new CartOrderAvailabilityException('Тестовый товар', 2, 3));
        });

        $response = $this->post(route('orders.store'), [
            'items' => [1],
            'delivery_method' => 'pickup',
            'payment_method' => 'cash_on_delivery',
            'name' => 'Покупатель',
            'email' => 'buyer@example.com',
            'phone' => '+375291234567',
        ]);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas(
            'error',
            'Данное количество товара «Тестовый товар» недоступно к заказу. В наличии имеется 2 единиц товара.'
        );
        $response->assertSessionMissing('error_link');
        $response->assertSessionMissing('checkout_unavailable');
    }
}
