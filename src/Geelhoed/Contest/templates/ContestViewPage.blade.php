@extends ('Index')

@section ('contents')
    @php
        /** @var \Cyndaron\Geelhoed\Contest\Contest $contest */
        /** @var \Cyndaron\Geelhoed\Member\Member $loggedInMember */
        $sport = $contest->getSport();
    @endphp

    @if ($canManage)
        @component('Widget/Toolbar2')
            @slot('left')
                @include('Widget/Button', ['kind' => 'list', 'link' => '/contest/manageOverview', 'title' => 'Wedstrijdbeheer', 'text' => 'Wedstrijdbeheer'])
            @endslot
            @slot('middle')
                <button id="gcv-add-date" class="btn btn-success" data-toggle="modal" data-target="#gcv-add-date-dialog">Datum toevoegen</button>
            @endslot
            @slot('right')
                <form class="form-inline" method="post" action="/contest/addAttachment/{{ $contest->id }}" enctype="multipart/form-data">
                    <label class="btn btn-outline-cyndaron" for="newFile">Bijlage toevoegen</label>
                    <input class="d-none" type="file" id="newFile" name="newFile" required>
                    <input type="hidden" name="csrfToken" value="{{ \Cyndaron\User\User::getCSRFToken('contest', 'addAttachment') }}">
                    <input class="btn btn-primary" type="submit" value="Uploaden">
                </form>
            @endslot
        @endcomponent
    @endif

    <table>
        <tr><th>Locatie: </th><td>{{ $contest->location }}</td></tr>
        <tr><th>Data: </th><td>
                <ul>
                @foreach ($contest->getDates() as $contestDate)
                    <li>
                    @php $classes = $contestDate->getClasses() @endphp
                    {{ $contestDate->datetime|dmyHm }}@if (count($classes) > 0):@endif
                    @foreach($classes as $class)
                        {{ $class->name }}@if (!$loop->last), @endif
                    @endforeach
                    @if ($canManage)
                        <form method="post" action="/contest/deleteDate/{{ $contestDate->id }}" style="display: inline;">
                            <input type="hidden" name="csrfToken" value="{{ $deleteDateCsrfToken }}">
                            <button class="btn btn-sm btn-danger" type="submit" title="Deze datum verwijderen"><span class="glyphicon glyphicon-trash"></span></button>
                        </form>
                    @endif
                    </li>
                @endforeach
                </ul>
            </td>
        </tr>
        <tr><th>Sport: </th><td>{{ $sport->name }}</td></tr>
        <tr><th>Inschrijven voor: </th><td>{{ $contest->registrationDeadline|dmyHm }}</td></tr>
        <tr><th>Beschrijving:</th><td>{{ $contest->description }}</td></tr>
        @php $attachments = $contest->getAttachments() @endphp
        @if (count($attachments) > 0)
        <tr><th>Documenten:</th><td>
            <ul>
            @foreach ($attachments as $attachment)
                <li>
                    <a href="/uploads/contest/{{ $contest->id }}/attachments/{{ $attachment }}">{{ $attachment }}</a>
                    @if ($canManage)
                        <form method="post" action="/contest/deleteAttachment/{{ $contest->id }}" style="display: inline;">
                            <input type="hidden" name="csrfToken" value="{{ $deleteCsrfToken }}">
                            <input type="hidden" name="filename" value="{{ $attachment }}">
                            <button class="btn btn-sm btn-danger" type="submit" title="Dit bestand verwijderen"><span class="glyphicon glyphicon-trash"></span></button>
                        </form>
                    @endif
                </li>
            @endforeach
            </ul>
        </td></tr>
        @endif
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

    @if (time() > strtotime($contest->registrationDeadline))
        De deadline voor het inschrijven is verlopen.
    @elseif ($loggedInMember === null)
        Om te kunnen omschrijven moet je wedstrijdjudoka/jitsuka zijn en ingelogd hebben.
    @elseif(!$loggedInMember->isContestant)
        Om te kunnen omschrijven moet je wedstrijdjudoka/jitsuka zijn.
    @elseif ($contest->hasMember($loggedInMember))
        @php $subscription = \Cyndaron\Geelhoed\Contest\ContestMember::fetchByContestAndMember($contest, $loggedInMember); @endphp
        Je hebt je al aangemeld voor deze wedstrijd.
        @if (!$subscription->isPaid)
            <div class="alert alert-warning">Je hebt nog niet betaald. Je bent pas definitief ingeschreven wanneer je betaald hebt.</div>
        @endif
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
            @php $sportName = strtolower($sport->name); @endphp
            @component ('Widget/Form/FormWrapper', ['id' => 'graduationId', 'label' => "Band {$sportName}"])
                @slot('right')
                    <select id="graduationId" name="graduationId" class="form-control custom-select">
                        @foreach (\Cyndaron\Geelhoed\Graduation::fetchAllBySport($sport) as $graduation)
                            @php
                                $highestGraduation = $loggedInMember->getHighestGraduation($sport);
                                $selected = ($highestGraduation !== null && $highestGraduation->id === $graduation->id) ? 'selected' : ''
                            @endphp
                            <option value="{{ $graduation->id }}" {{ $selected }}>{{ $graduation->name }}</option>
                        @endforeach
                    </select>
                @endslot
            @endcomponent
            @component ('Widget/Form/FormWrapper', ['id' => 'weight', 'label' => 'Gewicht (hele kg)'])
                @slot('right')
                    <input id="weight" name="weight" type="number" class="form-control" required>
                @endslot
            @endcomponent
            @include('Widget/Form/Textarea', ['id' => 'comments', 'label' => 'Opmerkingen'])
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

    @if ($canManage)
        @component('Widget/Modal', ['id' => 'gcv-add-date-dialog', 'title' => 'Datum toevoegen', 'sizeClass' => 'modal-lg'])
            @slot('body')
                <form id="gcv-add-date-form" method="post">
                    <input type="hidden" id="gcv-edit-id" name="contestId" value="{{ $contest->id }}">
                    <input type="hidden" name="csrfToken" value="{{ $addDateCsrfToken }}">

                    @component('Widget/Form/FormWrapper', ['id' => 'gcv-edit-date', 'label' => 'Datum en tijd'])
                        @slot('right')
                            <input id="gcv-edit-date" name="date" type="date" class="form-control form-control-inline" required>
                            <input id="gcv-edit-time" name="time" type="time" class="form-control form-control-inline" required>
                        @endslot
                    @endcomponent
                    <h4>Leeftijdklassen</h4>
                    <div style="column-count: 2;">
                        @foreach (\Cyndaron\Geelhoed\Contest\ContestClass::fetchAll() as $class)
                            @include ('Widget/Form/Checkbox', ['id' => 'class-' . $class->id, 'description' => $class->name, 'checked' => false])
                        @endforeach
                    </div>
                </form>

            @endslot
            @slot('footer')
                <button type="button" class="btn btn-success" id="gcv-add-date-save">Opslaan</button>
                <button type="button" class="btn btn-outline-cyndaron" data-toggle="modal" data-target="#gcv-add-date-dialog">Annuleren</button>
            @endslot
        @endcomponent
    @endif
@endsection