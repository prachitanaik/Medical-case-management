@if ($paginator->hasPages())
    <nav class="pagination" role="navigation" aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <span class="disabled">&lsaquo; Previous</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev">&lsaquo; Previous</a>
        @endif

        <span>Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next">Next &rsaquo;</a>
        @else
            <span class="disabled">Next &rsaquo;</span>
        @endif
    </nav>
@endif
