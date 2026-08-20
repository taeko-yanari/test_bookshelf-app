<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use Illuminate\Http\Request;
use App\Http\Requests\StoreGenreRequest;

class GenreController extends Controller
{
    public function index(Request $request)
    {
        $genres = Genre::withCount('books')
        ->orderBy('name', 'asc')
        ->get();
        return view('genres.index', compact('genres'));
    }

    public function show(Genre $genre)
    {
        $books = $genre->books()
        ->with('genres')
        ->orderBy('created_at', 'desc')
        ->paginate(10);

        return view('genres.show', compact('genre','books'));
    }

    public function create()
    {
        return view('genres.create');
    }

    public function store(StoreGenreRequest $request)
    {
        try {
            $validated = $request->validated();
            $genre = Genre::create($validated);

            return redirect()->route('genres.index')->with('success', 'ジャンルを登録しました');
        } catch (\Throwable $exception) {
            return redirect()->back()->withInput()->with('error', 'ジャンルの登録に失敗しました');
        }
    }
}