<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookStoreTest extends TestCase
{
    use RefreshDatabase;

    /** 正しいデータを入力した場合のベースを作るヘルパー */
    private function validData(array $overrides = []): array
    {
        $genre = Genre::factory()->create();

        return array_merge([
            'title' => 'テストタイトル',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '2024-01-01',
            'genres' => [$genre->id],
            'description' => 'テスト説明',
            'image_url' => 'https://example.com/image.png',
        ], $overrides);
    }

    /** 正常系:全項目正しく入力すると、書籍が登録されジャンルが紐付けられる */
    public function test_authenticated_user_can_store_book_with_valid_data(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->post('/books', $this->validData(['genres' => [$genre->id]]));

        $book = Book::first();
        $response->assertRedirect(route('books.show', $book->id));
        $response->assertSessionHas('success', '書籍を登録しました');

        $this->assertDatabaseHas('books', [
            'title' => 'テストタイトル',
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseHas('book_genres', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);
    }

    /** 異常系:未ログインユーザーは書籍登録画面にアクセスできず、ログイン画面にリダイレクトされる */
    public function test_guest_cannot_access_create_page(): void
    {
        $response = $this->get('/books/create');

        $response->assertRedirect('/login');
    }

    /** 異常系:未ログインユーザーは書籍を登録できず、ログイン画面にリダイレクトされる */
    public function test_guest_cannot_store_book(): void
    {
        $response = $this->post('/books', $this->validData());

        $response->assertRedirect('/login');
        $this->assertDatabaseCount('books', 0);
    }

    /** 異常系:タイトルが未入力 */
    public function test_store_fails_when_title_is_missing(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/books', $this->validData(['title' => '']));

        $response->assertSessionHasErrors('title');
        $this->assertDatabaseCount('books', 0);
    }

    /** 異常系:タイトルが256文字以上 */
    public function test_store_fails_when_title_exceeds_max_length(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/books', $this->validData(['title' => str_repeat('a', 256)]));

        $response->assertSessionHasErrors('title');
    }

    /** 境界値:タイトルがちょうど255文字 → 登録できる */
    public function test_store_succeeds_when_title_is_exactly_max_length(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/books', $this->validData(['title' => str_repeat('a', 255)]));

        $response->assertSessionDoesntHaveErrors('title');
        $this->assertDatabaseCount('books', 1);
    }

    /** 異常系:著者名が未入力 */
    public function test_store_fails_when_author_is_missing(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/books', $this->validData(['author' => '']));

        $response->assertSessionHasErrors('author');
    }

    /** 異常系:著者名が256文字以上 */
    public function test_store_fails_when_author_exceeds_max_length(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/books', $this->validData(['author' => str_repeat('a', 256)]));

        $response->assertSessionHasErrors('author');
    }

    /** 異常系:ISBNが未入力 */
    public function test_store_fails_when_isbn_is_missing(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/books', $this->validData(['isbn' => '']));

        $response->assertSessionHasErrors('isbn');
    }

    /** 異常系:ISBNが10桁でも13桁でもない */
    public function test_store_fails_when_isbn_has_invalid_length(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/books', $this->validData(['isbn' => '12345']));

        $response->assertSessionHasErrors('isbn');
    }

    /** 境界値:ISBNがちょうど10桁 → 登録できる */
    public function test_store_succeeds_when_isbn_is_10_digits(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/books', $this->validData(['isbn' => '1234567890']));

        $response->assertSessionDoesntHaveErrors('isbn');
        $this->assertDatabaseCount('books', 1);
    }

    /** 異常系:ISBNが重複している */
    public function test_store_fails_when_isbn_already_exists(): void
    {
        $user = User::factory()->create();
        Book::factory()->create(['isbn' => '1234567890123']);

        $response = $this->actingAs($user)->post('/books', $this->validData(['isbn' => '1234567890123']));

        $response->assertSessionHasErrors('isbn');
        $this->assertDatabaseCount('books', 1);
    }

    /** 異常系:出版日が未入力 */
    public function test_store_fails_when_published_date_is_missing(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/books', $this->validData(['published_date' => '']));

        $response->assertSessionHasErrors('published_date');
    }

    /** 異常系:出版日が不正な形式 */
    public function test_store_fails_when_published_date_is_invalid(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/books', $this->validData(['published_date' => 'not-a-date']));

        $response->assertSessionHasErrors('published_date');
    }

    /** 異常系:ジャンルが未選択(空配列) */
    public function test_store_fails_when_genres_is_empty(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/books', $this->validData(['genres' => []]));

        $response->assertSessionHasErrors('genres');
    }

    /** 異常系:存在しないジャンルIDを指定 */
    public function test_store_fails_when_genre_id_does_not_exist(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/books', $this->validData(['genres' => [99999]]));

        $response->assertSessionHasErrors('genres.0');
    }

    /** 異常系:説明文が501文字以上 */
    public function test_store_fails_when_description_exceeds_max_length(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/books', $this->validData(['description' => str_repeat('a', 501)]));

        $response->assertSessionHasErrors('description');
    }

    /** 正常系:説明文が未入力でも登録できる(nullable) */
    public function test_store_succeeds_when_description_is_empty(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/books', $this->validData(['description' => '']));

        $response->assertSessionDoesntHaveErrors('description');
        $this->assertDatabaseCount('books', 1);
    }

    /** 異常系:画像URLが不正な形式 */
    public function test_store_fails_when_image_url_is_invalid(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/books', $this->validData(['image_url' => 'not-a-url']));

        $response->assertSessionHasErrors('image_url');
    }

    /** 正常系:画像URLが未入力でも登録できる(nullable) */
    public function test_store_succeeds_when_image_url_is_empty(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/books', $this->validData(['image_url' => '']));

        $response->assertSessionDoesntHaveErrors('image_url');
        $this->assertDatabaseCount('books', 1);
    }

    /** 予期しないエラーが発生した場合、エラーメッセージを表示してリダイレクトする */
    public function test_store_handles_unexpected_exception(): void
    {
        $user = User::factory()->create();

        // Bookが保存されようとした瞬間に、わざと例外を発生させる
        Book::creating(function () {
            throw new \Exception('予期しないDBエラー');
        });

        $response = $this->actingAs($user)->post('/books', $this->validData());

        $response->assertRedirect();
        $response->assertSessionHas('error', '書籍の登録に失敗しました');
        $this->assertDatabaseCount('books', 0);
    }

    /** 認証済みユーザーは書籍登録画面を表示できる */
    public function test_authenticated_user_can_view_create_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/books/create');

        $response->assertStatus(200);
        $response->assertViewIs('books.create');
    }
}