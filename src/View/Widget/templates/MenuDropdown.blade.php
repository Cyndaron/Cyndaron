@php /** @var \Cyndaron\Url\UrlService $urlService */ @endphp
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        @if (!empty($icon)) @include('View/Widget/Icon', ['type' => $icon]) @endif {{ $title }}
    </a>
    <ul class="dropdown-menu">
        @php /** @var \Cyndaron\Util\Link[] $items */ @endphp
        @foreach ($items as $item)
            <li>
                @if ($item->link)
                    <a class="dropdown-item" href="{{ $urlService->toFriendly($item->link) }}">
                        @if ($item instanceof \Cyndaron\Util\LinkWithIcon)@include('View/Widget/Icon', ['type' => $item->icon])&nbsp; @endif{{ $item->name }}
                    </a>
                @else
                    <span class="dropdown-item">
                    @if ($item instanceof \Cyndaron\Util\LinkWithIcon)@include('View/Widget/Icon', ['type' => $item->icon])&nbsp; @endif<i>{{ $item->name }}</i>
                </span>
                @endif
            </li>
        @endforeach
    </ul>
</li>
