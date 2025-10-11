@extends ('Index')

@section ('contents')
    @php
        /** @var \Cyndaron\Geelhoed\Tryout\Ticket\Order $order */
        /** @var \Cyndaron\Geelhoed\Tryout\Ticket\OrderTotal $orderTotal */
    @endphp
    Uw betaling is ontvangen. U krijgt ook nog een bevestiging per e-mail.
    <br><br>
    Bestellings-ID: {{ $order->id }}
    <br><br>
    Toon deze pagina, of de bevestigingsmail, bij de kassa ter controle.<br><br>
    Overzicht van uw bestelling:
    <ul>
        {!! $orderTotal->asListItems() !!}
    </ul>
@endsection
