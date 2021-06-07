<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        @if (!empty($icon)) <span class="glyphicon glyphicon-{{ $icon }}"></span> @endif {{ $title }}
    </a>
    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
        @foreach ($items as $item)
            @if ($item['link'])
                <a class="dropdown-item" href="{{ $item['link'] }}">
                    @if (!empty($item['icon']))<span class="glyphicon glyphicon-{{ $item['icon'] }}"></span>&nbsp; @endif{{ $item['title'] }}
                </a>
            @else
                <span class="dropdown-item">
                    @if (!empty($item['icon']))<span class="glyphicon glyphicon-{{ $item['icon'] }}"></span>&nbsp; @endif<i>{{ $item['title'] }}</i>
                </span>
            @endif
        @endforeach
    </div>
</li>