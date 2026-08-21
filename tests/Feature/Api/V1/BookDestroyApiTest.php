<?php

namespace Tests\Feature\Api\V1;

use App\Models\Book;
use App\Models\Favorite;
use App\Models\Genre;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookDestroyApiTest extends TestCase
{
    use RefreshDatabase;

    /** 正常系:書籍を削除できる(204) */
    public function test_can_delete_book(): void
    {
        $book = Book::factory()->create();

        $response = $this->deleteJson("/api/v1/books/{$book->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    /** 存在しない書籍IDを指定すると、404エラーが返る */
    public function test_returns_404_for_nonexistent_book(): void
    {
        $response = $this->deleteJson('/api/v1/books/99999');

        $response->assertStatus(404);
    }

    /** 書籍を削除すると、関連するレビュー・お気に入り・ジャンル紐付けも連動して削除される */
    public function test_deleting_book_also_deletes_related_data(): void
    {
        $book = Book::factory()->create();
        $review = Review::factory()->create(['book_id' => $book->id]);
        $favorite = Favorite::factory()->create(['book_id' => $book->id]);
        $genre = Genre::factory()->create();
        $book->genres()->attach($genre->id);

        $this->deleteJson("/api/v1/books/{$book->id}");

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
        $this->assertDatabaseMissing('favorites', ['id' => $favorite->id]);
        $this->assertDatabaseMissing('book_genres', ['book_id' => $book->id, 'genre_id' => $genre->id]);
        $this->assertDatabaseHas('genres', ['id' => $genre->id]);
    }
}