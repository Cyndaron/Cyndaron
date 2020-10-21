@extends('Index')

@section('contents')
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Datum</th>
                <th>Lesuur</th>
                <th>Aantal reserveringen</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($stats as $stat)
                @php
                    /** @var \Cyndaron\Geelhoed\Hour\Hour $hour */
                    $hour = $hours[$stat['hourId']];
                    /** @var \Cyndaron\Geelhoed\Location\Location $location */
                    $location = $hour->getLocation();
                @endphp
                <tr>
                    <td>{{ $stat['date']|dmy }}</td>
                    <td>{{ $location->getName() }} {{ $hour->getRange() }}</td>
                    <td>{{ $stat['count'] }}</td>
                    <td><a href="/reservation/lesson/{{ $stat['hourId'] }}/{{ $stat['date'] }}" class="btn btn-outline-cyndaron">Bekijken</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
