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
            <th>Acties</th>
        </tr>
    </thead>
    <tbody>
        @php /** @var \Cyndaron\Geelhoed\Location\Location[] $locations */ @endphp
        @foreach ($locations as $location)
        <tr>
            <td><a href="/location/view/{{ $location->id }}">{{ $location->id }}</a></td>
            <td>
                <a href="/location/view/{{ $location->id }}">{{ $location->getName() }}</a>
                @foreach ($location->getHours() as $hour)
                    <br>
                    {{ \Cyndaron\View\Template\ViewHelpers::getDutchWeekday($hour->day) }} {{ $hour->from|hm }} - {{ $hour->until|hm }}: {{ $hour->description }} ({{ $hour->getSportName() }}) (<a href="/hour/memberList/{{ $hour->id }}">ledenlijst</a>)
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
