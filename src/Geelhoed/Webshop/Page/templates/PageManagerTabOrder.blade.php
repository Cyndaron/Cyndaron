@component ('View/Widget/Toolbar')
    @slot('left')
        <a class="btn btn-outline-cyndaron" href="/webwinkel/bestellijst">Bestellijst</a>
    @endslot
@endcomponent

<table id="gcam-table" class="table table-striped table-bordered pm-table">
    <thead>
    <tr>
        <th>ID</th>
        <th>Naam</th>
        <th>E-mail</th>
        <th>Eurobedrag</th>
        <th>Lotenaantal</th>
        <th>Status</th>
        <th>Les</th>
        <th>Acties</th>
    </tr>
    </thead>
    <tbody>
        @php /** @var \Cyndaron\Geelhoed\Webshop\Model\Order[] $orders */ @endphp
        @php /** @var \Cyndaron\Geelhoed\Webshop\Model\OrderRepository $orderRepository */ @endphp
        @foreach ($orders as $order)
            <tr>
                <td>{{ $order->id }}</td>
                <td>{{ $order->subscriber->getFullName() }}</td>
                <td>{{ $order->subscriber->email }}</td>
                <td>{{ $orderRepository->getEuroSubtotal($order)|euro }}</td>
                <td>{{ $orderRepository->getTicketTotal($order) }}</td>
                <td>{{ $order->status->getDescription() }}</td>
                <td>
                    {{ $order->hour->location->getName() }}<br>
                    {{ \Cyndaron\View\Template\ViewHelpers::getDutchWeekday($order->hour->day) }} {{ $order->hour->getRange() }}
                </td>
                <td>
                    <div class="btn-group">
                        @include('View/Widget/Button',
                            ['link' => "/webwinkel/beheer-order/{$order->id}", 'description' => 'Beheren', 'kind' => 'arrow-right'])
                        @if ($order->status === \Cyndaron\Geelhoed\Webshop\Model\OrderStatus::QUOTE)
                            <a
                                href="/webwinkel/winkelen/{{ $order->subscriber->hash }}"
                                class="btn btn-outline-cyndaron"
                                title="Naar bestelpagina"
                            >
                                @include('View/Widget/Icon', ['type' => 'shopping-cart'])
                            </a>
                        @endif
                    </div>

                </td>
            </tr>
        @endforeach
    </tbody>
</table>
