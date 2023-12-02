@extends ('Index')

@section ('contents')
    Inhoud

    @php /** @var \Cyndaron\Calendar\CalendarAppointment[] $appointments */ @endphp
    @foreach ($appointments as $appointment)
        {{ $appointment->getName() }}
    @endforeach
@endsection
