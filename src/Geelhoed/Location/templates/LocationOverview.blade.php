@extends ('Index')

@section ('contents')
    @if (!empty($locNotification))
        <div class="alert alert-info">
            {!! $locNotification !!}
        </div>
    @endif

    @php $lastCity = '' @endphp
    @php /** @var \Cyndaron\Location\Location[] $locations*/ @endphp
    @foreach ($locations as $location)
        @php $hoursPerWeekday = (new \Cyndaron\Geelhoed\Location\Location($location))->getHoursSortedByWeekday() @endphp
        @if (count($hoursPerWeekday) === 0) @continue @endif

        <div class="card location-card"
            @if ($lastCity !== $location->city)
                id="{{ $location->city }}"
                @php $lastCity = $location->city @endphp
            @endif>
            <div class="card-header">
                <h2>{{ $location->city }}, {{ $location->name ?: ($location->street) }}</h2>
            </div>
            <div class="card-body">
                {{ $location->street }} {{ $location->houseNumber }}<br>
                {{ $location->postalCode }} {{ $location->city }}<br>

                <a href="/locaties/details/{{ $location->id }}">Meer informatie</a>

                @foreach ($hoursPerWeekday as $weekDay => $hours)
                    <h4>{{ \Cyndaron\View\Template\ViewHelpers::getDutchWeekday($weekDay) }}</h4>

                    @include('Geelhoed/Location/HoursTable', ['hours' => $hours])
                @endforeach
            </div>
        </div>
    @endforeach
@endsection
