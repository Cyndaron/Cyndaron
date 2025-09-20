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
        @php /** @var \Cyndaron\RCTspace\RideExchange\RideExchangeTrack[] $trackDesigns */ @endphp
        @foreach($trackDesigns as $trackDesign)
            <tr>
                <td class="align-right">{{ $loop->iteration }}</td>
                <td>{{ $trackDesign->name }}</td>
                <td>{{ $trackDesign->category }}</td>
                <td>{{ $trackDesign->vehicle }}</td>
                <td>{{ $trackDesign->submitter }}</td>
                <td>{{ $trackDesign->submitDate }}</td>
                <td>
                    @if ($trackDesign->presentOnDisk())
                        <a href="/ride-exchange/download/{{ $trackDesign->id }}" target='_blank'>Download</a>
                        @if ($trackDesign->fallbackMessage)
                            {{ $trackDesign->fallbackMessage }}
                        @endif
                    @else
                        Missing file on disk: {{ basename($trackDesign->zipLocation) }}
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
