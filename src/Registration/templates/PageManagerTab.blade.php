@component('Widget/Toolbar')
    @slot('right')
        @include('Widget/Button', ['kind' => 'new', 'link' => '/editor/event', 'title' => 'Nieuw evenement', 'text' => 'Nieuw evenement'])
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
        @foreach ($events as $event)
        <tr>
            <td>{{ $event->id }}</td>
            <td>
                {{ $event->name }}
                (<a href="/event/register/{{ $event->id }}">inschrijfpagina</a>,
                <a href="/event/viewRegistrations/{{ $event->id }}">overzicht inschrijvingen</a>)
            </td>
            <td>
                <div class="btn-group">
                    <a class="btn btn-outline-cyndaron btn-sm" href="/editor/event/{{ $event->id }}"><span class="glyphicon glyphicon-pencil" title="Bewerk dit evenement"></span></a>
                    <button class="btn btn-danger btn-sm pm-delete" data-type="event" data-id="{{ $event->id }}" data-csrf-token="{{ \Cyndaron\User\User::getCSRFToken('event', 'delete') }}"><span class="glyphicon glyphicon-trash" title="Verwijder dit evenement"></span></button>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>