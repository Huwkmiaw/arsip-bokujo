@props(['book'])

<a href="{{ route('books.show', $book) }}" title="{{ $book->title }}" aria-label="{{ $book->title }}"
   class="book-hover group block">
    <div class="cover-shadow relative aspect-[3/4] overflow-hidden rounded-[3px] border border-ink/10 bg-paper-deep">
        <x-book-cover :book="$book" />

        {{-- kesan punggung buku --}}
        <span class="pointer-events-none absolute inset-y-0 left-0 w-[7px] bg-gradient-to-r from-black/30 to-transparent opacity-70"></span>

        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-ink/90 via-ink/45 to-transparent p-2 pt-7 opacity-0 transition-opacity duration-300 group-hover:opacity-100">
            <p class="line-clamp-2 text-[10px] leading-tight font-medium text-paper">{{ $book->title }}</p>
            <p class="mt-0.5 text-[9px] text-paper/70">{{ $book->year ?? $book->languageLabel() }}</p>
        </div>
    </div>
</a>
