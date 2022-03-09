Hartelijk dank voor uw inschrijving voor {{ $event->name }}.
Zodra de inschrijving is gesloten krijgt u van ons een nader bericht over het vervolg.

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
