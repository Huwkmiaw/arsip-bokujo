<?php

use App\Http\Controllers\Admin\BookController as AdminBookController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ImportController as AdminImportController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReaderController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/katalog', [CatalogController::class, 'index'])->name('catalog');
Route::get('/buku/{book}', [BookController::class, 'show'])->name('books.show');
Route::get('/buku/{book}/baca', [ReaderController::class, 'show'])->name('books.read');
Route::get('/buku/{book}/unduh', DownloadController::class)->name('books.download');

Route::middleware('guest')->group(function (): void {
    Route::get('/masuk', [LoginController::class, 'create'])->name('login');
    Route::post('/masuk', [LoginController::class, 'store']);
});

Route::post('/keluar', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::view('panduan', 'admin.guide')->name('guide');

    Route::get('impor', [AdminImportController::class, 'index'])->name('import.index');
    Route::post('impor', [AdminImportController::class, 'store'])->name('import.store');
    Route::post('impor-bguide', [AdminImportController::class, 'importBguide'])->name('import.bguide');

    Route::resource('buku', AdminBookController::class)
        ->except(['show'])
        ->parameters(['buku' => 'book']);
});
