<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Favorite;
use App\Models\Genre;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookIndexShowTest extends TestCase
{
    use RefreshDatabase;

    /** ゲストも書籍一覧を閲覧できる */
    public function test_guest_can_view_book_index(): void
    {
        Book::factory()->count(3)->create();

        $response = $this->get('/books');

        $response->assertStatus(200);
        $response->assertViewIs('books.index');
    }

    /** 書籍一覧はページネーションされ、1ページに10件表示される */
    public function test_book_index_is_paginated_by_10(): void
    {
        Book::factory()->count(15)->create();

        $response = $this->get('/books');

        $response->assertStatus(200);
        $response->assertViewHas('books', function ($books) {
            return $books->count() === 10;
        });
    }

    /** 書籍一覧は新着順(作成日時の降順)で表示される */
    public function test_book_index_is_ordered_by_latest(): void
    {
        $oldBook = Book::factory()->create(['created_at' => now()->subDays(2)]);
        $newBook = Book::factory()->create(['created_at' => now()]);

        $response = $this->get('/books');

        $books = $response->viewData('books');
        $this->assertEquals($newBook->id, $books->first()->id);
    }

    /** ゲストも書籍詳細を閲覧できる */
    public function test_guest_can_view_book_show(): void
    {
        $book = Book::factory()->create();

        $response = $this->get("/books/{$book->id}");

        $response->assertStatus(200);
        $response->assertViewIs('books.show');
        $response->assertViewHas('book', function ($viewBook) use ($book) {
            return $viewBook->id === $book->id;
        });
    }

    /** 書籍詳細画面で、関連するジャンル・レビュー・お気に入りが読み込まれている */
    public function test_book_show_loads_genres_reviews_and_favorites(): void
    {
        $book = Book::factory()->create();
        $genre = Genre::factory()->create();
        $book->genres()->attach($genre->id);
        Review::factory()->create(['book_id' => $book->id]);
        Favorite::factory()->create(['book_id' => $book->id]);

        $response = $this->get("/books/{$book->id}");

        $response->assertStatus(200);
        $viewBook = $response->viewData('book');
        $this->assertTrue($viewBook->relationLoaded('genres'));
        $this->assertTrue($viewBook->relationLoaded('reviews'));
        $this->assertTrue($viewBook->relationLoaded('favorites'));
        $this->assertCount(1, $viewBook->genres);
        $this->assertCount(1, $viewBook->reviews);
        $this->assertCount(1, $viewBook->favorites);
    }

    /** 存在しない書籍IDにアクセスすると404になる */
    public function test_show_returns_404_for_nonexistent_book(): void
    {
        $response = $this->get('/books/99999');

        $response->assertStatus(404);
    }
}
