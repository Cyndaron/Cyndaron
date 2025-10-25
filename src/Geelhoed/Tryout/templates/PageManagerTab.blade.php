@component('View/Widget/Toolbar')
    @slot('right')
        @include('View/Widget/Button', ['kind' => 'new', 'link' => '/editor/tryout', 'title' => 'Nieuw toernooi', 'text' => 'Nieuw toernooi'])
    @endslot
@endcomponent

<table class="table table-striped table-bordered pm-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Naam</th>
            <th>Locatie</th>
            <th>Datum</th>
            <th>Extra</th>
            <th>Acties</th>
        </tr>
    </thead>
    <tbody>
        @php /** @var \Cyndaron\Geelhoed\Tryout\Tryout[] $tryouts */ @endphp
        @foreach ($tryouts as $tryout)
            <tr>
                <td>{{ $tryout->id }}</td>
                <td>{{ $tryout->name }}</td>
                <td>
                    @php $location = $tryout->location @endphp
                    @if ($location !== null)
                        <a href="/locaties/details/{{ $location->id }}">{{ $location->getName() }}</a>
                    @endif
                </td>
                <td>
                    {{ $tryout->getStart()|dmy }}
                </td>
                <td>
                    <a href="/pagemanager/tryoutorders/{{ $tryout->id }}">Voorverkoop</a>
                    @if ($tryout->photoalbumLink !== '')
                        <br><a href="{{ $tryout->photoalbumLink }}">Fotoalbums</a>
                    @endif
                </td>
                <td>

                    <div class="btn-group">
                        <a class="btn btn-outline-cyndaron btn-sm" href="/editor/tryout/{{ $tryout->id }}" title="Bewerk dit toernooi">@include('View/Widget/Icon', ['type' => 'edit'])</a>
{{--                        <button class="btn btn-danger btn-sm pm-delete" data-type="tryout" data-id="{{ $tryout->id }}" data-csrf-token="{{ $tokenDelete }}" title="Verwijder dit toernooi">@include('View/Widget/Icon', ['type' => 'delete'])</button>--}}
                        @if ($tryout->photoalbumLink === '')
                            <button
                                title="Fotoalbums aanmaken"
                                class="create-photoalbums btn btn-sm btn-outline-cyndaron"
                                data-id="{{ $tryout->id }}"
                                data-csrf-token-create-photoalbums="{{ $csrfTokenCreatePhotoalbums }}"
                            >
                                @include('View/Widget/Icon', ['type' => 'picture'])
                            </button>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
