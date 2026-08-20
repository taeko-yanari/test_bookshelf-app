<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Support\Facades\Auth;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $books = Book::with('genres')->orderBy('created_at', 'desc')->paginate(10);
        return view('books.index', compact('books'));
    }

    public function create()
    {
        $genres = Genre::all();
        return view('books.create', compact('genres'));
    }

    public function store(StoreBookRequest $request)
    {
        try {
            $validated = $request->validated();
            $validated['user_id'] = Auth::id();

            $book = Book::create(collect($validated)->except('genres')->toArray());
            $book->genres()->sync($validated['genres']);

            return redirect()->route('books.show', $book->id)->with('success', '書籍を登録しました');
        } catch (\Throwable $exception) {
            return redirect()->back()->withInput()->with('error', '書籍の登録に失敗しました');
        }
    }

    public function show(Book $book)
    {
        $book -> load([
            'genres',
            'reviews' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'favorites'
        ]);
        return view('books.show', compact('book'));
    }

    public function edit(Book $book)
    {
        $this->authorize('update', $book);
        $genres = Genre::all();
        $bookGenreIds = $book->genres->pluck('id');

        return view('books.edit', compact('book','genres','bookGenreIds'));
    }

    public function update(UpdateBookRequest $request,Book $book)
    {
        $this->authorize('update', $book);

        try {
            $validated = $request->validated();

            $book->update(collect($validated)->except('genres')->toArray());
            $book->genres()->sync($validated['genres']);

            return redirect()->route('books.show', $book->id)->with('success', '書籍を更新しました');
        } catch (\Throwable $exception) {
            return redirect()->back()->withInput()->with('error', '書籍の更新に失敗しました');
        }
    }

    public function destroy(Book $book)
    {
        $this->authorize('delete', $book);

        try {
            $book->delete();
            return redirect()->route('books.index')->with('success', '書籍を削除しました');
        } catch (\Throwable $exception) {
            return redirect()->back()->with('error', '書籍の削除に失敗しました');
        }
    }
}