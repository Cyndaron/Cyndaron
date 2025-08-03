@extends ('Index')

@section ('contents')
    <table class="table table-striped table-bordered">
        <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Category</th>
            <th>Vehicle</th>
            <th>Submitter</th>
            <th>Submission date</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach($rows as $row)
            @php
                $fileId = $row['Id'];
                $name = html_entity_decode($row['Ride_name'], encoding: 'UTF-8');
                $vehicle = $row['Vehicle_type'];
                if ($vehicle == 'NONE')
                    $vehicle = '';
                $categoryId = $vehicleMap[$vehicle] ?? -1;
                if ($categoryId == -1)
                    $categoryId = $rideMap[$row['Ride_type']] ?? -1;

                $category = ($categoryMap[$categoryId] ?? '');
                $submitter = html_entity_decode($row['uploader_name'] ?? '?', encoding: 'UTF-8');
                $submitDate = date('Y-m-d H:i:s', $row['Upload_date']);
            @endphp
            <tr>
                <td class="align-right">{{ $loop->iteration }}</td>
                <td>{{ $name }}</td>
                <td>{{ $category }}</td>
                <td>{{ $vehicle }}</td>
                <td>{{ $submitter }}</td>
                <td>{{ $submitDate }}</td>
                <td><a href="/ride-exchange/download/{{ $fileId }}" target='_blank'>Download</a>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
