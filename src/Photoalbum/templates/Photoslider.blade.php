@php $htmlId = 'slider' . $album->id @endphp

<div id="{{ $htmlId }}Indicators" class="carousel slide" data-ride="carousel">
    <ol class="carousel-indicators">
        @foreach ($photos as $photo)
            <li data-target="#{{ $htmlId }}Indicators" data-slide-to="{{ $loop->index }}" @if ($loop->first) class="active" @endif></li>
        @endforeach
    </ol>
    <div class="carousel-inner">
        @php /** @var \Cyndaron\Photoalbum\Photo[] $photos */@endphp
        @foreach ($photos as $photo)
            @php $caption = $photo->caption @endphp
            @php $captionText = $caption ? trim($caption->caption) : '' @endphp
            @php $url = $photo->getUrl(); @endphp

            <div class="carousel-item @if ($loop->first) active @endif">
                @if ($url)
                    <a href="{!! $url !!}">
                        <img class="d-block w-100" src="{{ \Cyndaron\Util\Util::filenameToUrl($photo->getFullPath()) }}" alt="">
                    </a>
                @else
                    <img class="d-block w-100" src="{{ \Cyndaron\Util\Util::filenameToUrl($photo->getFullPath()) }}" alt="">
                    @if ($captionText)
                        <div class="carousel-caption d-none d-md-block">
                            {!! $captionText !!}
                        </div>
                    @endif
                @endif
            </div>
        @endforeach
    </div>
    <a class="carousel-control-prev" href="#{{ $htmlId }}Indicators" role="button" data-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="sr-only">Vorige</span>
    </a>
    <a class="carousel-control-next" href="#{{ $htmlId }}Indicators" role="button" data-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="sr-only">Volgende</span>
    </a>
</div>
