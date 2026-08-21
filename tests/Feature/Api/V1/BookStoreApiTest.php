<?php

namespace Tests\Feature\Api\V1;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookStoreApiTest extends TestCase
{
    use RefreshDatabase;

    private function validData(array $overrides = []): array
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        return array_merge([
            'title' => 'テストタイトル',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '2024-01-01',
            'genres' => [$genre->id],
            'description' => 'テスト説明',
            'image_url' => 'https://example.com/image.png',
            'user_id' => $user->id,
        ], $overrides);
    }

    /** 正常系:正しいデータで書籍を登録できる(201、ジャンル紐付け含む) */
    public function test_can_store_book_with_valid_data(): void
    {
        $genre = Genre::factory()->create();
        $data = $this->validData(['genres' => [$genre->id]]);

        $response = $this->postJson('/api/v1/books', $data);

        $response->assertStatus(201);
        $response->assertJsonPath('data.title', 'テストタイトル');
        $this->assertDatabaseHas('books', ['title' => 'テストタイトル']);
        $book = Book::first();
        $this->assertDatabaseHas('book_genres', ['book_id' => $book->id, 'genre_id' => $genre->id]);
    }

    /** 異常系:タイトルが未入力だと422+日本語エラーメッセージが返る */
    public function test_store_fails_when_title_is_missing(): void
    {
        $response = $this->postJson('/api/v1/books', $this->validData(['title' => '']));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('title');
        $response->assertJsonFragment(['title' => ['タイトルの入力は必須です。']]);
    }

    /** 異常系:著者名が未入力 */
    public function test_store_fails_when_author_is_missing(): void
    {
        $response = $this->postJson('/api/v1/books', $this->validData(['author' => '']));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('author');
    }

    /** 異常系:ISBNが桁数不正 */
    public function test_store_fails_when_isbn_has_invalid_length(): void
    {
        $response = $this->postJson('/api/v1/books', $this->validData(['isbn' => '12345']));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('isbn');
    }

    /** 異常系:ISBNが重複している */
    public function test_store_fails_when_isbn_already_exists(): void
    {
        Book::factory()->create(['isbn' => '1234567890123']);

        $response = $this->postJson('/api/v1/books', $this->validData(['isbn' => '1234567890123']));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('isbn');
    }

    /** 異常系:出版日が未入力 */
    public function test_store_fails_when_published_date_is_missing(): void
    {
        $response = $this->postJson('/api/v1/books', $this->validData(['published_date' => '']));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('published_date');
    }

    /** 異常系:ジャンルが未選択(空配列) */
    public function test_store_fails_when_genres_is_empty(): void
    {
        $response = $this->postJson('/api/v1/books', $this->validData(['genres' => []]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('genres');
    }

    /** 異常系:user_idが未指定 */
    public function test_store_fails_when_user_id_is_missing(): void
    {
        $data = $this->validData();
        unset($data['user_id']);

        $response = $this->postJson('/api/v1/books', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('user_id');
    }

    /** 異常系:存在しないuser_idを指定 */
    public function test_store_fails_when_user_id_does_not_exist(): void
    {
        $response = $this->postJson('/api/v1/books', $this->validData(['user_id' => 99999]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('user_id');
    }
}