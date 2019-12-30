@extends ('Index')

@section ('contents')
    @php
        $lastCity = '';
        /** @var \Cyndaron\Geelhoed\Location\Location[] $locations*/
    @endphp
    @foreach ($locations as $location)
        @if ($location->city !== $lastCity)<h2>{{ $location->city }}</h2>@php $lastCity = $location->city @endphp@endif

        <h3>{{ $location->name ?: ($location->street) }}</h3>
        {{ $location->street }} {{ $location->houseNumber }}<br>
        {{ $location->postalCode }} {{ $location->city }}<br>

        <a href="/location/view/{{ $location->id }}">Meer informatie</a>

        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Groep</th><th>Van</th><th>Tot</th><th>Training</th><th>Afdeling</th>
                </tr>
            </thead>
            <tbody>

            @php $department = $location->getDepartment() @endphp
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
    @endforeach
@endsection