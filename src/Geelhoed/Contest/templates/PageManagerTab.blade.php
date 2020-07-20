@component('Widget/Toolbar')
    @slot('right')
        <button id="gcm-new" class="btn btn-success" type="button"><span class="glyphicon glyphicon-plus"></span> Nieuwe wedstrijd</button>
    @endslot
@endcomponent

<table id="gcm-table" class="table table-striped table-bordered pm-table"
    data-csrf-token-edit="{{ Cyndaron\User\User::getCSRFToken('contest', 'edit') }}"
    data-csrf-token-delete="{{ Cyndaron\User\User::getCSRFToken('contest', 'delete') }}">
    <thead>
        <tr>
            <th>ID</th>
            <th>Naam</th>
            <th>Datum</th>
            <th>Acties</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($contests as $contest)
        <tr>
            <td>{{ $contest->id }}</td>
            <td>{{ $contest->name }}</td>
            <td>{{ $contest->getFirstDate() }}</td>
            <td>
                <div class="btn-group">
                    <a title="Naar de inschrijfpagina" href="/contest/view/{{ $contest->id }}" class="btn btn-outline-cyndaron"><span class="glyphicon glyphicon-plus"></span></a>
                    <a title="Bekijk lijst van inschrijvingen" href="/contest/subscriptionList/{{ $contest->id }}" class="btn btn-outline-cyndaron"><span class="glyphicon glyphicon-list"></span></a>
                    <button title="Wedstrijd bewerken" class="btn btn-warning gcm-edit" type="button"
                            data-id="{{ $contest->id }}"
                            data-name="{{ $contest->name }}"
                            data-description="{{ $contest->description }}"
                            data-location="{{ $contest->location }}"
                            data-sport-id="{{ $contest->sportId }}"
                            data-deadline-date="{{ date('Y-m-d', strtotime($contest->registrationDeadline)) }}"
                            data-deadline-time="{{ date('H:i', strtotime($contest->registrationDeadline)) }}"
                            data-registration-change-deadline-date="{{ date('Y-m-d', strtotime($contest->registrationChangeDeadline)) }}"
                            data-registration-change-deadline-time="{{ date('H:i', strtotime($contest->registrationChangeDeadline)) }}"
                            data-price="{{ $contest->price }}">
                    <span class="glyphicon glyphicon-edit"></span></button>
                    <button title="Wedstrijd verwijderen" class="btn btn-danger gcm-delete" data-id="{{ $contest->id }}" type="button"><span class="glyphicon glyphicon-trash"></span></button>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@component('Widget/Modal', ['id' => 'gcm-edit-dialog', 'title' => 'Wedstrijd toevoegen/bewerken', 'sizeClass' => 'modal-lg'])
    @slot('body')
        <input type="hidden" id="gcm-edit-id" value="">

        @include('Widget/Form/BasicInput', ['id' => 'gcm-edit-name', 'label' => 'Naam', 'required' => true])
        @include('Widget/Form/Textarea', ['id' => 'gcm-edit-description', 'label' => 'Beschrijving', 'placeholder' => '(Vul hier meer informatie over de wedstrijd in, zoals de weegtijden.)'])
        @include('Widget/Form/BasicInput', ['id' => 'gcm-edit-location', 'label' => 'Locatie', 'required' => true])
        @include('Widget/Form/Select', ['id' => 'gcm-edit-sportId', 'label' => 'Sport', 'required' => true, 'options' => \Cyndaron\Geelhoed\Sport::fetchAllForSelect()])
        @component('Widget/Form/FormWrapper', ['id' => 'gcm-edit-deadline-date', 'label' => 'Inschrijvings-deadline'])
            @slot('right')
                <input id="gcm-edit-deadline-date" type="date" class="form-control form-control-inline" required>
                <input id="gcm-edit-deadline-time" type="time" class="form-control form-control-inline" required>
            @endslot
        @endcomponent
        @component('Widget/Form/FormWrapper', ['id' => 'gcm-edit-registration-change-deadline-date', 'label' => 'Deadline voor wijzigingen'])
            @slot('right')
                <input id="gcm-edit-registration-change-deadline-date" type="date" class="form-control form-control-inline" required>
                <input id="gcm-edit-registration-change-deadline-time" type="time" class="form-control form-control-inline" required>
            @endslot
        @endcomponent
        @component('Widget/Form/FormWrapper', ['id' => 'gcm-edit-price', 'label' => 'Prijs'])
            @slot('right')
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">€</span>
                    </div>
                    <input id="gcm-edit-price" type="number" min="0.01" step="0.01" class="form-control" required>
                </div>
            @endslot
        @endcomponent
    @endslot
    @slot('footer')
        <button type="button" class="btn btn-success" id="gcm-edit-save">Opslaan</button>
        <button type="button" class="btn btn-outline-cyndaron" data-toggle="modal" data-target="#gcm-edit-dialog">Annuleren</button>
    @endslot
@endcomponent
