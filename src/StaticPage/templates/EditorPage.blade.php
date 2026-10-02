@extends ('Editor/PageBase')

@section ('contentSpecificButtons')
    @include ('View/Widget/Form/Checkbox', ['id' => 'enableComments', 'description' => $t->get('Reacties toestaan'), 'checked' => $enableComments])

    @include('View/Widget/Form/InputText', ['id' => 'tags', 'label' => $t->get('Tags'), 'value' => $tags])
@endsection
