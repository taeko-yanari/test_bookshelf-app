<?php

namespace Tests\Feature\Api\V1;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookShowApiTest extends TestCase
{
    use RefreshDatabase;

    /** 書籍詳細が取得でき、ジャンルとレビュー(投稿者名含む)を含む */
    public function test_can_get_book_detail_with_genres_and_reviews(): void
    {
        $book = Book::factory()->create();
        $genre = Genre::factory()->create();
        $book->genres()->attach($genre->id);
        $reviewer = User::factory()->create(['name' => 'レビュー太郎']);
        Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $reviewer->id,
            'rating' => 4,
            'comment' => '良かったです',
        ]);

        $response = $this->getJson("/api/v1/books/{$book->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $book->id);
        $response->assertJsonCount(1, 'data.genres');
        $response->assertJsonPath('data.reviews.0.user_name', 'レビュー太郎');
        $response->assertJsonPath('data.reviews.0.rating', 4);
        $response->assertJsonPath('data.reviews.0.comment', '良かったです');
    }

    /** 存在しない書籍IDを指定すると、404エラーが返る */
    public function test_returns_404_for_nonexistent_book(): void
    {
        $response = $this->getJson('/api/v1/books/99999');

        $response->assertStatus(404);
    }
}
