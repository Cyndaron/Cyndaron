@extends('Index')

@section('contents')
    <h2>
        {{ $newestBuild->version }} (latest)
    </h2>
    @php /** @var \Cyndaron\OpenRCT2\Downloads\Build $newestBuild */ @endphp
    @include('OpenRCT2/Downloads/BuildListing', ['build' => $newestBuild])

    @php /** @var \Cyndaron\OpenRCT2\Downloads\Build[] $olderBuilds */ @endphp
    <h2>Older builds</h2>
    <div id="older-builds">
        @foreach ($olderBuilds as $build)

            <div class="card">
                <div class="card-header" id="heading-b{{ $loop->index }}">
                    <h5 class="mb-0">
                        <button class="btn btn-link" data-toggle="collapse" data-target="#collapse-b{{ $loop->index }}" aria-expanded="true" aria-controls="collapse-b{{ $loop->index }}">
                            {{ $build->version }}
                        </button>
                    </h5>
                </div>

                <div id="collapse-b{{ $loop->index }}" class="collapse" aria-labelledby="heading-b{{ $loop->index }}" data-parent="#older-builds">
                    <div class="card-body">
                        @include('OpenRCT2/Downloads/BuildListing', ['build' => $build])
                    </div>
                </div>
            </div>
        @endforeach
    </div>

@endsection
