@if ($paginator->hasPages())
    <nav class="portal-pagination" aria-label="Pagination">
        <p class="portal-pagination-summary">
            Showing <strong>{{ $paginator->firstItem() }}</strong> to <strong>{{ $paginator->lastItem() }}</strong>
            of <strong>{{ $paginator->total() }}</strong> results
        </p>
        <ul class="portal-pagination-links">
            <li>
                @if ($paginator->onFirstPage())
                    <span class="portal-page disabled" aria-disabled="true">&lsaquo; Previous</span>
                @else
                    <a class="portal-page" href="{{ $paginator->previousPageUrl() }}" rel="prev">&lsaquo; Previous</a>
                @endif
            </li>
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="portal-page-number"><span class="portal-page disabled">{{ $element }}</span></li>
                @elseif (is_array($element))
                    @foreach ($element as $page => $url)
                        <li class="portal-page-number">
                            @if ($page == $paginator->currentPage())
                                <span class="portal-page current" aria-current="page">{{ $page }}</span>
                            @else
                                <a class="portal-page" href="{{ $url }}" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach
            <li>
                @if ($paginator->hasMorePages())
                    <a class="portal-page" href="{{ $paginator->nextPageUrl() }}" rel="next">Next &rsaquo;</a>
                @else
                    <span class="portal-page disabled" aria-disabled="true">Next &rsaquo;</span>
                @endif
            </li>
        </ul>
    </nav>
@endif
