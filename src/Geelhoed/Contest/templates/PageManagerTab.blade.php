<table class="table table-striped table-bordered pm-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Naam</th>
            <th>Datum</th>
            <th>Acties</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($contests as $contest)
        <tr>
            <td>{{ $contest->id }}</td>
            <td>{{ $contest->name }}</td>
            <td>{{ $contest->date }}</td>
            <td>
                <a href="/contest/subscriptionList/{{ $contest->id }}" class="btn btn-outline-cyndaron"><span class="glyphicon glyphicon-list"></span></a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>