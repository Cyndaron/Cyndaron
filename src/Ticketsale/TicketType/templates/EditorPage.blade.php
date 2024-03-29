@php /** @var \Cyndaron\Ticketsale\TicketType\TicketType|null $model */ @endphp
@extends ('Index')

@section ('contents')
    <form name="bewerkartikel" method="post" action="{{ $saveUrl }}" class="form-horizontal" enctype="multipart/form-data">

        <input type="hidden" name="concertId" value="{{ $concertId }}"/>
        @include('View/Widget/Form/BasicInput', ['id' => 'name', 'type' => 'text', 'label' => 'Omschrijving', 'value' => $model->name ?? ''])
        @include('View/Widget/Form/Currency', ['id' => 'price', 'label' => 'Prijs', 'value' => $model->price ?? 0.00])
        <input type="hidden" name="csrfToken" value="{{ \Cyndaron\User\User::getCSRFToken('editor', $articleType) }}"/>
        <input type="submit" value="Opslaan" class="btn btn-primary"/>
        <a role="button" class="btn btn-outline-cyndaron" href="{{ $referrer }}">Annuleren</a>
    </form>

@endsection
