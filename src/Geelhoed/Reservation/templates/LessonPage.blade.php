@extends('Index')

@section('contents')
    Aantal plaatsen: {{ $hour->capacity }}<br>
    Reserveringen: {{ count($reservations) }}<br>
    Vrije plaatsen: {{ $hour->capacity - count($reservations) }}<br><br>

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Naam</th>
                <th>Gereserveerd op</th>
            </tr>
        </thead>
        <tbody>
            @php /** @var \Cyndaron\Geelhoed\Reservation\Reservation $reservation */ @endphp
            @foreach ($reservations as $reservation)
                <tr>
                    <td>{{ $reservation->name }}</td>
                    <td>{{ $reservation->created|dmyHm }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

@endsection
