<?php

namespace Tests\Feature\Api\V1;

use App\Models\Book;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookUpdateApiTest extends TestCase
{
    use RefreshDatabase;

    private function validData(array $overrides = []): array
    {
        $genre = Genre::factory()->create();

        return array_merge([
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'isbn' => '9876543210987',
            'published_date' => '2024-06-01',
            'genres' => [$genre->id],
            'description' => '更新後説明',
            'image_url' => 'https://example.com/updated.png',
        ], $overrides);
    }

    /** 正常系:正しいデータで書籍を更新できる(200) */
    public function test_can_update_book_with_valid_data(): void
    {
        $book = Book::factory()->create();

        $response = $this->putJson("/api/v1/books/{$book->id}", $this->validData());

        $response->assertStatus(200);
        $response->assertJsonPath('data.title', '更新後タイトル');
        $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => '更新後タイトル']);
    }

    /** 存在しない書籍IDを指定すると、404エラーが返る */
    public function test_returns_404_for_nonexistent_book(): void
    {
        $response = $this->putJson('/api/v1/books/99999', $this->validData());

        $response->assertStatus(404);
    }

    /** 正常系:ISBNを変更せずに更新しても、unique制約でエラーにならない(自身は除外される) */
    public function test_can_update_book_keeping_same_isbn(): void
    {
        $book = Book::factory()->create(['isbn' => '1111111111111']);

        $response = $this->putJson("/api/v1/books/{$book->id}", $this->validData(['isbn' => '1111111111111']));

        $response->assertStatus(200);
    }

    /** 異常系:他の書籍が使っているISBNには変更できない */
    public function test_update_fails_when_isbn_belongs_to_another_book(): void
    {
        Book::factory()->create(['isbn' => '2222222222222']);
        $book = Book::factory()->create(['isbn' => '1111111111111']);

        $response = $this->putJson("/api/v1/books/{$book->id}", $this->validData(['isbn' => '2222222222222']));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('isbn');
    }

    /** 異常系:タイトルが未入力 */
    public function test_update_fails_when_title_is_missing(): void
    {
        $book = Book::factory()->create();

        $response = $this->putJson("/api/v1/books/{$book->id}", $this->validData(['title' => '']));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('title');
    }

    /** 異常系:ジャンルが未選択(空配列) */
    public function test_update_fails_when_genres_is_empty(): void
    {
        $book = Book::factory()->create();

        $response = $this->putJson("/api/v1/books/{$book->id}", $this->validData(['genres' => []]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('genres');
    }

    /** 正常系:更新時にジャンルの紐付けが正しく差し替えられる(sync) */
    public function test_update_replaces_genre_associations(): void
    {
        $book = Book::factory()->create();
        $oldGenre = Genre::factory()->create();
        $newGenre = Genre::factory()->create();
        $book->genres()->attach($oldGenre->id);

        $response = $this->putJson("/api/v1/books/{$book->id}", $this->validData(['genres' => [$newGenre->id]]));

        $response->assertStatus(200);
        $this->assertDatabaseMissing('book_genres', ['book_id' => $book->id, 'genre_id' => $oldGenre->id]);
        $this->assertDatabaseHas('book_genres', ['book_id' => $book->id, 'genre_id' => $newGenre->id]);
    }
}