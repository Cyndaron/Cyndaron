@extends ('Editor/PageBase')

@section ('contentSpecificButtons')

    @include ('Widget/Form/Textarea', ['id' => 'descriptionWhenClosed',  'label' => 'Beschijving indien gesloten', 'value' => $model->descriptionWhenClosed ?? ''])

    @include ('Widget/Form/Checkbox', ['id' => 'openForRegistration', 'description' => 'Open voor aanmelden', 'checked' => $model->openForRegistration ?? false])

    @include ('Widget/Form/Currency', ['id' => 'registrationCost0', 'label' => 'Inschrijfgeld groep 0', 'value' => $registrationCost0])
    @include ('Widget/Form/Currency', ['id' => 'registrationCost1', 'label' => 'Inschrijfgeld groep 1', 'value' => $registrationCost1])
    @include ('Widget/Form/Currency', ['id' => 'registrationCost2', 'label' => 'Inschrijfgeld groep 2', 'value' => $registrationCost2])


    @include ('Widget/Form/Currency', ['id' => 'lunchCost', 'label' => 'Lunchkosten', 'value' => $lunchCost])


    <div class="form-group row">
        <label for="maxRegistrations" class="col-sm-2 col-form-label">Maximaal aantal deelnemers:</label>
        <div class="col-sm-2">
            <input type="number" class="form-control" id="maxRegistrations" name="maxRegistrations" value="{{ $model->maxRegistrations ?: 300 }}">
        </div>
    </div>
    <div class="form-group row">
        <label for="numSeats" class="col-sm-2 col-form-label">Aantal plaatsen voor familie/vrienden:</label>
        <div class="col-sm-2">
            <input type="number" class="form-control" id="numSeats" name="numSeats" value="{{ $model->numSeats ?: 300 }}">
        </div>
    </div>

    @include ('Widget/Form/Checkbox', ['id' => 'requireApproval', 'description' => 'Vereis goedkeuring van inschrijvingen', 'checked' => $model->requireApproval])

    @include ('Widget/Form/InputText', ['id' => 'performedPiece',  'label' => 'Uitgevoerd stuk', 'value' => $model->performedPiece ?? ''])

    @include ('Widget/Form/Textarea', ['id' => 'termsAndConditions',  'label' => 'Voorwaarden', 'value' => $model->termsAndConditions ?? ''])

@endsection