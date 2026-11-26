<?php

namespace App\Services;

use App\Models\Book;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BguideCatalog
{
    /**
     * @var array<string, string>
     */
    private const SOURCES = [
        'bokumono' => "https://bguide.vercel.app/api/blog/Official%20and%20Fan's%20Guide%20for%20Bokumono",
        'rune_factory' => 'https://bguide.vercel.app/api/blog/Rune%20Factory%20Guide',
    ];

    /**
     * @return array{added: int, skipped: int, total: int}
     */
    public function import(): array
    {
        $references = [];

        foreach (self::SOURCES as $source => $url) {
            $markdown = Http::accept('text/plain')
                ->connectTimeout(5)
                ->timeout(20)
                ->get($url)
                ->throw()
                ->body();

            array_push($references, ...$this->parse($markdown, $source));
        }

        $references = collect($references)
            ->unique(fn (array $reference): string => $reference['archive_id']
                ? 'archive:'.$reference['archive_id']
                : 'external:'.$reference['external_url'])
            ->values();

        $added = 0;
        $skipped = 0;

        foreach ($references as $reference) {
            $existing = $this->findExisting($reference);

            if ($existing !== null) {
                $missingMetadata = array_filter([
                    'series' => blank($existing->series) ? $reference['series'] : null,
                    'platform' => blank($existing->platform) ? $reference['platform'] : null,
                ], fn (?string $value): bool => filled($value));

                if ($missingMetadata !== []) {
                    $existing->update($missingMetadata);
                }

                $skipped++;

                continue;
            }

            Book::query()->create([
                'title' => $reference['title'],
                'slug' => Book::makeSlug($reference['title']),
                'series' => $reference['series'],
                'platform' => $reference['platform'],
                'language' => 'jpn',
                'source' => $reference['archive_id'] ? 'archive' : 'external',
                'archive_id' => $reference['archive_id'],
                'external_url' => $reference['external_url'],
                'cover_url' => $reference['archive_id']
                    ? 'https://archive.org/services/img/'.$reference['archive_id']
                    : null,
                'allow_download' => false,
                'status' => 'published',
            ]);

            $added++;
        }

        return [
            'added' => $added,
            'skipped' => $skipped,
            'total' => $references->count(),
        ];
    }

    /**
     * @return array<int, array{title: string, series: ?string, platform: ?string, archive_id: ?string, external_url: ?string}>
     */
    private function parse(string $markdown, string $source): array
    {
        $books = [];
        $current = [];
        $platform = null;
        $series = $source === 'rune_factory' ? 'Rune Factory' : null;
        $markdown = $this->normalizeMarkup($markdown);

        $flush = function () use (&$books, &$current, &$platform, &$series): void {
            if (blank($current['title'] ?? null) || ($current['links'] ?? []) === []) {
                $current = [];

                return;
            }

            $archiveLink = collect($current['links'])->first(
                fn (array $link): bool => filled($link['archive_id']),
            );
            $selectedLink = $archiveLink ?? $current['links'][0];

            $books[] = [
                'title' => $current['title'],
                'series' => $series,
                'platform' => $this->platformForTitle($current['title'], $platform, $series),
                'archive_id' => $selectedLink['archive_id'],
                'external_url' => $selectedLink['external_url'],
            ];
            $current = [];
        };

        foreach (preg_split('/\R/u', $markdown) ?: [] as $line) {
            $line = trim($line);

            if (preg_match('/^#{1,2}\s+(.+)$/u', $line, $heading) === 1) {
                $flush();
                $headingText = trim($heading[1]);

                if (Str::contains(Str::lower($headingText), 'rune factory')) {
                    $series = 'Rune Factory';
                    $platform = null;

                    continue;
                }

                if (str_starts_with($line, '##')) {
                    $series = $this->normalizeSeries($headingText);
                } else {
                    $platform = $this->normalizePlatform($headingText);
                    $series = null;
                }

                continue;
            }

            if (preg_match('/^\d+\.\s+(.+)$/u', $line, $title) === 1) {
                $flush();
                $current = ['title' => trim($title[1]), 'links' => []];

                continue;
            }

            $links = $this->linksFromLine($line);

            if ($links !== []) {
                if (isset($current['title'])) {
                    array_push($current['links'], ...$links);
                }

                continue;
            }

            if ($line !== ''
                && preg_match('/^[-*#>!\[]/', $line) !== 1
                && ! Str::contains($line, '://')
            ) {
                $flush();
                $current = ['title' => trim($line, "* \t"), 'links' => []];
            }
        }

        $flush();

        return collect($books)
            ->unique(fn (array $book): string => $book['archive_id']
                ? 'archive:'.$book['archive_id']
                : 'external:'.$book['external_url'])
            ->values()
            ->all();
    }

    private function normalizeSeries(string $series): string
    {
        return match (Str::lower(trim($series))) {
            'story of seasons trio of town', 'story of seasons trio of towns' => 'Story of Seasons: Trio of Towns',
            default => trim($series),
        };
    }

    private function normalizePlatform(string $platform): ?string
    {
        return match (Str::lower(trim($platform))) {
            'snes' => 'Super Famicom',
            'nintendo 64' => 'Nintendo 64',
            'gameboy advance' => 'Game Boy Advance',
            'ps1' => 'PlayStation',
            'gamecube' => 'Nintendo GameCube',
            'ps2' => 'PlayStation 2',
            'nds', 'nintendo ds' => 'Nintendo DS',
            '3ds' => 'Nintendo 3DS',
            'switch' => 'Nintendo Switch',
            default => null,
        };
    }

    private function platformForTitle(string $title, ?string $platform, ?string $series): ?string
    {
        if (Str::contains(Str::lower($title), 'variety book')) {
            return 'Game Boy Advance / Nintendo GameCube';
        }

        if (preg_match('/Rune Factory [123]\b/i', $title) === 1) {
            return 'Nintendo DS';
        }

        if (Str::contains(Str::lower($title), 'rune factory 4')) {
            return 'Nintendo 3DS';
        }

        if (Str::contains(Str::lower($title), 'rune factory frontier')) {
            return 'Wii';
        }

        if (Str::contains(Str::lower($title), ['rune factory oceans', 'tides of destiny'])) {
            return 'Wii / PlayStation 3';
        }

        return match (Str::lower((string) $series)) {
            'harvest moon gb' => 'Game Boy',
            'harvest moon gbc2', 'harvest moon gbc3' => 'Game Boy Color',
            default => $platform,
        };
    }

    private function normalizeMarkup(string $content): string
    {
        $content = preg_replace_callback(
            '~<h([1-6])\b[^>]*>(.*?)</h\1>~is',
            fn (array $match): string => "\n".str_repeat('#', (int) $match[1]).' '.trim(strip_tags($match[2]))."\n",
            $content,
        ) ?? $content;
        $content = preg_replace_callback(
            '~<a\b[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)</a>~is',
            fn (array $match): string => '['.trim(strip_tags($match[2])).']('.html_entity_decode($match[1]).')',
            $content,
        ) ?? $content;
        $content = preg_replace('~</(?:li|p|div|section|article)>~i', "\n", $content) ?? $content;
        $content = preg_replace('~<br\s*/?>~i', "\n", $content) ?? $content;

        return html_entity_decode(strip_tags($content), ENT_QUOTES | ENT_HTML5);
    }

    /**
     * @return array<int, array{archive_id: ?string, external_url: ?string}>
     */
    private function linksFromLine(string $line): array
    {
        preg_match_all('~https?://[^\s\])"\'<>]+~u', $line, $matches);

        $links = [];

        foreach ($matches[0] as $url) {
            $url = rtrim($url, '.,;');
            $host = strtolower((string) parse_url($url, PHP_URL_HOST));
            $pathSegments = explode('/', trim((string) parse_url($url, PHP_URL_PATH), '/'));

            if ($host === 'archive.org' || str_ends_with($host, '.archive.org')) {
                $marker = array_search('details', $pathSegments, true);

                if ($marker === false) {
                    $marker = array_search('items', $pathSegments, true);
                }

                if ($marker !== false && isset($pathSegments[$marker + 1])) {
                    $links[] = [
                        'archive_id' => rawurldecode($pathSegments[$marker + 1]),
                        'external_url' => null,
                    ];
                }

                continue;
            }

            if ($host === 'drive.google.com'
                && parse_url($url, PHP_URL_SCHEME) === 'https'
                && preg_match('~^/(?:file/d/[A-Za-z0-9_-]+/view|open)$~', (string) parse_url($url, PHP_URL_PATH)) === 1
            ) {
                $links[] = [
                    'archive_id' => null,
                    'external_url' => $url,
                ];
            }
        }

        return $links;
    }

    /**
     * @param  array{title: string, series: ?string, platform: ?string, archive_id: ?string, external_url: ?string}  $reference
     */
    private function findExisting(array $reference): ?Book
    {
        $existing = Book::query()
            ->when($reference['archive_id'], fn ($query) => $query->where('archive_id', $reference['archive_id']))
            ->when($reference['external_url'], fn ($query) => $query->where('external_url', $reference['external_url']))
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        return Book::query()
            ->whereRaw('LOWER(title) = ?', [Str::lower($reference['title'])])
            ->get()
            ->first(fn (Book $book): bool =>
                (blank($reference['series']) || blank($book->series) || Str::lower($book->series) === Str::lower($reference['series']))
                && (blank($reference['platform']) || blank($book->platform) || Str::lower($book->platform) === Str::lower($reference['platform']))
            );
    }
}
