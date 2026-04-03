@if ($paginator->hasPages())
<nav class="neo-pagination" aria-label="Pagination">
    <ul>
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <li class="disabled" aria-disabled="true">
                <span><i class="bi bi-chevron-left"></i> Prev</span>
            </li>
        @else
            <li>
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"><i class="bi bi-chevron-left"></i> Prev</a>
            </li>
        @endif

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <li>
                <a href="{{ $paginator->nextPageUrl() }}" rel="next">Next <i class="bi bi-chevron-right"></i></a>
            </li>
        @else
            <li class="disabled" aria-disabled="true">
                <span>Next <i class="bi bi-chevron-right"></i></span>
            </li>
        @endif
    </ul>
</nav>
@endif
