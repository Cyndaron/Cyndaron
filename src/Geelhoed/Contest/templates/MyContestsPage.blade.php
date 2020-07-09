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
                    <tr><th>Datum en tijd:</th><td>{{ $contest->date|dmyHm }}</td></tr>
                    <tr><th>Inschrijven voor:</th><td>{{ $contest->registrationDeadline|dmyHm }}</td></tr>
                </table>
                <a role="button" class="btn btn-outline-cyndaron" href="/contest/view/{{ $contest->id }}">Meer informatie</a>
            </div>
        </div>
    @endforeach
@endsection