<table id="gcam-table" class="table table-striped table-bordered pm-table">
    <thead>
    <tr>
        <th>ID</th>
        <th>Naam</th>
        <th>E-mail</th>
        <th>Eurobedrag</th>
        <th>Lotenaantal</th>
        <th>Status</th>
    </tr>
    </thead>
    <tbody>
        @php /** @var \Cyndaron\Geelhoed\Webshop\Model\Order[] $orders */ @endphp
        @foreach ($orders as $order)
            <tr>
                <td>{{ $order->id }}</td>
                <td>{{ $order->getSubscriber()->getFullName() }}</td>
                <td>{{ $order->getSubscriber()->email }}</td>
                <td>{{ $order->getEuroSubtotal()|euro }}</td>
                <td>{{ $order->getTicketTotal() }}</td>
                <td>{{ $order->status->getDescription() }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
