@extends ('Index')

@section ('contents')

    Openstaand inschrijfgeld: {{ $due|euro }}
    @if ($due > 0.0)
        <button class="btn btn-primary">Betalen</button>
    @endif

    <p>Je hebt je voor de volgende wedstrijden ingeschreven:</p>

    @php /** @var \Cyndaron\Geelhoed\Contest\Contest[] $contests */ @endphp
    @foreach ($contests as $contest)
        <div class="card location-card">
            <div class="card-header">
                <h2><a href="/contest/view/{{ $contest->id }}">{{ $contest->name }}</a></h2>
            </div>
            <div class="card-body">
                <table>
                    <tr><th>Locatie:</th><td>{{ $contest->location }}</td></tr>
                    <tr><th>Data: </th><td>
                            @foreach ($contest->getDates() as $contestDate)
                                @php $classes = $contestDate->getClasses() @endphp
                                {{ $contestDate->datetime|dmyHm }}@if (count($classes) > 0):@endif
                                @foreach($classes as $class)
                                    {{ $class->name }}@if (!$loop->last), @endif
                                @endforeach
                            @endforeach
                        </td>
                    </tr>
                    <tr><th>Inschrijven voor:</th><td>{{ $contest->registrationDeadline|dmyHm }}</td></tr>
                </table>
                <a role="button" class="btn btn-outline-cyndaron" href="/contest/view/{{ $contest->id }}">Meer informatie</a>
            </div>
        </div>
    @endforeach
@endsection