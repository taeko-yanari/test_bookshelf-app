<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Http\Resources\BookResource;
use App\Http\Resources\BookDetailResource;
use App\Http\Resources\BookStoreResource;
use App\Http\Requests\Api\V1\ApiIndexBookRequest;
use App\Http\Requests\Api\V1\ApiStoreBookRequest;
use App\Http\Requests\Api\V1\ApiUpdateBookRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

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

    public function show(Book $book)
    {
        $book->load(['reviews.user', 'genres']);

        return new BookDetailResource($book);
    }

    public function store(ApiStoreBookRequest $request)
    {
        $validated = $request->validated();
        $book = DB::transaction(function () use ($validated) {
            $book = Book::create(Arr::except($validated, ['genres']));
            $book->genres()->sync($validated['genres']);
            return $book;
        });

        $book->load(['genres']);

        return (new BookStoreResource($book))->response()->setStatusCode(201);
    }

    public function update(ApiUpdateBookRequest $request, Book $book)
    {
        $validated = $request->validated();
            $book = DB::transaction(function () use ($validated, $book) {
                $book->update(collect($validated)->except('genres')->toArray());
                $book->genres()->sync($validated['genres']);
                return $book;
            });

        $book->load(['genres']);

        return (new BookDetailResource($book))->response()->setStatusCode(200);
    }

}
