@extends ('Index')

@section ('contents')
    @php
        /** @var \Cyndaron\Geelhoed\Contest\Model\Contest $contest */
        /** @var \Cyndaron\Geelhoed\Contest\Model\ContestRepository $contestRepository */
        /** @var \Cyndaron\Geelhoed\Contest\Model\ContestDateRepository $contestDateRepository */
        /** @var \Cyndaron\Geelhoed\Contest\Model\ContestMemberRepository $contestMemberRepository */
        $sport = $contest->sport;
    @endphp

    @if ($canManage)
        @component('View/Widget/Toolbar2')
            @slot('left')
                @include('View/Widget/Button', ['kind' => 'list', 'link' => '/contest/manageOverview', 'title' => 'Wedstrijdbeheer', 'text' => 'Wedstrijdbeheer'])
            @endslot
            @slot('middle')
                <button id="gcv-add-date" class="btn btn-success" data-bs-toggle="modal"
                        data-bs-target="#gcv-add-date-dialog">Datum toevoegen
                </button>
            @endslot
            @slot('right')
                <form class="form-inline" method="post" action="/contest/addAttachment/{{ $contest->id }}"
                      enctype="multipart/form-data">
                    <label class="btn btn-outline-cyndaron" for="newFile">Bijlage toevoegen</label>
                    <input class="d-none" type="file" id="newFile" name="newFile" required>
                    <input type="hidden" name="csrfToken"
                           value="{{ $addAttachmentToken }}">
                    <input class="btn btn-primary" type="submit" value="Uploaden">
                </form>
            @endslot
        @endcomponent
    @endif

    @if ($due > 0.00)
        <div class="alert alert-warning">
            Let op: er staan nog betalingen open. Inschrijvingen zijn pas definitief als er is betaald.<br>
            <a href="/contest/payFullDue" class="btn btn-primary">Betalen</a>
        </div>
    @endif

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
                        <li>
                            @php $classes = $contestDateRepository->getClasses($contestDate) @endphp
                            {{ $contestDate->start|dmyHm }}@if (count($classes) > 0)
                                :
                            @endif
                            @foreach($classes as $class)
                                {{ $class->name }}@if (!$loop->last)
                                    ,
                                @endif
                            @endforeach
                            @if ($canManage)
                                <form method="post" action="/contest/deleteDate/{{ $contestDate->id }}"
                                      class="inline-button-form">
                                    <input type="hidden" name="csrfToken" value="{{ $deleteDateCsrfToken }}">
                                    <button class="btn btn-sm btn-danger" type="submit" title="Deze datum verwijderen">
                                        @include('View/Widget/Icon', ['type' => 'delete'])</button>
                                </form>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </td>
        </tr>
        <tr>
            <th>Sport:</th>
            <td>{{ $sport->name }}</td>
        </tr>
        <tr>
            <th>Inschrijfgeld:</th>
            <td>{{ $contest->price|euro }}</td>
        </tr>
        <tr>
            <th>Inschrijven voor:</th>
            <td>{{ $contest->registrationDeadline|dmyHm }}</td>
        </tr>
        <tr>
            <th>Beschrijving:</th>
            <td>{!! nl2br(trim($contest->description)) !!}</td>
        </tr>
        @php $attachments = $contest->getAttachments() @endphp
        @if (count($attachments) > 0)
            <tr>
                <th>Documenten:</th>
                <td>
                    <ul>
                        @foreach ($attachments as $attachment)
                            <li>
                                <a href="/uploads/contest/{{ $contest->id }}/attachments/{{ $attachment }}">{{ $attachment }}</a>
                                @if ($canManage)
                                    <form method="post" action="/contest/deleteAttachment/{{ $contest->id }}"
                                          class="inline-button-form">
                                        <input type="hidden" name="csrfToken" value="{{ $deleteCsrfToken }}">
                                        <input type="hidden" name="filename" value="{{ $attachment }}">
                                        <button class="btn btn-sm btn-danger" type="submit"
                                                title="Dit bestand verwijderen">
                                            @include('View/Widget/Icon', ['type' => 'delete'])</button>
                                    </form>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </td>
            </tr>
        @endif
        @if ($mayViewOtherContestants)
            <tr>
                <th>Deelnemers:</th>
                <td>
                    @foreach ($contestMemberRepository->fetchAllByContest($contest) as $contestMember)
                        @php $member = $contestMember->member @endphp
                        {{ $member->profile->getFullName() }}@if (!$loop->last)
                            ,
                        @endif
                    @endforeach
                </td>
            </tr>
        @endif
    </table>

    @php /** @var \Cyndaron\Geelhoed\Member\Member[] $controlledMembers */@endphp
    @if (time() > strtotime($contest->registrationDeadline))
        De deadline voor het inschrijven is verlopen.
    @elseif (count($controlledMembers) === 0)
        Om te kunnen omschrijven moet je wedstrijdjudoka/jitsuka zijn (of ouder van) en ingelogd hebben.

        <a href="/user/login" class="btn btn-primary">Inloggen</a>
    @else
        <h3>Inschrijven</h3>
        <div>
            @php $canChange = $contestRepository->registrationCanBeChanged($contest, $profile); @endphp
            @foreach ($controlledMembers as $controlledMember)
                @php $subscription = $contestMemberRepository->fetchByContestAndMember($contest, $controlledMember) @endphp
                @include('Geelhoed/Contest/Page/MemberSubscriptionStatus', [
                    'contest' => $contest,
                    'contestMember' => $subscription,
                    'member' => $controlledMember,
                    'canChange' => $canChange,
                    'csrfToken' => $cancelSubscriptionCsrfToken,
                    'memberRepository' => $memberRepository,
                ])
            @endforeach
        </div>

    @endif

    @if ($canManage)
        @component('View/Widget/Modal', ['id' => 'gcv-add-date-dialog', 'title' => 'Datum toevoegen', 'sizeClass' => 'modal-lg'])
            @slot('body')
                <form id="gcv-add-date-form" method="post">
                    <input type="hidden" id="gcv-edit-id" name="contestId" value="{{ $contest->id }}">
                    <input type="hidden" name="csrfToken" value="{{ $addDateCsrfToken }}">

                    @component('View/Widget/Form/FormWrapper', ['id' => 'gcv-edit-date', 'label' => 'Datum, starttijd, endtijd'])
                        @slot('right')
                            <input id="gcv-edit-date" name="date" type="date" class="form-control form-control-inline"
                                   required>
                            <input id="gcv-edit-startTime" name="startTime" type="time"
                                   class="form-control form-control-inline"
                                   required>
                            <input id="gcv-edit-endTime" name="endTime" type="time"
                                   class="form-control form-control-inline"
                                   required>
                        @endslot
                    @endcomponent
                    <h4>Leeftijdklassen</h4>
                    <div style="column-count: 2;">
                        @foreach ($contestClasses as $class)
                            @include ('View/Widget/Form/Checkbox', ['id' => 'class-' . $class->id, 'description' => $class->name, 'checked' => false])
                        @endforeach
                    </div>
                </form>

            @endslot
            @slot('footer')
                <button type="button" class="btn btn-success" id="gcv-add-date-save">Opslaan</button>
                <button type="button" class="btn btn-outline-cyndaron" data-bs-toggle="modal"
                        data-bs-target="#gcv-add-date-dialog">Annuleren
                </button>
            @endslot
        @endcomponent
    @endif
@endsection
