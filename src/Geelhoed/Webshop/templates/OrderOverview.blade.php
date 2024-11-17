@foreach ($orderItems as $orderItem)
    <li class="list-group-item">
        {{ $orderItem->getLineDescription() }}
        <br>
        @if ($orderItem->currency === \Cyndaron\Geelhoed\Webshop\Model\Currency::LOTTERY_TICKET)
            ({{ $orderItem->price }} loten)
        @elseif($orderItem->currency === \Cyndaron\Geelhoed\Webshop\Model\Currency::EURO)
            ({{ $orderItem->price|euro }})
        @endif
    </li>
@endforeach
