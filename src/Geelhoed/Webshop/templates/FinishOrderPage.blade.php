@extends('Index')

@section('contents')
    @php /** @var \Cyndaron\Geelhoed\Webshop\Model\OrderItem[] $orderItems */ @endphp
    @php /** @var \Cyndaron\Geelhoed\Clubactie\Subscriber $subscriber */ @endphp


    <h2>Overzicht van de bestelling</h2>
    @include('Geelhoed/Webshop/OrderOverview', ['orderItems' => $orderItems])

    Subtotaal loten: {{ $ticketSubtotal }}
    <br>Subtotaal euro: {{ $euroSubtotal|euro }}

    @if (!$subscriber->soldTicketsAreVerified)
        @if ($euroSubtotal === 0.00)
            <div class="alert alert-info">
                Je puntenaantal moet worden gecheckt voordat je bestelling in behandeling genomen wordt.
                Je krijgt hierover nog bericht.
            </div>
        @else
            <div class="alert alert-info">
                Je puntenaantal moet worden gecheckt voordat je bestelling in behandeling genomen wordt.
                Je krijgt hierover nog bericht. In dit bericht zit ook een betaallink.
            </div>
        @endif
    @else
        @if ($euroSubtotal > 0.00)
            <div class="alert alert-info">
                Na bevestiging word je doorgestuurd naar de betaalomgeving.
            </div>
        @endif
    @endif

    <form method="post" action="/webwinkel/bestelling-plaatsen">
        <input type="hidden" name="hash" value="{{ $hash }}"/>
        @include('View/Widget/Form/BasicInput', ['id' => 'name', 'label' => 'Naam', 'required' => true, 'value' => $subscriber->getFullName(), 'readonly' => true])
        @include('View/Widget/Form/Select', ['id' => 'locationId', 'label' => 'Leslocatie', 'required' => true, 'options' => $locations])
        @include('View/Widget/Form/Select', ['id' => 'day', 'label' => 'Lesdag', 'required' => true, 'options' => $days])

        <button type="submit" class="btn btn-primary">Bevestigen</button>
    </form>
@endsection
