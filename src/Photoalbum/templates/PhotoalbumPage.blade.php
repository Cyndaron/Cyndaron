@extends ('Index')

@section ('titleControls')
    @if ($model->viewMode === 0)
        @include('View/Widget/Button', ['kind' => 'edit', 'link' => '/editor/photoalbum/' . $model->id, 'description' => 'Dit fotoalbum bewerken'])
    @endif
@endsection

@section ('contents')
    @if ($canUpload)
        <form method="post" action="/photoalbum/addPhoto/{{ $model->id }}" enctype="multipart/form-data" id="upload-photo">
            <label for="newFile">Foto toevoegen:</label>
            <input type="file" id="newFile" name="newFiles[]" multiple required>
            <input type="hidden" name="csrfToken" value="{{ \Cyndaron\User\User::getCSRFToken('photoalbum', 'addPhoto') }}">
            <input id="add-photo-submit" class="btn btn-primary" type="submit" value="Uploaden">
            <span id="upload-progress"></span>
        </form>
    @endif

    @php $numEntries = count($photos) @endphp
    @if ($numEntries === 0)
        <div class="alert alert-info">Dit album is leeg.</div>
    @else
        {!! $parsedNotes ?? '' !!}

        @if ($numEntries === 1)
            <p>Dit album bevat 1 foto. Klik op de verkleinde foto om een vergroting te zien.</p>
        @else
            <p>Dit album bevat {{ $numEntries }} foto's. Klik op de verkleinde foto's om een vergroting te zien.</p>
        @endif

        <div class="fotoalbum">
            @php $deleteToken = \Cyndaron\User\User::getCSRFToken('photoalbum', 'deletePhoto'); @endphp
            @php /** @var \Cyndaron\Photoalbum\Photo $photo */@endphp
            @foreach ($photos as $photo)
            <figure class="fotobadge">

                <a href="{{ \Cyndaron\Util\Util::filenameToUrl($photo->getFullPath()) }}" data-lightbox="{{ $model->name }}" data-hash="{{ $photo->hash }}"
                    @if ($photo->caption) data-title="{{ $photo->caption->caption }}" @endif>
                    @if ($photo->hasThumbnail())
                        <img loading="lazy" class="thumb" alt="{{ $photo->filename }}" src="{{ $photo->getRelativeThumbnailPath() }}"/>
                    @else
                        <img loading="lazy" class="thumb default-thumbnail-size" alt="{{ $photo->filename }}" src="{{ \Cyndaron\Util\Util::filenameToUrl($photo->getFullPath()) }}"/>
                    @endif
                </a>

                @if ($isAdmin)
                    <br/>
                    @php $captionId = $photo->caption->id ?? 0 @endphp
                    <form method="post" action="/photoalbum/deletePhoto/{{ $model->id }}/{{ base64_encode($photo->filename) }}">

                        <input type="hidden" name="csrfToken" value="{{ $deleteToken }}">
                        <div class="btn-group btn-group-sm">
                            @include('View/Widget/Button', ['kind' => 'edit', 'link' => "/editor/photo/$captionId/{$photo->hash}/{$model->id}", 'title' => 'Bijschrift bewerken', 'text' => 'Bijschrift bewerken', 'size' => 16])
                            <button class="btn btn-sm btn-danger" type="submit"><span class="glyphicon glyphicon-trash"></span></button>
                        </div>
                    </form>
                @endif
            </figure>
            @endforeach
        </div>

    @endif
@endsection
