<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewLikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userIds = User::pluck('id');

        foreach (Review::all() as $review) {
            $candidateUserIds = $userIds->reject(fn ($id) => $id === $review->user_id);
            $count = fake()->numberBetween(0, 3);
            $randomUserIds = $candidateUserIds->random($count);
            $review->likedByUsers()->syncWithoutDetaching($randomUserIds);
        }
    }
}
