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
            <input type="hidden" id="eventId" name="event_id" value="{{ $event->id }}"/>

            <h3>Uw gegevens (verplicht):</h3>

            @include('Widget/Form/Select', [
                'id' => 'lunchType',
                'label' => 'Soort lunch',
                'options' => ['Vegetarisch' => 'Vegetarisch', 'Vis' => 'Vis', 'Vlees' => 'Vlees']])

            <div class="form-group row">
                <div class="col-sm-12">
                    <input type="radio" id="registrationGroup-0" name="registrationGroup" value="0" checked />
                    <label for="registrationGroup-0">Ik ben volwassene</label>
                    <br>
                    <input type="radio" id="registrationGroup-1" name="registrationGroup" value="1"/>
                    <label for="registrationGroup-1">Ik ben jonger dan 20 jaar of student</label>
                </div>
            </div>

            @include('Widget/Form/BasicInput', ['id' => 'lastName', 'label' => 'Achternaam', 'required' => true])
            @include('Widget/Form/BasicInput', ['id' => 'initials', 'label' => 'Voorletters', 'required' => true])
            @include('Widget/Form/Select', ['id' => 'vocalRange', 'label' => 'Stemsoort', 'required' => true, 'options' => ['Sopraan' => 'Sopraan', 'Alt' => 'Alt', 'Tenor' => 'Tenor', 'Bas' => 'Bas']])
            @include('Widget/Form/BasicInput', ['id' => 'email', 'type' => 'email', 'label' => 'E-mailadres', 'required' => true])

            @include('Widget/Form/Checkbox', ['id' => 'lunch', 'label' => 'Ik wil graag gebruik maken van de lunch'])

            <div id="lunchTypeWrapper">
                @include('Widget/Form/Select', [
                    'id' => 'lunchType',
                    'label' => 'Soort lunch',
                    'options' => ['Vegetarisch' => 'Vegetarisch', 'Vis' => 'Vis', 'Vlees' => 'Vlees']])
            </div>

            @include('Widget/Form/Checkbox', ['id' => 'bhv', 'label' => 'Ik ben arts of in het bezit van een BHV- of AED-certificaat'])
            @include('Widget/Form/Checkbox', ['id' => 'kleinkoor', 'label' => 'Ik wil graag meezingen in het kleinkoor'])

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

            @include('Widget/Form/BasicInput', ['id' => 'birthYear', 'label' => 'Uw geboortejaar', 'type' => 'number', 'min' => 1900, 'max' => date('Y') - 10, 'step' => 1, 'pattern' => '[0-9]{4}'])

            @include('Widget/Form/BasicInput', ['id' => 'street', 'label' => 'Straatnaam'])
            @include('Widget/Form/BasicInput', ['id' => 'houseNumber', 'label' => 'Huisnummer', 'type' => 'number'])
            @include('Widget/Form/BasicInput', ['id' => 'houseNumberAddition', 'label' => 'Huisnummertoevoeging'])
            @include('Widget/Form/BasicInput', ['id' => 'postcode', 'label' => 'Postcode'])
            @include('Widget/Form/BasicInput', ['id' => 'city', 'label' => 'Woonplaats'])

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

            @include('Widget/Form/BasicInput', ['id' => 'antispam', 'label' => 'Antispam', 'required' => true])

            <p>Uw gegevens zullen worden verwerkt zoals beschreven in onze <a href="/privacyverklaring">privacyverklaring</a>.</p>

            <div class="col-sm-offset-2"><input id="verzendknop" class="btn btn-primary" type="submit"
                                                value="Inschrijven"/></div>
        </form>
    @endif
@endsection
