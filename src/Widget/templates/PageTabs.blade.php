<ul class="nav nav-tabs">
    @foreach ($subPages as $link => $title)
    <li role="presentation" class="nav-item">
        <a class="nav-link @if ($link === $currentPage) active @endif" href="{{ $urlPrefix }}{{ rtrim($link, '/') }}">{{ $title }}</a>
    </li>
    @endforeach
</ul>