@extends ('Index')

@section ('contents')
    @php $numEntries = count($photos) @endphp
    @if ($numEntries == 0)
        <div class="alert alert-info">Dit album is leeg.</div>
    @else
        {!! $model->notes !!}

        @if ($numEntries == 1)
            <p>Dit album bevat 1 foto. Klik op de verkleinde foto om een vergroting te zien.</p>
        @else
            <p>Dit album bevat {{ $numEntries }} foto's. Klik op de verkleinde foto's om een vergroting te zien.</p>
        @endif

        <div class="fotoalbum">
            @foreach ($photos as $photo)
            <figure class="fotobadge">

                <a href="/{{ $photo->getFullPath() }}" data-lightbox="{{ $model->name }}" data-hash="{{ $photo->hash }}"
                    @if ($photo->caption) data-title="{{ $photo->caption->caption }}" @endif>
                    @if (file_exists($photo->getThumbnailPath()))
                        <img class="thumb" alt="{{ $photo->filename }}" src="/fotoalbums/{{ $model->id }}thumbnails/{{ $photo->filename }}"/>
                    @else
                        <img class="thumb" alt="{{ $photo->filename }}" src="/fotoalbums/{{ $model->id }}/{{ $photo->filename }}" style="width:270px; height:200px"/>
                    @endif
                </a>

                @if ($isAdmin)
                    <br/>
                    @php $captionId = $photo->caption->id ?? 0 @endphp
                    {!! new \Cyndaron\Widget\Button('edit', "/editor/photo/#{captionId}/#{photo.hash}", 'Bijschrift bewerken', 'Bijschrift bewerken', 16) !!}
                @endif
            </figure>
            @endforeach
        </div>

    @endif
@endsection