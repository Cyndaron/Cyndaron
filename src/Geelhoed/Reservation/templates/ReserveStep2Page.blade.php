@extends('Index')

@section('contents')
    @if (count($datesInDropdown) > 0)
        <div class="alert alert-info">
            Selecteer voor welke datum u wilt reserveren.
        </div>
    <form method="post" action="/reservation/step-3">
        <input type="hidden" name="csrfToken" value="{{ $csrfToken }}">
        <input type="hidden" name="hourId" value="{{ $hour->id }}">
        @include('View/Widget/Form/Select', ['id' => 'date', 'label' => 'Datum', 'required' => true, 'options' => $datesInDropdown])
        <button type="submit" class="btn btn-success">Volgende</button>
    </form>
    @else
        Helaas zijn er de komende tijd geen plekken meer beschikbaar voor deze les!
    @endif
@endsection
