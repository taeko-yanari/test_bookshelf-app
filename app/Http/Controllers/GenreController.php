<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use Illuminate\Http\Request;

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
}