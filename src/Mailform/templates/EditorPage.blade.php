@extends ('Editor/PageBase')

@section ('contentSpecificButtons')
    @include('Widget/Form/Checkbox', ['id' => 'sendConfirmation', 'label' => 'Stuur bovenstaande tekst als bevestiging', 'checked' => $model->sendConfirmation ?? false])
    @include('Widget/Form/Email', ['id' => 'email', 'label' => 'E-mailadres', 'value' => $model->email ?? ''])
    @include('Widget/Form/InputText', ['id' => 'antiSpamAnswer', 'label' => 'Antispamantwoord', 'value' => $model->antiSpamAnswer ?? ''])
@endsection