<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadController extends Controller
{
    public function __invoke(Book $book): RedirectResponse|StreamedResponse
    {
        abort_unless($book->status === 'published', 404);
        abort_unless($book->allow_download, 403, 'Berkas ini tidak tersedia untuk diunduh.');

        $book->increment('downloads');

        if ($book->isFromArchive()) {
            return redirect()->away('https://archive.org/download/'.$book->archive_id);
        }

        abort_unless(filled($book->file_path), 404);
        abort_unless(Storage::disk('public')->exists($book->file_path), 404, 'Berkas tidak ditemukan di penyimpanan.');

        $extension = pathinfo($book->file_path, PATHINFO_EXTENSION) ?: 'pdf';

        return Storage::disk('public')->download($book->file_path, $book->slug.'.'.$extension);
    }
}
