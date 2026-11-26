<?php

namespace App\Models;

use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Book extends Model
{
    /** @use HasFactory<BookFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'title_jp',
        'slug',
        'series',
        'platform',
        'year',
        'language',
        'publisher',
        'description',
        'pages',
        'source',
        'archive_id',
        'external_url',
        'cover_url',
        'cover_path',
        'file_path',
        'file_size',
        'allow_download',
        'is_featured',
        'status',
        'views',
        'downloads',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'pages' => 'integer',
            'file_size' => 'integer',
            'views' => 'integer',
            'downloads' => 'integer',
            'allow_download' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeForPlatform(Builder $query, string $platform): Builder
    {
        return $query->where(function (Builder $query) use ($platform): void {
            $query->where('platform', $platform)
                ->orWhere('platform', 'like', $platform.' / %')
                ->orWhere('platform', 'like', '% / '.$platform.' / %')
                ->orWhere('platform', 'like', '% / '.$platform);
        });
    }

    public function isFromArchive(): bool
    {
        return $this->source === 'archive' && filled($this->archive_id);
    }

    public function isReadable(): bool
    {
        return $this->isFromArchive() || filled($this->file_path);
    }

    public function coverImageUrl(): ?string
    {
        if (filled($this->cover_path)) {
            return Storage::disk('public')->url($this->cover_path);
        }

        if (filled($this->cover_url)) {
            return $this->cover_url;
        }

        if (filled($this->archive_id)) {
            return 'https://archive.org/services/img/'.$this->archive_id;
        }

        return null;
    }

    public function readerUrl(): ?string
    {
        if ($this->isFromArchive()) {
            return 'https://archive.org/embed/'.$this->archive_id;
        }

        if (filled($this->file_path)) {
            return Storage::disk('public')->url($this->file_path);
        }

        return null;
    }

    public function sourceUrl(): ?string
    {
        if ($this->isFromArchive()) {
            return 'https://archive.org/details/'.$this->archive_id;
        }

        return $this->external_url;
    }

    public function languageLabel(): string
    {
        return match ($this->language) {
            'jpn' => 'Jepang',
            'eng' => 'Inggris',
            'chi' => 'Mandarin',
            'idn' => 'Indonesia',
            default => strtoupper((string) $this->language),
        };
    }

    public function fileSizeLabel(): ?string
    {
        if (blank($this->file_size)) {
            return null;
        }

        $megabytes = $this->file_size / 1048576;

        return $megabytes >= 1024
            ? number_format($megabytes / 1024, 1).' GB'
            : number_format($megabytes, 1).' MB';
    }

    public static function makeSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'buku';
        $slug = $base;
        $counter = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->exists()
        ) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
