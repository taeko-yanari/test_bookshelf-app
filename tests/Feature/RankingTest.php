<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingTest extends TestCase
{
    use RefreshDatabase;

    /** ゲストもランキング画面を閲覧できる */
    public function test_guest_can_view_ranking(): void
    {
        Book::factory()->create();

        $response = $this->get('/ranking');

        $response->assertStatus(200);
        $response->assertViewIs('ranking.index');
    }

    /** ランキングは、レビュー平均評価の降順で表示される */
    public function test_ranking_is_ordered_by_average_rating_descending(): void
    {
        $lowRatedBook = Book::factory()->create();
        Review::factory()->create(['book_id' => $lowRatedBook->id, 'rating' => 2]);

        $highRatedBook = Book::factory()->create();
        Review::factory()->create(['book_id' => $highRatedBook->id, 'rating' => 5]);

        $response = $this->get('/ranking');

        $rankedBooks = $response->viewData('rankedBooks');
        $this->assertEquals($highRatedBook->id, $rankedBooks->first()->id);
    }

    /** タイブレーク:平均評価が同じ場合、レビュー件数が多い書籍が上位に表示される */
    public function test_ranking_tie_break_favors_more_reviews(): void
    {
        $fewerReviewsBook = Book::factory()->create();
        Review::factory()->create(['book_id' => $fewerReviewsBook->id, 'rating' => 5]);

        $moreReviewsBook = Book::factory()->create();
        Review::factory()->count(2)->create(['book_id' => $moreReviewsBook->id, 'rating' => 5]);

        $response = $this->get('/ranking');

        $rankedBooks = $response->viewData('rankedBooks');
        $this->assertEquals($moreReviewsBook->id, $rankedBooks->first()->id);
    }

    /** レビューが1件も無い書籍は、ランキングに表示されない */
    public function test_books_without_reviews_are_excluded_from_ranking(): void
    {
        $bookWithReview = Book::factory()->create();
        Review::factory()->create(['book_id' => $bookWithReview->id, 'rating' => 4]);

        $bookWithoutReview = Book::factory()->create();

        $response = $this->get('/ranking');

        $rankedBooks = $response->viewData('rankedBooks');
        $this->assertTrue($rankedBooks->contains('id', $bookWithReview->id));
        $this->assertFalse($rankedBooks->contains('id', $bookWithoutReview->id));
    }

    /** ランキングは上位10件までしか表示されない */
    public function test_ranking_shows_only_top_10(): void
    {
        Book::factory()->count(12)->create()->each(function ($book) {
            Review::factory()->create(['book_id' => $book->id, 'rating' => 3]);
        });

        $response = $this->get('/ranking');

        $rankedBooks = $response->viewData('rankedBooks');
        $this->assertCount(10, $rankedBooks);
    }
}
