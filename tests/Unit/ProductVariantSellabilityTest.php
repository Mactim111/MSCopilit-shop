<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductVariant;
use Tests\TestCase;

class ProductVariantSellabilityTest extends TestCase
{
    public function test_variant_is_sellable_only_when_variant_and_parent_are_active_and_stock_is_available(): void
    {
        $product = new Product(['is_active' => true]);
        $variant = new ProductVariant([
            'is_active' => true,
            'stock' => 10,
            'reserved' => 4,
        ]);
        $variant->setRelation('product', $product);

        $this->assertTrue($variant->isActiveForSale());
        $this->assertTrue($variant->isSellable());

        $product->is_active = false;
        $this->assertFalse($variant->isActiveForSale());
        $this->assertFalse($variant->isSellable());

        $product->is_active = true;
        $variant->is_active = false;
        $this->assertFalse($variant->isSellable());

        $variant->is_active = true;
        $variant->reserved = 10;
        $this->assertFalse($variant->isSellable());
    }
}
