<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $series = $request->query('series');
        $language = $request->query('language');
        $published = Book::query()->published();

        $books = (clone $published)
            ->when($search !== '', function (Builder $query) use ($search): void {
                $like = '%'.$search.'%';

                $query->where(function (Builder $inner) use ($like): void {
                    $inner->where('title', 'like', $like)
                        ->orWhere('title_jp', 'like', $like)
                        ->orWhere('series', 'like', $like)
                        ->orWhere('description', 'like', $like);
                });
            })
            ->when(filled($series), fn (Builder $query) => $query->where('series', $series))
            ->when(filled($language), fn (Builder $query) => $query->where('language', $language))
            ->orderByRaw("coalesce(series, 'zzz')")
            ->orderBy('year')
            ->orderBy('title')
            ->paginate(25)
            ->withQueryString();

        return view('catalog', [
            'books' => $books,
            'search' => $search,
            'seriesOptions' => (clone $published)->whereNotNull('series')->distinct()->orderBy('series')->pluck('series'),
            'activeSeries' => $series,
            'activeLanguage' => $language,
            'languageCounts' => (clone $published)
                ->selectRaw('language, count(*) as total')
                ->groupBy('language')
                ->pluck('total', 'language'),
        ]);
    }
}
