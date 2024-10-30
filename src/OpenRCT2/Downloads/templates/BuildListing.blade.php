@php /** @var \Cyndaron\OpenRCT2\Downloads\Build $build */ @endphp
@php $hasOtherArtifacts = false; @endphp
<div class="row featured-downloads">
    @foreach ($build->artifacts as $artifact)
        @if ($artifact->inDefaultSelection)
            <div class="col-sm-6">
                @include ('OpenRCT2/Downloads/FeaturedArtifact', ['artifact' => $artifact])
            </div>
        @else
            @php $hasOtherArtifacts = true @endphp
        @endif
    @endforeach
</div>
@if ($hasOtherArtifacts)
<div>
    <h3>Other artifacts:</h3>
    <ul>
        @foreach ($build->artifacts as $artifact)
            @if (!$artifact->inDefaultSelection)
                <li>
                    <a href="{{ $artifact->downloadLink }}">
                        {{ $artifact->version }} {{ $artifact->operatingSystem->getFriendlyName() }} {{ $artifact->architecture->getFriendlyName() }} {{ $artifact->type->getFriendlyName() }} ({{ \Cyndaron\Util\Util::formatSize($artifact->size) }})
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
</div>
@endif
