<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Services\ArchiveOrg;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ArchiveOrgImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_reimport_appends_only_explicit_scan_credit_to_curated_description(): void
    {
        $book = Book::factory()->create([
            'archive_id' => 'guide-with-scan-credit',
            'description' => 'Deskripsi kurasi admin.',
        ]);

        Http::fake([
            'https://archive.org/metadata/*' => Http::response([
                'metadata' => [
                    'title' => $book->title,
                    'language' => 'jpn',
                    'imagecount' => '120',
                    'description' => 'Deskripsi metadata sumber.',
                    'scanned_by' => 'Relawan Arsip',
                    'scanner' => 'Internet Archive HTML5 Uploader 1.7.0',
                    'uploader' => 'uploader@example.com',
                ],
                'files' => [],
            ]),
        ]);

        app(ArchiveOrg::class)->import('guide-with-scan-credit');

        $this->assertSame(
            'Deskripsi kurasi admin. Pemindai: Relawan Arsip',
            $book->refresh()->description,
        );
    }
}
