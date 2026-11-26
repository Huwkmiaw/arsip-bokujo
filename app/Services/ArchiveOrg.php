<?php

namespace App\Services;

use App\Models\Book;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class ArchiveOrg
{
    public const SEARCH_QUERY = 'collection:(strategyguides) AND (title:(bokujou) OR title:(bokujo) OR title:(harvest moon) OR subject:(harvest moon) OR subject:(bokujo monogatari))';

    public function client(): PendingRequest
    {
        return Http::withHeaders([
            'User-Agent' => 'ArsipBokujou/1.0 (local archive demo; contact: admin@arsip.test)',
            'Accept' => 'application/json',
        ])->timeout(25);
    }

    /**
     * Ambil metadata mentah sebuah item Archive.org.
     *
     * @return array<string, mixed>|null
     */
    public function metadata(string $identifier): ?array
    {
        $identifier = $this->identifierFromUrl($identifier);

        if ($identifier === '') {
            return null;
        }

        try {
            $response = $this->client()->retry(2, 400)->get('https://archive.org/metadata/'.$identifier);
        } catch (Throwable) {
            return null;
        }

        if ($response->failed() || ! is_array($response->json('metadata'))) {
            return null;
        }

        return $response->json();
    }

    /**
     * Ubah metadata mentah menjadi atribut siap simpan.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function normalize(array $payload, string $identifier): array
    {
        $metadata = $payload['metadata'] ?? [];
        $files = $payload['files'] ?? [];

        $pdfSize = collect($files)
            ->filter(fn (array $file): bool => Str::contains(strtolower((string) ($file['format'] ?? '')), 'pdf'))
            ->sortByDesc(fn (array $file): int => (int) ($file['size'] ?? 0))
            ->first();

        $description = is_array($metadata['description'] ?? null)
            ? implode(' ', $metadata['description'])
            : (string) ($metadata['description'] ?? '');

        $description = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($description))));
        $description = $this->withScannerCredit(
            Str::limit($description, 1200) ?: null,
            $this->scannerCredit($metadata),
        );

        return [
            'title' => $this->firstValue($metadata['title'] ?? null) ?: Str::headline($identifier),
            'title_jp' => $this->firstValue($metadata['title_jp'] ?? null) ?: $this->firstValue($metadata['alternative'] ?? null),
            'archive_id' => $identifier,
            'source' => 'archive',
            'year' => $this->year($metadata),
            'language' => $this->language($metadata['language'] ?? null),
            'publisher' => $this->firstValue($metadata['publisher'] ?? null),
            'pages' => isset($metadata['imagecount']) ? (int) $metadata['imagecount'] : null,
            'description' => $description,
            'cover_url' => 'https://archive.org/services/img/'.$identifier,
            'file_size' => $pdfSize ? (int) $pdfSize['size'] : null,
            'allow_download' => ! filter_var($metadata['access-restricted-item'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'is_featured' => false,
            'status' => 'published',
        ];
    }

    public function import(string $identifier): ?Book
    {
        $identifier = $this->identifierFromUrl($identifier);
        $payload = $this->metadata($identifier);

        if ($payload === null) {
            return null;
        }

        $attributes = $this->normalize($payload, $identifier);

        if (blank($attributes['pages'] ?? null)) {
            $attributes['pages'] = $this->pageCountFromScandata($payload['files'] ?? [], $identifier);
        }

        $book = Book::query()->where('archive_id', $identifier)->first();

        if ($book === null) {
            $attributes['slug'] = Book::makeSlug($attributes['title']);
            $book = Book::query()->create($attributes);

            return $book;
        }

        // Isian kurasi tidak ditimpa; metadata hanya melengkapi yang masih kosong.
        foreach (['title', 'title_jp', 'year', 'language', 'publisher', 'pages', 'cover_url'] as $field) {
            if (blank($book->{$field}) && filled($attributes[$field] ?? null)) {
                $book->{$field} = $attributes[$field];
            }
        }

        $book->description = $this->withScannerCredit(
            $book->description,
            $this->scannerCredit($payload['metadata'] ?? []),
        );
        $book->file_size = $attributes['file_size'] ?? $book->file_size;
        $book->allow_download = $attributes['allow_download'];

        $book->save();

        return $book;
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function scannerCredit(array $metadata): ?string
    {
        foreach (['scanned_by', 'scan_credit', 'scan_credit_name', 'digitized_by', 'scan_by'] as $field) {
            $credit = $this->firstValue($metadata[$field] ?? null);

            if ($credit !== null && filter_var($credit, FILTER_VALIDATE_EMAIL) === false) {
                return Str::limit(trim(strip_tags($credit)), 160);
            }
        }

        return null;
    }

    private function withScannerCredit(?string $description, ?string $credit): ?string
    {
        if (blank($credit)) {
            return $description;
        }

        $creditText = 'Pemindai: '.$credit;

        if (Str::contains(Str::lower((string) $description), Str::lower($creditText))) {
            return $description;
        }

        return trim(implode(' ', array_filter([$description, $creditText]))) ?: null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function search(int $limit = 50): array
    {
        try {
            $response = $this->client()->retry(2, 400)->get('https://archive.org/advancedsearch.php', [
                'q' => self::SEARCH_QUERY,
                'fl' => ['identifier', 'title', 'year', 'date', 'language', 'downloads'],
                'rows' => $limit,
                'page' => 1,
                'sort' => ['downloads desc'],
                'output' => 'json',
            ]);
        } catch (Throwable) {
            return [];
        }

        return $response->json('response.docs') ?? [];
    }

    /**
     * Baca jumlah halaman dari berkas scandata.xml milik item.
     *
     * @param  array<int, array<string, mixed>>  $files
     */
    private function pageCountFromScandata(array $files, string $identifier): ?int
    {
        $scandata = collect($files)->first(
            fn (array $file): bool => str_ends_with((string) ($file['name'] ?? ''), '_scandata.xml'),
        );

        if ($scandata === null) {
            return null;
        }

        try {
            $response = $this->client()->timeout(30)->get(
                'https://archive.org/download/'.$identifier.'/'.rawurlencode($scandata['name']),
            );
        } catch (Throwable) {
            return null;
        }

        if ($response->failed() || preg_match('/<leafCount>(\d+)<\/leafCount>/', $response->body(), $matches) !== 1) {
            return null;
        }

        return (int) $matches[1];
    }

    public function identifierFromUrl(string $value): string
    {
        $value = trim($value);

        if (Str::contains($value, 'archive.org')) {
            $value = (string) parse_url($value, PHP_URL_PATH);
            $value = Str::after($value, '/details/');
            $value = Str::before($value, '/');
        }

        return trim($value, " \t\n\r\0\x0B/");
    }

    private function firstValue(mixed $value): ?string
    {
        if (is_array($value)) {
            $value = reset($value);
        }

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return trim($value);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function year(array $metadata): ?int
    {
        $candidates = [$metadata['year'] ?? null, $metadata['date'] ?? null, $metadata['publicdate'] ?? null];

        foreach ($candidates as $candidate) {
            if (is_array($candidate)) {
                $candidate = reset($candidate);
            }

            if (preg_match('/(19|20)\d{2}/', (string) $candidate, $matches) === 1) {
                return (int) $matches[0];
            }
        }

        return null;
    }

    private function language(mixed $value): string
    {
        $value = strtolower((string) (is_array($value) ? reset($value) : $value));

        return match (true) {
            Str::contains($value, ['jpn', 'japanese', 'jepang', '日本語']) => 'jpn',
            Str::contains($value, ['eng', 'english', 'inggris']) => 'eng',
            Str::contains($value, ['chi', 'chinese', 'mandarin', 'zho']) => 'chi',
            Str::contains($value, ['ind', 'indonesian', 'indonesia']) => 'idn',
            default => 'jpn',
        };
    }
}
