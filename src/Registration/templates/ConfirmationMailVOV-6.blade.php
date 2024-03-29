Hartelijk dank voor uw inschrijving voor {{ $event->name }}.
Na 1 oktober krijgt u een nader bericht van ons, inclusief een betalingsverzoek.
Voor vragen kunt u e-mailen naar info@zeeuwsconcertkoor.nl

Bestuur VOV / Zeeuws Concertkoor


Hieronder volgt een overzicht van uw inschrijving.

Inschrijvingsnummer: {{ $registration->id }}

Achternaam: {{ $registration->lastName }}
Voornaam: {{ $registration->initials }}
Woonplaats: {{ $registration->city }}
Stemsoort: {{ $registration->vocalRange }}
Lid van: {{ $registration->currentChoir ?: 'Geen koor / ander koor' }}
Opmerkingen: {{ $registration->comments }}

@if (!$event->hideRegistrationFee)Totaalbedrag: {{ \Cyndaron\View\Template\ViewHelpers::formatEuro($registrationTotal) }}@endif
