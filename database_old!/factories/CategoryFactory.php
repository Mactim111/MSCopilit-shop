<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    // старая фабрика без вложенных категорий
    // public function definition(): array
    // {
    //     $title = $this->faker->unique()->words(2, true);

    //     return [
    //         'title' => ucfirst($title),
    //         'slug' => Str::slug($title),
    //         'excerpt' => $this->faker->paragraph(2),
    //         'image' => 'https://picsum.photos/seed/' . $this->faker->uuid . '/600/600',
    //     ];
    // }

    // новая фабрика с вложенными категориями
    public function definition(): array
    {
        $title = $this->faker->unique()->words(2, true);

        return [
            'title' => ucfirst($title),
            'slug' => Str::slug($title),
            'excerpt' => $this->faker->paragraph(2),
            'image' => 'https://loremflickr.com/400/300/category?lock=' . $this->faker->unique()->numberBetween(1, 999999),
            'parent_id' => null, // ← добавили
        ];
    }

    // Фабрика для вложенных категорий
    public function child($parentId)
    {
        return $this->state(fn() => [
            'parent_id' => $parentId,
            'excerpt' => null,
            'image' => 'https://loremflickr.com/400/300/category?lock=' . $this->faker->unique()->numberBetween(1, 999999),
        ]);
    }
}
