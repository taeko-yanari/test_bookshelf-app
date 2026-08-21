<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Favorite;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    /** 書籍は、それを登録したユーザーに属している(belongsTo) */
    public function test_book_belongs_to_a_user(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $book->user);
        $this->assertEquals($user->id, $book->user->id);
    }

    /** 書籍は、複数のレビューを持つ(hasMany) */
    public function test_book_has_many_reviews(): void
    {
        $book = Book::factory()->create();
        Review::factory()->count(3)->create(['book_id' => $book->id]);

        $this->assertCount(3, $book->reviews);
        $this->assertInstanceOf(Review::class, $book->reviews->first());
    }

    /** 書籍は、複数のお気に入り登録を持つ(hasMany) */
    public function test_book_has_many_favorites(): void
    {
        $book = Book::factory()->create();
        Favorite::factory()->count(3)->create(['book_id' => $book->id]);

        $this->assertCount(3, $book->favorites);
        $this->assertInstanceOf(Favorite::class, $book->favorites->first());
    }

    /** 書籍は、複数のジャンルを持つ(belongsToMany) */
    public function test_book_belongs_to_many_genres(): void
    {
        $book = Book::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $book->genres()->attach($genres->pluck('id'));

        $this->assertCount(2, $book->genres);
        $this->assertInstanceOf(Genre::class, $book->genres->first());

        // 中間テーブル(book_genres)にも正しくレコードが作られているか確認
        foreach ($genres as $genre) {
            $this->assertDatabaseHas('book_genres', [
                'book_id' => $book->id,
                'genre_id' => $genre->id,
            ]);
        }
    }
}
