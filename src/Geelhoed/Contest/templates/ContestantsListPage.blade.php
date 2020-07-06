@extends ('Index')

@section ('contents')
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Naam</th>
                <th>Adres</th>
                <th>Geboortedatum</th>
                <th>Banden</th>
            </tr>
        </thead>
        <tbody>
            @php /** @var \Cyndaron\Geelhoed\Member\Member[] $contestants */ @endphp
            @foreach ($contestants as $contestant)
                @php $profile = $contestant->getProfile() @endphp
                <tr>
                    <td>{{ $profile->lastName }} {{ $profile->tussenvoegsel }} {{ $profile->firstName }}</td>
                    <td>
                        {{ $profile->street }} {{ $profile->houseNumber }} {{ $profile->houseNumberAddition }}<br>
                        {{ $profile->postalCode }} {{ $profile->city }}
                    </td>
                    <td>@if ($profile->dateOfBirth !== null){{ $profile->dateOfBirth|dmy }}@endif</td>
                    <td>
                        @foreach ($sports as $sport)
                            @php $highest = $contestant->getHighestGraduation($sport) @endphp
                            @if ($highest !== null) {{ $sport->name }}: {{ $highest->name }}@endif
                        @endforeach
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection