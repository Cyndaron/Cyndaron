@extends('Index')

@section('contents')
    <div class="alert alert-warning">
        Enkel judoka's onder de 18!
    </div>

    <div class="alert alert-info">
        Kies een les. Staat uw les er niet tussen, dan zijn er geen data meer beschikbaar.
    </div>

    <form method="post" action="/reservation/step-2">
        <input type="hidden" name="csrfToken" value="{{ $csrfToken }}">
        @include('Widget/Form/Select', ['id' => 'hourId', 'label' => 'Lesuur', 'required' => true, 'options' => $hoursSelect])
        <button type="submit" class="btn btn-success">Volgende</button>
    </form>

@endsection
