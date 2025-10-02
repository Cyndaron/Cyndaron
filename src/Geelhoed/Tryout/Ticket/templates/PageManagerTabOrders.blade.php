<table class="table table-striped table-bordered pm-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Naam</th>
            <th>Bestelling</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach ($orderRecords as $orderRecord)
            @php
            /** @var \Cyndaron\Geelhoed\Tryout\Ticket\Order $order */
            $order = $orderRecord['order'];
            /** @var \Cyndaron\Geelhoed\Tryout\Ticket\OrderTicketType[] $ticketTypes */
            $ticketTypes = $orderRecord['ticketTypes'];
            @endphp
            <tr>
                <td>{{ $order->id }}</td>
                <td>{{ $order->name }}</td>
                <td>
                    <ul>
                        @foreach ($ticketTypes as $ticketType)
                            <li>{{ $ticketType->amount }}× {{ $ticketType->type->name }}</li>
                        @endforeach
                    </ul>
                </td>
                <td></td>
            </tr>
        @endforeach
    </tbody>
</table>
