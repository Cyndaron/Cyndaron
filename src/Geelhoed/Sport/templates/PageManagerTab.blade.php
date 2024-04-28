@component('View/Widget/Toolbar')
@endcomponent

<table class="table table-striped table-bordered pm-table" style="width: auto;">
    <thead>
        <tr>
            <th>ID</th>
            <th>Naam</th>
            <th>Bedrag junioren</th>
            <th>Bedrag senioren</th>
            <th>Acties</th>
        </tr>
    </thead>
    <tbody>
        @php /** @var \Cyndaron\Geelhoed\Sport\Sport[] $sports */ @endphp
        @foreach ($sports as $sport)
            <tr>
                <td>{{ $sport->id }}</td>
                <td>{{ $sport->name }}</td>
                <td>{{ $sport->juniorFee|euro }}</td>
                <td>{{ $sport->seniorFee|euro }}</td>
                <td>
                    <button
                        class="btn btn-outline-cyndaron btn-sm pm-edit"
                        data-id="{{ $sport->id }}"
                        data-name="{{ $sport->name }}"
                        data-junior-fee="{{ $sport->juniorFee }}"
                        data-senior-fee="{{ $sport->seniorFee }}"
                        data-toggle="modal"
                        data-target="#pm-edit-modal">
                        <span class="glyphicon glyphicon-pencil" title="Bewerken"></span>
                    </button>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@component('View/Widget/Modal',  ['id' => 'pm-edit-modal', 'title' => 'Volgorde aanpassen', 'sizeClass' => 'modal-lg'])
    @slot('body')
        <form id="pm-edit-modal-form">
            <input type="hidden" name="csrfToken" value="{{ \Cyndaron\User\UserSession::getCSRFToken('sport', 'edit') }}">
            <div class="form-group row">
                <label for="pm-edit-modal-name" class="col-md-3 col-form-label">Naam:</label>
                <div class="col-md-6">
                    <input type="text" class="form-control" id="pm-edit-modal-name" name="name" value="">
                </div>
            </div>

            <div class="form-group row">
                <label for="pm-edit-modal-junior-fee" class="col-md-3 col-form-label">Bedrag junioren:</label>
                <div class="col-md-6">
                    <input type="number" class="form-control" step="0.25" min="0" pattern="[0-9\.,]+" id="pm-edit-modal-junior-fee" name="juniorFee" value="">
                </div>
            </div>

            <div class="form-group row">
                <label for="pm-edit-modal-senior-fee" class="col-md-3 col-form-label">Bedrag senioren:</label>
                <div class="col-md-6">
                    <input type="number" class="form-control" step="0.25" min="0" pattern="[0-9\.,]+" id="pm-edit-modal-senior-fee" name="seniorFee" value="">
                </div>
            </div>

        </form>
    @endslot
    @slot('footer')
        <button id="pm-edit-modal-save" type="button" class="btn btn-primary">Opslaan</button>
        <button type="button" class="btn btn-outline-cyndaron" data-dismiss="modal" data-target="#pm-edit-modal">Annuleren</button>
    @endslot
@endcomponent
