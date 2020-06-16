@extends ('Index')

@section ('contents')
    @php
        /** @var \Cyndaron\Geelhoed\Hour\Hour $hour */
        /** @var \Cyndaron\Geelhoed\Member\Member[] $members */
    @endphp
    <p>
        Trainingstype: {{ $hour->getSportName() }}<br>
        Groep: {{ $hour->description }}
    </p>

    <h2>Leden op deze training:</h2>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Naam</th>
                <th>Leeftijd</th>
                <th>Telefoonnummer(s)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($members as $member)
                @php $profile = $member->getProfile() @endphp
                <tr>
                    <td>{{ $profile->getFullName() }}</td>
                    <td>
                        @if (!empty($profile->dateOfBirth))
                            {{ $profile->getAge() }}
                            @if (date('m-d') === date('m-d', strtotime($profile->dateOfBirth))) (jarig) @endif
                        @endif
                    </td>
                    <td>
                        @foreach ($member->getPhoneNumbers() as $phoneNumber)
                            <a href="tel:{{ $phoneNumber }}">{{ $phoneNumber }}</a>@if (!$loop->last), @endif
                        @endforeach
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Trainingen op deze locatie</h2>
    <table class="table table-bordered table-striped">
        @foreach($hour->getLocation()->getHours($hour->departmentId) as $locationHour)
            <tr>
                @php $weekday = \Cyndaron\Template\ViewHelpers::getDutchWeekday($locationHour->day) @endphp
                <td><a href="/hour/memberList/{{ $locationHour->id }}">{{ $weekday }} {{ $locationHour->getRange() }}</a></td>
                <td>{{ $locationHour->getSportName() }}</td>
            </tr>
        @endforeach
    </table>

@endsection