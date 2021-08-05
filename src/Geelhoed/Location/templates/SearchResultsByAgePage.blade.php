@extends ('Index')

@section ('contents')
    @if (count($hours) === 0)

        Geen lessen gevonden!

    @else

        Als {{ $age }}-jarige kun je op de volgende tijden en plaatsen trainen:<br><br>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Locatie</th>
                    <th>Dag</th>
                    <th>Leeftijden</th>
                    <th>Van</th>
                    <th>Tot</th>
                    <th>Sport</th>
                </tr>
            </thead>
            <tbody>
                @php /** @var \Cyndaron\Geelhoed\Hour\Hour $hour */ @endphp
                @foreach ($hours as $hour)
                    @php $location = $hour->getLocation() @endphp
                    <tr>
                        <td>
                            <a href="/location/view/{{ $location->id }}">
                                {{ $location->city }}, {{ $location->street }}
                            </a>
                        </td>
                        <td>{{ \Cyndaron\View\Template\ViewHelpers::getDutchWeekday($hour->day) }}</td>
                        <td>{{ $hour->description }}</td>
                        <td>{{ $hour->from|hm }}</td>
                        <td>{{ $hour->until|hm }}</td>
                        <td>{{ $hour->getSportName() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    @endif

@endsection
