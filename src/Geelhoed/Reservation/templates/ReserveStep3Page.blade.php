@extends('Index')

@section('contents')
    <form method="post" action="/reservation/step-last">
        <input type="hidden" name="csrfToken" value="{{ $csrfToken }}">
        <input type="hidden" name="hourId" value="{{ $hour->id }}">
        <input type="hidden" name="date" value="{{ $date }}">

        @for ($i = 1; $i <= $maxNames; $i++)
            @include('Widget/Form/BasicInput', ['id' => "name[]", 'label' => "Naam judoka {$i}", 'required' => ($i === 1)])
        @endfor

        <button type="submit" class="btn btn-primary">Reservering afronden</button>
    </form>

@endsection
