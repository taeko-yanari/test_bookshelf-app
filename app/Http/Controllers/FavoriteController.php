<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Favorite;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $books =  Book::whereHas('favorites', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('favorites.index', compact('books'));
    }

    public function toggle(Book $book)
    {
        $userId = Auth::id();
            $favorite = Favorite::where('user_id', $userId)
            ->where('book_id', $book->id)
            ->first();

        if ($favorite) {
            try {
                $favorite->delete();
            } catch (\Throwable $exception) {
                return redirect()->back()->with('error', 'お気に入りを解除できませんでした');
            }
        } else {
            try {
                Favorite::create([
                    'user_id' => $userId,
                    'book_id' => $book->id,
                ]);
            } catch (\Throwable $exception) {
                return redirect()->back()->with('error', 'お気に入りに登録できませんでした');
            }
        }
        return redirect()->route('books.show', $book->id);
    }
}