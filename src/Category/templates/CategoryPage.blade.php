@php /** @var \Cyndaron\Url\UrlService $urlService */ @endphp
@php /** @var \Cyndaron\Category\ModelWithCategory[] $pages */ @endphp
@extends ('Index')

@section ('titleControls')
    @if ($model)
        <a href="/editor/category/{{ $model->id }}" class="btn btn-outline-cyndaron" title="Deze categorie bewerken" role="button"><span class="glyphicon glyphicon-pencil"></span></a>
    @endif
@endsection

@section ('contents')
<div id="category-container">
    <div id="category-main">
        {!! $parsedDescription ?? '' !!}

    @if ($viewMode === \Cyndaron\Category\ViewMode::Regular)
        <div class="category-listview">
        @foreach ($pages as $page)
            @php $friendlyUrl = $urlService->getFriendlyUrlForModel($page); @endphp
            <div>
                <h3><a href="{{ $friendlyUrl }}">{{ $page->name }}</a></h3>
                {{ $page->getBlurb() }} <a href="{{ $friendlyUrl }}" @if ($page->shouldOpenInNewTab()) target="_blank" @endif><br /><i>{{ $t->get('Meer lezen…') }}</i></a>
            </div>
        @endforeach
        </div>
    @elseif ($viewMode === \Cyndaron\Category\ViewMode::Titles)
    <ul class="zonderbullets">
        @foreach ($pages as $page)
            @php $friendlyUrl = $urlService->getFriendlyUrlForModel($page); @endphp
            <li><h3><a href="{{ $friendlyUrl }}" @if ($page->shouldOpenInNewTab()) target="_blank" @endif>{{ $page->name }}</a></h3></li>
        @endforeach
    </ul>
    @elseif ($viewMode === \Cyndaron\Category\ViewMode::Blog)
        <div class="category-blockview">
            @foreach ($pages as $page)
                @php $friendlyUrl = $urlService->getFriendlyUrlForModel($page); @endphp
                <div class="category-block">
                    <a class="category-block-image" href="{{ $friendlyUrl }}" @if ($page->shouldOpenInNewTab()) target="_blank" @endif>
                        <img src="{{ $page->getPreviewImage() }}" alt=""/>
                    </a>
                    <h3><a href="{{ $friendlyUrl }}">{{ $page->name }}</a></h3>
                    <a class="category-block-link" href="{{ $friendlyUrl }}" @if ($page->shouldOpenInNewTab()) target="_blank" @endif>{{ $page->blurb }}</a>
                </div>
            @endforeach
        </div>
    @elseif ($viewMode === \Cyndaron\Category\ViewMode::Portfolio)
        @foreach ($portfolioContent as $albumname => $albumcontent)
            <h2>{{ $albumname }}</h2>
            @foreach ($albumcontent as $page)
                @php $friendlyUrl = $urlService->getFriendlyUrlForModel($page); @endphp
                <div class="category-block">
                    <a class="category-block-image" href="{{ $friendlyUrl }}" @if ($page->shouldOpenInNewTab()) target="_blank" @endif>
                        <img src="{{ $page->getPreviewImage() }}" alt=""/>
                    </a>
                    <h3><a href="{{ $friendlyUrl }}">{{ $page->name }}</a></h3>
                    <a class="category-block-link" href="{{ $friendlyUrl }}" @if ($page->shouldOpenInNewTab()) target="_blank" @endif>{{ $page->getBlurb() }}</a>
                </div>
            @endforeach
        @endforeach
    @elseif ($viewMode === \Cyndaron\Category\ViewMode::Horizontal)
        <div class="category-horizontalview">
            @foreach ($pages as $page)
                @php $friendlyUrl = $urlService->getFriendlyUrlForModel($page); @endphp
                <div class="category-block">
                    <div class="category-block-left">
                        <h3><a href="{{ $friendlyUrl }}" @if ($page->shouldOpenInNewTab()) target="_blank" @endif>{{ $page->name }}</a></h3>
                        <a href="{{ $friendlyUrl }}" @if ($page->shouldOpenInNewTab()) target="_blank" @endif>{{ $page->getBlurb() }}</a>
                    </div>
                    <div class="category-block-right">
                        <a class="category-block-image" href="{{ $friendlyUrl }}" @if ($page->shouldOpenInNewTab()) target="_blank" @endif>
                            <img src="{{ $page->getPreviewImage() }}" alt=""/>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {!! $contents !!}
    </div>
    @if ($tags)
    <div id="category-tags">
        @foreach ($tags as $tag)
            <a role="button" href="/category/tag/{{ urlencode(strtolower($tag)) }}">{{ $tag }}</a>
        @endforeach
    </div>
    @endif
</div>
@endsection
