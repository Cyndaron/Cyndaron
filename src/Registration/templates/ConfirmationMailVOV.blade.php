Hartelijk dank voor uw inschrijving voor {{ $event->name }}.
Zodra de inschrijving is gesloten krijgt u van ons een nader bericht over het vervolg.

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