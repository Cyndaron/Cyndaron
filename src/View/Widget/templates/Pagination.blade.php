@if ((int)$numPages > 1)
    <div class="lettermenu">
        <ul class="pagination">
            @php $lastPageNum = 0 @endphp
            @foreach (\Cyndaron\View\Template\ViewHelpers::determinePages($numPages, $currentPage) as $i)
                @if ($i > $numPages)
                    @break;
                @endif

                @if ($i < 1)
                    @continue;
                @endif

                @if ($lastPageNum !== $i - 1)
                    <li><span>...</span></li>
                @endif

                @php $class = $i === $currentPage ? 'class="active"' : '' @endphp
                <li {{ $class }}><a href="{{ $link }}{{ $i + $offset }}">{{ $i }}</a></li>

                @php $lastPageNum = $i @endphp
            @endforeach
        </ul>
    </div>
@endif
