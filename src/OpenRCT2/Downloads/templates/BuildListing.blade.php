@php /** @var \Cyndaron\OpenRCT2\Downloads\Build $build */ @endphp
<div class="row">
    @foreach ($build->artifacts as $artifact)
        @if ($artifact->inDefaultSelection)
            <div class="col-sm-6">
                @include ('OpenRCT2/Downloads/FeaturedArtifact', ['artifact' => $artifact])
            </div>
        @endif
    @endforeach
</div>
<div>
    <h3>Other artifacts:</h3>
    <ul>
        @foreach ($build->artifacts as $artifact)
            @if (!$artifact->inDefaultSelection)
                <li>
                    <a href="{{ $artifact->downloadLink }}">
                        {{ $artifact->version }} {{ $artifact->operatingSystem->getFriendlyName() }} {{ $artifact->architecture->getFriendlyName() }} {{ $artifact->type->getFriendlyName()  }} {{ $artifact->size }}
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
</div>
