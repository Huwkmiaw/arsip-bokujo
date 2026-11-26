@props(['books'])

@php
    $items = $books instanceof \Illuminate\Contracts\Pagination\Paginator
        ? $books->getCollection()
        : collect($books);
    $desktopRows = $items->chunk(5);
    $mobileRows = $items->chunk(3);
@endphp

<div class="bookcase">
    <div class="bookcase-cap"></div>

    <div class="hidden sm:block" data-shelf-layout="desktop">
        @foreach ($desktopRows as $row)
            <div class="shelf shelf-row--desktop">
                <div class="shelf-books grid grid-cols-5 gap-x-1 sm:gap-x-3 lg:gap-x-4">
                    @foreach ($row as $book)
                        <x-book-card :book="$book" />
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <div class="sm:hidden" data-shelf-layout="mobile">
        @foreach ($mobileRows as $row)
            <div class="shelf shelf--mobile shelf-row--mobile">
                <div class="shelf-books shelf-books--mobile grid grid-cols-3 gap-x-1">
                    @foreach ($row as $book)
                        <x-book-card :book="$book" />
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
