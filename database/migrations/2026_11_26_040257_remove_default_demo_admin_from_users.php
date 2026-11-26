<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('users')->where('email', 'admin@arsip.test')->delete();
    }

    public function down(): void
    {
        // Do not restore the publicly known demo administrator.
    }
};
