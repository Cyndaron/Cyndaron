@extends ('Editor/PageBase')

@section ('contentSpecificButtons')
    @include ('View/Widget/Form/Checkbox', ['id' => 'hideFromOverview', 'description' => 'Niet tonen in overzicht fotoalbums', 'checked' => $model->hideFromOverview])
    @include ('View/Widget/Form/Dropdown', ['id' => 'viewMode', 'label' => 'Weergavemodus', 'options' => $viewModeOptions, 'selected' => $model->viewMode])
    @include ('View/Widget/Form/Dimensions', ['id1' => 'thumbnailWidth', 'id2' => 'thumbnailHeight', 'label' => 'Thumbnail-afmetingen', 'value1' => $model->thumbnailWidth, 'value2' => $model->thumbnailHeight])
@endsection
