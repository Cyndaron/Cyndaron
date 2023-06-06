@extends('Index')

@section('contents')
    <form id="subscribe-form">
        @include('View/Widget/Form/BasicInput', ['id' => 'name', 'label' => 'Naam', 'required' => true])
        @include('View/Widget/Form/BasicInput', ['id' => 'email', 'label' => 'E-mailadres', 'type' => 'email', 'required' => true])

    <div class="form-group row">
        <label class="col-sm-3 col-form-label">Ik wil graag helpen als:</label>
        <div class="col-sm-5">
            @foreach ($helpTypes as $key => $description)
                @php $disabled = $fullTypes[$key] @endphp
                <input id="type-{{ $key }}" name="type" type="radio" value="{{ $key }}" @if ($disabled) disabled @endif/>
                <label for="type-{{ $key }}">
                    @if($disabled)<del>@endif {{ $description }}@if($disabled)</del> (hiervoor hebben wij al voldoende aanmeldingen)@endif
                </label><br>
            @endforeach
        </div>
    </div>

    <div class="form-group row">
        <label class="col-sm-3 col-form-label">Ik wil graag helpen in:</label>
        <div class="col-sm-5">
            @for ($i = 0; $i < $numRounds; $i++)
                @php $disabled = $fullRounds[$i]; @endphp
                <input id="round-{{ $i }}" name="round-{{ $i }}" type="checkbox" value="1" @if($disabled) disabled @endif/>
                <label for="round-{{ $i }}">@if($disabled)<del>@endif Ronde {{ $i + 1 }}@if($disabled)</del> (hiervoor hebben wij al voldoende aanmeldingen)@endif
                </label><br>
            @endfor
        </div>
    </div>

    @include('View/Widget/Form/Textarea', ['id' => 'comments', 'label' => 'Opmerkingen', 'required' => false])

    <input type="hidden" id="eventId" name="eventId" value="{{ $event->id }}"/>
    <input type="hidden" name="csrfToken" value="{{ $csrfToken }}"/>
    <button type="button" id="submit" class="btn btn-primary">Versturen</button>
    </form>
@endsection
