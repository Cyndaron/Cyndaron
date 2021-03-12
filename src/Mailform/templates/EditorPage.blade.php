@extends ('Editor/PageBase')

@section ('contentSpecificButtons')
    @include('View/Widget/Form/Checkbox', ['id' => 'sendConfirmation', 'label' => 'Stuur bovenstaande tekst als bevestiging', 'checked' => $model->sendConfirmation ?? false])
    @include('View/Widget/Form/Email', ['id' => 'email', 'label' => 'E-mailadres', 'value' => $model->email ?? ''])
    @include('View/Widget/Form/InputText', ['id' => 'antiSpamAnswer', 'label' => 'Antispamantwoord', 'value' => $model->antiSpamAnswer ?? ''])
@endsection
