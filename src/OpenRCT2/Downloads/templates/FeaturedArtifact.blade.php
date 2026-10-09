@php
 /** @var \Cyndaron\OpenRCT2\Downloads\Artifact $artifact */
$friendlyName = $artifact->operatingSystem->getFriendlyName();
@endphp
<div class="card">
    <div class="card-body">
        @if ($artifact->operatingSystem != \Cyndaron\OpenRCT2\Downloads\Classification\OperatingSystem::OTHER)
            <a href="{{ $artifact->downloadLink }}" class="card-link rct-ride-image-link">
                <img src="{{ $artifact->operatingSystem->getImage() }}" alt="Image for {{ $friendlyName }}, leading to the download" class="rct-ride-image">
            </a>
        @endif
        <h5 class="card-title">{{ $friendlyName }}</h5>
        <h6 class="card-subtitle mb-2 text-muted">
            {{ $artifact->architecture->getFriendlyName() }},
            {{ $t->get($artifact->type->getFriendlyName()) }}
        </h6>
        <p class="card-text">{{ $t->get('Size:') }} {{ \Cyndaron\Util\Util::formatSize($artifact->size) }}</p>
        <a href="{{ $artifact->downloadLink }}" class="card-link">{{ $t->get('Download') }}</a>
    </div>
</div>
