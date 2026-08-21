<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookEditUpdateTest extends TestCase
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

    /** 認証+本人は編集画面を表示できる */
    public function test_owner_can_view_edit_page(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/books/{$book->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('books.edit');
    }

    /** 認証+他人は編集画面にアクセスできず、403になる */
    public function test_other_user_cannot_view_edit_page(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($otherUser)->get("/books/{$book->id}/edit");

        $response->assertStatus(403);
    }

    /** 未ログインユーザーは編集画面にアクセスできず、ログイン画面にリダイレクトされる */
    public function test_guest_cannot_view_edit_page(): void
    {
        $book = Book::factory()->create();

        $response = $this->get("/books/{$book->id}/edit");

        $response->assertRedirect('/login');
    }

    /** 正常系:本人が正しいデータで更新できる */
    public function test_owner_can_update_book_with_valid_data(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put("/books/{$book->id}", $this->validData());

        $response->assertRedirect(route('books.show', $book->id));
        $response->assertSessionHas('success', '書籍を更新しました');
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後タイトル',
        ]);
    }

    /** 認可:他人は書籍を更新できず、403になる */
    public function test_other_user_cannot_update_book(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id, 'title' => '元のタイトル']);

        $response = $this->actingAs($otherUser)->put("/books/{$book->id}", $this->validData());

        $response->assertStatus(403);
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '元のタイトル',
        ]);
    }

    /** 未ログインユーザーは書籍を更新できず、ログイン画面にリダイレクトされる */
    public function test_guest_cannot_update_book(): void
    {
        $book = Book::factory()->create(['title' => '元のタイトル']);

        $response = $this->put("/books/{$book->id}", $this->validData());

        $response->assertRedirect('/login');
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '元のタイトル',
        ]);
    }

    /** 正常系:ISBNを変更せずに更新しても、unique制約でエラーにならない(自身は除外される) */
    public function test_owner_can_update_book_keeping_same_isbn(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id, 'isbn' => '1111111111111']);

        $response = $this->actingAs($user)->put("/books/{$book->id}", $this->validData(['isbn' => '1111111111111']));

        $response->assertSessionDoesntHaveErrors('isbn');
        $response->assertRedirect(route('books.show', $book->id));
    }

    /** 異常系:他の書籍が使っているISBNには変更できない */
    public function test_update_fails_when_isbn_belongs_to_another_book(): void
    {
        $user = User::factory()->create();
        Book::factory()->create(['isbn' => '2222222222222']);
        $book = Book::factory()->create(['user_id' => $user->id, 'isbn' => '1111111111111']);

        $response = $this->actingAs($user)->put("/books/{$book->id}", $this->validData(['isbn' => '2222222222222']));

        $response->assertSessionHasErrors('isbn');
    }

    /** 異常系:タイトルが未入力 */
    public function test_update_fails_when_title_is_missing(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put("/books/{$book->id}", $this->validData(['title' => '']));

        $response->assertSessionHasErrors('title');
    }

    /** 異常系:ジャンルが未選択(空配列) */
    public function test_update_fails_when_genres_is_empty(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put("/books/{$book->id}", $this->validData(['genres' => []]));

        $response->assertSessionHasErrors('genres');
    }

    /** 正常系:更新時にジャンルの紐付けが正しく差し替えられる(sync) */
    public function test_update_replaces_genre_associations(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $oldGenre = Genre::factory()->create();
        $newGenre = Genre::factory()->create();
        $book->genres()->attach($oldGenre->id);

        $response = $this->actingAs($user)->put("/books/{$book->id}", $this->validData(['genres' => [$newGenre->id]]));

        $response->assertSessionDoesntHaveErrors();
        $this->assertDatabaseMissing('book_genres', [
            'book_id' => $book->id,
            'genre_id' => $oldGenre->id,
        ]);
        $this->assertDatabaseHas('book_genres', [
            'book_id' => $book->id,
            'genre_id' => $newGenre->id,
        ]);
    }

    /** 予期しないエラーが発生した場合、エラーメッセージを表示してリダイレクトする(update) */
    public function test_update_handles_unexpected_exception(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        Book::updating(function () {
            throw new \Exception('予期しないDBエラー');
        });

        $response = $this->actingAs($user)->put("/books/{$book->id}", $this->validData());

        $response->assertSessionHas('error', '書籍の更新に失敗しました');
    }
}