@extends ('Index')

@section ('contents')
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
                            <ul>
                            @foreach ($contest->getDates() as $contestDate)
                                <li>
                                @php $classes = $contestDate->getClasses() @endphp
                                {{ $contestDate->datetime|dmyHm }}@if (count($classes) > 0):@endif
                                @foreach($classes as $class)
                                    {{ $class->name }}@if (!$loop->last), @endif
                                @endforeach
                                </li>
                            @endforeach
                            </ul>
                        </td>
                    </tr>
                    <tr><th>Inschrijven voor:</th><td>{{ $contest->registrationDeadline|dmyHm }}</td></tr>
                </table>
                <a role="button" class="btn btn-outline-cyndaron" href="/contest/view/{{ $contest->id }}">Meer informatie en inschrijven</a>
            </div>
        </div>
    @endforeach
@endsection