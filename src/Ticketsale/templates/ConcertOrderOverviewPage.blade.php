@extends('Index')

@section('contents')
    @component('View/Widget/Toolbar')
        @slot('left')
            <a class="btn btn-outline-cyndaron" href="/pagemanager/concert">&laquo; Terug naar overzicht concerten</a>
        @endslot
        @slot('right')
            <a class="btn btn-outline-cyndaron" href="/concert/viewReservedSeats/' . $concertId . '">Overzicht gereserveerde plaatsen</a>
        @endslot
    @endcomponent

    <table class="overzichtBestellingen table table-striped">
        <thead>
            <tr class="rotate">
                <th class="rotate">
                    <div><span>Bestellingsnummer</span></div>
                </th>
                <th class="rotate">
                    <div><span>Achternaam</span></div>
                </th>
                <th class="rotate">
                    <div><span>Voorletters</span></div>
                </th>
                <th class="rotate">
                    <div><span>E-mailadres</span></div>
                </th>
                <th class="rotate">
                    <div><span>Adres</span></div>
                </th>
                <th class="rotate">
                    <div><span>Opmerkingen</span></div>
                </th>

                @foreach ($ticketTypes as $ticketType)
                    <th class="rotate"><div><span>{{ $ticketType['name'] }}</span></div></th>
                @endforeach

                <th class="rotate">
                    <div><span>Totaal</span></div>
                </th>
                @if (!$concert->forcedDelivery)
                    <th class="rotate">
                        <div><span>Thuisbezorgen</span></div>
                    </th>
                @else
                    <th class="rotate">
                        <div><span>Meegeven aan koorlid</span></div>
                    </th>
                @endif
                <th class="rotate">
                    <div><span>Al verstuurd?</span></div>
                </th>
                <th class="rotate">
                    <div><span>Geres. plaats?</span></div>
                </th>
                <th class="rotate">
                    <div><span>Is betaald?</span></div>
                </th>
                <th class="column-actions"></th>
            </tr>
        </thead>

        <tbody>
            @foreach ($orders as $order)
                @php
                    $orderId = $order['bestellingsnr'];
                    $totaalbedrag = 0.0;
                    $verzendkosten = $order['delivery'] * $concert->deliveryCost;
                    $toeslag_gereserveerde_plaats = $order['hasReservedSeats'] * $concert->reservedSeatCharge;
                @endphp

                <tr>
                    <td>{{ $orderId }}</td>
                    <td>{{ $order['lastName'] }}</td>
                    <td>{{ $order['initials'] }}</td>
                    <td>{{ $order['email'] }}</td>
                    <td>
                        {{ $order['street'] }}<br />
                        {{ $order['postcode'] }} {{ $order['city'] }}
                    </td>
                    <td>{{ $order['comments'] }}</td>

                    @foreach ($ticketTypes as $ticketTypeId)
                        <td>
                        @if (array_key_exists($ticketTypeId['id'], $ticketTypesByOrder[$order['bestellingsnr']]))
                            <b>{{ $ticketTypesByOrder[$order['bestellingsnr']][$ticketTypeId['id']] }}</b>
                            @php
                                $totaalbedrag += $ticketTypesByOrder[$orderId][$ticketTypeId['id']] * $ticketTypeId['price'];
                                $totaalbedrag += $ticketTypesByOrder[$orderId][$ticketTypeId['id']] * $verzendkosten;
                                $totaalbedrag += $ticketTypesByOrder[$orderId][$ticketTypeId['id']] * $toeslag_gereserveerde_plaats;
                            @endphp
                        @else
                            &nbsp;
                        @endif
                        </td>
                    @endforeach

                    <td>{{ $totaalbedrag|euro }}</td>

                    @if (!$concert->forcedDelivery)
                        <td>{{ $order['delivery']|boolToText }}</td>
                    @else
                        <td>
                        @if ($order['deliveryByMember'])
                            {{ $order['deliveryMemberName'] }}
                        @else
                            Nee
                        @endif
                        </td>
                    @endif

                    <td>
                        @if ($order['delivery'] || $concert->forcedDelivery)
                            {{ $order['isDelivered']|boolToText }}
                        @else
                            &nbsp;
                        @endif
                    </td>
                    <td>
                        {{ $order['hasReservedSeats']|boolToText }}
                    </td>
                    <td>
                        {{ $order['isPaid']|boolToText }}
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            @if (!$order['isPaid'])
                                <button data-order-id="{{ $orderId }}" data-csrf-token-set-is-paid="{{ \Cyndaron\User\User::getCSRFToken('concert-order', 'setIsPaid') }}" title="Markeren als betaald" class="com-order-set-paid btn btn-sm btn-success"><span class="glyphicon glyphicon-eur"></span></button>
                            @endif

                            @if (($concert->forcedDelivery || $order['delivery']) && !$order['isDelivered'])
                                <button data-order-id="{{ $orderId }}" data-csrf-token-set-is-sent="{{ \Cyndaron\User\User::getCSRFToken('concert-order', 'setIsSent') }}" title="Markeren als verstuurd" class="com-order-set-sent btn btn-sm btn-success"><span class="glyphicon glyphicon-envelope"></span></button>
                            @endif

                            <button data-order-id="{{ $orderId }}" data-csrf-token-delete="{{ \Cyndaron\User\User::getCSRFToken('concert-order', 'delete') }}" title="Bestelling verwijderen" class="com-order-delete btn btn-sm btn-danger"><span class="glyphicon glyphicon-trash"></span></button>
                         </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
