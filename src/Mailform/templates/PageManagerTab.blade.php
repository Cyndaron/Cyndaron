@component('Widget/Toolbar')
    @slot('right')
        @include('Widget/Button', ['kind' => 'new', 'link' => '/editor/mailform', 'title' => 'Nieuw mailformulier', 'text' => 'Nieuw mailformulier'])
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
        @foreach ($mailforms as $mailform)
        <tr id="pm-row-mailform-{{ $mailform->id }}">
            <td>{{ $mailform->id }}</td>
            <td>
                {{ $mailform->name }}
            </td>
            <td>
                <div class="btn-group">
                    <a class="btn btn-outline-cyndaron btn-sm" href="/editor/mailform/{{ $mailform->id }}"><span class="glyphicon glyphicon-pencil" title="Bewerk dit mailformulier"></span></a>
                    <button class="btn btn-danger btn-sm pm-delete" data-type="mailform" data-id="{{ $mailform->id }}" data-csrf-token="{{ \Cyndaron\User\User::getCSRFToken('mailform', 'delete') }}"><span class="glyphicon glyphicon-trash" title="Verwijder dit mailformulier"></span></button>
                </div>

            </td>
        </tr>
        @endforeach
    </tbody>
</table>