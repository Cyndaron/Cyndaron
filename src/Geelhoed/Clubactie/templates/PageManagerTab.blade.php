<table id="gcam-table" class="table table-striped table-bordered pm-table">
    <thead>
    <tr>
        <th>ID</th>
        <th>Naam</th>
        <th>E-mail</th>
        <th>Aantal loten</th>
        <th>Geverifieerd</th>
    </tr>
    </thead>
    <tbody>
        @php /** @var \Cyndaron\Geelhoed\Clubactie\Subscriber[] $subscribers */ @endphp
        @foreach ($subscribers as $subscriber)
            <tr>
                <td>{{ $subscriber->id }}</td>
                <td>{{ $subscriber->getFullName() }}</td>
                <td>{{ $subscriber->email }}</td>
                <td>{{ $subscriber->numSoldTickets }}</td>
                <td>{{ $subscriber->soldTicketsAreVerified|boolToDingbat }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
