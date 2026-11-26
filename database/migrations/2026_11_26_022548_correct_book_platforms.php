<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('books')
            ->where('archive_id', 'bokujou-monogatari-2-official-guide-book')
            ->update([
                'platform' => 'Nintendo 64',
                'year' => 1999,
                'description' => 'Panduan resmi Harvest Moon 64 untuk Nintendo 64: perbandingan lahan, daftar benih, dan cara efisien membangun peternakan besar.',
            ]);

        DB::table('books')
            ->where('archive_id', 'bokujou-monogatari-2-happiness-strategy-book')
            ->update([
                'platform' => 'Nintendo 64',
                'year' => 1999,
                'description' => 'Buku strategi alternatif untuk Harvest Moon 64 di Nintendo 64, berfokus pada efisiensi harian dan target pendapatan.',
            ]);

        DB::table('books')
            ->where('archive_id', 'bokujou-monogatari-3-complete-strategy-guide')
            ->update([
                'platform' => 'PlayStation 2',
                'year' => 2001,
                'description' => 'Panduan lengkap Harvest Moon: Save the Homeland untuk PlayStation 2.',
            ]);

        DB::table('books')
            ->where('archive_id', 'bokujo-monogatari-mittsu-no-sato-no-taisetsuna-tomodachi-official-perfect-guide')
            ->update([
                'series' => 'Story of Seasons: Trio of Towns',
                'platform' => 'Nintendo 3DS',
                'title_jp' => '牧場物語 3つの里の大切な友だち',
                'year' => 2016,
                'description' => 'Panduan resmi Story of Seasons: Trio of Towns untuk Nintendo 3DS.',
            ]);

        DB::table('books')
            ->where('archive_id', 'bokujou-monogatari-friends-of-mineral-town-wonderful-life-variety-book')
            ->update(['platform' => 'Game Boy Advance / Nintendo GameCube']);

        DB::table('books')
            ->where('archive_id', 'primagames-eguides')
            ->update(['platform' => 'Nintendo Switch']);

        DB::table('books')
            ->whereIn('title', [
                'Rune Factory 1 Perfect Guide',
                'Rune Factory 2 Perfect Guide',
                'Rune Factory 3 Perfect Guide',
            ])
            ->update(['platform' => 'Nintendo DS']);

        DB::table('books')
            ->where('title', 'Rune Factory 4 Perfect Guide')
            ->update(['platform' => 'Nintendo 3DS']);

        DB::table('books')
            ->where('title', 'Rune Factory Frontier Perfect Guide')
            ->update(['platform' => 'Wii']);

        DB::table('books')
            ->where('title', 'Rune Factory Oceans/Tides of Destiny Visual Book')
            ->update(['platform' => 'Wii / PlayStation 3']);

        DB::table('books')
            ->whereIn('platform', ['PS1', 'PlayStation 1'])
            ->update(['platform' => 'PlayStation']);

        DB::table('books')
            ->where('platform', 'Switch')
            ->update(['platform' => 'Nintendo Switch']);
    }

    public function down(): void
    {
        DB::table('books')
            ->where('archive_id', 'bokujou-monogatari-2-official-guide-book')
            ->update([
                'platform' => 'Super Famicom',
                'year' => 1995,
                'description' => 'Panduan resmi sekuel Super Famicom: perbandingan lahan, daftar benih, dan cara efisien membangun peternakan besar.',
            ]);

        DB::table('books')
            ->where('archive_id', 'bokujou-monogatari-2-happiness-strategy-book')
            ->update([
                'platform' => 'Super Famicom',
                'year' => 1995,
                'description' => 'Buku strategi alternatif untuk Bokujou Monogatari 2, berfokus pada efisiensi harian dan target pendapatan.',
            ]);

        DB::table('books')
            ->where('archive_id', 'bokujou-monogatari-3-complete-strategy-guide')
            ->update([
                'platform' => 'Super Famicom',
                'year' => 1996,
                'description' => 'Panduan lengkap Bokujou 3 dengan peta wilayah, daftar ikan, dan tabel nilai jual seluruh hasil panen.',
            ]);

        DB::table('books')
            ->where('archive_id', 'bokujo-monogatari-mittsu-no-sato-no-taisetsuna-tomodachi-official-perfect-guide')
            ->update([
                'series' => 'Twin Villages',
                'platform' => 'Nintendo DS',
                'title_jp' => '牧場物語 ふたごの村 公式パーフェクトガイド',
                'year' => 2008,
                'description' => 'Panduan perfect untuk Twin Villages: peta dua desa, daftar penduduk, dan tabel tunnel penghubung.',
            ]);

        DB::table('books')
            ->where('archive_id', 'bokujou-monogatari-friends-of-mineral-town-wonderful-life-variety-book')
            ->update(['platform' => 'Game Boy Advance']);

        DB::table('books')
            ->where('archive_id', 'primagames-eguides')
            ->update(['platform' => 'Switch']);

        DB::table('books')
            ->whereIn('title', [
                'Rune Factory 1 Perfect Guide',
                'Rune Factory 2 Perfect Guide',
                'Rune Factory 3 Perfect Guide',
                'Rune Factory 4 Perfect Guide',
                'Rune Factory Frontier Perfect Guide',
                'Rune Factory Oceans/Tides of Destiny Visual Book',
            ])
            ->update(['platform' => null]);

        DB::table('books')
            ->whereIn('title', [
                'Bokujou Monogatari: Harvest Moon Strategy Guide Koei',
                'Bokujou Monogatari: Harvest Moon Proffesional Guide Koie',
                'Bokujo Monogatari Harvest Moon Special Comic Pete Side',
                'Bokujo Monogatari Harvest Moon Special Comic Claire Side',
            ])
            ->update(['platform' => 'PS1']);

        DB::table('books')
            ->where('archive_id', 'bokujou-monogatari-friends-of-mineral-town-switch')
            ->update(['platform' => 'Switch']);
    }
};
