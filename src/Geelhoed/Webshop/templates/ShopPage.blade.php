@extends('Index')

@section('contents')
    @php /** @var \Cyndaron\Geelhoed\Webshop\Model\Product[] $products */ @endphp
    @php /** @var \Cyndaron\Geelhoed\Webshop\Model\OrderItem[] $orderItems */ @endphp
    @php $numOrderItems = count($orderItems) @endphp
    <p>Je hebt {{ $numSoldTickets }} loten verkocht.</p>

    @if ($numOrderItems === 1)
        <p>Er zit momenteel 1 artikel in je winkelmand.</p>
    @else
        <p>Er zitten momenteel {{ $numOrderItems }} artikelen in je winkelmand.</p>
    @endif
    @if ($numOrderItems > 0)
        <div class="card" style="width: 18rem;">
            <ul class="list-group list-group-flush">

                @foreach ($orderItems as $orderItem)
                    <li class="list-group-item cart-item">
                        <div>
                            {{ $orderItem->getLineDescription() }}
                            <br>
                            @if ($orderItem->currency === \Cyndaron\Geelhoed\Webshop\Model\Currency::LOTTERY_TICKET)
                                ({{ $orderItem->price }} loten)
                            @elseif($orderItem->currency === \Cyndaron\Geelhoed\Webshop\Model\Currency::EURO)
                                ({{ $orderItem->price|euro }})
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

        Subtotaal loten: {{ $ticketSubtotal }}
        <br>Subtotaal euro: {{ $euroSubtotal|euro }}

        <br>
        <a class="btn btn-primary" href="/webwinkel/overzicht/{{ $hash }}">Bestellen</a>
    @endif

    @if ($numSoldTickets >= 10 && !$hasGymtasInCart)
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
