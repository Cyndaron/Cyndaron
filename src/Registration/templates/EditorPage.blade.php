@extends ('Editor/PageBase')

@section ('contentSpecificButtons')
    <div class="form-group">
        <label for="descriptionWhenClosed">Beschijving indien gesloten:</label>
        <textarea class="form-control" id="descriptionWhenClosed" name="descriptionWhenClosed" rows="3">{{ $model->descriptionWhenClosed }}</textarea>
    </div>

    @include ('Widget/Form/Checkbox', ['id' => 'openForRegistration', 'description' => 'Open voor inschrijving', 'checked' => $model->openForRegistration])

    @include ('Widget/Form/Currency', ['id' => 'registrationCost0', 'label' => 'Inschrijfgeld groep 0', 'value' => $registrationCost0])
    @include ('Widget/Form/Currency', ['id' => 'registrationCost1', 'label' => 'Inschrijfgeld groep 1', 'value' => $registrationCost1])
    @include ('Widget/Form/Currency', ['id' => 'registrationCost2', 'label' => 'Inschrijfgeld groep 2', 'value' => $registrationCost2])


    @include ('Widget/Form/Currency', ['id' => 'lunchCost', 'label' => 'Lunchkosten', 'value' => $lunchCost])


    <div class="form-group row">
        <label for="maxRegistrations" class="col-sm-2 col-form-label">Maximaal aantal deelnemers:</label>
        <div class="col-sm-1">
            <input type="number" class="form-control" id="maxRegistrations" name="maxRegistrations" value="{{ $model->maxRegistrations ?: 300 }}">
        </div>
    </div>
    <div class="form-group row">
        <label for="numSeats" class="col-sm-2 col-form-label">Aantal plaatsen voor familie/vrienden:</label>
        <div class="col-sm-1">
            <input type="number" class="form-control" id="numSeats" name="numSeats" value="{{ $model->numSeats ?: 300 }}">
        </div>
    </div>

@endsection