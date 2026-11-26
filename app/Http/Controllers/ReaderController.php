<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Contracts\View\View;

class ReaderController extends Controller
{
    public function show(Book $book): View
    {
        abort_unless($book->status === 'published', 404);
        abort_unless($book->isReadable(), 404, 'Buku ini tidak memiliki berkas untuk dibaca.');

        return view('books.read', [
            'book' => $book,
        ]);
    }
}
