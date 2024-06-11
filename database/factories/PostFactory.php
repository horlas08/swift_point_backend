<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Random\RandomException;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     * @throws RandomException
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->title,
            'image' => $this->faker->imageUrl,
            'content' => $this->faker->sentence(random_int(100, 200)),
            'is_banner' => $this->faker->boolean(50)
        ];
    }
}
