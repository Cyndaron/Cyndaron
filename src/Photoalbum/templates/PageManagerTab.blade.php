@component('View/Widget/Toolbar')
    @slot('right')
        <label for="pm-photoalbum-new-name" class="mr-sm-2">Nieuw fotoalbum:</label>
        <div class="input-group">
            <input class="form-control form-control-inline mr-sm-2" id="pm-photoalbum-new-name" type="text"/>
            <button type="button" id="pm-create-photoalbum" data-csrf-token="{{ $tokenAdd }}" class="btn btn-success">@include('View/Widget/Icon', ['type' => 'new']) Aanmaken</button>
        </div>
    @endslot
@endcomponent

<table class="table table-striped table-bordered pm-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Naam</th>
            <th>Acties</th>
        </tr>
    </thead>
    <tbody>
        @php /** @var \Cyndaron\Photoalbum\Photoalbum[] $photoalbums */ @endphp
        @foreach ($photoalbums as $photoalbum)
        <tr id="pm-row-photoalbum-{{ $photoalbum->id }}">
            <td><a href="/photoalbum/{{ $photoalbum->id }}">{{ $photoalbum->id }}</a></td>
            <td>
                <a href="/photoalbum/{{ $photoalbum->id }}"><b>{{ $photoalbum->name }}</b></a>
            </td>
            <td>
                <div class="btn-group">
                    @if ($canEdit)
                        @include('View/Widget/Button', ['kind' => 'edit', 'link' => "/editor/photoalbum/{$photoalbum->id}", 'title' => 'Bewerk dit fotoalbum', 'size' => 16])
                    @endif
                    @if ($currentUser->isAdmin())
                        <button class="btn btn-outline-cyndaron btn-sm pm-delete" data-type="photoalbum" data-id="{{ $photoalbum->id }}" data-csrf-token="{{ $tokenDelete }}" title="Verwijder dit fotoalbum">@include('View/Widget/Icon', ['type' => 'delete'])</button>
                        <button class="btn btn-outline-cyndaron btn-sm pm-addtomenu" data-type="photoalbum" data-id="{{ $photoalbum->id }}" data-csrf-token="{{ $tokenAddToMenu }}" title="Voeg dit fotoalbum toe aan het menu">@include('View/Widget/Icon', ['type' => 'bookmark'])</button>
                    @endif
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
