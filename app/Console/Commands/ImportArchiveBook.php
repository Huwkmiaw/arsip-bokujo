<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Services\ArchiveOrg;
use Illuminate\Console\Command;

class ImportArchiveBook extends Command
{
    protected $signature = 'arsip:impor
        {identifier?* : Identifier atau tautan item Archive.org}
        {--search : Impor seluruh hasil pencarian koleksi guide book}
        {--refresh : Perbarui metadata semua buku yang sudah terhubung ke Archive.org}
        {--limit=26 : Jumlah maksimum item ketika memakai --search}';

    protected $description = 'Tarik metadata buku dari Archive.org ke katalog lokal';

    public function handle(ArchiveOrg $archive): int
    {
        /** @var array<int, string> $identifiers */
        $identifiers = $this->argument('identifier');

        if ($this->option('refresh')) {
            $identifiers = array_merge(
                $identifiers,
                Book::query()->whereNotNull('archive_id')->pluck('archive_id')->all(),
            );
        }

        if ($this->option('search')) {
            $this->info('Mencari guide book di koleksi Archive.org...');

            $results = $archive->search((int) $this->option('limit'));
            $identifiers = array_merge($identifiers, array_column($results, 'identifier'));
        }

        if ($identifiers === []) {
            $this->warn('Tidak ada identifier. Contoh: php artisan arsip:impor bokujo-monogatari-hyper-guide-book');

            return self::FAILURE;
        }

        $imported = 0;

        foreach (array_unique($identifiers) as $identifier) {
            $book = $archive->import($identifier);

            if ($book === null) {
                $this->error('Gagal  '.$identifier);

                continue;
            }

            $this->line('OK     '.$book->title.'  ['.$book->archive_id.']');
            $imported++;
        }

        $this->info('Selesai. '.$imported.' buku tersimpan di katalog.');

        return self::SUCCESS;
    }
}
