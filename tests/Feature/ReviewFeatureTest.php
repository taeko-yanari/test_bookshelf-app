<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\ReviewLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewFeatureTest extends TestCase
{
    use RefreshDatabase;

    // ============ store ============

    /** 正常系:認証済みユーザーがレビューを投稿できる */
    public function test_authenticated_user_can_store_review(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post("/reviews/{$book->id}", [
            'rating' => 4,
            'comment' => 'とても面白かったです',
        ]);

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas('success', 'レビューを登録しました');
        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
        ]);
    }

    /** 未ログインユーザーはレビューを投稿できず、ログイン画面にリダイレクトされる */
    public function test_guest_cannot_store_review(): void
    {
        $book = Book::factory()->create();

        $response = $this->post("/reviews/{$book->id}", [
            'rating' => 4,
            'comment' => 'とても面白かったです',
        ]);

        $response->assertRedirect('/login');
        $this->assertDatabaseCount('reviews', 0);
    }

    /** 異常系:評価が未入力 */
    public function test_store_fails_when_rating_is_missing(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post("/reviews/{$book->id}", ['comment' => 'コメント']);

        $response->assertSessionHasErrors('rating');
    }

    /** 異常系:評価が整数でない */
    public function test_store_fails_when_rating_is_not_integer(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post("/reviews/{$book->id}", ['rating' => 'abc']);

        $response->assertSessionHasErrors('rating');
    }

    /** 異常系:評価が0(範囲外) */
    public function test_store_fails_when_rating_is_below_range(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post("/reviews/{$book->id}", ['rating' => 0]);

        $response->assertSessionHasErrors('rating');
    }

    /** 異常系:評価が6(範囲外) */
    public function test_store_fails_when_rating_is_above_range(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post("/reviews/{$book->id}", ['rating' => 6]);

        $response->assertSessionHasErrors('rating');
    }

    /** 境界値:評価が1(範囲内の下限) → 登録できる */
    public function test_store_succeeds_when_rating_is_1(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post("/reviews/{$book->id}", ['rating' => 1]);

        $response->assertSessionDoesntHaveErrors('rating');
        $this->assertDatabaseCount('reviews', 1);
    }

    /** 境界値:評価が5(範囲内の上限) → 登録できる */
    public function test_store_succeeds_when_rating_is_5(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post("/reviews/{$book->id}", ['rating' => 5]);

        $response->assertSessionDoesntHaveErrors('rating');
        $this->assertDatabaseCount('reviews', 1);
    }

    /** 異常系:コメントが256文字以上 */
    public function test_store_fails_when_comment_exceeds_max_length(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post("/reviews/{$book->id}", [
            'rating' => 3,
            'comment' => str_repeat('a', 256),
        ]);

        $response->assertSessionHasErrors('comment');
    }

    /** 正常系:コメントが未入力でも投稿できる(nullable) */
    public function test_store_succeeds_when_comment_is_empty(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post("/reviews/{$book->id}", ['rating' => 3]);

        $response->assertSessionDoesntHaveErrors('comment');
        $this->assertDatabaseCount('reviews', 1);
    }

    // ============ edit ============

    /** 認証+本人は編集画面を表示できる */
    public function test_owner_can_view_edit_page(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/reviews/{$review->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('reviews.edit');
    }

    /** 認証+他人は編集画面にアクセスできず、403になる */
    public function test_other_user_cannot_view_edit_page(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($otherUser)->get("/reviews/{$review->id}/edit");

        $response->assertStatus(403);
    }

    /** 未ログインユーザーは編集画面にアクセスできず、ログイン画面にリダイレクトされる */
    public function test_guest_cannot_view_edit_page(): void
    {
        $review = Review::factory()->create();

        $response = $this->get("/reviews/{$review->id}/edit");

        $response->assertRedirect('/login');
    }

    // ============ update ============

    /** 正常系:本人がレビューを更新できる */
    public function test_owner_can_update_review(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id, 'rating' => 3]);

        $response = $this->actingAs($user)->put("/reviews/{$review->id}", [
            'rating' => 5,
            'comment' => '更新後コメント',
        ]);

        $response->assertRedirect(route('books.show', $review->book_id));
        $response->assertSessionHas('success', 'レビューを更新しました');
        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'rating' => 5]);
    }

    /** 認可:他人はレビューを更新できず、403になる */
    public function test_other_user_cannot_update_review(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $owner->id, 'rating' => 3]);

        $response = $this->actingAs($otherUser)->put("/reviews/{$review->id}", ['rating' => 5]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'rating' => 3]);
    }

    /** 未ログインユーザーはレビューを更新できず、ログイン画面にリダイレクトされる */
    public function test_guest_cannot_update_review(): void
    {
        $review = Review::factory()->create(['rating' => 3]);

        $response = $this->put("/reviews/{$review->id}", ['rating' => 5]);

        $response->assertRedirect('/login');
        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'rating' => 3]);
    }

    /** 異常系:更新時に評価が範囲外だとエラーになる */
    public function test_update_fails_when_rating_is_out_of_range(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put("/reviews/{$review->id}", ['rating' => 6]);

        $response->assertSessionHasErrors('rating');
    }

    /** 予期しないエラーが発生した場合、エラーメッセージを表示してリダイレクトする(update) */
    public function test_update_handles_unexpected_exception(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id, 'rating' => 3]);

        Review::updating(function () {
            throw new \Exception('予期しないDBエラー');
        });

        $response = $this->actingAs($user)->put("/reviews/{$review->id}", ['rating' => 5]);

        $response->assertSessionHas('error', 'レビューの更新に失敗しました');
    }

    // ============ destroy ============

    /** 正常系:本人がレビューを削除できる */
    public function test_owner_can_delete_review(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete("/reviews/{$review->id}");

        $response->assertRedirect(route('books.show', $review->book_id));
        $response->assertSessionHas('success', 'レビューを削除しました');
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    /** 認可:他人はレビューを削除できず、403になる */
    public function test_other_user_cannot_delete_review(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($otherUser)->delete("/reviews/{$review->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }

    /** 未ログインユーザーはレビューを削除できず、ログイン画面にリダイレクトされる */
    public function test_guest_cannot_delete_review(): void
    {
        $review = Review::factory()->create();

        $response = $this->delete("/reviews/{$review->id}");

        $response->assertRedirect('/login');
        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }

    /** レビューを削除すると、関連するいいねも連動して削除される */
    public function test_deleting_review_also_deletes_related_review_likes(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);
        $reviewLike = ReviewLike::factory()->create(['review_id' => $review->id]);

        $this->actingAs($user)->delete("/reviews/{$review->id}");

        $this->assertDatabaseMissing('review_likes', ['id' => $reviewLike->id]);
    }

    /** 予期しないエラーが発生した場合、エラーメッセージを表示してリダイレクトする(store) */
    public function test_store_handles_unexpected_exception(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        Review::creating(function () {
            throw new \Exception('予期しないDBエラー');
        });

        $response = $this->actingAs($user)->post("/reviews/{$book->id}", ['rating' => 4]);

        $response->assertSessionHas('error', 'レビューの登録に失敗しました');
    }

    /** 予期しないエラーが発生した場合、エラーメッセージを表示してリダイレクトする(destroy) */
    public function test_destroy_handles_unexpected_exception(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        Review::deleting(function () {
            throw new \Exception('予期しないDBエラー');
        });

        $response = $this->actingAs($user)->delete("/reviews/{$review->id}");

        $response->assertSessionHas('error', 'レビューの削除に失敗しました');
    }
}
