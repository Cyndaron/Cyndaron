@extends ('Index')

@section ('contents')
    <table class="table table-striped table-bordered">
        <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Category</th>
            <th>Submitter</th>
            <th>Submission date</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach($rows as $row)
            @php
                $fileId = $row['file_id'];
                $name = $row['file_name'];
                $category = $row['cname'] ?? '';
                $submitter = $row['submitter'] ?? '?';
                $submitDate = date('Y-m-d H:i:s', $row['file_submitted']);
                if ($row['file_version'])
                    $name .= " {$row['file_version']}";
            @endphp
            <tr>
                <td class="align-right">{{ $loop->iteration }}</td>
                <td><a href='https://forums.rctspace.com/index.php?app=downloads&showfile={{ $fileId }}' target='_blank'>{{ $name }}</a></td>
                <td>{{ $category }}</td>
                <td>{{ $submitter }}</td>
                <td>{{ $submitDate }}</td>
                <td><a href="/downloads/download/{{ $fileId }}" target='_blank'>Download</a>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
