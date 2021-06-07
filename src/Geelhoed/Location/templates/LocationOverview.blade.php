@extends ('Index')

@section ('contents')
    @if (!empty($locNotification))
        <div class="alert alert-info">
            {!! $locNotification !!}
        </div>
    @endif

    @php /** @var \Cyndaron\Geelhoed\Location\Location[] $locations*/ @endphp
    @foreach ($locations as $location)
        @php $hoursPerWeekday = $location->getHoursSortedByWeekday() @endphp
        @if (count($hoursPerWeekday) === 0) @continue @endif
        <div class="card location-card">
            <div class="card-header">
                <h2>{{ $location->city }}, {{ $location->name ?: ($location->street) }}</h2>
            </div>
            <div class="card-body">
                {{ $location->street }} {{ $location->houseNumber }}<br>
                {{ $location->postalCode }} {{ $location->city }}<br>

                <a href="/location/view/{{ $location->id }}">Meer informatie</a>

                @foreach ($hoursPerWeekday as $weekDay => $hours)
                    <h4>{{ \Cyndaron\View\Template\ViewHelpers::getDutchWeekday($weekDay) }}</h4>

                    <table class="table table-striped table-bordered location-overview">
                        <thead>
                            <tr>
                                <th>Groep</th><th>Lestijd</th><th>Training</th><th>Afdeling</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($hours as $hour)
                                <tr>
                                    <td>{{ $hour->description }}</td>
                                    <td>{{ $hour->from|hm }}&nbsp;&ndash;&nbsp;{{ $hour->until|hm }}</td>
                                    <td>{{ $hour->getSportName() }}</td>
                                    <td>{{ $hour->getDepartment()->name }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endforeach
            </div>
        </div>
    @endforeach
@endsection
