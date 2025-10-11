<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Naam</th>
            <th>Bestelling</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($orderRecords as $orderRecord)
            @php
            /** @var \Cyndaron\Geelhoed\Tryout\Ticket\Order $order */
            $order = $orderRecord['order'];
            /** @var \Cyndaron\Geelhoed\Tryout\Ticket\OrderTotal $orderTotal */
            $orderTotal = $orderRecord['orderTotal'];
            @endphp
            <tr>
                <td>{{ $order->id }}</td>
                <td>{{ $order->name }}</td>
                <td>
                    <ul>
                        {!! $orderTotal->asListItems() !!}
                    </ul>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
