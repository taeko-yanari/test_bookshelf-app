<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Favorite;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** ユーザーは、複数の書籍を登録できる(hasMany) */
    public function test_user_has_many_books(): void
    {
        $user = User::factory()->create();
        Book::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->books);
        $this->assertInstanceOf(Book::class, $user->books->first());
    }

    /** ユーザーは、複数のレビューを投稿できる(hasMany) */
    public function test_user_has_many_reviews(): void
    {
        $user = User::factory()->create();
        Review::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->reviews);
        $this->assertInstanceOf(Review::class, $user->reviews->first());
    }

    /** ユーザーは、複数のお気に入りレコードを持つ(hasMany) */
    public function test_user_has_many_favorites(): void
    {
        $user = User::factory()->create();
        Favorite::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->favorites);
        $this->assertInstanceOf(Favorite::class, $user->favorites->first());
    }

    /** ユーザーは、複数の書籍をお気に入り登録できる(belongsToMany) */
    public function test_user_belongs_to_many_favorite_books(): void
    {
        $user = User::factory()->create();
        $books = Book::factory()->count(2)->create();

        $user->favoriteBooks()->attach($books->pluck('id'));

        $this->assertCount(2, $user->favoriteBooks);
        $this->assertInstanceOf(Book::class, $user->favoriteBooks->first());

        foreach ($books as $book) {
            $this->assertDatabaseHas('favorites', [
                'user_id' => $user->id,
                'book_id' => $book->id,
            ]);
        }
    }

    /** ユーザーは、複数のレビューにいいねできる(belongsToMany) */
    public function test_user_belongs_to_many_liked_reviews(): void
    {
        $user = User::factory()->create();
        $reviews = Review::factory()->count(2)->create();

        $user->likedReviews()->attach($reviews->pluck('id'));

        $this->assertCount(2, $user->likedReviews);
        $this->assertInstanceOf(Review::class, $user->likedReviews->first());

        foreach ($reviews as $review) {
            $this->assertDatabaseHas('review_likes', [
                'user_id' => $user->id,
                'review_id' => $review->id,
            ]);
        }
    }
}
