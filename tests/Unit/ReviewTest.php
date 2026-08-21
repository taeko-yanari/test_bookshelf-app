<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Review;
use App\Models\ReviewLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    /** レビューは、投稿したユーザーに属している(belongsTo) */
    public function test_review_belongs_to_a_user(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $review->user);
        $this->assertEquals($user->id, $review->user->id);
    }

    /** レビューは、対象の書籍に属している(belongsTo) */
    public function test_review_belongs_to_a_book(): void
    {
        $book = Book::factory()->create();
        $review = Review::factory()->create(['book_id' => $book->id]);

        $this->assertInstanceOf(Book::class, $review->book);
        $this->assertEquals($book->id, $review->book->id);
    }

    /** レビューは、複数のいいねレコードを持つ(hasMany) */
    public function test_review_has_many_review_likes(): void
    {
        $review = Review::factory()->create();
        ReviewLike::factory()->count(3)->create(['review_id' => $review->id]);

        $this->assertCount(3, $review->reviewLikes);
        $this->assertInstanceOf(ReviewLike::class, $review->reviewLikes->first());
    }

    /** レビューは、いいねを押した複数のユーザーを持つ(belongsToMany) */
    public function test_review_belongs_to_many_liking_users(): void
    {
        $review = Review::factory()->create();
        $users = User::factory()->count(2)->create();

        $review->likedByUsers()->attach($users->pluck('id'));

        $this->assertCount(2, $review->likedByUsers);
        $this->assertInstanceOf(User::class, $review->likedByUsers->first());

        foreach ($users as $user) {
            $this->assertDatabaseHas('review_likes', [
                'review_id' => $review->id,
                'user_id' => $user->id,
            ]);
        }
    }
}
