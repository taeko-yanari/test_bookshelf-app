<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    /** お気に入りは、登録したユーザーに属している(belongsTo) */
    public function test_favorite_belongs_to_a_user(): void
    {
        $user = User::factory()->create();
        $favorite = Favorite::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $favorite->user);
        $this->assertEquals($user->id, $favorite->user->id);
    }

    /** お気に入りは、対象の書籍に属している(belongsTo) */
    public function test_favorite_belongs_to_a_book(): void
    {
        $book = Book::factory()->create();
        $favorite = Favorite::factory()->create(['book_id' => $book->id]);

        $this->assertInstanceOf(Book::class, $favorite->book);
        $this->assertEquals($book->id, $favorite->book->id);
    }
}
