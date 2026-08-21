<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    /** ジャンルは、複数の書籍を持つ(belongsToMany) */
    public function test_genre_belongs_to_many_books(): void
    {
        $genre = Genre::factory()->create();
        $books = Book::factory()->count(2)->create();

        $genre->books()->attach($books->pluck('id'));

        $this->assertCount(2, $genre->books);
        $this->assertInstanceOf(Book::class, $genre->books->first());

        foreach ($books as $book) {
            $this->assertDatabaseHas('book_genres', [
                'genre_id' => $genre->id,
                'book_id' => $book->id,
            ]);
        }
    }
}
