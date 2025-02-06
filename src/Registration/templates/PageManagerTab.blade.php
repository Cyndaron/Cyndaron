@component('View/Widget/Toolbar')
    @slot('right')
        @include('View/Widget/Button', ['kind' => 'new', 'link' => '/editor/event', 'title' => 'Nieuw evenement', 'text' => 'Nieuw evenement'])
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
                    <a class="btn btn-outline-cyndaron btn-sm" href="/editor/event/{{ $event->id }}" title="Bewerk dit evenement">@include('View/Widget/Icon', ['type' => 'edit'])</a>
                    <button class="btn btn-danger btn-sm pm-delete" data-type="event" data-id="{{ $event->id }}" data-csrf-token="{{ $tokenDelete }}" title="Verwijder dit evenement">@include('View/Widget/Icon', ['type' => 'delete'])</button>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
