<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Http\Request;
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
}