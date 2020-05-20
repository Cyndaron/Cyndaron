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
        @foreach ($members as $member)
            <tr>
                <td>{{ $member->getProfile()->getFullName() }}</td>
            </tr>
        @endforeach
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