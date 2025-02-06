@component('View/Widget/Form/FormWrapper', ['id' => 'editorHeaderImage', 'label' => $t->get('Afbeelding')])
    @slot('right')
        <div class="input-group">
            <input id="editorHeaderImage" name="editorHeaderImage" class="form-control" value="{{ $editorHeaderImage }}"
                   list="page-header-images"/>
            <label class="btn btn-primary" for="header-image-upload">{{ $t->get('Uploaden…') }}
                <input type="file" id="header-image-upload" name="header-image-upload">
            </label>
        </div>
    @endslot
@endcomponent

@component('View/Widget/Form/FormWrapper', ['id' => 'editorPreviewImage', 'label' => $t->get('Preview-afbeelding')])
    @slot('right')
        <div class="input-group">
            <input id="editorPreviewImage" name="editorPreviewImage" class="form-control" value="{{ $editorPreviewImage }}"
                   list="page-preview-images"/>
            <label class="btn btn-primary" for="preview-image-upload">{{ $t->get('Uploaden…') }}
                <input type="file" id="preview-image-upload" name="preview-image-upload">
            </label>
        </div>
    @endslot
@endcomponent

@include('View/Widget/Form/BasicInput', ['id' => 'blurb', 'label' => $t->get('Korte samenvatting'), 'type' => 'text', 'value' => $blurb])

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
