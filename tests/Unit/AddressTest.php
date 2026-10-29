<?php

namespace Tests\Unit;

use App\Models\Address;
use Tests\TestCase;

class AddressTest extends TestCase
{
    public function test_full_address_includes_available_address_parts_in_order(): void
    {
        $address = new Address([
            'country' => 'Беларусь',
            'city' => 'Брест',
            'state' => 'Brestskaya',
            'zip' => '224014',
            'address_line' => 'Ленинградская, 115, 191',
        ]);

        $this->assertSame(
            'Беларусь, Брест, Brestskaya, 224014, Ленинградская, 115, 191',
            $address->fullAddress()
        );
    }

    public function test_full_address_omits_empty_optional_parts(): void
    {
        $address = new Address([
            'country' => 'Беларусь',
            'city' => 'Брест',
            'state' => null,
            'zip' => '',
            'address_line' => 'Ленинградская, 115, 191',
        ]);

        $this->assertSame(
            'Беларусь, Брест, Ленинградская, 115, 191',
            $address->fullAddress()
        );
    }
}
