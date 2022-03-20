Hartelijk dank voor uw inschrijving voor {{ $event->name }}.

@if ($registrationTotal > 0)Na betaling is uw inschrijving definitief.

    Gebruik bij het betalen de volgende gegevens:
    Rekeningnummer: NL06 INGB 0000 5459 25 t.n.v. Vlissingse Oratorium Vereniging
    Bedrag: {{ $registrationTotal|euro }}
    Onder vermelding van: inschrijvingsnummer {{ $registration->id }}
@endif

Hieronder volgt een overzicht van uw inschrijving.

Inschrijvingsnummer: {{ $registration->id }}

Achternaam: {{ $registration->lastName }}
Voornaam: {{ $registration->initials }}
Woonplaats: {{ $registration->city }}
Telefoon: {{ $registration->phone }}
Stemsoort: {{ $registration->vocalRange }}
Lid van: {{ $registration->currentChoir ?: 'Geen koor / ander koor' }}
Leeftijdscategorie: {{ \Cyndaron\Registration\Util::birthYearToCategory($event, $registration->birthYear) }}
Opmerkingen: {{ $registration->comments }}

@if (!$event->hideRegistrationFee)Totaalbedrag: {{ \Cyndaron\View\Template\ViewHelpers::formatEuro($registrationTotal) }}@endif
