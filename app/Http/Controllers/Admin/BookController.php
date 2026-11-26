<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Services\ArchiveOrg;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{
    public function index(): View
    {
        return view('admin.books.index', [
            'books' => Book::query()->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.books.form', [
            'book' => new Book([
                'language' => 'jpn',
                'status' => 'published',
                'allow_download' => true,
            ]),
        ]);
    }

    public function store(Request $request, ArchiveOrg $archive): RedirectResponse
    {
        $data = $request->validate($this->rules(), $this->messages());

        $book = new Book;
        $book->fill(Arr::except($data, ['cover']));
        $book->slug = Book::makeSlug($data['title']);
        $book->allow_download = $request->boolean('allow_download');
        $book->is_featured = $request->boolean('is_featured');
        $book->archive_id = filled($data['archive_id'] ?? null)
            ? $archive->identifierFromUrl($data['archive_id'])
            : null;

        $this->storeCover($request, $book);
        $this->applySource($book);
        $book->save();

        return redirect()
            ->route('admin.buku.index')
            ->with('status', 'Buku "'.$book->title.'" berhasil ditambahkan.');
    }

    public function edit(Book $book): View
    {
        return view('admin.books.form', [
            'book' => $book,
        ]);
    }

    public function update(Request $request, Book $book, ArchiveOrg $archive): RedirectResponse
    {
        $data = $request->validate($this->rules($book), $this->messages());

        $book->fill(Arr::except($data, ['cover']));
        $book->allow_download = $request->boolean('allow_download');
        $book->is_featured = $request->boolean('is_featured');
        $book->archive_id = filled($data['archive_id'] ?? null)
            ? $archive->identifierFromUrl($data['archive_id'])
            : null;

        $this->storeCover($request, $book);
        $this->applySource($book);
        $book->save();

        return redirect()
            ->route('admin.buku.index')
            ->with('status', 'Perubahan pada "'.$book->title.'" tersimpan.');
    }

    public function destroy(Book $book): RedirectResponse
    {
        foreach (['cover_path', 'file_path'] as $column) {
            if (filled($book->{$column})) {
                Storage::disk('public')->delete($book->{$column});
            }
        }

        $book->delete();

        return redirect()
            ->route('admin.buku.index')
            ->with('status', 'Buku berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(?Book $book = null): array
    {
        $archiveRules = ['nullable', 'string', 'max:2048', 'prohibits:external_url'];
        $externalRules = ['nullable', 'url:https', 'max:2048', 'prohibits:archive_id'];

        if (blank($book?->file_path)) {
            $archiveRules[] = 'required_without:external_url';
            $externalRules[] = 'required_without:archive_id';
        }

        $archiveRules[] = function (string $attribute, mixed $value, Closure $fail): void {
            if (filled($value) && ! $this->isValidArchiveReference((string) $value)) {
                $fail('Masukkan identifier Archive.org atau tautan item Archive.org yang valid.');
            }
        };
        $externalRules[] = function (string $attribute, mixed $value, Closure $fail): void {
            if (filled($value) && ! $this->isGoogleDriveShareUrl((string) $value)) {
                $fail('Tautan eksternal harus berupa tautan file Google Drive yang dapat dibagikan.');
            }
        };

        return [
            'title' => ['required', 'string', 'max:255'],
            'title_jp' => ['nullable', 'string', 'max:255'],
            'series' => ['nullable', 'string', 'max:255'],
            'platform' => ['nullable', 'string', 'max:100'],
            'year' => ['nullable', 'integer', 'min:1990', 'max:2035'],
            'language' => ['required', 'in:jpn,eng,chi,idn'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'pages' => ['nullable', 'integer', 'min:1', 'max:5000'],
            'archive_id' => $archiveRules,
            'external_url' => $externalRules,
            'status' => ['required', 'in:draft,published'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'file' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'archive_id.required_without' => 'Masukkan tautan Archive.org atau Google Drive.',
            'external_url.required_without' => 'Masukkan tautan Archive.org atau Google Drive.',
            'archive_id.prohibits' => 'Pilih satu sumber saja: Archive.org atau Google Drive.',
            'external_url.prohibits' => 'Pilih satu sumber saja: Archive.org atau Google Drive.',
            'file.prohibited' => 'PDF/ZIP tidak dapat diunggah. Gunakan tautan Archive.org atau Google Drive.',
        ];
    }

    private function isValidArchiveReference(string $value): bool
    {
        $value = trim($value);

        if (preg_match('~^https?://~i', $value) === 1) {
            $parts = parse_url($value);
            $host = strtolower((string) ($parts['host'] ?? ''));
            $path = (string) ($parts['path'] ?? '');

            if (($parts['scheme'] ?? null) !== 'https'
                || ! in_array($host, ['archive.org', 'www.archive.org'], true)
                || isset($parts['user'])
                || preg_match('~^/details/([^/]+)(?:/.*)?$~', $path, $matches) !== 1
            ) {
                return false;
            }

            $value = rawurldecode($matches[1]);
        }

        return strlen($value) <= 255
            && preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]*$/D', $value) === 1;
    }

    private function isGoogleDriveShareUrl(string $value): bool
    {
        $parts = parse_url($value);

        if (($parts['scheme'] ?? null) !== 'https'
            || strtolower((string) ($parts['host'] ?? '')) !== 'drive.google.com'
            || isset($parts['user'])
        ) {
            return false;
        }

        $path = rtrim((string) ($parts['path'] ?? ''), '/');

        if (preg_match('~^/file/d/[A-Za-z0-9_-]+/view$~', $path) === 1) {
            return true;
        }

        if ($path !== '/open') {
            return false;
        }

        parse_str((string) ($parts['query'] ?? ''), $query);

        return preg_match('/^[A-Za-z0-9_-]+$/D', (string) ($query['id'] ?? '')) === 1;
    }

    private function applySource(Book $book): void
    {
        if (filled($book->archive_id)) {
            $book->source = 'archive';

            return;
        }

        if (filled($book->file_path)) {
            $book->source = 'upload';

            return;
        }

        if (filled($book->external_url)) {
            $book->source = 'external';
            $book->allow_download = false;

            return;
        }

        $book->source = 'upload';
    }

    private function storeCover(Request $request, Book $book): void
    {
        if ($request->hasFile('cover')) {
            if (filled($book->cover_path)) {
                Storage::disk('public')->delete($book->cover_path);
            }

            $book->cover_path = $request->file('cover')->store('covers', 'public');
        }

        if (blank($book->cover_path) && filled($book->archive_id)) {
            $book->cover_url = 'https://archive.org/services/img/'.$book->archive_id;
        }
    }
}
