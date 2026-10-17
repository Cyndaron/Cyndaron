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
                        data-bs-toggle="modal"
                        data-bs-target="#pm-edit-modal"
                        title="Bewerken">
                        @include('View/Widget/Icon', ['type' => 'edit'])
                    </button>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@component('View/Widget/Modal',  ['id' => 'pm-edit-modal', 'title' => 'Prijzen aanpassen', 'sizeClass' => 'modal-lg'])
    @slot('body')
        <form id="pm-edit-modal-form">
            <input type="hidden" name="csrfToken" value="{{ $tokenEdit }}">

            @include('View/Widget/Form/BasicInput', ['id' => 'pm-edit-modal-name', 'name' => 'name', 'required' => true, 'label' => 'Naam'])

            @component('View/Widget/Form/FormWrapper', ['id' => 'pm-edit-modal-junior-fee', 'label' => 'Bedrag junioren'])
                @slot('right')
                    <input type="number" class="form-control" step="0.25" min="0" pattern="[0-9\.,]+" id="pm-edit-modal-junior-fee" name="juniorFee" value="">
                @endslot
            @endcomponent

            @component('View/Widget/Form/FormWrapper', ['id' => 'pm-edit-modal-senior-fee', 'label' => 'Bedrag senioren'])
                @slot('right')
                    <input type="number" class="form-control" step="0.25" min="0" pattern="[0-9\.,]+" id="pm-edit-modal-senior-fee" name="seniorFee" value="">
                @endslot
            @endcomponent
        </form>
    @endslot
    @slot('footer')
        <button id="pm-edit-modal-save" type="button" class="btn btn-primary">Opslaan</button>
        <button type="button" class="btn btn-outline-cyndaron" data-bs-dismiss="modal" data-bs-target="#pm-edit-modal">Annuleren</button>
    @endslot
@endcomponent
