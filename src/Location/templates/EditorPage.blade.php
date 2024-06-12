@php /** @var \Cyndaron\Location\Location|null $model */ @endphp
@extends ('Index')

@section ('contents')
    <form name="bewerkartikel" method="post" action="{{ $saveUrl }}" class="form-horizontal" enctype="multipart/form-data">

        @include('View/Widget/Form/BasicInput', ['id' => 'name', 'type' => 'text', 'label' => 'Naam', 'value' => $model->name ?? ''])
        @include('View/Widget/Form/BasicInput', ['id' => 'street', 'type' => 'text', 'label' => 'Straat', 'value' => $model->street ?? ''])
        @include('View/Widget/Form/BasicInput', ['id' => 'houseNumber', 'type' => 'text', 'label' => 'Huisnummer', 'value' => $model->houseNumber ?? ''])
        @include('View/Widget/Form/BasicInput', ['id' => 'postalCode', 'type' => 'text', 'label' => 'Postcode', 'value' => $model->postalCode ?? ''])
        @include('View/Widget/Form/BasicInput', ['id' => 'city', 'type' => 'text', 'label' => 'Plaats', 'value' => $model->city ?? ''])
        <input type="hidden" name="csrfToken" value="{{ $tokenHandler->get('editor', $articleType) }}"/>
        <input type="submit" value="Opslaan" class="btn btn-primary"/>
        <a role="button" class="btn btn-outline-cyndaron" href="{{ $referrer }}">Annuleren</a>
    </form>

@endsection
