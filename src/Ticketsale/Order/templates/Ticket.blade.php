@php /** @var \Cyndaron\Ticketsale\Concert $concert */ @endphp
@php /** @var \Cyndaron\Ticketsale\Order\Order $order */ @endphp
@php /** @var \Cyndaron\Ticketsale\TicketType $ticketType */ @endphp
@php /** @var \Cyndaron\Ticketsale\Order\OrderTicketTypes $orderTicketType */ @endphp

<div style="width: 100%; position: relative;">

    <div style="float:right; position: absolute; top: 0; right: 0; width: 200px; height: 0; overflow-y: visible; text-align: right;">
        <img src="data:image/png;base64,{!! $rawLogo !!}" alt="" style="display: block; max-width: 200px;">
    </div>

    <h1>{{ $concert->name }}</h1>
    <h2>{{ $organisation }}</h2>
    <h4>{{ $concert->date|dmyHm }}</h4>
    <h4>{{ $concert->location }}</h4>

    <h4>Kaartsoort: {{ $ticketTypeDescription }}</h4>
    Prijs: {{ $orderTicketType->getPrice()|euro }}<br>
    <br>
    Bestelnummer: {{ $order->id }}<br>
    Naam: {{ $order->initials }} {{ $order->lastName }}<br><br>
    <br>
    <p>{!! $concert->ticketInfo !!}</p>
    <br>
    <img src="data:image/png;base64,{!! $rawImage !!}" alt="{{ $order->secretCode }}">
    <br>
    <h3>Bewijs van toegang</h3>

</div>


