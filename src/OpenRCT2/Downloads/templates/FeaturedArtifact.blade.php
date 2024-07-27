@php /** @var \Cyndaron\OpenRCT2\Downloads\Artifact $artifact */ @endphp
<div class="card">
    <div class="card-body">
        <h5 class="card-title">{{ $artifact->operatingSystem->getFriendlyName() }}</h5>
        <h6 class="card-subtitle mb-2 text-muted">
            {{ $artifact->architecture->getFriendlyName() }},
            {{ $artifact->type->getFriendlyName() }}
        </h6>
        <p class="card-text">Size: {{ \Cyndaron\Util\Util::formatSize($artifact->size) }}</p>
        <a href="{{ $artifact->downloadLink }}" class="card-link">Download</a>
    </div>
</div>
