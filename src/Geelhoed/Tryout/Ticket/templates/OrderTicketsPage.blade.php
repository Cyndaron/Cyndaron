@extends ('Index')

@section ('contents')
    <form method="post" action="/tryout-ticket/order" class="form-horizontal" id="kaartenbestellen">
        <input type="hidden" name="csrfToken" value="{{ $tokenHandler->get('tryout-ticket', 'order') }}"/>
        <h3>Kaartsoorten:</h3>
        <input type="hidden" id="eventId" name="event_id" value="{{ $event->id }}"/>
        <table class="kaartverkoop table table-striped">
            <tr>
                <th>Kaartsoort:</th>
                <th>Prijs per stuk:</th>
                <th>Aantal:</th>
            </tr>
            @php /** @var \Cyndaron\Geelhoed\Tryout\Ticket\Type[] $ticketTypes */ @endphp
            @foreach ($ticketTypes as $ticketType)
                @include(
                    'Event/Form/Order/TicketLine',
                    ['name' => $ticketType->name, 'id' => $ticketType->id, 'description' => $ticketType->annotation, 'price' => $ticketType->price]
                )
            @endforeach
        </table>

        <div class="well"><b>Totaalprijs:</b> <span id="prijsvak">€&nbsp;0,00</span></div>

        <h3>Uw gegevens (verplicht):</h3>

        <div class="form-group row">
            <label class="col-sm-3 col-form-label" for="name">Naam:</label>
            <div class="col-sm-5"><input id="name" name="name" class="form-control" autocomplete="family-name" required /></div>
        </div>

        <div class="form-group row">
            <label class="col-sm-3 col-form-label" for="email">E-mailadres:</label>
            <div class="col-sm-5"><input id="email" name="email" type="email" class="form-control" autocomplete="email" required /></div>
        </div>

        <div class="form-group row">
            <div class="col-sm-3"></div>
            <div class="col-sm-5"><input id="verzendknop" class="btn btn-primary btn-lg" type="submit" value="Bestellen"/></div>
        </div>
    </form>
@endsection
