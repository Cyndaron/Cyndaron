@php /** @var \Cyndaron\Ticketsale\Concert $concert */ @endphp
@php /** @var \Cyndaron\Ticketsale\Order\Order $order */ @endphp
<h1>{{ $concert->name }}</h1>

Prijs: {{ $order->calculatePrice()|euro }}<br>
<br>
Bestellingsnummer: {{ $order->id }}<br>
Naam: {{ $order->initials }} {{ $order->lastName }}<br>
<br>
<img src="data:image/png;base64,{!! $rawImage !!}" alt="{{ $order->secretCode }}">
