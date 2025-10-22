Hartelijk dank voor uw inschrijving bij de Scratch Messiah.
Na betaling is uw inschrijving definitief. Eventueel bestelde kaarten voor vrienden en familie zullen op de avond van het concert voor u klaargelegd worden bij de ingang van de kerk.

Gebruik bij het betalen de volgende gegevens:
Rekeningnummer: NL44 RABO 0389 3198 21 t.n.v. Scratch Messiah Zeeland
Bedrag: {{ $registrationTotal|euro }}
Onder vermelding van: inschrijvingsnummer {{ $registration->id }}


Hieronder volgt een overzicht van uw inschrijving.

Inschrijvingsnummer: {{ $registration->id }}

Achternaam: {{ $registration->lastName }}
Voorletters: {{ $registration->initials }}
Stemsoort: {{ $registration->vocalRange }}
Arts / BHV / AED: {{ $registration->bhv|boolToText }}
Lunch: {{ $lunchText }}
@if ($registration->masterClass): Inclusief inschrijving voor de masterclass op vrijdag 6 maart 2026 (14-17h)@endif

@foreach ($extraFields as $description => $contents)
@if (!empty(trim((string)$contents))){{ $description }}: {{ $contents }}
@endif
@endforeach

@if (!empty($ticketTypes))
Kaartsoorten/extra’s:
@foreach ($ticketTypes as $ticketType)
@if ($registrationTicketTypes[$ticketType->id] > 0)   {{ $ticketType->name }}: {{ $registrationTicketTypes[$ticketType->id] }} à {{ \Cyndaron\View\Template\ViewHelpers::formatEuro((float)$ticketType->price) }}
@endif
@endforeach
@endif
Totaalbedrag: {{ \Cyndaron\View\Template\ViewHelpers::formatEuro($registrationTotal) }}
