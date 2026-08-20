<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class RankingController extends Controller
{
    public function index()
    {
        $rankedBooks = Book::withAvg('reviews', 'rating')
        ->withCount('reviews')
        ->havingRaw('reviews_avg_rating IS NOT NULL')
        ->orderBy('reviews_avg_rating', 'desc')
        ->orderBy('reviews_count', 'desc')
        ->take(10)
        ->get();

        return view('ranking.index', compact('rankedBooks'));
    }
}