@php /** @var \Cyndaron\Url\UrlService $urlService */ @endphp
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        @if (!empty($icon)) <span class="glyphicon glyphicon-{{ $icon }}"></span> @endif {{ $title }}
    </a>
    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
        @php /** @var \Cyndaron\Util\Link[] $items */ @endphp
        @foreach ($items as $item)
            @if ($item->link)
                <a class="dropdown-item" href="{{ $urlService->toFriendly($item->link) }}">
                    @if ($item instanceof \Cyndaron\Util\LinkWithIcon)<span class="glyphicon glyphicon-{{ $item->icon }}"></span>&nbsp; @endif{{ $item->name }}
                </a>
            @else
                <span class="dropdown-item">
                    @if ($item instanceof \Cyndaron\Util\LinkWithIcon)<span class="glyphicon glyphicon-{{ $item->icon }}"></span>&nbsp; @endif<i>{{ $item->name }}</i>
                </span>
            @endif
        @endforeach
    </div>
</li>
