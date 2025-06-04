@extends ('Index')

@section ('contents')
    @component('View/Widget/Toolbar')
        @slot('right')
            @include('View/Widget/Button', ['kind' => 'th', 'link' => '/contest/contestantsListExcel/', 'text' => 'Excel-export'])
        @endslot
    @endcomponent

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Naam</th>
                <th>Geslacht</th>
                <th>Adres</th>
                <th>Geboortedatum</th>
                <th>Banden</th>
                <th>JBN-nummer</th>
                <th>Acties</th>
            </tr>
        </thead>
        <tbody>
            @php /** @var \Cyndaron\Geelhoed\Member\Member[] $contestants */ @endphp
            @php /** @var \Cyndaron\Geelhoed\Member\MemberRepository $memberRepository */ @endphp
            @foreach ($contestants as $contestant)
                @php $profile = $contestant->profile @endphp
                <tr>
                    <td>{{ $profile->lastName }} {{ $profile->tussenvoegsel }} {{ $profile->firstName }}</td>
                    <td>{{ $profile->getGenderDisplay() }}</td>
                    <td>
                        {{ $profile->street }} {{ $profile->houseNumber }} {{ $profile->houseNumberAddition }}<br>
                        {{ $profile->postalCode }} {{ $profile->city }}
                    </td>
                    <td>@if ($profile->dateOfBirth !== null){{ $profile->dateOfBirth|dmy }}@endif</td>
                    <td>
                        @foreach ($sports as $sport)
                            @php $highest = $memberRepository->getHighestGraduation($contestant, $sport) @endphp
                            @if ($highest !== null) {{ $sport->name }}: {{ $highest->name }}@endif
                        @endforeach
                    </td>
                    <td>{{ $contestant->jbnNumber }}</td>
                    <td>
                        <form method="post" action="/contest/removeAsContestant">
                            <input type="hidden" name="csrfToken" value="{{ $tokenHandler->get('contest', 'removeAsContestant') }}"/>
                            <input type="hidden" name="memberId" value="{{ $contestant->id }}"/>
                            <button type="submit" class="btn btn-outline-cyndaron" title="Status wedstrijdjudoka verwijderen">
                                @include('View/Widget/Icon', ['type' => 'delete'])
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
