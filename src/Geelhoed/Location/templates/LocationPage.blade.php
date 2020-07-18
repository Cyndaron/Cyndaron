@extends ('Index')

@section ('contents')
    @if (!empty($locNotification))
        <div class="alert alert-info">
            {{ $locNotification }}
        </div>
    @endif

    <p>
        {{ $location->street }} {{ $location->houseNumber }}<br>
        {{ $location->postalCode }} {{$location->city}}
    </p>

    <p><a href="{{ $location->getMapsLink() }}" target="_blank">Bekijken op Google Maps</a></p>

    <h3>Lessen</h3>

    @foreach ($location->getHoursSortedByWeekday() as $weekday => $hours)
        <h4>{{ \Cyndaron\Template\ViewHelpers::getDutchWeekday($weekday) }}</h4>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Groep</th><th>Van</th><th>Tot</th><th>Training</th><th>Afdeling</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($hours as $hour)
                    <tr>
                        <td>{{ $hour->description }}</td>
                        <td>{{ $hour->from|hm }}</td>
                        <td>{{ $hour->until|hm }}</td>
                        <td>{{ $hour->getSportName() }}</td>
                        <td>{{ $hour->getDepartment()->name }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

@endsection