<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    public function approved(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'approved_at' => now(),
            ];
        });
    }

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'post_id' => Post::factory(),
            'content' => fake()->paragraphs(mt_rand(1, 7), true),
            'approved_at' => fake()->boolean(69) ? fake()->dateTimeBetween('-1 year') : null,
        ];
    }
}
