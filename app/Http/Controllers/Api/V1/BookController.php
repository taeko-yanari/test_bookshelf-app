<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Http\Resources\BookResource;
use App\Http\Requests\Api\V1\ApiIndexBookRequest;

class BookController extends Controller
{
    public function index(ApiIndexBookRequest $request)
    {
        $keyword = $request->input('keyword');
        $query = Book::query()->with('genres');

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                ->orWhere('author', 'like', "%{$keyword}%")
                ->orWhere('isbn', 'like', "%{$keyword}%");
            });
        }

        $genreId = $request->input('genre_id');

        if ($genreId) {
            $query->whereHas('genres', fn($q) => $q->where('genres.id', $genreId));
        }

        $books = $query->withAvg('reviews', 'rating')
        ->withCount('reviews')
        ->orderBy('created_at', 'desc')
        ->paginate($request->input('per_page', 20));

        return BookResource::collection($books);
    }
}
