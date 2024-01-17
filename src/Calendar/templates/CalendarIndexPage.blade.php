@extends ('Index')

@section ('contents')

    @php $lastDate = null; @endphp
    @php /** @var \Cyndaron\Calendar\CalendarAppointment[] $appointments */ @endphp
    @foreach ($appointments as $appointment)
        @if ($appointment->getStart() !== $lastDate)
            @php $lastDate = $appointment->getStart(); @endphp
            <h2>{{ $lastDate|dmy }}</h2>
        @endif

        <div class="card location-card">
            <div class="card-header">
                @if ($appointment->getUrl() !== false)
                    <h2><a href="{{ $appointment->getUrl() }}">{{ $appointment->getName() }}</a></h2>
                @else
                    <h2>{{ $appointment->getName() }}</h2>
                @endif
            </div>
            <div class="card-body">
                Start: {{ $appointment->getStart()->format('H:i') }}
                <br>
                Einde: @if ($appointment->getEnd()->format('Y-m-d') != $appointment->getStart()->format('Y-m-d'))
                    {{ $appointment->getEnd()|dmyHm }}
                @else
                    {{ $appointment->getEnd()->format('H:i') }}
                @endif
                <br>
                @if ($appointment->getLocation())
                    Locatie: {{ $appointment->getLocation() }}
                    <br>
                @endif
                {!! $appointment->getDescription() !!}
                @if ($appointment->getUrl())
                    <br>
                    <a class="btn btn-primary" href="{{ $appointment->getUrl() }}">Meer informatie</a>
                @endif
            </div>
        </div>
    @endforeach
@endsection
