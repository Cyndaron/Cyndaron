@extends ('Index')

@section ('contents')
    <form name="bewerkartikel" method="post" action="{{ $saveUrl }}" class="form-horizontal" enctype="multipart/form-data">

        @if ($hasTitle)
            @include('Editor/TitleFields', [
                'contentTitle' => $contentTitle,
                'hasCategory' => $hasCategory,
                'showBreadcrumbs' => ($showBreadcrumbs ?? false),
                'friendlyUrlPrefix' => $friendlyUrlPrefix,
                'friendlyUrl' => $friendlyUrl,
            ])
        @endif

        @if ($hasCategory)
            @include('Editor/ImageAndBlurbFields', [
                'editorHeaderImage' => $editorHeaderImage,
                'editorPreviewImage' => $editorPreviewImage,
                'blurb' => $blurb,
                'pageHeaderImages' => $pageHeaderImages,
                'pagePreviewImages' => $pagePreviewImages,
            ])
        @endif

        @include('View/Widget/Form/Editor', ['id' => 'artikel', 'value' => $article, 'internalLinks' => $internalLinks])

        @if ($hasCategory)
            @include('Editor/CategorySelector', [
                'categories' => $categories,
                'selectedCategories' => $selectedCategories,
            ])
        @endif

        @yield ('contentSpecificButtons')


        <input type="hidden" name="csrfToken" value="{{ \Cyndaron\User\User::getCSRFToken('editor', $articleType) }}"/>
        <input type="submit" value="Opslaan" class="btn btn-primary"/>
        <a role="button" class="btn btn-outline-cyndaron" href="{{ $referrer }}">Annuleren</a>
    </form>

@endsection
