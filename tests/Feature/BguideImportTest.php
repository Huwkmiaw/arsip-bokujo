<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BguideImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_imports_archive_and_drive_books_and_skips_existing_books(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'bguide.vercel.app/api/blog/Official*' => Http::response(<<<'HTML'
                <h1>Nintendo DS</h1>
                <h2>Harvest Moon DS</h2>
                <ol>
                    <li>1. Existing Official Guide
                        <ul><li><input checked disabled type="checkbox"> [IA] (<a href="https://archive.org/details/guide-already-there">https://archive.org/details/guide-already-there</a>)</li></ul>
                    </li>
                    <li>2. New Official Guide
                        <ul>
                            <li><input checked disabled type="checkbox"> [IA] (<a href="https://archive.org/details/new-official-guide">https://archive.org/details/new-official-guide</a>)</li>
                            <li><input disabled type="checkbox"> [GD] (<a href="https://drive.google.com/file/d/new-guide-id/view?usp=drive_link">https://drive.google.com/file/d/new-guide-id/view?usp=drive_link</a>)</li>
                        </ul>
                    </li>
                    <li>4. Guide Without Online Copy</li>
                    <li>GD - Reupload</li>
                    <li>Harvest Moon Special Comic Pete Side</li>
                    <li><a href="https://archive.org/details/hmspecial-comic/HMSpecialComicChapter0-3/">IA</a></li>
                </ol>
                HTML),
            'bguide.vercel.app/api/blog/Rune*' => Http::response(<<<'MARKDOWN'
                # Rune Factory Guide
                Rune Factory 1 Perfect Guide
                * [GD](https://drive.google.com/file/d/rune-factory-1/view?usp=drive_link)
                MARKDOWN),
        ]);

        $admin = User::factory()->create(['is_admin' => true]);
        Book::factory()->create([
            'title' => 'Existing Official Guide',
            'archive_id' => 'guide-already-there',
            'series' => 'Harvest Moon DS',
            'platform' => 'Nintendo DS',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.import.index'))
            ->assertOk()
            ->assertSee('Impor koleksi Bguide')
            ->assertSee('Mengimpor seluruh katalog Bguide...')
            ->assertSee('Mengambil metadata dan mengimpor buku...')
            ->assertSee('Tarik metadata &amp; impor buku', false)
            ->assertSee('data-loading-form', false)
            ->assertSee('Ini bukan pratinjau saja.')
            ->assertSee(route('admin.import.bguide'));

        $response = $this->actingAs($admin)->post(route('admin.import.bguide'));

        $response->assertRedirect(route('admin.import.index'));
        $response->assertSessionHas('status', 'Impor Bguide selesai: 3 buku ditambahkan, 1 duplikat dilewati.');

        $this->assertDatabaseHas('books', [
            'title' => 'New Official Guide',
            'archive_id' => 'new-official-guide',
            'external_url' => null,
            'series' => 'Harvest Moon DS',
            'platform' => 'Nintendo DS',
            'source' => 'archive',
        ]);
        $this->assertDatabaseHas('books', [
            'title' => 'Rune Factory 1 Perfect Guide',
            'archive_id' => null,
            'external_url' => 'https://drive.google.com/file/d/rune-factory-1/view?usp=drive_link',
            'series' => 'Rune Factory',
            'platform' => 'Nintendo DS',
            'source' => 'external',
        ]);
        $this->assertDatabaseHas('books', [
            'title' => 'Harvest Moon Special Comic Pete Side',
            'archive_id' => 'hmspecial-comic',
            'series' => 'Harvest Moon DS',
            'platform' => 'Nintendo DS',
        ]);
        $this->assertDatabaseMissing('books', ['title' => 'Guide Without Online Copy']);
        $this->assertDatabaseCount('books', 4);

        $driveBook = Book::query()->where('external_url', 'like', 'https://drive.google.com/%')->firstOrFail();

        $this->get(route('books.show', $driveBook))
            ->assertOk()
            ->assertSee('Buka sumber eksternal')
            ->assertSee($driveBook->external_url);

        $this->actingAs($admin)
            ->post(route('admin.import.bguide'))
            ->assertRedirect(route('admin.import.index'))
            ->assertSessionHas('status', 'Impor Bguide selesai: 0 buku ditambahkan, 4 duplikat dilewati.');

        $this->assertDatabaseCount('books', 4);

        Http::assertSent(fn (Request $request): bool => str_contains($request->url(), 'Official%20and%20Fan')
            || str_contains($request->url(), 'Rune%20Factory%20Guide')
        );
    }

    public function test_bguide_import_assigns_correct_platforms_to_story_of_seasons_and_rune_factory(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'bguide.vercel.app/api/blog/Official*' => Http::response(<<<'MARKDOWN'
                # 3DS
                ## Story of Seasons Trio of Town

                1. Trio of Towns Official Guide
                * [IA](https://archive.org/details/trio-of-towns-guide)

                # Switch
                ## Harvest Moon Light of Hope

                1. Light of Hope Official Guide
                * [IA](https://archive.org/details/light-of-hope-guide)
                MARKDOWN),
            'bguide.vercel.app/api/blog/Rune*' => Http::response(<<<'MARKDOWN'
                # Rune Factory Guide

                Rune Factory 4 Perfect Guide
                * [GD](https://drive.google.com/file/d/rune-factory-4/view?usp=drive_link)
                MARKDOWN),
        ]);
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.import.bguide'))
            ->assertRedirect(route('admin.import.index'))
            ->assertSessionHas('status', 'Impor Bguide selesai: 3 buku ditambahkan, 0 duplikat dilewati.');

        $this->assertDatabaseHas('books', [
            'title' => 'Trio of Towns Official Guide',
            'series' => 'Story of Seasons: Trio of Towns',
            'platform' => 'Nintendo 3DS',
        ]);
        $this->assertDatabaseHas('books', [
            'title' => 'Rune Factory 4 Perfect Guide',
            'series' => 'Rune Factory',
            'platform' => 'Nintendo 3DS',
        ]);
        $this->assertDatabaseHas('books', [
            'title' => 'Light of Hope Official Guide',
            'platform' => 'Nintendo Switch',
        ]);
    }

    public function test_non_admin_cannot_import_bguide_books(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->post(route('admin.import.bguide'))
            ->assertForbidden();

        $this->assertDatabaseCount('books', 0);
    }

    public function test_bguide_fetch_errors_are_reported_without_importing_books(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'bguide.vercel.app/api/blog/Official*' => Http::response([], 503),
        ]);

        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.import.bguide'))
            ->assertRedirect(route('admin.import.index'))
            ->assertSessionHasErrors('bguide');

        $this->assertDatabaseCount('books', 0);
    }

    public function test_archive_org_import_creates_one_book_from_its_metadata(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://archive.org/metadata/*' => Http::response([
                'metadata' => [
                    'title' => 'Panduan dari Archive.org',
                    'language' => 'eng',
                    'imagecount' => '128',
                    'description' => 'Panduan resmi.',
                ],
                'files' => [],
            ]),
        ]);
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.import.store'), [
            'identifier' => 'https://archive.org/details/example-guide',
        ]);
        $book = Book::query()->where('archive_id', 'example-guide')->firstOrFail();

        $response
            ->assertRedirect(route('admin.buku.edit', $book))
            ->assertSessionHas('status', 'Metadata "Panduan dari Archive.org" berhasil diimpor dari Archive.org. Periksa dan sunting datanya sebelum selesai.');

        $this->assertSame('archive', $book->source);
        $this->assertSame(128, $book->pages);
    }

    public function test_archive_org_import_rejects_google_drive_links_with_an_explanation(): void
    {
        Http::preventStrayRequests();
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.import.store'), [
            'identifier' => 'https://drive.google.com/file/d/example-guide/view',
        ])->assertSessionHasErrors([
            'identifier' => 'Masukkan identifier atau tautan Archive.org. Tautan Google Drive ditambahkan melalui Tambah manual.',
        ]);

        $this->assertDatabaseCount('books', 0);
    }
}
