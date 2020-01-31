@extends ('Index')

@section ('titleControls')
    <a href="/editor/event/{{ $event->id }}" class="btn btn-outline-cyndaron" title="Dit evenement bewerken" role="button"><span class="glyphicon glyphicon-pencil"></span></a>
@endsection

@section ('contents')
    @php /** @var \Cyndaron\Registration\Event $event */ @endphp
    @if (!$event->openForRegistration)
        {!! $event->descriptionWhenClosed ?? 'Voor dit evenement kunt u zich helaas niet meer inschrijven.' !!}
    @else
        {!! $event->description !!}

        <form method="post" action="/event-order/add" class="form-horizontal" id="kaartenbestellen">
            <input type="hidden" name="csrfToken" value="{{ \Cyndaron\User\User::getCSRFToken('event-order', 'add') }}"/>

            <h3>Uw gegevens (verplicht):</h3>

            <div class="form-group row">
                <div class="col-sm-12">
                    <input type="radio" id="registrationGroup-0" name="registrationGroup" value="0" checked />
                    <label for="registrationGroup-0">Ik ben volwassene</label>
                    <br>
                    <input type="radio" id="registrationGroup-1" name="registrationGroup" value="1"/>
                    <label for="registrationGroup-1">Ik ben jonger dan 20 jaar of student</label>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="lastName">Achternaam:</label>
                <div class="col-sm-5"><input id="lastName" name="lastName" required class="form-control"/></div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="initials">Voorletters:</label>
                <div class="col-sm-5"><input id="initials" name="initials" required class="form-control"/></div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="vocalRange">Stemsoort:</label>
                <div class="col-sm-5">
                    <select id="vocalRange" name="vocalRange" required class="form-control">
                        <option value="">Kies een zangstem</option>
                        <option>Sopraan</option>
                        <option>Alt</option>
                        <option>Tenor</option>
                        <option>Bas</option>
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="email">E-mailadres:</label>
                <div class="col-sm-5"><input id="email" name="email" type="email" required class="form-control"/></div>
            </div>

            <div class="form-group row">
                <div class="col-sm-12">
                    <input id="lunch" name="lunch" type="checkbox" value="1" class="recalculateTotal">
                    <label for="lunch">Ik wil graag gebruik maken van de lunch</label>
                </div>
            </div>

            <div id="lunchTypeWrapper">
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label" for="lunchType">Soort lunch:</label>
                    <div class="col-sm-5">
                        <select id="lunchType" name="lunchType" class="form-control">
                            <option value="Vegetarisch">Vegetarisch</option>
                            <option value="Vis">Vis</option>
                            <option value="Vlees">Vlees</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-12">
                    <input id="bhv" name="bhv" type="checkbox" value="1">
                    <label for="bhv">Ik ben arts of in het bezit van een BHV- of AED-certificaat</label>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-12">
                    <input id="kleinkoor" name="kleinkoor" type="checkbox" value="1">
                    <label for="kleinkoor">Ik wil graag meezingen in het kleinkoor</label>
                </div>
            </div>

            <div id="kleinkoorExplanationWrapper">
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label" for="kleinkoorExplanation">Mijn zangervaring/huidige koor:</label>
                    <div class="col-sm-5">
                        <textarea id="kleinkoorExplanation" name="kleinkoorExplanation"
                                  class="form-control" rows="4"></textarea>
                    </div>
                </div>
            </div>

            <h3 id="adresgegevensKop">Overige gegevens (niet verplicht):</h3>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="birthYear">Uw geboortejaar:</label>
                <div class="col-sm-1"><input id="birthYear" name="birthYear" type="number" min="1900" max="2019" step="1" pattern="[0-9]{4}" class="form-control"/></div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="street">Straatnaam:</label>
                <div class="col-sm-5"><input id="street" name="street"
                                             class="form-control"/></div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="houseNumber">Huisnummer:</label>
                <div class="col-sm-5"><input id="houseNumber" name="houseNumber" type="number"
                                             class="form-control"/></div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="houseNumberAddition">Huisnummertoevoeging:</label>
                <div class="col-sm-5"><input id="houseNumberAddition" name="houseNumberAddition"
                                             class="form-control"/></div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="postcode">Postcode:</label>
                <div class="col-sm-5"><input id="postcode" name="postcode" class="form-control"/></div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="city">Woonplaats:</label>
                <div class="col-sm-5"><input id="city" name="city" class="form-control"/></div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label">Eerder meegedaan?</label>
                <div class="col-sm-5">
                    <input id="participatedBefore1" name="participatedBefore" type="radio" value="1"/> <label for="participatedBefore1">Ik heb al eerder meegedaan</label><br>
                    <input id="participatedBefore0" name="participatedBefore" type="radio" value="0"/> <label for="participatedBefore0">Ik heb nog niet eerder meegedaan</label>
                </div>
            </div>

            <p>Hoeveel raamposters kunt u kwijt om ons concert te promoten? U krijgt op termijn bericht hoe deze posters te verkrijgen zijn.</p>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="numPosters">Aantal raamposters:</label>
                <div class="col-sm-1"><input id="numPosters" name="numPosters" type="number" min="0" max="9" step="1" pattern="[0-9]{1}" value="0" class="form-control"/></div>
            </div>

            <h3>Kaarten voor vrienden/familie:</h3>
            <input type="hidden" id="eventId" name="event_id" value="{{ $event->id }}"/>
            <table class="kaartverkoop table table-striped">
                <thead>
                    <tr>
                        <th>Kaartsoort:</th>
                        <th>Prijs per stuk:</th>
                        <th>Aantal:</th>
                    </tr>
                </thead>
                <tbody>
                    @php /** @var \Cyndaron\Registration\EventTicketType[] $ticketTypes */ @endphp
                    @foreach ($ticketTypes as $ticketType)
                        <tr>
                            <td>{{ $ticketType->name }}</td>
                            <td>{{ $ticketType->price|euro }}@if ($ticketType->discountPer5) (5 halen, 4 betalen)@endif</td>
                            <td>
                                <input class="numTickets form-control form-control-inline" readonly="readonly" size="2" name="tickettype-{{ $ticketType->id }}" id="tickettype-{{ $ticketType->id }}" value="0"/>
                                <button type="button" class="numTickets btn btn-outline-cyndaron numTickets-increase" data-kaartsoort="{{ $ticketType->id }}"><span class="glyphicon glyphicon-plus"></span></button>
                                <button type="button" class="numTickets btn btn-outline-cyndaron numTickets-decrease" data-kaartsoort="{{ $ticketType->id }}"><span class="glyphicon glyphicon-minus"></span></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div><b>Totaalprijs:</b> <span id="prijsvak">€&nbsp;0,00</span></div>


            <h3>Verzenden:</h3>

            <p>Als u nog opmerkingen heeft kunt u deze hier kwijt.</p>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="comments">Opmerkingen (niet verplicht):</label>
                <div class="col-sm-5"><textarea id="comments" name="comments" class="form-control"
                                                rows="4"></textarea></div>
            </div>

            <p>Om te voorkomen dat er spam wordt verstuurd met dit formulier<br/>wordt u verzocht in het onderstaande
                vak <span style="font-family:monospace;">{{ $event->getAntiSpam() }}</span> in te vullen.</p>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="antispam">Antispam:</label>
                <div class="col-sm-5"><input id="antispam" name="antispam" required class="form-control"/></div>
            </div>

            <p>Uw gegevens zullen worden verwerkt zoals beschreven in onze <a href="/privacyverklaring">privacyverklaring</a>.</p>

            <div class="col-sm-offset-2"><input id="verzendknop" class="btn btn-primary" type="submit"
                                                value="Inschrijven"/></div>
        </form>
    @endif
@endsection
