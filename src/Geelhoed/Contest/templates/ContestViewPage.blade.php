@extends ('Index')

@section ('contents')
    @php
        /** @var \Cyndaron\Geelhoed\Contest\Contest $contest */
        /** @var \Cyndaron\Geelhoed\Member\Member $loggedInMember */
        $sport = $contest->getSport();
    @endphp

    <table>
        <tr><th>Locatie: </th><td>{{ $contest->location }}</td></tr>
        <tr><th>Datum en tijd: </th><td>{{ $contest->date|dmyHm }}</td></tr>
        <tr><th>Sport: </th><td>{{ $sport->name }}</td></tr>
        <tr><th>Inschrijven voor: </th><td>{{ $contest->participationDeadline|dmyHm }}</td></tr>
        @if ($mayViewOtherContestants)
            <tr>
                <th>Deelnemers: </th>
                <td>
                    @foreach ($contest->getContestMembers() as $contestMember)
                        @php $member = $contestMember->getMember() @endphp
                        {{ $member->getProfile()->getFullName() }}@if (!$loop->last), @endif
                    @endforeach
                </td>
            </tr>
        @endif
    </table>

    @if (time() > strtotime($contest->date))
        De deadline voor het inschrijven is verlopen.
    @else
        @if ($loggedInMember === null)
            Om te kunnen omschrijven moet je wedstrijdjudoka/jitsuka zijn en ingelogd hebben.
        @elseif(!$loggedInMember->isContestant)
            Om te kunnen omschrijven moet je wedstrijdjudoka/jitsuka zijn.
        @elseif ($contest->hasMember($loggedInMember))
            @php $subscription = \Cyndaron\Geelhoed\Contest\ContestMember::fetchByContestAndMember($contest, $loggedInMember); @endphp
            Je hebt je al ingeschreven voor deze wedstrijd.
            @if (!$subscription->isPaid)Je hebt nog niet betaald.@endif
        @else
            @php $loggedInProfile = \Cyndaron\User\User::getLoggedIn() @endphp
            <hr>
            <h3>Inschrijven</h3>
            <form method="post" action="/contest/subscribe/{{ $contest->id }}">
                <input type="hidden" name="csrfToken" value="{{ \Cyndaron\User\User::getCSRFToken('contest', 'subscribe') }}"/>
                @component ('Widget/Form/FormWrapper', ['label' => 'Naam'])
                    @slot('right')
                        {{ $loggedInProfile->getFullName() }}
                    @endslot
                @endcomponent
                @component ('Widget/Form/FormWrapper', ['label' => 'JBN-nummer'])
                    @slot('right')
                        {{ $loggedInMember->jbnNumber }} ({{ $loggedInMember->jbnNumberLocation }})
                    @endslot
                @endcomponent
                @component ('Widget/Form/FormWrapper', ['label' => 'E-mailadres'])
                    @slot('right')
                        {{ $loggedInMember->getEmail() }}
                    @endslot
                @endcomponent
                @component ('Widget/Form/FormWrapper', ['id' => 'graduationId', 'label' => 'Band'])
                    @slot('right')
                        <select id="graduationId" name="graduationId" class="form-control custom-select">
                            @foreach (\Cyndaron\Geelhoed\Graduation::fetchAllBySport($sport) as $graduation)
                                @php $selected = ($loggedInMember->getHighestGraduation($sport) == $graduation) ? 'selected' : '' @endphp
                                <option value="{{ $graduation->id }}" {{ $selected }}>{{ $graduation->name }}</option>
                            @endforeach
                        </select>
                    @endslot
                @endcomponent
                @component ('Widget/Form/FormWrapper', ['id' => 'weight', 'label' => 'Gewicht (hele kg)'])
                    @slot('right')
                        <input id="weight" name="weight" type="number" class="form-control">
                    @endslot
                @endcomponent
                @component ('Widget/Form/FormWrapper', ['label' => 'Inschrijfgeld'])
                    @slot('right')
                        {{ $contest->price|euro }}
                    @endslot
                @endcomponent

                <p>
                    Kloppen alle bovenstaande gegevens? Klik dan op de knop “Inschrijven” om te betalen.
                    Na betaling is je inschrijving definitief.
                </p>
                @component ('Widget/Form/FormWrapper')
                    @slot('right')
                        <input type="submit" class="btn btn-lg btn-primary" value="Inschrijven">
                    @endslot
                @endcomponent

            </form>

        @endif
    @endif
@endsection