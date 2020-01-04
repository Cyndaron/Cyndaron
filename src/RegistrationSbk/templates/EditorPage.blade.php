@extends ('Editor/PageBase')

@section ('contentSpecificButtons')

    @include ('Widget/Form/Textarea', ['id' => 'descriptionWhenClosed',  'label' => 'Beschijving indien gesloten', 'value' => $model->descriptionWhenClosed ?? ''])

    @include ('Widget/Form/Checkbox', ['id' => 'openForRegistration', 'description' => 'Open voor aanmelden', 'checked' => $model->openForRegistration ?? false])

    @include ('Widget/Form/Currency', ['id' => 'registrationCost', 'label' => 'Inschrijfgeld', 'value' => $registrationCost ?? ''])

    @include ('Widget/Form/InputText', ['id' => 'performedPiece',  'label' => 'Uitgevoerd stuk', 'value' => $model->performedPiece ?? ''])

    @include ('Widget/Form/Textarea', ['id' => 'termsAndConditions',  'label' => 'Voorwaarden', 'value' => $model->termsAndConditions ?? ''])

@endsection