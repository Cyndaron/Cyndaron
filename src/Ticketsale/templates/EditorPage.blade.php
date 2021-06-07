@extends ('Editor/PageBase')

@section ('contentSpecificButtons')
    <div class="form-group">
        <label for="descriptionWhenClosed">Beschijving indien gesloten:</label>
        <textarea class="form-control" id="descriptionWhenClosed" name="descriptionWhenClosed" rows="3">{!! $descriptionWhenClosed !!}</textarea>
    </div>
    @include('View/Widget/Form/Checkbox', ['id' => 'openForSales', 'label' => 'Open voor verkoop', 'checked' => (bool)($model->openForSales ?? false)])

    @include('View/Widget/Form/Checkbox', ['id' => 'forcedDelivery', 'label' => 'Bezorgen verplicht', 'checked' => (bool)($model->forcedDelivery ?? false)])

    @include('View/Widget/Form/Checkbox', ['id' => 'hasReservedSeats', 'label' => 'Heeft gereserveerde plaatsen', 'checked' => (bool)($model->hasReservedSeats ?? false)])

    @include('View/Widget/Form/Checkbox', ['id' => 'reservedSeatsAreSoldOut', 'label' => 'Gereserveerde plaatsen zijn uitverkocht', 'checked' => (bool)($model->reservedSeatsAreSoldOut ?? false)])

    @include('View/Widget/Form/Currency', ['id' => 'deliveryCost', 'label' => 'Verzendkosten', 'value' => $deliveryCost])

    @include('View/Widget/Form/Currency', ['id' => 'reservedSeatCharge', 'label' => 'Toeslag gereserveerde plaats', 'value' => $reservedSeatCharge])

    @include('View/Widget/Form/Number', ['id' => 'numFreeSeats', 'label' => 'Aantal vrije plaatsen', 'value' => $numFreeSeats])

    @include('View/Widget/Form/Number', ['id' => 'numReservedSeats', 'label' => 'Aantal gereserveerde plaatsen', 'value' => $numReservedSeats])

@endsection
