@extends('Index')

@section('contents')
    @php /** @var \Cyndaron\Geelhoed\Webshop\Model\Product[] $products */ @endphp
    @php /** @var \Cyndaron\Geelhoed\Webshop\Model\OrderItem[] $orderItems */ @endphp
    @php $numOrderItems = count($orderItems) @endphp

    @if ($numOrderItems > 0 && !$needsAddingGymtas)
        @if ($numOrderItems === 1)
            <p>Er zit momenteel 1 artikel in je winkelmand.</p>
        @else
            <p>Er zitten momenteel {{ $numOrderItems }} artikelen in je winkelmand.</p>
        @endif
        <div class="card" style="width: 18rem;">
            <ul class="list-group list-group-flush">

                @foreach ($orderItems as $orderItem)
                    <li class="list-group-item cart-item">
                        <div>
                            {{ $orderItem->getLineDescription() }}
                            <br>
                            @if ($orderItem->quantity !== 1)
                                Aantal: {{ $orderItem->quantity }}
                                <br>
                            @endif
                            @if ($orderItem->currency === \Cyndaron\Geelhoed\Webshop\Model\Currency::LOTTERY_TICKET)
                                ({{ $orderItem->getLineAmount() }} loten)
                            @elseif($orderItem->currency === \Cyndaron\Geelhoed\Webshop\Model\Currency::EURO)
                                ({{ $orderItem->getLineAmount()|euro }})
                            @endif
                        </div>
                        <button
                            class="btn btn-sm btn-outline-cyndaron remove-from-cart"
                            data-order-item-id="{{ $orderItem->id }}"
                            data-hash="{{ $hash }}"
                        >
                            <span class="glyphicon glyphicon-trash"></span>
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>

        @if ($numSoldTickets > 0)
            <p>
                Je hebt <b>{{ $numSoldTickets }}</b> loten verkocht.
                Je hebt er <b>{{ $ticketSubtotal }}</b> gebruikt voor de artikelen in je winkelmandje.
            </p>
            @if (($numSoldTickets - $ticketSubtotal) > 0)
                <p>
                    Als je meer wilt bestellen, kun je daar je resterende <b>{{ $numSoldTickets - $ticketSubtotal }}</b> loten voor gebruiken,
                    of ze gewoon afrekenen in euro’s. Je kunt je resterende loten ook doneren aan Sportschool Geelhoed.
                </p>
                <form action="/webwinkel/doneer-loten/{{ $hash }}" method="post">
                    <input type="submit" class="btn btn-sm btn-outline-cyndaron" value="Resterende loten doneren">
                </form>
            @endif
        @endif
        <br>Subtotaal euro: {{ $euroSubtotal|euro }}

        <br>
        <a class="btn btn-primary" href="/webwinkel/overzicht/{{ $hash }}">Bestellen</a>
    @endif

    @if ($needsAddingGymtas)
        <p>
            Omdat je 10 loten of meer hebt verkocht, heb je recht op een gratis gymtasje:

            @include('Geelhoed/Webshop/Product', ['product' => $gymtas, 'validCurrencies' => [\Cyndaron\Geelhoed\Webshop\Model\Currency::LOTTERY_TICKET], 'hash' => $hash])
        </p>

        Nadat je dit gymtasje in je winkelwagen hebt gestopt, kun je andere artikelen bestellen.
    @else
        <h2>Producten</h2>
        <div class="product-list">
            @foreach ($products as $product)
                @php
                    $validCurrencies = [\Cyndaron\Geelhoed\Webshop\Model\Currency::EURO];
                    if ($product->getGcaTicketPrice() <= ($numSoldTickets - $ticketSubtotal)):
                        $validCurrencies[] = \Cyndaron\Geelhoed\Webshop\Model\Currency::LOTTERY_TICKET;
                    endif;
                @endphp
                @include('Geelhoed/Webshop/Product', ['product' => $product, 'validCurrencies' => $validCurrencies, 'hash' => $hash])

            @endforeach
        </div>

    @endif
@endsection
