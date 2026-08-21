<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookAuthorizationDetailTest extends TestCase
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

    /** 存在しない書籍IDへの更新リクエストは、認可チェックより先に404になる */
    public function test_update_nonexistent_book_returns_404(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/books/99999', $this->validData());

        $response->assertStatus(404);
    }

    /** 存在しない書籍IDへの削除リクエストは、認可チェックより先に404になる */
    public function test_delete_nonexistent_book_returns_404(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->delete('/books/99999');

        $response->assertStatus(404);
    }

    /** 他人が無効なデータで更新しようとした場合、403ではなくバリデーションエラーになる(実行順序の確認) */
    public function test_other_user_with_invalid_data_gets_validation_error_not_403(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($otherUser)->put(
            "/books/{$book->id}",
            $this->validData(['title' => ''])
        );

        $response->assertSessionHasErrors('title');
        $response->assertStatus(302);
    }

    /** 他人が有効なデータで更新しようとした場合、バリデーションは通るが認可(403)で拒否される */
    public function test_other_user_with_valid_data_gets_403(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($otherUser)->put(
            "/books/{$book->id}",
            $this->validData()
        );

        $response->assertSessionDoesntHaveErrors();
        $response->assertStatus(403);
    }
}
