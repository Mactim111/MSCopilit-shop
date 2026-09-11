<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class AdressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('addresses')->insert([
            [
                'user_id' => 2,
                'label' => 'Дом',
                'address_line' => 'Ленинградская, 115, 191',
                'country' => 'Беларусь', 
                'city' => 'Брест',
                'state' => 'Brestskaya',
                'zip' => 224014,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 3,
                'label' => 'Дом',
                'address_line' => 'Орловская, 52, 119',
                'country' => 'Беларусь', 
                'city' => 'Минск',
                'state' => 'Minskaya',
                'zip' => 221028,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 4,
                'label' => 'Дом',
                'address_line' => 'Ленина, 11, 1',
                'country' => 'Беларусь', 
                'city' => 'Брест',
                'state' => 'Brestskaya',
                'zip' => 224028,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 5,
                'label' => 'Дом',
                'address_line' => 'Московская, 226, 78',
                'country' => 'Беларусь', 
                'city' => 'Минск',
                'state' => 'Minskaya',
                'zip' => 221015,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 6,
                'label' => 'Дом',
                'address_line' => 'Жукова, 14, 55',
                'country' => 'Беларусь', 
                'city' => 'Витебск',
                'state' => 'Vitebskaya',
                'zip' => 226014,
                'created_at' => now(),
                'updated_at' => now(),
            ],       
        ]);
    }
}
