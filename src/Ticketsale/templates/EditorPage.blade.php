@php /** @var \Cyndaron\Ticketsale\Concert $model */ @endphp
@extends ('Editor/PageBase')

@section ('contentSpecificButtons')
    <div class="form-group">
        <label for="descriptionWhenClosed">Beschijving indien gesloten:</label>
        <textarea class="form-control" id="descriptionWhenClosed" name="descriptionWhenClosed"
                  rows="3">{!! $descriptionWhenClosed !!}</textarea>
    </div>
    @include('View/Widget/Form/Checkbox', ['id' => 'openForSales', 'label' => 'Open voor verkoop', 'checked' => (bool)($model->openForSales ?? false)])

    @include('View/Widget/Form/Select', ['id' => 'delivery', 'label' => 'Kaartlevering', 'required' => true, 'selected' => $delivery, 'options' => [
        0 => 'Bij kerk of opsturen',
        1 => 'Verplicht opsturen',
        2 => 'Digitaal',
    ]])

    @include('View/Widget/Form/Checkbox', ['id' => 'hasReservedSeats', 'label' => 'Heeft gereserveerde plaatsen', 'checked' => (bool)($model->hasReservedSeats ?? false)])

    @include('View/Widget/Form/Checkbox', ['id' => 'reservedSeatsAreSoldOut', 'label' => 'Gereserveerde plaatsen zijn uitverkocht', 'checked' => (bool)($model->reservedSeatsAreSoldOut ?? false)])

    @include('View/Widget/Form/Select', ['id' => 'deliveryCostInterface', 'label' => 'Berekening verzendkosten', 'selected' => $deliveryCostInterface, 'options' => $deliveryCostOptions])

    @include('View/Widget/Form/Currency', ['id' => 'deliveryCost', 'label' => 'Verzendkosten', 'value' => $deliveryCost])

    @include('View/Widget/Form/Currency', ['id' => 'reservedSeatCharge', 'label' => 'Toeslag gereserveerde plaats', 'value' => $reservedSeatCharge])

    @include('View/Widget/Form/Number', ['id' => 'numFreeSeats', 'label' => 'Aantal vrije plaatsen', 'value' => $numFreeSeats])

    @include('View/Widget/Form/Number', ['id' => 'numReservedSeats', 'label' => 'Aantal gereserveerde plaatsen', 'value' => $numReservedSeats])

    @include('View/Widget/Form/BasicInput', ['id' => 'date', 'type' => 'datetime-local', 'label' => 'Datum', 'value' => $model->date ?? ''])

@endsection
