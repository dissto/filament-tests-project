<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $users = User::factory(10)->create();
        $users->push($testUser);

        $posts = Post::factory(50)
            ->make()
            ->each(function ($post) use ($users) {
                $post->author()->associate($users->random());
                $post->save();
            });

        Comment::factory(100)
            ->make()
            ->each(function ($comment) use ($posts, $users) {
                $comment->post()->associate($posts->random());
                $comment->author()->associate($users->random());
                $comment->save();
            });
    }
}
