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
<p>
    This build was published at {{ $build->publishedAt->format('Y-m-d, H:i') }} UTC.
</p>
@if ($build->buildType->hasChangelog())
    <p><a href="/download/changelog/{{ $build->buildType->value }}/{{ $build->version }}">View the changelog for this build</a></p>
@endif
@if ($build->signedWithSignPath)
    <div class="alert alert-info">
        Free code signing provided by <a href="https://about.signpath.io/">SignPath.io</a>, certificate by <a href="https://signpath.org/">SignPath Foundation</a>.<br><br>

        For more details, see our <a href="/code-signing-policy">Code signing policy</a>.
    </div>
@endif
@if ($hasOtherArtifacts)
<div>
    <h3>Other artifacts:</h3>
    <ul>
        @foreach ($build->artifacts as $artifact)
            @if (!$artifact->inDefaultSelection)
                <li>
                    <a href="{{ $artifact->downloadLink }}">
                        {{ $artifact->version }} {{ $artifact->operatingSystem->getFriendlyName() }} {{ $artifact->operatingSystemVersion }} {{ $artifact->architecture->getFriendlyName() }} {{ $artifact->type->getFriendlyName() }} ({{ \Cyndaron\Util\Util::formatSize($artifact->size) }})
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
</div>
@endif
