<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Favorite;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookDestroyTest extends TestCase
{
    use RefreshDatabase;

    /** 正常系:本人が書籍を削除でき、一覧にリダイレクトされる */
    public function test_owner_can_delete_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete("/books/{$book->id}");

        $response->assertRedirect(route('books.index'));
        $response->assertSessionHas('success', '書籍を削除しました');
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    /** 認可:他人は書籍を削除できず、403になる */
    public function test_other_user_cannot_delete_book(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($otherUser)->delete("/books/{$book->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('books', ['id' => $book->id]);
    }

    /** 未ログインユーザーは書籍を削除できず、ログイン画面にリダイレクトされる */
    public function test_guest_cannot_delete_book(): void
    {
        $book = Book::factory()->create();

        $response = $this->delete("/books/{$book->id}");

        $response->assertRedirect('/login');
        $this->assertDatabaseHas('books', ['id' => $book->id]);
    }

    /** 書籍を削除すると、関連するレビューも連動して削除される */
    public function test_deleting_book_also_deletes_related_reviews(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $review = Review::factory()->create(['book_id' => $book->id]);

        $this->actingAs($user)->delete("/books/{$book->id}");

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    /** 書籍を削除すると、関連するお気に入りも連動して削除される */
    public function test_deleting_book_also_deletes_related_favorites(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $favorite = Favorite::factory()->create(['book_id' => $book->id]);

        $this->actingAs($user)->delete("/books/{$book->id}");

        $this->assertDatabaseMissing('favorites', ['id' => $favorite->id]);
    }

    /** 書籍を削除すると、ジャンルとの紐付け(中間テーブル)も連動して削除される */
    public function test_deleting_book_also_deletes_genre_associations(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $genre = Genre::factory()->create();
        $book->genres()->attach($genre->id);

        $this->actingAs($user)->delete("/books/{$book->id}");

        $this->assertDatabaseMissing('book_genres', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);
        // ジャンル自体は削除されないことも確認
        $this->assertDatabaseHas('genres', ['id' => $genre->id]);
    }

    /** 予期しないエラーが発生した場合、エラーメッセージを表示してリダイレクトする(destroy) */
    public function test_destroy_handles_unexpected_exception(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        Book::deleting(function () {
            throw new \Exception('予期しないDBエラー');
        });

        $response = $this->actingAs($user)->delete("/books/{$book->id}");

        $response->assertSessionHas('error', '書籍の削除に失敗しました');
        $this->assertDatabaseHas('books', ['id' => $book->id]);
    }
}
