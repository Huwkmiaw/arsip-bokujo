<?php

namespace Database\Factories;

use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    public function definition(): array
    {
        $title = 'Bokujou Monogatari '.fake()->unique()->words(3, true);

        return [
            'title' => $title,
            'title_jp' => null,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 99999),
            'series' => 'Friends of Mineral Town',
            'platform' => 'Game Boy Advance',
            'year' => 2003,
            'language' => 'jpn',
            'publisher' => null,
            'description' => fake()->sentence(12),
            'pages' => 132,
            'source' => 'archive',
            'archive_id' => 'uji-'.fake()->unique()->slug(3),
            'cover_url' => null,
            'cover_path' => null,
            'file_path' => null,
            'file_size' => null,
            'allow_download' => true,
            'is_featured' => false,
            'status' => 'published',
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (): array => ['status' => 'draft']);
    }

    public function uploaded(): static
    {
        return $this->state(fn (): array => [
            'source' => 'upload',
            'archive_id' => null,
            'file_path' => 'books/contoh.pdf',
            'file_size' => 1024,
        ]);
    }
}
