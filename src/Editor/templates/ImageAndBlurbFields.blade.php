@include('View/Widget/Form/BasicInput', ['id' => 'editorHeaderImage', 'label' => 'Afbeelding', 'type' => 'text', 'value' => $editorHeaderImage, 'datalist' => 'page-header-images'])
@include('View/Widget/Form/BasicInput', ['id' => 'editorPreviewImage', 'label' => 'Preview-afbeelding', 'type' => 'text', 'value' => $editorPreviewImage, 'datalist' => 'page-preview-images'])
@include('View/Widget/Form/BasicInput', ['id' => 'blurb', 'label' => 'Korte samenvatting', 'type' => 'text', 'value' => $blurb])

<label class="btn btn-primary" for="header-image-upload">Headerafbeelding uploaden...
    <input type="file" id="header-image-upload" name="header-image-upload">
</label>
<label class="btn btn-primary" for="preview-image-upload">Previewafbeelding uploaden...
    <input type="file" id="preview-image-upload" name="preview-image-upload">
</label>

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
