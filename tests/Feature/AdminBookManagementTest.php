<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminBookManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_non_admin_users_cannot_open_the_dashboard(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_admin_cannot_upload_a_local_pdf(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['is_admin' => true]);
        $upload = UploadedFile::fake()->create('panduan.pdf', 320, 'application/pdf');

        $this->actingAs($admin)->post(route('admin.buku.store'), [
            'title' => 'Panduan Uji Unggahan',
            'language' => 'idn',
            'status' => 'published',
            'external_url' => 'https://drive.google.com/file/d/panduan-test/view',
            'file' => $upload,
        ])
            ->assertSessionHasErrors([
                'file' => 'PDF/ZIP tidak dapat diunggah. Gunakan tautan Archive.org atau Google Drive.',
            ]);

        $this->assertDatabaseMissing('books', ['title' => 'Panduan Uji Unggahan']);
        Storage::disk('public')->assertDirectoryEmpty('books');
    }

    public function test_admin_must_provide_a_supported_book_source(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.buku.store'), [
            'title' => 'Panduan Tanpa Sumber',
            'language' => 'jpn',
            'status' => 'published',
        ])->assertSessionHasErrors([
            'archive_id' => 'Masukkan tautan Archive.org atau Google Drive.',
            'external_url' => 'Masukkan tautan Archive.org atau Google Drive.',
        ]);

        $this->assertDatabaseMissing('books', ['title' => 'Panduan Tanpa Sumber']);
    }

    public function test_admin_cannot_use_an_unapproved_external_source(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.buku.store'), [
            'title' => 'Panduan Sumber Tidak Didukung',
            'language' => 'jpn',
            'status' => 'published',
            'external_url' => 'https://example.com/panduan.pdf',
        ])->assertSessionHasErrors([
            'external_url' => 'Tautan eksternal harus berupa tautan file Google Drive yang dapat dibagikan.',
        ]);

        $this->assertDatabaseMissing('books', ['title' => 'Panduan Sumber Tidak Didukung']);
    }

    public function test_admin_must_choose_only_one_book_source(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.buku.store'), [
            'title' => 'Panduan Dua Sumber',
            'language' => 'jpn',
            'status' => 'published',
            'archive_id' => 'guide-from-archive',
            'external_url' => 'https://drive.google.com/file/d/guide-from-drive/view',
        ])->assertSessionHasErrors([
            'archive_id' => 'Pilih satu sumber saja: Archive.org atau Google Drive.',
            'external_url' => 'Pilih satu sumber saja: Archive.org atau Google Drive.',
        ]);

        $this->assertDatabaseMissing('books', ['title' => 'Panduan Dua Sumber']);
    }

    public function test_admin_can_create_a_book_from_an_archive_org_item_url(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.buku.store'), [
            'title' => 'Panduan Item Archive',
            'language' => 'jpn',
            'status' => 'published',
            'archive_id' => 'https://archive.org/details/guide-from-archive',
        ])->assertRedirect(route('admin.buku.index'));

        $book = Book::query()->where('title', 'Panduan Item Archive')->firstOrFail();

        $this->assertSame('archive', $book->source);
        $this->assertSame('guide-from-archive', $book->archive_id);
    }

    public function test_admin_can_replace_a_missing_archive_cover_with_a_manual_upload(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['is_admin' => true]);
        $book = Book::factory()->create([
            'cover_url' => 'https://archive.org/services/img/cover-not-available',
            'cover_path' => null,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.buku.edit', $book))
            ->assertOk()
            ->assertSee('Unggah sampul manual');

        $this->actingAs($admin)
            ->put(route('admin.buku.update', $book), [
                'title' => $book->title,
                'language' => $book->language,
                'status' => 'published',
                'archive_id' => $book->archive_id,
                'cover' => UploadedFile::fake()->image('manual-cover.webp', 600, 800),
            ])
            ->assertRedirect(route('admin.buku.index'));

        $book->refresh();

        Storage::disk('public')->assertExists($book->cover_path);
        $this->assertStringContainsString('/storage/covers/', $book->coverImageUrl());
    }

    public function test_external_books_use_their_source_link_instead_of_local_downloads(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $externalUrl = 'https://drive.google.com/file/d/external-book/view';

        $this->actingAs($admin)
            ->post(route('admin.buku.store'), [
                'title' => 'Panduan Sumber Eksternal',
                'language' => 'jpn',
                'status' => 'published',
                'external_url' => $externalUrl,
                'allow_download' => '1',
            ])
            ->assertRedirect(route('admin.buku.index'));

        $book = Book::query()->where('title', 'Panduan Sumber Eksternal')->firstOrFail();

        $this->assertSame('external', $book->source);
        $this->assertFalse($book->allow_download);
        $this->get(route('books.show', $book))
            ->assertOk()
            ->assertSee($externalUrl)
            ->assertDontSee('Unduh');
    }

    public function test_admin_can_import_metadata_by_archive_identifier(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.import.store'), [
                'identifier' => 'https://archive.org/details/bokujo-monogatari-hyper-guide-book',
            ])
            ->assertRedirect();

        $book = Book::query()->where('archive_id', 'bokujo-monogatari-hyper-guide-book')->first();

        if ($book === null) {
            $this->markTestSkipped('Archive.org tidak dapat dihubungi dari lingkungan test.');
        }

        $this->assertSame('archive', $book->source);
        $this->assertNotEmpty($book->title);
    }

    public function test_admin_can_open_the_quick_start_guide(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('admin.guide'))
            ->assertOk()
            ->assertSee('Panduan admin')
            ->assertSee('PDF/ZIP tidak diunggah ke server')
            ->assertSee('kredit pemindai');
    }

    public function test_deleting_a_book_removes_its_files(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['is_admin' => true]);
        $book = Book::factory()->uploaded()->create();
        Storage::disk('public')->put($book->file_path, 'isi');

        $this->actingAs($admin)
            ->delete(route('admin.buku.destroy', $book))
            ->assertRedirect(route('admin.buku.index'));

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
        Storage::disk('public')->assertMissing($book->file_path);
    }
}
