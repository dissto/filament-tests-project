<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($count = 1): void
    {
        $user = User::inRandomOrder()->first();

        Post::factory($count)
            ->for($user ?? User::factory(), 'author')
            ->create();

        $this->callWith(CommentSeeder::class, ['count' => 100]);
    }
}
