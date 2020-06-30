@extends ('Index')

@section ('titleControls')
    <a href="/editor/category/{{ $model->id }}" class="btn btn-outline-cyndaron" title="Deze categorie bewerken" role="button"><span class="glyphicon glyphicon-pencil"></span></a>
@endsection

@section ('contents')
<div id="category-container">
    <div id="category-main">
        {!! $model ? $model->description : '' !!}

    @if ($viewMode === 0)
        <div class="category-listview">
        @foreach ($pages as $page)
            <div>
                <h3><a href="{{ $page->getFriendlyUrl() }}">{{ $page->name }}</a></h3>
                {{ $page->getBlurb() }} <a href="{{ $page->getFriendlyUrl() }}" @if ($page->openInNewTab) target="_blank" @endif><br /><i>Meer lezen…</i></a>
            </div>
        @endforeach
        </div>
    @elseif ($viewMode === 1)
    <ul class="zonderbullets">
        @foreach ($pages as $page)
            <li><h3><a href="{{ $page->getFriendlyUrl() }}" @if ($page->openInNewTab) target="_blank" @endif>{{ $page->name }}</a></h3></li>
        @endforeach
    </ul>
    @elseif ($viewMode === 2)
        <div class="category-blockview">
            @foreach ($pages as $page)
                <div class="category-block">
                    <a class="category-block-image" href="{{ $page->getFriendlyUrl() }}" @if ($page->openInNewTab) target="_blank" @endif>
                        <img src="{{ $page->getPreviewImage() }}" alt=""/>
                    </a>
                    <h3><a href="{{ $page->getFriendlyUrl() }}">{{ $page->name }}</a></h3>
                    <a class="category-block-link" href="{{ $page->getFriendlyUrl() }}" @if ($page->openInNewTab) target="_blank" @endif>{{ $page->blurb }}</a>
                </div>
            @endforeach
        </div>
    @elseif ($viewMode === 3)
        @foreach ($portfolioContent as $albumname => $albumcontent)
            <h2>{{ $albumname }}</h2>
            @foreach ($albumcontent as $page)
                <div class="category-block">
                    <a class="category-block-image" href="{{ $page->getFriendlyUrl() }}" @if ($page->openInNewTab) target="_blank" @endif>
                        <img src="{{ $page->getPreviewImage() }}" alt=""/>
                    </a>
                    <h3><a href="{{ $page->getFriendlyUrl() }}">{{ $page->name }}</a></h3>
                    <a class="category-block-link" href="{{ $page->getFriendlyUrl() }}" @if ($page->openInNewTab) target="_blank" @endif>{{ $page->getBlurb() }}</a>
                </div>
            @endforeach
        @endforeach
    @elseif ($viewMode === 4)
        <div class="category-horizontalview">
            @foreach ($pages as $page)
                <div class="category-block">
                    <div class="category-block-left">
                        <h3><a href="{{ $page->getFriendlyUrl() }}" @if ($page->openInNewTab) target="_blank" @endif>{{ $page->name }}</a></h3>
                        <a href="{{ $page->getFriendlyUrl() }}" @if ($page->openInNewTab) target="_blank" @endif>{{ $page->getBlurb() }}</a>
                    </div>
                    <div class="category-block-right">
                        <a class="category-block-image" href="{{ $page->getFriendlyUrl() }}" @if ($page->openInNewTab) target="_blank" @endif>
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