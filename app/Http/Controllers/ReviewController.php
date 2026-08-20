<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Review;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request, Book $book)
    {
        try {
            $validated = $request->validated();
            $validated['user_id'] = Auth::id();
            $validated['book_id'] = $book->id;
            $review = Review::create($validated);

            return redirect()->route('books.show', $book)->with('success', 'レビューを登録しました');
        } catch (\Throwable $exception) {
            return redirect()->back()->withInput()->with('error', 'レビューの登録に失敗しました');
        }
    }

    public function edit(Review $review)
    {
        $this->authorize('update', $review);

        return view('reviews.edit', compact('review'));
    }

    public function update(UpdateReviewRequest $request, Review $review)
    {
        $this->authorize('update', $review);

        try {
            $validated = $request->validated();
            $review->update($validated);

            return redirect()->route('books.show', $review->book->id)->with('success', 'レビューを更新しました');
        } catch (\Throwable $exception) {
            return redirect()->back()->withInput()->with('error', 'レビューの更新に失敗しました');
        }
    }

    public function destroy(Review $review)
    {
        $this->authorize('delete', $review);

        try {
            $review->delete();

            return redirect()->route('books.show', $review->book->id)->with('success', 'レビューを削除しました');
        } catch (\Throwable $exception) {
            return redirect()->back()->with('error', 'レビューの削除に失敗しました');
        }
    }
}