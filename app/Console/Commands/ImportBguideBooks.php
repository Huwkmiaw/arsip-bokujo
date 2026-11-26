<?php

namespace App\Console\Commands;

use App\Services\BguideCatalog;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('arsip:impor-bguide')]
#[Description('Impor koleksi Bokumono dan Rune Factory dari Bguide')]
class ImportBguideBooks extends Command
{
    public function handle(BguideCatalog $catalog): int
    {
        $result = $catalog->import();

        $this->info("Selesai: {$result['added']} buku ditambahkan, {$result['skipped']} duplikat dilewati.");

        return self::SUCCESS;
    }
}
