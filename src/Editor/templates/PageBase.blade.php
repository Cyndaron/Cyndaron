@extends ('Index')

@section ('contents')
    <form name="bewerkartikel" method="post" action="{{ $saveUrl }}" class="form-horizontal" enctype="multipart/form-data">

        @if ($hasTitle)
            @include('View/Widget/Form/BasicInput', ['id' => 'titel', 'required' => true, 'value' => $contentTitle, 'label' => 'Titel'])

            @if ($hasCategory)
                @include ('View/Widget/Form/Checkbox', ['id' => 'showBreadcrumbs', 'description' => 'Titel tonen als breadcrumbs', 'checked' => $showBreadcrumbs])
            @endif

            @component('View/Widget/Form/FormWrapper', ['id' => 'friendlyUrl', 'label' => 'Friendly URL'])
                @slot('right')
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"
                                  id="basic-addon3">{{ $friendlyUrlPrefix }}</span>
                        </div>
                        <input type="text" class="form-control" id="friendlyUrl" name="friendlyUrl"
                               aria-describedby="basic-addon3" value="{{ $friendlyUrl }}"/>
                    </div>
                @endslot
            @endcomponent
        @endif

        @if ($hasCategory)
            @include('View/Widget/Form/BasicInput', ['id' => 'editorHeaderImage', 'label' => 'Afbeelding', 'type' => 'text', 'value' => $editorHeaderImage, 'datalist' => 'page-header-images'])
            @include('View/Widget/Form/BasicInput', ['id' => 'editorPreviewImage', 'label' => 'Preview-afbeelding', 'type' => 'text', 'value' => $editorPreviewImage, 'datalist' => 'page-preview-images'])
            @include('View/Widget/Form/BasicInput', ['id' => 'blurb', 'label' => 'Korte samenvatting', 'type' => 'text', 'value' => $blurb])

            <label class="btn btn-primary" for="header-image-upload">Headerafbeelding uploaden...
                    <input type="file" id="header-image-upload" name="header-image-upload">
            </label>
                <label class="btn btn-primary" for="preview-image-upload">Previewafbeelding uploaden...
                    <input type="file" id="preview-image-upload" name="preview-image-upload">
                </label>
        @endif

        @include('View/Widget/Form/Editor', ['id' => 'artikel', 'value' => $article, 'internalLinks' => $internalLinks])

        @if ($hasCategory)
        <div class="form-group row" id="categories-accordion">
            <div class="container">
                <div class="card">
                    <div class="card-header" id="heading2">
                        <h5 class="mb-0">
                            <button type="button" class="btn btn-link" data-toggle="collapse" data-target="#collapse2" aria-expanded="false" aria-controls="collapse2" style="width: 100%; text-align: left;">
                                Categorieën (klik om open te klappen)
                            </button>
                        </h5>
                    </div>
                    <div id="collapse2" class="collapse" aria-labelledby="heading2" data-parent="#categories-accordion">
                        <div class="card-body">
                            @foreach($categories as $category)
                                <div class="form-group form-check">
                                    <input type="checkbox" class="form-check-input" id="category-{{ $category->id }}" name="category-{{ $category->id }}" value="1" @if($selectedCategories[$category->id] ?? false) checked @endif>
                                    <label class="form-check-label" for="category-{{ $category->id }}">{{ $category->name }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {!! $contents !!}

        @yield ('contentSpecificButtons')


        <input type="hidden" name="csrfToken" value="{{ \Cyndaron\User\User::getCSRFToken('editor', $articleType) }}"/>
        <input type="submit" value="Opslaan" class="btn btn-primary"/>
        <a role="button" class="btn btn-outline-cyndaron" href="{{ $referrer }}">Annuleren</a>
    </form>

    @if ($hasCategory)
        <datalist id="page-header-images">
            @foreach ($pageHeaderImages as $pageHeaderImage)
                <option value="/uploads/images/page-header/{{ $pageHeaderImage }}">
            @endforeach
        </datalist>
        <datalist id="page-preview-images">
            @foreach ($pagePreviewImages as $pagePreviewImage)
                <option value="/uploads/images/page-header/{{ $pagePreviewImage }}">
            @endforeach
            @foreach ($pageHeaderImages as $pageHeaderImage)
                <option value="/uploads/images/page-preview/{{ $pageHeaderImage }}">
            @endforeach
        </datalist>
    @endif

@endsection
