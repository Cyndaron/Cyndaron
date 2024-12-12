<table id="gcam-table" class="table table-striped table-bordered pm-table">
    <thead>
    <tr>
        <th>ID</th>
        <th>Naam</th>
        <th>E-mail</th>
        <th>Tel.nummer</th>
        <th>Aantal loten</th>
        <th>Geverifieerd</th>
        <th>E-mail gestuurd</th>
        <th>Acties</th>
    </tr>
    </thead>
    <tbody>
        @php /** @var \Cyndaron\Geelhoed\Clubactie\Subscriber[] $subscribers */ @endphp
        @foreach ($subscribers as $subscriber)
            <tr>
                <td>{{ $subscriber->id }}</td>
                <td>{{ $subscriber->getFullName() }}</td>
                <td>{{ $subscriber->email }}</td>
                <td>{{ $subscriber->phone }}</td>
                <td>{{ $subscriber->numSoldTickets }}</td>
                <td>{{ $subscriber->soldTicketsAreVerified|boolToDingbat }}</td>
                <td>{{ $subscriber->emailSent|boolToDingbat }}</td>
                <td>
                    <form method="post" action="/webwinkel/send-mail/{{ $subscriber->hash }}">
                        <button type="submit" class="btn btn-outline-cyndaron" title="Accountgegevens mailen">
                            <span class="glyphicon glyphicon-envelope"></span>
                        </button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
