<?php

namespace Tests\Feature;

use App\Models\Review;
use App\Models\ReviewLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewLikeFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** 正常系:未いいねのレビューをトグルすると、いいねが追加される */
    public function test_toggling_unliked_review_adds_a_like(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create();

        $response = $this->actingAs($user)->post("/reviews/{$review->id}/like");

        $response->assertRedirect(route('books.show', $review->book_id));
        $response->assertSessionMissing('success');
        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    /** 正常系:同じレビューを2回トグルすると、追加→解除の順に切り替わる */
    public function test_toggling_same_review_twice_adds_then_removes_like(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create();

        // 1回目:追加される
        $this->actingAs($user)->post("/reviews/{$review->id}/like");
        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);

        // 2回目:解除される
        $this->actingAs($user)->post("/reviews/{$review->id}/like");
        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    /** 未ログインユーザーはいいねをトグルできず、ログイン画面にリダイレクトされる */
    public function test_guest_cannot_toggle_like(): void
    {
        $review = Review::factory()->create();

        $response = $this->post("/reviews/{$review->id}/like");

        $response->assertRedirect('/login');
        $this->assertDatabaseCount('review_likes', 0);
    }

    /** 予期しないエラーが発生した場合、エラーメッセージを表示してリダイレクトする(いいね追加時) */
    public function test_toggle_handles_unexpected_exception_on_create(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create();

        ReviewLike::creating(function () {
            throw new \Exception('予期しないDBエラー');
        });

        $response = $this->actingAs($user)->post("/reviews/{$review->id}/like");

        $response->assertSessionHas('error', 'いいねできませんでした');
    }

    /** 予期しないエラーが発生した場合、エラーメッセージを表示してリダイレクトする(いいね解除時) */
    public function test_toggle_handles_unexpected_exception_on_delete(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create();
        ReviewLike::factory()->create(['user_id' => $user->id, 'review_id' => $review->id]);

        ReviewLike::deleting(function () {
            throw new \Exception('予期しないDBエラー');
        });

        $response = $this->actingAs($user)->post("/reviews/{$review->id}/like");

        $response->assertSessionHas('error', 'いいねを解除できませんでした');
    }
}
