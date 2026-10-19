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
            'gender' => 'male',
            'birth_date' => '1990-01-01',
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
            'birth_date' => '1995-08-09',
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
            'birth_date' => '1990-01-01',
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
            'birth_date' => '1992-05-15',
            'gender' => 'male',
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
            'birth_date' => '1988-09-03',
            'gender' => 'male',
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
            'birth_date' => '1997-03-11',
            'gender' => 'male',
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
            'birth_date' => '2090-06-17',
            'gender' => 'male', 
            'email' => 'panas@mail.ru',
            'email_verified_at' => now(),
            'password' => Hash::make('11111111'),
            'remember_token' => Str::random(10),
            'is_admin' => false, 
        ]);
        // Создаем Юзера 7
        User::factory()->create([
            'name' => 'Maria',
            'last_name' => 'Marinina',  
            'gender' => 'female',
            'birth_date' => '1970-11-22',
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
