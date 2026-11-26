<?php

namespace Tests\Feature;

use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_lists_published_books(): void
    {
        $book = Book::factory()->create();

        $this->get('/')
            ->assertOk()
            ->assertSee($book->title)
            ->assertSee('s-l1200-transparent.png')
            ->assertSee('Digital Archive books')
            ->assertSee('Tampilkan seri Harvest Moon')
            ->assertDontSee('Rak Buku Bokujou')
            ->assertDontSee('Koleksi Digital')
            ->assertDontSee('Digital Archive Books Hero Banner');
    }

    public function test_asset_urls_use_https_when_request_is_forwarded_from_vercel(): void
    {
        $this->withHeaders([
            'X-Forwarded-Host' => 'archive.test',
            'X-Forwarded-Proto' => 'https',
        ])->get('/')
            ->assertOk()
            ->assertSee('https://archive.test/build/assets/', false);
    }

    public function test_shelves_show_three_books_on_mobile_and_five_on_desktop(): void
    {
        foreach (range(1, 7) as $number) {
            Book::factory()->create(['title' => 'Panduan Rak '.$number]);
        }

        $response = $this->get('/')->assertOk();
        $html = $response->getContent();

        $this->assertSame(3, substr_count($html, 'shelf-row--mobile'));
        $this->assertSame(2, substr_count($html, 'shelf-row--desktop'));
    }

    public function test_homepage_separates_harvest_moon_and_story_of_seasons_filters(): void
    {
        $storyOfSeasons = Book::factory()->create([
            'title' => 'Trio of Towns official guide',
            'series' => 'Story of Seasons: Trio of Towns',
            'platform' => 'Nintendo 3DS',
        ]);
        $harvestMoon = Book::factory()->create([
            'title' => 'A New Beginning official guide',
            'series' => 'A New Beginning',
            'platform' => 'Nintendo 3DS',
        ]);
        $runeFactory = Book::factory()->create([
            'title' => 'Rune Factory 4 official guide',
            'series' => 'Rune Factory',
            'platform' => 'Nintendo 3DS',
        ]);

        $this->get(route('home', ['franchise' => 'story-of-seasons']))
            ->assertOk()
            ->assertSee($storyOfSeasons->title)
            ->assertDontSee($harvestMoon->title)
            ->assertDontSee($runeFactory->title);

        $this->get(route('home', ['franchise' => 'harvest-moon']))
            ->assertOk()
            ->assertSee($harvestMoon->title)
            ->assertDontSee($storyOfSeasons->title)
            ->assertDontSee($runeFactory->title);
    }

    public function test_homepage_filters_books_by_each_platform_in_multi_platform_values(): void
    {
        $gameCubeGuide = Book::factory()->create([
            'title' => 'Friends of Mineral Town and Wonderful Life guide',
            'platform' => 'Game Boy Advance / Nintendo GameCube',
        ]);
        $otherGuide = Book::factory()->create([
            'title' => 'Game Boy Color guide',
            'platform' => 'Game Boy Color',
        ]);

        $this->get(route('home', ['platform' => 'Nintendo GameCube']))
            ->assertOk()
            ->assertSee($gameCubeGuide->title)
            ->assertDontSee($otherGuide->title);
    }

    public function test_catalog_can_be_searched_by_title(): void
    {
        $target = Book::factory()->create(['title' => 'Panduan Rahasia Mineral Town']);
        $other = Book::factory()->create(['title' => 'Catatan Ladang Biasa', 'series' => 'Back to Nature']);

        $this->get('/katalog?q=Mineral')
            ->assertOk()
            ->assertSee($target->title)
            ->assertDontSee($other->title);
    }

    public function test_book_detail_page_is_reachable_by_slug(): void
    {
        $book = Book::factory()->create();

        $this->get(route('books.show', $book))
            ->assertOk()
            ->assertSee($book->title)
            ->assertSee('Kembali ke rak')
            ->assertDontSee('Cari panduan');
    }

    public function test_draft_books_are_hidden_from_visitors(): void
    {
        $draft = Book::factory()->draft()->create(['title' => 'Buku Draf Tersembunyi']);

        $this->get('/katalog')->assertDontSee($draft->title);
        $this->get(route('books.show', $draft))->assertNotFound();
        $this->get(route('books.read', $draft))->assertNotFound();
    }

    public function test_reader_page_requires_a_readable_source(): void
    {
        $book = Book::factory()->create([
            'source' => 'external',
            'archive_id' => null,
            'external_url' => 'https://drive.google.com/file/d/unreadable-guide/view',
            'file_path' => null,
        ]);

        $this->get(route('books.read', $book))->assertNotFound();

        $readable = Book::factory()->create();

        $this->get(route('books.read', $readable))
            ->assertOk()
            ->assertSee('Kembali ke rak')
            ->assertDontSee('Sumber asli');
    }

    public function test_download_is_blocked_when_not_allowed(): void
    {
        $book = Book::factory()->create(['allow_download' => false]);

        $this->get(route('books.download', $book))->assertForbidden();
    }
}
