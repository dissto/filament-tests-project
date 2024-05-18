<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $title = str(fake()->sentence(mt_rand(3, 10)))->title(),
            'slug' => str($title)->slug(),
            'content' => fake()->paragraphs(mt_rand(3, 10), true),
            'published_at' => fake()->optional()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
