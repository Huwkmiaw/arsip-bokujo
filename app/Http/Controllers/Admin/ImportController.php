<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Services\ArchiveOrg;
use App\Services\BguideCatalog;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function index(): View
    {
        return view('admin.import', [
            'examples' => [
                'bokujo-monogatari-yokoso-kaze-no-bazaru-e-ultimate-guide',
                'bokujo-monogatari-hyper-guide-book',
                'bokujou-monogatari-friends-of-mineral-town-switch',
            ],
            'imported' => Book::query()
                ->where(fn ($query) => $query->whereNotNull('archive_id')->orWhereNotNull('external_url'))
                ->latest()
                ->take(6)
                ->get(),
        ]);
    }

    public function store(Request $request, ArchiveOrg $archive): RedirectResponse
    {
        $validated = $request->validate([
            'identifier' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! $this->isArchiveOrgIdentifier((string) $value)) {
                        $fail('Masukkan identifier atau tautan Archive.org. Tautan Google Drive ditambahkan melalui Tambah manual.');
                    }
                },
            ],
        ], [
            'identifier.required' => 'Tempelkan identifier atau tautan Archive.org terlebih dahulu.',
        ]);

        $book = $archive->import($validated['identifier']);

        if ($book === null) {
            return back()
                ->withInput()
                ->withErrors(['identifier' => 'Item tidak ditemukan. Pastikan identifier atau tautannya benar.']);
        }

        return redirect()
            ->route('admin.buku.edit', $book)
            ->with('status', 'Metadata "'.$book->title.'" berhasil diimpor dari Archive.org. Periksa dan sunting datanya sebelum selesai.');
    }

    public function importBguide(BguideCatalog $catalog): RedirectResponse
    {
        try {
            $result = $catalog->import();
        } catch (ConnectionException|RequestException $exception) {
            report($exception);

            return redirect()
                ->route('admin.import.index')
                ->withErrors([
                    'bguide' => 'Daftar Bguide tidak dapat diambil. Coba lagi beberapa saat nanti.',
                ]);
        }

        return redirect()
            ->route('admin.import.index')
            ->with('status', "Impor Bguide selesai: {$result['added']} buku ditambahkan, {$result['skipped']} duplikat dilewati.");
    }

    private function isArchiveOrgIdentifier(string $value): bool
    {
        $value = trim($value);

        if (preg_match('~^https?://~i', $value) === 1) {
            $parts = parse_url($value);

            if (! is_array($parts)) {
                return false;
            }

            $host = strtolower((string) ($parts['host'] ?? ''));
            $path = (string) ($parts['path'] ?? '');

            if (strtolower((string) ($parts['scheme'] ?? '')) !== 'https'
                || ! in_array($host, ['archive.org', 'www.archive.org'], true)
                || isset($parts['user'])
                || preg_match('~^/details/([A-Za-z0-9][A-Za-z0-9._-]*)(?:/.*)?$~', $path, $matches) !== 1
            ) {
                return false;
            }

            return strlen($matches[1]) <= 255;
        }

        return strlen($value) <= 255
            && preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]*$/D', $value) === 1;
    }
}
