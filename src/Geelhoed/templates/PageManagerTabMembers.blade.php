<table class="table table-striped table-bordered pm-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Naam</th>
            <th>Trainingen</th>
            <th>Contactgegevens</th>
            <th>Acties</th>
        </tr>
    </thead>
    <tbody>
        @php /** @var \Cyndaron\Geelhoed\Member[] $members */ @endphp
        @foreach ($members as $member)
            <tr>
                <td>{{ $member->id }}</td>
                @php $profile = $member->getProfile() @endphp
                <td>
                    {{ $profile->getFullName() }}
                </td>
                <td>
                    @foreach ($member->getHours() as $hour)
                        {{ \Cyndaron\Util::getWeekday($hour->day) }} {{ $hour->from|hm }} - {{ $hour->until|hm }} ({{ $hour->sport }}, {{ $hour->getLocation()->getName() }})
                        @if (!$loop->last) <br>@endif
                    @endforeach
                </td>
                <td>
                    E-mail: {{ $member->email }}
                    @foreach ($member->getPhoneNumbers() as $phoneNumber)
                        <br>
                        Tel.nr. {{ $loop->iteration }}: {{ $phoneNumber }}
                    @endforeach
                </td>
                <td>
                    <div class="btn-group">
                        <a class="btn btn-outline-cyndaron btn-sm" href="/editor/member/{{ $member->id }}"><span class="glyphicon glyphicon-pencil" title="Bewerk dit lid"></span></a>
                        <button class="btn btn-danger btn-sm pm-delete" data-type="member" data-id="{{ $member->id }}" data-csrf-token="{{ \Cyndaron\User\User::getCSRFToken('member', 'delete') }}"><span class="glyphicon glyphicon-trash" title="Verwijder deze locatie"></span></button>
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
