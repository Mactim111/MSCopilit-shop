<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //  User::factory(10)->create();

//        User::factory()->create([
//            'name' => 'Test User',
//            'email' => 'test@example.com',
//        ]);
        // Создаем Админа
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@mail.ru',
            'email_verified_at' => now(),
            'password' => Hash::make('00000000'),
            'remember_token' => Str::random(10),
            'is_admin' => true, 
        ]);

        // Создаем Юзера
        User::factory()->create([
            'name' => 'Ivan',
            'email' => 'ivan@mail.ru',
            'email_verified_at' => now(),
            'password' => Hash::make('00000000'),
            'remember_token' => Str::random(10),
            'is_admin' => false, 
        ]);

        $this->call([
            CategorySeeder::class,
            BrandSeeder::class,
            ProductSeeder::class,
            // CategoryProductSeeder::class,
            ProductVariantSeeder::class,
            LabelSeeder::class,
            ProductVariantLabelSeeder::class,
            ProductVariantImagesSeeder::class,
            PropertiesSeeder::class,
            ProductPropertiesSeeder::class,
            PropertyOptionsSeeder::class,
            ProductVariantPropertyOptionsSeeder::class,
            FrontendImagesSeeder::class,
            PageSectionSeeder::class,
        ]);
    }
}
