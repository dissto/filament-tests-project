<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($count = 1): void
    {
        $posts = Post::inRandomOrder()->take($count)->get();
        $users = User::inRandomOrder()->take($count)->get();

        Comment::factory($count)->make()->each(function ($comment) use ($posts, $users) {
            $comment->post()->associate($posts->random());
            $comment->author()->associate($users->random());
            $comment->save();
        });

    }
}
