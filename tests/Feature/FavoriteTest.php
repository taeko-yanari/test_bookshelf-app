<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    // ============ index ============

    /** 認証済みユーザーは、自分がお気に入り登録した書籍のみ一覧で見られる */
    public function test_authenticated_user_can_view_own_favorite_books(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $myFavoriteBook = Book::factory()->create();
        Favorite::factory()->create(['user_id' => $user->id, 'book_id' => $myFavoriteBook->id]);

        $otherFavoriteBook = Book::factory()->create();
        Favorite::factory()->create(['user_id' => $otherUser->id, 'book_id' => $otherFavoriteBook->id]);

        $response = $this->actingAs($user)->get('/favorites');

        $response->assertStatus(200);
        $books = $response->viewData('books');
        $this->assertCount(1, $books);
        $this->assertEquals($myFavoriteBook->id, $books->first()->id);
    }

    /** お気に入り一覧はページネーションされ、1ページに10件表示される */
    public function test_favorite_index_is_paginated_by_10(): void
    {
        $user = User::factory()->create();
        $books = Book::factory()->count(15)->create();
        foreach ($books as $book) {
            Favorite::factory()->create(['user_id' => $user->id, 'book_id' => $book->id]);
        }

        $response = $this->actingAs($user)->get('/favorites');

        $response->assertViewHas('books', function ($books) {
            return $books->count() === 10;
        });
    }

    /** 未ログインユーザーはお気に入り一覧にアクセスできず、ログイン画面にリダイレクトされる */
    public function test_guest_cannot_view_favorite_index(): void
    {
        $response = $this->get('/favorites');

        $response->assertRedirect('/login');
    }

    // ============ toggle ============

    /** 正常系:未登録の書籍をトグルすると、お気に入りに追加される */
    public function test_toggling_unfavorited_book_adds_it_to_favorites(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post("/favorites/{$book->id}");

        $response->assertRedirect(route('books.show', $book->id));
        $response->assertSessionMissing('success');
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    /** 正常系:同じ書籍を2回トグルすると、追加→解除の順に切り替わる */
    public function test_toggling_same_book_twice_adds_then_removes_favorite(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // 1回目:追加される
        $this->actingAs($user)->post("/favorites/{$book->id}");
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        // 2回目:解除される
        $this->actingAs($user)->post("/favorites/{$book->id}");
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    /** 未ログインユーザーはお気に入りをトグルできず、ログイン画面にリダイレクトされる */
    public function test_guest_cannot_toggle_favorite(): void
    {
        $book = Book::factory()->create();

        $response = $this->post("/favorites/{$book->id}");

        $response->assertRedirect('/login');
        $this->assertDatabaseCount('favorites', 0);
    }

    /** 予期しないエラーが発生した場合、エラーメッセージを表示してリダイレクトする(お気に入り追加時) */
    public function test_toggle_handles_unexpected_exception_on_create(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        Favorite::creating(function () {
            throw new \Exception('予期しないDBエラー');
        });

        $response = $this->actingAs($user)->post("/favorites/{$book->id}");

        $response->assertSessionHas('error', 'お気に入りに登録できませんでした');
    }

    /** 予期しないエラーが発生した場合、エラーメッセージを表示してリダイレクトする(お気に入り解除時) */
    public function test_toggle_handles_unexpected_exception_on_delete(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        Favorite::factory()->create(['user_id' => $user->id, 'book_id' => $book->id]);

        Favorite::deleting(function () {
            throw new \Exception('予期しないDBエラー');
        });

        $response = $this->actingAs($user)->post("/favorites/{$book->id}");

        $response->assertSessionHas('error', 'お気に入りを解除できませんでした');
    }
}
