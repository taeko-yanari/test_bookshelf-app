<?php

namespace App\Http\Controllers;

use App\Models\ReviewLike;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewLikeController extends Controller
{
    public function toggle(Review $review)
    {
        $userId = Auth::id();
            $reviewlike = ReviewLike::where('user_id', $userId)
            ->where('review_id', $review->id)
            ->first();

        if ($reviewlike) {
            try {
                $reviewlike->delete();
            } catch (\Throwable $exception) {
                return redirect()->back()->with('error', 'いいねを解除できませんでした');
            }
        } else {
            try {
                ReviewLike::create([
                    'user_id' => $userId,
                    'review_id' => $review->id,
                ]);
            } catch (\Throwable $exception) {
                return redirect()->back()->with('error', 'いいねできませんでした');
            }
        }
        return redirect()->route('books.show', $review->book->id);
    }
}