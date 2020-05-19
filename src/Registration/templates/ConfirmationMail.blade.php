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
Arts / BHV / AED: ' . Util::boolToText($this->bhv) . '
Meezingen in kleinkoor: ' . Util::boolToText($this->kleinkoor) . '
Lunch: {{ $lunchText }}


@foreach ($extraFields as $description => $contents)
@if (!empty(trim((string)$contents))){{ $description }}: {{ $contents }}
@endif
@endforeach

@if (!empty($ticketTypes))
Kaartsoorten:
@foreach ($ticketTypes as $ticketType)
@if ($registrationTicketTypes[$ticketType->id] > 0)   {{ $ticketType->name }}: {{ $registrationTicketTypes[$ticketType->id] }} à {{ Cyndaron\Util::formatEuro((float)$ticketType->price) }}
@endif
@endforeach
@endif
Totaalbedrag: {{ \Cyndaron\Util::formatEuro($registrationTotal) }}