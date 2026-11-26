<?php

namespace Tests\Unit;

use App\Services\ArchiveOrg;
use PHPUnit\Framework\TestCase;

class ArchiveOrgMetadataTest extends TestCase
{
    public function test_it_extracts_identifier_from_various_url_shapes(): void
    {
        $service = new ArchiveOrg;

        $this->assertSame(
            'bokujo-monogatari-hyper-guide-book',
            $service->identifierFromUrl('https://archive.org/details/bokujo-monogatari-hyper-guide-book'),
        );

        $this->assertSame(
            'bokujo-monogatari-hyper-guide-book',
            $service->identifierFromUrl('https://archive.org/details/bokujo-monogatari-hyper-guide-book/page/n9/mode/2up'),
        );

        $this->assertSame('hmgb-3', $service->identifierFromUrl('  hmgb-3  '));
    }

    public function test_it_normalizes_metadata_into_book_attributes(): void
    {
        $service = new ArchiveOrg;

        $attributes = $service->normalize([
            'metadata' => [
                'title' => 'Bokujo Monogatari Hyper Guide Book',
                'language' => 'jpn',
                'date' => '1996-01-01',
                'imagecount' => '164',
                'description' => '<p>Panduan <b>lengkap</b>.</p>',
                'scanner' => 'Internet Archive HTML5 Uploader 1.7.0',
                'uploader' => 'uploader@example.com',
                'access-restricted-item' => 'true',
            ],
            'files' => [
                ['format' => 'Image Container PDF', 'size' => '1048576', 'name' => 'buku.pdf'],
                ['format' => 'Djvu XML', 'size' => '2048', 'name' => 'buku_djvu.xml'],
            ],
        ], 'bokujo-monogatari-hyper-guide-book');

        $this->assertSame('Bokujo Monogatari Hyper Guide Book', $attributes['title']);
        $this->assertSame(1996, $attributes['year']);
        $this->assertSame('jpn', $attributes['language']);
        $this->assertSame(164, $attributes['pages']);
        $this->assertSame('Panduan lengkap.', $attributes['description']);
        $this->assertFalse($attributes['allow_download']);
        $this->assertSame(1048576, $attributes['file_size']);
        $this->assertSame(
            'https://archive.org/services/img/bokujo-monogatari-hyper-guide-book',
            $attributes['cover_url'],
        );
    }

    public function test_it_adds_a_scan_credit_only_from_an_explicit_credit_field(): void
    {
        $service = new ArchiveOrg;

        $attributes = $service->normalize([
            'metadata' => [
                'title' => 'Panduan Komunitas',
                'description' => 'Panduan hasil pemindaian.',
                'scan_credit' => 'Komunitas Arsip',
                'scanner' => 'Internet Archive HTML5 Uploader 1.7.0',
                'uploader' => 'uploader@example.com',
            ],
            'files' => [],
        ], 'panduan-komunitas');

        $this->assertSame(
            'Panduan hasil pemindaian. Pemindai: Komunitas Arsip',
            $attributes['description'],
        );
    }

    public function test_it_maps_english_language_and_allows_download_when_public(): void
    {
        $service = new ArchiveOrg;

        $attributes = $service->normalize([
            'metadata' => [
                'title' => 'Harvest Moon Back to Nature Prima Guide',
                'language' => 'English',
            ],
            'files' => [],
        ], 'contoh');

        $this->assertSame('eng', $attributes['language']);
        $this->assertTrue($attributes['allow_download']);
        $this->assertNull($attributes['year']);
    }
}
