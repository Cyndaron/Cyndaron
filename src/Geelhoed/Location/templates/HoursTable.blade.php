<table class="table table-striped table-bordered location-overview">
    <thead>
        <tr>
            <th>Leeftijd</th><th>Lestijd</th><th>Training</th><th>Afdeling</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($hours as $hour)
            <tr>
                <td>{{ $hour->description }}</td>
                <td>{{ $hour->from|hm }}&nbsp;&ndash;&nbsp;{{ $hour->until|hm }}</td>
                <td>{{ $hour->getSportName() }}</td>
                <td>{{ $hour->getDepartment()->name }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
