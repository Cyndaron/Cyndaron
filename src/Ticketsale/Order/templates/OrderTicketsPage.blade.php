@extends('Index')

@section('titleControls')
    <a href="/editor/concert/{{ $concert->id }}" class="btn btn-outline-cyndaron" title="Dit concert bewerken" role="button"><span class="glyphicon glyphicon-pencil"></span></a>
@endsection

@section('contents')
    <input type="hidden" id="organisation-value" value="{{ $organisation }}"/>

    @php /** @var \Cyndaron\Ticketsale\Concert $concert */ @endphp
    @if (!$concert->openForSales)
        @if ($concert->descriptionWhenClosed)
            {{ $concert->descriptionWhenClosed }}
        @else
            Voor dit concert kunt u kaarten kopen aan de kassa in de St. Jacobskerk voor aanvang van het concert. Bestellen via de website is voor dit concert niet meer mogelijk.
        @endif
    @else
        <p>{{ $concert->description }}</p>

        <h3>Rang 1 en rang 2</h3>
        <p>{{ sprintf(\Cyndaron\Util\Setting::get('ticketsale_reservedSeatsDescription'), $concert->numReservedSeats) }}</p>

        <br/>
        <form method="post" action="/concert-order/add" class="form-horizontal" id="kaartenbestellen">
            <input type="hidden" name="csrfToken" value="{{ \Cyndaron\User\User::getCSRFToken('concert-order', 'add') }}"/>
            <h3>Kaartsoorten:</h3>
            <input type="hidden" id="concertId" name="concert_id" value="{{ $concert->id }}"/>
            <table class="kaartverkoop table table-striped">
                <tr>
                    <th>Kaartsoort:</th>
                    <th>Prijs per stuk:</th>
                    <th>Aantal:</th>
                </tr>
                @php /** @var \Cyndaron\Ticketsale\TicketType[] $ticketTypes */ @endphp
                @foreach ($ticketTypes as $ticketType)
                    <tr>
                        <td>{{ $ticketType->name }}</td>
                        <td>{{ $ticketType->price|euro }}</td>
                        <td>
                            <input class="numTickets form-control form-control-inline" readonly="readonly" size="2" name="tickettype-{{ $ticketType->id }}" id="tickettype-{{ $ticketType->id }}" value="0"/>
                            <button type="button" class="numTickets btn btn-outline-cyndaron numTickets-increase" data-kaartsoort="{{ $ticketType->id }}"><span class="glyphicon glyphicon-plus"></span></button>
                            <button type="button" class="numTickets btn btn-outline-cyndaron numTickets-decrease" data-kaartsoort="{{ $ticketType->id }}"><span class="glyphicon glyphicon-minus"></span></button>
                        </td>
                    </tr>
                @endforeach
            </table>
            <div @if ($concert->getDelivery() !== \Cyndaron\Ticketsale\TicketDelivery::COLLECT_OR_DELIVER) style="display:none" @endif>
                <input id="bezorgen" name="bezorgen" type="checkbox" value="1" class="recalculateTotal">
                <label for="bezorgen">
                    Bezorg mijn kaarten thuis (hiervoor worden extra kosten in rekening gebracht)
                </label>
            </div>

            @if ($concert->hasReservedSeats)
                @if ($concert->reservedSeatsAreSoldOut)
                    <input id="hasReservedSeats-0" name="hasReservedSeats" style="display:none;"
                        type="checkbox" value="0"/>
                    U kunt voor dit concert nog kaarten voor Rang 2. <b>De kaarten voor Rang 1 zijn inmiddels uitverkocht.</b>
                @else
                    <div class="form-group form-check">
                        <input id="hasReservedSeats-1" class="recalculateTotal form-check-input" name="hasReservedSeats"
                               type="radio" value="1"/>
                        <label class="form-check-label" for="hasReservedSeats-1">
                            Plaats op Rang 1 (meerprijs
                            van {{ $concert->reservedSeatCharge|euro }} per kaart)
                        </label>
                    </div>
                    <div class="form-group form-check">
                        <input id="hasReservedSeats-0" class="recalculateTotal form-check-input" name="hasReservedSeats"
                               type="radio" value="0" checked/>
                        <label class="form-check-label" for="hasReservedSeats-0">
                            Plaats op Rang 2
                        </label>
                    </div>
                @endif
            @else
                <input id="hasReservedSeats-0" name="hasReservedSeats" type="hidden" value="0">
            @endif

            @if ($concert->getDelivery() === \Cyndaron\Ticketsale\TicketDelivery::FORCED_PHYSICAL)
                <h3>Bezorging</h3>
                <p>
                    Bij dit concert is het alleen mogelijk om uw kaarten te laten thuisbezorgen. Als u op Walcheren
                    woont is dit gratis.
                    @if (strpos($concert->deliveryCostInterface, 'FlatFee') !== false)
                        Woont u buiten Walcheren, dan kost het
                        thuisbezorgen {{ $concert->deliveryCost|euro }} per kaart.<br>
                    @else
                        Woont u buiten Walcheren, dan worden er verzendkosten in rekening gebracht.
                    @endif
                    Het is ook mogelijk om uw kaarten te laten ophalen door een koorlid. Dit is gratis.
                </p>

                <div class="radio">
                    <label for="country-nederland">
                        <input id="country-nederland" name="country" type="radio" value="nederland" checked />
                        Ik woon in Nederland
                    </label>
                </div>
                <div class="radio">
                    <label for="country-abroad">
                        <input id="country-abroad" name="country" type="radio" value="abroad"/>
                        Ik woon niet in Nederland
                    </label>
                </div>
                <br>


                <p class="postcode-related">
                    Vul hieronder uw postcode in om de totaalprijs te laten berekenen.
                </p>

                <div class="form-group row postcode-related">
                    <label class="col-sm-3 col-form-label" for="postcode">Postcode (verplicht):</label>
                    <div class="col-sm-5"><input id="postcode" name="postcode" class="form-control form-control-inline"
                                                 maxlength="7"/></div>
                </div>

                <div id="deliveryByMember_div" style="display:none;">
                    <input id="deliveryByMember" name="deliveryByMember" type="checkbox" value="1"
                           class="recalculateTotal">
                    <label for="deliveryByMember">Mijn kaarten laten ophalen door een koorlid</label>
                    <br>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label" for="deliveryMemberName">Naam koorlid:</label>
                        <div class="col-sm-5"><input id="deliveryMemberName" name="deliveryMemberName" type="text"
                                                     class="form-control"/></div>
                    </div>
                </div>
            @endif

            <div class="well"><b>Totaalprijs:</b> <span id="prijsvak">€&nbsp;0,00</span></div>

            <h3>Uw gegevens (verplicht):</h3>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="lastName">Achternaam:</label>
                <div class="col-sm-5"><input id="lastName" name="lastName" class="form-control" required /></div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="initials">Voorletters:</label>
                <div class="col-sm-5"><input id="initials" name="initials" class="form-control" required /></div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="email">E-mailadres:</label>
                <div class="col-sm-5"><input id="email" name="email" type="email" class="form-control" required /></div>
            </div>

            <div class="radio">
                <label for="donor-yes">
                    <input id="donor-yes" name="donor" type="radio" value="1"/>
                    Ik ben donateur
                </label>
            </div>
            <div class="radio">
                <label for="donor-no">
                    <input id="donor-no" name="donor" type="radio" value="0" />
                    Ik ben geen donateur
                </label>
            </div>

            <h3 id="adresgegevensKop">Uw adresgegevens:</h3>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="street">Straatnaam en huisnummer:</label>
                <div class="col-sm-5"><input id="street" name="street"
                                             class="form-control"/></div>
            </div>

            @if ($concert->getDelivery() !== \Cyndaron\Ticketsale\TicketDelivery::FORCED_PHYSICAL)
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label" for="postcode">Postcode:</label>
                    <div class="col-sm-5"><input id="postcode" name="postcode" class="form-control"/></div>
                </div>
            @endif

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="city">Woonplaats:</label>
                <div class="col-sm-5"><input id="city" name="city" class="form-control"/></div>
            </div>


            <h3>Verzenden:</h3>

            <p>Als u nog opmerkingen heeft kunt u deze hier kwijt.</p>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="comments">Opmerkingen (niet verplicht):</label>
                <div class="col-sm-5"><textarea id="comments" name="comments" class="form-control"
                                                rows="4"></textarea></div>
            </div>

            <p>Om te voorkomen dat er spam wordt verstuurd met dit formulier<br/>wordt u verzocht in het onderstaande
                vak <span class="inline-monospace">Vlissingen</span> in te vullen.</p>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="antispam">Antispam:</label>
                <div class="col-sm-5"><input id="antispam" name="antispam" class="form-control"/></div>
            </div>

            <div class="col-sm-offset-2"><input id="verzendknop" class="btn btn-primary" type="submit"
                                                value="Bestellen"/></div>
        </form>
    @endif
@endsection
