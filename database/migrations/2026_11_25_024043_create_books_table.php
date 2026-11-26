<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('title_jp')->nullable();
            $table->string('slug')->unique();
            $table->string('series')->nullable()->index();
            $table->string('platform')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('language', 8)->default('jpn')->index();
            $table->string('publisher')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('pages')->nullable();
            $table->string('source', 16)->default('archive')->index();
            $table->string('archive_id')->nullable()->unique();
            $table->string('cover_url')->nullable();
            $table->string('cover_path')->nullable();
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->boolean('allow_download')->default(true);
            $table->boolean('is_featured')->default(false)->index();
            $table->string('status', 16)->default('published')->index();
            $table->unsignedBigInteger('views')->default(0);
            $table->unsignedBigInteger('downloads')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
