@component('View/Widget/Toolbar')
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
                    @php $location = $tryout->getLocationObject() @endphp
                    @if ($location !== null)
                        <a href="/locaties/details/{{ $location->id }}">{{ $location->getName() }}</a>
                    @endif
                </td>
                <td>
                    {{ $tryout->getStart()|dmy }}
                </td>
                <td>
                    @if ($tryout->photoalbumLink !== '')
                        <a href="{{ $tryout->photoalbumLink }}">Fotoalbums</a>
                    @endif
                </td>
                <td>
                    @if ($tryout->photoalbumLink === '')
                        <button
                            title="Fotoalbums aanmaken"
                            class="create-photoalbums btn btn-outline-cyndaron"
                            data-id="{{ $tryout->id }}"
                            data-csrf-token-create-photoalbums="{{ $csrfTokenCreatePhotoalbums }}"
                        >
                            <span class="glyphicon glyphicon-picture"></span>
                        </button>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
