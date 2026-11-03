@if ($paginator->hasPages())
    <nav class="flex items-center justify-between gap-4" aria-label="Страницы">
        @if ($paginator->onFirstPage())
            <span class="btn btn-ghost pointer-events-none opacity-40">Назад</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-ghost">Назад</a>
        @endif

        <span class="font-display text-sm uppercase tracking-wider">Страница {{ $paginator->currentPage() }} из {{ $paginator->lastPage() }}</span>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-ghost">Вперёд</a>
        @else
            <span class="btn btn-ghost pointer-events-none opacity-40">Вперёд</span>
        @endif
    </nav>
@endif
