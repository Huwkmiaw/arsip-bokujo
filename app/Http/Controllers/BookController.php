<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Contracts\View\View;

class BookController extends Controller
{
    public function show(Book $book): View
    {
        abort_unless($book->status === 'published', 404);

        $book->increment('views');

        $related = Book::query()
            ->published()
            ->whereKeyNot($book->getKey())
            ->when(filled($book->series), fn ($query) => $query->where('series', $book->series))
            ->take(4)
            ->get();

        if ($related->isEmpty()) {
            $related = Book::query()->published()->whereKeyNot($book->getKey())->inRandomOrder()->take(4)->get();
        }

        return view('books.show', [
            'book' => $book,
            'related' => $related,
        ]);
    }
}
