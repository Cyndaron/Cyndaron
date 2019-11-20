<table class="table table-striped table-bordered pm-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Naam</th>
            <th>Acties</th>
        </tr>
    </thead>
    <tbody>
        @php /** @var \Cyndaron\Geelhoed\Location[] $locations */ @endphp
        @foreach ($locations as $location)
        <tr>
            <td>{{ $location->id }}</td>
            <td>
                {{ $location->getName() }}
                @foreach ($location->getHours() as $hour)
                    <br>
                    {{ $hour->from|hm }} - {{ $hour->until|hm }} ({{ $hour->sport }})
                @endforeach
            </td>
            <td>
                <div class="btn-group">
                    <a class="btn btn-outline-cyndaron btn-sm" href="/editor/location/{{ $location->id }}"><span class="glyphicon glyphicon-pencil" title="Bewerk dit concert"></span></a>
                    <button class="btn btn-danger btn-sm pm-delete" data-type="location" data-id="{{ $location->id }}" data-csrf-token="{{ \Cyndaron\User\User::getCSRFToken('location', 'delete') }}"><span class="glyphicon glyphicon-trash" title="Verwijder deze locatie"></span></button>
                </div>

            </td>
        </tr>
        @endforeach
    </tbody>
</table>
