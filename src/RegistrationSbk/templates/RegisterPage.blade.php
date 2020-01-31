@extends ('Index')
@php /** @var \Cyndaron\RegistrationSbk\Event $event */@endphp

@section ('titleControls')
    <a href="/editor/eventSbk/{{ $event->id }}" class="btn btn-outline-cyndaron" title="Dit evenement bewerken" role="button"><span class="glyphicon glyphicon-pencil"></span></a>
@endsection

@section ('contents')
    @if (!$event->openForRegistration)
        {!! $event->descriptionWhenClosed ?: 'Voor dit evenement kun je je helaas niet meer aanmelden.' !!}
    @else
        {!! $event->description !!}

        <form method="post" action="/eventSbk-registration/add" class="form-horizontal" id="kaartenbestellen">
            <input type="hidden" name="csrfToken" value="{{ \Cyndaron\User\User::getCSRFToken('eventSbk-registration', 'add') }}"/>
            <input type="hidden" id="eventId" name="event_id" value="{{ $event->id }}"/>

            <h3>Je gegevens (verplicht):</h3>

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
                <label class="col-sm-3 col-form-label" for="city">Woonplaats:</label>
                <div class="col-sm-5"><input id="city" name="city" class="form-control"/></div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="email">E-mailadres:</label>
                <div class="col-sm-5"><input id="email" name="email" type="email" required class="form-control"/></div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="phone">Telefoonnummer:</label>
                <div class="col-sm-5"><input id="phone" name="phone" type="tel" required class="form-control"/></div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="currentChoir">Ik ben koorlid bij:</label>
                <div class="col-sm-5"><input id="currentChoir" name="currentChoir" required class="form-control"/></div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="choirExperience">Ik heb:</label>
                <div class="col-sm-5"><input id="choirExperience" name="choirExperience" type="number" min="0" required class="form-control-inline"/> jaar koorervaring</div>
            </div>

            <div class="form-group row">
                <div class="col-sm-12">
                    <input id="performedBefore" name="performedBefore" type="checkbox" value="1">
                    <label for="performedBefore">Ik heb {{ $event->performedPiece }} al eens eerder gezongen</label>
                </div>
            </div>

            <div><b>Kosten van deelname:</b> <span id="prijsvak">{{ $event->registrationCost|euro }}</span><br>
            Je hoeft pas te betalen nadat je bericht van indeling hebt gekregen.</div>

            <div class="termsAndConditions" style="margin: 25px 0;">{!! $event->termsAndConditions !!}</div>

            <h3>Versturen:</h3>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="comments">Eventuele opmerkingen:</label>
                <div class="col-sm-5"><textarea id="comments" name="comments" class="form-control"
                                                rows="4"></textarea></div>
            </div>

            <p>Om te voorkomen dat er spam wordt verstuurd met dit formulier<br/>word je verzocht in het onderstaande
                vak <span style="font-family:monospace;">{{ $event->getAntiSpam() }}</span> in te vullen.</p>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="antispam">Antispam:</label>
                <div class="col-sm-5"><input id="antispam" name="antispam" required class="form-control"/></div>
            </div>

{{--            <p>Je gegevens zullen worden verwerkt zoals beschreven in onze <a href="/privacyverklaring">privacyverklaring</a>.</p>--}}

            <div class="col-sm-offset-2"><input id="verzendknop" class="btn btn-primary" type="submit"
                                                value="Versturen"/></div>
        </form>
    @endif
@endsection
