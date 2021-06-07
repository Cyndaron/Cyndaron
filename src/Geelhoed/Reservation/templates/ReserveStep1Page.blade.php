@extends('Index')

@section('contents')
    Voor het inschrijven voor de anderhalvemeterlessen.

    <div class="alert alert-info">
        Kies een les. Op sommige locaties kunnen we niet schuiven met de lessen. Dit komt meestal door de beschikbare tijd en de groepsgroottes. Wanneer uw gebruikelijke leslocatie er niet tussen staat, willen wij u vragen op een andere locatie te trainen.
    </div>

    <form method="post" action="/reservation/step-2">
        <input type="hidden" name="csrfToken" value="{{ $csrfToken }}">
        @include('View/Widget/Form/Select', ['id' => 'hourId', 'label' => 'Lesuur', 'required' => true, 'options' => $hoursSelect])
        <button type="submit" class="btn btn-success">Volgende</button>
    </form>

@endsection
