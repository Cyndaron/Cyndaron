@php /** @var \Cyndaron\Ticketsale\Concert\Concert $model */ @endphp
@extends ('Editor/PageBase')

@section ('contentSpecificButtons')

    @include('View/Widget/Form/Checkbox', ['id' => 'openForSales', 'label' => 'Open voor verkoop', 'checked' => (bool)($model->openForSales ?? false)])

    @component('View/Widget/Form/FormWrapper', ['id' => 'descriptionWhenClosed', 'label' => 'Beschijving indien gesloten'])
        @slot('right')
            @include('View/Widget/Form/Editor', ['id' => 'descriptionWhenClosed', 'value' => $descriptionWhenClosed])
        @endslot
    @endcomponent

    @include('View/Widget/Form/Select', ['id' => 'delivery', 'label' => 'Kaartlevering', 'required' => true, 'selected' => $delivery, 'options' => [
        0 => 'Bij kerk of opsturen',
        1 => 'Verplicht opsturen',
        2 => 'Digitaal',
    ]])

    @include('View/Widget/Form/Select', ['id' => 'deliveryCostInterface', 'label' => 'Berekening verzendkosten', 'selected' => $deliveryCostInterface, 'options' => $deliveryCostOptions])

    @include('View/Widget/Form/Currency', ['id' => 'deliveryCost', 'label' => 'Verzendkosten', 'value' => $deliveryCost])

    @include('View/Widget/Form/Number', ['id' => 'numFreeSeats', 'label' => 'Aantal vrije plaatsen', 'value' => $numFreeSeats])

    @include('View/Widget/Form/BasicInput', ['id' => 'date', 'type' => 'datetime-local', 'label' => 'Datum', 'value' => $model->date ?? ''])

    @include('View/Widget/Form/BasicInput', ['id' => 'location', 'type' => 'text', 'label' => 'Locatie', 'value' => $model->location ?? ''])

    @include('View/Widget/Form/Select', ['id' => 'locationId', 'label' => 'Locatie', 'selected' => $model->location?->id, 'options' => $locations])

    @component('View/Widget/Form/FormWrapper', ['id' => 'ticketInfo', 'label' => 'Informatie op de tickets'])
        @slot('right')
            @include('View/Widget/Form/Editor', ['id' => 'ticketInfo', 'value' => $model->ticketInfo ?? ''])
        @endslot
    @endcomponent

    <hr>

    @include('View/Widget/Form/Checkbox', ['id' => 'hasReservedSeats', 'label' => 'Heeft gereserveerde plaatsen', 'checked' => (bool)($model->hasReservedSeats ?? false)])

    @include('View/Widget/Form/Checkbox', ['id' => 'reservedSeatsAreSoldOut', 'label' => 'Gereserveerde plaatsen zijn uitverkocht', 'checked' => (bool)($model->reservedSeatsAreSoldOut ?? false)])

    @include('View/Widget/Form/Number', ['id' => 'numReservedSeats', 'label' => 'Aantal gereserveerde plaatsen', 'value' => $numReservedSeats])

    @include('View/Widget/Form/Currency', ['id' => 'reservedSeatCharge', 'label' => 'Toeslag gereserveerde plaats', 'value' => $reservedSeatCharge])

@endsection
