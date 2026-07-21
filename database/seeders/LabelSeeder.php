<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LabelSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('labels')->insert([
            [
                'title' => 'Суперцена',
                'slug' => 'supercena',
                'component' => 'label-supercena', // имя Blade-компонента
                'position' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Акция',
                'slug' => 'akciya',
                'component' => 'label-akciya',
                'position' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Хит продаж',
                'slug' => 'bestseller',
                'component' => 'label-bestseller',
                'position' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Новинка',
                'slug' => 'novinka',
                'component' => 'label-novinka',
                'position' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
