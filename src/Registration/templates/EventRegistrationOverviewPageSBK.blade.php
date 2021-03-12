@extends ('Index')
@php /** @var \Cyndaron\Registration\Event $event */@endphp

@section ('contents')
    @component('View/Widget/Toolbar')
        @slot('left')
            <a class="btn btn-outline-cyndaron" href="/pagemanager/eventSbk">&laquo; Terug naar overzicht evenementen</a>
        @endslot
    @endcomponent

    <table class="overzichtInschrijvingen table table-striped">
        <thead>
            <tr class="rotate">
                <th class="rotate">
                    <div><span>Aanmeldingsnummer</span></div>
                </th>
                <th class="rotate">
                    <div><span>Persoonsgegevens</span></div>
                </th>
                <th class="rotate">
                    <div><span>Zanggegevens</span></div>
                </th>
                <th class="rotate">
                    <div><span>Contactgegevens</span></div>
                </th>
                <th class="rotate">
                    <div><span>Aanmeldingsdatum</span></div>
                </th>
                <th class="rotate">
                    <div><span>Opmerkingen</span></div>
                </th>
                <th class="rotate">
                    <div><span>Totaalbedrag</span></div>
                </th>
                <th class="rotate">
                    <div><span>Status</span></div>
                </th>
                <th class="column-actions"></th>
            </tr>
        </thead>
        <tbody>
            @php /** @var \Cyndaron\Registration\Registration[] $registrations */@endphp
            @foreach ($registrations as $registration)
            @php $registrationId = $registration->id @endphp
            <tr>
                <td>{{ $registrationId }}</td>
                <td>
                    {{ $registration->initials }} {{ $registration->lastName }}<br>
                </td>
                <td>
                    {{ $registration->vocalRange }}<br>
                    Zingt momenteel bij: {{ $registration->currentChoir }}<br>
                    Heeft {{ $registration->choirExperience }} jaar koorervaring
                    @if ($registration->performedBefore)
                        <br>Heeft het werk eerder uitgevoerd
                    @endif
                </td>
                <td>
                    <a href="mailto:{{ $registration->email }}">{{ $registration->email }}</a><br>
                    {{ $registration->phone }}<br>
                    {{ $registration->city }}
                </td>
                <td>
                    {{ date('d-m-Y', $registration->created) }}
                </td>
                <td>
                    {{ $registration->comments }}
                </td>

                <td>{{ $registration->calculateTotal()|euro }}</td>
                <td>{{ $registration->getStatus() }}</td>
                <td>
                    <div class="btn-group btn-group-sm">
                        @if ($registration->approvalStatus === Cyndaron\Registration\Registration::APPROVAL_UNDECIDED)
                            <button data-registration-id="{{ $registrationId }}" data-approval-status="{{ \Cyndaron\Registration\Registration::APPROVAL_APPROVED }}" data-csrf-token-set-approval-status="{{ \Cyndaron\User\User::getCSRFToken('eventSbk-registration', 'setApprovalStatus') }}" title="Aanmelding goedkeuren" class="eom-registration-set-approval-status btn btn-sm btn-success"><span class="glyphicon glyphicon-ok"></span></button>
                            <button data-registration-id="{{ $registrationId }}" data-approval-status="{{ \Cyndaron\Registration\Registration::APPROVAL_DISAPPROVED }}" data-csrf-token-set-approval-status="{{ \Cyndaron\User\User::getCSRFToken('eventSbk-registration', 'setApprovalStatus') }}" title="Aanmelding afkeuren" class="eom-registration-set-approval-status btn btn-sm btn-warning"><span class="glyphicon glyphicon-remove"></span></button>
                        @endif
                        @if ($registration->shouldPay())
                            <button data-registration-id="{{ $registrationId }}" data-csrf-token-set-is-paid="{{ \Cyndaron\User\User::getCSRFToken('eventSbk-registration', 'setIsPaid') }}" title="Markeren als betaald" class="eom-registration-set-paid btn btn-sm btn-success"><span class="glyphicon glyphicon-eur"></span></button>
                        @endif
                    </div>
                    <button data-registration-id="{{ $registrationId }}" data-csrf-token-delete="{{ \Cyndaron\User\User::getCSRFToken('eventSbk-registration', 'delete') }}" title="Aanmelding verwijderen" class="eom-registration-delete btn btn-sm btn-danger"><span class="glyphicon glyphicon-trash"></span></button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Zangstem</th>
                <th>Aantal niet betaald</th>
                <th>Aantal wel betaald</th>
                <th>Aantal totaal</th>
                <th>Bedrag niet betaald</th>
                <th>Bedrag wel betaald</th>
                <th>Bedrag totaal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($totals as $vocalRange => $counts)
                <tr>
                    <td>@if ($vocalRange === 'Totaal')<b>{{ $vocalRange }}</b>@else{{ $vocalRange }}@endif</td>
                    <td>{{ $counts[0]['num'] }}</td>
                    <td>{{ $counts[0]['num'] }}</td>
                    <td>{{ $counts[0]['num'] + $counts[0]['num'] }}</td>
                    <td>{{ $counts[0]['amount']|euro }}</td>
                    <td>{{ $counts[0]['amount']|euro }}</td>
                    <td>{{ ($counts[0]['amount'] + $counts[0]['amount'])|euro }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
