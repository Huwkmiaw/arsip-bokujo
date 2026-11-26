<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'total' => Book::query()->count(),
                'published' => Book::query()->where('status', 'published')->count(),
                'draft' => Book::query()->where('status', 'draft')->count(),
                'archive' => Book::query()->where('source', 'archive')->count(),
                'external' => Book::query()->where('source', 'external')->count(),
                'views' => (int) Book::query()->sum('views'),
            ],
            'recent' => Book::query()->latest()->take(8)->get(),
        ]);
    }
}
