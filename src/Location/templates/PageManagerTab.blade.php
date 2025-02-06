@component('View/Widget/Toolbar')
    @slot('right')
        @include('View/Widget/Button', ['kind' => 'new', 'link' => '/editor/location', 'title' => 'Nieuwe locatie', 'text' => 'Nieuwe locatie'])
    @endslot
@endcomponent

<table class="table table-striped table-bordered pm-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Naam</th>
            <th>Straat</th>
            <th>Postcode</th>
            <th>Plaats</th>
            <th>Acties</th>
        </tr>
    </thead>
    <tbody>
        @php /** @var \Cyndaron\Location\Location[] $locations */ @endphp
        @foreach ($locations as $location)
        <tr>
            <td><a href="/locaties/details/{{ $location->id }}">{{ $location->id }}</a></td>
            <td>
                <a href="/locaties/details/{{ $location->id }}">{{ $location->getName() }}</a>
            </td>
            <td>
                {{ $location->street }} {{ $location->houseNumber }}
            </td>
            <td>
                {{ $location->postalCode }}
            </td>
            <td>
                {{ $location->city }}
            </td>
            <td>
                <div class="btn-group">
                    <a class="btn btn-outline-cyndaron btn-sm" href="/editor/location/{{ $location->id }}" title="Bewerk deze locatie">@include('View/Widget/Icon', ['type' => 'edit'])</a>
                    <button class="btn btn-danger btn-sm pm-delete" data-type="location" data-id="{{ $location->id }}" data-csrf-token="{{ $tokenDelete }}" title="Verwijder deze locatie">@include('View/Widget/Icon', ['type' => 'delete'])</button>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
