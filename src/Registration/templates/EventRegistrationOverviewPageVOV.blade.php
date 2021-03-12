@extends ('Index')

@section ('contents')
    @component ('View/Widget/Toolbar')
        @slot('left')
            <a class="btn btn-outline-cyndaron" href="/pagemanager/event">&laquo; Terug naar overzicht evenementen</a>
        @endslot
    @endcomponent

    <table class="overzichtInschrijvingen table table-striped">
        <thead>
            <tr class="rotate">
                <th class="rotate">
                    <div><span>Inschrijvingsnummer</span></div>
                </th>
                <th class="rotate">
                    <div><span>Persoonsgegevens</span></div>
                </th>
                <th class="rotate">
                    <div><span>Zanggegevens</span></div>
                </th>
                <th class="rotate">
                    <div><span>Adresgegevens</span></div>
                </th>
                <th class="rotate">
                    <div><span>Inschrijfdatum</span></div>
                </th>
                <th class="rotate">
                    <div><span>Opmerkingen</span></div>
                </th>
                <th class="rotate">
                    <div><span>Totaal</span></div>
                </th>
                <th class="rotate">
                    <div><span>Status</span></div>
                </th>
                <th class="column-actions"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($registrations as $registration)
            @php $registrationId = $registration->id @endphp
            <tr>
                <td>{{ $registrationId }}</td>
                <td>
                    {{ $registration->initials }} {{ $registration->lastName }}
                    @if ($registration->birthYear)
                        <br>Leeftijdcategorie: {{ \Cyndaron\Registration\Util::birthYearToCategory($registration->birthYear) }}
                    @endif
                </td>
                <td>
                    {{ $registration->vocalRange }}<br>
                    {{ $registration->currentChoir }}
                    @if ($registration->participatedBefore)
                        <br>Deed eerder mee: {{ $registration->kleinkoorExplanation }}
                    @endif
                    <br>Koorvoorkeur: {{ $registration->choirPreference }}
                </td>
                <td>
                    <a href="mailto:{{ $registration->email }}">{{ $registration->email }}</a><br>
                    {{ $registration->street }} @if ($registration->houseNumber > 0){{ $registration->houseNumber }} {{ $registration->houseNumberAddition }} @endif<br>
                    {{ $registration->postcode }} {{ $registration->city }}
                </td>
                <td>
                    {{ date('d-m-Y', strtotime($registration->created)) }}
                </td>
                <td>
                    {{ $registration->comments }}
                </td>

                <td>{{ $registration->calculateTotal()|euro }}</td>
                <td>{{ $registration->getStatus() }}</td>
                <td>
                    <div class="btn-group btn-group-sm">
                        @if (!$registration->isPaid)
                            <button data-registration-id="{{ $registrationId }}" data-csrf-token-set-is-paid="{{ Cyndaron\User\User::getCSRFToken('event-registration', 'setIsPaid') }}" title="Markeren als betaald" class="eom-registration-set-paid btn btn-sm btn-success"><span class="glyphicon glyphicon-eur"></span></button>
                        @endif
                        <button data-registration-id="{{ $registrationId }}" data-csrf-token-delete="{{ Cyndaron\User\User::getCSRFToken('event-registration', 'delete') }}" title="Inschrijving verwijderen" class="eom-registration-delete btn btn-sm btn-danger"><span class="glyphicon glyphicon-trash"></span></button>
                    </div>
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
                    <td>{{ $counts[1]['num'] }}</td>
                    <td>{{ $counts[0]['num'] + $counts[1]['num'] }}</td>
                    <td>{{ $counts[0]['amount']|euro }}</td>
                    <td>{{ $counts[1]['amount']|euro }}</td>
                    <td>{{ ($counts[0]['amount'] + $counts[1]['amount'])|euro }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
