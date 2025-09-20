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
        @php /** @var \Cyndaron\RCTspace\Downloads\Download[] $downloads */ @endphp
        @foreach($downloads as $download)
            <tr>
                <td class="align-right">{{ $loop->iteration }}</td>
                <td><a href='https://forums.rctspace.com/index.php?app=downloads&showfile={{ $download->id }}' target='_blank'>{{ $download->name }}</a></td>
                <td>{{ $download->category }}</td>
                <td>{{ $download->submitter }}</td>
                <td>{{ $download->submitDate }}</td>
                <td>
                    @if ($download->presentOnDisk())
                        <a href="/downloads/download/{{ $download->id }}" target='_blank'>Download</a>
                    @else
                        Missing file on disk: {{ basename($download->getPath()) }}
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
