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
                <td>
                    {{ $order->getHour()->getLocation()->getName() }}<br>
                    {{ \Cyndaron\View\Template\ViewHelpers::getDutchWeekday($order->getHour()->day) }} {{ $order->getHour()->getRange() }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
