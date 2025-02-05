@php /** @var \Cyndaron\Geelhoed\Webshop\Model\OrderItem[] $orderItems */ @endphp
@foreach ($orderItems as $orderItem)
    <li class="list-group-item">
        {{ $orderItem->quantity }}× {{ $orderItem->getLineDescription() }}
        <br>
        @if ($orderItem->currency === \Cyndaron\Geelhoed\Webshop\Model\Currency::LOTTERY_TICKET)
            ({{ $orderItem->getLineAmount() }} loten)
        @elseif($orderItem->currency === \Cyndaron\Geelhoed\Webshop\Model\Currency::EURO)
            ({{ $orderItem->getLineAmount()|euro }})
        @endif
    </li>
@endforeach
