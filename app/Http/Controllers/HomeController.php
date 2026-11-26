<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __invoke(Request $request): View
    {
        $published = Book::query()->published();

        $franchises = [
            'harvest-moon' => [
                'label' => 'Harvest Moon',
                'logo' => 'images/Harvest_Moon_Logo_Transparent.png',
            ],
            'story-of-seasons' => [
                'label' => 'Story of Seasons',
                'logo' => 'images/Story-of-Seasons-logo.png',
            ],
            'rune-factory' => [
                'label' => 'Rune Factory',
                'logo' => 'images/Rune_Factory_Logo.png',
            ],
        ];
        $platformOptions = (clone $published)
            ->whereNotNull('platform')
            ->pluck('platform')
            ->flatMap(fn (string $platform): array => explode(' / ', $platform))
            ->map(fn (string $platform): string => trim($platform))
            ->filter()
            ->unique()
            ->sort()
            ->values();
        $requestedFranchise = (string) $request->query('franchise', '');
        $activeFranchise = array_key_exists($requestedFranchise, $franchises) ? $requestedFranchise : null;
        $requestedPlatform = trim((string) $request->query('platform', ''));
        $activePlatform = $platformOptions->containsStrict($requestedPlatform) ? $requestedPlatform : null;
        $requestedLanguage = (string) $request->query('language', '');
        $activeLanguage = in_array($requestedLanguage, ['jpn', 'eng', 'chi'], true) ? $requestedLanguage : null;

        $booksQuery = (clone $published)
            ->when($activeFranchise !== null, fn (Builder $query) => $this->filterFranchise($query, $activeFranchise))
            ->when($activePlatform !== null, fn (Builder $query) => $query->forPlatform($activePlatform))
            ->when($activeLanguage !== null, fn (Builder $query) => $query->where('language', $activeLanguage));

        $books = $booksQuery
            ->orderByRaw("coalesce(series, 'zzz')")
            ->orderBy('year')
            ->orderBy('title')
            ->get();

        return view('home', [
            'books' => $books,
            'franchises' => $franchises,
            'activeFranchise' => $activeFranchise,
            'platformOptions' => $platformOptions,
            'activePlatform' => $activePlatform,
            'activeLanguage' => $activeLanguage,
            'languageCounts' => (clone $published)
                ->selectRaw('language, count(*) as total')
                ->groupBy('language')
                ->pluck('total', 'language'),
        ]);
    }

    private function filterFranchise(Builder $query, string $franchise): Builder
    {
        return match ($franchise) {
            'harvest-moon' => $query
                ->where(function (Builder $query): void {
                    $query->whereNull('series')
                        ->orWhereNotIn('series', ['Rune Factory', 'Story of Seasons: Trio of Towns']);
                })
                ->where(function (Builder $query): void {
                    $query->whereNull('series')
                        ->orWhere('series', '!=', 'Friends of Mineral Town')
                        ->orWhere('platform', '!=', 'Nintendo Switch');
                }),
            'story-of-seasons' => $query->where(function (Builder $query): void {
                $query->where('series', 'like', 'Story of Seasons%')
                    ->orWhere(function (Builder $query): void {
                        $query->where('series', 'Friends of Mineral Town')
                            ->where('platform', 'like', '%Nintendo Switch%');
                    });
            }),
            'rune-factory' => $query->where('series', 'Rune Factory'),
            default => $query,
        };
    }
}
