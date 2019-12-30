@extends ('Index')

@section ('contents')
    @php
        /** @var \Cyndaron\Geelhoed\Location\Location $location */
        $department = $location->getDepartment();
    @endphp
    <p>
        {{ $location->street }} {{ $location->houseNumber }}<br>
        {{ $location->postalCode }} {{$location->city}}
    </p>

    <p><a href="{{ $location->getMapsLink() }}" target="_blank">Bekijken op Google Maps</a></p>

    <h3>Lessen</h3>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Groep</th><th>Van</th><th>Tot</th><th>Training</th><th>Afdeling</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($location->getHours() as $hour)
                <tr>
                    <td>{{ $hour->description }}</td>
                    <td>{{ $hour->from|hm }}</td>
                    <td>{{ $hour->until|hm }}</td>
                    <td>{{ $hour->getSportName() }}</td>
                    <td>{{ $department->name }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

@endsection