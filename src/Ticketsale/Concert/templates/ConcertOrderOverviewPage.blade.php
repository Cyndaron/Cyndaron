@extends('Index')

@section('contents')
    @component('View/Widget/Toolbar')
        @slot('left')
            <a class="btn btn-outline-cyndaron" href="/pagemanager/concert">&laquo; Terug naar overzicht concerten</a>
        @endslot
        @slot('right')
            @include('View/Widget/Button', ['kind' => 'th', 'link' => '/concert/orderListExcel/' . $concert->id, 'text' => 'Excel-export'])
        @endslot
    @endcomponent

    @php /** @var \Cyndaron\Ticketsale\Concert\Concert $concert */ @endphp
    @php /** @var \Cyndaron\Ticketsale\Order\OrderHelper $orderHelper */ @endphp
    <table class="overzichtBestellingen table table-striped">
        <thead>
            <tr class="rotate">
                <th class="rotate">
                    <div><span>Bestelnummer</span></div>
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
                @if (!$concert->digitalDelivery)
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
                @endif
                @if ($concert->hasReservedSeats)
                    <th class="rotate">
                        <div><span>Geres. plaats?</span></div>
                    </th>
                @endif
                <th class="rotate">
                    <div><span>Is betaald?</span></div>
                </th>
                <th class="rotate">
                    <div><span>Is donateur?</span></div>
                </th>
                <th class="column-actions"></th>
            </tr>
        </thead>

        <tbody>
            @php /** @var \Cyndaron\Ticketsale\Order\Order[] $orders */ @endphp
            @foreach ($orders as $order)
                @php
                    $orderId = $order->id;
                    $isDonor = $order->getAdditionalData()['donor'] ?? false;
                @endphp

                <tr>
                    <td>{{ $orderId }}</td>
                    <td>{{ $order->lastName }}</td>
                    <td>{{ $order->initials }}</td>
                    <td><a href="mailto:{{ $order->email }}">{{ $order->email }}</a></td>
                    <td>
                        {{ $order->street }}<br />
                        {{ $order->postcode }} {{ $order->city }}
                    </td>
                    <td>{{ $order->comments }}</td>

                    @foreach ($ticketTypes as $ticketType)
                        <td>
                        @if (\array_key_exists($order->id, $ticketTypesByOrder) && \array_key_exists($ticketType['id'], $ticketTypesByOrder[$order->id]))
                            <b>{{ $ticketTypesByOrder[$order->id][$ticketType['id']] }}</b>
                            @php $isDonor = $isDonor || str_contains(strtolower($ticketType['name']), 'donateur'); @endphp
                        @else
                            &nbsp;
                        @endif
                        </td>
                    @endforeach

                    <td>{{ $orderHelper->calculateOrderTotal($order)|euro }}</td>

                    @if (!$concert->digitalDelivery)
                        @if (!$concert->forcedDelivery)
                            <td>{{ $order->delivery|boolToDingbat }}</td>
                        @else
                            <td>
                            @if ($order->deliveryByMember)
                                {{ $order->deliveryMemberName }}
                            @else
                                ⨯
                            @endif
                            </td>
                        @endif

                        <td>
                            @if ($order->delivery || $concert->forcedDelivery)
                                {{ $order->isDelivered|boolToDingbat }}
                            @else
                                &nbsp;
                            @endif
                        </td>
                    @endif
                    @if ($concert->hasReservedSeats)
                        <td>
                            {{ $order->hasReservedSeats|boolToDingbat }}
                        </td>
                    @endif
                    <td>
                        {{ $order->isPaid|boolToDingbat }}
                    </td>
                    <td>
                        {{ $isDonor|boolToDingbat }}
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            @if (!$order->isPaid)
                                <button data-order-id="{{ $orderId }}" data-csrf-token-set-is-paid="{{ $tokenHandler->get('concert-order', 'setIsPaid') }}" title="Markeren als betaald" class="com-order-set-paid btn btn-sm btn-success">@include('View/Widget/Icon', ['type' => 'money'])</button>
                            @endif

                            @if (($concert->forcedDelivery || $order->delivery) && !$order->isDelivered)
                                <button data-order-id="{{ $orderId }}" data-csrf-token-set-is-sent="{{ $tokenHandler->get('concert-order', 'setIsSent') }}" title="Markeren als verstuurd" class="com-order-set-sent btn btn-sm btn-success">@include('View/Widget/Icon', ['type' => 'envelope'])</button>
                            @endif

                            <button data-order-id="{{ $orderId }}" data-csrf-token-delete="{{ $tokenHandler->get('concert-order', 'delete') }}" title="Bestelling verwijderen" class="com-order-delete btn btn-sm btn-danger">@include('View/Widget/Icon', ['type' => 'delete'])</button>
                            <a class="btn btn-sm btn-outline-cyndaron" href="/concert-order/getTickets/{{ $order->id }}/{{ $order->secretCode }}" title="Tickets opvragen" target="_blank">
                                @include('View/Widget/Icon', ['type' => 'tag'])
                            </a>
                         </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <h2>Totalen</h2>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Type</th>
                <th>Aantal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($ticketTypes as $ticketType)
            <tr>
                <td>{{ $ticketType['name'] }}</td>
                <td>{{ $totals[$ticketType['id']] ?? 0 }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection
