@extends ('Editor/PageBase')

@section ('contentSpecificButtons')
    @include ('View/Widget/Form/Checkbox', ['id' => 'hideFromOverview', 'description' => 'Niet tonen in overzicht fotoalbums', 'checked' => $model->hideFromOverview])
    @include ('View/Widget/Form/Dropdown', ['id' => 'viewMode', 'label' => 'Weergavemodus', 'options' => $viewModeOptions, 'selected' => $model->viewMode])
@endsection
