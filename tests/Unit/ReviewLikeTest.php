<?php

namespace Tests\Unit;

use App\Models\Review;
use App\Models\ReviewLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewLikeTest extends TestCase
{
    use RefreshDatabase;

    /** いいねは、押したユーザーに属している(belongsTo) */
    public function test_review_like_belongs_to_a_user(): void
    {
        $user = User::factory()->create();
        $reviewLike = ReviewLike::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $reviewLike->user);
        $this->assertEquals($user->id, $reviewLike->user->id);
    }

    /** いいねは、対象のレビューに属している(belongsTo) */
    public function test_review_like_belongs_to_a_review(): void
    {
        $review = Review::factory()->create();
        $reviewLike = ReviewLike::factory()->create(['review_id' => $review->id]);

        $this->assertInstanceOf(Review::class, $reviewLike->review);
        $this->assertEquals($review->id, $reviewLike->review->id);
    }
}
