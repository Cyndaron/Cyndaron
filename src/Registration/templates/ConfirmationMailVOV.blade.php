Hartelijk dank voor uw inschrijving voor {{ $event->name }}.
Zodra de inschrijving is gesloten krijgt u van ons een nader bericht over het vervolg.

Hieronder volgt een overzicht van uw inschrijving.

Inschrijvingsnummer: {{ $registration->id }}

Achternaam: {{ $registration->lastName }}
Voorletters: {{ $registration->initials }}
Stemsoort: {{ $registration->vocalRange }}
Lid van: {{ $registration->currentChoir ?: 'Geen koor / ander koor' }}
Voorkeur koor I/II: {{ $registration->choirPreference }}

@foreach ($extraFields as $description => $contents)
@if ($description === 'Geboortejaar') @continue @endif
@if (!empty(trim((string)$contents))){{ $description }}: {{ $contents }}
@endif
@endforeach

@if (!$event->hideRegistrationFee)Totaalbedrag: {{ \Cyndaron\View\Template\ViewHelpers::formatEuro($registrationTotal) }}@endif
