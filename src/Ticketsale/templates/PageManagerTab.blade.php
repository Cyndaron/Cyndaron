@component('Widget/Toolbar')
    @slot('right')
        @include('Widget/Button', ['kind' => 'new', 'link' => '/editor/concert', 'title' => 'Nieuw concert', 'text' => 'Nieuw concert'])
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
        @foreach ($concerts as $concert)
        <tr>
            <td>{{ $concert->id }}</td>
            <td>
                {{ $concert->name }}
                (<a href="/concert/order/{{ $concert->id }}">bestelpagina</a>,
                <a href="/concert/viewOrders/{{ $concert->id }}">overzicht bestellingen</a>)
            </td>
            <td>
                <div class="btn-group">
                    <a class="btn btn-outline-cyndaron btn-sm" href="/editor/concert/{{ $concert->id }}"><span class="glyphicon glyphicon-pencil" title="Bewerk dit concert"></span></a>
                    <button class="btn btn-danger btn-sm pm-delete" data-type="concert" data-id="{{ $concert->id }}" data-csrf-token="{{ \Cyndaron\User\User::getCSRFToken('concert', 'delete') }}"><span class="glyphicon glyphicon-trash" title="Verwijder dit concert"></span></button>
                </div>

            </td>
        </tr>
        @endforeach
    </tbody>
</table>