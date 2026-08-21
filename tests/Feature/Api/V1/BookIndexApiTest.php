<?php

namespace Tests\Feature\Api\V1;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookIndexApiTest extends TestCase
{
    use RefreshDatabase;

    /** 書籍一覧が取得でき、ジャンル・平均評価・レビュー件数を含む */
    public function test_can_get_book_index_with_genres_and_review_stats(): void
    {
        $book = Book::factory()->create();
        $genre = Genre::factory()->create();
        $book->genres()->attach($genre->id);
        Review::factory()->create(['book_id' => $book->id, 'rating' => 4]);
        Review::factory()->create(['book_id' => $book->id, 'rating' => 5]);

        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $book->id]);
        $bookData = collect($response->json('data'))->firstWhere('id', $book->id);
        $this->assertEquals(2, $bookData['reviews_count']);
        $this->assertEquals(4.5, $bookData['reviews_avg_rating']);
        $this->assertCount(1, $bookData['genres']);
    }

    /** レビューが無い書籍は、平均評価がnullで返る */
    public function test_book_without_reviews_has_null_average_rating(): void
    {
        $book = Book::factory()->create();

        $response = $this->getJson('/api/v1/books');

        $bookData = collect($response->json('data'))->firstWhere('id', $book->id);
        $this->assertEquals(0, $bookData['reviews_count']);
        $this->assertNull($bookData['reviews_avg_rating']);
    }

    /** キーワード検索で、タイトルに部分一致する書籍のみ取得できる */
    public function test_can_search_books_by_keyword_matching_title(): void
    {
        $matchingBook = Book::factory()->create(['title' => 'Laravel入門']);
        $otherBook = Book::factory()->create(['title' => 'PHP基礎']);

        $response = $this->getJson('/api/v1/books?keyword=Laravel');

        $response->assertJsonFragment(['id' => $matchingBook->id]);
        $response->assertJsonMissing(['id' => $otherBook->id]);
    }

    /** キーワード検索で、著者名に部分一致する書籍も取得できる */
    public function test_can_search_books_by_keyword_matching_author(): void
    {
        $matchingBook = Book::factory()->create(['author' => '山田太郎']);
        $otherBook = Book::factory()->create(['author' => '鈴木花子']);

        $response = $this->getJson('/api/v1/books?keyword=山田');

        $response->assertJsonFragment(['id' => $matchingBook->id]);
        $response->assertJsonMissing(['id' => $otherBook->id]);
    }

    /** ジャンルで絞り込むと、そのジャンルに紐づく書籍のみ取得できる */
    public function test_can_filter_books_by_genre(): void
    {
        $genre = Genre::factory()->create();
        $matchingBook = Book::factory()->create();
        $matchingBook->genres()->attach($genre->id);
        $otherBook = Book::factory()->create();

        $response = $this->getJson("/api/v1/books?genre_id={$genre->id}");

        $response->assertJsonFragment(['id' => $matchingBook->id]);
        $response->assertJsonMissing(['id' => $otherBook->id]);
    }

    /** ページネーションが機能し、デフォルトで20件ずつ返る */
    public function test_book_index_is_paginated_by_default_20(): void
    {
        Book::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200);
        $this->assertCount(20, $response->json('data'));
    }

    /** per_page パラメータで、1ページの件数を変更できる */
    public function test_can_change_per_page_with_query_parameter(): void
    {
        Book::factory()->count(10)->create();

        $response = $this->getJson('/api/v1/books?per_page=5');

        $this->assertCount(5, $response->json('data'));
    }

    /** 異常系:per_pageが51以上だとバリデーションエラーになる */
    public function test_index_fails_when_per_page_exceeds_max(): void
    {
        $response = $this->getJson('/api/v1/books?per_page=51');

        $response->assertStatus(422);
    }

    /** 異常系:存在しないジャンルIDを指定するとバリデーションエラーになる */
    public function test_index_fails_when_genre_id_does_not_exist(): void
    {
        $response = $this->getJson('/api/v1/books?genre_id=99999');

        $response->assertStatus(422);
    }
}
