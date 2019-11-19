@extends ('Editor/PageBase')

@section ('contentSpecificButtons')
    @include ('Editor/Checkbox', ['id' => 'hideFromOverview', 'description' => 'Niet tonen in overzicht fotoalbums', 'checked' => $model->hideFromOverview])
    @include ('Editor/Dropdown', ['id' => 'viewMode', 'label' => 'Weergavemodus', 'options' => $viewModeOptions, 'selected' => $model->viewMode])
@endsection