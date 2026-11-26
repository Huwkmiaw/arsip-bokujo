<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('books')
            ->where('archive_id', 'primagames-eguides')
            ->update(['platform' => 'Nintendo Switch']);
    }

    public function down(): void
    {
        DB::table('books')
            ->where('archive_id', 'primagames-eguides')
            ->update(['platform' => 'PC / PlayStation 4 / Nintendo Switch']);
    }
};
