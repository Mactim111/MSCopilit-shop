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
            'last_name' => 'Admin',
            'gender' => 'male',
            'birth_date' => '1985-02-12',
            'phone' => '+375291000001',
            'email' => 'admin@mail.ru',
            'email_verified_at' => now(),
            'password' => Hash::make('00000000'),
            'remember_token' => Str::random(10),
            'is_admin' => true, 
        ]);

        // Создаем Юзера 1
        User::factory()->create([
            'name' => 'Ivan',
            'last_name' => 'Ivanov',
            'gender' => 'male',
            'birth_date' => '1990-04-01',
            'phone' => '+375291000002',
            'email' => 'ivan@mail.ru',
            'email_verified_at' => now(),
            'password' => Hash::make('11111111'),
            'remember_token' => Str::random(10),
            'is_admin' => false, 
        ]);
        // Создаем Юзера 2
        User::factory()->create([
            'name' => 'Peter',
            'last_name' => 'Petrov',
            'gender' => 'male',
            'birth_date' => '1987-06-15',
            'phone' => '+375291000003',
            'email' => 'peter@mail.ru',
            'email_verified_at' => now(),
            'password' => Hash::make('11111111'),
            'remember_token' => Str::random(10),
            'is_admin' => false, 
        ]);
        // Создаем Юзера 3
        User::factory()->create([
            'name' => 'Sidor',
            'last_name' => 'Sidorov',
            'gender' => 'male',
            'birth_date' => '1982-08-21',
            'phone' => '+375291000004',
            'email' => 'sidor@mail.ru',
            'email_verified_at' => now(),
            'password' => Hash::make('11111111'),
            'remember_token' => Str::random(10),
            'is_admin' => false, 
        ]);
        // Создаем Юзера 4
        User::factory()->create([
            'name' => 'Gleb',
            'last_name' => 'Glebov',
            'gender' => 'male',
            'birth_date' => '1995-10-05',
            'phone' => '+375291000005',
            'email' => 'gleb@mail.ru',
            'email_verified_at' => now(),
            'password' => Hash::make('11111111'),
            'remember_token' => Str::random(10),
            'is_admin' => false, 
        ]);
        // Создаем Юзера 5
        User::factory()->create([
            'name' => 'Igor',
            'last_name' => 'Igorev',
            'gender' => 'male',
            'birth_date' => '1978-12-11',
            'phone' => '+375291000006',
            'email' => 'igor@mail.ru',
            'email_verified_at' => now(),
            'password' => Hash::make('11111111'),
            'remember_token' => Str::random(10),
            'is_admin' => false, 
        ]);
        // Создаем Юзера 6
        User::factory()->create([
            'name' => 'Panas',
            'last_name' => 'Panasov',
            'gender' => 'male',
            'birth_date' => '2000-01-27',
            'phone' => '+375291000007',
            'email' => 'panas@mail.ru',
            'email_verified_at' => now(),
            'password' => Hash::make('11111111'),
            'remember_token' => Str::random(10),
            'is_admin' => false, 
        ]);
        User::factory()->create([
            'name' => 'Maria',
            'last_name' => 'Ivanova',
            'gender' => 'female',
            'birth_date' => '2005-03-18',
            'phone' => '+375291000008',
            'email' => 'maria@mail.ru',
            'email_verified_at' => now(),
            'password' => Hash::make('11111111'),
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
            AdressSeeder::class,
        ]);
    }
}
