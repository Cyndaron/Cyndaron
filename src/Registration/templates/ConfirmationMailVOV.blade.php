Hartelijk dank voor uw inschrijving bij de Vlissingse Oratorium Vereniging. Na betaling is uw inschrijving definitief.

Gebruik bij het betalen de volgende gegevens:
Rekeningnummer: NL06 INGB 0000 5459 25 t.n.v. Vlissingse Oratorium Vereniging
Bedrag: {{ $orderTotal|euro }}
Onder vermelding van: inschrijvingsnummer {{ $order->id }}


Hieronder volgt een overzicht van uw inschrijving.

Inschrijvingsnummer: {{ $order->id }}

Achternaam: {{ $order->lastName }}
Voorletters: {{ $order->initials }}
Stemsoort: {{ $order->vocalRange }}
Lid van: {{ $order->currentChoir ?: 'Geen koor / ander koor' }}
Voorkeur koor I/II: {{ $order->choirPreference }}

@foreach ($extraFields as $description => $contents)
@if (!empty(trim((string)$contents))){{ $description }}: {{ $contents }}
@endif
@endforeach

Totaalbedrag: {{ \Cyndaron\Util::formatEuro($orderTotal) }}