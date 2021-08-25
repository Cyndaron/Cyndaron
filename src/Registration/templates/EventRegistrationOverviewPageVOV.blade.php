@extends ('Index')

@section ('contents')
    @component ('View/Widget/Toolbar')
        @slot('left')
            <a class="btn btn-outline-cyndaron" href="/pagemanager/event">&laquo; Terug naar overzicht evenementen</a>
        @endslot
        @slot('right')
            @include('View/Widget/Button', ['kind' => 'th', 'link' => '/event/registrationListExcel/' . $event->id, 'text' => 'Excel-export'])
        @endslot
    @endcomponent

    <table class="overzichtBestellingen table table-striped">
        <thead>
            <tr class="rotate">
                <th class="rotate">
                    <div><span>Inschrijvingsnummer</span></div>
                </th>
                <th class="rotate">
                    <div><span>Naam</span></div>
                </th>
                <th class="rotate">
                    <div><span>Leeftijdscategorie</span></div>
                </th>
                <th class="rotate">
                    <div><span>Stemsoort</span></div>
                </th>
                <th class="rotate">
                    <div><span>Koor</span></div>
                </th>
                <th class="rotate">
                    <div><span>E-mailadres</span></div>
                </th>
                <th class="rotate">
                    <div><span>Woonplaats</span></div>
                </th>
                <th class="rotate">
                    <div><span>Inschrijfdatum</span></div>
                </th>
                <th class="rotate">
                    <div><span>Opmerkingen</span></div>
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
                </td>
                <td>
                    @if ($registration->birthYear)
                        {{ \Cyndaron\Registration\Util::birthYearToCategory($event, $registration->birthYear) }}
                    @endif
                </td>
                <td>
                    {{ $registration->vocalRange }}
                </td>
                <td>
                    {{ $registration->currentChoir }}
                </td>
                <td>
                    <a href="mailto:{{ $registration->email }}">{{ $registration->email }}</a>
                </td>
                <td>
                    {{ $registration->city }}
                </td>
                <td>
                    {{ date('d-m-Y', strtotime($registration->created)) }}
                </td>
                <td>
                    {{ $registration->comments }}
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button data-registration-id="{{ $registrationId }}" data-csrf-token-delete="{{ Cyndaron\User\User::getCSRFToken('event-registration', 'delete') }}" title="Inschrijving verwijderen" class="eom-registration-delete btn btn-sm btn-danger"><span class="glyphicon glyphicon-trash"></span></button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection
