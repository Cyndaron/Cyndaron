@extends ('Index')

@section ('contents')

    Openstaand inschrijfgeld: {{ $due|euro }}
    @if ($due > 0.0)
        <a class="btn btn-primary" href="/contest/payFullDue">Betalen</a>
    @endif

    @php /** @var \Cyndaron\Geelhoed\Contest\Model\Contest[] $contests */ @endphp
    @php /** @var \Cyndaron\Geelhoed\Contest\Model\ContestRepository $contestRepository */ @endphp
    @php /** @var \Cyndaron\Geelhoed\Contest\Model\ContestDateRepository $contestDateRepository */ @endphp
    @php /** @var \Cyndaron\Geelhoed\Contest\Model\ContestMemberRepository $contestMemberRepository */ @endphp
    @foreach ($contests as $contest)
        @php $canChange = $contestRepository->registrationCanBeChanged($contest, $profile); @endphp
        <div class="card location-card">
            <div class="card-header">
                <h2><a href="/contest/view/{{ $contest->id }}">{{ $contest->name }}</a></h2>
            </div>
            <div class="card-body">
                <table>
                    <tr>
                        <th>Locatie:</th>
                        <td>{{ $contest->location }}</td>
                    </tr>
                    <tr>
                        <th>Data:</th>
                        <td>
                            <ul>
                                @foreach ($contestDateRepository->fetchAllByContest($contest) as $contestDate)
                                    @php $classes = $contestDateRepository->getClasses($contestDate) @endphp
                                    <li>
                                        {{ $contestDate->start|dmyHm }}@if (count($classes) > 0)
                                            :
                                        @endif
                                        @foreach($classes as $class)
                                            {{ $class->name }}@if (!$loop->last)
                                                ,
                                            @endif
                                        @endforeach
                                    </li>
                                @endforeach
                            </ul>

                        </td>
                    </tr>
                    <tr>
                        <th>Inschrijfgeld:</th>
                        <td>{{ $contest->price|euro }}</td>
                    </tr>
                </table>
                <a role="button" class="btn btn-outline-cyndaron" href="/contest/view/{{ $contest->id }}">Meer
                    informatie</a>

                <h6 class="mt-3">Ingeschreven</h6>

                <div>
                    @foreach ($contestMemberRepository->fetchAllByContestAndMembers($contest, $controlledMembers) as $contestMember)
                        @php $member = $contestMember->member; @endphp
                        @include('Geelhoed/Contest/Page/MemberSubscriptionStatus', [
                            'contest' => $contest,
                            'contestMember' => $contestMember,
                            'member' => $member,
                            'canChange' => $canChange,
                            'csrfToken' => $cancelSubscriptionCsrfToken,
                            'memberRepository' => $memberRepository,
                        ])
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
@endsection
