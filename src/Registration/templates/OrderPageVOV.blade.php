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
            <input type="hidden" id="eventId" name="event_id" value="{{ $event->id }}" autocomplete="off"/>
            <input type="hidden" name="registrationGroup" value="0"/>

            <h3>Uw gegevens (verplicht):</h3>

            @include('Widget/Form/BasicInput', ['id' => 'lastName', 'label' => 'Achternaam', 'required' => true])
            @include('Widget/Form/BasicInput', ['id' => 'initials', 'label' => 'Voornaam', 'required' => true])
            @include('Widget/Form/BasicInput', ['id' => 'city', 'label' => 'Woonplaats', 'required' => true])
            @include('Widget/Form/BasicInput', ['id' => 'email', 'type' => 'email', 'label' => 'E-mailadres', 'required' => true])

            @include('Widget/Form/Select', ['id' => 'birthYear', 'label' => 'Leeftijdscategorie', 'required' => true, 'options' => [
                              (0) => 'Maak een keuze',
                 (date('Y') - 12) => '12 - 25',
                 (date('Y') - 26) => '26 - 50',
                 (date('Y') - 51) => '51 - 65',
                 (date('Y') - 66) => '66 - 70',
                 (date('Y') - 71) => '71 - 75',
                 (date('Y') - 76) => '76 - 80',
                 (date('Y') - 81) => '81+',
            ]])
            @include('Widget/Form/Select', ['id' => 'vocalRange', 'label' => 'Stemsoort', 'required' => true, 'options' => ['Sopraan' => 'Sopraan', 'Alt' => 'Alt', 'Tenor' => 'Tenor', 'Bas' => 'Bas']])

            <div class="form-group row">
                <label for="currentChoir" class="col-md-3 col-form-label">Ik ben lid van:</label>
                <div class="col-md-6">
                    <select id="currentChoir" name="currentChoir" class="form-control custom-select" required>
                        <option data-registration-group="0" selected value="" disabled>Maak een keuze</option>
                        <option data-registration-group="1" value="Vlissingse Oratorium Vereniging">Vlissingse Oratorium Vereniging</option>
                        <option data-registration-group="2" value="KOV Middelburg">KOV Middelburg</option>
                        <option data-registration-group="2" value="COV Goes">COV Goes</option>
                        <option data-registration-group="2" value="Vocaal Ensembe Cantare">Vocaal Ensembe Cantare</option>
                        <option data-registration-group="2" value="COV Te Deum Laudamus Kapelle">COV Te Deum Laudamus Kapelle</option>
                        <option data-registration-group="2" value="Zwinkoor Sluis">Zwinkoor Sluis</option>
                        <option data-registration-group="2" value="Hét Concertkoor Bergen op Zoom">Hét Concertkoor Bergen op Zoom</option>
                        <option data-registration-group="2" value="COV Soli Deo Gloria Sliedrecht">COV Soli Deo Gloria Sliedrecht</option>
                        <option data-registration-group="0" value="">Geen koor / een ander koor</option>
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label">Eerder meegedaan?</label>
                <div class="col-sm-5">
                    <input id="participatedBefore1" name="participatedBefore" type="radio" value="1"/> <label for="participatedBefore1">Ik heb al eerder meegedaan</label><br>
                    <input id="participatedBefore0" name="participatedBefore" type="radio" value="0"/> <label for="participatedBefore0">Ik heb nog niet eerder meegedaan</label>
                </div>
            </div>
            <div id="participatedBeforeWrapper">
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label" for="kleinkoorExplanation">Wanneer was de laatste keer en waar?:</label>
                    <div class="col-sm-5">
                        <textarea id="kleinkoorExplanation" name="kleinkoorExplanation"
                                  class="form-control" rows="4"></textarea>
                    </div>
                </div>
            </div>

            @include('Widget/Form/Select', ['id' => 'choirPreference', 'label' => 'Voorkeur koor 1 / 2', 'required' => true, 'options' => ['Geen voorkeur' => 'Geen voorkeur', 'Voorkeur koor 1' => 'Voorkeur koor 1', 'Voorkeur koor 2' => 'Voorkeur koor 2']])

            <div><h5><b>Totaalprijs:</b> <span id="prijsvak">€&nbsp;0,00</span></h5></div>


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

{{--            <p>Uw gegevens zullen worden verwerkt zoals beschreven in onze <a href="/privacyverklaring">privacyverklaring</a>.</p>--}}

            <div class="col-sm-offset-2"><input id="verzendknop" class="btn btn-primary" type="submit"
                                                value="Inschrijven"/></div>
        </form>
    @endif
@endsection
