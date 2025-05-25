@php /** @var \Cyndaron\Geelhoed\Tryout\Tryout|null $model */ @endphp
@extends ('Index')

@section ('contents')
    <form name="bewerkartikel" method="post" action="{{ $saveUrl }}" class="form-horizontal" enctype="multipart/form-data">

        @include('View/Widget/Form/BasicInput', ['id' => 'titel', 'required' => true, 'value' => $contentTitle, 'label' => $t->get('Titel')])

        @include('View/Widget/Form/Select', ['id' => 'locationId', 'label' => 'Locatie', 'selected' => $model?->location?->id, 'options' => $locations])

        @include('View/Widget/Form/DateTime', ['id' => 'start', 'label' => 'Start', 'value' => $model?->start])

        @include('View/Widget/Form/DateTime', ['id' => 'end', 'label' => 'Einde', 'value' => $model?->end])

        <input type="hidden" name="csrfToken" value="{{ $tokenHandler->get('editor', $articleType) }}"/>
        <input type="submit" value="{{ $t->get('Opslaan') }}" class="btn btn-primary"/>
        <a role="button" class="btn btn-outline-cyndaron" href="{{ $referrer }}">{{ $t->get('Annuleren') }}</a>
    </form>

@endsection
