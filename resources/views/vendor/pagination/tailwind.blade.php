@if ($paginator->hasPages())
<nav role="navigation" aria-label="Pagination" style="display: flex; align-items: center; justify-content: center; gap: 4px;">

    {{-- Previous --}}
    @if ($paginator->onFirstPage())
        <span style="padding: 6px 10px; font-size: 12px; color: var(--ink-4); border: 1px solid var(--rule-2); cursor: not-allowed;">
            <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" style="vertical-align: middle;"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
        </span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" style="padding: 6px 10px; font-size: 12px; color: var(--ink-2); border: 1px solid var(--rule); text-decoration: none; background: var(--paper);">
            <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" style="vertical-align: middle;"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
        </a>
    @endif

    {{-- Pages --}}
    @foreach ($elements as $element)
        @if (is_string($element))
            <span style="padding: 6px 10px; font-size: 12px; color: var(--ink-4);">{{ $element }}</span>
        @endif

        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span style="padding: 6px 12px; font-size: 12px; font-weight: 600; color: var(--cream); background: var(--forest); border: 1px solid var(--forest);">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" style="padding: 6px 12px; font-size: 12px; color: var(--ink-2); border: 1px solid var(--rule); text-decoration: none; background: var(--paper);">{{ $page }}</a>
                @endif
            @endforeach
        @endif
    @endforeach

    {{-- Next --}}
    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" rel="next" style="padding: 6px 10px; font-size: 12px; color: var(--ink-2); border: 1px solid var(--rule); text-decoration: none; background: var(--paper);">
            <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" style="vertical-align: middle;"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
        </a>
    @else
        <span style="padding: 6px 10px; font-size: 12px; color: var(--ink-4); border: 1px solid var(--rule-2); cursor: not-allowed;">
            <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" style="vertical-align: middle;"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
        </span>
    @endif

</nav>
@endif
